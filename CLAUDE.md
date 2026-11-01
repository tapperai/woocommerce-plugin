# woocommerce-plugin

"Tapper for WooCommerce": the WordPress plugin that puts Tapper's tracker on a WooCommerce storefront and saves the shopper's visitor (`tcid`) and ad click on each order at checkout. It is the WooCommerce counterpart of tracker-shopify-extension. The back-end connector that reads what it saves: back-end `src/services/woocommerceOrderMapper.ts` (`readWooCommerceClickIds`), spec back-end `docs/woocommerce/SPEC.md`.

## Map
- `tapper-for-woocommerce.php`: header, constants, HPOS and blocks compatibility, boot.
- `includes/class-tapper-identity.php`: PURE reader of the tracker's `tsid` cookie (no WordPress calls, unit tested).
- `includes/class-tapper-order-meta.php`: the two checkout hooks, the consent gate, the meta writes.
- `includes/class-tapper-tracker.php`: the `wp_head` loader (+ WP Consent API relay). `includes/class-tapper-settings.php`: Settings > Tapper.
- `tests/run.php`: `php tests/run.php`. `bin/build-zip.sh`: the release zip.

## Rules
- **The meta keys are a contract with the back-end connector** (`tapper_visitor_id`, `tapper_<click kind>`, not underscore-prefixed so the REST API exposes them). Change one only with the back-end mapper in the same release.
- **Consent first.** Read only the cookie the tracker writes under consent; never read, save or send a name, address, e-mail, phone, IP address or user agent. Never overwrite a value already on the order.
- **Public repo, public plugin.** No internal host, project id, secret or customer name in code, docs or commits. Only `monitor.tapper.ai` (the public tracker) appears.
- **Standards:** WordPress and WooCommerce coding standards, escaped output, capability checks, `defined('ABSPATH')`, text domain `tapper-for-woocommerce`, GPL-2.0-or-later.
- **One version:** the plugin header, `TAPPER_WC_VERSION` and `readme.txt` Stable tag agree (`bin/build-zip.sh` fails otherwise).
- **Branch flow:** PRs target `main`; a release is a tag `v<version>`; the workflow attaches the zip.
- **Testing policy:** record per change `Tests: local (<cmd>)`; the local command is `php tests/run.php`, and a store check on the proof store for anything touching checkout.
- **Identity:** work from the operator's laptop is his own GitHub login; work on Loop's machine is `tapper-loop[bot]`.
