import DynamicShortcodeInput from "./shortcode/dynamicShortcode";
import { escapeAttribute, escapeHTML } from "@wordpress/escape-html";
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { PanelBody, PanelRow } from '@wordpress/components';
import { Fragment, createElement } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
const { serverSideRender: ServerSideRender } = wp;
const el = createElement;

/**
 * Register: WooCategory Gutenberg Block.
 */
registerBlockType("woo-category-slider/shortcode", {
	title: escapeHTML(__("Reno Product Category", "woo-category-slider-grid") ),
  description: escapeHTML( __(
    "Use Reno Product Category to insert a shortcode in your page.",
    "woo-category-slider-grid"
  )),
  icon: escapeAttribute(' wcsp-icon-wcs-icon'),
  category: "common",
  supports: {
    html: true,
  },
  edit: (props) => {
    const { attributes, setAttributes } = props;
    var shortCodeList = sp_woo_category_slider_load_script.shortCodeList;

    let scriptLoad = ( shortcodeId ) => {

      let spwcspBlockLoaded = false;
      let spwcspBlockLoadedInterval = setInterval(function () {
        let uniqId = jQuery("#sp-wcsp-wrapper-" + shortcodeId).parents().parents().attr('id');
        if (document.getElementById(uniqId)) {
          // Preloader JS
          jQuery('#wcsp-preloader-' + shortcodeId).css({ 'opacity': 0, 'display': 'none' });
          jQuery('#sp-wcsp-slider-section-' + shortcodeId).animate({ opacity: 1 }, 600);
          
          jQuery.getScript(sp_woo_category_slider_load_script.loadScript);
          jQuery.getScript(sp_woo_category_slider_load_script.path + 'public/js/preloader.min.js');
          spwcspBlockLoaded = true;
          uniqId = '';
        }
        if (spwcspBlockLoaded) {
          clearInterval(spwcspBlockLoadedInterval);
        }
        if ( 0 == shortcodeId ) {
          clearInterval(spwcspBlockLoadedInterval);
        }
      }, 10);
    }

    let updateShortcode = ( updateShortcode ) => {
      setAttributes({shortcode: escapeAttribute( updateShortcode.target.value )});
    }

    let shortcodeUpdate = (e) => {
      updateShortcode(e);
      let shortcodeId = escapeAttribute( e.target.value );
      scriptLoad(shortcodeId);	
    }


    if (jQuery('.sp-wcsp-slider-section:not(.sp-wcsp-loaded)').length > 0 ) {
      let shortcodeId = escapeAttribute( attributes.shortcode );
      scriptLoad(shortcodeId);
    }

    if( attributes.preview ) {
      return (
        el('div', {className: 'spwcsp_shortcode_block_preview_image'},
          el('img', { src: escapeAttribute( sp_woo_category_slider_load_script.path + "admin/GutenbergBlock/assets/wcs-block-preview.svg" )})
        )
      )
    }

    if ( shortCodeList.length === 0 ) {
      return (
        <Fragment>
          {
            el('div', {className: 'components-placeholder components-placeholder is-large'}, 
              el('div', {className: 'components-placeholder__label'}, 
                el('span', {className: 'block-editor-block-icon wcsp-icon-wcs-icon'}),
				  escapeHTML(__("Reno Product Category", "woo-category-slider-grid") )
              ),
              el('div', {className: 'components-placeholder__instructions'}, 
				  escapeHTML(__("No shortcode found. ", "woo-category-slider-grid") ),
                el('a', {href: escapeAttribute( sp_woo_category_slider_load_script.url )}, 
					escapeHTML(__("Create a shortcode now!", "woo-category-slider-grid") )
                )
              )
            )
          }
        </Fragment>
      );
    }

    if ( ! attributes.shortcode || attributes.shortcode == 0 ) {
      return (
        <Fragment>
          <InspectorControls>
            <PanelBody title="Select a shortcode">
                <PanelRow>
                  <DynamicShortcodeInput
                    attributes={attributes}
                    shortCodeList={shortCodeList}
                    shortcodeUpdate={shortcodeUpdate}
                  />
                </PanelRow>
            </PanelBody>
          </InspectorControls>
          {
            el('div', {className: 'components-placeholder components-placeholder is-large'}, 
              el('div', {className: 'components-placeholder__label'},
                el('span', { className: 'block-editor-block-icon wcsp-icon-wcs-icon'}),
				  escapeHTML(__("Reno Product Category", "woo-category-slider-grid") )
              ),
				el('div', { className: 'components-placeholder__instructions' }, escapeHTML(__("Select a shortcode", "woo-category-slider-grid") ) ),
              <DynamicShortcodeInput
                attributes={attributes}
                shortCodeList={shortCodeList}
                shortcodeUpdate={shortcodeUpdate}
              />
            )
          }
        </Fragment>
      );
    }

    return (
      <Fragment>
        <InspectorControls>
            <PanelBody title="Select a shortcode">
                <PanelRow>
                  <DynamicShortcodeInput
                    attributes={attributes}
                    shortCodeList={shortCodeList}
                    shortcodeUpdate={shortcodeUpdate}
                  />
                </PanelRow>
            </PanelBody>
        </InspectorControls>
        <ServerSideRender block="woo-category-slider/shortcode" attributes={attributes} />
      </Fragment>
    );
  },
  save() {
    // Rendering in PHP
    return null;
  },
});
