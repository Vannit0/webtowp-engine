<?php
/**
 * Dashboard Class
 * 
 * Gestiona el dashboard principal con widgets y estadísticas.
 *
 * @package WebToWP_Engine
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Dashboard {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dashboard_assets' ) );
    }

    /**
     * Enqueue dashboard assets
     */
    public function enqueue_dashboard_assets( $hook ) {
        if ( 'toplevel_page_webtowp-engine' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'w2wp-admin-styles',
            W2WP_ASSETS_URL . 'css/admin-styles.css',
            array(),
            W2WP_VERSION
        );

        wp_enqueue_script(
            'w2wp-dashboard',
            W2WP_ASSETS_URL . 'js/dashboard.js',
            array( 'jquery' ),
            W2WP_VERSION,
            true
        );

        wp_localize_script( 'w2wp-dashboard', 'w2wpDashboard', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'w2wp_dashboard_nonce' ),
        ) );
    }

    /**
     * Obtiene estadísticas del sistema
     */
    public function get_system_stats() {
        $logger = W2WP_Deployment_Logger::get_instance();
        $cache = W2WP_Cache_Manager::get_instance();

        // Estadísticas de deployments
        $deployment_stats = $logger->get_stats( 30 );
        $last_deployment = $logger->get_last_deployment();

        // Estadísticas de caché
        $cache_stats = $cache->get_stats();

        // Módulos activos
        $active_modules = array();
        if ( get_option( 'w2wp_mod_informativo', '0' ) === '1' ) {
            $active_modules[] = 'informativo';
        }
        if ( get_option( 'w2wp_mod_landing', '0' ) === '1' ) {
            $active_modules[] = 'landing';
        }

        // Conteo de posts por tipo
        $post_counts = array(
            'posts' => wp_count_posts( 'post' )->publish,
            'pages' => wp_count_posts( 'page' )->publish,
        );

        if ( in_array( 'informativo', $active_modules, true ) ) {
            $post_counts['servicios'] = wp_count_posts( 'w2wp_servicios' )->publish;
            $post_counts['recursos'] = wp_count_posts( 'w2wp_recursos' )->publish;
        }

        return array(
            'deployments' => $deployment_stats,
            'last_deployment' => $last_deployment,
            'cache' => $cache_stats,
            'modules' => $active_modules,
            'posts' => $post_counts,
            'wp_version' => get_bloginfo( 'version' ),
            'php_version' => PHP_VERSION,
            'plugin_version' => W2WP_VERSION,
        );
    }

    /**
     * Obtiene el estado de salud del sistema
     */
    public function get_health_status() {
        $issues = array();
        $score = 100;

        // Verificar ACF
        if ( ! class_exists( 'ACF' ) ) {
            $issues[] = array(
                'type' => 'error',
                'message' => __( 'ACF no está instalado', 'webtowp-engine' ),
            );
            $score -= 30;
        }

        // Verificar API Key
        if ( empty( get_option( 'w2wp_api_key' ) ) ) {
            $issues[] = array(
                'type' => 'warning',
                'message' => __( 'API Key no configurada', 'webtowp-engine' ),
            );
            $score -= 10;
        }

        // Verificar Webhook
        if ( empty( get_option( 'w2wp_webhook_url' ) ) ) {
            $issues[] = array(
                'type' => 'warning',
                'message' => __( 'Webhook URL no configurada', 'webtowp-engine' ),
            );
            $score -= 10;
        }

        // Verificar versión de PHP
        if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
            $issues[] = array(
                'type' => 'warning',
                'message' => __( 'PHP desactualizado. Se recomienda PHP 7.4+', 'webtowp-engine' ),
            );
            $score -= 15;
        }

        // Verificar módulos
        $has_modules = get_option( 'w2wp_mod_informativo', '0' ) === '1' || 
                       get_option( 'w2wp_mod_landing', '0' ) === '1';
        
        if ( ! $has_modules ) {
            $issues[] = array(
                'type' => 'info',
                'message' => __( 'No hay módulos activos', 'webtowp-engine' ),
            );
            $score -= 5;
        }

        $status = 'excellent';
        if ( $score < 90 ) {
            $status = 'good';
        }
        if ( $score < 70 ) {
            $status = 'fair';
        }
        if ( $score < 50 ) {
            $status = 'poor';
        }

        return array(
            'score' => $score,
            'status' => $status,
            'issues' => $issues,
        );
    }

    /**
     * Obtiene actividad reciente
     */
    public function get_recent_activity() {
        $logger = W2WP_Deployment_Logger::get_instance();
        
        $recent_logs = $logger->get_logs( array(
            'limit' => 5,
            'order' => 'DESC',
        ) );

        $activity = array();

        foreach ( $recent_logs as $log ) {
            $activity[] = array(
                'type' => $log->action,
                'status' => $log->status,
                'message' => $log->response_message,
                'timestamp' => $log->timestamp,
                'user_id' => $log->user_id,
            );
        }

        return $activity;
    }

    /**
     * Obtiene tareas pendientes
     */
    public function get_pending_tasks() {
        $tasks = array();

        // Verificar si hay actualizaciones disponibles
        $update_plugins = get_site_transient( 'update_plugins' );
        if ( isset( $update_plugins->response[ W2WP_BASENAME ] ) ) {
            $tasks[] = array(
                'priority' => 'high',
                'title' => __( 'Actualización disponible', 'webtowp-engine' ),
                'description' => __( 'Hay una nueva versión del plugin disponible', 'webtowp-engine' ),
                'action_url' => admin_url( 'plugins.php' ),
                'action_text' => __( 'Actualizar', 'webtowp-engine' ),
            );
        }

        // Verificar configuración incompleta
        if ( empty( get_option( 'w2wp_api_key' ) ) ) {
            $tasks[] = array(
                'priority' => 'medium',
                'title' => __( 'Configurar API Key', 'webtowp-engine' ),
                'description' => __( 'Genera una API Key para habilitar el acceso a la API REST', 'webtowp-engine' ),
                'action_url' => admin_url( 'admin.php?page=webtowp-deployment-api' ),
                'action_text' => __( 'Configurar', 'webtowp-engine' ),
            );
        }

        if ( empty( get_option( 'w2wp_webhook_url' ) ) ) {
            $tasks[] = array(
                'priority' => 'medium',
                'title' => __( 'Configurar Webhook', 'webtowp-engine' ),
                'description' => __( 'Configura la URL del webhook para deployments automáticos', 'webtowp-engine' ),
                'action_url' => admin_url( 'admin.php?page=webtowp-deployment-api' ),
                'action_text' => __( 'Configurar', 'webtowp-engine' ),
            );
        }

        // Verificar si hay módulos sin activar
        $has_modules = get_option( 'w2wp_mod_informativo', '0' ) === '1' || 
                       get_option( 'w2wp_mod_landing', '0' ) === '1';
        
        if ( ! $has_modules ) {
            $tasks[] = array(
                'priority' => 'low',
                'title' => __( 'Activar módulos', 'webtowp-engine' ),
                'description' => __( 'Activa módulos para añadir funcionalidad a tu sitio', 'webtowp-engine' ),
                'action_url' => admin_url( 'admin.php?page=webtowp-active-modules' ),
                'action_text' => __( 'Ver módulos', 'webtowp-engine' ),
            );
        }

        return $tasks;
    }

    /**
     * Obtiene enlaces rápidos
     */
    public function get_quick_links() {
        return array(
            array(
                'icon' => '🎨',
                'title' => __( 'Ajustes Globales', 'webtowp-engine' ),
                'description' => __( 'Configura colores, logos y redes sociales', 'webtowp-engine' ),
                'url' => admin_url( 'admin.php?page=webtowp-global-settings' ),
            ),
            array(
                'icon' => '🚀',
                'title' => __( 'Despliegue & API', 'webtowp-engine' ),
                'description' => __( 'Gestiona deployments y API keys', 'webtowp-engine' ),
                'url' => admin_url( 'admin.php?page=webtowp-deployment-api' ),
            ),
            array(
                'icon' => '📊',
                'title' => __( 'Logs de Deployment', 'webtowp-engine' ),
                'description' => __( 'Revisa el historial de deployments', 'webtowp-engine' ),
                'url' => admin_url( 'admin.php?page=webtowp-deployment-logs' ),
            ),
            array(
                'icon' => '💾',
                'title' => __( 'Backup & Restauración', 'webtowp-engine' ),
                'description' => __( 'Exporta e importa configuración', 'webtowp-engine' ),
                'url' => admin_url( 'admin.php?page=webtowp-backup' ),
            ),
            array(
                'icon' => '🔧',
                'title' => __( 'Módulos Activos', 'webtowp-engine' ),
                'description' => __( 'Activa o desactiva módulos', 'webtowp-engine' ),
                'url' => admin_url( 'admin.php?page=webtowp-active-modules' ),
            ),
            array(
                'icon' => '📈',
                'title' => __( 'Estado del Sistema', 'webtowp-engine' ),
                'description' => __( 'Información técnica del sistema', 'webtowp-engine' ),
                'url' => admin_url( 'admin.php?page=webtowp-system-status' ),
            ),
        );
    }
}
