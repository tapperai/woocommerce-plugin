<?php
/**
 * Saves the shopper's Tapper visitor and ad click on the order, at checkout.
 *
 * Both checkouts are covered: the classic shortcode checkout (`woocommerce_checkout_create_order`) and the Cart and
 * Checkout blocks (`woocommerce_store_api_checkout_update_order_from_request`). The cookie travels with the checkout
 * request itself, so no script has to run on the checkout page. A value already on the order is never overwritten.
 *
 * @package Tapper_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

final class Tapper_Order_Meta {

	public static function register(): void {
		add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'on_classic_checkout' ), 10, 1 );
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( __CLASS__, 'on_block_checkout' ), 10, 1 );
	}

	/** @param WC_Order $order The order being created (not yet saved). */
	public static function on_classic_checkout( $order ): void {
		self::save( $order );
	}

	/** @param WC_Order $order The order being created from the Store API request (not yet saved). */
	public static function on_block_checkout( $order ): void {
		self::save( $order );
	}

	/** Whether this shopper's identity may be saved: the plugin's switch, and the store's consent plugin when there is one. */
	public static function may_capture(): bool {
		if ( ! Tapper_Settings::capture_enabled() ) {
			return false;
		}
		// The WP Consent API (https://wordpress.org/plugins/wp-consent-api/): when a consent plugin speaks it, marketing consent is required.
		$allowed = true;
		if ( function_exists( 'wp_has_consent' ) ) {
			$allowed = (bool) wp_has_consent( 'marketing' );
		}

		return (bool) apply_filters( 'tapper_wc_may_capture', $allowed );
	}

	/** @param WC_Order $order The order. */
	private static function save( $order ): void {
		if ( ! is_object( $order ) || ! method_exists( $order, 'update_meta_data' ) || ! self::may_capture() ) {
			return;
		}
		$cookies = isset( $_COOKIE ) && is_array( $_COOKIE ) ? wp_unslash( $_COOKIE ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- the values are validated by Tapper_Identity, not trusted.
		foreach ( Tapper_Identity::meta_from_cookies( $cookies ) as $key => $value ) {
			if ( '' === (string) $order->get_meta( $key ) ) {
				$order->update_meta_data( $key, $value );
			}
		}
	}
}
