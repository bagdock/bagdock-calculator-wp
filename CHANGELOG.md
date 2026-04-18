# Changelog

All notable changes to the Bagdock Calculator WordPress plugin will be documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - Unreleased

Initial release.

### Added
- `[bagdock_calculator]` shortcode with attributes for `embed_key`, `facility_id`, `storefront_url`, `region`, `preset`, `button_label`, `mode`, and `class`.
- Gutenberg block (`bagdock/calculator`) with server-side rendering and an inspector sidebar mirroring every shortcode attribute.
- Elementor widget with a controls panel, including per-widget overrides for facility, preset, region, and storefront URL.
- Classic editor TinyMCE button that opens a small prompt and inserts a pre-filled shortcode.
- Settings page under **Settings → Bagdock Calculator** with embed key, default facility, storefront URL, region override, default preset, and advanced CDN overrides.
- Filter `bagdock_calculator_always_enqueue` to switch from sitewide enqueue to on-demand enqueue.
- Input validation for `ek_live_*` / `ek_test_*` embed keys and `fac_*` facility identifiers.
- Uninstall handler that removes plugin options when the admin explicitly deletes the plugin. Shortcode output in existing posts is never altered.

[0.1.0]: https://github.com/bagdock/bagdock-calculator-wp/releases/tag/v0.1.0
