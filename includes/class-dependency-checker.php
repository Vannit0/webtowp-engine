<?php
/**
 * Dependency Checker
 * 
 * Verifica que todas las dependencias necesarias estén instaladas y activas.
 *
 * @package WebToWP_Engine
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Dependency_Checker {

    private static $instance = null;
    private $missing_dependencies = array();
    private $is_valid = true;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_init', array( $this, 'check_dependencies' ) );
        add_action( 'admin_notices', array( $this, 'display_dependency_notices' ) );
    }

    /**
     * Verifica todas las dependencias requeridas
     */
    public function check_dependencies() {
        $this->missing_dependencies = array();
        $this->is_valid = true;

        // Verificar ACF
        if ( ! $this->is_acf_active() ) {
            $this->missing_dependencies[] = array(
                'name' => 'Advanced Custom Fields',
                'slug' => 'advanced-custom-fields',
                'required' => true,
                'message' => __( 'WebToWP Engine requiere Advanced Custom Fields (ACF) para funcionar correctamente.', 'webtowp-engine' ),
                'install_url' => admin_url( 'plugin-install.php?s=Advanced+Custom+Fields&tab=search&type=term' ),
            );
            $this->is_valid = false;
        }

        // Verificar versión de PHP
        if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
            $this->missing_dependencies[] = array(
                'name' => 'PHP 7.4+',
                'slug' => 'php',
                'required' => true,
                'message' => sprintf(
                    __( 'WebToWP Engine requiere PHP 7.4 o superior. Tu versión actual es %s.', 'webtowp-engine' ),
                    PHP_VERSION
                ),
            );
            $this->is_valid = false;
        }

        // Verificar versión de WordPress
        global $wp_version;
        if ( version_compare( $wp_version, '5.8', '<' ) ) {
            $this->missing_dependencies[] = array(
                'name' => 'WordPress 5.8+',
                'slug' => 'wordpress',
                'required' => true,
                'message' => sprintf(
                    __( 'WebToWP Engine requiere WordPress 5.8 o superior. Tu versión actual es %s.', 'webtowp-engine' ),
                    $wp_version
                ),
            );
            $this->is_valid = false;
        }

        return $this->is_valid;
    }

    /**
     * Verifica si ACF está activo
     */
    private function is_acf_active() {
        // Verificar si la función principal de ACF existe
        if ( ! function_exists( 'acf' ) && ! class_exists( 'ACF' ) ) {
            return false;
        }

        // Verificar si acf_add_local_field_group existe (necesaria para el plugin)
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return false;
        }

        return true;
    }

    /**
     * Verifica si ACF Pro está activo
     */
    public function is_acf_pro_active() {
        return defined( 'ACF_PRO' ) && ACF_PRO;
    }

    /**
     * Obtiene la versión de ACF instalada
     */
    public function get_acf_version() {
        if ( defined( 'ACF_VERSION' ) ) {
            return ACF_VERSION;
        }
        return null;
    }

    /**
     * Muestra notices de dependencias faltantes
     */
    public function display_dependency_notices() {
        if ( empty( $this->missing_dependencies ) ) {
            return;
        }

        foreach ( $this->missing_dependencies as $dependency ) {
            $this->display_dependency_notice( $dependency );
        }
    }

    /**
     * Muestra un notice individual de dependencia
     */
    private function display_dependency_notice( $dependency ) {
        $class = $dependency['required'] ? 'notice-error' : 'notice-warning';
        ?>
        <div class="notice <?php echo esc_attr( $class ); ?> is-dismissible">
            <p>
                <strong><?php echo esc_html( $dependency['name'] ); ?>:</strong>
                <?php echo esc_html( $dependency['message'] ); ?>
            </p>
            <?php if ( isset( $dependency['install_url'] ) ) : ?>
                <p>
                    <a href="<?php echo esc_url( $dependency['install_url'] ); ?>" class="button button-primary">
                        <?php printf( __( 'Instalar %s', 'webtowp-engine' ), esc_html( $dependency['name'] ) ); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Verifica si todas las dependencias están satisfechas
     */
    public function are_dependencies_met() {
        return $this->is_valid;
    }

    /**
     * Obtiene lista de dependencias faltantes
     */
    public function get_missing_dependencies() {
        return $this->missing_dependencies;
    }

    /**
     * Desactiva el plugin si las dependencias no están satisfechas
     */
    public function deactivate_if_dependencies_missing() {
        if ( ! $this->are_dependencies_met() ) {
            deactivate_plugins( plugin_basename( W2WP_PATH . '/webtowp-engine.php' ) );
            
            // Mostrar mensaje de error
            wp_die(
                wp_kses_post( $this->get_deactivation_message() ),
                esc_html__( 'Error de Dependencias - WebToWP Engine', 'webtowp-engine' ),
                array( 'back_link' => true )
            );
        }
    }

    /**
     * Obtiene el mensaje de desactivación
     */
    private function get_deactivation_message() {
        $message = '<h1>' . __( 'WebToWP Engine no pudo activarse', 'webtowp-engine' ) . '</h1>';
        $message .= '<p>' . __( 'El plugin requiere las siguientes dependencias:', 'webtowp-engine' ) . '</p>';
        $message .= '<ul>';
        
        foreach ( $this->missing_dependencies as $dependency ) {
            $message .= '<li><strong>' . esc_html( $dependency['name'] ) . ':</strong> ' . esc_html( $dependency['message'] ) . '</li>';
        }
        
        $message .= '</ul>';
        $message .= '<p>' . __( 'Por favor, instala las dependencias requeridas e intenta activar el plugin nuevamente.', 'webtowp-engine' ) . '</p>';
        
        return $message;
    }
}
