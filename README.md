# Tapper for WooCommerce

A small WordPress plugin that connects a WooCommerce store to [Tapper](https://tapper.ai). It loads Tapper's tracker on the storefront and, at checkout, saves the shopper's Tapper visitor and ad click on the order, so Tapper's Relay can match the order to the campaign that earned it.

Without it a WooCommerce order carries no visitor and no click, and nothing can be sent to the ad platforms for it.

## Install

1. Download [tapper-for-woocommerce.zip](https://github.com/tapperai/woocommerce-plugin/releases/latest/download/tapper-for-woocommerce.zip) (the latest [release](../../releases)).
2. WordPress admin: Plugins > Add New > Upload Plugin > the zip > Activate.
3. Settings > Tapper: paste the tracking id (`pk_live_...`).
4. In Tapper, connect the store from your WooCommerce source.

Requirements: WordPress 6.0, WooCommerce 7.0, PHP 7.4. Works with the classic checkout, the Cart and Checkout blocks and High-Performance Order Storage.

## What it does, exactly

- `wp_head`: the same loader as Tapper's Shopify block (`monitor.tapper.ai/bundle.js`, then `tapper.init(<id>)`).
- `woocommerce_checkout_create_order` (classic) and `woocommerce_store_api_checkout_update_order_from_request` (blocks): read the tracker's first-party cookie `tsid` from the checkout request and save, when absent:

| Order meta | From the cookie |
|---|---|
| `tapper_visitor_id` | `tcid`, a UUID |
| `tapper_gclid`, `tapper_gbraid`, `tapper_wbraid`, `tapper_fbclid`, `tapper_ttclid`, `tapper_msclkid`, `tapper_li_fat_id`, `tapper_sccid`, `tapper_rdt_cid`, `tapper_epik`, `tapper_twclid` | `clid`, under the kind `clid_query_parameter_name` names |

The keys are not underscore-prefixed so WooCommerce's REST API shows them: Tapper's connector reads them from the order's `meta_data` in WooCommerce's REST API (`GET /wp-json/wc/v3/orders`).

- **Consent:** the tracker writes the `tsid` cookie only when the shopper's consent allows it, so no cookie means nothing saved. With the WP Consent API, `wp_has_consent('marketing')` is also required and the answer is relayed to `tapper.consent()`. Filter `tapper_wc_may_capture` overrides.
- No name, address, e-mail, phone, IP address or user agent is read.

## Develop

```
php tests/run.php          # the pure identity reader, no WordPress needed
bin/build-zip.sh           # dist/tapper-for-woocommerce-<version>.zip
```

A release is a tag `v<version>`: the workflow checks the version in the plugin header, the constant and `readme.txt` agree, runs the tests and attaches the zip.

See `CLAUDE.md` for the rules this repo follows.
