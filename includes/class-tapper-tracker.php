<?php
/**
 * Loads Tapper's tracker on every storefront page (the same loader as Tapper's Shopify block), and passes the store's
 * consent plugin's marketing answer on when the store runs the WP Consent API.
 *
 * @package Tapper_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

final class Tapper_Tracker {

	/** Tapper's tracker bundle (a public address). */
	const BUNDLE_URL = 'https://monitor.tapper.ai/bundle.js';

	public static function register(): void {
		add_action( 'wp_head', array( __CLASS__, 'print_loader' ), 1 );
	}

	public static function print_loader(): void {
		$key = Tapper_Settings::public_key();
		if ( '' === $key || is_admin() || ! apply_filters( 'tapper_wc_load_tracker', true ) ) {
			return;
		}
		$consent_bridge = function_exists( 'wp_has_consent' );
		?>
<script>
(function () {
	var s = document.createElement('script');
	s.src = <?php echo wp_json_encode( self::BUNDLE_URL ); ?>;
	s.async = true;
	s.onload = function () {
		if (!window.tapper) { return; }
		window.tapper.init(<?php echo wp_json_encode( $key ); ?>);
		<?php if ( $consent_bridge ) : ?>
		// WP Consent API: pass the consent plugin's marketing answer on, now and when it changes.
		function relay() {
			if (typeof window.wp_has_consent !== 'function' || typeof window.tapper.consent !== 'function') { return; }
			window.tapper.consent(window.wp_has_consent('marketing') ? 'GRANTED' : 'DENIED');
		}
		relay();
		document.addEventListener('wp_listen_for_consent_change', relay);
		<?php endif; ?>
	};
	document.head.appendChild(s);
})();
</script>
		<?php
	}
}
