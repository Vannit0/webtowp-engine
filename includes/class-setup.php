<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Setup {

    public static function activate_plugin() {
        // Verificar dependencias antes de activar
        require_once W2WP_INCLUDES_PATH . 'class-dependency-checker.php';
        $dependency_checker = W2WP_Dependency_Checker::get_instance();
        $dependency_checker->check_dependencies();
        
        // Si las dependencias no están satisfechas, desactivar el plugin
        if ( ! $dependency_checker->are_dependencies_met() ) {
            $dependency_checker->deactivate_if_dependencies_missing();
            return;
        }

        self::create_initial_pages();
        self::set_default_branding();
        self::create_deployment_log_table();
        self::create_security_tables();
        self::upgrade_database();
        
        // Marcar que el plugin se activó correctamente
        update_option( 'w2wp_activation_timestamp', current_time( 'timestamp' ) );
        update_option( 'w2wp_version', W2WP_VERSION );
    }

    public static function set_default_branding() {
        if ( ! get_option( 'w2wp_signature_text' ) ) {
            update_option( 'w2wp_signature_text', 'Desarrollado por WebToWP' );
        }
        if ( ! get_option( 'w2wp_signature_url' ) ) {
            update_option( 'w2wp_signature_url', 'https://webtowp.com' );
        }
        error_log( '[WebToWP Engine] Valores de marca blanca configurados por defecto.' );
    }

    public static function create_deployment_log_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'w2wp_deployment_logs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            action varchar(100) NOT NULL,
            status varchar(20) DEFAULT 'success' NOT NULL,
            response_code int(3) DEFAULT NULL,
            response_message text DEFAULT NULL,
            user_id bigint(20) DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY status (status),
            KEY timestamp (timestamp)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        
        error_log( '[WebToWP Engine] Tabla de logs de despliegue creada/verificada.' );
    }

    public static function create_initial_pages() {
        $pages = array(
            'Inicio',
            'Servicios',
            'Nosotros',
            'Proyectos',
            'Blog',
            'Contacto'
        );

        $created_pages = array();
        $existing_pages = array();

        foreach ( $pages as $page_title ) {
            // Sanitizar el título y crear slug válido
            if ( empty( $page_title ) || ! is_string( $page_title ) ) {
                continue;
            }
            
            $slug = sanitize_title( $page_title );
            if ( empty( $slug ) ) {
                $slug = strtolower( str_replace( ' ', '-', $page_title ) );
            }
            
            $page_exists = get_page_by_path( $slug );
            
            if ( ! $page_exists ) {
                $page_id = wp_insert_post( array(
                    'post_title'   => $page_title,
                    'post_name'    => $slug,
                    'post_content' => '',
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_author'  => 1
                ) );

                if ( ! is_wp_error( $page_id ) ) {
                    $created_pages[] = $page_title . ' (ID: ' . $page_id . ')';
                    error_log( '[WebToWP Engine] Página creada: ' . $page_title . ' - Slug: ' . $slug . ' - ID: ' . $page_id );
                } else {
                    error_log( '[WebToWP Engine] Error al crear página: ' . $page_title . ' - ' . $page_id->get_error_message() );
                }
            } else {
                $existing_pages[] = $page_title . ' (ID: ' . $page_exists->ID . ')';
                error_log( '[WebToWP Engine] Página ya existe: ' . $page_title . ' - Slug: ' . $slug . ' - ID: ' . $page_exists->ID );
            }
        }

        if ( ! empty( $created_pages ) ) {
            error_log( '[WebToWP Engine] Resumen - Páginas creadas: ' . implode( ', ', $created_pages ) );
        }

        if ( ! empty( $existing_pages ) ) {
            error_log( '[WebToWP Engine] Resumen - Páginas existentes: ' . implode( ', ', $existing_pages ) );
        }

        error_log( '[WebToWP Engine] Proceso de creación de páginas completado.' );
    }

    public static function create_security_tables() {
        // Crear tabla de API keys
        require_once W2WP_INCLUDES_PATH . 'class-api-key-manager.php';
        W2WP_API_Key_Manager::create_table();
        
        // Crear tabla de logs de seguridad
        require_once W2WP_INCLUDES_PATH . 'class-security-logger.php';
        W2WP_Security_Logger::create_table();
        
        error_log( '[WebToWP Engine] Tablas de seguridad creadas/verificadas.' );
    }

    public static function upgrade_database() {
        global $wpdb;
        
        $current_db_version = get_option( 'w2wp_db_version', '1.0.0' );
        
        // Verificar y actualizar tabla de deployment logs si es necesario
        $table_name = $wpdb->prefix . 'w2wp_deployment_logs';
        
        // Verificar si la tabla existe primero
        $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" );
        if ( ! $table_exists ) {
            error_log( '[WebToWP Engine] Tabla de deployment logs no existe, se creará en create_deployment_log_table()' );
            update_option( 'w2wp_db_version', W2WP_VERSION );
            return;
        }
        
        // Verificar si la columna status existe usando SHOW COLUMNS (más compatible)
        $columns = $wpdb->get_results( "SHOW COLUMNS FROM {$table_name} LIKE 'status'" );
        $column_exists = ! empty( $columns );
        
        if ( ! $column_exists ) {
            // Agregar columna status si no existe
            $result = $wpdb->query( 
                "ALTER TABLE {$table_name} 
                ADD COLUMN status varchar(20) DEFAULT 'success' NOT NULL AFTER action,
                ADD KEY status (status)"
            );
            
            if ( false !== $result ) {
                error_log( '[WebToWP Engine] Columna status agregada a la tabla de deployment logs.' );
            } else {
                error_log( '[WebToWP Engine] Error al agregar columna status: ' . $wpdb->last_error );
            }
        }
        
        // Actualizar versión de la base de datos
        update_option( 'w2wp_db_version', W2WP_VERSION );
        error_log( '[WebToWP Engine] Base de datos actualizada a versión ' . W2WP_VERSION );
    }
}
