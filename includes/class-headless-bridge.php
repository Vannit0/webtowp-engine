<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Headless_Bridge {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', array( $this, 'enable_application_passwords' ) );
        add_action( 'rest_api_init', array( $this, 'register_seo_fields_in_rest' ) );
        add_action( 'rest_api_init', array( $this, 'cleanup_rest_endpoints' ) );
        add_filter( 'rest_prepare_post', array( $this, 'cleanup_rest_response' ), 10, 3 );
        add_filter( 'rest_prepare_page', array( $this, 'cleanup_rest_response' ), 10, 3 );
        add_filter( 'rest_prepare_w2wp_servicios', array( $this, 'cleanup_rest_response' ), 10, 3 );
        add_filter( 'rest_prepare_w2wp_recursos', array( $this, 'cleanup_rest_response' ), 10, 3 );
        add_filter( 'rest_prepare_servicio_medico', array( $this, 'cleanup_rest_response' ), 10, 3 );
        add_action( 'rest_api_init', array( $this, 'configure_cors' ) );
        add_action( 'admin_bar_menu', array( $this, 'add_deploy_button' ), 100 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_deploy_scripts' ) );
        add_action( 'wp_ajax_w2wp_trigger_deploy', array( $this, 'trigger_deploy' ) );
    }

    public function enable_application_passwords() {
        add_filter( 'wp_is_application_passwords_available', '__return_true' );
    }

    public function register_seo_fields_in_rest() {
        if ( ! function_exists( 'get_field' ) ) {
            return;
        }

        $post_types = array( 'post', 'page', 'w2wp_servicios', 'w2wp_recursos', 'servicio_medico' );

        $seo_schema = array(
            'description' => 'SEO & Social Media fields',
            'type' => 'object',
            'context' => array( 'view', 'edit' ),
            'properties' => array(
                'title' => array(
                    'type' => 'string',
                    'description' => 'SEO Title',
                ),
                'description' => array(
                    'type' => 'string',
                    'description' => 'SEO Description',
                ),
                'image' => array(
                    'type' => 'object',
                    'description' => 'SEO Image (Open Graph)',
                ),
            ),
        );

        foreach ( $post_types as $post_type ) {
            register_rest_field(
                $post_type,
                'seo',
                array(
                    'get_callback' => array( $this, 'get_seo_fields' ),
                    'schema' => $seo_schema,
                )
            );

            register_rest_field(
                $post_type,
                'seo_data',
                array(
                    'get_callback' => array( $this, 'get_seo_fields' ),
                    'schema' => $seo_schema,
                )
            );
        }
    }

    public function get_seo_fields( $object ) {
        $post_id = $object['id'];
        
        $seo_data = array(
            'title' => get_field( 'seo_title', $post_id ),
            'description' => get_field( 'seo_description', $post_id ),
            'image' => get_field( 'seo_image', $post_id ),
        );

        if ( empty( $seo_data['title'] ) ) {
            $seo_data['title'] = get_the_title( $post_id );
        }

        if ( empty( $seo_data['description'] ) ) {
            $excerpt = get_the_excerpt( $post_id );
            $seo_data['description'] = $excerpt ? wp_trim_words( $excerpt, 30 ) : '';
        }

        if ( empty( $seo_data['image'] ) ) {
            $default_seo_image = get_option( 'w2wp_default_seo_image', '' );
            if ( ! empty( $default_seo_image ) ) {
                $seo_data['image'] = array(
                    'url' => $default_seo_image,
                    'alt' => get_option( 'w2wp_brand_name', get_bloginfo( 'name' ) ),
                );
            }
        }

        return $seo_data;
    }

    public function cleanup_rest_endpoints() {
        $endpoints_to_remove = array(
            '/wp/v2/users',
            '/wp/v2/comments',
            '/wp/v2/settings',
            '/wp/v2/themes',
            '/wp/v2/plugins',
            '/wp/v2/block-types',
            '/wp/v2/block-renderer',
            '/wp/v2/search',
            '/wp/v2/block-directory',
        );

        foreach ( $endpoints_to_remove as $endpoint ) {
            remove_action( 'rest_api_init', $endpoint );
        }
    }

    public function cleanup_rest_response( $response, $post, $request ) {
        $fields_to_remove = array(
            'ping_status',
            'comment_status',
            'template',
            'meta',
            'guid',
            'sticky',
            'format',
            'categories',
            'tags',
            '_links',
        );

        foreach ( $fields_to_remove as $field ) {
            if ( isset( $response->data[ $field ] ) ) {
                unset( $response->data[ $field ] );
            }
        }

        return $response;
    }

    public function configure_cors() {
        $allowed_origins = get_option( 'w2wp_allowed_origins', '' );
        
        if ( empty( $allowed_origins ) ) {
            return;
        }

        $origins = array_filter( array_map( 'trim', explode( "\n", $allowed_origins ) ) );

        remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
        
        add_filter( 'rest_pre_serve_request', function( $served, $result, $request, $server ) use ( $origins ) {
            $origin = get_http_origin();
            
            if ( $origin && in_array( $origin, $origins, true ) ) {
                header( 'Access-Control-Allow-Origin: ' . esc_url_raw( $origin ) );
                header( 'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS' );
                header( 'Access-Control-Allow-Credentials: true' );
                header( 'Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Nonce' );
                header( 'Access-Control-Max-Age: 86400' );
            }
            
            if ( $_SERVER['REQUEST_METHOD'] === 'OPTIONS' ) {
                status_header( 200 );
                exit;
            }
            
            return $served;
        }, 15, 4 );
    }

    public function add_deploy_button( $wp_admin_bar ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $args = array(
            'id'    => 'w2wp_deploy',
            'title' => '🚀 Publicar Cambios',
            'href'  => '#',
            'meta'  => array(
                'class' => 'w2wp-deploy-button',
                'title' => 'Disparar despliegue de producción',
            ),
        );

        $wp_admin_bar->add_node( $args );
    }

    public function enqueue_deploy_scripts( $hook ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        wp_enqueue_script(
            'w2wp-deploy',
            W2WP_URL . 'assets/js/deploy.js',
            array( 'jquery' ),
            W2WP_VERSION,
            true
        );

        wp_localize_script(
            'w2wp-deploy',
            'w2wpDeploy',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'w2wp_deploy_nonce' ),
            )
        );

        wp_add_inline_style( 'admin-bar', '
            #wpadminbar .w2wp-deploy-button .ab-item {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                color: white !important;
                font-weight: 600 !important;
                transition: all 0.3s ease !important;
            }
            #wpadminbar .w2wp-deploy-button:hover .ab-item {
                background: linear-gradient(135deg, #764ba2 0%, #667eea 100%) !important;
                transform: translateY(-2px);
            }
            .w2wp-deploy-notice {
                position: fixed;
                top: 50px;
                right: 20px;
                background: white;
                border-left: 4px solid #46b450;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                padding: 15px 20px;
                border-radius: 4px;
                z-index: 999999;
                animation: slideIn 0.3s ease;
            }
            .w2wp-deploy-notice.error {
                border-left-color: #dc3232;
            }
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        ' );
    }

    public function trigger_deploy() {
        check_ajax_referer( 'w2wp_deploy_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array(
                'message' => 'No tienes permisos para realizar esta acción.',
            ) );
        }

        $webhook_url = get_option( 'w2wp_webhook_url', '' );

        if ( empty( $webhook_url ) ) {
            wp_send_json_error( array(
                'message' => 'Configura la URL de Cloudflare en los ajustes (WebToWP > Despliegue & API)',
            ) );
        }

        $response = wp_remote_post( $webhook_url, array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode( array(
                'source' => 'webtowp-engine',
                'timestamp' => current_time( 'mysql' ),
                'user' => wp_get_current_user()->user_login,
            ) ),
        ) );

        if ( is_wp_error( $response ) ) {
            error_log( '[WebToWP Engine] Error al disparar webhook: ' . $response->get_error_message() );
            wp_send_json_error( array(
                'message' => 'Error al conectar con el webhook: ' . $response->get_error_message(),
            ) );
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        
        if ( $response_code >= 200 && $response_code < 300 ) {
            error_log( '[WebToWP Engine] Webhook disparado exitosamente. Código: ' . $response_code );
            wp_send_json_success( array(
                'message' => '¡Despliegue enviado exitosamente! Tu sitio se actualizará en breve.',
            ) );
        } else {
            error_log( '[WebToWP Engine] Webhook respondió con código: ' . $response_code );
            wp_send_json_error( array(
                'message' => 'El webhook respondió con código ' . $response_code . '. Verifica la configuración.',
            ) );
        }
    }
}
