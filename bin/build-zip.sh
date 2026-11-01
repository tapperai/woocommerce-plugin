#!/usr/bin/env bash
# Builds dist/tapper-for-woocommerce-<version>.zip: the folder WordPress expects (tapper-for-woocommerce/), runtime files only.
set -euo pipefail
cd "$(dirname "$0")/.."
VERSION="$(sed -n 's/^ \* Version:[[:space:]]*//p' tapper-for-woocommerce.php | head -1)"
HEADER_CONST="$(sed -n "s/^define( 'TAPPER_WC_VERSION', '\(.*\)' );/\1/p" tapper-for-woocommerce.php)"
STABLE="$(sed -n 's/^Stable tag:[[:space:]]*//p' readme.txt | head -1)"
[ -n "$VERSION" ] && [ "$VERSION" = "$HEADER_CONST" ] && [ "$VERSION" = "$STABLE" ] || { echo "version mismatch: header=$VERSION constant=$HEADER_CONST readme=$STABLE" >&2; exit 1; }
rm -rf dist && mkdir -p "dist/tapper-for-woocommerce"
cp -R tapper-for-woocommerce.php uninstall.php readme.txt LICENSE includes dist/tapper-for-woocommerce/
( cd dist && zip -qr "tapper-for-woocommerce-$VERSION.zip" tapper-for-woocommerce )
rm -rf dist/tapper-for-woocommerce
echo "dist/tapper-for-woocommerce-$VERSION.zip"
