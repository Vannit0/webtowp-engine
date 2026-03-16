<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Module_Manager {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'acf/init', array( $this, 'load_active_modules' ) );
        add_action( 'init', array( $this, 'register_custom_post_types' ) );
        add_action( 'acf/save_post', array( $this, 'check_module_activation' ), 20 );
    }

    public function load_active_modules() {
        if ( ! function_exists( 'get_field' ) ) {
            return;
        }

        $mod_landing = get_field( 'mod_landing', 'option' );
        $mod_dentista = get_field( 'mod_dentista', 'option' );
        $mod_barberia = get_field( 'mod_barberia', 'option' );
        $mod_informativo = get_field( 'mod_informativo', 'option' );

        if ( $mod_landing ) {
            $this->register_landing_page_fields();
        }

        if ( $mod_dentista ) {
            $this->register_dentista_fields();
        }

        if ( $mod_barberia ) {
        }

        if ( $mod_informativo ) {
            $this->register_informativo_fields();
        }
    }

    public function register_custom_post_types() {
        if ( ! function_exists( 'get_field' ) ) {
            return;
        }

        $mod_dentista = get_field( 'mod_dentista', 'option' );
        $mod_informativo = get_field( 'mod_informativo', 'option' );

        if ( $mod_dentista ) {
            $this->register_servicios_medicos_cpt();
        }

        if ( $mod_informativo ) {
            $this->register_servicios_cpt();
            $this->register_recursos_cpt();
        }
    }

    private function register_landing_page_fields() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        $home_page_id = $this->get_home_page_id();

        acf_add_local_field_group( array(
            'key' => 'group_landing_hero',
            'title' => 'Landing Page - Hero Section',
            'fields' => array(
                array(
                    'key' => 'field_landing_hero_titulo',
                    'label' => 'Título Principal (H1)',
                    'name' => 'landing_hero_titulo',
                    'type' => 'text',
                    'required' => 1,
                    'placeholder' => 'Ej: Transforma tu Negocio Digital',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_hero_subtitulo',
                    'label' => 'Subtítulo',
                    'name' => 'landing_hero_subtitulo',
                    'type' => 'textarea',
                    'rows' => 3,
                    'placeholder' => 'Descripción breve y atractiva',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_hero_boton_1_texto',
                    'label' => 'Botón 1 - Texto',
                    'name' => 'landing_hero_boton_1_texto',
                    'type' => 'text',
                    'default_value' => 'Comenzar Ahora',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_hero_boton_1_url',
                    'label' => 'Botón 1 - URL',
                    'name' => 'landing_hero_boton_1_url',
                    'type' => 'url',
                    'placeholder' => 'https://',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_hero_boton_2_texto',
                    'label' => 'Botón 2 - Texto',
                    'name' => 'landing_hero_boton_2_texto',
                    'type' => 'text',
                    'default_value' => 'Más Información',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_hero_boton_2_url',
                    'label' => 'Botón 2 - URL',
                    'name' => 'landing_hero_boton_2_url',
                    'type' => 'url',
                    'placeholder' => 'https://',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_hero_imagen',
                    'label' => 'Imagen Hero',
                    'name' => 'landing_hero_imagen',
                    'type' => 'image',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'all',
                    'show_in_rest' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'page',
                        'operator' => '==',
                        'value' => $home_page_id,
                    ),
                ),
                array(
                    array(
                        'param' => 'page_type',
                        'operator' => '==',
                        'value' => 'front_page',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'show_in_rest' => 1,
        ) );

        acf_add_local_field_group( array(
            'key' => 'group_landing_features',
            'title' => 'Landing Page - Features',
            'fields' => array(
                array(
                    'key' => 'field_landing_features_titulo',
                    'label' => 'Título de Sección Features',
                    'name' => 'landing_features_titulo',
                    'type' => 'text',
                    'default_value' => 'Nuestras Características',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_feature_1_icono',
                    'label' => 'Feature 1 - Icono',
                    'name' => 'landing_feature_1_icono',
                    'type' => 'image',
                    'return_format' => 'id',
                    'preview_size' => 'thumbnail',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_feature_1_titulo',
                    'label' => 'Feature 1 - Título',
                    'name' => 'landing_feature_1_titulo',
                    'type' => 'text',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_feature_1_descripcion',
                    'label' => 'Feature 1 - Descripción',
                    'name' => 'landing_feature_1_descripcion',
                    'type' => 'textarea',
                    'rows' => 2,
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_feature_2_icono',
                    'label' => 'Feature 2 - Icono',
                    'name' => 'landing_feature_2_icono',
                    'type' => 'image',
                    'return_format' => 'id',
                    'preview_size' => 'thumbnail',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_feature_2_titulo',
                    'label' => 'Feature 2 - Título',
                    'name' => 'landing_feature_2_titulo',
                    'type' => 'text',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_feature_2_descripcion',
                    'label' => 'Feature 2 - Descripción',
                    'name' => 'landing_feature_2_descripcion',
                    'type' => 'textarea',
                    'rows' => 2,
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_feature_3_icono',
                    'label' => 'Feature 3 - Icono',
                    'name' => 'landing_feature_3_icono',
                    'type' => 'image',
                    'return_format' => 'id',
                    'preview_size' => 'thumbnail',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_feature_3_titulo',
                    'label' => 'Feature 3 - Título',
                    'name' => 'landing_feature_3_titulo',
                    'type' => 'text',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_feature_3_descripcion',
                    'label' => 'Feature 3 - Descripción',
                    'name' => 'landing_feature_3_descripcion',
                    'type' => 'textarea',
                    'rows' => 2,
                    'show_in_rest' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'page',
                        'operator' => '==',
                        'value' => $home_page_id,
                    ),
                ),
                array(
                    array(
                        'param' => 'page_type',
                        'operator' => '==',
                        'value' => 'front_page',
                    ),
                ),
            ),
            'menu_order' => 1,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'show_in_rest' => 1,
        ) );

        acf_add_local_field_group( array(
            'key' => 'group_landing_cta',
            'title' => 'Landing Page - Call to Action',
            'fields' => array(
                array(
                    'key' => 'field_landing_cta_titulo',
                    'label' => 'CTA - Título',
                    'name' => 'landing_cta_titulo',
                    'type' => 'text',
                    'default_value' => '¿Listo para comenzar?',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_cta_descripcion',
                    'label' => 'CTA - Descripción',
                    'name' => 'landing_cta_descripcion',
                    'type' => 'textarea',
                    'rows' => 2,
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_cta_boton_texto',
                    'label' => 'CTA - Texto del Botón',
                    'name' => 'landing_cta_boton_texto',
                    'type' => 'text',
                    'default_value' => 'Contactar Ahora',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_cta_boton_url',
                    'label' => 'CTA - URL del Botón',
                    'name' => 'landing_cta_boton_url',
                    'type' => 'url',
                    'placeholder' => 'https://',
                    'show_in_rest' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'page',
                        'operator' => '==',
                        'value' => $home_page_id,
                    ),
                ),
                array(
                    array(
                        'param' => 'page_type',
                        'operator' => '==',
                        'value' => 'front_page',
                    ),
                ),
            ),
            'menu_order' => 2,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'show_in_rest' => 1,
        ) );
    }

    private function register_servicios_medicos_cpt() {
        $labels = array(
            'name'                  => _x( 'Servicios Médicos', 'Post type general name', 'webtowp-engine' ),
            'singular_name'         => _x( 'Servicio Médico', 'Post type singular name', 'webtowp-engine' ),
            'menu_name'             => _x( 'Servicios Médicos', 'Admin Menu text', 'webtowp-engine' ),
            'name_admin_bar'        => _x( 'Servicio Médico', 'Add New on Toolbar', 'webtowp-engine' ),
            'add_new'               => __( 'Agregar Nuevo', 'webtowp-engine' ),
            'add_new_item'          => __( 'Agregar Nuevo Servicio Médico', 'webtowp-engine' ),
            'new_item'              => __( 'Nuevo Servicio Médico', 'webtowp-engine' ),
            'edit_item'             => __( 'Editar Servicio Médico', 'webtowp-engine' ),
            'view_item'             => __( 'Ver Servicio Médico', 'webtowp-engine' ),
            'all_items'             => __( 'Todos los Servicios', 'webtowp-engine' ),
            'search_items'          => __( 'Buscar Servicios Médicos', 'webtowp-engine' ),
            'parent_item_colon'     => __( 'Servicios Médicos Padre:', 'webtowp-engine' ),
            'not_found'             => __( 'No se encontraron servicios médicos.', 'webtowp-engine' ),
            'not_found_in_trash'    => __( 'No se encontraron servicios médicos en la papelera.', 'webtowp-engine' ),
            'featured_image'        => _x( 'Imagen del Servicio', 'Overrides the "Featured Image" phrase', 'webtowp-engine' ),
            'set_featured_image'    => _x( 'Establecer imagen del servicio', 'Overrides the "Set featured image" phrase', 'webtowp-engine' ),
            'remove_featured_image' => _x( 'Eliminar imagen del servicio', 'Overrides the "Remove featured image" phrase', 'webtowp-engine' ),
            'use_featured_image'    => _x( 'Usar como imagen del servicio', 'Overrides the "Use as featured image" phrase', 'webtowp-engine' ),
            'archives'              => _x( 'Archivo de Servicios Médicos', 'The post type archive label used in nav menus', 'webtowp-engine' ),
            'insert_into_item'      => _x( 'Insertar en servicio médico', 'Overrides the "Insert into post" phrase', 'webtowp-engine' ),
            'uploaded_to_this_item' => _x( 'Subido a este servicio médico', 'Overrides the "Uploaded to this post" phrase', 'webtowp-engine' ),
            'filter_items_list'     => _x( 'Filtrar lista de servicios médicos', 'Screen reader text for the filter links', 'webtowp-engine' ),
            'items_list_navigation' => _x( 'Navegación de lista de servicios médicos', 'Screen reader text for the pagination', 'webtowp-engine' ),
            'items_list'            => _x( 'Lista de servicios médicos', 'Screen reader text for the items list', 'webtowp-engine' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'servicio-medico' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-heart',
            'show_in_rest'       => true,
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        );

        register_post_type( 'servicio_medico', $args );
    }

    private function register_dentista_fields() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        acf_add_local_field_group( array(
            'key' => 'group_horarios_atencion',
            'title' => 'Horarios de Atención',
            'fields' => array(
                array(
                    'key' => 'field_horario_lunes_viernes',
                    'label' => 'Lunes a Viernes',
                    'name' => 'horario_lunes_viernes',
                    'type' => 'text',
                    'placeholder' => 'Ej: 9:00 AM - 6:00 PM',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_horario_sabado',
                    'label' => 'Sábado',
                    'name' => 'horario_sabado',
                    'type' => 'text',
                    'placeholder' => 'Ej: 9:00 AM - 2:00 PM',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_horario_domingo',
                    'label' => 'Domingo',
                    'name' => 'horario_domingo',
                    'type' => 'text',
                    'placeholder' => 'Ej: Cerrado',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_telefono_emergencias',
                    'label' => 'Teléfono de Emergencias',
                    'name' => 'telefono_emergencias',
                    'type' => 'text',
                    'placeholder' => 'Ej: +1 234 567 8900',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_email_citas',
                    'label' => 'Email para Citas',
                    'name' => 'email_citas',
                    'type' => 'email',
                    'placeholder' => 'citas@clinica.com',
                    'show_in_rest' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'servicio_medico',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'side',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'show_in_rest' => 1,
        ) );
    }

    private function register_servicios_cpt() {
        $labels = array(
            'name'                  => _x( 'Servicios', 'Post type general name', 'webtowp-engine' ),
            'singular_name'         => _x( 'Servicio', 'Post type singular name', 'webtowp-engine' ),
            'menu_name'             => _x( 'Servicios', 'Admin Menu text', 'webtowp-engine' ),
            'add_new'               => __( 'Agregar Nuevo', 'webtowp-engine' ),
            'add_new_item'          => __( 'Agregar Nuevo Servicio', 'webtowp-engine' ),
            'edit_item'             => __( 'Editar Servicio', 'webtowp-engine' ),
            'view_item'             => __( 'Ver Servicio', 'webtowp-engine' ),
            'all_items'             => __( 'Todos los Servicios', 'webtowp-engine' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'servicio' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-portfolio',
            'show_in_rest'       => true,
            'supports'           => array( 'title', 'editor', 'thumbnail' ),
        );

        register_post_type( 'w2wp_servicios', $args );

        if ( function_exists( 'acf_add_local_field_group' ) ) {
            acf_add_local_field_group( array(
                'key' => 'group_servicios_fields',
                'title' => 'Información del Servicio',
                'fields' => array(
                    array(
                        'key' => 'field_servicio_icono',
                        'label' => 'Icono (Lucide o SVG)',
                        'name' => 'servicio_icono',
                        'type' => 'text',
                        'instructions' => 'Nombre del icono de Lucide (ej: "Zap", "Rocket") o código SVG',
                        'placeholder' => 'Ej: Zap',
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
        }
    }

    private function register_recursos_cpt() {
        $labels = array(
            'name'                  => _x( 'Recursos', 'Post type general name', 'webtowp-engine' ),
            'singular_name'         => _x( 'Recurso', 'Post type singular name', 'webtowp-engine' ),
            'menu_name'             => _x( 'Recursos', 'Admin Menu text', 'webtowp-engine' ),
            'add_new'               => __( 'Agregar Nuevo', 'webtowp-engine' ),
            'add_new_item'          => __( 'Agregar Nuevo Recurso', 'webtowp-engine' ),
            'edit_item'             => __( 'Editar Recurso', 'webtowp-engine' ),
            'view_item'             => __( 'Ver Recurso', 'webtowp-engine' ),
            'all_items'             => __( 'Todos los Recursos', 'webtowp-engine' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'recurso' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 21,
            'menu_icon'          => 'dashicons-download',
            'show_in_rest'       => true,
            'supports'           => array( 'title', 'editor', 'thumbnail' ),
            'taxonomies'         => array( 'category' ),
        );

        register_post_type( 'w2wp_recursos', $args );

        if ( function_exists( 'acf_add_local_field_group' ) ) {
            acf_add_local_field_group( array(
                'key' => 'group_recursos_fields',
                'title' => 'Información del Recurso',
                'fields' => array(
                    array(
                        'key' => 'field_recurso_tipo',
                        'label' => 'Tipo de Recurso',
                        'name' => 'recurso_tipo',
                        'type' => 'select',
                        'instructions' => 'Selecciona el tipo de recurso',
                        'choices' => array(
                            'guia' => 'Guía',
                            'herramienta' => 'Herramienta',
                            'plantilla' => 'Plantilla',
                            'ebook' => 'eBook',
                            'video' => 'Video',
                            'otro' => 'Otro',
                        ),
                        'default_value' => 'guia',
                        'required' => 1,
                        'show_in_rest' => 1,
                    ),
                    array(
                        'key' => 'field_recurso_url_acceso',
                        'label' => 'URL de Descarga/Acceso',
                        'name' => 'recurso_url_acceso',
                        'type' => 'url',
                        'instructions' => 'URL del archivo o recurso descargable/accesible',
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
    }

    private function register_informativo_fields() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        $nosotros_page_id = $this->get_page_id_by_slug( 'nosotros' );
        $contacto_page_id = $this->get_page_id_by_slug( 'contacto' );
        $faq_page_id = $this->get_page_id_by_slug( 'faq' );
        $home_page_id = $this->get_home_page_id();

        if ( $nosotros_page_id ) {
            $this->register_nosotros_fields( $nosotros_page_id );
        }

        if ( $contacto_page_id ) {
            $this->register_contacto_fields( $contacto_page_id );
        }

        if ( $faq_page_id ) {
            $this->register_faq_fields( $faq_page_id );
        }

        if ( $home_page_id ) {
            $this->register_home_informativo_fields( $home_page_id );
        }
    }

    private function register_nosotros_fields( $page_id ) {
        $is_acf_pro = defined( 'ACF_PRO' ) && ACF_PRO;

        acf_add_local_field_group( array(
            'key' => 'group_nosotros',
            'title' => 'Información de la Empresa',
            'fields' => array(
                array(
                    'key' => 'field_nosotros_historia',
                    'label' => 'Historia',
                    'name' => 'nosotros_historia',
                    'type' => 'wysiwyg',
                    'instructions' => 'Cuéntanos la historia de tu empresa',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_nosotros_mision',
                    'label' => 'Misión',
                    'name' => 'nosotros_mision',
                    'type' => 'textarea',
                    'rows' => 4,
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_nosotros_vision',
                    'label' => 'Visión',
                    'name' => 'nosotros_vision',
                    'type' => 'textarea',
                    'rows' => 4,
                    'show_in_rest' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'page',
                        'operator' => '==',
                        'value' => $page_id,
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'show_in_rest' => 1,
        ) );

        if ( $is_acf_pro ) {
            acf_add_local_field_group( array(
                'key' => 'group_equipo',
                'title' => 'Miembros del Equipo',
                'fields' => array(
                    array(
                        'key' => 'field_equipo_repeater',
                        'label' => 'Equipo',
                        'name' => 'equipo_miembros',
                        'type' => 'repeater',
                        'layout' => 'block',
                        'button_label' => 'Agregar Miembro',
                        'show_in_rest' => 1,
                        'sub_fields' => array(
                            array(
                                'key' => 'field_miembro_nombre',
                                'label' => 'Nombre',
                                'name' => 'nombre',
                                'type' => 'text',
                                'required' => 1,
                                'show_in_rest' => 1,
                            ),
                            array(
                                'key' => 'field_miembro_cargo',
                                'label' => 'Cargo',
                                'name' => 'cargo',
                                'type' => 'text',
                                'show_in_rest' => 1,
                            ),
                            array(
                                'key' => 'field_miembro_bio',
                                'label' => 'Biografía',
                                'name' => 'biografia',
                                'type' => 'textarea',
                                'rows' => 3,
                                'show_in_rest' => 1,
                            ),
                            array(
                                'key' => 'field_miembro_foto',
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
                            'param' => 'page',
                            'operator' => '==',
                            'value' => $page_id,
                        ),
                    ),
                ),
                'menu_order' => 1,
                'position' => 'normal',
                'style' => 'default',
                'show_in_rest' => 1,
            ) );
        }
    }

    private function register_contacto_fields( $page_id ) {
        acf_add_local_field_group( array(
            'key' => 'group_contacto',
            'title' => 'Información de Contacto',
            'fields' => array(
                array(
                    'key' => 'field_contacto_email_soporte',
                    'label' => 'Email de Soporte',
                    'name' => 'contacto_email_soporte',
                    'type' => 'email',
                    'placeholder' => 'soporte@empresa.com',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_contacto_telefono',
                    'label' => 'Teléfono',
                    'name' => 'contacto_telefono',
                    'type' => 'text',
                    'placeholder' => '+1 234 567 8900',
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
                    'key' => 'field_contacto_maps_lat',
                    'label' => 'Google Maps - Latitud',
                    'name' => 'contacto_maps_latitud',
                    'type' => 'text',
                    'instructions' => 'Coordenada de latitud para Google Maps',
                    'placeholder' => 'Ej: -34.603722',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_contacto_maps_lng',
                    'label' => 'Google Maps - Longitud',
                    'name' => 'contacto_maps_longitud',
                    'type' => 'text',
                    'instructions' => 'Coordenada de longitud para Google Maps',
                    'placeholder' => 'Ej: -58.381592',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_contacto_facebook',
                    'label' => 'Facebook URL',
                    'name' => 'contacto_facebook',
                    'type' => 'url',
                    'placeholder' => 'https://facebook.com/tupagina',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_contacto_instagram',
                    'label' => 'Instagram URL',
                    'name' => 'contacto_instagram',
                    'type' => 'url',
                    'placeholder' => 'https://instagram.com/tuusuario',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_contacto_twitter',
                    'label' => 'Twitter/X URL',
                    'name' => 'contacto_twitter',
                    'type' => 'url',
                    'placeholder' => 'https://twitter.com/tuusuario',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_contacto_linkedin',
                    'label' => 'LinkedIn URL',
                    'name' => 'contacto_linkedin',
                    'type' => 'url',
                    'placeholder' => 'https://linkedin.com/company/tuempresa',
                    'show_in_rest' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'page',
                        'operator' => '==',
                        'value' => $page_id,
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'show_in_rest' => 1,
        ) );
    }

    private function register_faq_fields( $page_id ) {
        $is_acf_pro = defined( 'ACF_PRO' ) && ACF_PRO;

        if ( $is_acf_pro ) {
            acf_add_local_field_group( array(
                'key' => 'group_faq',
                'title' => 'Preguntas Frecuentes',
                'fields' => array(
                    array(
                        'key' => 'field_faq_repeater',
                        'label' => 'FAQs',
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
                                'type' => 'wysiwyg',
                                'tabs' => 'visual',
                                'toolbar' => 'basic',
                                'media_upload' => 0,
                                'show_in_rest' => 1,
                            ),
                        ),
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param' => 'page',
                            'operator' => '==',
                            'value' => $page_id,
                        ),
                    ),
                ),
                'menu_order' => 0,
                'position' => 'normal',
                'style' => 'default',
                'show_in_rest' => 1,
            ) );
        }
    }

    private function register_home_informativo_fields( $page_id ) {
        acf_add_local_field_group( array(
            'key' => 'group_home_informativo_hero',
            'title' => 'Sitio Informativo - Hero',
            'fields' => array(
                array(
                    'key' => 'field_info_hero_titulo',
                    'label' => 'Título Hero',
                    'name' => 'info_hero_titulo',
                    'type' => 'text',
                    'required' => 1,
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_hero_subtitulo',
                    'label' => 'Subtítulo Hero',
                    'name' => 'info_hero_subtitulo',
                    'type' => 'textarea',
                    'rows' => 3,
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_hero_imagen',
                    'label' => 'Imagen Hero',
                    'name' => 'info_hero_imagen',
                    'type' => 'image',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'show_in_rest' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'page',
                        'operator' => '==',
                        'value' => $page_id,
                    ),
                ),
                array(
                    array(
                        'param' => 'page_type',
                        'operator' => '==',
                        'value' => 'front_page',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'show_in_rest' => 1,
        ) );

        acf_add_local_field_group( array(
            'key' => 'group_home_informativo_beneficios',
            'title' => 'Sitio Informativo - Beneficios',
            'fields' => array(
                array(
                    'key' => 'field_info_beneficios_titulo',
                    'label' => 'Título de Beneficios',
                    'name' => 'info_beneficios_titulo',
                    'type' => 'text',
                    'default_value' => 'Nuestros Beneficios',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_beneficio_1_icono',
                    'label' => 'Beneficio 1 - Icono',
                    'name' => 'info_beneficio_1_icono',
                    'type' => 'image',
                    'return_format' => 'id',
                    'preview_size' => 'thumbnail',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_beneficio_1_titulo',
                    'label' => 'Beneficio 1 - Título',
                    'name' => 'info_beneficio_1_titulo',
                    'type' => 'text',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_beneficio_1_desc',
                    'label' => 'Beneficio 1 - Descripción',
                    'name' => 'info_beneficio_1_descripcion',
                    'type' => 'textarea',
                    'rows' => 2,
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_beneficio_2_icono',
                    'label' => 'Beneficio 2 - Icono',
                    'name' => 'info_beneficio_2_icono',
                    'type' => 'image',
                    'return_format' => 'id',
                    'preview_size' => 'thumbnail',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_beneficio_2_titulo',
                    'label' => 'Beneficio 2 - Título',
                    'name' => 'info_beneficio_2_titulo',
                    'type' => 'text',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_beneficio_2_desc',
                    'label' => 'Beneficio 2 - Descripción',
                    'name' => 'info_beneficio_2_descripcion',
                    'type' => 'textarea',
                    'rows' => 2,
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_beneficio_3_icono',
                    'label' => 'Beneficio 3 - Icono',
                    'name' => 'info_beneficio_3_icono',
                    'type' => 'image',
                    'return_format' => 'id',
                    'preview_size' => 'thumbnail',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_beneficio_3_titulo',
                    'label' => 'Beneficio 3 - Título',
                    'name' => 'info_beneficio_3_titulo',
                    'type' => 'text',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_beneficio_3_desc',
                    'label' => 'Beneficio 3 - Descripción',
                    'name' => 'info_beneficio_3_descripcion',
                    'type' => 'textarea',
                    'rows' => 2,
                    'show_in_rest' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'page',
                        'operator' => '==',
                        'value' => $page_id,
                    ),
                ),
                array(
                    array(
                        'param' => 'page_type',
                        'operator' => '==',
                        'value' => 'front_page',
                    ),
                ),
            ),
            'menu_order' => 1,
            'position' => 'normal',
            'style' => 'default',
            'show_in_rest' => 1,
        ) );

        acf_add_local_field_group( array(
            'key' => 'group_home_informativo_servicios',
            'title' => 'Sitio Informativo - Servicios Destacados',
            'fields' => array(
                array(
                    'key' => 'field_info_servicios_titulo',
                    'label' => 'Título de Servicios',
                    'name' => 'info_servicios_titulo',
                    'type' => 'text',
                    'default_value' => 'Nuestros Servicios',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_servicios_destacados',
                    'label' => 'Servicios Destacados',
                    'name' => 'info_servicios_destacados',
                    'type' => 'relationship',
                    'post_type' => array( 'w2wp_servicios' ),
                    'filters' => array( 'search' ),
                    'max' => 6,
                    'return_format' => 'id',
                    'show_in_rest' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'page',
                        'operator' => '==',
                        'value' => $page_id,
                    ),
                ),
                array(
                    array(
                        'param' => 'page_type',
                        'operator' => '==',
                        'value' => 'front_page',
                    ),
                ),
            ),
            'menu_order' => 2,
            'position' => 'normal',
            'style' => 'default',
            'show_in_rest' => 1,
        ) );

        acf_add_local_field_group( array(
            'key' => 'group_home_informativo_como_funciona',
            'title' => 'Sitio Informativo - Cómo Funciona',
            'fields' => array(
                array(
                    'key' => 'field_info_como_funciona_titulo',
                    'label' => 'Título de Sección',
                    'name' => 'info_como_funciona_titulo',
                    'type' => 'text',
                    'default_value' => 'Cómo Funciona',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_paso_1_titulo',
                    'label' => 'Paso 1 - Título',
                    'name' => 'info_paso_1_titulo',
                    'type' => 'text',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_paso_1_descripcion',
                    'label' => 'Paso 1 - Descripción',
                    'name' => 'info_paso_1_descripcion',
                    'type' => 'textarea',
                    'rows' => 2,
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_paso_2_titulo',
                    'label' => 'Paso 2 - Título',
                    'name' => 'info_paso_2_titulo',
                    'type' => 'text',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_paso_2_descripcion',
                    'label' => 'Paso 2 - Descripción',
                    'name' => 'info_paso_2_descripcion',
                    'type' => 'textarea',
                    'rows' => 2,
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_paso_3_titulo',
                    'label' => 'Paso 3 - Título',
                    'name' => 'info_paso_3_titulo',
                    'type' => 'text',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_paso_3_descripcion',
                    'label' => 'Paso 3 - Descripción',
                    'name' => 'info_paso_3_descripcion',
                    'type' => 'textarea',
                    'rows' => 2,
                    'show_in_rest' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'page',
                        'operator' => '==',
                        'value' => $page_id,
                    ),
                ),
                array(
                    array(
                        'param' => 'page_type',
                        'operator' => '==',
                        'value' => 'front_page',
                    ),
                ),
            ),
            'menu_order' => 3,
            'position' => 'normal',
            'style' => 'default',
            'show_in_rest' => 1,
        ) );

        acf_add_local_field_group( array(
            'key' => 'group_home_informativo_cta',
            'title' => 'Sitio Informativo - CTA Final',
            'fields' => array(
                array(
                    'key' => 'field_info_cta_titulo',
                    'label' => 'Título CTA',
                    'name' => 'info_cta_titulo',
                    'type' => 'text',
                    'default_value' => '¿Listo para comenzar?',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_cta_descripcion',
                    'label' => 'Descripción CTA',
                    'name' => 'info_cta_descripcion',
                    'type' => 'textarea',
                    'rows' => 2,
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_cta_boton_texto',
                    'label' => 'Texto del Botón',
                    'name' => 'info_cta_boton_texto',
                    'type' => 'text',
                    'default_value' => 'Contactar',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_info_cta_boton_url',
                    'label' => 'URL del Botón',
                    'name' => 'info_cta_boton_url',
                    'type' => 'url',
                    'placeholder' => 'https://',
                    'show_in_rest' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'page',
                        'operator' => '==',
                        'value' => $page_id,
                    ),
                ),
                array(
                    array(
                        'param' => 'page_type',
                        'operator' => '==',
                        'value' => 'front_page',
                    ),
                ),
            ),
            'menu_order' => 4,
            'position' => 'normal',
            'style' => 'default',
            'show_in_rest' => 1,
        ) );
    }

    private function get_page_id_by_slug( $slug ) {
        $page = get_page_by_path( $slug );
        return $page ? $page->ID : 0;
    }

    private function get_home_page_id() {
        $home_page = get_page_by_path( 'inicio' );
        
        if ( $home_page ) {
            return $home_page->ID;
        }

        $front_page_id = get_option( 'page_on_front' );
        
        if ( $front_page_id ) {
            return $front_page_id;
        }

        return 0;
    }

    public function check_module_activation( $post_id ) {
        if ( $post_id !== 'options' ) {
            return;
        }

        if ( ! function_exists( 'get_field' ) ) {
            return;
        }

        $mod_informativo = get_field( 'mod_informativo', 'option' );

        if ( $mod_informativo ) {
            $this->create_legal_and_faq_pages();
        }
    }

    private function create_legal_and_faq_pages() {
        $pages_to_create = array(
            'privacidad' => array(
                'title' => 'Política de Privacidad',
                'content' => '<h2>Política de Privacidad</h2><p>Esta es una página de marcador de posición para tu Política de Privacidad. Por favor, actualiza este contenido con tu política de privacidad real.</p><p>Asegúrate de incluir información sobre:</p><ul><li>Qué datos personales recopilas</li><li>Cómo utilizas esos datos</li><li>Con quién compartes los datos</li><li>Cómo proteges los datos</li><li>Derechos del usuario sobre sus datos</li></ul>',
            ),
            'terminos-y-condiciones' => array(
                'title' => 'Términos y Condiciones',
                'content' => '<h2>Términos y Condiciones</h2><p>Esta es una página de marcador de posición para tus Términos y Condiciones. Por favor, actualiza este contenido con tus términos reales.</p><p>Asegúrate de incluir información sobre:</p><ul><li>Uso aceptable del sitio</li><li>Derechos de propiedad intelectual</li><li>Limitaciones de responsabilidad</li><li>Términos de servicio</li><li>Modificaciones a los términos</li></ul>',
            ),
            'cookies' => array(
                'title' => 'Política de Cookies',
                'content' => '<h2>Política de Cookies</h2><p>Esta es una página de marcador de posición para tu Política de Cookies. Por favor, actualiza este contenido con tu política real.</p><p>Asegúrate de incluir información sobre:</p><ul><li>Qué son las cookies</li><li>Qué cookies utiliza tu sitio</li><li>Para qué se utilizan las cookies</li><li>Cómo desactivar las cookies</li><li>Cookies de terceros</li></ul>',
            ),
            'faq' => array(
                'title' => 'Preguntas Frecuentes',
                'content' => '<h2>Preguntas Frecuentes</h2><p>Esta página contiene las respuestas a las preguntas más frecuentes. Utiliza los campos personalizados de ACF para agregar tus preguntas y respuestas.</p>',
            ),
        );

        foreach ( $pages_to_create as $slug => $page_data ) {
            $existing_page = get_page_by_path( $slug );

            if ( ! $existing_page ) {
                $page_id = wp_insert_post( array(
                    'post_title'   => $page_data['title'],
                    'post_name'    => $slug,
                    'post_content' => $page_data['content'],
                    'post_status'  => 'draft',
                    'post_type'    => 'page',
                    'post_author'  => get_current_user_id(),
                ) );

                if ( ! is_wp_error( $page_id ) ) {
                    error_log( '[WebToWP Engine] Página legal/FAQ creada: ' . $page_data['title'] . ' - Slug: ' . $slug . ' - ID: ' . $page_id );
                } else {
                    error_log( '[WebToWP Engine] Error al crear página: ' . $page_data['title'] . ' - ' . $page_id->get_error_message() );
                }
            }
        }
    }
}
