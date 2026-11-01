<?php
/**
 * Reads the visitor and the ad click Tapper's tracker keeps on the shopper's own browser.
 *
 * PURE: no WordPress function is called here, so the same code is unit-tested without a store (tests/run.php).
 *
 * The tracker (monitor.tapper.ai/bundle.js) keeps ONE first-party cookie, `tsid`, whose value is a small JSON object:
 * `{ "tcid": "<visitor uuid>", "clid": "<click id>", "clid_query_parameter_name": "gclid" | "fbclid" | ..., "utm_*": ... }`.
 * It writes that cookie only when the shopper's consent allows it, so its presence IS the shopper's consent for this
 * lane; a refused or undecided shopper has no cookie and nothing is saved. Nothing else the tracker keeps is read.
 *
 * @package Tapper_For_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

final class Tapper_Identity {

	/** The cookie the tracker writes. */
	const COOKIE = 'tsid';

	/** The ad platforms' click ids the tracker can hold in the cookie's `clid` (lower case, as the tracker's server reads them). */
	const CLICK_KEYS = array( 'gclid', 'gbraid', 'wbraid', 'fbclid', 'ttclid', 'msclkid', 'li_fat_id', 'sccid', 'rdt_cid', 'epik', 'twclid' );

	/** The order meta key of the visitor. Not underscore-prefixed, so WooCommerce's REST API shows it to Tapper. */
	const META_VISITOR = 'tapper_visitor_id';

	/** The order meta key of one click id kind: `tapper_gclid`, `tapper_fbclid`, ... */
	public static function meta_key( string $kind ): string {
		return 'tapper_' . $kind;
	}

	/**
	 * The order meta to save, from the request's cookies.
	 *
	 * @param array<string,mixed> $cookies The request's cookies (`$_COOKIE`, already unslashed).
	 * @return array<string,string> Meta key => value; empty when the shopper has no usable Tapper cookie.
	 */
	public static function meta_from_cookies( array $cookies ): array {
		$raw = isset( $cookies[ self::COOKIE ] ) && is_string( $cookies[ self::COOKIE ] ) ? $cookies[ self::COOKIE ] : '';
		if ( '' === $raw || strlen( $raw ) > 4096 ) {
			return array();
		}
		$envelope = self::decode( $raw );
		if ( null === $envelope ) {
			return array();
		}

		$meta = array();
		$tcid = isset( $envelope['tcid'] ) && is_string( $envelope['tcid'] ) ? strtolower( $envelope['tcid'] ) : '';
		if ( self::is_uuid( $tcid ) ) {
			$meta[ self::META_VISITOR ] = $tcid;
		}

		$kind = isset( $envelope['clid_query_parameter_name'] ) && is_string( $envelope['clid_query_parameter_name'] ) ? strtolower( $envelope['clid_query_parameter_name'] ) : '';
		$clid = isset( $envelope['clid'] ) && is_string( $envelope['clid'] ) ? $envelope['clid'] : '';
		if ( in_array( $kind, self::CLICK_KEYS, true ) && self::is_click_id( $clid ) ) {
			$meta[ self::meta_key( $kind ) ] = $clid;
		}

		return $meta;
	}

	/** The cookie's JSON (it may arrive percent-encoded or, from an older tracker, base64 encoded), or null. */
	public static function decode( string $raw ): ?array {
		$candidates = array( $raw, rawurldecode( $raw ) );
		$b64        = base64_decode( strtr( $raw, '-_', '+/' ), true );
		if ( false !== $b64 ) {
			$candidates[] = $b64;
		}
		foreach ( $candidates as $text ) {
			$value = json_decode( $text, true );
			if ( is_array( $value ) ) {
				return $value;
			}
		}

		return null;
	}

	public static function is_uuid( string $value ): bool {
		return 1 === preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $value );
	}

	/** A click id as an ad platform sets it: visible ASCII, 1 to 1024 characters. */
	public static function is_click_id( string $value ): bool {
		return 1 === preg_match( '/^[\x21-\x7E]{1,1024}$/', $value );
	}
}
