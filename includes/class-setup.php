<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Setup {

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
            $slug = strtolower( $page_title );
            
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
}
