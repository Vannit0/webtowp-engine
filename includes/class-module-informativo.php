<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Module_Informativo {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', array( $this, 'maybe_activate_module' ) );
        add_action( 'w2wp_modules_updated', array( $this, 'on_module_activation_hook' ), 10, 2 );
        add_action( 'admin_menu', array( $this, 'maybe_hide_cpt_menus' ), 999 );
    }

    public function on_module_activation_hook( $mod_informativo, $mod_landing ) {
        $this->on_module_activation( null );
    }

    public function maybe_activate_module() {
        $is_active = get_option( 'w2wp_mod_informativo', '0' );
        
        if ( $is_active === '1' ) {
            $this->register_cpts();
            add_action( 'acf/init', array( $this, 'register_module_fields' ) );
        }
    }

    public function on_module_activation( $post_id ) {
        $is_active = get_option( 'w2wp_mod_informativo', '0' );
        $was_active = get_option( 'w2wp_mod_informativo_activated', false );

        if ( $is_active === '1' && ! $was_active ) {
            $this->blueprint_pages();
            update_option( 'w2wp_mod_informativo_activated', true );
            // Marcar que se necesita flush en el próximo request
            set_transient( 'w2wp_flush_rewrite_rules', 1, 60 );
        } elseif ( $is_active === '0' && $was_active ) {
            update_option( 'w2wp_mod_informativo_activated', false );
            set_transient( 'w2wp_flush_rewrite_rules', 1, 60 );
        }
    }

    private function register_cpts() {
        register_post_type( 'w2wp_servicios', array(
            'labels' => array(
                'name' => __( 'Servicios', 'webtowp-engine' ),
                'singular_name' => __( 'Servicio', 'webtowp-engine' ),
                'add_new' => __( 'Añadir Nuevo', 'webtowp-engine' ),
                'add_new_item' => __( 'Añadir Nuevo Servicio', 'webtowp-engine' ),
                'edit_item' => __( 'Editar Servicio', 'webtowp-engine' ),
                'new_item' => __( 'Nuevo Servicio', 'webtowp-engine' ),
                'view_item' => __( 'Ver Servicio', 'webtowp-engine' ),
                'search_items' => __( 'Buscar Servicios', 'webtowp-engine' ),
                'not_found' => __( 'No se encontraron servicios', 'webtowp-engine' ),
            ),
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-portfolio',
            'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
            'rewrite' => array( 'slug' => 'servicios' ),
        ) );

        register_post_type( 'w2wp_recursos', array(
            'labels' => array(
                'name' => __( 'Recursos', 'webtowp-engine' ),
                'singular_name' => __( 'Recurso', 'webtowp-engine' ),
                'add_new' => __( 'Añadir Nuevo', 'webtowp-engine' ),
                'add_new_item' => __( 'Añadir Nuevo Recurso', 'webtowp-engine' ),
                'edit_item' => __( 'Editar Recurso', 'webtowp-engine' ),
                'new_item' => __( 'Nuevo Recurso', 'webtowp-engine' ),
                'view_item' => __( 'Ver Recurso', 'webtowp-engine' ),
                'search_items' => __( 'Buscar Recursos', 'webtowp-engine' ),
                'not_found' => __( 'No se encontraron recursos', 'webtowp-engine' ),
            ),
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-book',
            'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
            'rewrite' => array( 'slug' => 'recursos' ),
        ) );

        add_action( 'acf/init', array( $this, 'register_cpt_fields' ) );
    }

    private function blueprint_pages() {
        $pages = array(
            'inicio' => array(
                'title' => 'Inicio',
                'slug' => 'inicio',
                'option' => 'w2wp_page_inicio'
            ),
            'sobre-nosotros' => array(
                'title' => 'Sobre Nosotros',
                'slug' => 'sobre-nosotros',
                'option' => 'w2wp_page_sobre_nosotros'
            ),
            'servicios' => array(
                'title' => 'Servicios',
                'slug' => 'servicios',
                'option' => 'w2wp_page_servicios'
            ),
            'blog' => array(
                'title' => 'Blog',
                'slug' => 'blog',
                'option' => 'w2wp_page_blog'
            ),
            'recursos' => array(
                'title' => 'Recursos',
                'slug' => 'recursos',
                'option' => 'w2wp_page_recursos'
            ),
            'contacto' => array(
                'title' => 'Contacto',
                'slug' => 'contacto',
                'option' => 'w2wp_page_contacto'
            ),
            'faq' => array(
                'title' => 'FAQ',
                'slug' => 'faq',
                'option' => 'w2wp_page_faq'
            ),
            'legales' => array(
                'title' => 'Legales',
                'slug' => 'legales',
                'option' => 'w2wp_page_legales'
            ),
        );

        foreach ( $pages as $key => $page_data ) {
            $is_page_active = get_option( $page_data['option'], '0' );
            
            if ( $is_page_active === '1' ) {
                $existing_page = get_page_by_path( $page_data['slug'] );
                
                if ( ! $existing_page ) {
                    $page_id = wp_insert_post( array(
                        'post_title' => $page_data['title'],
                        'post_name' => $page_data['slug'],
                        'post_status' => 'publish',
                        'post_type' => 'page',
                        'post_content' => '',
                    ) );
                    
                    // Si es la página de inicio, configurarla como front page
                    if ( $key === 'inicio' && $page_id && ! is_wp_error( $page_id ) ) {
                        update_option( 'show_on_front', 'page' );
                        update_option( 'page_on_front', $page_id );
                        error_log( '[WebToWP Module Informativo] Página de inicio configurada como front page (ID: ' . $page_id . ')' );
                    }
                } else {
                    // Si la página ya existe y es inicio, asegurar que esté configurada como front page
                    if ( $key === 'inicio' ) {
                        update_option( 'show_on_front', 'page' );
                        update_option( 'page_on_front', $existing_page->ID );
                        error_log( '[WebToWP Module Informativo] Página de inicio existente configurada como front page (ID: ' . $existing_page->ID . ')' );
                    }
                }
            }
        }

        // Marcar que se necesita flush en el próximo request
        set_transient( 'w2wp_flush_rewrite_rules', 1, 60 );
    }

    public function register_cpt_fields() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        acf_add_local_field_group( array(
            'key' => 'group_servicios_fields',
            'title' => 'Información del Servicio',
            'fields' => array(
                array(
                    'key' => 'field_servicio_icono',
                    'label' => 'Icono (Texto/SVG)',
                    'name' => 'servicio_icono',
                    'type' => 'text',
                    'instructions' => 'Nombre del icono de Lucide o código SVG',
                    'placeholder' => 'Ej: Zap, Rocket, Star',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_servicio_resumen',
                    'label' => 'Resumen Corto',
                    'name' => 'servicio_resumen',
                    'type' => 'textarea',
                    'rows' => 3,
                    'instructions' => 'Descripción breve del servicio',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_servicio_destacado',
                    'label' => '¿Es destacado?',
                    'name' => 'servicio_destacado',
                    'type' => 'true_false',
                    'instructions' => 'Marcar si este servicio debe destacarse',
                    'default_value' => 0,
                    'show_in_rest' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'w2wp_servicios',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'side',
            'style' => 'default',
            'show_in_rest' => 1,
        ) );

        acf_add_local_field_group( array(
            'key' => 'group_recursos_fields',
            'title' => 'Información del Recurso',
            'fields' => array(
                array(
                    'key' => 'field_recurso_tipo',
                    'label' => 'Tipo de Recurso',
                    'name' => 'recurso_tipo',
                    'type' => 'select',
                    'choices' => array(
                        'guia' => 'Guía',
                        'herramienta' => 'Herramienta',
                        'ebook' => 'Ebook',
                    ),
                    'default_value' => 'guia',
                    'required' => 1,
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_recurso_link',
                    'label' => 'Link de Acceso',
                    'name' => 'recurso_link',
                    'type' => 'url',
                    'instructions' => 'URL del recurso o descarga',
                    'placeholder' => 'https://',
                    'required' => 1,
                    'show_in_rest' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'w2wp_recursos',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'side',
            'style' => 'default',
            'show_in_rest' => 1,
        ) );
    }

    public function register_module_fields() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        if ( get_option( 'w2wp_page_sobre_nosotros', '0' ) === '1' ) {
            $this->register_sobre_nosotros_fields();
        }
        
        if ( get_option( 'w2wp_page_faq', '0' ) === '1' ) {
            $this->register_faq_fields();
        }
        
        if ( get_option( 'w2wp_page_contacto', '0' ) === '1' ) {
            $this->register_contacto_fields();
        }
    }

    private function register_sobre_nosotros_fields() {
        acf_add_local_field_group( array(
            'key' => 'group_sobre_nosotros',
            'title' => 'Sobre Nosotros',
            'fields' => array(
                array(
                    'key' => 'field_mision',
                    'label' => 'Misión',
                    'name' => 'mision',
                    'type' => 'textarea',
                    'rows' => 5,
                    'instructions' => 'Describe la misión de la empresa',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_vision',
                    'label' => 'Visión',
                    'name' => 'vision',
                    'type' => 'textarea',
                    'rows' => 5,
                    'instructions' => 'Describe la visión de la empresa',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_equipo',
                    'label' => 'Equipo',
                    'name' => 'equipo',
                    'type' => 'repeater',
                    'layout' => 'block',
                    'button_label' => 'Agregar Miembro del Equipo',
                    'show_in_rest' => 1,
                    'sub_fields' => array(
                        array(
                            'key' => 'field_equipo_nombre',
                            'label' => 'Nombre',
                            'name' => 'nombre',
                            'type' => 'text',
                            'required' => 1,
                            'show_in_rest' => 1,
                        ),
                        array(
                            'key' => 'field_equipo_puesto',
                            'label' => 'Puesto',
                            'name' => 'puesto',
                            'type' => 'text',
                            'show_in_rest' => 1,
                        ),
                        array(
                            'key' => 'field_equipo_foto',
                            'label' => 'Foto',
                            'name' => 'foto',
                            'type' => 'image',
                            'return_format' => 'array',
                            'preview_size' => 'thumbnail',
                            'show_in_rest' => 1,
                        ),
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'page',
                    ),
                    array(
                        'param' => 'page_template',
                        'operator' => '==',
                        'value' => 'default',
                    ),
                    array(
                        'param' => 'page',
                        'operator' => '==',
                        'value' => get_page_by_path( 'sobre-nosotros' ) ? get_page_by_path( 'sobre-nosotros' )->ID : 0,
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'show_in_rest' => 1,
        ) );
    }

    private function register_contacto_fields() {
        acf_add_local_field_group( array(
            'key' => 'group_contacto',
            'title' => 'Información de Contacto',
            'fields' => array(
                array(
                    'key' => 'field_contacto_telefono',
                    'label' => 'Teléfono',
                    'name' => 'contacto_telefono',
                    'type' => 'text',
                    'placeholder' => '+1 234 567 8900',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_contacto_email',
                    'label' => 'Email de Soporte',
                    'name' => 'contacto_email',
                    'type' => 'email',
                    'placeholder' => 'soporte@empresa.com',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_contacto_whatsapp',
                    'label' => 'WhatsApp',
                    'name' => 'contacto_whatsapp',
                    'type' => 'text',
                    'instructions' => 'Número de WhatsApp (incluir código de país)',
                    'placeholder' => '+1 234 567 8900',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_contacto_maps_iframe',
                    'label' => 'Iframe de Google Maps',
                    'name' => 'contacto_maps_iframe',
                    'type' => 'textarea',
                    'rows' => 5,
                    'instructions' => 'Pega el código iframe de Google Maps',
                    'placeholder' => '<iframe src="https://www.google.com/maps/embed?pb=..."></iframe>',
                    'show_in_rest' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'page',
                    ),
                    array(
                        'param' => 'page',
                        'operator' => '==',
                        'value' => get_page_by_path( 'contacto' ) ? get_page_by_path( 'contacto' )->ID : 0,
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'show_in_rest' => 1,
        ) );
    }

    private function register_faq_fields() {
        acf_add_local_field_group( array(
            'key' => 'group_faq',
            'title' => 'Preguntas Frecuentes',
            'fields' => array(
                array(
                    'key' => 'field_faq_items',
                    'label' => 'Preguntas y Respuestas',
                    'name' => 'faq_items',
                    'type' => 'repeater',
                    'layout' => 'block',
                    'button_label' => 'Agregar Pregunta',
                    'show_in_rest' => 1,
                    'sub_fields' => array(
                        array(
                            'key' => 'field_faq_pregunta',
                            'label' => 'Pregunta',
                            'name' => 'pregunta',
                            'type' => 'text',
                            'required' => 1,
                            'show_in_rest' => 1,
                        ),
                        array(
                            'key' => 'field_faq_respuesta',
                            'label' => 'Respuesta',
                            'name' => 'respuesta',
                            'type' => 'textarea',
                            'rows' => 4,
                            'required' => 1,
                            'show_in_rest' => 1,
                        ),
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'page',
                    ),
                    array(
                        'param' => 'page',
                        'operator' => '==',
                        'value' => get_page_by_path( 'faq' ) ? get_page_by_path( 'faq' )->ID : 0,
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'show_in_rest' => 1,
        ) );
    }

    private function get_page_id_by_slug( $slug ) {
        $page = get_page_by_path( $slug );
        return $page ? $page->ID : 0;
    }

    public function maybe_hide_cpt_menus() {
        $is_active = get_option( 'w2wp_mod_informativo', '0' );
        
        if ( $is_active !== '1' ) {
            remove_menu_page( 'edit.php?post_type=w2wp_servicios' );
            remove_menu_page( 'edit.php?post_type=w2wp_recursos' );
        }
    }
}
