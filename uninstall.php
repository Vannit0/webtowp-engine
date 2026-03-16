<?php
/**
 * Uninstall Script
 * 
 * Se ejecuta cuando el plugin es desinstalado desde WordPress.
 * Limpia todas las opciones y datos del plugin.
 *
 * @package WebToWP_Engine
 * @since 1.2.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Verificar si el usuario quiere eliminar los datos
$delete_data = get_option( 'w2wp_delete_data_on_uninstall', false );

if ( ! $delete_data ) {
    // Si no está configurado para eliminar datos, salir
    return;
}

global $wpdb;

// Eliminar todas las opciones del plugin
$options_to_delete = array(
    // Opciones globales
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
    'w2wp_default_seo_image',
    'w2wp_signature_text',
    'w2wp_signature_url',
    
    // Opciones de deployment
    'w2wp_webhook_url',
    'w2wp_allowed_origins',
    'w2wp_api_key',
    
    // Opciones de módulos
    'w2wp_mod_informativo',
    'w2wp_mod_landing',
    'w2wp_mod_dentista',
    'w2wp_mod_barberia',
    'w2wp_mod_informativo_activated',
    'w2wp_mod_landing_activated',
    
    // Opciones de páginas del módulo informativo
    'w2wp_page_inicio',
    'w2wp_page_sobre_nosotros',
    'w2wp_page_servicios',
    'w2wp_page_blog',
    'w2wp_page_recursos',
    'w2wp_page_contacto',
    'w2wp_page_faq',
    'w2wp_page_legales',
    
    // Opciones del sistema
    'w2wp_activation_timestamp',
    'w2wp_version',
    'w2wp_persistent_notices',
    'w2wp_delete_data_on_uninstall',
);

foreach ( $options_to_delete as $option ) {
    delete_option( $option );
}

// Eliminar transients
delete_transient( 'w2wp_flush_rewrite_rules' );

// Eliminar tabla de logs de deployment
$table_name = $wpdb->prefix . 'w2wp_deployment_logs';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

// Eliminar posts creados por el plugin (opcional, comentado por seguridad)
// Solo descomentar si estás seguro de que quieres eliminar el contenido
/*
$post_types = array( 'w2wp_servicios', 'w2wp_recursos', 'servicio_medico' );
foreach ( $post_types as $post_type ) {
    $posts = get_posts( array(
        'post_type' => $post_type,
        'numberposts' => -1,
        'post_status' => 'any',
    ) );
    
    foreach ( $posts as $post ) {
        wp_delete_post( $post->ID, true );
    }
}
*/

// Limpiar rewrite rules
flush_rewrite_rules();

// Log de desinstalación
error_log( '[WebToWP Engine] Plugin desinstalado y datos eliminados.' );
