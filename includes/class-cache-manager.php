<?php
/**
 * Cache Manager Class
 * 
 * Gestiona el sistema de caché del plugin usando transients de WordPress.
 *
 * @package WebToWP_Engine
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Cache_Manager {

    private static $instance = null;
    private $cache_prefix = 'w2wp_cache_';
    private $default_expiration = 3600; // 1 hora

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'save_post', array( $this, 'clear_post_cache' ) );
        add_action( 'deleted_post', array( $this, 'clear_post_cache' ) );
        add_action( 'updated_option', array( $this, 'clear_settings_cache' ), 10, 3 );
        add_action( 'admin_bar_menu', array( $this, 'add_clear_cache_button' ), 100 );
        add_action( 'wp_ajax_w2wp_clear_cache', array( $this, 'ajax_clear_cache' ) );
    }

    /**
     * Obtiene un valor del caché
     */
    public function get( $key, $group = 'default' ) {
        $cache_key = $this->get_cache_key( $key, $group );
        $value = get_transient( $cache_key );
        
        if ( false !== $value ) {
            error_log( "[WebToWP Cache] HIT: {$cache_key}" );
            return $value;
        }
        
        error_log( "[WebToWP Cache] MISS: {$cache_key}" );
        return false;
    }

    /**
     * Guarda un valor en el caché
     */
    public function set( $key, $value, $group = 'default', $expiration = null ) {
        if ( null === $expiration ) {
            $expiration = $this->default_expiration;
        }
        
        $cache_key = $this->get_cache_key( $key, $group );
        $result = set_transient( $cache_key, $value, $expiration );
        
        if ( $result ) {
            error_log( "[WebToWP Cache] SET: {$cache_key} (expires in {$expiration}s)" );
        }
        
        return $result;
    }

    /**
     * Elimina un valor del caché
     */
    public function delete( $key, $group = 'default' ) {
        $cache_key = $this->get_cache_key( $key, $group );
        $result = delete_transient( $cache_key );
        
        if ( $result ) {
            error_log( "[WebToWP Cache] DELETE: {$cache_key}" );
        }
        
        return $result;
    }

    /**
     * Limpia todo el caché de un grupo específico
     */
    public function clear_group( $group ) {
        global $wpdb;
        
        $pattern = $this->cache_prefix . $group . '_%';
        $transient_pattern = '_transient_' . $pattern;
        
        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $transient_pattern
            )
        );
        
        // También eliminar los timeouts
        $timeout_pattern = '_transient_timeout_' . $pattern;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $timeout_pattern
            )
        );
        
        error_log( "[WebToWP Cache] CLEAR GROUP: {$group} ({$deleted} items)" );
        
        return $deleted;
    }

    /**
     * Limpia todo el caché del plugin
     */
    public function clear_all() {
        global $wpdb;
        
        $pattern = $this->cache_prefix . '%';
        $transient_pattern = '_transient_' . $pattern;
        
        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $transient_pattern
            )
        );
        
        // También eliminar los timeouts
        $timeout_pattern = '_transient_timeout_' . $pattern;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $timeout_pattern
            )
        );
        
        error_log( "[WebToWP Cache] CLEAR ALL ({$deleted} items)" );
        
        return $deleted;
    }

    /**
     * Obtiene o establece un valor en caché (patrón remember)
     */
    public function remember( $key, $callback, $group = 'default', $expiration = null ) {
        $value = $this->get( $key, $group );
        
        if ( false !== $value ) {
            return $value;
        }
        
        $value = call_user_func( $callback );
        $this->set( $key, $value, $group, $expiration );
        
        return $value;
    }

    /**
     * Genera la clave de caché completa
     */
    private function get_cache_key( $key, $group ) {
        return $this->cache_prefix . $group . '_' . $key;
    }

    /**
     * Limpia el caché cuando se guarda un post
     */
    public function clear_post_cache( $post_id ) {
        $post_type = get_post_type( $post_id );
        
        // Limpiar caché del post específico
        $this->delete( 'post_' . $post_id, 'posts' );
        
        // Limpiar caché de listados del post type
        $this->clear_group( 'posts_' . $post_type );
        
        // Limpiar caché de API
        $this->clear_group( 'api' );
        
        error_log( "[WebToWP Cache] Post cache cleared for post {$post_id}" );
    }

    /**
     * Limpia el caché cuando se actualiza una opción
     */
    public function clear_settings_cache( $option, $old_value, $value ) {
        // Solo limpiar si es una opción del plugin
        if ( strpos( $option, 'w2wp_' ) === 0 ) {
            $this->clear_group( 'settings' );
            $this->clear_group( 'api' );
            error_log( "[WebToWP Cache] Settings cache cleared for option {$option}" );
        }
    }

    /**
     * Añade botón de limpiar caché en la admin bar
     */
    public function add_clear_cache_button( $wp_admin_bar ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $wp_admin_bar->add_node( array(
            'id'    => 'w2wp_clear_cache',
            'title' => '🗑️ Limpiar Caché W2WP',
            'href'  => '#',
            'meta'  => array(
                'class' => 'w2wp-clear-cache-button',
            ),
        ) );
    }

    /**
     * Maneja la petición AJAX para limpiar caché
     */
    public function ajax_clear_cache() {
        check_ajax_referer( 'w2wp_clear_cache_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array(
                'message' => __( 'No tienes permisos para realizar esta acción.', 'webtowp-engine' ),
            ) );
        }

        $deleted = $this->clear_all();
        
        wp_send_json_success( array(
            'message' => sprintf(
                __( 'Caché limpiado correctamente. Se eliminaron %d elementos.', 'webtowp-engine' ),
                $deleted
            ),
        ) );
    }

    /**
     * Obtiene estadísticas del caché
     */
    public function get_stats() {
        global $wpdb;
        
        $pattern = $this->cache_prefix . '%';
        $transient_pattern = '_transient_' . $pattern;
        
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
                $transient_pattern
            )
        );
        
        $size = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE option_name LIKE %s",
                $transient_pattern
            )
        );
        
        return array(
            'count' => (int) $count,
            'size' => (int) $size,
            'size_formatted' => size_format( $size ),
        );
    }

    /**
     * Cachea la respuesta de un endpoint de API
     */
    public function cache_api_response( $endpoint, $data, $expiration = 300 ) {
        return $this->set( 'endpoint_' . md5( $endpoint ), $data, 'api', $expiration );
    }

    /**
     * Obtiene la respuesta cacheada de un endpoint
     */
    public function get_cached_api_response( $endpoint ) {
        return $this->get( 'endpoint_' . md5( $endpoint ), 'api' );
    }

    /**
     * Cachea configuración global
     */
    public function cache_global_settings( $settings, $expiration = 3600 ) {
        return $this->set( 'global_settings', $settings, 'settings', $expiration );
    }

    /**
     * Obtiene configuración global cacheada
     */
    public function get_cached_global_settings() {
        return $this->get( 'global_settings', 'settings' );
    }

    /**
     * Cachea información del sitio
     */
    public function cache_site_info( $info, $expiration = 3600 ) {
        return $this->set( 'site_info', $info, 'settings', $expiration );
    }

    /**
     * Obtiene información del sitio cacheada
     */
    public function get_cached_site_info() {
        return $this->get( 'site_info', 'settings' );
    }
}
