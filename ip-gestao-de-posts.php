<?php
/**
 * Plugin Name: IP Gestão de Posts
 * Description: Dashboard e calendário editorial para posts publicados, com métricas por autor e categoria.
 * Version: 1.0.0
 * Author: IP
 * Text Domain: ip-gestao-de-posts
 * Domain Path: /languages
 * Requires at least: 5.5
 * Requires PHP: 7.4
 */

if (! defined('ABSPATH')) {
    exit;
}

define('IPGP_VERSION', '1.0.0');
define('IPGP_FILE', __FILE__);
define('IPGP_PATH', plugin_dir_path(__FILE__));
define('IPGP_URL', plugin_dir_url(__FILE__));

require_once IPGP_PATH . 'includes/class-date-range.php';
require_once IPGP_PATH . 'includes/class-author-colors.php';
require_once IPGP_PATH . 'includes/class-settings-service.php';
require_once IPGP_PATH . 'includes/class-dashboard-service.php';
require_once IPGP_PATH . 'includes/class-calendar-service.php';
require_once IPGP_PATH . 'includes/class-assets.php';
require_once IPGP_PATH . 'includes/class-admin-menu.php';
require_once IPGP_PATH . 'includes/class-ajax-controller.php';
require_once IPGP_PATH . 'includes/class-plugin.php';

register_activation_hook(__FILE__, ['IPGP_Plugin', 'activate']);

add_action('plugins_loaded', static function () {
    IPGP_Plugin::instance()->init();
});
