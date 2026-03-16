<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Module_Landing {

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
    }

    public function on_module_activation_hook( $mod_informativo, $mod_landing ) {
        $this->on_module_activation( null );
    }

    public function maybe_activate_module() {
        $is_active = get_option( 'w2wp_mod_landing', '0' );
        
        if ( $is_active === '1' ) {
            add_action( 'acf/init', array( $this, 'register_module_fields' ) );
        }
    }

    public function on_module_activation( $post_id ) {
        $is_active = get_option( 'w2wp_mod_landing', '0' );
        $was_active = get_option( 'w2wp_mod_landing_activated', false );

        if ( $is_active === '1' && ! $was_active ) {
            $this->blueprint_landing_page();
            update_option( 'w2wp_mod_landing_activated', true );
            flush_rewrite_rules();
        } elseif ( $is_active === '0' && $was_active ) {
            update_option( 'w2wp_mod_landing_activated', false );
        }
    }

    private function blueprint_landing_page() {
        $existing_page = get_page_by_path( 'oferta-especial' );
        
        if ( ! $existing_page ) {
            wp_insert_post( array(
                'post_title' => 'Landing Page',
                'post_name' => 'oferta-especial',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => '',
            ) );
        }

        flush_rewrite_rules();
    }

    public function register_module_fields() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        $landing_page_id = $this->get_page_id_by_slug( 'oferta-especial' );

        if ( ! $landing_page_id ) {
            return;
        }

        $this->register_hero_section( $landing_page_id );
        $this->register_beneficios_section( $landing_page_id );
        $this->register_precios_section( $landing_page_id );
        $this->register_testimonios_section( $landing_page_id );
        $this->register_cta_section( $landing_page_id );
    }

    private function register_hero_section( $page_id ) {
        acf_add_local_field_group( array(
            'key' => 'group_landing_hero',
            'title' => 'Sección Hero',
            'fields' => array(
                array(
                    'key' => 'field_landing_hero_titulo',
                    'label' => 'Título Principal (H1)',
                    'name' => 'landing_hero_titulo',
                    'type' => 'text',
                    'required' => 1,
                    'placeholder' => 'Ej: Oferta Especial - 50% de Descuento',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_hero_subtitulo',
                    'label' => 'Subtítulo',
                    'name' => 'landing_hero_subtitulo',
                    'type' => 'textarea',
                    'rows' => 3,
                    'placeholder' => 'Descripción breve y persuasiva',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_hero_imagen',
                    'label' => 'Imagen Hero',
                    'name' => 'landing_hero_imagen',
                    'type' => 'image',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_hero_cta_texto',
                    'label' => 'Texto del Botón CTA',
                    'name' => 'landing_hero_cta_texto',
                    'type' => 'text',
                    'default_value' => 'Obtener Oferta Ahora',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_hero_cta_url',
                    'label' => 'URL del Botón CTA',
                    'name' => 'landing_hero_cta_url',
                    'type' => 'url',
                    'placeholder' => 'https://',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_hero_video_url',
                    'label' => 'URL de Video (Opcional)',
                    'name' => 'landing_hero_video_url',
                    'type' => 'url',
                    'instructions' => 'URL de YouTube o Vimeo',
                    'placeholder' => 'https://youtube.com/watch?v=...',
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

    private function register_beneficios_section( $page_id ) {
        acf_add_local_field_group( array(
            'key' => 'group_landing_beneficios',
            'title' => 'Sección Beneficios',
            'fields' => array(
                array(
                    'key' => 'field_landing_beneficios_titulo',
                    'label' => 'Título de Sección',
                    'name' => 'landing_beneficios_titulo',
                    'type' => 'text',
                    'default_value' => '¿Por qué elegirnos?',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_beneficios_subtitulo',
                    'label' => 'Subtítulo',
                    'name' => 'landing_beneficios_subtitulo',
                    'type' => 'textarea',
                    'rows' => 2,
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_beneficios_items',
                    'label' => 'Beneficios',
                    'name' => 'landing_beneficios_items',
                    'type' => 'repeater',
                    'layout' => 'block',
                    'button_label' => 'Agregar Beneficio',
                    'show_in_rest' => 1,
                    'sub_fields' => array(
                        array(
                            'key' => 'field_landing_beneficio_icono',
                            'label' => 'Icono',
                            'name' => 'icono',
                            'type' => 'text',
                            'instructions' => 'Nombre del icono de Lucide (ej: Check, Star, Zap)',
                            'placeholder' => 'Ej: Check',
                            'show_in_rest' => 1,
                        ),
                        array(
                            'key' => 'field_landing_beneficio_titulo',
                            'label' => 'Título',
                            'name' => 'titulo',
                            'type' => 'text',
                            'required' => 1,
                            'show_in_rest' => 1,
                        ),
                        array(
                            'key' => 'field_landing_beneficio_descripcion',
                            'label' => 'Descripción',
                            'name' => 'descripcion',
                            'type' => 'textarea',
                            'rows' => 3,
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

    private function register_precios_section( $page_id ) {
        acf_add_local_field_group( array(
            'key' => 'group_landing_precios',
            'title' => 'Sección Precios',
            'fields' => array(
                array(
                    'key' => 'field_landing_precios_titulo',
                    'label' => 'Título de Sección',
                    'name' => 'landing_precios_titulo',
                    'type' => 'text',
                    'default_value' => 'Planes y Precios',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_precios_subtitulo',
                    'label' => 'Subtítulo',
                    'name' => 'landing_precios_subtitulo',
                    'type' => 'textarea',
                    'rows' => 2,
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_precios_planes',
                    'label' => 'Planes',
                    'name' => 'landing_precios_planes',
                    'type' => 'repeater',
                    'layout' => 'block',
                    'button_label' => 'Agregar Plan',
                    'show_in_rest' => 1,
                    'sub_fields' => array(
                        array(
                            'key' => 'field_landing_plan_nombre',
                            'label' => 'Nombre del Plan',
                            'name' => 'nombre',
                            'type' => 'text',
                            'required' => 1,
                            'placeholder' => 'Ej: Básico, Pro, Premium',
                            'show_in_rest' => 1,
                        ),
                        array(
                            'key' => 'field_landing_plan_precio',
                            'label' => 'Precio',
                            'name' => 'precio',
                            'type' => 'text',
                            'required' => 1,
                            'placeholder' => 'Ej: $99',
                            'show_in_rest' => 1,
                        ),
                        array(
                            'key' => 'field_landing_plan_periodo',
                            'label' => 'Período',
                            'name' => 'periodo',
                            'type' => 'text',
                            'default_value' => '/mes',
                            'placeholder' => 'Ej: /mes, /año',
                            'show_in_rest' => 1,
                        ),
                        array(
                            'key' => 'field_landing_plan_descripcion',
                            'label' => 'Descripción',
                            'name' => 'descripcion',
                            'type' => 'textarea',
                            'rows' => 2,
                            'show_in_rest' => 1,
                        ),
                        array(
                            'key' => 'field_landing_plan_destacado',
                            'label' => 'Plan Destacado',
                            'name' => 'destacado',
                            'type' => 'true_false',
                            'instructions' => 'Marcar como plan recomendado',
                            'default_value' => 0,
                            'show_in_rest' => 1,
                        ),
                        array(
                            'key' => 'field_landing_plan_caracteristicas',
                            'label' => 'Características',
                            'name' => 'caracteristicas',
                            'type' => 'repeater',
                            'layout' => 'table',
                            'button_label' => 'Agregar Característica',
                            'show_in_rest' => 1,
                            'sub_fields' => array(
                                array(
                                    'key' => 'field_landing_plan_caracteristica_texto',
                                    'label' => 'Texto',
                                    'name' => 'texto',
                                    'type' => 'text',
                                    'required' => 1,
                                    'show_in_rest' => 1,
                                ),
                                array(
                                    'key' => 'field_landing_plan_caracteristica_incluida',
                                    'label' => 'Incluida',
                                    'name' => 'incluida',
                                    'type' => 'true_false',
                                    'default_value' => 1,
                                    'show_in_rest' => 1,
                                ),
                            ),
                        ),
                        array(
                            'key' => 'field_landing_plan_cta_texto',
                            'label' => 'Texto del Botón',
                            'name' => 'cta_texto',
                            'type' => 'text',
                            'default_value' => 'Comenzar Ahora',
                            'show_in_rest' => 1,
                        ),
                        array(
                            'key' => 'field_landing_plan_cta_url',
                            'label' => 'URL del Botón',
                            'name' => 'cta_url',
                            'type' => 'url',
                            'placeholder' => 'https://',
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
            'menu_order' => 2,
            'position' => 'normal',
            'style' => 'default',
            'show_in_rest' => 1,
        ) );
    }

    private function register_testimonios_section( $page_id ) {
        acf_add_local_field_group( array(
            'key' => 'group_landing_testimonios',
            'title' => 'Sección Testimonios',
            'fields' => array(
                array(
                    'key' => 'field_landing_testimonios_titulo',
                    'label' => 'Título de Sección',
                    'name' => 'landing_testimonios_titulo',
                    'type' => 'text',
                    'default_value' => 'Lo que dicen nuestros clientes',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_testimonios_items',
                    'label' => 'Testimonios',
                    'name' => 'landing_testimonios_items',
                    'type' => 'repeater',
                    'layout' => 'block',
                    'button_label' => 'Agregar Testimonio',
                    'show_in_rest' => 1,
                    'sub_fields' => array(
                        array(
                            'key' => 'field_landing_testimonio_texto',
                            'label' => 'Testimonio',
                            'name' => 'texto',
                            'type' => 'textarea',
                            'rows' => 4,
                            'required' => 1,
                            'show_in_rest' => 1,
                        ),
                        array(
                            'key' => 'field_landing_testimonio_nombre',
                            'label' => 'Nombre del Cliente',
                            'name' => 'nombre',
                            'type' => 'text',
                            'required' => 1,
                            'show_in_rest' => 1,
                        ),
                        array(
                            'key' => 'field_landing_testimonio_cargo',
                            'label' => 'Cargo/Empresa',
                            'name' => 'cargo',
                            'type' => 'text',
                            'placeholder' => 'Ej: CEO de Empresa XYZ',
                            'show_in_rest' => 1,
                        ),
                        array(
                            'key' => 'field_landing_testimonio_foto',
                            'label' => 'Foto',
                            'name' => 'foto',
                            'type' => 'image',
                            'return_format' => 'array',
                            'preview_size' => 'thumbnail',
                            'show_in_rest' => 1,
                        ),
                        array(
                            'key' => 'field_landing_testimonio_rating',
                            'label' => 'Calificación (1-5)',
                            'name' => 'rating',
                            'type' => 'number',
                            'min' => 1,
                            'max' => 5,
                            'step' => 1,
                            'default_value' => 5,
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
            'menu_order' => 3,
            'position' => 'normal',
            'style' => 'default',
            'show_in_rest' => 1,
        ) );
    }

    private function register_cta_section( $page_id ) {
        acf_add_local_field_group( array(
            'key' => 'group_landing_cta_final',
            'title' => 'Sección CTA Final',
            'fields' => array(
                array(
                    'key' => 'field_landing_cta_titulo',
                    'label' => 'Título',
                    'name' => 'landing_cta_titulo',
                    'type' => 'text',
                    'default_value' => '¿Listo para comenzar?',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_cta_descripcion',
                    'label' => 'Descripción',
                    'name' => 'landing_cta_descripcion',
                    'type' => 'textarea',
                    'rows' => 3,
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_cta_boton_texto',
                    'label' => 'Texto del Botón Principal',
                    'name' => 'landing_cta_boton_texto',
                    'type' => 'text',
                    'default_value' => 'Obtener Acceso Ahora',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_cta_boton_url',
                    'label' => 'URL del Botón Principal',
                    'name' => 'landing_cta_boton_url',
                    'type' => 'url',
                    'placeholder' => 'https://',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_cta_boton_secundario_texto',
                    'label' => 'Texto del Botón Secundario (Opcional)',
                    'name' => 'landing_cta_boton_secundario_texto',
                    'type' => 'text',
                    'placeholder' => 'Ej: Más Información',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_cta_boton_secundario_url',
                    'label' => 'URL del Botón Secundario',
                    'name' => 'landing_cta_boton_secundario_url',
                    'type' => 'url',
                    'placeholder' => 'https://',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_cta_urgencia',
                    'label' => 'Mensaje de Urgencia',
                    'name' => 'landing_cta_urgencia',
                    'type' => 'text',
                    'instructions' => 'Mensaje de escasez o urgencia (opcional)',
                    'placeholder' => 'Ej: ¡Solo quedan 5 lugares disponibles!',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_landing_cta_garantia',
                    'label' => 'Garantía',
                    'name' => 'landing_cta_garantia',
                    'type' => 'text',
                    'placeholder' => 'Ej: Garantía de devolución de dinero de 30 días',
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
}
