/*
 * Classic editor TinyMCE button. Prompts for an optional facility_id, then
 * inserts a `[bagdock_calculator]` shortcode at the caret.
 */
(function () {
	if (typeof tinymce === 'undefined') return;

	tinymce.PluginManager.add('bagdock_calculator', function (editor) {
		editor.addButton('bagdock_calculator', {
			title: 'Bagdock Calculator',
			icon: 'dashicon dashicons-calculator',
			// Fall back to a generic icon if the dashicon isn't wired up
			// (some custom admin themes strip the dashicon font from the
			// TinyMCE iframe).
			classes: 'bagdock-calculator-button-tinymce',
			onclick: function () {
				editor.windowManager.open({
					title: 'Insert Bagdock Calculator',
					body: [
						{
							type: 'textbox',
							name: 'facility_id',
							label: 'Facility ID (optional)',
							value: ''
						},
						{
							type: 'listbox',
							name: 'preset',
							label: 'Preset',
							values: [
								{ text: 'Use default', value: '' },
								{ text: 'Home goods', value: 'home-goods' },
								{ text: 'Vehicle', value: 'vehicle' },
								{ text: 'Business', value: 'business' },
								{ text: 'Wine', value: 'wine' }
							],
							value: ''
						},
						{
							type: 'textbox',
							name: 'button_label',
							label: 'Button label',
							value: 'Size calculator'
						}
					],
					onsubmit: function (e) {
						var parts = ['bagdock_calculator'];
						if (e.data.facility_id) {
							parts.push('facility_id="' + e.data.facility_id.replace(/"/g, '') + '"');
						}
						if (e.data.preset) {
							parts.push('preset="' + e.data.preset + '"');
						}
						if (e.data.button_label) {
							parts.push('button_label="' + e.data.button_label.replace(/"/g, '') + '"');
						}
						editor.insertContent('[' + parts.join(' ') + ']');
					}
				});
			}
		});
	});
})();
