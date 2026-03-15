<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Admin_Setup {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'acf/init', array( $this, 'register_white_label_menu' ) );
        add_action( 'admin_menu', array( $this, 'security_vault' ), 999 );
        add_action( 'admin_head', array( $this, 'hide_screen_options_and_help' ) );
    }

    public function register_white_label_menu() {
        if ( ! function_exists( 'acf_add_options_page' ) ) {
            return;
        }

        acf_add_options_page( array(
            'page_title'    => __( 'WebToWP', 'webtowp-engine' ),
            'menu_title'    => __( 'WebToWP', 'webtowp-engine' ),
            'menu_slug'     => 'webtowp-engine',
            'capability'    => 'manage_options',
            'icon_url'      => 'dashicons-admin-site-alt3',
            'position'      => 3,
            'redirect'      => false,
        ) );

        acf_add_options_sub_page( array(
            'page_title'    => __( 'Módulos Activos', 'webtowp-engine' ),
            'menu_title'    => __( 'Módulos Activos', 'webtowp-engine' ),
            'menu_slug'     => 'webtowp-active-modules',
            'parent_slug'   => 'webtowp-engine',
            'capability'    => 'manage_options',
        ) );

        acf_add_options_sub_page( array(
            'page_title'    => __( 'Ajustes Globales', 'webtowp-engine' ),
            'menu_title'    => __( 'Ajustes Globales', 'webtowp-engine' ),
            'menu_slug'     => 'webtowp-global-settings',
            'parent_slug'   => 'webtowp-engine',
            'capability'    => 'manage_options',
        ) );

        acf_add_options_sub_page( array(
            'page_title'    => __( 'Despliegue & API', 'webtowp-engine' ),
            'menu_title'    => __( 'Despliegue & API', 'webtowp-engine' ),
            'menu_slug'     => 'webtowp-deployment-api',
            'parent_slug'   => 'webtowp-engine',
            'capability'    => 'manage_options',
        ) );
    }

    public function security_vault() {
        if ( current_user_can( 'manage_options' ) ) {
            return;
        }

        remove_menu_page( 'plugins.php' );
        remove_menu_page( 'themes.php' );
        remove_menu_page( 'options-general.php' );
        remove_menu_page( 'tools.php' );
        remove_menu_page( 'edit-comments.php' );
        remove_menu_page( 'edit.php?post_type=acf-field-group' );
    }

    public function hide_screen_options_and_help() {
        if ( current_user_can( 'manage_options' ) ) {
            return;
        }

        echo '<style>
            #screen-meta-links,
            #contextual-help-link-wrap,
            #screen-options-link-wrap {
                display: none !important;
            }
        </style>';
    }
}
