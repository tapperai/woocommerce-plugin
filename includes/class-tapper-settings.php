<?php
/**
 * The plugin's one settings page (Settings > Tapper): the store's Tapper tracking id and a capture switch.
 *
 * @package Tapper_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

final class Tapper_Settings {

	const OPTION_KEY     = 'tapper_wc_public_key';
	const OPTION_CAPTURE = 'tapper_wc_capture_enabled';

	/** The tracking id shape Tapper issues (the same check Tapper's own embed uses). */
	const KEY_PATTERN = '/^pk_(live|test)_[A-Za-z0-9_-]{4,128}$/';

	public static function register(): void {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'settings' ) );
	}

	public static function public_key(): string {
		$key = get_option( self::OPTION_KEY, '' );

		return is_string( $key ) && 1 === preg_match( self::KEY_PATTERN, $key ) ? $key : '';
	}

	public static function capture_enabled(): bool {
		return 'no' !== get_option( self::OPTION_CAPTURE, 'yes' );
	}

	public static function sanitize_key( $value ): string {
		$value = is_string( $value ) ? trim( $value ) : '';

		return 1 === preg_match( self::KEY_PATTERN, $value ) ? $value : '';
	}

	public static function sanitize_switch( $value ): string {
		return 'no' === $value ? 'no' : 'yes';
	}

	public static function menu(): void {
		add_options_page( __( 'Tapper', 'tapper-for-woocommerce' ), __( 'Tapper', 'tapper-for-woocommerce' ), 'manage_woocommerce', 'tapper-for-woocommerce', array( __CLASS__, 'page' ) );
	}

	public static function settings(): void {
		register_setting( 'tapper_wc', self::OPTION_KEY, array( 'type' => 'string', 'sanitize_callback' => array( __CLASS__, 'sanitize_key' ), 'default' => '' ) );
		register_setting( 'tapper_wc', self::OPTION_CAPTURE, array( 'type' => 'string', 'sanitize_callback' => array( __CLASS__, 'sanitize_switch' ), 'default' => 'yes' ) );
	}

	public static function page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Tapper for WooCommerce', 'tapper-for-woocommerce' ); ?></h1>
			<p><?php esc_html_e( 'Paste your Tapper tracking id. Tapper then follows each visit to your store and saves the visitor and the ad click on every order, so each sale is matched to the campaign that earned it.', 'tapper-for-woocommerce' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'tapper_wc' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="tapper_wc_public_key"><?php esc_html_e( 'Tracking id', 'tapper-for-woocommerce' ); ?></label></th>
						<td>
							<input name="<?php echo esc_attr( self::OPTION_KEY ); ?>" id="tapper_wc_public_key" type="text" class="regular-text" value="<?php echo esc_attr( self::public_key() ); ?>" placeholder="pk_live_..." autocomplete="off" />
							<p class="description"><?php esc_html_e( 'It starts with pk_live_. You find it in Tapper, on your store source.', 'tapper-for-woocommerce' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Save the visitor on orders', 'tapper-for-woocommerce' ); ?></th>
						<td>
							<label><input name="<?php echo esc_attr( self::OPTION_CAPTURE ); ?>" type="radio" value="yes" <?php checked( self::capture_enabled() ); ?> /> <?php esc_html_e( 'Yes', 'tapper-for-woocommerce' ); ?></label>
							<label><input name="<?php echo esc_attr( self::OPTION_CAPTURE ); ?>" type="radio" value="no" <?php checked( ! self::capture_enabled() ); ?> /> <?php esc_html_e( 'No', 'tapper-for-woocommerce' ); ?></label>
							<p class="description"><?php esc_html_e( 'Only visitors who accepted tracking are saved. With a consent plugin that supports the WP Consent API, marketing consent is also required.', 'tapper-for-woocommerce' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
