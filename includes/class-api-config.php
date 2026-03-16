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
            'permission_callback' => array( $this, 'validate_api_key' ),
        ) );

        register_rest_route( 'webtowp/v1', '/settings', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_global_settings' ),
            'permission_callback' => array( $this, 'validate_api_key' ),
        ) );

        register_rest_route( 'webtowp/v1', '/debug', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_debug_info' ),
            'permission_callback' => array( $this, 'validate_api_key' ),
        ) );

        register_rest_route( 'webtowp/v1', '/pages/(?P<slug>[a-zA-Z0-9-]+)', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_page_by_slug' ),
            'permission_callback' => array( $this, 'validate_api_key' ),
            'args'                => array(
                'slug' => array(
                    'required'          => true,
                    'validate_callback' => function( $param ) {
                        return is_string( $param );
                    },
                ),
            ),
        ) );

        register_rest_route( 'webtowp/v1', '/servicios', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_servicios' ),
            'permission_callback' => array( $this, 'validate_api_key' ),
        ) );
    }

    public function validate_api_key( $request ) {
        $stored_api_key = get_option( 'w2wp_api_key', '' );
        
        if ( empty( $stored_api_key ) ) {
            return new WP_Error(
                'no_api_key_configured',
                __( 'No se ha configurado una clave de API. Por favor, genera una en WebToWP > Despliegue & API.', 'webtowp-engine' ),
                array( 'status' => 503 )
            );
        }
        
        $provided_key = $request->get_header( 'X-WebToWP-Key' );
        
        if ( empty( $provided_key ) ) {
            return new WP_Error(
                'missing_api_key',
                __( 'Se requiere la clave de API en el header X-WebToWP-Key.', 'webtowp-engine' ),
                array( 'status' => 401 )
            );
        }
        
        if ( $provided_key !== $stored_api_key ) {
            return new WP_Error(
                'invalid_api_key',
                __( 'Clave de API inválida.', 'webtowp-engine' ),
                array( 'status' => 403 )
            );
        }
        
        return true;
    }

    public function get_site_info( $request ) {
        $cache = W2WP_Cache_Manager::get_instance();
        
        // Intentar obtener del caché
        $cached = $cache->get_cached_site_info();
        if ( false !== $cached ) {
            return rest_ensure_response( $cached );
        }
        
        $site_logo_id = get_theme_mod( 'custom_logo' );
        $site_logo_url = '';
        
        if ( $site_logo_id ) {
            $site_logo_url = wp_get_attachment_image_url( $site_logo_id, 'full' );
        }

        $response = array(
            'success' => true,
            'data'    => array(
                'name'        => get_bloginfo( 'name' ) ?: '',
                'description' => get_bloginfo( 'description' ) ?: '',
                'url'         => get_bloginfo( 'url' ) ?: home_url(),
                'logo'        => $site_logo_url,
                'admin_email' => get_bloginfo( 'admin_email' ) ?: get_option( 'admin_email', '' ),
                'language'    => get_bloginfo( 'language' ) ?: 'en-US',
                'charset'     => get_bloginfo( 'charset' ) ?: 'UTF-8',
                'version'     => get_bloginfo( 'version' ) ?: '',
                'plugin_version' => W2WP_VERSION,
            ),
            'timestamp' => current_time( 'mysql' ),
        );
        
        // Guardar en caché por 1 hora
        $cache->cache_site_info( $response, 3600 );

        return rest_ensure_response( $response );
    }

    public function get_global_settings( $request ) {
        $cache = W2WP_Cache_Manager::get_instance();
        
        // Intentar obtener del caché
        $cached = $cache->get_cached_global_settings();
        if ( false !== $cached ) {
            return rest_ensure_response( $cached );
        }
        
        $logo_principal = get_option( 'w2wp_logo_principal', '' );
        $logo_contraste = get_option( 'w2wp_logo_contraste', '' );
        $favicon = get_option( 'w2wp_favicon', '' );
        $brand_name = get_option( 'w2wp_brand_name', '' );
        $copyright_text = get_option( 'w2wp_copyright_text', '' );
        $primary_color = get_option( 'w2wp_primary_color', '#667eea' );
        $secondary_color = get_option( 'w2wp_secondary_color', '#764ba2' );
        $whatsapp_contact = get_option( 'w2wp_whatsapp_contact', '' );
        $support_email = get_option( 'w2wp_support_email', '' );
        $physical_address = get_option( 'w2wp_physical_address', '' );
        $social_instagram = get_option( 'w2wp_social_instagram', '' );
        $social_linkedin = get_option( 'w2wp_social_linkedin', '' );
        $social_facebook = get_option( 'w2wp_social_facebook', '' );
        $social_twitter = get_option( 'w2wp_social_twitter', '' );
        $social_youtube = get_option( 'w2wp_social_youtube', '' );
        $google_analytics_id = get_option( 'w2wp_google_analytics_id', '' );
        $facebook_pixel_id = get_option( 'w2wp_facebook_pixel_id', '' );
        $frontend_url = get_option( 'w2wp_frontend_url', '' );
        $header_scripts = get_option( 'w2wp_header_scripts', '' );
        $footer_scripts = get_option( 'w2wp_footer_scripts', '' );
        $signature_text = get_option( 'w2wp_signature_text', 'Desarrollado por WebToWP' );
        $signature_url = get_option( 'w2wp_signature_url', 'https://webtowp.com' );
        
        $signature_html = sprintf(
            '<a href="%s" target="_blank" rel="noopener">%s</a>',
            esc_url( $signature_url ),
            esc_html( $signature_text )
        );

        $response = array(
            'success' => true,
            'data'    => array(
                'brand_identity' => array(
                    'logo_principal' => ! empty( $logo_principal ) ? $logo_principal : null,
                    'logo_contraste' => ! empty( $logo_contraste ) ? $logo_contraste : null,
                    'favicon'        => ! empty( $favicon ) ? $favicon : null,
                    'brand_name'     => ! empty( $brand_name ) ? $brand_name : null,
                    'copyright_text' => ! empty( $copyright_text ) ? $copyright_text : null,
                ),
                'colors' => array(
                    'primary'   => $primary_color,
                    'secondary' => $secondary_color,
                ),
                'communication' => array(
                    'whatsapp'         => ! empty( $whatsapp_contact ) ? $whatsapp_contact : null,
                    'support_email'    => ! empty( $support_email ) ? $support_email : null,
                    'physical_address' => ! empty( $physical_address ) ? $physical_address : null,
                ),
                'social_networks' => array(
                    'instagram' => ! empty( $social_instagram ) ? $social_instagram : null,
                    'linkedin'  => ! empty( $social_linkedin ) ? $social_linkedin : null,
                    'facebook'  => ! empty( $social_facebook ) ? $social_facebook : null,
                    'twitter'   => ! empty( $social_twitter ) ? $social_twitter : null,
                    'youtube'   => ! empty( $social_youtube ) ? $social_youtube : null,
                ),
                'marketing' => array(
                    'google_analytics_id' => ! empty( $google_analytics_id ) ? $google_analytics_id : null,
                    'facebook_pixel_id'   => ! empty( $facebook_pixel_id ) ? $facebook_pixel_id : null,
                ),
                'headless' => array(
                    'frontend_url' => ! empty( $frontend_url ) ? $frontend_url : null,
                ),
                'scripts' => array(
                    'header' => ! empty( $header_scripts ) ? $header_scripts : null,
                    'footer' => ! empty( $footer_scripts ) ? $footer_scripts : null,
                ),
                'branding' => array(
                    'signature_text' => $signature_text,
                    'signature_url'  => $signature_url,
                    'html'           => $signature_html,
                ),
                'modules' => array(
                    'informativo' => get_option( 'w2wp_mod_informativo', '0' ) === '1',
                    'landing'     => get_option( 'w2wp_mod_landing', '0' ) === '1',
                ),
            ),
            'timestamp' => current_time( 'mysql' ),
        );
        
        // Guardar en caché por 1 hora
        $cache->cache_global_settings( $response, 3600 );

        return rest_ensure_response( $response );
    }

    public function get_debug_info( $request ) {
        global $wpdb;
        
        $webhook_url = get_option( 'w2wp_webhook_url', '' );
        $allowed_origins = get_option( 'w2wp_allowed_origins', '' );
        $frontend_url = get_option( 'w2wp_frontend_url', '' );
        
        $webhook_configured = ! empty( $webhook_url );
        $webhook_masked = $webhook_configured ? substr( $webhook_url, 0, 30 ) . '...' : null;
        
        $table_name = $wpdb->prefix . 'w2wp_deployment_logs';
        $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name;
        
        $last_deployment = null;
        if ( $table_exists ) {
            $last_log = $wpdb->get_row( 
                "SELECT * FROM $table_name ORDER BY timestamp DESC LIMIT 1",
                ARRAY_A 
            );
            if ( $last_log ) {
                $last_deployment = array(
                    'timestamp' => $last_log['timestamp'],
                    'action' => $last_log['action'],
                    'response_code' => $last_log['response_code'],
                    'success' => $last_log['response_code'] >= 200 && $last_log['response_code'] < 300,
                );
            }
        }
        
        $response = array(
            'success' => true,
            'data' => array(
                'system' => array(
                    'wordpress_version' => get_bloginfo( 'version' ) ?: '',
                    'php_version' => phpversion(),
                    'plugin_version' => W2WP_VERSION,
                    'acf_active' => class_exists( 'ACF' ),
                ),
                'modules' => array(
                    'informativo' => get_option( 'w2wp_mod_informativo', '0' ) === '1',
                    'landing' => get_option( 'w2wp_mod_landing', '0' ) === '1',
                ),
                'pages' => array(
                    'inicio' => get_option( 'w2wp_page_inicio', '0' ) === '1',
                    'sobre_nosotros' => get_option( 'w2wp_page_sobre_nosotros', '0' ) === '1',
                    'servicios' => get_option( 'w2wp_page_servicios', '0' ) === '1',
                    'blog' => get_option( 'w2wp_page_blog', '0' ) === '1',
                    'recursos' => get_option( 'w2wp_page_recursos', '0' ) === '1',
                    'contacto' => get_option( 'w2wp_page_contacto', '0' ) === '1',
                    'faq' => get_option( 'w2wp_page_faq', '0' ) === '1',
                    'legales' => get_option( 'w2wp_page_legales', '0' ) === '1',
                ),
                'deployment' => array(
                    'webhook_configured' => $webhook_configured,
                    'webhook_url_preview' => $webhook_masked,
                    'allowed_origins_count' => ! empty( $allowed_origins ) ? count( explode( "\n", $allowed_origins ) ) : 0,
                    'frontend_url' => ! empty( $frontend_url ) ? $frontend_url : null,
                    'last_deployment' => $last_deployment,
                    'logs_table_exists' => $table_exists,
                ),
                'branding' => array(
                    'signature_configured' => ! empty( get_option( 'w2wp_signature_text', '' ) ),
                    'signature_text' => get_option( 'w2wp_signature_text', 'Desarrollado por WebToWP' ),
                ),
                'api_endpoints' => array(
                    'site_info' => rest_url( 'webtowp/v1/site-info' ),
                    'settings' => rest_url( 'webtowp/v1/settings' ),
                    'debug' => rest_url( 'webtowp/v1/debug' ),
                ),
            ),
            'timestamp' => current_time( 'mysql' ),
        );

        return rest_ensure_response( $response );
    }

    public function get_page_by_slug( $request ) {
        $slug = $request->get_param( 'slug' );
        
        $args = array(
            'name'        => $slug,
            'post_type'   => 'page',
            'post_status' => 'publish',
            'numberposts' => 1,
        );
        
        $pages = get_posts( $args );
        
        if ( empty( $pages ) ) {
            return new WP_Error(
                'page_not_found',
                __( 'Página no encontrada.', 'webtowp-engine' ),
                array( 'status' => 404 )
            );
        }
        
        $page = $pages[0];
        $post_id = $page->ID;
        
        // Obtener todos los campos de ACF
        $acf_fields = array();
        if ( function_exists( 'get_fields' ) ) {
            $fields = get_fields( $post_id );
            if ( is_array( $fields ) ) {
                $acf_fields = $fields;
            }
        }
        
        $response = array(
            'success' => true,
            'data'    => array(
                'id'      => $post_id,
                'title'   => get_the_title( $post_id ),
                'slug'    => $page->post_name,
                'content' => apply_filters( 'the_content', $page->post_content ),
                'excerpt' => get_the_excerpt( $post_id ),
                'fields'  => $acf_fields,
            ),
        );
        
        return rest_ensure_response( $response );
    }

    public function get_servicios( $request ) {
        $args = array(
            'post_type'      => 'w2wp_servicios',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        );
        
        $servicios = get_posts( $args );
        $data = array();
        
        foreach ( $servicios as $servicio ) {
            $post_id = $servicio->ID;
            
            // Obtener campos de ACF específicos
            $icono = '';
            $resumen = '';
            $destacado = false;
            
            if ( function_exists( 'get_field' ) ) {
                $icono = get_field( 'icono', $post_id ) ?: '';
                $resumen = get_field( 'resumen', $post_id ) ?: '';
                $destacado = get_field( 'destacado', $post_id ) ?: false;
            }
            
            $data[] = array(
                'id'        => $post_id,
                'title'     => get_the_title( $post_id ),
                'slug'      => $servicio->post_name,
                'content'   => apply_filters( 'the_content', $servicio->post_content ),
                'excerpt'   => get_the_excerpt( $post_id ),
                'icono'     => $icono,
                'resumen'   => $resumen,
                'destacado' => $destacado,
            );
        }
        
        $response = array(
            'success' => true,
            'data'    => $data,
            'count'   => count( $data ),
        );
        
        return rest_ensure_response( $response );
    }
}
