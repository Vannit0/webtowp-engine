<?php
/**
 * Plugin Name: WebToWP Engine
 * Plugin URI: https://webtowp.com
 * Description: A powerful engine to convert web content into WordPress content with modular architecture
 * Version: 1.1.1
 * Author: WebToWP Team
 * Author URI: https://webtowp.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: webtowp-engine
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'W2WP_VERSION', '1.1.1' );
define( 'W2WP_PATH', plugin_dir_path( __FILE__ ) );
define( 'W2WP_URL', plugin_dir_url( __FILE__ ) );
define( 'W2WP_BASENAME', plugin_basename( __FILE__ ) );

require_once W2WP_PATH . 'includes/class-webtowp-engine.php';
require_once W2WP_PATH . 'includes/class-setup.php';

function webtowp_engine() {
    return WebToWP_Engine::get_instance();
}

register_activation_hook( __FILE__, array( 'W2WP_Setup', 'create_initial_pages' ) );

$update_checker_path = W2WP_PATH . 'includes/plugin-update-checker/plugin-update-checker.php';
if ( file_exists( $update_checker_path ) ) {
    require_once $update_checker_path;
    
    use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
    
    $myUpdateChecker = PucFactory::buildUpdateChecker(
        'https://github.com/Vannit0/webtowp-engine',
        __FILE__,
        'webtowp-engine'
    );
    
    $myUpdateChecker->setBranch( 'main' );
    
    if ( defined( 'W2WP_GITHUB_TOKEN' ) && W2WP_GITHUB_TOKEN ) {
        $myUpdateChecker->setAuthentication( W2WP_GITHUB_TOKEN );
    }
    
    $myUpdateChecker->getVcsApi()->enableReleaseAssets();
}

webtowp_engine();
