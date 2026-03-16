<?php
/**
 * Backup Manager Class
 * 
 * Gestiona la exportación e importación de configuraciones del plugin.
 *
 * @package WebToWP_Engine
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Backup_Manager {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_post_w2wp_export_settings', array( $this, 'export_settings' ) );
        add_action( 'admin_post_w2wp_import_settings', array( $this, 'import_settings' ) );
    }

    /**
     * Obtiene todas las opciones del plugin
     */
    public function get_all_plugin_options() {
        global $wpdb;
        
        $options = array();
        
        // Obtener todas las opciones que empiezan con 'w2wp_'
        $results = $wpdb->get_results(
            "SELECT option_name, option_value 
             FROM {$wpdb->options} 
             WHERE option_name LIKE 'w2wp_%'",
            ARRAY_A
        );
        
        foreach ( $results as $row ) {
            $options[ $row['option_name'] ] = maybe_unserialize( $row['option_value'] );
        }
        
        return $options;
    }

    /**
     * Exporta la configuración a JSON
     */
    public function export_to_json() {
        $data = array(
            'version' => W2WP_VERSION,
            'export_date' => current_time( 'mysql' ),
            'site_url' => get_site_url(),
            'options' => $this->get_all_plugin_options(),
            'modules' => array(
                'informativo' => get_option( 'w2wp_mod_informativo', '0' ),
                'landing' => get_option( 'w2wp_mod_landing', '0' ),
            ),
        );
        
        return wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
    }

    /**
     * Exporta la configuración y descarga el archivo
     */
    public function export_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'No tienes permisos para realizar esta acción.', 'webtowp-engine' ) );
        }
        
        check_admin_referer( 'w2wp_export_settings' );
        
        $json = $this->export_to_json();
        $filename = 'webtowp-engine-backup-' . date( 'Y-m-d-His' ) . '.json';
        
        header( 'Content-Type: application/json' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Content-Length: ' . strlen( $json ) );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );
        
        echo $json;
        
        // Log de exportación
        $logger = W2WP_Deployment_Logger::get_instance();
        $logger->log_success( 'export_settings', 200, 'Configuración exportada: ' . $filename );
        
        exit;
    }

    /**
     * Importa la configuración desde JSON
     */
    public function import_from_json( $json_string ) {
        $data = json_decode( $json_string, true );
        
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return new WP_Error( 'invalid_json', __( 'El archivo JSON no es válido.', 'webtowp-engine' ) );
        }
        
        if ( ! isset( $data['options'] ) || ! is_array( $data['options'] ) ) {
            return new WP_Error( 'invalid_format', __( 'El formato del archivo no es válido.', 'webtowp-engine' ) );
        }
        
        $imported = 0;
        $skipped = 0;
        $errors = array();
        
        foreach ( $data['options'] as $option_name => $option_value ) {
            // Validar que sea una opción del plugin
            if ( strpos( $option_name, 'w2wp_' ) !== 0 ) {
                $skipped++;
                continue;
            }
            
            // No importar opciones sensibles
            $sensitive_options = array( 'w2wp_api_key', 'w2wp_webhook_url' );
            if ( in_array( $option_name, $sensitive_options, true ) ) {
                $skipped++;
                continue;
            }
            
            $result = update_option( $option_name, $option_value );
            
            if ( $result ) {
                $imported++;
            } else {
                $errors[] = $option_name;
            }
        }
        
        // Limpiar caché después de importar
        $cache = W2WP_Cache_Manager::get_instance();
        $cache->clear_all();
        
        return array(
            'success' => true,
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
            'version' => isset( $data['version'] ) ? $data['version'] : 'unknown',
        );
    }

    /**
     * Procesa la importación desde el formulario
     */
    public function import_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'No tienes permisos para realizar esta acción.', 'webtowp-engine' ) );
        }
        
        check_admin_referer( 'w2wp_import_settings' );
        
        if ( ! isset( $_FILES['import_file'] ) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK ) {
            $notice_manager = W2WP_Notice_Manager::get_instance();
            $notice_manager->add_error( __( 'Error al subir el archivo.', 'webtowp-engine' ) );
            wp_redirect( admin_url( 'admin.php?page=webtowp-backup' ) );
            exit;
        }
        
        $file = $_FILES['import_file'];
        
        // Validar tipo de archivo
        $file_type = wp_check_filetype( $file['name'] );
        if ( $file_type['ext'] !== 'json' ) {
            $notice_manager = W2WP_Notice_Manager::get_instance();
            $notice_manager->add_error( __( 'Solo se permiten archivos JSON.', 'webtowp-engine' ) );
            wp_redirect( admin_url( 'admin.php?page=webtowp-backup' ) );
            exit;
        }
        
        $json_string = file_get_contents( $file['tmp_name'] );
        $result = $this->import_from_json( $json_string );
        
        $notice_manager = W2WP_Notice_Manager::get_instance();
        $logger = W2WP_Deployment_Logger::get_instance();
        
        if ( is_wp_error( $result ) ) {
            $notice_manager->add_error( $result->get_error_message() );
            $logger->log_error( 'import_settings', 400, $result->get_error_message() );
        } else {
            $message = sprintf(
                __( 'Importación completada: %d opciones importadas, %d omitidas.', 'webtowp-engine' ),
                $result['imported'],
                $result['skipped']
            );
            $notice_manager->add_success( $message );
            $logger->log_success( 'import_settings', 200, $message );
        }
        
        wp_redirect( admin_url( 'admin.php?page=webtowp-backup' ) );
        exit;
    }

    /**
     * Crea un backup automático antes de actualizar
     */
    public function create_auto_backup() {
        $json = $this->export_to_json();
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/webtowp-backups';
        
        if ( ! file_exists( $backup_dir ) ) {
            wp_mkdir_p( $backup_dir );
        }
        
        $filename = 'auto-backup-' . date( 'Y-m-d-His' ) . '.json';
        $filepath = $backup_dir . '/' . $filename;
        
        $result = file_put_contents( $filepath, $json );
        
        if ( $result ) {
            // Limpiar backups antiguos (mantener solo los últimos 10)
            $this->cleanup_old_backups( $backup_dir, 10 );
            return $filepath;
        }
        
        return false;
    }

    /**
     * Limpia backups antiguos
     */
    private function cleanup_old_backups( $backup_dir, $keep = 10 ) {
        $files = glob( $backup_dir . '/auto-backup-*.json' );
        
        if ( count( $files ) <= $keep ) {
            return;
        }
        
        // Ordenar por fecha de modificación
        usort( $files, function( $a, $b ) {
            return filemtime( $b ) - filemtime( $a );
        });
        
        // Eliminar los más antiguos
        $to_delete = array_slice( $files, $keep );
        foreach ( $to_delete as $file ) {
            @unlink( $file );
        }
    }

    /**
     * Obtiene la lista de backups automáticos
     */
    public function get_auto_backups() {
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/webtowp-backups';
        
        if ( ! file_exists( $backup_dir ) ) {
            return array();
        }
        
        $files = glob( $backup_dir . '/auto-backup-*.json' );
        $backups = array();
        
        foreach ( $files as $file ) {
            $backups[] = array(
                'filename' => basename( $file ),
                'filepath' => $file,
                'size' => filesize( $file ),
                'date' => filemtime( $file ),
            );
        }
        
        // Ordenar por fecha descendente
        usort( $backups, function( $a, $b ) {
            return $b['date'] - $a['date'];
        });
        
        return $backups;
    }

    /**
     * Restaura un backup automático
     */
    public function restore_auto_backup( $filename ) {
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/webtowp-backups';
        $filepath = $backup_dir . '/' . basename( $filename );
        
        if ( ! file_exists( $filepath ) ) {
            return new WP_Error( 'file_not_found', __( 'El archivo de backup no existe.', 'webtowp-engine' ) );
        }
        
        $json_string = file_get_contents( $filepath );
        return $this->import_from_json( $json_string );
    }

    /**
     * Exporta solo módulos activos
     */
    public function export_modules_only() {
        $data = array(
            'version' => W2WP_VERSION,
            'export_date' => current_time( 'mysql' ),
            'modules' => array(
                'informativo' => get_option( 'w2wp_mod_informativo', '0' ),
                'landing' => get_option( 'w2wp_mod_landing', '0' ),
            ),
        );
        
        return wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
    }

    /**
     * Exporta solo configuración global
     */
    public function export_global_settings_only() {
        $global_options = array(
            'w2wp_logo_principal',
            'w2wp_logo_contraste',
            'w2wp_favicon',
            'w2wp_brand_name',
            'w2wp_copyright_text',
            'w2wp_primary_color',
            'w2wp_secondary_color',
            'w2wp_whatsapp_contact',
            'w2wp_support_email',
            'w2wp_physical_address',
            'w2wp_social_instagram',
            'w2wp_social_linkedin',
            'w2wp_social_facebook',
            'w2wp_social_twitter',
            'w2wp_social_youtube',
            'w2wp_google_analytics_id',
            'w2wp_facebook_pixel_id',
            'w2wp_frontend_url',
            'w2wp_header_scripts',
            'w2wp_footer_scripts',
            'w2wp_signature_text',
            'w2wp_signature_url',
        );
        
        $options = array();
        foreach ( $global_options as $option_name ) {
            $options[ $option_name ] = get_option( $option_name, '' );
        }
        
        $data = array(
            'version' => W2WP_VERSION,
            'export_date' => current_time( 'mysql' ),
            'type' => 'global_settings',
            'options' => $options,
        );
        
        return wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
    }
}
