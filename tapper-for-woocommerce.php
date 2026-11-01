<?php
/**
 * Plugin Name:       Tapper for WooCommerce
 * Plugin URI:        https://tapper.ai
 * Description:       Connects your WooCommerce orders to Tapper: loads Tapper's tracker and saves the visitor and the ad click on every order, so each sale can be matched to the campaign that earned it.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * WC requires at least: 7.0
 * Author:            Tapper
 * Author URI:        https://tapper.ai
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       tapper-for-woocommerce
 *
 * @package Tapper_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

define( 'TAPPER_WC_VERSION', '1.0.0' );
define( 'TAPPER_WC_FILE', __FILE__ );
define( 'TAPPER_WC_DIR', plugin_dir_path( __FILE__ ) );

require_once TAPPER_WC_DIR . 'includes/class-tapper-identity.php';
require_once TAPPER_WC_DIR . 'includes/class-tapper-settings.php';
require_once TAPPER_WC_DIR . 'includes/class-tapper-tracker.php';
require_once TAPPER_WC_DIR . 'includes/class-tapper-order-meta.php';

// Declare compatibility with High-Performance Order Storage and the Cart and Checkout blocks.
add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', TAPPER_WC_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', TAPPER_WC_FILE, true );
		}
	}
);

add_action(
	'plugins_loaded',
	static function () {
		Tapper_Settings::register();
		Tapper_Tracker::register();
		if ( class_exists( 'WooCommerce' ) ) {
			Tapper_Order_Meta::register();
		}
	}
);
