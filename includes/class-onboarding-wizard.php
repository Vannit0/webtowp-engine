<?php
/**
 * Onboarding Wizard Class
 * 
 * Wizard de configuración inicial para nuevos usuarios.
 *
 * @package WebToWP_Engine
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Onboarding_Wizard {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'register_wizard_page' ) );
        add_action( 'admin_init', array( $this, 'check_onboarding_status' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_wizard_assets' ) );
        add_action( 'admin_post_w2wp_complete_onboarding', array( $this, 'complete_onboarding' ) );
        add_action( 'admin_post_w2wp_skip_onboarding', array( $this, 'skip_onboarding' ) );
    }

    /**
     * Registra la página del wizard (oculta del menú)
     */
    public function register_wizard_page() {
        add_submenu_page(
            null, // Parent slug null = página oculta
            __( 'Configuración Inicial', 'webtowp-engine' ),
            __( 'Configuración Inicial', 'webtowp-engine' ),
            'manage_options',
            'webtowp-onboarding',
            array( $this, 'render_wizard_page' )
        );
    }

    /**
     * Verifica si el usuario necesita ver el onboarding
     */
    public function check_onboarding_status() {
        // Solo en páginas del plugin
        $screen = get_current_screen();
        if ( ! $screen || ! isset( $screen->id ) || ! is_string( $screen->id ) || strpos( $screen->id, 'webtowp' ) === false ) {
            return;
        }

        // No mostrar en la página del wizard
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'webtowp-onboarding' ) {
            return;
        }

        // Verificar si ya completó el onboarding
        $completed = get_option( 'w2wp_onboarding_completed', false );
        if ( $completed ) {
            return;
        }

        // Verificar si es la primera vez
        $activation_time = get_option( 'w2wp_activation_timestamp', 0 );
        $current_time = current_time( 'timestamp' );
        
        // Si se activó hace menos de 1 hora, mostrar onboarding
        if ( ( $current_time - $activation_time ) < HOUR_IN_SECONDS ) {
            wp_redirect( admin_url( 'admin.php?page=webtowp-onboarding' ) );
            exit;
        }
    }

    /**
     * Enqueue wizard assets
     */
    public function enqueue_wizard_assets( $hook ) {
        if ( 'admin_page_webtowp-onboarding' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'w2wp-wizard',
            W2WP_ASSETS_URL . 'css/wizard.css',
            array( 'w2wp-admin-styles' ),
            W2WP_VERSION
        );

        wp_enqueue_script(
            'w2wp-wizard',
            W2WP_ASSETS_URL . 'js/wizard.js',
            array( 'jquery' ),
            W2WP_VERSION,
            true
        );

        wp_localize_script( 'w2wp-wizard', 'w2wpWizard', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'w2wp_wizard_nonce' ),
        ) );
    }

    /**
     * Renderiza la página del wizard
     */
    public function render_wizard_page() {
        $current_step = isset( $_GET['step'] ) ? intval( $_GET['step'] ) : 1;
        $total_steps = 5;

        ?>
        <div class="wrap webtowp-wizard-page">
            <div class="webtowp-wizard-container">
                <!-- Header -->
                <div class="webtowp-wizard-header">
                    <h1>🚀 <?php _e( 'Bienvenido a WebToWP Engine', 'webtowp-engine' ); ?></h1>
                    <p><?php _e( 'Configuremos tu sitio en pocos pasos', 'webtowp-engine' ); ?></p>
                </div>

                <!-- Progress -->
                <div class="webtowp-wizard-progress">
                    <div class="webtowp-wizard-progress-bar" style="width: <?php echo ( $current_step / $total_steps ) * 100; ?>%"></div>
                    <div class="webtowp-wizard-progress-steps">
                        <?php for ( $i = 1; $i <= $total_steps; $i++ ) : ?>
                            <div class="webtowp-wizard-progress-step <?php echo $i <= $current_step ? 'active' : ''; ?> <?php echo $i < $current_step ? 'completed' : ''; ?>">
                                <span class="step-number"><?php echo $i; ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Content -->
                <div class="webtowp-wizard-content">
                    <?php $this->render_step( $current_step ); ?>
                </div>

                <!-- Navigation -->
                <div class="webtowp-wizard-navigation">
                    <?php if ( $current_step > 1 ) : ?>
                        <a href="<?php echo esc_url( add_query_arg( 'step', $current_step - 1 ) ); ?>" class="webtowp-button webtowp-button-secondary">
                            ← <?php _e( 'Anterior', 'webtowp-engine' ); ?>
                        </a>
                    <?php endif; ?>

                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: inline;">
                        <input type="hidden" name="action" value="w2wp_skip_onboarding">
                        <?php wp_nonce_field( 'w2wp_skip_onboarding' ); ?>
                        <button type="submit" class="webtowp-wizard-skip">
                            <?php _e( 'Omitir configuración', 'webtowp-engine' ); ?>
                        </button>
                    </form>

                    <?php if ( $current_step < $total_steps ) : ?>
                        <a href="<?php echo esc_url( add_query_arg( 'step', $current_step + 1 ) ); ?>" class="webtowp-button webtowp-button-primary">
                            <?php _e( 'Siguiente', 'webtowp-engine' ); ?> →
                        </a>
                    <?php else : ?>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: inline;">
                            <input type="hidden" name="action" value="w2wp_complete_onboarding">
                            <?php wp_nonce_field( 'w2wp_complete_onboarding' ); ?>
                            <button type="submit" class="webtowp-button webtowp-button-primary">
                                ✅ <?php _e( 'Finalizar Configuración', 'webtowp-engine' ); ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Renderiza un paso específico del wizard
     */
    private function render_step( $step ) {
        switch ( $step ) {
            case 1:
                $this->render_step_welcome();
                break;
            case 2:
                $this->render_step_modules();
                break;
            case 3:
                $this->render_step_branding();
                break;
            case 4:
                $this->render_step_deployment();
                break;
            case 5:
                $this->render_step_complete();
                break;
        }
    }

    /**
     * Paso 1: Bienvenida
     */
    private function render_step_welcome() {
        ?>
        <div class="webtowp-wizard-step">
            <div class="webtowp-wizard-icon">🎉</div>
            <h2><?php _e( '¡Bienvenido a WebToWP Engine!', 'webtowp-engine' ); ?></h2>
            <p class="lead"><?php _e( 'Tu motor headless para WordPress está listo para despegar.', 'webtowp-engine' ); ?></p>
            
            <div class="webtowp-wizard-features">
                <div class="feature-card">
                    <span class="feature-icon">🎨</span>
                    <h3><?php _e( 'Personalización Total', 'webtowp-engine' ); ?></h3>
                    <p><?php _e( 'Configura colores, logos y branding de tu sitio', 'webtowp-engine' ); ?></p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">🚀</span>
                    <h3><?php _e( 'Deployments Automáticos', 'webtowp-engine' ); ?></h3>
                    <p><?php _e( 'Conecta con Cloudflare y despliega con un clic', 'webtowp-engine' ); ?></p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">📡</span>
                    <h3><?php _e( 'API REST Potente', 'webtowp-engine' ); ?></h3>
                    <p><?php _e( 'Consume tu contenido desde cualquier frontend', 'webtowp-engine' ); ?></p>
                </div>
            </div>

            <div class="webtowp-wizard-info">
                <p><strong><?php _e( 'Este wizard te ayudará a:', 'webtowp-engine' ); ?></strong></p>
                <ul>
                    <li>✅ <?php _e( 'Activar los módulos que necesitas', 'webtowp-engine' ); ?></li>
                    <li>✅ <?php _e( 'Configurar tu identidad de marca', 'webtowp-engine' ); ?></li>
                    <li>✅ <?php _e( 'Conectar tu sistema de deployment', 'webtowp-engine' ); ?></li>
                    <li>✅ <?php _e( 'Generar tu API Key', 'webtowp-engine' ); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Paso 2: Selección de módulos
     */
    private function render_step_modules() {
        ?>
        <div class="webtowp-wizard-step">
            <div class="webtowp-wizard-icon">🔧</div>
            <h2><?php _e( 'Selecciona tus Módulos', 'webtowp-engine' ); ?></h2>
            <p class="lead"><?php _e( 'Elige qué funcionalidades necesitas para tu proyecto.', 'webtowp-engine' ); ?></p>
            
            <div class="webtowp-wizard-modules">
                <div class="module-card">
                    <div class="module-header">
                        <h3>📄 <?php _e( 'Sitio Informativo Pro', 'webtowp-engine' ); ?></h3>
                        <label class="webtowp-toggle">
                            <input type="checkbox" name="w2wp_mod_informativo" value="1">
                            <span class="webtowp-toggle-slider"></span>
                        </label>
                    </div>
                    <p><?php _e( 'Incluye: Servicios, Recursos, Sobre Nosotros, Contacto, FAQ y más.', 'webtowp-engine' ); ?></p>
                    <ul class="module-features">
                        <li>✓ Custom Post Types</li>
                        <li>✓ Páginas predefinidas</li>
                        <li>✓ Campos ACF configurados</li>
                    </ul>
                </div>

                <div class="module-card">
                    <div class="module-header">
                        <h3>🎯 <?php _e( 'Landing Page', 'webtowp-engine' ); ?></h3>
                        <label class="webtowp-toggle">
                            <input type="checkbox" name="w2wp_mod_landing" value="1">
                            <span class="webtowp-toggle-slider"></span>
                        </label>
                    </div>
                    <p><?php _e( 'Crea landing pages de conversión con secciones optimizadas.', 'webtowp-engine' ); ?></p>
                    <ul class="module-features">
                        <li>✓ Hero, Beneficios, Precios</li>
                        <li>✓ Testimonios y CTA</li>
                        <li>✓ Optimizado para conversión</li>
                    </ul>
                </div>
            </div>

            <div class="webtowp-wizard-tip">
                <strong>💡 Consejo:</strong> <?php _e( 'Puedes activar o desactivar módulos en cualquier momento desde el panel de administración.', 'webtowp-engine' ); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Paso 3: Configuración de marca
     */
    private function render_step_branding() {
        ?>
        <div class="webtowp-wizard-step">
            <div class="webtowp-wizard-icon">🎨</div>
            <h2><?php _e( 'Identidad de Marca', 'webtowp-engine' ); ?></h2>
            <p class="lead"><?php _e( 'Configura los elementos visuales de tu sitio.', 'webtowp-engine' ); ?></p>
            
            <div class="webtowp-wizard-form">
                <div class="form-group">
                    <label><?php _e( 'Nombre de la Marca', 'webtowp-engine' ); ?></label>
                    <input type="text" name="w2wp_brand_name" class="webtowp-form-input" placeholder="<?php esc_attr_e( 'Mi Empresa', 'webtowp-engine' ); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><?php _e( 'Color Primario', 'webtowp-engine' ); ?></label>
                        <input type="color" name="w2wp_primary_color" class="webtowp-form-input" value="#667eea">
                    </div>

                    <div class="form-group">
                        <label><?php _e( 'Color Secundario', 'webtowp-engine' ); ?></label>
                        <input type="color" name="w2wp_secondary_color" class="webtowp-form-input" value="#764ba2">
                    </div>
                </div>

                <div class="form-group">
                    <label><?php _e( 'Email de Soporte', 'webtowp-engine' ); ?></label>
                    <input type="email" name="w2wp_support_email" class="webtowp-form-input" placeholder="soporte@miempresa.com">
                </div>

                <div class="form-group">
                    <label><?php _e( 'WhatsApp', 'webtowp-engine' ); ?></label>
                    <input type="text" name="w2wp_whatsapp_contact" class="webtowp-form-input" placeholder="+34 123 456 789">
                </div>
            </div>

            <div class="webtowp-wizard-tip">
                <strong>💡 Consejo:</strong> <?php _e( 'Podrás subir logos y configurar más detalles después en Ajustes Globales.', 'webtowp-engine' ); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Paso 4: Configuración de deployment
     */
    private function render_step_deployment() {
        ?>
        <div class="webtowp-wizard-step">
            <div class="webtowp-wizard-icon">🚀</div>
            <h2><?php _e( 'Configuración de Deployment', 'webtowp-engine' ); ?></h2>
            <p class="lead"><?php _e( 'Conecta tu sistema de despliegue automático.', 'webtowp-engine' ); ?></p>
            
            <div class="webtowp-wizard-form">
                <div class="form-group">
                    <label><?php _e( 'URL del Webhook (Cloudflare)', 'webtowp-engine' ); ?></label>
                    <input type="url" name="w2wp_webhook_url" class="webtowp-form-input" placeholder="https://api.cloudflare.com/...">
                    <p class="form-help"><?php _e( 'Obtén esta URL desde tu panel de Cloudflare Pages', 'webtowp-engine' ); ?></p>
                </div>

                <div class="form-group">
                    <label><?php _e( 'URL del Frontend', 'webtowp-engine' ); ?></label>
                    <input type="url" name="w2wp_frontend_url" class="webtowp-form-input" placeholder="https://mi-sitio.com">
                    <p class="form-help"><?php _e( 'La URL donde está desplegado tu frontend headless', 'webtowp-engine' ); ?></p>
                </div>

                <div class="webtowp-wizard-api-key">
                    <h3><?php _e( 'API Key', 'webtowp-engine' ); ?></h3>
                    <p><?php _e( 'Se generará automáticamente una API Key para acceder a la API REST.', 'webtowp-engine' ); ?></p>
                    <div class="api-key-preview">
                        <code id="generated-api-key"><?php echo esc_html( wp_generate_password( 32, false ) ); ?></code>
                        <button type="button" class="copy-button" onclick="copyApiKey()">📋 <?php _e( 'Copiar', 'webtowp-engine' ); ?></button>
                    </div>
                </div>
            </div>

            <div class="webtowp-wizard-tip">
                <strong>⚠️ Importante:</strong> <?php _e( 'Guarda tu API Key en un lugar seguro. No podrás verla de nuevo después de este paso.', 'webtowp-engine' ); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Paso 5: Completado
     */
    private function render_step_complete() {
        ?>
        <div class="webtowp-wizard-step">
            <div class="webtowp-wizard-icon success">✅</div>
            <h2><?php _e( '¡Todo Listo!', 'webtowp-engine' ); ?></h2>
            <p class="lead"><?php _e( 'Tu sitio está configurado y listo para usar.', 'webtowp-engine' ); ?></p>
            
            <div class="webtowp-wizard-summary">
                <h3><?php _e( 'Próximos Pasos:', 'webtowp-engine' ); ?></h3>
                <div class="next-steps">
                    <div class="next-step-card">
                        <span class="step-icon">📝</span>
                        <div>
                            <h4><?php _e( 'Crea Contenido', 'webtowp-engine' ); ?></h4>
                            <p><?php _e( 'Empieza a añadir posts, páginas y contenido personalizado', 'webtowp-engine' ); ?></p>
                        </div>
                    </div>

                    <div class="next-step-card">
                        <span class="step-icon">🔌</span>
                        <h4><?php _e( 'Conecta tu Frontend', 'webtowp-engine' ); ?></h4>
                        <p><?php _e( 'Usa la API REST para consumir el contenido desde tu aplicación', 'webtowp-engine' ); ?></p>
                    </div>

                    <div class="next-step-card">
                        <span class="step-icon">📚</span>
                        <div>
                            <h4><?php _e( 'Lee la Documentación', 'webtowp-engine' ); ?></h4>
                            <p><?php _e( 'Consulta la documentación de la API y ejemplos de código', 'webtowp-engine' ); ?></p>
                            <a href="<?php echo esc_url( W2WP_PATH . 'API-DOCUMENTATION.md' ); ?>" target="_blank" class="webtowp-button webtowp-button-secondary" style="margin-top: 10px;">
                                <?php _e( 'Ver Documentación', 'webtowp-engine' ); ?> →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="webtowp-wizard-resources">
                <h3><?php _e( 'Recursos Útiles:', 'webtowp-engine' ); ?></h3>
                <ul>
                    <li>📖 <a href="https://github.com/Vannit0/webtowp-engine" target="_blank">GitHub Repository</a></li>
                    <li>🔐 <a href="<?php echo esc_url( W2WP_PATH . 'SECURITY.md' ); ?>" target="_blank">Guía de Seguridad</a></li>
                    <li>🔄 <a href="<?php echo esc_url( W2WP_PATH . 'UPDATES.md' ); ?>" target="_blank">Sistema de Actualizaciones</a></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Completa el onboarding
     */
    public function complete_onboarding() {
        check_admin_referer( 'w2wp_complete_onboarding' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'No tienes permisos para realizar esta acción.', 'webtowp-engine' ) );
        }

        update_option( 'w2wp_onboarding_completed', true );
        update_option( 'w2wp_onboarding_completed_date', current_time( 'mysql' ) );

        wp_redirect( admin_url( 'admin.php?page=webtowp-engine' ) );
        exit;
    }

    /**
     * Omite el onboarding
     */
    public function skip_onboarding() {
        check_admin_referer( 'w2wp_skip_onboarding' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'No tienes permisos para realizar esta acción.', 'webtowp-engine' ) );
        }

        update_option( 'w2wp_onboarding_completed', true );
        update_option( 'w2wp_onboarding_skipped', true );

        wp_redirect( admin_url( 'admin.php?page=webtowp-engine' ) );
        exit;
    }
}
