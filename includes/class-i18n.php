<?php
/**
 * Internationalization Class
 * 
 * Gestiona la carga de traducciones del plugin.
 *
 * @package WebToWP_Engine
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_i18n {

    private static $instance = null;
    private $text_domain = 'webtowp-engine';

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
        add_action( 'init', array( $this, 'register_translations' ) );
    }

    /**
     * Carga el dominio de texto del plugin
     */
    public function load_plugin_textdomain() {
        $locale = apply_filters( 'plugin_locale', get_locale(), $this->text_domain );
        
        // Cargar traducciones desde el directorio de idiomas de WordPress
        load_textdomain(
            $this->text_domain,
            WP_LANG_DIR . '/plugins/' . $this->text_domain . '-' . $locale . '.mo'
        );
        
        // Cargar traducciones desde el directorio del plugin
        load_plugin_textdomain(
            $this->text_domain,
            false,
            dirname( W2WP_BASENAME ) . '/languages/'
        );
        
        error_log( "[WebToWP i18n] Traducciones cargadas para locale: {$locale}" );
    }

    /**
     * Registra traducciones para JavaScript
     */
    public function register_translations() {
        if ( function_exists( 'wp_set_script_translations' ) ) {
            wp_set_script_translations(
                'w2wp-deploy',
                $this->text_domain,
                W2WP_PATH . 'languages'
            );
            
            wp_set_script_translations(
                'w2wp-cache',
                $this->text_domain,
                W2WP_PATH . 'languages'
            );
        }
    }

    /**
     * Obtiene el dominio de texto del plugin
     */
    public function get_text_domain() {
        return $this->text_domain;
    }

    /**
     * Obtiene los idiomas disponibles
     */
    public function get_available_languages() {
        $languages_dir = W2WP_PATH . 'languages/';
        $available = array();
        
        if ( ! is_dir( $languages_dir ) ) {
            return $available;
        }
        
        $files = glob( $languages_dir . '*.mo' );
        
        if ( is_array( $files ) ) {
            foreach ( $files as $file ) {
                $filename = basename( $file, '.mo' );
                if ( ! is_string( $filename ) || $filename === '' ) {
                    continue;
                }
                $locale = str_replace( $this->text_domain . '-', '', $filename );
                
                if ( $locale !== $filename ) {
                    $available[] = $locale;
                }
            }
        }
        
        return $available;
    }

    /**
     * Obtiene información del idioma actual
     */
    public function get_current_language_info() {
        $locale = get_locale();
        $language = get_bloginfo( 'language' );
        
        return array(
            'locale' => $locale,
            'language' => $language,
            'text_domain' => $this->text_domain,
            'available_languages' => $this->get_available_languages(),
        );
    }

    /**
     * Traduce una cadena con contexto
     */
    public function translate_with_context( $text, $context ) {
        return _x( $text, $context, $this->text_domain );
    }

    /**
     * Traduce una cadena con plural
     */
    public function translate_plural( $single, $plural, $number ) {
        return _n( $single, $plural, $number, $this->text_domain );
    }

    /**
     * Escapa y traduce una cadena
     */
    public function esc_translate( $text ) {
        return esc_html__( $text, $this->text_domain );
    }

    /**
     * Escapa, traduce y muestra una cadena
     */
    public function esc_translate_e( $text ) {
        echo esc_html__( $text, $this->text_domain );
    }
}
