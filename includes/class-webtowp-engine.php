<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WebToWP_Engine {

    private static $instance = null;

    private function __construct() {
        $this->define_constants();
        $this->init_autoloader();
        $this->init_hooks();
        $this->init_components();
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function define_constants() {
        if ( ! defined( 'W2WP_INCLUDES_PATH' ) ) {
            define( 'W2WP_INCLUDES_PATH', W2WP_PATH . 'includes/' );
        }
        if ( ! defined( 'W2WP_MODULES_PATH' ) ) {
            define( 'W2WP_MODULES_PATH', W2WP_PATH . 'modules/' );
        }
        if ( ! defined( 'W2WP_ASSETS_PATH' ) ) {
            define( 'W2WP_ASSETS_PATH', W2WP_PATH . 'assets/' );
        }
        if ( ! defined( 'W2WP_ASSETS_URL' ) ) {
            define( 'W2WP_ASSETS_URL', W2WP_URL . 'assets/' );
        }
    }

    private function init_autoloader() {
        spl_autoload_register( array( $this, 'autoload' ) );
    }

    public function autoload( $class ) {
        $prefixes = array( 'WebToWP_', 'W2WP_' );
        $relative_class = '';

        foreach ( $prefixes as $prefix ) {
            $len = strlen( $prefix );
            if ( strncmp( $prefix, $class, $len ) === 0 ) {
                $relative_class = substr( $class, $len );
                break;
            }
        }

        if ( empty( $relative_class ) || ! is_string( $relative_class ) ) {
            return;
        }

        $file = 'class-' . str_replace( '_', '-', strtolower( $relative_class ) ) . '.php';
        
        $paths = array(
            W2WP_INCLUDES_PATH . $file,
            W2WP_MODULES_PATH . $file,
        );

        foreach ( $paths as $path ) {
            if ( file_exists( $path ) ) {
                require_once $path;
                return;
            }
        }
    }

    private function init_hooks() {
        add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
        add_action( 'init', array( $this, 'init' ) );
    }

    public function load_plugin_textdomain() {
        $basename = defined( 'W2WP_BASENAME' ) ? W2WP_BASENAME : '';
        if ( empty( $basename ) ) {
            return;
        }
        
        $lang_dir = dirname( $basename );
        if ( ! is_string( $lang_dir ) ) {
            return;
        }
        
        load_plugin_textdomain(
            'webtowp-engine',
            false,
            $lang_dir . '/languages/'
        );
    }

    public function init() {
        do_action( 'webtowp_engine_init' );
    }

    private function init_components() {
        // Verificar dependencias primero
        $dependency_checker = W2WP_Dependency_Checker::get_instance();
        
        // Si las dependencias no están satisfechas, no inicializar componentes
        if ( ! $dependency_checker->are_dependencies_met() ) {
            return;
        }

        W2WP_i18n::get_instance();
        W2WP_Rewrite_Manager::get_instance();
        W2WP_Notice_Manager::get_instance();
        W2WP_Cache_Manager::get_instance();
        W2WP_Deployment_Logger::get_instance();
        W2WP_Backup_Manager::get_instance();
        W2WP_Dashboard::get_instance();
        W2WP_Notification_System::get_instance();
        W2WP_Onboarding_Wizard::get_instance();
        W2WP_Security_Logger::get_instance();
        W2WP_API_Key_Manager::get_instance();
        W2WP_Rate_Limiter::get_instance();
        W2WP_Encryption::get_instance();
        new W2WP_API_Config();
        new W2WP_ACF_Fields();
        W2WP_Admin_Setup::get_instance();
        W2WP_Module_Manager::get_instance();
        W2WP_Module_Informativo::get_instance();
        W2WP_Module_Landing::get_instance();
        W2WP_Headless_Bridge::get_instance();
    }

    private function __clone() {}

    public function __wakeup() {
        throw new Exception( 'Cannot unserialize singleton' );
    }
}
