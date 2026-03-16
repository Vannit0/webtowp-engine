<?php
/**
 * API Key Manager
 *
 * Gestión avanzada de API keys con soporte para múltiples keys,
 * expiración, permisos granulares y auditoría.
 *
 * @package WebToWP_Engine
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_API_Key_Manager {

    private static $instance = null;
    private $table_name;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'w2wp_api_keys';
        
        add_action( 'init', array( $this, 'init' ) );
    }

    public function init() {
        // Hook para verificar keys expiradas diariamente
        if ( ! wp_next_scheduled( 'w2wp_check_expired_keys' ) ) {
            wp_schedule_event( time(), 'daily', 'w2wp_check_expired_keys' );
        }
        add_action( 'w2wp_check_expired_keys', array( $this, 'deactivate_expired_keys' ) );
    }

    /**
     * Crear tabla de API keys
     */
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'w2wp_api_keys';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            key_hash varchar(255) NOT NULL,
            key_prefix varchar(10) NOT NULL,
            permissions text NOT NULL,
            rate_limit int(11) DEFAULT 60,
            created_by bigint(20) NOT NULL,
            created_at datetime NOT NULL,
            expires_at datetime DEFAULT NULL,
            last_used_at datetime DEFAULT NULL,
            last_used_ip varchar(45) DEFAULT NULL,
            usage_count bigint(20) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            PRIMARY KEY  (id),
            KEY key_hash (key_hash),
            KEY key_prefix (key_prefix),
            KEY is_active (is_active),
            KEY expires_at (expires_at)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Generar nueva API key
     *
     * @param array $args Argumentos para la key
     * @return array|WP_Error
     */
    public function generate_key( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'name'        => 'API Key',
            'permissions' => array( 'read' ),
            'rate_limit'  => 60,
            'expires_in'  => null, // días, null = sin expiración
            'created_by'  => get_current_user_id(),
        );

        $args = wp_parse_args( $args, $defaults );

        // Validar nombre
        if ( empty( $args['name'] ) ) {
            return new WP_Error( 'invalid_name', __( 'El nombre de la API key es requerido.', 'webtowp-engine' ) );
        }

        // Generar key aleatoria
        $key = 'w2wp_' . bin2hex( random_bytes( 32 ) );
        $key_hash = hash( 'sha256', $key );
        $key_prefix = substr( $key, 0, 12 );

        // Calcular fecha de expiración
        $expires_at = null;
        if ( ! empty( $args['expires_in'] ) && is_numeric( $args['expires_in'] ) ) {
            $expires_at = date( 'Y-m-d H:i:s', strtotime( '+' . intval( $args['expires_in'] ) . ' days' ) );
        }

        // Insertar en base de datos
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'name'        => sanitize_text_field( $args['name'] ),
                'key_hash'    => $key_hash,
                'key_prefix'  => $key_prefix,
                'permissions' => json_encode( $args['permissions'] ),
                'rate_limit'  => intval( $args['rate_limit'] ),
                'created_by'  => intval( $args['created_by'] ),
                'created_at'  => current_time( 'mysql' ),
                'expires_at'  => $expires_at,
                'is_active'   => 1,
            ),
            array( '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%d' )
        );

        if ( false === $result ) {
            return new WP_Error( 'db_error', __( 'Error al crear la API key.', 'webtowp-engine' ) );
        }

        $key_id = $wpdb->insert_id;

        // Log de auditoría
        $this->log_action( 'key_created', $key_id, array(
            'name' => $args['name'],
            'permissions' => $args['permissions'],
        ) );

        return array(
            'id'         => $key_id,
            'key'        => $key,
            'key_prefix' => $key_prefix,
            'name'       => $args['name'],
            'expires_at' => $expires_at,
        );
    }

    /**
     * Validar API key
     *
     * @param string $key API key a validar
     * @return array|false Datos de la key o false si es inválida
     */
    public function validate_key( $key ) {
        global $wpdb;

        if ( empty( $key ) || ! is_string( $key ) ) {
            return false;
        }

        $key_hash = hash( 'sha256', $key );

        $key_data = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE key_hash = %s AND is_active = 1",
            $key_hash
        ), ARRAY_A );

        if ( ! $key_data ) {
            return false;
        }

        // Verificar expiración
        if ( ! empty( $key_data['expires_at'] ) ) {
            if ( strtotime( $key_data['expires_at'] ) < time() ) {
                $this->deactivate_key( $key_data['id'], 'expired' );
                return false;
            }
        }

        // Actualizar último uso
        $this->update_last_used( $key_data['id'] );

        // Decodificar permisos
        $key_data['permissions'] = json_decode( $key_data['permissions'], true );

        return $key_data;
    }

    /**
     * Verificar permiso específico
     *
     * @param array $key_data Datos de la key
     * @param string $permission Permiso a verificar
     * @return bool
     */
    public function has_permission( $key_data, $permission ) {
        if ( empty( $key_data['permissions'] ) ) {
            return false;
        }

        // Permiso wildcard
        if ( in_array( '*', $key_data['permissions'], true ) ) {
            return true;
        }

        return in_array( $permission, $key_data['permissions'], true );
    }

    /**
     * Actualizar último uso de la key
     *
     * @param int $key_id ID de la key
     */
    private function update_last_used( $key_id ) {
        global $wpdb;

        $ip = $this->get_client_ip();

        $wpdb->update(
            $this->table_name,
            array(
                'last_used_at' => current_time( 'mysql' ),
                'last_used_ip' => $ip,
                'usage_count'  => $wpdb->prepare( 'usage_count + 1' ),
            ),
            array( 'id' => $key_id ),
            array( '%s', '%s', '%s' ),
            array( '%d' )
        );

        // Incrementar contador manualmente porque prepare() no funciona con expresiones
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$this->table_name} SET usage_count = usage_count + 1 WHERE id = %d",
            $key_id
        ) );
    }

    /**
     * Obtener todas las keys
     *
     * @param array $args Argumentos de filtrado
     * @return array
     */
    public function get_keys( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'is_active' => null,
            'orderby'   => 'created_at',
            'order'     => 'DESC',
            'limit'     => 100,
            'offset'    => 0,
        );

        $args = wp_parse_args( $args, $defaults );

        $where = array( '1=1' );
        $where_values = array();

        if ( ! is_null( $args['is_active'] ) ) {
            $where[] = 'is_active = %d';
            $where_values[] = intval( $args['is_active'] );
        }

        $where_clause = implode( ' AND ', $where );

        $query = "SELECT * FROM {$this->table_name} WHERE {$where_clause}";
        $query .= $wpdb->prepare( " ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d", $args['limit'], $args['offset'] );

        if ( ! empty( $where_values ) ) {
            $query = $wpdb->prepare( $query, $where_values );
        }

        $keys = $wpdb->get_results( $query, ARRAY_A );

        // Decodificar permisos
        foreach ( $keys as &$key ) {
            $key['permissions'] = json_decode( $key['permissions'], true );
        }

        return $keys;
    }

    /**
     * Obtener key por ID
     *
     * @param int $key_id ID de la key
     * @return array|null
     */
    public function get_key( $key_id ) {
        global $wpdb;

        $key = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $key_id
        ), ARRAY_A );

        if ( $key ) {
            $key['permissions'] = json_decode( $key['permissions'], true );
        }

        return $key;
    }

    /**
     * Actualizar key
     *
     * @param int $key_id ID de la key
     * @param array $data Datos a actualizar
     * @return bool
     */
    public function update_key( $key_id, $data ) {
        global $wpdb;

        $allowed_fields = array( 'name', 'permissions', 'rate_limit', 'expires_at', 'is_active' );
        $update_data = array();
        $update_format = array();

        foreach ( $data as $field => $value ) {
            if ( in_array( $field, $allowed_fields, true ) ) {
                if ( 'permissions' === $field ) {
                    $update_data[ $field ] = json_encode( $value );
                    $update_format[] = '%s';
                } elseif ( 'name' === $field ) {
                    $update_data[ $field ] = sanitize_text_field( $value );
                    $update_format[] = '%s';
                } elseif ( in_array( $field, array( 'rate_limit', 'is_active' ), true ) ) {
                    $update_data[ $field ] = intval( $value );
                    $update_format[] = '%d';
                } else {
                    $update_data[ $field ] = $value;
                    $update_format[] = '%s';
                }
            }
        }

        if ( empty( $update_data ) ) {
            return false;
        }

        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            array( 'id' => $key_id ),
            $update_format,
            array( '%d' )
        );

        if ( false !== $result ) {
            $this->log_action( 'key_updated', $key_id, $data );
        }

        return false !== $result;
    }

    /**
     * Desactivar key
     *
     * @param int $key_id ID de la key
     * @param string $reason Razón de desactivación
     * @return bool
     */
    public function deactivate_key( $key_id, $reason = 'manual' ) {
        $result = $this->update_key( $key_id, array( 'is_active' => 0 ) );

        if ( $result ) {
            $this->log_action( 'key_deactivated', $key_id, array( 'reason' => $reason ) );
        }

        return $result;
    }

    /**
     * Eliminar key
     *
     * @param int $key_id ID de la key
     * @return bool
     */
    public function delete_key( $key_id ) {
        global $wpdb;

        $result = $wpdb->delete(
            $this->table_name,
            array( 'id' => $key_id ),
            array( '%d' )
        );

        if ( false !== $result ) {
            $this->log_action( 'key_deleted', $key_id );
        }

        return false !== $result;
    }

    /**
     * Desactivar keys expiradas
     */
    public function deactivate_expired_keys() {
        global $wpdb;

        $wpdb->query(
            "UPDATE {$this->table_name} 
            SET is_active = 0 
            WHERE expires_at IS NOT NULL 
            AND expires_at < NOW() 
            AND is_active = 1"
        );
    }

    /**
     * Obtener estadísticas de uso
     *
     * @param int $key_id ID de la key
     * @param int $days Días hacia atrás
     * @return array
     */
    public function get_usage_stats( $key_id, $days = 30 ) {
        global $wpdb;
        $logger = W2WP_Security_Logger::get_instance();

        $stats = array(
            'total_requests' => 0,
            'requests_by_day' => array(),
            'requests_by_endpoint' => array(),
            'unique_ips' => 0,
        );

        // Obtener logs de los últimos N días
        $logs = $logger->get_logs( array(
            'action' => 'api_request',
            'key_id' => $key_id,
            'days'   => $days,
        ) );

        $ips = array();
        $endpoints = array();

        foreach ( $logs as $log ) {
            $stats['total_requests']++;

            // Por día
            $day = date( 'Y-m-d', strtotime( $log['created_at'] ) );
            if ( ! isset( $stats['requests_by_day'][ $day ] ) ) {
                $stats['requests_by_day'][ $day ] = 0;
            }
            $stats['requests_by_day'][ $day ]++;

            // Por endpoint
            $metadata = json_decode( $log['metadata'], true );
            if ( ! empty( $metadata['endpoint'] ) ) {
                $endpoint = $metadata['endpoint'];
                if ( ! isset( $endpoints[ $endpoint ] ) ) {
                    $endpoints[ $endpoint ] = 0;
                }
                $endpoints[ $endpoint ]++;
            }

            // IPs únicas
            if ( ! empty( $log['ip_address'] ) && ! in_array( $log['ip_address'], $ips, true ) ) {
                $ips[] = $log['ip_address'];
            }
        }

        $stats['unique_ips'] = count( $ips );
        $stats['requests_by_endpoint'] = $endpoints;

        return $stats;
    }

    /**
     * Registrar acción en log de auditoría
     *
     * @param string $action Acción realizada
     * @param int $key_id ID de la key
     * @param array $metadata Metadata adicional
     */
    private function log_action( $action, $key_id, $metadata = array() ) {
        $logger = W2WP_Security_Logger::get_instance();
        
        $logger->log( array(
            'action'     => $action,
            'key_id'     => $key_id,
            'user_id'    => get_current_user_id(),
            'ip_address' => $this->get_client_ip(),
            'metadata'   => $metadata,
        ) );
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
     * Obtener permisos disponibles
     *
     * @return array
     */
    public function get_available_permissions() {
        return array(
            'read'        => __( 'Lectura (GET)', 'webtowp-engine' ),
            'write'       => __( 'Escritura (POST, PUT, PATCH)', 'webtowp-engine' ),
            'delete'      => __( 'Eliminación (DELETE)', 'webtowp-engine' ),
            'deploy'      => __( 'Trigger de deployment', 'webtowp-engine' ),
            'cache'       => __( 'Gestión de caché', 'webtowp-engine' ),
            'settings'    => __( 'Modificar configuración', 'webtowp-engine' ),
            '*'           => __( 'Todos los permisos', 'webtowp-engine' ),
        );
    }

    private function __clone() {}
    public function __wakeup() {
        throw new Exception( 'Cannot unserialize singleton' );
    }
}
