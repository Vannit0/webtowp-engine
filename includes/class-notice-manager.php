<?php
/**
 * Notice Manager Class
 * 
 * Gestiona todas las notificaciones admin del plugin.
 *
 * @package WebToWP_Engine
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Notice_Manager {

    private static $instance = null;
    private $notices = array();
    private $persistent_notices_option = 'w2wp_persistent_notices';

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_notices', array( $this, 'display_notices' ) );
        add_action( 'admin_init', array( $this, 'handle_dismiss_notice' ) );
    }

    /**
     * Agrega un notice de éxito
     */
    public function add_success( $message, $dismissible = true, $persistent = false ) {
        $this->add_notice( $message, 'success', $dismissible, $persistent );
    }

    /**
     * Agrega un notice de error
     */
    public function add_error( $message, $dismissible = true, $persistent = false ) {
        $this->add_notice( $message, 'error', $dismissible, $persistent );
    }

    /**
     * Agrega un notice de advertencia
     */
    public function add_warning( $message, $dismissible = true, $persistent = false ) {
        $this->add_notice( $message, 'warning', $dismissible, $persistent );
    }

    /**
     * Agrega un notice informativo
     */
    public function add_info( $message, $dismissible = true, $persistent = false ) {
        $this->add_notice( $message, 'info', $dismissible, $persistent );
    }

    /**
     * Agrega un notice genérico
     */
    private function add_notice( $message, $type = 'info', $dismissible = true, $persistent = false ) {
        $notice = array(
            'message' => $message,
            'type' => $type,
            'dismissible' => $dismissible,
            'persistent' => $persistent,
            'id' => md5( $message . $type ),
        );

        if ( $persistent ) {
            $this->save_persistent_notice( $notice );
        } else {
            $this->notices[] = $notice;
        }
    }

    /**
     * Guarda un notice persistente en la base de datos
     */
    private function save_persistent_notice( $notice ) {
        $persistent_notices = get_option( $this->persistent_notices_option, array() );
        
        // Evitar duplicados
        $notice_exists = false;
        foreach ( $persistent_notices as $existing_notice ) {
            if ( $existing_notice['id'] === $notice['id'] ) {
                $notice_exists = true;
                break;
            }
        }
        
        if ( ! $notice_exists ) {
            $persistent_notices[] = $notice;
            update_option( $this->persistent_notices_option, $persistent_notices );
        }
    }

    /**
     * Obtiene notices persistentes
     */
    private function get_persistent_notices() {
        return get_option( $this->persistent_notices_option, array() );
    }

    /**
     * Elimina un notice persistente
     */
    public function dismiss_persistent_notice( $notice_id ) {
        $persistent_notices = $this->get_persistent_notices();
        
        $persistent_notices = array_filter( $persistent_notices, function( $notice ) use ( $notice_id ) {
            return $notice['id'] !== $notice_id;
        });
        
        update_option( $this->persistent_notices_option, array_values( $persistent_notices ) );
    }

    /**
     * Maneja la acción de descartar un notice
     */
    public function handle_dismiss_notice() {
        if ( ! isset( $_GET['w2wp_dismiss_notice'] ) ) {
            return;
        }

        $notice_id = sanitize_text_field( $_GET['w2wp_dismiss_notice'] );
        
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'w2wp_dismiss_notice_' . $notice_id ) ) {
            return;
        }

        $this->dismiss_persistent_notice( $notice_id );
        
        // Redirigir para limpiar la URL
        wp_safe_redirect( remove_query_arg( array( 'w2wp_dismiss_notice', '_wpnonce' ) ) );
        exit;
    }

    /**
     * Muestra todos los notices
     */
    public function display_notices() {
        // Mostrar notices de sesión
        foreach ( $this->notices as $notice ) {
            $this->render_notice( $notice );
        }

        // Mostrar notices persistentes
        $persistent_notices = $this->get_persistent_notices();
        foreach ( $persistent_notices as $notice ) {
            $this->render_notice( $notice, true );
        }
    }

    /**
     * Renderiza un notice individual
     */
    private function render_notice( $notice, $is_persistent = false ) {
        $type = $notice['type'];
        $message = $notice['message'];
        $dismissible = $notice['dismissible'];
        $notice_id = $notice['id'];

        $class = 'notice notice-' . $type;
        
        if ( $dismissible && ! $is_persistent ) {
            $class .= ' is-dismissible';
        }

        ?>
        <div class="<?php echo esc_attr( $class ); ?>" data-notice-id="<?php echo esc_attr( $notice_id ); ?>">
            <p><?php echo wp_kses_post( $message ); ?></p>
            <?php if ( $dismissible && $is_persistent ) : ?>
                <p>
                    <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'w2wp_dismiss_notice', $notice_id ), 'w2wp_dismiss_notice_' . $notice_id ) ); ?>" class="button button-small">
                        <?php _e( 'Descartar', 'webtowp-engine' ); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Limpia todos los notices persistentes
     */
    public function clear_all_persistent_notices() {
        delete_option( $this->persistent_notices_option );
    }

    /**
     * Agrega un notice de validación con errores específicos
     */
    public function add_validation_errors( $errors ) {
        if ( empty( $errors ) ) {
            return;
        }

        $message = '<strong>' . __( 'Se encontraron los siguientes errores:', 'webtowp-engine' ) . '</strong><ul>';
        
        foreach ( $errors as $field => $field_errors ) {
            foreach ( $field_errors as $error ) {
                $message .= '<li>' . esc_html( $error ) . '</li>';
            }
        }
        
        $message .= '</ul>';
        
        $this->add_error( $message, true, false );
    }

    /**
     * Agrega un notice de configuración incompleta
     */
    public function add_incomplete_setup_notice() {
        $message = sprintf(
            __( '<strong>WebToWP Engine:</strong> La configuración está incompleta. Por favor, completa la <a href="%s">configuración global</a> para aprovechar todas las funcionalidades.', 'webtowp-engine' ),
            admin_url( 'admin.php?page=webtowp-settings' )
        );
        
        $this->add_warning( $message, true, true );
    }

    /**
     * Agrega un notice de actualización disponible
     */
    public function add_update_available_notice( $new_version ) {
        $message = sprintf(
            __( '<strong>WebToWP Engine:</strong> Hay una nueva versión disponible (%s). <a href="%s">Actualizar ahora</a>.', 'webtowp-engine' ),
            $new_version,
            admin_url( 'plugins.php' )
        );
        
        $this->add_info( $message, true, true );
    }

    /**
     * Agrega un notice de deployment exitoso
     */
    public function add_deployment_success_notice() {
        $this->add_success(
            __( '<strong>Deployment exitoso:</strong> Los cambios se han desplegado correctamente.', 'webtowp-engine' ),
            true,
            false
        );
    }

    /**
     * Agrega un notice de deployment fallido
     */
    public function add_deployment_error_notice( $error_message = '' ) {
        $message = __( '<strong>Error en deployment:</strong> No se pudieron desplegar los cambios.', 'webtowp-engine' );
        
        if ( ! empty( $error_message ) ) {
            $message .= ' ' . esc_html( $error_message );
        }
        
        $this->add_error( $message, true, false );
    }
}
