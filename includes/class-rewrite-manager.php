<?php
/**
 * Rewrite Manager Class
 * 
 * Gestiona el flush de rewrite rules de manera eficiente para evitar llamadas excesivas.
 *
 * @package WebToWP_Engine
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Rewrite_Manager {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', array( $this, 'maybe_flush_rewrite_rules' ), 999 );
    }

    /**
     * Verifica si se necesita hacer flush de rewrite rules
     */
    public function maybe_flush_rewrite_rules() {
        if ( get_transient( 'w2wp_flush_rewrite_rules' ) ) {
            flush_rewrite_rules();
            delete_transient( 'w2wp_flush_rewrite_rules' );
            error_log( '[WebToWP Engine] Rewrite rules flushed' );
        }
    }

    /**
     * Marca que se necesita hacer flush en el próximo request
     */
    public static function schedule_flush() {
        set_transient( 'w2wp_flush_rewrite_rules', 1, 60 );
    }
}
