<?php
/**
 * Deployment Logger Class
 * 
 * Gestiona el registro de logs de deployment en la base de datos.
 *
 * @package WebToWP_Engine
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Deployment_Logger {

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
        $this->table_name = $wpdb->prefix . 'w2wp_deployment_logs';
    }

    /**
     * Registra un deployment exitoso
     */
    public function log_success( $action, $response_code = 200, $message = '' ) {
        return $this->log( $action, 'success', $response_code, $message );
    }

    /**
     * Registra un deployment fallido
     */
    public function log_error( $action, $response_code = 500, $message = '' ) {
        return $this->log( $action, 'error', $response_code, $message );
    }

    /**
     * Registra un deployment
     */
    public function log( $action, $status = 'success', $response_code = 200, $message = '' ) {
        global $wpdb;

        $user_id = get_current_user_id();
        
        $data = array(
            'timestamp' => current_time( 'mysql' ),
            'action' => sanitize_text_field( $action ),
            'status' => sanitize_text_field( $status ),
            'response_code' => absint( $response_code ),
            'response_message' => sanitize_textarea_field( $message ),
            'user_id' => $user_id,
        );

        $result = $wpdb->insert( $this->table_name, $data );

        if ( $result ) {
            error_log( "[WebToWP Deployment Logger] Logged: {$action} - Status: {$status}" );
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Obtiene logs con paginación
     */
    public function get_logs( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'limit' => 20,
            'offset' => 0,
            'status' => '', // 'success', 'error', o vacío para todos
            'action' => '',
            'user_id' => 0,
            'order_by' => 'timestamp',
            'order' => 'DESC',
        );

        $args = wp_parse_args( $args, $defaults );

        $where = array( '1=1' );
        $where_values = array();

        if ( ! empty( $args['status'] ) ) {
            $where[] = 'status = %s';
            $where_values[] = $args['status'];
        }

        if ( ! empty( $args['action'] ) ) {
            $where[] = 'action = %s';
            $where_values[] = $args['action'];
        }

        if ( ! empty( $args['user_id'] ) ) {
            $where[] = 'user_id = %d';
            $where_values[] = $args['user_id'];
        }

        $where_clause = implode( ' AND ', $where );

        $order_by = sanitize_sql_orderby( $args['order_by'] . ' ' . $args['order'] );
        if ( ! $order_by ) {
            $order_by = 'timestamp DESC';
        }

        $limit = absint( $args['limit'] );
        $offset = absint( $args['offset'] );

        if ( ! empty( $where_values ) ) {
            $query = $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE {$where_clause} ORDER BY {$order_by} LIMIT %d OFFSET %d",
                array_merge( $where_values, array( $limit, $offset ) )
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE {$where_clause} ORDER BY {$order_by} LIMIT %d OFFSET %d",
                $limit,
                $offset
            );
        }

        return $wpdb->get_results( $query );
    }

    /**
     * Cuenta total de logs
     */
    public function count_logs( $args = array() ) {
        global $wpdb;

        $where = array( '1=1' );
        $where_values = array();

        if ( ! empty( $args['status'] ) ) {
            $where[] = 'status = %s';
            $where_values[] = $args['status'];
        }

        if ( ! empty( $args['action'] ) ) {
            $where[] = 'action = %s';
            $where_values[] = $args['action'];
        }

        if ( ! empty( $args['user_id'] ) ) {
            $where[] = 'user_id = %d';
            $where_values[] = $args['user_id'];
        }

        $where_clause = implode( ' AND ', $where );

        if ( ! empty( $where_values ) ) {
            $query = $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause}",
                $where_values
            );
        } else {
            $query = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause}";
        }

        return (int) $wpdb->get_var( $query );
    }

    /**
     * Obtiene un log específico por ID
     */
    public function get_log( $log_id ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
                $log_id
            )
        );
    }

    /**
     * Elimina logs antiguos
     */
    public function cleanup_old_logs( $days = 30 ) {
        global $wpdb;

        $date = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->table_name} WHERE timestamp < %s",
                $date
            )
        );

        error_log( "[WebToWP Deployment Logger] Cleaned up {$deleted} old logs (older than {$days} days)" );

        return $deleted;
    }

    /**
     * Elimina todos los logs
     */
    public function clear_all_logs() {
        global $wpdb;

        $deleted = $wpdb->query( "TRUNCATE TABLE {$this->table_name}" );

        error_log( "[WebToWP Deployment Logger] All logs cleared" );

        return $deleted;
    }

    /**
     * Obtiene estadísticas de deployments
     */
    public function get_stats( $days = 7 ) {
        global $wpdb;

        $date = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        $total = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE timestamp >= %s",
                $date
            )
        );

        $success = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE timestamp >= %s AND status = 'success'",
                $date
            )
        );

        $errors = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE timestamp >= %s AND status = 'error'",
                $date
            )
        );

        $success_rate = $total > 0 ? round( ( $success / $total ) * 100, 2 ) : 0;

        return array(
            'total' => (int) $total,
            'success' => (int) $success,
            'errors' => (int) $errors,
            'success_rate' => $success_rate,
            'period_days' => $days,
        );
    }

    /**
     * Obtiene el último deployment
     */
    public function get_last_deployment() {
        global $wpdb;

        return $wpdb->get_row(
            "SELECT * FROM {$this->table_name} ORDER BY timestamp DESC LIMIT 1"
        );
    }

    /**
     * Exporta logs a CSV
     */
    public function export_to_csv( $args = array() ) {
        $logs = $this->get_logs( array_merge( $args, array( 'limit' => 999999 ) ) );

        if ( empty( $logs ) ) {
            return false;
        }

        $csv = "ID,Timestamp,Action,Status,Response Code,Message,User ID\n";

        foreach ( $logs as $log ) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%d,%s,%d\n",
                $log->id,
                $log->timestamp,
                $log->action,
                $log->status,
                $log->response_code,
                str_replace( array( "\r", "\n", '"' ), array( '', '', '""' ), $log->response_message ),
                $log->user_id
            );
        }

        return $csv;
    }
}
