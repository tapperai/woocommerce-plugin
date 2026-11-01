<?php
/**
 * Tests for the pure identity reader. No WordPress and no test framework: `php tests/run.php` exits 0 when every check holds.
 */

define( 'ABSPATH', __DIR__ . '/' );
require_once __DIR__ . '/../includes/class-tapper-identity.php';

$failures = 0;
$checks   = 0;

function check( string $name, $actual, $expected ): void {
	global $failures, $checks;
	++$checks;
	if ( $actual !== $expected ) {
		++$failures;
		fwrite( STDERR, "FAIL: $name\n  expected " . json_encode( $expected ) . "\n  got      " . json_encode( $actual ) . "\n" );
	}
}

$tcid = '5613671d-3466-4b38-b122-43ba14157c94';
$env  = static function ( array $over = array() ) use ( $tcid ): string {
	return json_encode( array_merge( array( 'tcid' => $tcid, 'clid' => 'Cj0KCQjw_abc-123', 'clid_query_parameter_name' => 'gclid', 'utm_source' => 'google' ), $over ) );
};

// The cookie as the tracker writes it: plain JSON, or percent-encoded JSON.
check( 'plain JSON: visitor and gclid', Tapper_Identity::meta_from_cookies( array( 'tsid' => $env() ) ), array( 'tapper_visitor_id' => $tcid, 'tapper_gclid' => 'Cj0KCQjw_abc-123' ) );
check( 'percent-encoded JSON', Tapper_Identity::meta_from_cookies( array( 'tsid' => rawurlencode( $env() ) ) ), array( 'tapper_visitor_id' => $tcid, 'tapper_gclid' => 'Cj0KCQjw_abc-123' ) );
check( 'base64 JSON (an older tracker)', Tapper_Identity::meta_from_cookies( array( 'tsid' => base64_encode( $env() ) ) ), array( 'tapper_visitor_id' => $tcid, 'tapper_gclid' => 'Cj0KCQjw_abc-123' ) );

// Every click id kind the tracker holds, the name read in any case.
foreach ( array( 'gclid', 'gbraid', 'wbraid', 'fbclid', 'ttclid', 'msclkid', 'li_fat_id', 'sccid', 'rdt_cid', 'epik', 'twclid' ) as $kind ) {
	check( "click kind $kind", Tapper_Identity::meta_from_cookies( array( 'tsid' => $env( array( 'clid_query_parameter_name' => $kind, 'clid' => 'ID-123' ) ) ) ), array( 'tapper_visitor_id' => $tcid, "tapper_$kind" => 'ID-123' ) );
}
check( 'Snapchat spelling ScCid reads as sccid', Tapper_Identity::meta_from_cookies( array( 'tsid' => $env( array( 'clid_query_parameter_name' => 'ScCid', 'clid' => 'S1' ) ) ) ), array( 'tapper_visitor_id' => $tcid, 'tapper_sccid' => 'S1' ) );
check( 'uppercase tcid is stored lower case', Tapper_Identity::meta_from_cookies( array( 'tsid' => $env( array( 'tcid' => strtoupper( $tcid ) ) ) ) )['tapper_visitor_id'], $tcid );

// Nothing usable: nothing saved.
check( 'no cookie', Tapper_Identity::meta_from_cookies( array() ), array() );
check( 'other cookies only', Tapper_Identity::meta_from_cookies( array( '_ga' => 'GA1.1.1.2', 'wordpress_logged_in' => 'x' ) ), array() );
check( 'not JSON', Tapper_Identity::meta_from_cookies( array( 'tsid' => 'hello' ) ), array() );
check( 'cookie that is not a string', Tapper_Identity::meta_from_cookies( array( 'tsid' => array( 'a' ) ) ), array() );
check( 'oversized cookie', Tapper_Identity::meta_from_cookies( array( 'tsid' => str_repeat( 'a', 5000 ) ) ), array() );

// Bad parts are dropped one by one; a good part survives a bad one.
check( 'a tcid that is not a uuid is dropped, the click stays', Tapper_Identity::meta_from_cookies( array( 'tsid' => $env( array( 'tcid' => '123' ) ) ) ), array( 'tapper_gclid' => 'Cj0KCQjw_abc-123' ) );
check( 'a click name outside the list is dropped, the visitor stays', Tapper_Identity::meta_from_cookies( array( 'tsid' => $env( array( 'clid_query_parameter_name' => 'unknown' ) ) ) ), array( 'tapper_visitor_id' => $tcid ) );
check( 'a click id with a space or control character is dropped', Tapper_Identity::meta_from_cookies( array( 'tsid' => $env( array( 'clid' => "bad id" ) ) ) ), array( 'tapper_visitor_id' => $tcid ) );
check( 'a click id of 1025 characters is dropped', Tapper_Identity::meta_from_cookies( array( 'tsid' => $env( array( 'clid' => str_repeat( 'a', 1025 ) ) ) ) ), array( 'tapper_visitor_id' => $tcid ) );
check( 'a click id of 1024 characters is kept', isset( Tapper_Identity::meta_from_cookies( array( 'tsid' => $env( array( 'clid' => str_repeat( 'a', 1024 ) ) ) ) )['tapper_gclid'] ), true );
check( 'no click at all keeps the visitor', Tapper_Identity::meta_from_cookies( array( 'tsid' => json_encode( array( 'tcid' => $tcid ) ) ) ), array( 'tapper_visitor_id' => $tcid ) );
check( 'meta keys are not underscore-prefixed (WooCommerce REST shows them)', array_filter( array_keys( Tapper_Identity::meta_from_cookies( array( 'tsid' => $env() ) ) ), static function ( $k ) { return 0 === strpos( $k, '_' ); } ), array() );

// The key set equals what Tapper's back-end connector reads for its columns.
check( 'meta_key', Tapper_Identity::meta_key( 'gclid' ), 'tapper_gclid' );

echo "$checks checks, $failures failed\n";
exit( $failures > 0 ? 1 : 0 );
