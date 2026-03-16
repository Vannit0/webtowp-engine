<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Admin_Setup {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        error_log( 'W2WP_Admin_Setup: Constructor ejecutado' );
        add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 10 );
        add_action( 'admin_menu', array( $this, 'security_vault' ), 999 );
        add_action( 'admin_head', array( $this, 'hide_screen_options_and_help' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_post_w2wp_save_modules', array( $this, 'save_modules_settings' ) );
        add_action( 'admin_post_w2wp_save_deployment', array( $this, 'save_deployment_settings' ) );
        add_action( 'admin_post_w2wp_save_global', array( $this, 'save_global_settings' ) );
    }

    public function register_admin_menu() {
        error_log( 'W2WP_Admin_Setup: register_admin_menu ejecutado' );
        
        add_menu_page(
            __( 'WebToWP', 'webtowp-engine' ),
            __( 'WebToWP', 'webtowp-engine' ),
            'manage_options',
            'webtowp-engine',
            array( $this, 'render_main_page' ),
            'dashicons-admin-site-alt3',
            3
        );

        add_submenu_page(
            'webtowp-engine',
            __( 'Módulos Activos', 'webtowp-engine' ),
            __( 'Módulos Activos', 'webtowp-engine' ),
            'manage_options',
            'webtowp-active-modules',
            array( $this, 'render_modules_page' )
        );

        add_submenu_page(
            'webtowp-engine',
            __( 'Ajustes Globales', 'webtowp-engine' ),
            __( 'Ajustes Globales', 'webtowp-engine' ),
            'manage_options',
            'webtowp-global-settings',
            array( $this, 'render_settings_page' )
        );

        add_submenu_page(
            'webtowp-engine',
            __( 'Despliegue & API', 'webtowp-engine' ),
            __( 'Despliegue & API', 'webtowp-engine' ),
            'manage_options',
            'webtowp-deployment-api',
            array( $this, 'render_deployment_page' )
        );

        add_submenu_page(
            'webtowp-engine',
            __( 'Estado del Sistema', 'webtowp-engine' ),
            __( 'Estado del Sistema', 'webtowp-engine' ),
            'manage_options',
            'webtowp-system-status',
            array( $this, 'render_system_status_page' )
        );
        
        error_log( 'W2WP_Admin_Setup: Menús nativos registrados' );
    }

    public function register_settings() {
        register_setting( 'w2wp_modules_group', 'w2wp_mod_informativo' );
        register_setting( 'w2wp_modules_group', 'w2wp_mod_landing' );
        
        register_setting( 'w2wp_modules_group', 'w2wp_page_inicio' );
        register_setting( 'w2wp_modules_group', 'w2wp_page_sobre_nosotros' );
        register_setting( 'w2wp_modules_group', 'w2wp_page_servicios' );
        register_setting( 'w2wp_modules_group', 'w2wp_page_blog' );
        register_setting( 'w2wp_modules_group', 'w2wp_page_recursos' );
        register_setting( 'w2wp_modules_group', 'w2wp_page_contacto' );
        register_setting( 'w2wp_modules_group', 'w2wp_page_faq' );
        register_setting( 'w2wp_modules_group', 'w2wp_page_legales' );
        
        register_setting( 'w2wp_deployment_group', 'w2wp_webhook_url' );
        register_setting( 'w2wp_deployment_group', 'w2wp_allowed_origins' );
        register_setting( 'w2wp_deployment_group', 'w2wp_api_key' );
        
        register_setting( 'w2wp_global_group', 'w2wp_logo_principal' );
        register_setting( 'w2wp_global_group', 'w2wp_logo_contraste' );
        register_setting( 'w2wp_global_group', 'w2wp_favicon' );
        register_setting( 'w2wp_global_group', 'w2wp_brand_name' );
        register_setting( 'w2wp_global_group', 'w2wp_copyright_text' );
        register_setting( 'w2wp_global_group', 'w2wp_primary_color' );
        register_setting( 'w2wp_global_group', 'w2wp_secondary_color' );
        register_setting( 'w2wp_global_group', 'w2wp_whatsapp_contact' );
        register_setting( 'w2wp_global_group', 'w2wp_support_email' );
        register_setting( 'w2wp_global_group', 'w2wp_physical_address' );
        register_setting( 'w2wp_global_group', 'w2wp_social_instagram' );
        register_setting( 'w2wp_global_group', 'w2wp_social_linkedin' );
        register_setting( 'w2wp_global_group', 'w2wp_social_facebook' );
        register_setting( 'w2wp_global_group', 'w2wp_social_twitter' );
        register_setting( 'w2wp_global_group', 'w2wp_social_youtube' );
        register_setting( 'w2wp_global_group', 'w2wp_google_analytics_id' );
        register_setting( 'w2wp_global_group', 'w2wp_facebook_pixel_id' );
        register_setting( 'w2wp_global_group', 'w2wp_frontend_url' );
        register_setting( 'w2wp_global_group', 'w2wp_header_scripts' );
        register_setting( 'w2wp_global_group', 'w2wp_footer_scripts' );
        register_setting( 'w2wp_global_group', 'w2wp_signature_text' );
        register_setting( 'w2wp_global_group', 'w2wp_signature_url' );
        register_setting( 'w2wp_global_group', 'w2wp_default_seo_image' );
    }

    public function render_main_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin: 20px 0;">
                <h2 style="color: white; margin-top: 0;">🚀 WebToWP Engine</h2>
                <p style="font-size: 16px; margin: 0;"><?php _e( 'Motor headless para WordPress. Configura tus módulos y conecta tu frontend moderno.', 'webtowp-engine' ); ?></p>
            </div>
            <div class="card" style="padding: 20px;">
                <h3><?php _e( 'Inicio Rápido', 'webtowp-engine' ); ?></h3>
                <ol>
                    <li><?php _e( 'Ve a <strong>Módulos Activos</strong> y activa el módulo que necesites', 'webtowp-engine' ); ?></li>
                    <li><?php _e( 'Configura los <strong>Ajustes Globales</strong> de tu sitio', 'webtowp-engine' ); ?></li>
                    <li><?php _e( 'En <strong>Despliegue & API</strong>, conecta tu webhook de Cloudflare', 'webtowp-engine' ); ?></li>
                    <li><?php _e( 'Usa la API REST en <code>/wp-json/wp/v2/</code> para consumir el contenido', 'webtowp-engine' ); ?></li>
                </ol>
            </div>
        </div>
        <?php
    }

    public function render_modules_page() {
        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error( 'w2wp_messages', 'w2wp_message', __( 'Configuración guardada correctamente', 'webtowp-engine' ), 'updated' );
        }
        settings_errors( 'w2wp_messages' );

        $mod_informativo = get_option( 'w2wp_mod_informativo', '0' );
        $mod_landing = get_option( 'w2wp_mod_landing', '0' );
        
        $pages_config = array(
            'inicio' => array(
                'title' => 'Página de Inicio',
                'description' => 'Página principal del sitio'
            ),
            'sobre_nosotros' => array(
                'title' => 'Sobre Nosotros',
                'description' => 'Incluye Misión, Visión y Repeater de Equipo (Nombre, Puesto, Foto)'
            ),
            'servicios' => array(
                'title' => 'Servicios',
                'description' => 'Página de listado de servicios + CPT Servicios (Icono, Resumen, ¿Es destacado?)'
            ),
            'blog' => array(
                'title' => 'Blog',
                'description' => 'Página de archivo de entradas del blog'
            ),
            'recursos' => array(
                'title' => 'Recursos',
                'description' => 'Página de listado de recursos + CPT Recursos (Tipo: Guía/Herramienta/Ebook, Link de acceso)'
            ),
            'contacto' => array(
                'title' => 'Contacto',
                'description' => 'Incluye Teléfono, Email de Soporte, WhatsApp e Iframe de Google Maps'
            ),
            'faq' => array(
                'title' => 'FAQ',
                'description' => 'Incluye Repeater de Preguntas y Respuestas'
            ),
            'legales' => array(
                'title' => 'Legales',
                'description' => 'Página para términos legales, privacidad, etc.'
            ),
        );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php _e( 'Activa o desactiva los módulos según las necesidades de tu proyecto.', 'webtowp-engine' ); ?></p>
            
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="w2wp_save_modules">
                <?php wp_nonce_field( 'w2wp_modules_nonce', 'w2wp_modules_nonce_field' ); ?>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="w2wp_mod_informativo"><?php _e( 'Sitio Informativo Pro', 'webtowp-engine' ); ?></label>
                            </th>
                            <td>
                                <label class="w2wp-toggle-switch">
                                    <input type="checkbox" id="w2wp_mod_informativo" name="w2wp_mod_informativo" value="1" <?php checked( $mod_informativo, '1' ); ?> class="w2wp-master-toggle">
                                    <span class="w2wp-toggle-slider"></span>
                                </label>
                                <p class="description">
                                    <?php _e( 'Activa el módulo completo con Servicios, Recursos, Nosotros, Contacto y FAQ. Selecciona las páginas que deseas crear a continuación.', 'webtowp-engine' ); ?>
                                </p>
                                
                                <div id="w2wp-informativo-pages" class="w2wp-sub-toggles" style="<?php echo $mod_informativo === '1' ? '' : 'display:none;'; ?>margin-top: 20px; padding: 20px; background: #f9f9f9; border-left: 4px solid #667eea; border-radius: 4px;">
                                    <h4 style="margin-top: 0;"><?php _e( '📄 Páginas del Módulo Informativo', 'webtowp-engine' ); ?></h4>
                                    <p class="description" style="margin-bottom: 15px;">
                                        <?php _e( 'Selecciona las páginas que deseas activar. Al activar estas páginas, se crearán automáticamente con campos personalizados listos para editar.', 'webtowp-engine' ); ?>
                                    </p>
                                    
                                    <table class="widefat" style="background: white;">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;"></th>
                                                <th><?php _e( 'Página', 'webtowp-engine' ); ?></th>
                                                <th><?php _e( 'Descripción / Secciones', 'webtowp-engine' ); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ( $pages_config as $page_key => $page_data ) : 
                                                $option_name = 'w2wp_page_' . $page_key;
                                                $is_active = get_option( $option_name, '0' );
                                            ?>
                                            <tr>
                                                <td style="text-align: center;">
                                                    <label class="w2wp-toggle-switch w2wp-toggle-small">
                                                        <input type="checkbox" name="<?php echo esc_attr( $option_name ); ?>" value="1" <?php checked( $is_active, '1' ); ?>>
                                                        <span class="w2wp-toggle-slider"></span>
                                                    </label>
                                                </td>
                                                <td><strong><?php echo esc_html( $page_data['title'] ); ?></strong></td>
                                                <td><span class="description"><?php echo esc_html( $page_data['description'] ); ?></span></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    
                                    <p class="description" style="margin-top: 15px; padding: 10px; background: #fff3cd; border-left: 3px solid #ffc107; border-radius: 3px;">
                                        <strong>💡 Nota:</strong> <?php _e( 'Al activar estas páginas, se crearán automáticamente con campos personalizados listos para editar. Los CPTs (Servicios y Recursos) se activarán automáticamente con el módulo.', 'webtowp-engine' ); ?>
                                    </p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="w2wp_mod_landing"><?php _e( 'Landing Page High-Conversion', 'webtowp-engine' ); ?></label>
                            </th>
                            <td>
                                <label class="w2wp-toggle-switch">
                                    <input type="checkbox" id="w2wp_mod_landing" name="w2wp_mod_landing" value="1" <?php checked( $mod_landing, '1' ); ?>>
                                    <span class="w2wp-toggle-slider"></span>
                                </label>
                                <p class="description">
                                    <?php _e( 'Activa el módulo de Landing Page Premium con diseño avanzado y campos optimizados para conversión.', 'webtowp-engine' ); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <?php submit_button( __( 'Guardar Módulos', 'webtowp-engine' ) ); ?>
            </form>
        </div>
        
        <style>
            .w2wp-toggle-switch {
                position: relative;
                display: inline-block;
                width: 60px;
                height: 34px;
            }
            .w2wp-toggle-switch.w2wp-toggle-small {
                width: 44px;
                height: 24px;
            }
            .w2wp-toggle-switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }
            .w2wp-toggle-slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                transition: .4s;
                border-radius: 34px;
            }
            .w2wp-toggle-slider:before {
                position: absolute;
                content: "";
                height: 26px;
                width: 26px;
                left: 4px;
                bottom: 4px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }
            .w2wp-toggle-small .w2wp-toggle-slider:before {
                height: 18px;
                width: 18px;
                left: 3px;
                bottom: 3px;
            }
            input:checked + .w2wp-toggle-slider {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            input:checked + .w2wp-toggle-slider:before {
                transform: translateX(26px);
            }
            .w2wp-toggle-small input:checked + .w2wp-toggle-slider:before {
                transform: translateX(20px);
            }
            .w2wp-sub-toggles table th {
                padding: 12px;
                background: #f0f0f1;
                font-weight: 600;
            }
            .w2wp-sub-toggles table td {
                padding: 12px;
                border-bottom: 1px solid #e0e0e0;
            }
            .w2wp-sub-toggles table tbody tr:last-child td {
                border-bottom: none;
            }
        </style>
        <script>
            jQuery(document).ready(function($) {
                $('#w2wp_mod_informativo').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#w2wp-informativo-pages').slideDown(300);
                    } else {
                        $('#w2wp-informativo-pages').slideUp(300);
                    }
                });
            });
        </script>
        <?php
    }

    public function save_modules_settings() {
        if ( ! isset( $_POST['w2wp_modules_nonce_field'] ) || ! wp_verify_nonce( $_POST['w2wp_modules_nonce_field'], 'w2wp_modules_nonce' ) ) {
            wp_die( __( 'Error de seguridad', 'webtowp-engine' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'No tienes permisos suficientes', 'webtowp-engine' ) );
        }

        $mod_informativo = isset( $_POST['w2wp_mod_informativo'] ) ? '1' : '0';
        $mod_landing = isset( $_POST['w2wp_mod_landing'] ) ? '1' : '0';

        update_option( 'w2wp_mod_informativo', $mod_informativo );
        update_option( 'w2wp_mod_landing', $mod_landing );
        
        $pages = array( 'inicio', 'sobre_nosotros', 'servicios', 'blog', 'recursos', 'contacto', 'faq', 'legales' );
        foreach ( $pages as $page ) {
            $option_name = 'w2wp_page_' . $page;
            $value = isset( $_POST[$option_name] ) ? '1' : '0';
            update_option( $option_name, $value );
        }

        do_action( 'w2wp_modules_updated', $mod_informativo, $mod_landing );

        wp_redirect( add_query_arg( 'settings-updated', 'true', wp_get_referer() ) );
        exit;
    }

    public function render_settings_page() {
        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error( 'w2wp_messages', 'w2wp_message', __( 'Configuración guardada correctamente', 'webtowp-engine' ), 'updated' );
        }
        settings_errors( 'w2wp_messages' );

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
        
        $can_manage_branding = current_user_can( 'manage_options' );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php _e( 'Gestiona toda la identidad de tu sitio desde aquí. Estos valores estarán disponibles en la API para tu frontend.', 'webtowp-engine' ); ?></p>
            
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="w2wp_save_global">
                <?php wp_nonce_field( 'w2wp_global_nonce', 'w2wp_global_nonce_field' ); ?>
                
                <div class="card" style="margin-bottom: 20px; padding: 20px;">
                    <h2 style="margin-top: 0;"><?php _e( '🎨 Identidad de Marca', 'webtowp-engine' ); ?></h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_logo_principal"><?php _e( 'Logo Principal', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="w2wp_logo_principal" name="w2wp_logo_principal" value="<?php echo esc_attr( $logo_principal ); ?>" class="regular-text" readonly>
                                    <button type="button" class="button w2wp-media-upload" data-target="w2wp_logo_principal"><?php _e( 'Seleccionar Logo', 'webtowp-engine' ); ?></button>
                                    <?php if ( $logo_principal ) : ?>
                                        <button type="button" class="button w2wp-media-remove" data-target="w2wp_logo_principal"><?php _e( 'Eliminar', 'webtowp-engine' ); ?></button>
                                    <?php endif; ?>
                                    <p class="description"><?php _e( 'Logo principal de tu marca (para fondos claros)', 'webtowp-engine' ); ?></p>
                                    <?php if ( $logo_principal ) : ?>
                                        <div class="w2wp-preview" style="margin-top: 10px;">
                                            <img src="<?php echo esc_url( $logo_principal ); ?>" style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; background: white;">
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_logo_contraste"><?php _e( 'Logo de Contraste', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="w2wp_logo_contraste" name="w2wp_logo_contraste" value="<?php echo esc_attr( $logo_contraste ); ?>" class="regular-text" readonly>
                                    <button type="button" class="button w2wp-media-upload" data-target="w2wp_logo_contraste"><?php _e( 'Seleccionar Logo', 'webtowp-engine' ); ?></button>
                                    <?php if ( $logo_contraste ) : ?>
                                        <button type="button" class="button w2wp-media-remove" data-target="w2wp_logo_contraste"><?php _e( 'Eliminar', 'webtowp-engine' ); ?></button>
                                    <?php endif; ?>
                                    <p class="description"><?php _e( 'Logo alternativo para fondos oscuros', 'webtowp-engine' ); ?></p>
                                    <?php if ( $logo_contraste ) : ?>
                                        <div class="w2wp-preview" style="margin-top: 10px;">
                                            <img src="<?php echo esc_url( $logo_contraste ); ?>" style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; background: #333;">
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_favicon"><?php _e( 'Favicon', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="w2wp_favicon" name="w2wp_favicon" value="<?php echo esc_attr( $favicon ); ?>" class="regular-text" readonly>
                                    <button type="button" class="button w2wp-media-upload" data-target="w2wp_favicon"><?php _e( 'Seleccionar Favicon', 'webtowp-engine' ); ?></button>
                                    <?php if ( $favicon ) : ?>
                                        <button type="button" class="button w2wp-media-remove" data-target="w2wp_favicon"><?php _e( 'Eliminar', 'webtowp-engine' ); ?></button>
                                    <?php endif; ?>
                                    <p class="description"><?php _e( 'Icono del sitio (recomendado: 512x512px, formato PNG o ICO)', 'webtowp-engine' ); ?></p>
                                    <?php if ( $favicon ) : ?>
                                        <div class="w2wp-preview" style="margin-top: 10px;">
                                            <img src="<?php echo esc_url( $favicon ); ?>" style="max-width: 64px; height: auto; border: 1px solid #ddd; padding: 5px; background: white;">
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_brand_name"><?php _e( 'Nombre de la Marca', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="w2wp_brand_name" name="w2wp_brand_name" value="<?php echo esc_attr( $brand_name ); ?>" class="regular-text" placeholder="Mi Empresa S.L.">
                                    <p class="description"><?php _e( 'Nombre completo de tu marca o empresa (para textos)', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_copyright_text"><?php _e( 'Texto de Copyright', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="w2wp_copyright_text" name="w2wp_copyright_text" value="<?php echo esc_attr( $copyright_text ); ?>" class="large-text" placeholder="© 2026 Mi Empresa. Todos los derechos reservados.">
                                    <p class="description"><?php _e( 'Texto de copyright para el footer', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_default_seo_image"><?php _e( 'Imagen SEO por Defecto', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="w2wp_default_seo_image" name="w2wp_default_seo_image" value="<?php echo esc_attr( get_option( 'w2wp_default_seo_image', '' ) ); ?>" class="regular-text" readonly>
                                    <button type="button" class="button w2wp-media-upload" data-target="w2wp_default_seo_image"><?php _e( 'Seleccionar Imagen', 'webtowp-engine' ); ?></button>
                                    <?php if ( get_option( 'w2wp_default_seo_image', '' ) ) : ?>
                                        <button type="button" class="button w2wp-media-remove" data-target="w2wp_default_seo_image"><?php _e( 'Eliminar', 'webtowp-engine' ); ?></button>
                                    <?php endif; ?>
                                    <p class="description"><?php _e( 'Imagen de respaldo para Open Graph cuando una página no tiene imagen SEO definida (recomendado: 1200x630px)', 'webtowp-engine' ); ?></p>
                                    <?php if ( get_option( 'w2wp_default_seo_image', '' ) ) : ?>
                                        <div class="w2wp-preview" style="margin-top: 10px;">
                                            <img src="<?php echo esc_url( get_option( 'w2wp_default_seo_image', '' ) ); ?>" style="max-width: 300px; height: auto; border: 1px solid #ddd; padding: 5px; background: white;">
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="card" style="margin-bottom: 20px; padding: 20px;">
                    <h2 style="margin-top: 0;"><?php _e( '🎨 Colores y Estilo', 'webtowp-engine' ); ?></h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_primary_color"><?php _e( 'Color Primario', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="color" id="w2wp_primary_color" name="w2wp_primary_color" value="<?php echo esc_attr( $primary_color ); ?>">
                                    <input type="text" value="<?php echo esc_attr( $primary_color ); ?>" class="regular-text" readonly style="margin-left: 10px;">
                                    <p class="description"><?php _e( 'Color principal de tu marca', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_secondary_color"><?php _e( 'Color Secundario', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="color" id="w2wp_secondary_color" name="w2wp_secondary_color" value="<?php echo esc_attr( $secondary_color ); ?>">
                                    <input type="text" value="<?php echo esc_attr( $secondary_color ); ?>" class="regular-text" readonly style="margin-left: 10px;">
                                    <p class="description"><?php _e( 'Color secundario o de acento', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="card" style="margin-bottom: 20px; padding: 20px;">
                    <h2 style="margin-top: 0;"><?php _e( '📞 Comunicación', 'webtowp-engine' ); ?></h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_whatsapp_contact"><?php _e( 'WhatsApp', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="w2wp_whatsapp_contact" name="w2wp_whatsapp_contact" value="<?php echo esc_attr( $whatsapp_contact ); ?>" class="regular-text" placeholder="+34612345678">
                                    <p class="description"><?php _e( 'Número de WhatsApp con código de país (ej: +34612345678)', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_support_email"><?php _e( 'Email de Soporte', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="email" id="w2wp_support_email" name="w2wp_support_email" value="<?php echo esc_attr( $support_email ); ?>" class="regular-text" placeholder="soporte@ejemplo.com">
                                    <p class="description"><?php _e( 'Email de contacto para soporte', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_physical_address"><?php _e( 'Dirección Física', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <textarea id="w2wp_physical_address" name="w2wp_physical_address" rows="3" class="large-text" placeholder="Calle Ejemplo 123, 28001 Madrid, España"><?php echo esc_textarea( $physical_address ); ?></textarea>
                                    <p class="description"><?php _e( 'Dirección física de tu empresa u oficina', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="card" style="margin-bottom: 20px; padding: 20px;">
                    <h2 style="margin-top: 0;"><?php _e( '🌐 Redes Sociales', 'webtowp-engine' ); ?></h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_social_instagram"><?php _e( 'Instagram', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="url" id="w2wp_social_instagram" name="w2wp_social_instagram" value="<?php echo esc_attr( $social_instagram ); ?>" class="regular-text" placeholder="https://instagram.com/tuempresa">
                                    <p class="description"><?php _e( 'URL completa de tu perfil de Instagram', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_social_linkedin"><?php _e( 'LinkedIn', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="url" id="w2wp_social_linkedin" name="w2wp_social_linkedin" value="<?php echo esc_attr( $social_linkedin ); ?>" class="regular-text" placeholder="https://linkedin.com/company/tuempresa">
                                    <p class="description"><?php _e( 'URL completa de tu perfil de LinkedIn', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_social_facebook"><?php _e( 'Facebook', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="url" id="w2wp_social_facebook" name="w2wp_social_facebook" value="<?php echo esc_attr( $social_facebook ); ?>" class="regular-text" placeholder="https://facebook.com/tuempresa">
                                    <p class="description"><?php _e( 'URL completa de tu página de Facebook', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_social_twitter"><?php _e( 'X (Twitter)', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="url" id="w2wp_social_twitter" name="w2wp_social_twitter" value="<?php echo esc_attr( $social_twitter ); ?>" class="regular-text" placeholder="https://x.com/tuempresa">
                                    <p class="description"><?php _e( 'URL completa de tu perfil de X (Twitter)', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_social_youtube"><?php _e( 'YouTube', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="url" id="w2wp_social_youtube" name="w2wp_social_youtube" value="<?php echo esc_attr( $social_youtube ); ?>" class="regular-text" placeholder="https://youtube.com/@tuempresa">
                                    <p class="description"><?php _e( 'URL completa de tu canal de YouTube', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="card" style="margin-bottom: 20px; padding: 20px;">
                    <h2 style="margin-top: 0;"><?php _e( '📊 Marketing y Scripts', 'webtowp-engine' ); ?></h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_google_analytics_id"><?php _e( 'Google Analytics ID', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="w2wp_google_analytics_id" name="w2wp_google_analytics_id" value="<?php echo esc_attr( $google_analytics_id ); ?>" class="regular-text" placeholder="G-XXXXXXXXXX o UA-XXXXXXXXX-X">
                                    <p class="description"><?php _e( 'ID de Google Analytics (GA4: G-XXXXXXXXXX o Universal: UA-XXXXXXXXX-X)', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_facebook_pixel_id"><?php _e( 'Facebook Pixel ID', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="w2wp_facebook_pixel_id" name="w2wp_facebook_pixel_id" value="<?php echo esc_attr( $facebook_pixel_id ); ?>" class="regular-text" placeholder="123456789012345">
                                    <p class="description"><?php _e( 'ID del Pixel de Facebook para seguimiento de conversiones', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="card" style="margin-bottom: 20px; padding: 20px;">
                    <h2 style="margin-top: 0;"><?php _e( '🚀 Configuración Headless', 'webtowp-engine' ); ?></h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_frontend_url"><?php _e( 'URL del Frontend (Astro)', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="url" id="w2wp_frontend_url" name="w2wp_frontend_url" value="<?php echo esc_attr( $frontend_url ); ?>" class="large-text" placeholder="https://mi-sitio.pages.dev">
                                    <p class="description"><?php _e( 'URL de tu frontend en Astro/Next.js para vincular botones de previsualización', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="card" style="margin-bottom: 20px; padding: 20px;">
                    <h2 style="margin-top: 0;"><?php _e( '💻 Scripts Avanzados', 'webtowp-engine' ); ?></h2>
                    <p class="description" style="margin-top: 0;"><?php _e( 'Añade scripts personalizados que se inyectarán en tu frontend. Útil para tracking, chatbots, etc.', 'webtowp-engine' ); ?></p>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_header_scripts"><?php _e( 'Scripts de Cabecera (Header)', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <textarea id="w2wp_header_scripts" name="w2wp_header_scripts" rows="8" class="large-text code" placeholder="<script>\n  // Tu código aquí\n</script>"><?php echo esc_textarea( $header_scripts ); ?></textarea>
                                    <p class="description"><?php _e( 'Scripts que se insertarán en el <head> de tu sitio (ej: Google Tag Manager, Meta Pixel)', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_footer_scripts"><?php _e( 'Scripts de Pie de Página (Footer)', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <textarea id="w2wp_footer_scripts" name="w2wp_footer_scripts" rows="8" class="large-text code" placeholder="<script>\n  // Tu código aquí\n</script>"><?php echo esc_textarea( $footer_scripts ); ?></textarea>
                                    <p class="description"><?php _e( 'Scripts que se insertarán antes del cierre de </body> (ej: chatbots, analytics)', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <?php if ( $can_manage_branding ) : ?>
                <div class="card" style="margin-bottom: 20px; padding: 20px; border-left: 4px solid #f0ad4e;">
                    <h2 style="margin-top: 0;"><?php _e( '🏷️ Firma de Marca Blanca', 'webtowp-engine' ); ?></h2>
                    <p class="description" style="margin-top: 0; color: #856404; background: #fff3cd; padding: 10px; border-radius: 4px;">
                        <strong>⚠️ Sección Exclusiva para Administradores:</strong> <?php _e( 'Esta firma aparecerá en la API y en el footer del frontend. Solo administradores pueden editar estos campos.', 'webtowp-engine' ); ?>
                    </p>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_signature_text"><?php _e( 'Texto de la Firma', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="w2wp_signature_text" name="w2wp_signature_text" value="<?php echo esc_attr( $signature_text ); ?>" class="regular-text" placeholder="Desarrollado por WebToWP">
                                    <p class="description"><?php _e( 'Texto que aparecerá en la firma de marca blanca', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="w2wp_signature_url"><?php _e( 'Enlace de la Firma', 'webtowp-engine' ); ?></label>
                                </th>
                                <td>
                                    <input type="url" id="w2wp_signature_url" name="w2wp_signature_url" value="<?php echo esc_attr( $signature_url ); ?>" class="regular-text" placeholder="https://webtowp.com">
                                    <p class="description"><?php _e( 'URL de tu agencia o sitio web', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <?php _e( 'Vista Previa', 'webtowp-engine' ); ?>
                                </th>
                                <td>
                                    <div style="padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                                        <a href="<?php echo esc_url( $signature_url ); ?>" target="_blank" rel="noopener" style="text-decoration: none; color: #667eea;">
                                            <?php echo esc_html( $signature_text ); ?>
                                        </a>
                                    </div>
                                    <p class="description" style="margin-top: 10px;"><?php _e( 'Así se verá la firma en el frontend', 'webtowp-engine' ); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                
                <?php submit_button( __( 'Guardar Ajustes Globales', 'webtowp-engine' ) ); ?>
            </form>
        </div>
        
        <script>
            jQuery(document).ready(function($) {
                var mediaUploader;
                
                $('.w2wp-media-upload').on('click', function(e) {
                    e.preventDefault();
                    var button = $(this);
                    var targetId = button.data('target');
                    
                    if (mediaUploader) {
                        mediaUploader.open();
                        return;
                    }
                    
                    mediaUploader = wp.media({
                        title: '<?php _e( 'Seleccionar Imagen', 'webtowp-engine' ); ?>',
                        button: {
                            text: '<?php _e( 'Usar esta imagen', 'webtowp-engine' ); ?>'
                        },
                        multiple: false
                    });
                    
                    mediaUploader.on('select', function() {
                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                        $('#' + targetId).val(attachment.url);
                        location.reload();
                    });
                    
                    mediaUploader.open();
                });
                
                $('.w2wp-media-remove').on('click', function(e) {
                    e.preventDefault();
                    var button = $(this);
                    var targetId = button.data('target');
                    $('#' + targetId).val('');
                    button.closest('td').find('.w2wp-preview').remove();
                    button.remove();
                });
                
                $('input[type="color"]').on('change', function() {
                    $(this).next('input[type="text"]').val($(this).val());
                });
            });
        </script>
        <?php
        wp_enqueue_media();
    }

    public function save_global_settings() {
        if ( ! isset( $_POST['w2wp_global_nonce_field'] ) || ! wp_verify_nonce( $_POST['w2wp_global_nonce_field'], 'w2wp_global_nonce' ) ) {
            wp_die( __( 'Error de seguridad', 'webtowp-engine' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'No tienes permisos suficientes', 'webtowp-engine' ) );
        }

        $logo_principal = isset( $_POST['w2wp_logo_principal'] ) ? esc_url_raw( $_POST['w2wp_logo_principal'] ) : '';
        $logo_contraste = isset( $_POST['w2wp_logo_contraste'] ) ? esc_url_raw( $_POST['w2wp_logo_contraste'] ) : '';
        $favicon = isset( $_POST['w2wp_favicon'] ) ? esc_url_raw( $_POST['w2wp_favicon'] ) : '';
        $brand_name = isset( $_POST['w2wp_brand_name'] ) ? sanitize_text_field( $_POST['w2wp_brand_name'] ) : '';
        $copyright_text = isset( $_POST['w2wp_copyright_text'] ) ? sanitize_text_field( $_POST['w2wp_copyright_text'] ) : '';
        $primary_color = isset( $_POST['w2wp_primary_color'] ) ? sanitize_hex_color( $_POST['w2wp_primary_color'] ) : '#667eea';
        $secondary_color = isset( $_POST['w2wp_secondary_color'] ) ? sanitize_hex_color( $_POST['w2wp_secondary_color'] ) : '#764ba2';
        $whatsapp_contact = isset( $_POST['w2wp_whatsapp_contact'] ) ? sanitize_text_field( $_POST['w2wp_whatsapp_contact'] ) : '';
        $support_email = isset( $_POST['w2wp_support_email'] ) ? sanitize_email( $_POST['w2wp_support_email'] ) : '';
        $physical_address = isset( $_POST['w2wp_physical_address'] ) ? sanitize_textarea_field( $_POST['w2wp_physical_address'] ) : '';
        $social_instagram = isset( $_POST['w2wp_social_instagram'] ) ? esc_url_raw( $_POST['w2wp_social_instagram'] ) : '';
        $social_linkedin = isset( $_POST['w2wp_social_linkedin'] ) ? esc_url_raw( $_POST['w2wp_social_linkedin'] ) : '';
        $social_facebook = isset( $_POST['w2wp_social_facebook'] ) ? esc_url_raw( $_POST['w2wp_social_facebook'] ) : '';
        $social_twitter = isset( $_POST['w2wp_social_twitter'] ) ? esc_url_raw( $_POST['w2wp_social_twitter'] ) : '';
        $social_youtube = isset( $_POST['w2wp_social_youtube'] ) ? esc_url_raw( $_POST['w2wp_social_youtube'] ) : '';
        $google_analytics_id = isset( $_POST['w2wp_google_analytics_id'] ) ? sanitize_text_field( $_POST['w2wp_google_analytics_id'] ) : '';
        $facebook_pixel_id = isset( $_POST['w2wp_facebook_pixel_id'] ) ? sanitize_text_field( $_POST['w2wp_facebook_pixel_id'] ) : '';
        $frontend_url = isset( $_POST['w2wp_frontend_url'] ) ? esc_url_raw( $_POST['w2wp_frontend_url'] ) : '';
        $header_scripts = isset( $_POST['w2wp_header_scripts'] ) ? wp_kses_post( $_POST['w2wp_header_scripts'] ) : '';
        $footer_scripts = isset( $_POST['w2wp_footer_scripts'] ) ? wp_kses_post( $_POST['w2wp_footer_scripts'] ) : '';
        $default_seo_image = isset( $_POST['w2wp_default_seo_image'] ) ? esc_url_raw( $_POST['w2wp_default_seo_image'] ) : '';

        update_option( 'w2wp_logo_principal', $logo_principal );
        update_option( 'w2wp_logo_contraste', $logo_contraste );
        update_option( 'w2wp_favicon', $favicon );
        update_option( 'w2wp_brand_name', $brand_name );
        update_option( 'w2wp_copyright_text', $copyright_text );
        update_option( 'w2wp_primary_color', $primary_color );
        update_option( 'w2wp_secondary_color', $secondary_color );
        update_option( 'w2wp_whatsapp_contact', $whatsapp_contact );
        update_option( 'w2wp_support_email', $support_email );
        update_option( 'w2wp_physical_address', $physical_address );
        update_option( 'w2wp_social_instagram', $social_instagram );
        update_option( 'w2wp_social_linkedin', $social_linkedin );
        update_option( 'w2wp_social_facebook', $social_facebook );
        update_option( 'w2wp_social_twitter', $social_twitter );
        update_option( 'w2wp_social_youtube', $social_youtube );
        update_option( 'w2wp_google_analytics_id', $google_analytics_id );
        update_option( 'w2wp_facebook_pixel_id', $facebook_pixel_id );
        update_option( 'w2wp_frontend_url', $frontend_url );
        update_option( 'w2wp_header_scripts', $header_scripts );
        update_option( 'w2wp_footer_scripts', $footer_scripts );
        update_option( 'w2wp_default_seo_image', $default_seo_image );
        
        if ( current_user_can( 'manage_options' ) ) {
            $signature_text = isset( $_POST['w2wp_signature_text'] ) ? sanitize_text_field( $_POST['w2wp_signature_text'] ) : 'Desarrollado por WebToWP';
            $signature_url = isset( $_POST['w2wp_signature_url'] ) ? esc_url_raw( $_POST['w2wp_signature_url'] ) : 'https://webtowp.com';
            
            update_option( 'w2wp_signature_text', $signature_text );
            update_option( 'w2wp_signature_url', $signature_url );
        }

        wp_redirect( add_query_arg( 'settings-updated', 'true', wp_get_referer() ) );
        exit;
    }

    public function render_deployment_page() {
        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error( 'w2wp_messages', 'w2wp_message', __( 'Configuración guardada correctamente', 'webtowp-engine' ), 'updated' );
        }
        settings_errors( 'w2wp_messages' );

        $webhook_url = get_option( 'w2wp_webhook_url', '' );
        $allowed_origins = get_option( 'w2wp_allowed_origins', '' );
        $api_key = get_option( 'w2wp_api_key', '' );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php _e( 'Configura el webhook de Cloudflare Pages, los dominios permitidos para CORS y la clave de API.', 'webtowp-engine' ); ?></p>
            
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="w2wp_save_deployment">
                <?php wp_nonce_field( 'w2wp_deployment_nonce', 'w2wp_deployment_nonce_field' ); ?>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="w2wp_api_key"><?php _e( 'Clave de API de WebToWP', 'webtowp-engine' ); ?></label>
                            </th>
                            <td>
                                <input type="text" id="w2wp_api_key" name="w2wp_api_key" value="<?php echo esc_attr( $api_key ); ?>" class="large-text" readonly style="background: #f0f0f1; font-family: monospace;">
                                <button type="button" id="w2wp_generate_api_key" class="button button-secondary" style="margin-left: 10px;">
                                    <?php _e( 'Generar Nueva Clave', 'webtowp-engine' ); ?>
                                </button>
                                <button type="button" id="w2wp_copy_api_key" class="button button-secondary" style="margin-left: 5px;" <?php echo empty( $api_key ) ? 'disabled' : ''; ?>>
                                    <?php _e( 'Copiar', 'webtowp-engine' ); ?>
                                </button>
                                <p class="description">
                                    <?php _e( 'Clave de seguridad para proteger los endpoints personalizados. Incluye esta clave en el header <code>X-WebToWP-Key</code> de tus peticiones.', 'webtowp-engine' ); ?>
                                </p>
                                <?php if ( ! empty( $api_key ) ) : ?>
                                    <p class="description" style="margin-top: 10px; padding: 10px; background: #fff3cd; border-left: 3px solid #ffc107; border-radius: 3px;">
                                        <strong>⚠️ Importante:</strong> <?php _e( 'Guarda esta clave en un lugar seguro. Se requiere para acceder a los endpoints /settings, /debug y /site-info.', 'webtowp-engine' ); ?>
                                    </p>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="w2wp_webhook_url"><?php _e( 'URL de Deploy Hook de Cloudflare', 'webtowp-engine' ); ?></label>
                            </th>
                            <td>
                                <input type="url" id="w2wp_webhook_url" name="w2wp_webhook_url" value="<?php echo esc_attr( $webhook_url ); ?>" class="large-text" placeholder="https://api.cloudflare.com/client/v4/pages/webhooks/deploy_hooks/...">
                                <p class="description"><?php _e( 'URL del Deploy Hook de Cloudflare Pages para disparar el despliegue automático', 'webtowp-engine' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="w2wp_allowed_origins"><?php _e( 'Dominios Permitidos (CORS)', 'webtowp-engine' ); ?></label>
                            </th>
                            <td>
                                <textarea id="w2wp_allowed_origins" name="w2wp_allowed_origins" rows="5" class="large-text" placeholder="https://midominio.com&#10;https://preview.midominio.com"><?php echo esc_textarea( $allowed_origins ); ?></textarea>
                                <p class="description"><?php _e( 'Dominios permitidos para acceder a la API (uno por línea). Ejemplo: https://midominio.com', 'webtowp-engine' ); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <?php submit_button( __( 'Guardar Configuración', 'webtowp-engine' ) ); ?>
            </form>
            
            <div class="card" style="margin-top: 20px; padding: 20px;">
                <h3><?php _e( '📡 Endpoints de la API REST', 'webtowp-engine' ); ?></h3>
                <p><?php _e( 'Usa estos endpoints para consumir el contenido desde tu frontend:', 'webtowp-engine' ); ?></p>
                <ul>
                    <li><code><?php echo esc_url( rest_url( 'wp/v2/pages' ) ); ?></code> - Páginas</li>
                    <li><code><?php echo esc_url( rest_url( 'wp/v2/posts' ) ); ?></code> - Entradas</li>
                    <li><code><?php echo esc_url( rest_url( 'webtowp/v1/settings' ) ); ?></code> - Ajustes Globales (requiere API Key)</li>
                    <li><code><?php echo esc_url( rest_url( 'webtowp/v1/site-info' ) ); ?></code> - Información del Sitio (requiere API Key)</li>
                    <li><code><?php echo esc_url( rest_url( 'webtowp/v1/debug' ) ); ?></code> - Debug (requiere API Key)</li>
                </ul>
            </div>
        </div>
        
        <script>
            jQuery(document).ready(function($) {
                $('#w2wp_generate_api_key').on('click', function() {
                    if (!confirm('<?php _e( '¿Estás seguro? Esto generará una nueva clave y la anterior dejará de funcionar.', 'webtowp-engine' ); ?>')) {
                        return;
                    }
                    
                    var newKey = 'w2wp_' + Array.from({length: 32}, () => 
                        'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'.charAt(Math.floor(Math.random() * 62))
                    ).join('');
                    
                    $('#w2wp_api_key').val(newKey);
                    $('#w2wp_copy_api_key').prop('disabled', false);
                    
                    alert('<?php _e( 'Nueva clave generada. No olvides guardar la configuración.', 'webtowp-engine' ); ?>');
                });
                
                $('#w2wp_copy_api_key').on('click', function() {
                    var apiKey = $('#w2wp_api_key').val();
                    if (!apiKey) return;
                    
                    var tempInput = $('<input>');
                    $('body').append(tempInput);
                    tempInput.val(apiKey).select();
                    document.execCommand('copy');
                    tempInput.remove();
                    
                    $(this).text('<?php _e( '¡Copiado!', 'webtowp-engine' ); ?>');
                    var btn = $(this);
                    setTimeout(function() {
                        btn.text('<?php _e( 'Copiar', 'webtowp-engine' ); ?>');
                    }, 2000);
                });
            });
        </script>
        <?php
    }

    public function save_deployment_settings() {
        if ( ! isset( $_POST['w2wp_deployment_nonce_field'] ) || ! wp_verify_nonce( $_POST['w2wp_deployment_nonce_field'], 'w2wp_deployment_nonce' ) ) {
            wp_die( __( 'Error de seguridad', 'webtowp-engine' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'No tienes permisos suficientes', 'webtowp-engine' ) );
        }

        $webhook_url = isset( $_POST['w2wp_webhook_url'] ) ? esc_url_raw( $_POST['w2wp_webhook_url'] ) : '';
        $allowed_origins = isset( $_POST['w2wp_allowed_origins'] ) ? sanitize_textarea_field( $_POST['w2wp_allowed_origins'] ) : '';
        $api_key = isset( $_POST['w2wp_api_key'] ) ? sanitize_text_field( $_POST['w2wp_api_key'] ) : '';

        update_option( 'w2wp_webhook_url', $webhook_url );
        update_option( 'w2wp_allowed_origins', $allowed_origins );
        update_option( 'w2wp_api_key', $api_key );

        wp_redirect( add_query_arg( 'settings-updated', 'true', wp_get_referer() ) );
        exit;
    }

    public function security_vault() {
        if ( current_user_can( 'manage_options' ) ) {
            return;
        }

        remove_menu_page( 'plugins.php' );
        remove_menu_page( 'themes.php' );
        remove_menu_page( 'options-general.php' );
        remove_menu_page( 'tools.php' );
        remove_menu_page( 'edit-comments.php' );
        remove_menu_page( 'edit.php?post_type=acf-field-group' );
    }

    public function render_system_status_page() {
        global $wpdb;
        
        $acf_active = class_exists( 'ACF' );
        $webhook_url = get_option( 'w2wp_webhook_url', '' );
        
        $table_name = $wpdb->prefix . 'w2wp_deployment_logs';
        $recent_logs = $wpdb->get_results( 
            "SELECT * FROM $table_name ORDER BY timestamp DESC LIMIT 5",
            ARRAY_A 
        );
        
        if ( isset( $_POST['test_connection'] ) && check_admin_referer( 'w2wp_test_connection' ) ) {
            $test_result = $this->test_webhook_connection( $webhook_url );
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php _e( 'Diagnóstico y estado del sistema WebToWP Engine.', 'webtowp-engine' ); ?></p>
            
            <div class="card" style="margin-bottom: 20px; padding: 20px;">
                <h2 style="margin-top: 0;"><?php _e( '🔌 Estado de Plugins', 'webtowp-engine' ); ?></h2>
                <table class="widefat" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th><?php _e( 'Plugin', 'webtowp-engine' ); ?></th>
                            <th><?php _e( 'Estado', 'webtowp-engine' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Advanced Custom Fields (ACF)</strong></td>
                            <td>
                                <?php if ( $acf_active ) : ?>
                                    <span style="color: #46b450; font-weight: bold;">✓ Activo</span>
                                <?php else : ?>
                                    <span style="color: #dc3232; font-weight: bold;">✗ No Instalado</span>
                                    <p class="description" style="margin: 5px 0 0 0;">
                                        <?php _e( 'ACF es requerido para los campos personalizados. Por favor, instálalo desde el repositorio de WordPress.', 'webtowp-engine' ); ?>
                                    </p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card" style="margin-bottom: 20px; padding: 20px;">
                <h2 style="margin-top: 0;"><?php _e( '📡 Test de Conexión al Deploy Hook', 'webtowp-engine' ); ?></h2>
                <?php if ( empty( $webhook_url ) ) : ?>
                    <p style="color: #dc3232;">
                        <strong><?php _e( 'No hay URL de Deploy Hook configurada.', 'webtowp-engine' ); ?></strong>
                        <br>
                        <?php _e( 'Por favor, configura la URL en la pestaña "Despliegue & API".', 'webtowp-engine' ); ?>
                    </p>
                <?php else : ?>
                    <p><strong><?php _e( 'URL Configurada:', 'webtowp-engine' ); ?></strong> <code><?php echo esc_html( $webhook_url ); ?></code></p>
                    
                    <form method="post" style="margin-top: 15px;">
                        <?php wp_nonce_field( 'w2wp_test_connection' ); ?>
                        <button type="submit" name="test_connection" class="button button-primary">
                            <?php _e( 'Probar Conexión', 'webtowp-engine' ); ?>
                        </button>
                    </form>
                    
                    <?php if ( isset( $test_result ) ) : ?>
                        <div style="margin-top: 15px; padding: 15px; background: <?php echo $test_result['success'] ? '#d4edda' : '#f8d7da'; ?>; border: 1px solid <?php echo $test_result['success'] ? '#c3e6cb' : '#f5c6cb'; ?>; border-radius: 4px;">
                            <strong><?php echo $test_result['success'] ? '✓' : '✗'; ?> <?php echo esc_html( $test_result['message'] ); ?></strong>
                            <?php if ( isset( $test_result['code'] ) ) : ?>
                                <br><span style="font-size: 12px;">Código de respuesta: <?php echo esc_html( $test_result['code'] ); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <div class="card" style="margin-bottom: 20px; padding: 20px;">
                <h2 style="margin-top: 0;"><?php _e( '📋 Últimos 5 Intentos de Despliegue', 'webtowp-engine' ); ?></h2>
                <?php if ( empty( $recent_logs ) ) : ?>
                    <p style="color: #666;"><?php _e( 'No hay registros de despliegue todavía.', 'webtowp-engine' ); ?></p>
                <?php else : ?>
                    <table class="widefat" style="margin-top: 15px;">
                        <thead>
                            <tr>
                                <th><?php _e( 'Fecha/Hora', 'webtowp-engine' ); ?></th>
                                <th><?php _e( 'Acción', 'webtowp-engine' ); ?></th>
                                <th><?php _e( 'Código', 'webtowp-engine' ); ?></th>
                                <th><?php _e( 'Mensaje', 'webtowp-engine' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $recent_logs as $log ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $log['timestamp'] ); ?></td>
                                    <td><?php echo esc_html( $log['action'] ); ?></td>
                                    <td>
                                        <?php if ( $log['response_code'] ) : ?>
                                            <span style="color: <?php echo $log['response_code'] >= 200 && $log['response_code'] < 300 ? '#46b450' : '#dc3232'; ?>;">
                                                <?php echo esc_html( $log['response_code'] ); ?>
                                            </span>
                                        <?php else : ?>
                                            <span style="color: #999;">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size: 12px;"><?php echo esc_html( wp_trim_words( $log['response_message'], 10 ) ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <div class="card" style="margin-bottom: 20px; padding: 20px;">
                <h2 style="margin-top: 0;"><?php _e( 'ℹ️ Información del Sistema', 'webtowp-engine' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><?php _e( 'Versión de WordPress', 'webtowp-engine' ); ?></th>
                        <td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Versión de PHP', 'webtowp-engine' ); ?></th>
                        <td><?php echo esc_html( phpversion() ); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Versión del Plugin', 'webtowp-engine' ); ?></th>
                        <td><?php echo esc_html( W2WP_VERSION ); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e( 'URL de la API', 'webtowp-engine' ); ?></th>
                        <td><code><?php echo esc_url( rest_url( 'webtowp/v1/' ) ); ?></code></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }

    private function test_webhook_connection( $webhook_url ) {
        if ( empty( $webhook_url ) ) {
            return array(
                'success' => false,
                'message' => __( 'No hay URL configurada', 'webtowp-engine' )
            );
        }

        $response = wp_remote_post( $webhook_url, array(
            'timeout' => 15,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            $this->log_deployment( 'Test de Conexión', 0, $response->get_error_message() );
            return array(
                'success' => false,
                'message' => __( 'Error: ', 'webtowp-engine' ) . $response->get_error_message()
            );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        
        $this->log_deployment( 'Test de Conexión', $code, $body );

        if ( $code >= 200 && $code < 300 ) {
            return array(
                'success' => true,
                'message' => __( 'Conexión exitosa', 'webtowp-engine' ),
                'code' => $code
            );
        } else {
            return array(
                'success' => false,
                'message' => __( 'Conexión fallida', 'webtowp-engine' ),
                'code' => $code
            );
        }
    }

    private function log_deployment( $action, $response_code = null, $response_message = '' ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'w2wp_deployment_logs';
        
        $wpdb->insert(
            $table_name,
            array(
                'action' => $action,
                'response_code' => $response_code,
                'response_message' => substr( $response_message, 0, 500 ),
                'user_id' => get_current_user_id(),
            ),
            array( '%s', '%d', '%s', '%d' )
        );
    }

    public function hide_screen_options_and_help() {
        if ( current_user_can( 'manage_options' ) ) {
            return;
        }

        echo '<style>
            #screen-meta-links,
            #contextual-help-link-wrap,
            #screen-options-link-wrap {
                display: none !important;
            }
        </style>';
    }
}
