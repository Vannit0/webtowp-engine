<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_API_Config {

    public function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        add_filter( 'acf/rest_api/field_settings/show_in_rest', array( $this, 'enable_acf_in_rest' ), 10, 1 );
        add_filter( 'acf/settings/rest_api_format', array( $this, 'set_acf_rest_format' ) );
        add_action( 'rest_api_init', array( $this, 'register_custom_endpoints' ) );
    }

    public function enable_acf_in_rest( $show_in_rest ) {
        return true;
    }

    public function set_acf_rest_format( $format ) {
        return 'standard';
    }

    public function register_custom_endpoints() {
        register_rest_route( 'webtowp/v1', '/site-info', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_site_info' ),
            'permission_callback' => '__return_true',
        ) );
    }

    public function get_site_info( $request ) {
        $site_logo_id = get_theme_mod( 'custom_logo' );
        $site_logo_url = '';
        
        if ( $site_logo_id ) {
            $site_logo_url = wp_get_attachment_image_url( $site_logo_id, 'full' );
        }

        $response = array(
            'success' => true,
            'data'    => array(
                'name'        => get_bloginfo( 'name' ),
                'description' => get_bloginfo( 'description' ),
                'url'         => get_bloginfo( 'url' ),
                'logo'        => $site_logo_url,
                'admin_email' => get_bloginfo( 'admin_email' ),
                'language'    => get_bloginfo( 'language' ),
                'charset'     => get_bloginfo( 'charset' ),
                'version'     => get_bloginfo( 'version' ),
                'plugin_version' => W2WP_VERSION,
            ),
            'timestamp' => current_time( 'mysql' ),
        );

        return rest_ensure_response( $response );
    }
}
