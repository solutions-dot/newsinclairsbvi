/*
 * Editor half of the "Sinclairs Slider" block. The block is
 * server-rendered, so all this does is register it in the inserter and
 * offer the optional list of slide IDs; the preview comes from the
 * server via ServerSideRender.
 */
(function (blocks, element, blockEditor, components, serverSideRender, i18n) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType('sinclairs/slider', {
		apiVersion: 2,
		title: __('Sinclairs Slider', 'sinclairs-slider'),
		description: __('The hero slider. Slides are managed under Slider in the admin menu.', 'sinclairs-slider'),
		category: 'media',
		icon: 'images-alt2',
		supports: { html: false, multiple: true },
		attributes: { ids: { type: 'string', default: '' } },

		edit: function (props) {
			return el(
				element.Fragment,
				null,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __('Slides', 'sinclairs-slider') },
						el(components.TextControl, {
							label: __('Slide IDs', 'sinclairs-slider'),
							help: __('Leave blank to show every published slide in order. Otherwise a comma-separated list, e.g. 12,8,3.', 'sinclairs-slider'),
							value: props.attributes.ids,
							onChange: function (value) { props.setAttributes({ ids: value }); }
						})
					)
				),
				el(
					'div',
					blockEditor.useBlockProps ? blockEditor.useBlockProps() : {},
					el(serverSideRender, {
						block: 'sinclairs/slider',
						attributes: props.attributes
					})
				)
			);
		},

		save: function () {
			return null;
		}
	});
})(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.serverSideRender,
	window.wp.i18n
);
