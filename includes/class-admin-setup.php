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

        add_submenu_page(
            'webtowp-engine',
            __( 'Logs de Deployment', 'webtowp-engine' ),
            __( 'Logs de Deployment', 'webtowp-engine' ),
            'manage_options',
            'webtowp-deployment-logs',
            array( $this, 'render_deployment_logs_page' )
        );

        add_submenu_page(
            'webtowp-engine',
            __( 'Backup & Restauración', 'webtowp-engine' ),
            __( 'Backup & Restauración', 'webtowp-engine' ),
            'manage_options',
            'webtowp-backup',
            array( $this, 'render_backup_page' )
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
        $dashboard = W2WP_Dashboard::get_instance();
        $stats = $dashboard->get_system_stats();
        $health = $dashboard->get_health_status();
        $activity = $dashboard->get_recent_activity();
        $tasks = $dashboard->get_pending_tasks();
        $quick_links = $dashboard->get_quick_links();
        
        ?>
        <div class="wrap webtowp-admin-page">
            <!-- Header -->
            <div class="webtowp-header">
                <h1>🚀 <?php _e( 'WebToWP Engine Dashboard', 'webtowp-engine' ); ?></h1>
                <p><?php _e( 'Motor headless para WordPress. Gestiona tu sitio desde un panel moderno y potente.', 'webtowp-engine' ); ?></p>
            </div>

            <!-- Health Status -->
            <div class="webtowp-card webtowp-fade-in">
                <div class="webtowp-card-header">
                    <h2 class="webtowp-card-title">
                        <span class="webtowp-card-icon">💚</span>
                        <?php _e( 'Estado de Salud del Sistema', 'webtowp-engine' ); ?>
                    </h2>
                    <span class="webtowp-badge webtowp-badge-<?php echo $health['status'] === 'excellent' ? 'success' : 'warning'; ?>">
                        <?php echo esc_html( $health['score'] ); ?>%
                    </span>
                </div>
                <div class="webtowp-card-body">
                    <div class="webtowp-progress">
                        <div class="webtowp-progress-bar" style="width: <?php echo esc_attr( $health['score'] ); ?>%"></div>
                    </div>
                    <?php if ( ! empty( $health['issues'] ) ) : ?>
                        <div style="margin-top: 15px;">
                            <?php foreach ( $health['issues'] as $issue ) : ?>
                                <div class="webtowp-alert webtowp-alert-<?php echo esc_attr( $issue['type'] ); ?>" style="margin-bottom: 10px;">
                                    <span class="webtowp-alert-icon">
                                        <?php echo $issue['type'] === 'error' ? '❌' : ($issue['type'] === 'warning' ? '⚠️' : 'ℹ️'); ?>
                                    </span>
                                    <div class="webtowp-alert-content">
                                        <p class="webtowp-alert-message"><?php echo esc_html( $issue['message'] ); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="webtowp-grid webtowp-grid-4">
                <div class="webtowp-stat-card success">
                    <h3 class="webtowp-stat-label"><?php _e( 'Deployments (30d)', 'webtowp-engine' ); ?></h3>
                    <p class="webtowp-stat-value"><?php echo esc_html( $stats['deployments']['total'] ); ?></p>
                    <div class="webtowp-stat-change positive">
                        ↗ <?php echo esc_html( $stats['deployments']['success_rate'] ); ?>% <?php _e( 'éxito', 'webtowp-engine' ); ?>
                    </div>
                </div>

                <div class="webtowp-stat-card info">
                    <h3 class="webtowp-stat-label"><?php _e( 'Items en Caché', 'webtowp-engine' ); ?></h3>
                    <p class="webtowp-stat-value"><?php echo esc_html( $stats['cache']['count'] ); ?></p>
                    <div class="webtowp-stat-change">
                        📦 <?php echo esc_html( size_format( $stats['cache']['size'] ) ); ?>
                    </div>
                </div>

                <div class="webtowp-stat-card">
                    <h3 class="webtowp-stat-label"><?php _e( 'Módulos Activos', 'webtowp-engine' ); ?></h3>
                    <p class="webtowp-stat-value"><?php echo esc_html( count( $stats['modules'] ) ); ?></p>
                    <div class="webtowp-stat-change">
                        🔧 <?php echo esc_html( implode( ', ', $stats['modules'] ) ?: __( 'Ninguno', 'webtowp-engine' ) ); ?>
                    </div>
                </div>

                <div class="webtowp-stat-card">
                    <h3 class="webtowp-stat-label"><?php _e( 'Contenido Total', 'webtowp-engine' ); ?></h3>
                    <p class="webtowp-stat-value"><?php echo esc_html( array_sum( $stats['posts'] ) ); ?></p>
                    <div class="webtowp-stat-change">
                        📝 <?php printf( __( '%d posts, %d páginas', 'webtowp-engine' ), $stats['posts']['posts'], $stats['posts']['pages'] ); ?>
                    </div>
                </div>
            </div>

            <div class="webtowp-grid webtowp-grid-2">
                <!-- Pending Tasks -->
                <?php if ( ! empty( $tasks ) ) : ?>
                <div class="webtowp-card">
                    <div class="webtowp-card-header">
                        <h2 class="webtowp-card-title">
                            <span class="webtowp-card-icon">✅</span>
                            <?php _e( 'Tareas Pendientes', 'webtowp-engine' ); ?>
                        </h2>
                        <span class="webtowp-badge webtowp-badge-warning"><?php echo count( $tasks ); ?></span>
                    </div>
                    <div class="webtowp-card-body">
                        <?php foreach ( $tasks as $task ) : ?>
                            <div style="padding: 15px; background: #f8fafc; border-radius: 6px; margin-bottom: 10px; border-left: 3px solid <?php echo $task['priority'] === 'high' ? '#dc3232' : ($task['priority'] === 'medium' ? '#ffb900' : '#0073aa'); ?>;">
                                <h4 style="margin: 0 0 5px 0; font-size: 14px;"><?php echo esc_html( $task['title'] ); ?></h4>
                                <p style="margin: 0 0 10px 0; font-size: 13px; color: #64748b;"><?php echo esc_html( $task['description'] ); ?></p>
                                <a href="<?php echo esc_url( $task['action_url'] ); ?>" class="webtowp-button webtowp-button-primary" style="font-size: 12px; padding: 6px 12px;">
                                    <?php echo esc_html( $task['action_text'] ); ?> →
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Recent Activity -->
                <div class="webtowp-card">
                    <div class="webtowp-card-header">
                        <h2 class="webtowp-card-title">
                            <span class="webtowp-card-icon">📊</span>
                            <?php _e( 'Actividad Reciente', 'webtowp-engine' ); ?>
                        </h2>
                    </div>
                    <div class="webtowp-card-body">
                        <?php if ( ! empty( $activity ) ) : ?>
                            <?php foreach ( $activity as $item ) : ?>
                                <div style="padding: 12px 0; border-bottom: 1px solid #e2e8f0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <span class="webtowp-badge webtowp-badge-<?php echo $item['status'] === 'success' ? 'success' : 'error'; ?>">
                                                <?php echo esc_html( $item['type'] ); ?>
                                            </span>
                                            <p style="margin: 5px 0 0 0; font-size: 13px; color: #64748b;">
                                                <?php echo esc_html( wp_trim_words( $item['message'], 10 ) ); ?>
                                            </p>
                                        </div>
                                        <span style="font-size: 12px; color: #94a3b8;">
                                            <?php echo esc_html( human_time_diff( strtotime( $item['timestamp'] ), current_time( 'timestamp' ) ) ); ?> ago
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p style="text-align: center; color: #94a3b8; padding: 20px 0;">
                                <?php _e( 'No hay actividad reciente', 'webtowp-engine' ); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="webtowp-card">
                <div class="webtowp-card-header">
                    <h2 class="webtowp-card-title">
                        <span class="webtowp-card-icon">⚡</span>
                        <?php _e( 'Accesos Rápidos', 'webtowp-engine' ); ?>
                    </h2>
                </div>
                <div class="webtowp-card-body">
                    <div class="webtowp-grid webtowp-grid-3">
                        <?php foreach ( $quick_links as $link ) : ?>
                            <a href="<?php echo esc_url( $link['url'] ); ?>" style="text-decoration: none; color: inherit;">
                                <div style="padding: 20px; background: #f8fafc; border-radius: 8px; transition: all 0.3s ease; border: 2px solid transparent;">
                                    <div style="font-size: 32px; margin-bottom: 10px;"><?php echo $link['icon']; ?></div>
                                    <h4 style="margin: 0 0 5px 0; font-size: 16px; color: #1e293b;"><?php echo esc_html( $link['title'] ); ?></h4>
                                    <p style="margin: 0; font-size: 13px; color: #64748b;"><?php echo esc_html( $link['description'] ); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- System Info -->
            <div class="webtowp-card" style="background: #f0f6fc; border-left: 4px solid #0073aa;">
                <div class="webtowp-card-header">
                    <h2 class="webtowp-card-title">
                        <span class="webtowp-card-icon">ℹ️</span>
                        <?php _e( 'Información del Sistema', 'webtowp-engine' ); ?>
                    </h2>
                </div>
                <div class="webtowp-card-body">
                    <div class="webtowp-grid webtowp-grid-3">
                        <div>
                            <strong><?php _e( 'WordPress:', 'webtowp-engine' ); ?></strong>
                            <p style="margin: 5px 0 0 0;"><?php echo esc_html( $stats['wp_version'] ); ?></p>
                        </div>
                        <div>
                            <strong><?php _e( 'PHP:', 'webtowp-engine' ); ?></strong>
                            <p style="margin: 5px 0 0 0;"><?php echo esc_html( $stats['php_version'] ); ?></p>
                        </div>
                        <div>
                            <strong><?php _e( 'Plugin:', 'webtowp-engine' ); ?></strong>
                            <p style="margin: 5px 0 0 0;"><?php echo esc_html( $stats['plugin_version'] ); ?></p>
                        </div>
                    </div>
                </div>
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

        $notice_manager = W2WP_Notice_Manager::get_instance();
        $errors = array();

        // Validar y sanitizar URLs de imágenes
        $logo_principal = isset( $_POST['w2wp_logo_principal'] ) ? W2WP_Validator::sanitize_url( $_POST['w2wp_logo_principal'] ) : '';
        $logo_contraste = isset( $_POST['w2wp_logo_contraste'] ) ? W2WP_Validator::sanitize_url( $_POST['w2wp_logo_contraste'] ) : '';
        $favicon = isset( $_POST['w2wp_favicon'] ) ? W2WP_Validator::sanitize_url( $_POST['w2wp_favicon'] ) : '';
        $default_seo_image = isset( $_POST['w2wp_default_seo_image'] ) ? W2WP_Validator::sanitize_url( $_POST['w2wp_default_seo_image'] ) : '';
        
        // Validar y sanitizar textos
        $brand_name = isset( $_POST['w2wp_brand_name'] ) ? W2WP_Validator::sanitize_text( $_POST['w2wp_brand_name'] ) : '';
        $copyright_text = isset( $_POST['w2wp_copyright_text'] ) ? W2WP_Validator::sanitize_text( $_POST['w2wp_copyright_text'] ) : '';
        
        // Validar y sanitizar colores
        $primary_color = isset( $_POST['w2wp_primary_color'] ) ? W2WP_Validator::sanitize_hex_color( $_POST['w2wp_primary_color'] ) : '#667eea';
        $secondary_color = isset( $_POST['w2wp_secondary_color'] ) ? W2WP_Validator::sanitize_hex_color( $_POST['w2wp_secondary_color'] ) : '#764ba2';
        
        if ( empty( $primary_color ) ) {
            $primary_color = '#667eea';
            $errors['primary_color'][] = __( 'El color primario no es válido, se usó el valor por defecto.', 'webtowp-engine' );
        }
        if ( empty( $secondary_color ) ) {
            $secondary_color = '#764ba2';
            $errors['secondary_color'][] = __( 'El color secundario no es válido, se usó el valor por defecto.', 'webtowp-engine' );
        }
        
        // Validar y sanitizar WhatsApp
        $whatsapp_contact = isset( $_POST['w2wp_whatsapp_contact'] ) ? W2WP_Validator::sanitize_phone( $_POST['w2wp_whatsapp_contact'] ) : '';
        if ( ! empty( $whatsapp_contact ) && ! W2WP_Validator::is_valid_phone( $whatsapp_contact ) ) {
            $errors['whatsapp_contact'][] = __( 'El número de WhatsApp no tiene un formato válido.', 'webtowp-engine' );
        }
        
        // Validar y sanitizar email
        $support_email = isset( $_POST['w2wp_support_email'] ) ? W2WP_Validator::sanitize_email( $_POST['w2wp_support_email'] ) : '';
        if ( ! empty( $support_email ) && ! W2WP_Validator::is_valid_email( $support_email ) ) {
            $errors['support_email'][] = __( 'El email de soporte no es válido.', 'webtowp-engine' );
        }
        
        // Validar y sanitizar dirección física
        $physical_address = isset( $_POST['w2wp_physical_address'] ) ? W2WP_Validator::sanitize_textarea( $_POST['w2wp_physical_address'] ) : '';
        
        // Validar y sanitizar URLs de redes sociales
        $social_instagram = isset( $_POST['w2wp_social_instagram'] ) ? W2WP_Validator::sanitize_url( $_POST['w2wp_social_instagram'] ) : '';
        $social_linkedin = isset( $_POST['w2wp_social_linkedin'] ) ? W2WP_Validator::sanitize_url( $_POST['w2wp_social_linkedin'] ) : '';
        $social_facebook = isset( $_POST['w2wp_social_facebook'] ) ? W2WP_Validator::sanitize_url( $_POST['w2wp_social_facebook'] ) : '';
        $social_twitter = isset( $_POST['w2wp_social_twitter'] ) ? W2WP_Validator::sanitize_url( $_POST['w2wp_social_twitter'] ) : '';
        $social_youtube = isset( $_POST['w2wp_social_youtube'] ) ? W2WP_Validator::sanitize_url( $_POST['w2wp_social_youtube'] ) : '';
        
        // Validar y sanitizar IDs de tracking
        $google_analytics_id = isset( $_POST['w2wp_google_analytics_id'] ) ? W2WP_Validator::sanitize_text( $_POST['w2wp_google_analytics_id'] ) : '';
        $facebook_pixel_id = isset( $_POST['w2wp_facebook_pixel_id'] ) ? W2WP_Validator::sanitize_text( $_POST['w2wp_facebook_pixel_id'] ) : '';
        
        // Validar y sanitizar frontend URL
        $frontend_url = isset( $_POST['w2wp_frontend_url'] ) ? W2WP_Validator::sanitize_url( $_POST['w2wp_frontend_url'] ) : '';
        if ( ! empty( $frontend_url ) && ! filter_var( $frontend_url, FILTER_VALIDATE_URL ) ) {
            $errors['frontend_url'][] = __( 'La URL del frontend no es válida.', 'webtowp-engine' );
        }
        
        // Sanitizar scripts (permitir solo tags seguros)
        $header_scripts = isset( $_POST['w2wp_header_scripts'] ) ? W2WP_Validator::sanitize_html( $_POST['w2wp_header_scripts'], array( 'script' => array( 'src' => array(), 'type' => array(), 'async' => array(), 'defer' => array() ) ) ) : '';
        $footer_scripts = isset( $_POST['w2wp_footer_scripts'] ) ? W2WP_Validator::sanitize_html( $_POST['w2wp_footer_scripts'], array( 'script' => array( 'src' => array(), 'type' => array(), 'async' => array(), 'defer' => array() ) ) ) : '';

        // Validar y sanitizar firma (solo para administradores)
        if ( current_user_can( 'manage_options' ) ) {
            $signature_text = isset( $_POST['w2wp_signature_text'] ) ? W2WP_Validator::sanitize_text( $_POST['w2wp_signature_text'] ) : 'Desarrollado por WebToWP';
            $signature_url = isset( $_POST['w2wp_signature_url'] ) ? W2WP_Validator::sanitize_url( $_POST['w2wp_signature_url'] ) : 'https://webtowp.com';
        }

        // Si hay errores críticos, mostrarlos
        if ( ! empty( $errors ) ) {
            // Filtrar solo errores críticos (no warnings de colores por defecto)
            $critical_errors = array_filter( $errors, function( $field_errors, $field ) {
                return ! in_array( $field, array( 'primary_color', 'secondary_color' ) );
            }, ARRAY_FILTER_USE_BOTH );
            
            if ( ! empty( $critical_errors ) ) {
                $notice_manager->add_validation_errors( $critical_errors );
                wp_redirect( wp_get_referer() );
                exit;
            }
        }

        // Guardar todas las opciones
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
            update_option( 'w2wp_signature_text', $signature_text );
            update_option( 'w2wp_signature_url', $signature_url );
        }

        $notice_manager->add_success( __( 'Configuración global guardada correctamente.', 'webtowp-engine' ) );
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

        $notice_manager = W2WP_Notice_Manager::get_instance();
        $errors = array();

        // Validar y sanitizar webhook URL
        $webhook_url = isset( $_POST['w2wp_webhook_url'] ) ? W2WP_Validator::sanitize_url( $_POST['w2wp_webhook_url'] ) : '';
        if ( ! empty( $webhook_url ) && ! filter_var( $webhook_url, FILTER_VALIDATE_URL ) ) {
            $errors['webhook_url'][] = __( 'La URL del webhook no es válida.', 'webtowp-engine' );
        }

        // Validar y sanitizar allowed origins
        $allowed_origins = isset( $_POST['w2wp_allowed_origins'] ) ? W2WP_Validator::sanitize_textarea( $_POST['w2wp_allowed_origins'] ) : '';
        if ( ! empty( $allowed_origins ) ) {
            $origins = explode( "\n", $allowed_origins );
            foreach ( $origins as $origin ) {
                $origin = trim( $origin );
                if ( ! empty( $origin ) && ! filter_var( $origin, FILTER_VALIDATE_URL ) ) {
                    $errors['allowed_origins'][] = sprintf( __( 'El origen "%s" no es una URL válida.', 'webtowp-engine' ), esc_html( $origin ) );
                }
            }
        }

        // Validar y sanitizar API key
        $api_key = isset( $_POST['w2wp_api_key'] ) ? W2WP_Validator::sanitize_api_key( $_POST['w2wp_api_key'] ) : '';
        if ( ! empty( $api_key ) && ! W2WP_Validator::is_valid_api_key( $api_key ) ) {
            $errors['api_key'][] = __( 'La API Key debe tener al menos 16 caracteres alfanuméricos.', 'webtowp-engine' );
        }

        // Si hay errores, mostrarlos y no guardar
        if ( ! empty( $errors ) ) {
            $notice_manager->add_validation_errors( $errors );
            wp_redirect( wp_get_referer() );
            exit;
        }

        // Guardar opciones
        update_option( 'w2wp_webhook_url', $webhook_url );
        update_option( 'w2wp_allowed_origins', $allowed_origins );
        update_option( 'w2wp_api_key', $api_key );

        $notice_manager->add_success( __( 'Configuración de deployment guardada correctamente.', 'webtowp-engine' ) );
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

    public function render_deployment_logs_page() {
        $logger = W2WP_Deployment_Logger::get_instance();
        
        // Manejar acciones
        if ( isset( $_GET['action'] ) && isset( $_GET['_wpnonce'] ) ) {
            if ( $_GET['action'] === 'clear_logs' && wp_verify_nonce( $_GET['_wpnonce'], 'w2wp_clear_logs' ) ) {
                $logger->clear_all_logs();
                $notice_manager = W2WP_Notice_Manager::get_instance();
                $notice_manager->add_success( __( 'Todos los logs han sido eliminados.', 'webtowp-engine' ) );
                wp_redirect( admin_url( 'admin.php?page=webtowp-deployment-logs' ) );
                exit;
            }
        }
        
        // Paginación
        $page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        $per_page = 20;
        $offset = ( $page - 1 ) * $per_page;
        
        // Filtros
        $status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
        
        $args = array(
            'limit' => $per_page,
            'offset' => $offset,
        );
        
        if ( ! empty( $status_filter ) ) {
            $args['status'] = $status_filter;
        }
        
        $logs = $logger->get_logs( $args );
        $total_logs = $logger->count_logs( $args );
        $total_pages = ceil( $total_logs / $per_page );
        
        // Estadísticas
        $stats = $logger->get_stats( 7 );
        $last_deployment = $logger->get_last_deployment();
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <!-- Estadísticas -->
            <div class="w2wp-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
                <div class="w2wp-stat-card" style="background: #fff; padding: 20px; border-left: 4px solid #46b450; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;"><?php _e( 'Total (7 días)', 'webtowp-engine' ); ?></h3>
                    <p style="margin: 0; font-size: 32px; font-weight: bold; color: #333;"><?php echo esc_html( $stats['total'] ); ?></p>
                </div>
                
                <div class="w2wp-stat-card" style="background: #fff; padding: 20px; border-left: 4px solid #46b450; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;"><?php _e( 'Exitosos', 'webtowp-engine' ); ?></h3>
                    <p style="margin: 0; font-size: 32px; font-weight: bold; color: #46b450;"><?php echo esc_html( $stats['success'] ); ?></p>
                </div>
                
                <div class="w2wp-stat-card" style="background: #fff; padding: 20px; border-left: 4px solid #dc3232; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;"><?php _e( 'Errores', 'webtowp-engine' ); ?></h3>
                    <p style="margin: 0; font-size: 32px; font-weight: bold; color: #dc3232;"><?php echo esc_html( $stats['errors'] ); ?></p>
                </div>
                
                <div class="w2wp-stat-card" style="background: #fff; padding: 20px; border-left: 4px solid #0073aa; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;"><?php _e( 'Tasa de Éxito', 'webtowp-engine' ); ?></h3>
                    <p style="margin: 0; font-size: 32px; font-weight: bold; color: #0073aa;"><?php echo esc_html( $stats['success_rate'] ); ?>%</p>
                </div>
            </div>
            
            <?php if ( $last_deployment ) : ?>
            <div class="notice notice-info" style="margin: 20px 0;">
                <p>
                    <strong><?php _e( 'Último deployment:', 'webtowp-engine' ); ?></strong>
                    <?php echo esc_html( $last_deployment->timestamp ); ?> -
                    <span style="color: <?php echo $last_deployment->status === 'success' ? '#46b450' : '#dc3232'; ?>">
                        <?php echo esc_html( ucfirst( $last_deployment->status ) ); ?>
                    </span>
                </p>
            </div>
            <?php endif; ?>
            
            <!-- Filtros y acciones -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin: 20px 0;">
                <form method="get" action="">
                    <input type="hidden" name="page" value="webtowp-deployment-logs">
                    <select name="status" onchange="this.form.submit()">
                        <option value=""><?php _e( 'Todos los estados', 'webtowp-engine' ); ?></option>
                        <option value="success" <?php selected( $status_filter, 'success' ); ?>><?php _e( 'Exitosos', 'webtowp-engine' ); ?></option>
                        <option value="error" <?php selected( $status_filter, 'error' ); ?>><?php _e( 'Errores', 'webtowp-engine' ); ?></option>
                    </select>
                </form>
                
                <div>
                    <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'clear_logs' ), 'w2wp_clear_logs' ) ); ?>" 
                       class="button" 
                       onclick="return confirm('<?php esc_attr_e( '¿Estás seguro de que quieres eliminar todos los logs?', 'webtowp-engine' ); ?>');">
                        <?php _e( 'Limpiar Logs', 'webtowp-engine' ); ?>
                    </a>
                </div>
            </div>
            
            <!-- Tabla de logs -->
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 50px;"><?php _e( 'ID', 'webtowp-engine' ); ?></th>
                        <th style="width: 150px;"><?php _e( 'Fecha', 'webtowp-engine' ); ?></th>
                        <th><?php _e( 'Acción', 'webtowp-engine' ); ?></th>
                        <th style="width: 100px;"><?php _e( 'Estado', 'webtowp-engine' ); ?></th>
                        <th style="width: 80px;"><?php _e( 'Código', 'webtowp-engine' ); ?></th>
                        <th><?php _e( 'Mensaje', 'webtowp-engine' ); ?></th>
                        <th style="width: 100px;"><?php _e( 'Usuario', 'webtowp-engine' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( ! empty( $logs ) ) : ?>
                        <?php foreach ( $logs as $log ) : ?>
                            <?php
                            $user = get_userdata( $log->user_id );
                            $user_name = $user ? $user->user_login : __( 'Desconocido', 'webtowp-engine' );
                            $status_color = $log->status === 'success' ? '#46b450' : '#dc3232';
                            ?>
                            <tr>
                                <td><?php echo esc_html( $log->id ); ?></td>
                                <td><?php echo esc_html( $log->timestamp ); ?></td>
                                <td><?php echo esc_html( $log->action ); ?></td>
                                <td>
                                    <span style="color: <?php echo esc_attr( $status_color ); ?>; font-weight: bold;">
                                        <?php echo esc_html( ucfirst( $log->status ) ); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html( $log->response_code ); ?></td>
                                <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo esc_attr( $log->response_message ); ?>">
                                    <?php echo esc_html( $log->response_message ); ?>
                                </td>
                                <td><?php echo esc_html( $user_name ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px;">
                                <?php _e( 'No hay logs de deployment registrados.', 'webtowp-engine' ); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- Paginación -->
            <?php if ( $total_pages > 1 ) : ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php
                    echo paginate_links( array(
                        'base' => add_query_arg( 'paged', '%#%' ),
                        'format' => '',
                        'prev_text' => __( '&laquo;' ),
                        'next_text' => __( '&raquo;' ),
                        'total' => $total_pages,
                        'current' => $page,
                    ) );
                    ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function render_backup_page() {
        $backup_manager = W2WP_Backup_Manager::get_instance();
        $auto_backups = $backup_manager->get_auto_backups();
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
                <!-- Exportar Configuración -->
                <div class="postbox" style="padding: 20px;">
                    <h2 style="margin-top: 0;">📤 <?php _e( 'Exportar Configuración', 'webtowp-engine' ); ?></h2>
                    <p><?php _e( 'Descarga un archivo JSON con toda la configuración del plugin.', 'webtowp-engine' ); ?></p>
                    
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <?php wp_nonce_field( 'w2wp_export_settings' ); ?>
                        <input type="hidden" name="action" value="w2wp_export_settings">
                        
                        <p>
                            <button type="submit" class="button button-primary button-large">
                                📥 <?php _e( 'Exportar Todo', 'webtowp-engine' ); ?>
                            </button>
                        </p>
                    </form>
                    
                    <hr>
                    
                    <h3><?php _e( 'Exportaciones Parciales', 'webtowp-engine' ); ?></h3>
                    <p style="font-size: 13px; color: #666;">
                        <?php _e( 'Exporta solo partes específicas de la configuración.', 'webtowp-engine' ); ?>
                    </p>
                    
                    <p>
                        <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'w2wp_export_modules' ) ), 'w2wp_export_modules' ) ); ?>" 
                           class="button">
                            <?php _e( 'Solo Módulos', 'webtowp-engine' ); ?>
                        </a>
                        
                        <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'w2wp_export_global' ) ), 'w2wp_export_global' ) ); ?>" 
                           class="button">
                            <?php _e( 'Solo Ajustes Globales', 'webtowp-engine' ); ?>
                        </a>
                    </p>
                </div>
                
                <!-- Importar Configuración -->
                <div class="postbox" style="padding: 20px;">
                    <h2 style="margin-top: 0;">📥 <?php _e( 'Importar Configuración', 'webtowp-engine' ); ?></h2>
                    <p><?php _e( 'Sube un archivo JSON para restaurar la configuración.', 'webtowp-engine' ); ?></p>
                    
                    <div class="notice notice-warning inline" style="margin: 15px 0;">
                        <p>
                            <strong>⚠️ <?php _e( 'Advertencia:', 'webtowp-engine' ); ?></strong>
                            <?php _e( 'Esta acción sobrescribirá la configuración actual. Se recomienda hacer un backup antes.', 'webtowp-engine' ); ?>
                        </p>
                    </div>
                    
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
                        <?php wp_nonce_field( 'w2wp_import_settings' ); ?>
                        <input type="hidden" name="action" value="w2wp_import_settings">
                        
                        <p>
                            <input type="file" name="import_file" accept=".json" required style="width: 100%;">
                        </p>
                        
                        <p>
                            <button type="submit" class="button button-primary button-large">
                                📤 <?php _e( 'Importar Configuración', 'webtowp-engine' ); ?>
                            </button>
                        </p>
                    </form>
                </div>
            </div>
            
            <!-- Backups Automáticos -->
            <div class="postbox" style="padding: 20px; margin-top: 20px;">
                <h2 style="margin-top: 0;">🔄 <?php _e( 'Backups Automáticos', 'webtowp-engine' ); ?></h2>
                <p><?php _e( 'El sistema crea backups automáticos antes de actualizaciones importantes.', 'webtowp-engine' ); ?></p>
                
                <?php if ( ! empty( $auto_backups ) ) : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e( 'Archivo', 'webtowp-engine' ); ?></th>
                                <th style="width: 150px;"><?php _e( 'Fecha', 'webtowp-engine' ); ?></th>
                                <th style="width: 100px;"><?php _e( 'Tamaño', 'webtowp-engine' ); ?></th>
                                <th style="width: 150px;"><?php _e( 'Acciones', 'webtowp-engine' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $auto_backups as $backup ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $backup['filename'] ); ?></td>
                                    <td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $backup['date'] ) ); ?></td>
                                    <td><?php echo esc_html( size_format( $backup['size'] ) ); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'w2wp_download_backup', 'file' => $backup['filename'] ) ), 'w2wp_download_backup' ) ); ?>" 
                                           class="button button-small">
                                            <?php _e( 'Descargar', 'webtowp-engine' ); ?>
                                        </a>
                                        
                                        <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'w2wp_restore_backup', 'file' => $backup['filename'] ) ), 'w2wp_restore_backup' ) ); ?>" 
                                           class="button button-small"
                                           onclick="return confirm('<?php esc_attr_e( '¿Estás seguro de que quieres restaurar este backup?', 'webtowp-engine' ); ?>');">
                                            <?php _e( 'Restaurar', 'webtowp-engine' ); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p style="text-align: center; padding: 40px; color: #666;">
                        <?php _e( 'No hay backups automáticos disponibles.', 'webtowp-engine' ); ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <!-- Información -->
            <div class="postbox" style="padding: 20px; margin-top: 20px; background: #f0f6fc; border-left: 4px solid #0073aa;">
                <h3 style="margin-top: 0;">ℹ️ <?php _e( 'Información sobre Backups', 'webtowp-engine' ); ?></h3>
                <ul style="margin: 0; padding-left: 20px;">
                    <li><?php _e( 'Los backups incluyen toda la configuración del plugin excepto datos sensibles (API keys, webhooks).', 'webtowp-engine' ); ?></li>
                    <li><?php _e( 'Los backups automáticos se crean antes de actualizaciones y se mantienen los últimos 10.', 'webtowp-engine' ); ?></li>
                    <li><?php _e( 'Puedes exportar e importar configuraciones entre diferentes instalaciones de WordPress.', 'webtowp-engine' ); ?></li>
                    <li><?php _e( 'Se recomienda hacer backups manuales antes de cambios importantes.', 'webtowp-engine' ); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }
}
