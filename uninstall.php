<?php
/**
 * Removes the plugin's own settings. Order meta already saved stays: it is part of the order.
 *
 * @package Tapper_For_WooCommerce
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'tapper_wc_public_key' );
delete_option( 'tapper_wc_capture_enabled' );
