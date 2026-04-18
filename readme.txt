=== Bagdock Calculator ===
Contributors: bagdock
Tags: self storage, storage calculator, size calculator, bagdock, storage
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Embed the Bagdock storage size calculator anywhere on your WordPress site. Shortcode, Gutenberg block, Elementor widget, and Classic editor button.

== Description ==

The official WordPress plugin for [Bagdock](https://bagdock.com) operators. Drop the Bagdock size calculator into any page, post, sidebar, or footer on your WordPress site so visitors can work out what unit size they need before they book.

= Features =

* **Shortcode** — `[bagdock_calculator]` with attributes for facility, preset, region, storefront URL, and button label.
* **Gutenberg block** — searchable in the block inserter. Server-side rendered with a live preview.
* **Elementor widget** — drag-and-drop with a full controls panel.
* **Classic editor button** — inserts a pre-filled shortcode with a tiny prompt.
* **Facility-aware** — supply a `facility_id` and the calculator honours that facility's custom rooms, items, and overrides. Perfect for multi-location operators.
* **Region-aware** — sizing ceilings automatically adapt to UK/IE, EU, or US markets.
* **Zero build step** — the plugin loads the SDK from the Bagdock CDN. No npm, no bundler, no server resources.
* **Events** — the SDK fires `bagdock:calculator:open`, `bagdock:calculator:close`, and `bagdock:calculator:apply` so themes can hook into the flow (analytics, A/B tests, custom redirects).

= Requirements =

* A Bagdock operator account with the calculator enabled.
* An embed key with the `calculator:read` scope (issued from the operator dashboard).

See the [GitHub repository](https://github.com/bagdock/bagdock-calculator-wp) for the full integration reference.

== Installation ==

1. Upload the `bagdock-calculator` folder to `/wp-content/plugins/` — or install from within WordPress via Plugins → Add New.
2. Activate **Bagdock Calculator** from the Plugins screen.
3. Go to **Settings → Bagdock Calculator** and paste your embed key.
4. Drop the `[bagdock_calculator]` shortcode on any page, or search for "Bagdock Calculator" in the block inserter.

== Frequently Asked Questions ==

= Where do I get an embed key? =

Create an embed key with the `calculator:read` scope from your Bagdock operator account. Embed keys are safe to expose on your public site; they cannot modify data.

= Can I show a different facility's calculator on different pages? =

Yes. Set a default facility in the plugin settings for most pages, then override per placement via the `facility_id` attribute on the shortcode, the block sidebar, or the Elementor controls.

= Does the calculator work without an embed key? =

Yes. Without a key the calculator renders with the built-in presets but cannot load your branding, pricing, or overrides. We recommend setting a key for the full experience.

= Can I style the calculator? =

Yes. Appearance (colours, radius, typography) is configured from the operator dashboard and ships with the embed. Host theme CSS can override `bdk-calc-*` variables if you need finer control.

= Does this plugin work with Elementor? =

Yes. Drag the **Bagdock Calculator** widget from the general category into any section.

== Screenshots ==

1. Settings page with embed key, defaults, and facility override.
2. Shortcode rendered on a live page.
3. Gutenberg block with inspector controls.
4. Elementor widget controls panel.

== Changelog ==

= 0.1.0 =
* Initial release. Shortcode, Gutenberg block, Elementor widget, Classic editor button.

== Upgrade Notice ==

= 0.1.0 =
Initial release.
