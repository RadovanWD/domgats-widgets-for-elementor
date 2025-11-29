=== DomGat's Widgets for Elementor ===
Contributors: domgat
Tags: elementor, widgets, grid, filter, ajax, masonry, slider
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An Elementor extension that introduces a powerful Dynamic Filter Grid widget with AJAX filtering, deep linking, load-more/infinite scroll, slider/masonry layouts, and Elementor template support.

== Description ==

DomGat's Widgets for Elementor adds the Dynamic Filter Grid, helping you display posts, WooCommerce products, or custom post types in a responsive grid, masonry, or slider layout. Features include:

* AJAX filtering with dropdowns/checkboxes/pills/tags and deep-linkable URLs.
* Sorting (date/title/custom field), offset, include/exclude IDs, ACF/meta ordering.
* Pagination types: numbers, load more, infinite scroll with lazy-loading images.
* Layouts: grid, masonry, slider (Swiper-enabled), equal-height cards.
* Elementor template per card or built-in card layout with up to 3 CTA buttons.
* No-results template or custom message.
* External JSON/REST source option for remote data.
* Analytics hooks, animations, responsive controls, and accessibility-friendly markup.

= Transport & Security =
* Uses the REST route `domgats-widgets/v1/grid` by default with a shared nonce (`wp_create_nonce( 'dgwfe-request' )`).
* Falls back to `admin-ajax.php` via the `domgats_grid` action when REST is blocked.
* Both transports sanitize input, require the nonce, and let you filter access with `domgats/widgets/rest_permission`; logged-in users must have the `read` capability.

== Installation ==

1. Upload `domgats-widgets-for-elementor` to the `/wp-content/plugins/` directory or install via the WordPress Plugins screen.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Ensure Elementor is installed and activated.
4. Locate **Dynamic Filter Grid** in the Elementor widget panel.

== Frequently Asked Questions ==

= Does this require Elementor? =
Yes, Elementor is required and the plugin will alert you if it is missing.

= Does it support ACF/meta sorting? =
Yes. Choose a meta order option and provide the meta key; the widget will order by that value.

= Can it fetch external data? =
Yes. Enable External Data and provide a JSON endpoint (with optional dot-notation path).

== Changelog ==

= 1.0.0 =
* Full production build: AJAX filtering/sorting, load more/infinite scroll, deep linking, slider/masonry layouts, animations, CTA buttons, Elementor templates, external JSON, analytics hooks, and accessibility improvements.
