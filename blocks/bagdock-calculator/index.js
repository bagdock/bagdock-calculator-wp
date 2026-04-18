/*
 * Gutenberg block editor entry. Ships as plain ES5-compatible JS (no JSX,
 * no JSX-pragma) so the plugin works on sites that don't run wp-scripts
 * during install. When a CI build step produces `build/index.js`, the
 * PHP enqueue prefers that optimised artefact.
 */
(function (wp) {
	if (!wp || !wp.blocks || !wp.element || !wp.blockEditor) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __ = (wp.i18n && wp.i18n.__) || function (s) { return s; };
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var SelectControl = wp.components.SelectControl;
	var ServerSideRender = wp.serverSideRender;

	wp.blocks.registerBlockType('bagdock/calculator', {
		edit: function (props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			// useBlockProps wires spacing/align support the block declares
			// in block.json. Keeping the wrapping <div> thin.
			var blockProps = useBlockProps({ className: 'bagdock-calculator-block-editor' });

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __('Calculator', 'bagdock-calculator'), initialOpen: true },
						el(SelectControl, {
							label: __('Display', 'bagdock-calculator'),
							value: attributes.mode || 'button',
							options: [
								{ value: 'button', label: __('Button that opens modal', 'bagdock-calculator') },
								{ value: 'inline', label: __('Inline (always visible)', 'bagdock-calculator') }
							],
							onChange: function (v) { setAttributes({ mode: v }); }
						}),
						attributes.mode !== 'inline' && el(TextControl, {
							label: __('Button label', 'bagdock-calculator'),
							value: attributes.buttonLabel || '',
							placeholder: __('Size calculator', 'bagdock-calculator'),
							onChange: function (v) { setAttributes({ buttonLabel: v }); }
						}),
						el(TextControl, {
							label: __('Facility ID override', 'bagdock-calculator'),
							value: attributes.facilityId || '',
							placeholder: 'fac_…',
							help: __('Leave blank to inherit the default from plugin settings.', 'bagdock-calculator'),
							onChange: function (v) { setAttributes({ facilityId: v }); }
						}),
						el(SelectControl, {
							label: __('Preset', 'bagdock-calculator'),
							value: attributes.preset || '',
							options: [
								{ value: '', label: __('Use default', 'bagdock-calculator') },
								{ value: 'home-goods', label: __('Home goods', 'bagdock-calculator') },
								{ value: 'vehicle', label: __('Vehicle', 'bagdock-calculator') },
								{ value: 'business', label: __('Business', 'bagdock-calculator') },
								{ value: 'wine', label: __('Wine', 'bagdock-calculator') }
							],
							onChange: function (v) { setAttributes({ preset: v }); }
						}),
						el(SelectControl, {
							label: __('Region override', 'bagdock-calculator'),
							value: attributes.region || '',
							options: [
								{ value: '', label: __('Use default', 'bagdock-calculator') },
								{ value: 'uk_ie', label: __('UK & Ireland', 'bagdock-calculator') },
								{ value: 'eu', label: __('Europe', 'bagdock-calculator') },
								{ value: 'usa', label: __('United States', 'bagdock-calculator') }
							],
							onChange: function (v) { setAttributes({ region: v }); }
						}),
						el(TextControl, {
							label: __('Storefront URL override', 'bagdock-calculator'),
							value: attributes.storefrontUrl || '',
							placeholder: 'https://example.com/book',
							onChange: function (v) { setAttributes({ storefrontUrl: v }); }
						})
					)
				),
				el(
					'div',
					blockProps,
					ServerSideRender
						? el(ServerSideRender, {
							block: 'bagdock/calculator',
							attributes: attributes,
							// EmptyResponsePlaceholder surfaces a helpful
							// message when the Settings page hasn't been
							// configured yet, instead of an unhelpful
							// "Block rendered no content" placeholder.
							EmptyResponsePlaceholder: function () {
								return el(
									'div',
									{ className: 'bagdock-calculator-block-empty' },
									el('strong', null, __('Bagdock Calculator', 'bagdock-calculator')),
									el('p', null, __('Add your embed key in Settings › Bagdock Calculator to see a preview here.', 'bagdock-calculator'))
								);
							}
						})
						: el('p', null, __('Bagdock Calculator block preview requires WordPress 6.0 or newer.', 'bagdock-calculator'))
				)
			);
		},
		save: function () {
			// Server-side rendered. Returning null keeps the post content
			// free of serialised markup, so future SDK changes don't require
			// post re-saves.
			return null;
		}
	});
})(window.wp);
