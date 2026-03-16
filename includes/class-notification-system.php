<?php
/**
 * Notification System Class
 * 
 * Sistema avanzado de notificaciones con soporte para toast, modales y persistencia.
 *
 * @package WebToWP_Engine
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Notification_System {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_notification_assets' ) );
        add_action( 'admin_footer', array( $this, 'render_notification_container' ) );
        add_action( 'wp_ajax_w2wp_dismiss_notification', array( $this, 'dismiss_notification' ) );
    }

    /**
     * Enqueue notification assets
     */
    public function enqueue_notification_assets() {
        wp_enqueue_style(
            'w2wp-notifications',
            W2WP_ASSETS_URL . 'css/notifications.css',
            array(),
            W2WP_VERSION
        );

        wp_enqueue_script(
            'w2wp-notifications',
            W2WP_ASSETS_URL . 'js/notifications.js',
            array( 'jquery' ),
            W2WP_VERSION,
            true
        );

        wp_localize_script( 'w2wp-notifications', 'w2wpNotifications', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'w2wp_notifications_nonce' ),
        ) );
    }

    /**
     * Render notification container
     */
    public function render_notification_container() {
        ?>
        <div id="w2wp-notification-container"></div>
        <div id="w2wp-modal-container"></div>
        <?php
    }

    /**
     * Añade una notificación toast
     */
    public function add_toast( $message, $type = 'info', $duration = 5000 ) {
        $notification = array(
            'id' => uniqid( 'w2wp_toast_' ),
            'type' => $type,
            'message' => $message,
            'duration' => $duration,
            'timestamp' => current_time( 'timestamp' ),
        );

        $this->queue_notification( $notification );
        return $notification['id'];
    }

    /**
     * Añade una notificación persistente
     */
    public function add_persistent( $message, $type = 'info', $dismissible = true ) {
        $notification = array(
            'id' => uniqid( 'w2wp_persistent_' ),
            'type' => $type,
            'message' => $message,
            'dismissible' => $dismissible,
            'persistent' => true,
            'timestamp' => current_time( 'timestamp' ),
        );

        $this->save_persistent_notification( $notification );
        return $notification['id'];
    }

    /**
     * Añade una notificación modal
     */
    public function add_modal( $title, $message, $type = 'info', $actions = array() ) {
        $notification = array(
            'id' => uniqid( 'w2wp_modal_' ),
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'actions' => $actions,
            'modal' => true,
            'timestamp' => current_time( 'timestamp' ),
        );

        $this->queue_notification( $notification );
        return $notification['id'];
    }

    /**
     * Cola de notificaciones en sesión
     */
    private function queue_notification( $notification ) {
        if ( ! isset( $_SESSION ) ) {
            session_start();
        }

        if ( ! isset( $_SESSION['w2wp_notifications'] ) ) {
            $_SESSION['w2wp_notifications'] = array();
        }

        $_SESSION['w2wp_notifications'][] = $notification;
    }

    /**
     * Obtiene notificaciones en cola
     */
    public function get_queued_notifications() {
        if ( ! isset( $_SESSION ) ) {
            session_start();
        }

        $notifications = isset( $_SESSION['w2wp_notifications'] ) ? $_SESSION['w2wp_notifications'] : array();
        
        // Limpiar cola
        unset( $_SESSION['w2wp_notifications'] );

        return $notifications;
    }

    /**
     * Guarda notificación persistente
     */
    private function save_persistent_notification( $notification ) {
        $user_id = get_current_user_id();
        $notifications = get_user_meta( $user_id, 'w2wp_persistent_notifications', true );
        
        if ( ! is_array( $notifications ) ) {
            $notifications = array();
        }

        $notifications[ $notification['id'] ] = $notification;
        update_user_meta( $user_id, 'w2wp_persistent_notifications', $notifications );
    }

    /**
     * Obtiene notificaciones persistentes
     */
    public function get_persistent_notifications() {
        $user_id = get_current_user_id();
        $notifications = get_user_meta( $user_id, 'w2wp_persistent_notifications', true );
        
        return is_array( $notifications ) ? $notifications : array();
    }

    /**
     * Elimina notificación persistente
     */
    public function dismiss_notification() {
        check_ajax_referer( 'w2wp_notifications_nonce', 'nonce' );

        $notification_id = isset( $_POST['notification_id'] ) ? sanitize_text_field( $_POST['notification_id'] ) : '';

        if ( empty( $notification_id ) ) {
            wp_send_json_error( array( 'message' => 'ID de notificación inválido' ) );
        }

        $user_id = get_current_user_id();
        $notifications = get_user_meta( $user_id, 'w2wp_persistent_notifications', true );

        if ( is_array( $notifications ) && isset( $notifications[ $notification_id ] ) ) {
            unset( $notifications[ $notification_id ] );
            update_user_meta( $user_id, 'w2wp_persistent_notifications', $notifications );
            wp_send_json_success( array( 'message' => 'Notificación eliminada' ) );
        }

        wp_send_json_error( array( 'message' => 'Notificación no encontrada' ) );
    }

    /**
     * Limpia notificaciones antiguas
     */
    public function cleanup_old_notifications( $days = 30 ) {
        $user_id = get_current_user_id();
        $notifications = get_user_meta( $user_id, 'w2wp_persistent_notifications', true );

        if ( ! is_array( $notifications ) ) {
            return;
        }

        $cutoff = current_time( 'timestamp' ) - ( $days * DAY_IN_SECONDS );
        $cleaned = array();

        foreach ( $notifications as $id => $notification ) {
            if ( $notification['timestamp'] >= $cutoff ) {
                $cleaned[ $id ] = $notification;
            }
        }

        update_user_meta( $user_id, 'w2wp_persistent_notifications', $cleaned );
    }

    /**
     * Notificaciones predefinidas
     */
    public function notify_deployment_success( $message = '' ) {
        if ( empty( $message ) ) {
            $message = __( '¡Deployment exitoso! Tu sitio se ha actualizado correctamente.', 'webtowp-engine' );
        }
        return $this->add_toast( $message, 'success', 5000 );
    }

    public function notify_deployment_error( $message = '' ) {
        if ( empty( $message ) ) {
            $message = __( 'Error en el deployment. Revisa los logs para más información.', 'webtowp-engine' );
        }
        return $this->add_toast( $message, 'error', 8000 );
    }

    public function notify_cache_cleared() {
        return $this->add_toast(
            __( 'Caché limpiada correctamente.', 'webtowp-engine' ),
            'success',
            3000
        );
    }

    public function notify_settings_saved() {
        return $this->add_toast(
            __( 'Configuración guardada correctamente.', 'webtowp-engine' ),
            'success',
            3000
        );
    }

    public function notify_module_activated( $module_name ) {
        return $this->add_toast(
            sprintf( __( 'Módulo "%s" activado correctamente.', 'webtowp-engine' ), $module_name ),
            'success',
            4000
        );
    }

    public function notify_backup_created() {
        return $this->add_toast(
            __( 'Backup creado correctamente.', 'webtowp-engine' ),
            'success',
            4000
        );
    }

    public function notify_update_available( $version ) {
        return $this->add_persistent(
            sprintf( __( 'Nueva versión %s disponible. Actualiza para obtener las últimas mejoras.', 'webtowp-engine' ), $version ),
            'info',
            true
        );
    }

    public function notify_configuration_incomplete() {
        return $this->add_persistent(
            __( 'Configuración incompleta. Completa los ajustes básicos para comenzar.', 'webtowp-engine' ),
            'warning',
            true
        );
    }

    /**
     * Notificación de bienvenida para nuevos usuarios
     */
    public function notify_welcome() {
        $actions = array(
            array(
                'text' => __( 'Comenzar Tour', 'webtowp-engine' ),
                'url' => admin_url( 'admin.php?page=webtowp-engine&tour=start' ),
                'primary' => true,
            ),
            array(
                'text' => __( 'Más Tarde', 'webtowp-engine' ),
                'dismiss' => true,
            ),
        );

        return $this->add_modal(
            __( '¡Bienvenido a WebToWP Engine!', 'webtowp-engine' ),
            __( '¿Quieres hacer un tour rápido para conocer las funcionalidades principales?', 'webtowp-engine' ),
            'info',
            $actions
        );
    }
}
