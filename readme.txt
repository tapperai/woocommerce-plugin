=== Tapper for WooCommerce ===
Contributors: tapperai
Tags: woocommerce, advertising, attribution, conversions, tracking
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Match every WooCommerce order to the ad that earned it. Tapper saves the visitor and the ad click on each order.

== Description ==

Tapper for WooCommerce connects your store to Tapper. It does two things:

1. Loads Tapper's tracker on your storefront, so each visit and each ad click is followed.
2. When a shopper places an order, saves the visitor and the ad click on that order (as order notes data, not visible to the shopper), so Tapper can match the sale to the campaign that earned it and send it back to Google, Meta, TikTok and your other ad platforms.

It works with the classic checkout and with the Cart and Checkout blocks, and with High-Performance Order Storage.

**Consent.** Only shoppers who accepted tracking are saved. The plugin reads Tapper's own tracker cookie, which the tracker writes only when the shopper's consent allows it. If your store runs a consent plugin that supports the WP Consent API, the shopper's marketing consent is also required, and Tapper's tracker is told the answer.

**What is saved on an order.** `tapper_visitor_id` (Tapper's random visitor id) and the ad click id the shopper arrived with, for example `tapper_gclid`, `tapper_fbclid` or `tapper_ttclid`. No name, address, e-mail or phone number is read or saved by this plugin.

== Installation ==

1. Upload the plugin zip in Plugins > Add New > Upload Plugin, then activate it.
2. Open Settings > Tapper and paste your Tapper tracking id (it starts with pk_live_).
3. In Tapper, connect the store from your WooCommerce source.

== Frequently Asked Questions ==

= Does it slow my store down? =
The tracker loads asynchronously. Saving the visitor on an order reads one cookie and adds a few small order fields.

= Can I turn off saving the visitor on orders? =
Yes. Settings > Tapper > Save the visitor on orders > No. The tracker still loads.

= Which hooks can I use? =
`tapper_wc_may_capture` (filter, bool) decides whether an order's visitor is saved. `tapper_wc_load_tracker` (filter, bool) decides whether the tracker is loaded.

== Changelog ==

= 1.0.0 =
* First release: tracker loader, visitor and ad click saved on classic and block checkout orders, consent respected.
