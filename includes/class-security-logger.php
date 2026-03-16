<?php
/**
 * Security Logger
 *
 * Sistema de auditoría de seguridad para registrar eventos
 * relacionados con seguridad, autenticación y acceso.
 *
 * @package WebToWP_Engine
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Security_Logger {

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
        $this->table_name = $wpdb->prefix . 'w2wp_security_logs';
    }

    /**
     * Crear tabla de logs de seguridad
     */
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'w2wp_security_logs';
        $charset_collate = $wpdb->get_charset_collate();
        
        // Asegurar que charset_collate no sea null
        if ( ! is_string( $charset_collate ) ) {
            $charset_collate = '';
        }

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            action varchar(100) NOT NULL,
            severity enum('low','medium','high','critical') DEFAULT 'low',
            user_id bigint(20) DEFAULT NULL,
            key_id bigint(20) DEFAULT NULL,
            ip_address varchar(45) NOT NULL,
            user_agent text DEFAULT NULL,
            endpoint varchar(255) DEFAULT NULL,
            metadata longtext DEFAULT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY action (action),
            KEY severity (severity),
            KEY user_id (user_id),
            KEY key_id (key_id),
            KEY ip_address (ip_address),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Registrar evento de seguridad
     *
     * @param array $args Argumentos del log
     * @return int|false ID del log o false en error
     */
    public function log( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'action'     => '',
            'severity'   => 'low',
            'user_id'    => null,
            'key_id'     => null,
            'ip_address' => $this->get_client_ip(),
            'user_agent' => $this->get_user_agent(),
            'endpoint'   => $this->get_current_endpoint(),
            'metadata'   => array(),
        );

        $args = wp_parse_args( $args, $defaults );

        // Validar acción
        if ( empty( $args['action'] ) ) {
            return false;
        }

        // Convertir metadata a JSON
        if ( is_array( $args['metadata'] ) ) {
            $args['metadata'] = wp_json_encode( $args['metadata'] );
        }

        // Insertar en base de datos
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'action'     => sanitize_text_field( $args['action'] ),
                'severity'   => $this->validate_severity( $args['severity'] ),
                'user_id'    => ! empty( $args['user_id'] ) ? intval( $args['user_id'] ) : null,
                'key_id'     => ! empty( $args['key_id'] ) ? intval( $args['key_id'] ) : null,
                'ip_address' => sanitize_text_field( $args['ip_address'] ),
                'user_agent' => sanitize_text_field( $args['user_agent'] ),
                'endpoint'   => sanitize_text_field( $args['endpoint'] ),
                'metadata'   => $args['metadata'],
                'created_at' => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
        );

        if ( false === $result ) {
            return false;
        }

        // Si es crítico, enviar notificación
        if ( 'critical' === $args['severity'] ) {
            $this->send_critical_alert( $args );
        }

        return $wpdb->insert_id;
    }

    /**
     * Obtener logs con filtros
     *
     * @param array $args Argumentos de filtrado
     * @return array
     */
    public function get_logs( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'action'     => null,
            'severity'   => null,
            'user_id'    => null,
            'key_id'     => null,
            'ip_address' => null,
            'days'       => null,
            'orderby'    => 'created_at',
            'order'      => 'DESC',
            'limit'      => 100,
            'offset'     => 0,
        );

        $args = wp_parse_args( $args, $defaults );

        $where = array( '1=1' );
        $where_values = array();

        if ( ! is_null( $args['action'] ) ) {
            $where[] = 'action = %s';
            $where_values[] = $args['action'];
        }

        if ( ! is_null( $args['severity'] ) ) {
            $where[] = 'severity = %s';
            $where_values[] = $args['severity'];
        }

        if ( ! is_null( $args['user_id'] ) ) {
            $where[] = 'user_id = %d';
            $where_values[] = intval( $args['user_id'] );
        }

        if ( ! is_null( $args['key_id'] ) ) {
            $where[] = 'key_id = %d';
            $where_values[] = intval( $args['key_id'] );
        }

        if ( ! is_null( $args['ip_address'] ) ) {
            $where[] = 'ip_address = %s';
            $where_values[] = $args['ip_address'];
        }

        if ( ! is_null( $args['days'] ) ) {
            $where[] = 'created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)';
            $where_values[] = intval( $args['days'] );
        }

        $where_clause = implode( ' AND ', $where );

        $query = "SELECT * FROM {$this->table_name} WHERE {$where_clause}";
        $query .= $wpdb->prepare( " ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d", $args['limit'], $args['offset'] );

        if ( ! empty( $where_values ) ) {
            $query = $wpdb->prepare( $query, $where_values );
        }

        $logs = $wpdb->get_results( $query, ARRAY_A );

        // Decodificar metadata
        foreach ( $logs as &$log ) {
            if ( ! empty( $log['metadata'] ) ) {
                $log['metadata'] = json_decode( $log['metadata'], true );
            }
        }

        return $logs;
    }

    /**
     * Obtener estadísticas de seguridad
     *
     * @param int $days Días hacia atrás
     * @return array
     */
    public function get_stats( $days = 30 ) {
        global $wpdb;

        $stats = array(
            'total_events'       => 0,
            'by_severity'        => array(),
            'by_action'          => array(),
            'unique_ips'         => 0,
            'failed_attempts'    => 0,
            'blocked_requests'   => 0,
            'suspicious_activity' => 0,
        );

        // Total de eventos
        $stats['total_events'] = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ) );

        // Por severidad
        $by_severity = $wpdb->get_results( $wpdb->prepare(
            "SELECT severity, COUNT(*) as count FROM {$this->table_name} 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY) 
            GROUP BY severity",
            $days
        ), ARRAY_A );

        foreach ( $by_severity as $row ) {
            $stats['by_severity'][ $row['severity'] ] = intval( $row['count'] );
        }

        // Por acción
        $by_action = $wpdb->get_results( $wpdb->prepare(
            "SELECT action, COUNT(*) as count FROM {$this->table_name} 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY) 
            GROUP BY action 
            ORDER BY count DESC 
            LIMIT 10",
            $days
        ), ARRAY_A );

        foreach ( $by_action as $row ) {
            $stats['by_action'][ $row['action'] ] = intval( $row['count'] );
        }

        // IPs únicas
        $stats['unique_ips'] = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT ip_address) FROM {$this->table_name} WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ) );

        // Intentos fallidos
        $stats['failed_attempts'] = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} 
            WHERE action IN ('auth_failed', 'invalid_api_key', 'permission_denied') 
            AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ) );

        // Requests bloqueados
        $stats['blocked_requests'] = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} 
            WHERE action IN ('rate_limit_exceeded', 'ip_blacklisted') 
            AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ) );

        // Actividad sospechosa
        $stats['suspicious_activity'] = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} 
            WHERE severity IN ('high', 'critical') 
            AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ) );

        return $stats;
    }

    /**
     * Obtener IPs sospechosas
     *
     * @param int $days Días hacia atrás
     * @param int $threshold Número mínimo de eventos sospechosos
     * @return array
     */
    public function get_suspicious_ips( $days = 7, $threshold = 10 ) {
        global $wpdb;

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT ip_address, COUNT(*) as event_count, 
            MAX(created_at) as last_event 
            FROM {$this->table_name} 
            WHERE severity IN ('high', 'critical') 
            AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY) 
            GROUP BY ip_address 
            HAVING event_count >= %d 
            ORDER BY event_count DESC",
            $days,
            $threshold
        ), ARRAY_A );

        return $results;
    }

    /**
     * Limpiar logs antiguos
     *
     * @param int $days Días a mantener
     * @return int Número de registros eliminados
     */
    public function cleanup_old_logs( $days = 90 ) {
        global $wpdb;

        $deleted = $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ) );

        if ( $deleted ) {
            $this->log( array(
                'action'   => 'logs_cleaned',
                'severity' => 'low',
                'metadata' => array(
                    'deleted_count' => $deleted,
                    'days'          => $days,
                ),
            ) );
        }

        return $deleted;
    }

    /**
     * Exportar logs a CSV
     *
     * @param array $args Argumentos de filtrado
     * @return string Contenido CSV
     */
    public function export_to_csv( $args = array() ) {
        $logs = $this->get_logs( array_merge( $args, array( 'limit' => 10000 ) ) );

        $csv = "ID,Acción,Severidad,Usuario,IP,Endpoint,Fecha\n";

        foreach ( $logs as $log ) {
            $user = ! empty( $log['user_id'] ) ? get_userdata( $log['user_id'] ) : null;
            $username = $user ? $user->user_login : 'N/A';

            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s\n",
                $log['id'],
                $log['action'],
                $log['severity'],
                $username,
                $log['ip_address'],
                $log['endpoint'],
                $log['created_at']
            );
        }

        return $csv;
    }

    /**
     * Validar severidad
     *
     * @param string $severity Severidad a validar
     * @return string
     */
    private function validate_severity( $severity ) {
        $valid = array( 'low', 'medium', 'high', 'critical' );
        return in_array( $severity, $valid, true ) ? $severity : 'low';
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
     * Obtener User Agent
     *
     * @return string
     */
    private function get_user_agent() {
        return ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '';
    }

    /**
     * Obtener endpoint actual
     *
     * @return string
     */
    private function get_current_endpoint() {
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            global $wp;
            return ! empty( $wp->query_vars['rest_route'] ) ? $wp->query_vars['rest_route'] : '';
        }
        return '';
    }

    /**
     * Enviar alerta crítica
     *
     * @param array $args Datos del evento
     */
    private function send_critical_alert( $args ) {
        $admin_email = get_option( 'admin_email' );
        
        $subject = sprintf(
            __( '[WebToWP Engine] Alerta de Seguridad Crítica: %s', 'webtowp-engine' ),
            $args['action']
        );

        $message = sprintf(
            __( "Se ha detectado un evento de seguridad crítico:\n\nAcción: %s\nIP: %s\nFecha: %s\n\nMetadata: %s", 'webtowp-engine' ),
            $args['action'],
            $args['ip_address'],
            current_time( 'mysql' ),
            is_array( $args['metadata'] ) ? print_r( $args['metadata'], true ) : $args['metadata']
        );

        wp_mail( $admin_email, $subject, $message );
    }

    /**
     * Métodos helper para eventos comunes
     */

    public function log_api_request( $key_id, $endpoint, $success = true ) {
        $this->log( array(
            'action'   => 'api_request',
            'severity' => 'low',
            'key_id'   => $key_id,
            'endpoint' => $endpoint,
            'metadata' => array( 'success' => $success ),
        ) );
    }

    public function log_auth_failed( $username ) {
        $this->log( array(
            'action'   => 'auth_failed',
            'severity' => 'medium',
            'metadata' => array( 'username' => $username ),
        ) );
    }

    public function log_invalid_api_key( $key_prefix ) {
        $this->log( array(
            'action'   => 'invalid_api_key',
            'severity' => 'medium',
            'metadata' => array( 'key_prefix' => $key_prefix ),
        ) );
    }

    public function log_permission_denied( $key_id, $permission ) {
        $this->log( array(
            'action'   => 'permission_denied',
            'severity' => 'medium',
            'key_id'   => $key_id,
            'metadata' => array( 'permission' => $permission ),
        ) );
    }

    public function log_suspicious_activity( $description, $metadata = array() ) {
        $this->log( array(
            'action'   => 'suspicious_activity',
            'severity' => 'high',
            'metadata' => array_merge( array( 'description' => $description ), $metadata ),
        ) );
    }

    public function log_sql_injection_attempt( $query ) {
        $this->log( array(
            'action'   => 'sql_injection_attempt',
            'severity' => 'critical',
            'metadata' => array( 'query' => $query ),
        ) );
    }

    public function log_xss_attempt( $input ) {
        $this->log( array(
            'action'   => 'xss_attempt',
            'severity' => 'critical',
            'metadata' => array( 'input' => substr( $input, 0, 500 ) ),
        ) );
    }

    private function __clone() {}
    public function __wakeup() {
        throw new Exception( 'Cannot unserialize singleton' );
    }
}
