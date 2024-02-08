<?php

/**
 * Plugin Name: WooCommerce Product Addons
 * Plugin URI: https://mahbub/plugins/fetch-to-shop/
 * Description:  WooCommerce to Add Product's Extra Addons options on the product details page.
 * Version: 1.0.0
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * Author: Mahbub Hussain
 * Author URI: https://mahbub.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://example.com/my-plugin/
 * Text Domain: woo-extra
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    // Include the main class
    require_once plugin_dir_path(__FILE__) . 'includes/Product_Extra_Addons.php';

    // Initialize the plugin
    $product_extra_addons = new Product_Extra_Addons();
} else {
    // Display a notice if WooCommerce is not installed/activated
    add_action('admin_notices', 'product_extra_addons_missing_woocommerce_notice');

    function product_extra_addons_missing_woocommerce_notice()
    {
?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('Product Extra Addons for WooCommerce requires WooCommerce to be installed and activated. Please install WooCommerce to use this plugin.', 'woo-extra'); ?></p>
        </div>
<?php
    }
}
