<?php
/**
 * Rate Limiter
 *
 * Sistema de rate limiting y throttling para proteger la API
 * contra abuso y ataques DDoS.
 *
 * @package WebToWP_Engine
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Rate_Limiter {

    private static $instance = null;
    private $cache_group = 'w2wp_rate_limit';

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter( 'rest_pre_dispatch', array( $this, 'check_rate_limit' ), 10, 3 );
    }

    /**
     * Verificar rate limit antes de procesar request
     *
     * @param mixed $result Response to replace the requested version with
     * @param WP_REST_Server $server Server instance
     * @param WP_REST_Request $request Request used to generate the response
     * @return mixed
     */
    public function check_rate_limit( $result, $server, $request ) {
        // Solo aplicar a endpoints de WebToWP
        $route = $request->get_route();
        if ( strpos( $route, '/webtowp/' ) === false ) {
            return $result;
        }

        // Obtener API key del header
        $api_key = $request->get_header( 'X-WebToWP-Key' );
        
        if ( empty( $api_key ) ) {
            // Rate limit por IP para requests sin autenticación
            $identifier = $this->get_client_ip();
            $limit = 20; // 20 requests por minuto para no autenticados
            $window = 60; // 1 minuto
        } else {
            // Rate limit por API key
            $key_manager = W2WP_API_Key_Manager::get_instance();
            $key_data = $key_manager->validate_key( $api_key );
            
            if ( ! $key_data ) {
                return new WP_Error(
                    'invalid_api_key',
                    __( 'API key inválida.', 'webtowp-engine' ),
                    array( 'status' => 401 )
                );
            }

            $identifier = 'key_' . $key_data['id'];
            $limit = ! empty( $key_data['rate_limit'] ) ? intval( $key_data['rate_limit'] ) : 60;
            $window = 60; // 1 minuto
        }

        // Verificar rate limit
        $is_allowed = $this->is_allowed( $identifier, $limit, $window );

        if ( ! $is_allowed ) {
            $retry_after = $this->get_retry_after( $identifier, $window );
            
            // Log de rate limit excedido
            $this->log_rate_limit_exceeded( $identifier, $route );

            return new WP_Error(
                'rate_limit_exceeded',
                sprintf(
                    __( 'Rate limit excedido. Intenta nuevamente en %d segundos.', 'webtowp-engine' ),
                    $retry_after
                ),
                array(
                    'status' => 429,
                    'headers' => array(
                        'X-RateLimit-Limit' => $limit,
                        'X-RateLimit-Remaining' => 0,
                        'X-RateLimit-Reset' => time() + $retry_after,
                        'Retry-After' => $retry_after,
                    ),
                )
            );
        }

        // Añadir headers de rate limit a la respuesta
        add_filter( 'rest_post_dispatch', function( $response ) use ( $identifier, $limit, $window ) {
            $remaining = $this->get_remaining( $identifier, $limit, $window );
            $reset = $this->get_reset_time( $identifier, $window );

            $response->header( 'X-RateLimit-Limit', $limit );
            $response->header( 'X-RateLimit-Remaining', $remaining );
            $response->header( 'X-RateLimit-Reset', $reset );

            return $response;
        }, 10, 1 );

        return $result;
    }

    /**
     * Verificar si el request está permitido
     *
     * @param string $identifier Identificador único (IP o key ID)
     * @param int $limit Límite de requests
     * @param int $window Ventana de tiempo en segundos
     * @return bool
     */
    public function is_allowed( $identifier, $limit, $window ) {
        $cache_key = $this->get_cache_key( $identifier );
        $requests = $this->get_requests( $cache_key );

        // Limpiar requests antiguos
        $current_time = time();
        $requests = array_filter( $requests, function( $timestamp ) use ( $current_time, $window ) {
            return ( $current_time - $timestamp ) < $window;
        } );

        // Verificar si excede el límite
        if ( count( $requests ) >= $limit ) {
            return false;
        }

        // Registrar nuevo request
        $requests[] = $current_time;
        $this->save_requests( $cache_key, $requests, $window );

        return true;
    }

    /**
     * Obtener requests restantes
     *
     * @param string $identifier Identificador único
     * @param int $limit Límite de requests
     * @param int $window Ventana de tiempo en segundos
     * @return int
     */
    public function get_remaining( $identifier, $limit, $window ) {
        $cache_key = $this->get_cache_key( $identifier );
        $requests = $this->get_requests( $cache_key );

        // Limpiar requests antiguos
        $current_time = time();
        $requests = array_filter( $requests, function( $timestamp ) use ( $current_time, $window ) {
            return ( $current_time - $timestamp ) < $window;
        } );

        $used = count( $requests );
        $remaining = max( 0, $limit - $used );

        return $remaining;
    }

    /**
     * Obtener tiempo hasta el reset
     *
     * @param string $identifier Identificador único
     * @param int $window Ventana de tiempo en segundos
     * @return int Timestamp
     */
    public function get_reset_time( $identifier, $window ) {
        $cache_key = $this->get_cache_key( $identifier );
        $requests = $this->get_requests( $cache_key );

        if ( empty( $requests ) ) {
            return time() + $window;
        }

        $oldest_request = min( $requests );
        return $oldest_request + $window;
    }

    /**
     * Obtener segundos hasta poder reintentar
     *
     * @param string $identifier Identificador único
     * @param int $window Ventana de tiempo en segundos
     * @return int
     */
    public function get_retry_after( $identifier, $window ) {
        $reset_time = $this->get_reset_time( $identifier, $window );
        return max( 0, $reset_time - time() );
    }

    /**
     * Obtener requests del caché
     *
     * @param string $cache_key Clave de caché
     * @return array
     */
    private function get_requests( $cache_key ) {
        $requests = get_transient( $cache_key );
        return is_array( $requests ) ? $requests : array();
    }

    /**
     * Guardar requests en caché
     *
     * @param string $cache_key Clave de caché
     * @param array $requests Array de timestamps
     * @param int $expiration Tiempo de expiración
     */
    private function save_requests( $cache_key, $requests, $expiration ) {
        set_transient( $cache_key, $requests, $expiration );
    }

    /**
     * Generar clave de caché
     *
     * @param string $identifier Identificador único
     * @return string
     */
    private function get_cache_key( $identifier ) {
        return $this->cache_group . '_' . md5( $identifier );
    }

    /**
     * Obtener IP del cliente
     *
     * @return string
     */
    private function get_client_ip() {
        $ip = '';

        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return sanitize_text_field( $ip );
    }

    /**
     * Registrar rate limit excedido en log
     *
     * @param string $identifier Identificador
     * @param string $route Ruta del endpoint
     */
    private function log_rate_limit_exceeded( $identifier, $route ) {
        $logger = W2WP_Security_Logger::get_instance();
        
        $logger->log( array(
            'action'     => 'rate_limit_exceeded',
            'ip_address' => $this->get_client_ip(),
            'metadata'   => array(
                'identifier' => $identifier,
                'route'      => $route,
            ),
        ) );
    }

    /**
     * Limpiar rate limits (útil para testing)
     *
     * @param string $identifier Identificador específico o null para todos
     */
    public function clear_limits( $identifier = null ) {
        if ( $identifier ) {
            $cache_key = $this->get_cache_key( $identifier );
            delete_transient( $cache_key );
        } else {
            global $wpdb;
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                    '_transient_' . $this->cache_group . '_%'
                )
            );
        }
    }

    /**
     * Verificar si una IP está en lista negra
     *
     * @param string $ip Dirección IP
     * @return bool
     */
    public function is_blacklisted( $ip ) {
        $blacklist = get_option( 'w2wp_ip_blacklist', array() );
        return in_array( $ip, $blacklist, true );
    }

    /**
     * Añadir IP a lista negra
     *
     * @param string $ip Dirección IP
     * @param string $reason Razón del bloqueo
     * @return bool
     */
    public function blacklist_ip( $ip, $reason = '' ) {
        $blacklist = get_option( 'w2wp_ip_blacklist', array() );
        
        if ( ! in_array( $ip, $blacklist, true ) ) {
            $blacklist[] = $ip;
            update_option( 'w2wp_ip_blacklist', $blacklist );

            // Log
            $logger = W2WP_Security_Logger::get_instance();
            $logger->log( array(
                'action'     => 'ip_blacklisted',
                'ip_address' => $ip,
                'metadata'   => array( 'reason' => $reason ),
            ) );

            return true;
        }

        return false;
    }

    /**
     * Remover IP de lista negra
     *
     * @param string $ip Dirección IP
     * @return bool
     */
    public function unblacklist_ip( $ip ) {
        $blacklist = get_option( 'w2wp_ip_blacklist', array() );
        $key = array_search( $ip, $blacklist, true );

        if ( false !== $key ) {
            unset( $blacklist[ $key ] );
            update_option( 'w2wp_ip_blacklist', array_values( $blacklist ) );

            // Log
            $logger = W2WP_Security_Logger::get_instance();
            $logger->log( array(
                'action'     => 'ip_unblacklisted',
                'ip_address' => $ip,
            ) );

            return true;
        }

        return false;
    }

    /**
     * Obtener lista negra completa
     *
     * @return array
     */
    public function get_blacklist() {
        return get_option( 'w2wp_ip_blacklist', array() );
    }

    /**
     * Auto-bloquear IP por intentos fallidos
     *
     * @param string $ip Dirección IP
     * @param int $threshold Número de intentos antes de bloquear
     * @param int $window Ventana de tiempo en segundos
     */
    public function auto_block_on_failed_attempts( $ip, $threshold = 10, $window = 300 ) {
        $cache_key = 'w2wp_failed_attempts_' . md5( $ip );
        $attempts = get_transient( $cache_key );
        $attempts = is_array( $attempts ) ? $attempts : array();

        // Limpiar intentos antiguos
        $current_time = time();
        $attempts = array_filter( $attempts, function( $timestamp ) use ( $current_time, $window ) {
            return ( $current_time - $timestamp ) < $window;
        } );

        // Añadir nuevo intento
        $attempts[] = $current_time;
        set_transient( $cache_key, $attempts, $window );

        // Verificar si excede el threshold
        if ( count( $attempts ) >= $threshold ) {
            $this->blacklist_ip( $ip, sprintf(
                __( 'Auto-bloqueado por %d intentos fallidos en %d segundos', 'webtowp-engine' ),
                $threshold,
                $window
            ) );
        }
    }

    private function __clone() {}
    public function __wakeup() {
        throw new Exception( 'Cannot unserialize singleton' );
    }
}
