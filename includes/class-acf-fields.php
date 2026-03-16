<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_ACF_Fields {

    public function __construct() {
        add_action( 'acf/init', array( $this, 'register_field_groups' ) );
    }

    public function register_field_groups() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        $this->register_hero_home_fields();
        $this->register_servicios_fields();
        $this->register_global_marquee_fields();
        $this->register_seo_fields();
    }

    private function register_hero_home_fields() {
        acf_add_local_field_group( array(
            'key' => 'group_hero_home',
            'title' => 'Hero Home',
            'fields' => array(
                array(
                    'key' => 'field_h1_titulo',
                    'label' => 'H1 Título',
                    'name' => 'h1_titulo',
                    'type' => 'text',
                    'required' => 1,
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_hero_subtitulo',
                    'label' => 'Hero Subtítulo',
                    'name' => 'hero_subtitulo',
                    'type' => 'textarea',
                    'rows' => 3,
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_boton_texto',
                    'label' => 'Botón Texto',
                    'name' => 'boton_texto',
                    'type' => 'text',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_hero_imagen',
                    'label' => 'Hero Imagen',
                    'name' => 'hero_imagen',
                    'type' => 'image',
                    'return_format' => 'id',
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
                        'value' => $this->get_page_id_by_slug( 'inicio' ),
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
    }

    private function register_servicios_fields() {
        $is_acf_pro = defined( 'ACF_PRO' ) && ACF_PRO;

        if ( $is_acf_pro ) {
            acf_add_local_field_group( array(
                'key' => 'group_servicios',
                'title' => 'Sección Servicios',
                'fields' => array(
                    array(
                        'key' => 'field_servicios_repeater',
                        'label' => 'Servicios',
                        'name' => 'servicios',
                        'type' => 'repeater',
                        'layout' => 'block',
                        'button_label' => 'Agregar Servicio',
                        'show_in_rest' => 1,
                        'sub_fields' => array(
                            array(
                                'key' => 'field_servicio_titulo',
                                'label' => 'Título',
                                'name' => 'titulo',
                                'type' => 'text',
                                'required' => 1,
                                'show_in_rest' => 1,
                            ),
                            array(
                                'key' => 'field_servicio_descripcion',
                                'label' => 'Descripción',
                                'name' => 'descripcion',
                                'type' => 'textarea',
                                'rows' => 3,
                                'show_in_rest' => 1,
                            ),
                            array(
                                'key' => 'field_servicio_icono',
                                'label' => 'Icono',
                                'name' => 'icono',
                                'type' => 'image',
                                'return_format' => 'id',
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
                            'value' => $this->get_page_id_by_slug( 'servicios' ),
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
        } else {
            $fields = array();
            
            for ( $i = 1; $i <= 3; $i++ ) {
                $fields[] = array(
                    'key' => 'field_servicio_' . $i . '_titulo',
                    'label' => 'Servicio ' . $i . ' - Título',
                    'name' => 'servicio_' . $i . '_titulo',
                    'type' => 'text',
                    'show_in_rest' => 1,
                );
                $fields[] = array(
                    'key' => 'field_servicio_' . $i . '_descripcion',
                    'label' => 'Servicio ' . $i . ' - Descripción',
                    'name' => 'servicio_' . $i . '_descripcion',
                    'type' => 'textarea',
                    'rows' => 3,
                    'show_in_rest' => 1,
                );
                $fields[] = array(
                    'key' => 'field_servicio_' . $i . '_icono',
                    'label' => 'Servicio ' . $i . ' - Icono',
                    'name' => 'servicio_' . $i . '_icono',
                    'type' => 'image',
                    'return_format' => 'id',
                    'preview_size' => 'thumbnail',
                    'show_in_rest' => 1,
                );
            }

            acf_add_local_field_group( array(
                'key' => 'group_servicios',
                'title' => 'Sección Servicios',
                'fields' => $fields,
                'location' => array(
                    array(
                        array(
                            'param' => 'page',
                            'operator' => '==',
                            'value' => $this->get_page_id_by_slug( 'servicios' ),
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
        }
    }

    private function register_global_marquee_fields() {
        $fields = array();
        
        for ( $i = 1; $i <= 6; $i++ ) {
            $fields[] = array(
                'key' => 'field_logo_' . $i,
                'label' => 'Logo ' . $i,
                'name' => 'logo_' . $i,
                'type' => 'image',
                'return_format' => 'id',
                'preview_size' => 'thumbnail',
                'library' => 'all',
                'show_in_rest' => 1,
            );
        }

        acf_add_local_field_group( array(
            'key' => 'group_global_marquee',
            'title' => 'Global Marquee',
            'fields' => $fields,
            'location' => array(
                array(
                    array(
                        'param' => 'page',
                        'operator' => '==',
                        'value' => $this->get_page_id_by_slug( 'inicio' ),
                    ),
                ),
                array(
                    array(
                        'param' => 'page',
                        'operator' => '==',
                        'value' => $this->get_page_id_by_slug( 'nosotros' ),
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
    }

    private function register_seo_fields() {
        acf_add_local_field_group( array(
            'key' => 'group_seo_social',
            'title' => 'SEO & Redes Sociales',
            'fields' => array(
                array(
                    'key' => 'field_seo_title',
                    'label' => 'SEO Title',
                    'name' => 'seo_title',
                    'type' => 'text',
                    'instructions' => 'Título optimizado para motores de búsqueda (50-60 caracteres)',
                    'maxlength' => 60,
                    'placeholder' => 'Título SEO personalizado',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_seo_description',
                    'label' => 'SEO Description',
                    'name' => 'seo_description',
                    'type' => 'textarea',
                    'instructions' => 'Descripción optimizada para motores de búsqueda (150-160 caracteres)',
                    'maxlength' => 160,
                    'rows' => 3,
                    'placeholder' => 'Descripción SEO personalizada',
                    'show_in_rest' => 1,
                ),
                array(
                    'key' => 'field_seo_image',
                    'label' => 'SEO Image (Open Graph)',
                    'name' => 'seo_image',
                    'type' => 'image',
                    'instructions' => 'Imagen para compartir en redes sociales (1200x630px recomendado)',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'all',
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
                ),
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'post',
                    ),
                ),
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'w2wp_servicios',
                    ),
                ),
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'w2wp_recursos',
                    ),
                ),
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'servicio_medico',
                    ),
                ),
            ),
            'menu_order' => 100,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'show_in_rest' => 1,
        ) );
    }

    private function get_page_id_by_slug( $slug ) {
        $page = get_page_by_path( $slug );
        return $page ? $page->ID : 0;
    }
}
