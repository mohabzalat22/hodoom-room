/**
 * Botiga Style Guide.
 */
( function( $, wp ) {
	'use strict';

	if ( ! wp.customize || ! wp.template ) {
		return;
	}

	const customize = wp.customize;
	let paletteTimer = null;

	const normalizeColor = ( color ) => {
		if ( ! color ) {
			return color;
		}

		if ( color.includes( '#' ) || color.includes( 'rgb' ) || color.includes( 'var(' ) ) {
			return color;
		}

		return `#${ color }`;
	};

	const rgbToHex = ( color ) => {
		const values = color.match( /\d+/g );

		if ( ! values || values.length < 3 ) {
			return color;
		}

		return `#${ values
			.slice( 0, 3 )
			.map( function( value ) {
				const hex = parseInt( value, 10 ).toString( 16 );

				return 1 === hex.length ? `0${ hex }` : hex;
			} )
			.join( '' ) }`;
	};

	const normalizePaletteColor = ( color ) => {
		if ( ! color ) {
			return color;
		}

		if ( color.includes( 'rgb' ) ) {
			return rgbToHex( color );
		}

		return normalizeColor( color );
	};

	const getSettingValue = ( settingId, fallback = '' ) => {
		if ( ! customize( settingId ) ) {
			return fallback;
		}

		const value = customize( settingId )();

		return undefined === value || null === value ? fallback : value;
	};

	const bindSetting = ( settingId, callback ) => {
		customize( settingId, function( value ) {
			value.bind( callback );
		} );
	};

	const getPresetPaletteColors = () => {
		if ( ! customize( 'color_palettes' ) || ! window.botigaStyleGuide ) {
			return [];
		}

		const paletteId = customize( 'color_palettes' )();
		const palettes = window.botigaStyleGuide.palettes || {};

		if ( ! palettes[ paletteId ] ) {
			return [];
		}

		return palettes[ paletteId ];
	};

	const getCustomPaletteSettingColors = () => {
		const colors = [];

		for ( let index = 1; index <= 8; index++ ) {
			const settingId = `custom_color${ index }`;

			if ( ! customize( settingId ) ) {
				return [];
			}

			colors.push( customize( settingId )() );
		}

		return colors;
	};

	const getCustomPaletteControlColors = () => {
		const colors = [];
		const controls = $( '#customize-control-custom_palette .botiga-color-control' );

		if ( ! controls.length ) {
			return [];
		}

		controls.each( function() {
			const control = $( this );
			const picker = control.find( '.botiga-color-picker' ).get( 0 );
			const input = control.find( '.botiga-color-input' );
			const pickerColor = picker && picker.style ? picker.style.backgroundColor : '';
			const inputValue = input.val();
			const inputAttr = input.attr( 'value' );

			colors.push( pickerColor || inputValue || inputAttr );
		} );

		return 8 === colors.length ? colors : [];
	};

	const isCustomPaletteEnabled = () => {
		if ( customize.control( 'custom_palette' ) && customize.control( 'custom_palette' ).active ) {
			return customize.control( 'custom_palette' ).active();
		}

		if ( ! customize( 'custom_palette_toggle' ) ) {
			return false;
		}

		const value = customize( 'custom_palette_toggle' )();

		return [ true, 1, '1', 'true', 'on', 'yes' ].includes( value );
	};

	const updatePaletteSwatches = ( colors ) => {
		if ( ! colors || ! colors.length ) {
			return;
		}

		colors.forEach( function( color, index ) {
			const settingId = `custom_color${ index + 1 }`;
			const normalizedColor = normalizePaletteColor( color );
			const colorSwatch = $( `.botiga-style-guide-color-swatch[data-color-setting="${ settingId }"]` );

			if ( ! colorSwatch.length ) {
				return;
			}

			colorSwatch.css( 'background-color', normalizedColor );
			colorSwatch
				.closest( '.botiga-style-guide-color' )
				.find( '.botiga-style-guide-color-value' )
				.text( normalizedColor );
		} );
	};

	const updateActivePaletteSwatches = () => {
		if ( isCustomPaletteEnabled() ) {
			const controlColors = getCustomPaletteControlColors();

			if ( controlColors.length ) {
				updatePaletteSwatches( controlColors );
				return;
			}

			updatePaletteSwatches( getCustomPaletteSettingColors() );
			return;
		}

		updatePaletteSwatches( getPresetPaletteColors() );
	};

	const schedulePaletteSwatchUpdate = ( immediateColors = [] ) => {
		if ( immediateColors.length ) {
			updatePaletteSwatches( immediateColors );
		}

		window.clearTimeout( paletteTimer );

		paletteTimer = window.setTimeout( function() {
			updateActivePaletteSwatches();
		}, 50 );

		window.setTimeout( function() {
			updateActivePaletteSwatches();
		}, 250 );

		window.setTimeout( function() {
			updateActivePaletteSwatches();
		}, 600 );
	};

	const getAttachmentUrl = ( attachmentId, callback ) => {
		if ( ! attachmentId || ! wp.media || ! wp.media.attachment ) {
			callback( '' );
			return;
		}

		const attachment = wp.media.attachment( attachmentId );

		attachment.fetch().done( function() {
			const sizes = attachment.get( 'sizes' ) || {};
			const url =
				( sizes.medium && sizes.medium.url ) ||
				( sizes.full && sizes.full.url ) ||
				attachment.get( 'url' ) ||
				'';

			callback( url );
		} );
	};

	const updateSiteTitle = () => {
		if ( ! customize( 'blogname' ) ) {
			return;
		}

		const siteTitle = customize( 'blogname' )() || '';

		$( '.botiga-style-guide-site-title' ).text( siteTitle );

		if ( ! customize( 'custom_logo' ) || customize( 'custom_logo' )() ) {
			return;
		}

		$( '.botiga-style-guide-identity-logo' )
			.empty()
			.append( $( '<strong />' ).text( siteTitle ) );
	};

	const updateSiteDescription = () => {
		if ( ! customize( 'blogdescription' ) ) {
			return;
		}

		$( '.botiga-style-guide-site-description' ).text( customize( 'blogdescription' )() || '' );
	};

	const updateLogo = () => {
		if ( ! customize( 'custom_logo' ) ) {
			return;
		}

		const logoId = customize( 'custom_logo' )();

		if ( ! logoId ) {
			updateSiteTitle();
			return;
		}

		getAttachmentUrl( logoId, function( url ) {
			if ( ! url ) {
				return;
			}

			$( '.botiga-style-guide-identity-logo' )
				.empty()
				.append(
					$( '<img />', {
						src: url,
						alt: '',
						class: 'botiga-style-guide-logo',
					} )
				);
		} );
	};

	/**
	 * Update the site icon preview.
	 *
	 * @return {void}
	 */
	const updateSiteIcon = () => {
		if ( ! customize( 'site_icon' ) ) {
			return;
		}

		const siteIconId = customize( 'site_icon' )();
		const browserTab = $( '.botiga-style-guide-browser-tab' );
		const siteIconTitle = browserTab.find( '.botiga-style-guide-site-icon-title' );

		siteIconTitle.text( getSettingValue( 'blogname', '' ) );

		if ( ! siteIconId ) {
			$( '.botiga-style-guide-site-icon' ).remove();

			if ( ! browserTab.find( '.botiga-style-guide-site-icon-placeholder' ).length ) {
				browserTab.prepend(
					$( '<span />', {
						class: 'botiga-style-guide-site-icon-placeholder',
						'aria-hidden': 'true',
					} ).append(
						$( '<span />', {
							class: 'dashicons dashicons-wordpress-alt',
						} )
					)
				);
			}

			return;
		}

		getAttachmentUrl( siteIconId, function( url ) {
			if ( ! url ) {
				return;
			}

			const siteIcon = $( '.botiga-style-guide-site-icon' );

			browserTab.find( '.botiga-style-guide-site-icon-placeholder' ).remove();

			if ( siteIcon.length ) {
				siteIcon.attr( 'src', url );
				return;
			}

			browserTab.prepend(
				$( '<img />', {
					src: url,
					alt: '',
					class: 'botiga-style-guide-site-icon',
				} )
			);
		} );
	};

	const updateSiteIdentity = () => {
		updateSiteTitle();
		updateSiteDescription();
		updateLogo();
		updateSiteIcon();
	};

	const getParsedFont = ( value ) => {
		try {
			return JSON.parse( value );
		} catch ( error ) {
			return {};
		}
	};

	const getGoogleFontData = ( value, defaultWeight ) => {
		const fontData = getParsedFont( value );

		return {
			font: fontData.font || 'System default',
			regularweight: fontData.regularweight || defaultWeight,
			isGoogleFont: true,
		};
	};

	const getAdobeFontData = ( value, defaultWeight ) => {
		if ( ! value ) {
			return {
				font: 'System default',
				regularweight: defaultWeight,
				isGoogleFont: false,
			};
		}

		const parts = value.split( '|' );
		const font =
			parts[ 0 ] && 'system-default' !== parts[ 0 ] ? parts[ 0 ] : 'System default';
		const weight = parts[ 1 ] ? `${ parts[ 1 ].replace( 'n', '' ) }00` : defaultWeight;

		return {
			font,
			regularweight: weight,
			isGoogleFont: false,
		};
	};

	const getCustomFontData = ( fontSettingId, weightSettingId, defaultWeight ) => {
		const font = getSettingValue( fontSettingId, '' );
		const weight = getSettingValue( weightSettingId, defaultWeight );

		return {
			font: font || 'System default',
			regularweight: weight || defaultWeight,
			isGoogleFont: false,
		};
	};

	const getActiveFontData = (
		googleSettingId,
		adobeSettingId,
		customSettingId,
		customWeightSettingId,
		defaultWeight
	) => {
		const fontsLibrary = getSettingValue( 'fonts_library', 'google' );

		if ( 'adobe' === fontsLibrary ) {
			return getAdobeFontData( getSettingValue( adobeSettingId, '' ), defaultWeight );
		}

		if ( 'custom' === fontsLibrary ) {
			return getCustomFontData( customSettingId, customWeightSettingId, defaultWeight );
		}

		return getGoogleFontData( getSettingValue( googleSettingId, '' ), defaultWeight );
	};

	const loadGoogleFont = ( id, fontData ) => {
		$( `#${ id }` ).remove();

		if ( ! fontData.font || 'System default' === fontData.font ) {
			return;
		}

		$( 'head' ).append( `<link id="${ id }" href="" rel="stylesheet">` );

		$( `#${ id }` ).attr(
			'href',
			`https://fonts.googleapis.com/css?family=${ fontData.font.replace(
				/ /g,
				'+'
			) }:${ fontData.regularweight }&display=swap`
		);
	};

	const applyFontData = ( selector, dataSelector, styleId, fontData ) => {
		if ( ! fontData.font ) {
			return;
		}

		if ( fontData.isGoogleFont ) {
			loadGoogleFont( styleId, fontData );
		} else {
			$( `#${ styleId }` ).remove();
		}

		$( selector ).css( {
			fontFamily: 'System default' === fontData.font ? '' : fontData.font,
			fontWeight: fontData.regularweight,
		} );

		$( dataSelector )
			.find( '.botiga-style-guide-typography-family' )
			.text( fontData.font );

		$( dataSelector )
			.find( '.botiga-style-guide-typography-weight' )
			.text( fontData.regularweight );
	};

	const applyHeadingTypographyStyles = () => {
		applyFontData(
			'.botiga-style-guide-heading',
			'.botiga-style-guide-headings-typography-data',
			'botiga-style-guide-google-fonts-headings-css',
			getActiveFontData(
				'botiga_headings_font',
				'botiga_headings_adobe_font',
				'botiga_headings_custom_font',
				'botiga_headings_custom_font_weight',
				700
			)
		);

		const headingTypography = {
			headings_line_height: 'line-height',
			headings_letter_spacing: 'letter-spacing',
			headings_text_transform: 'text-transform',
			headings_text_decoration: 'text-decoration',
			headings_font_style: 'font-style',
		};

		Object.keys( headingTypography ).forEach( function( settingId ) {
			if ( ! customize( settingId ) ) {
				return;
			}

			const property = headingTypography[ settingId ];
			const suffix = 'letter-spacing' === property ? 'px' : '';

			$( '.botiga-style-guide-heading' ).css(
				property,
				`${ customize( settingId )() }${ suffix }`
			);
		} );

		for ( let index = 1; index <= 6; index++ ) {
			const settingId = `h${ index }_font_size_desktop`;

			if ( ! customize( settingId ) ) {
				continue;
			}

			const newValue = customize( settingId )();

			$( `.botiga-style-guide h${ index }.botiga-style-guide-heading` ).css(
				'font-size',
				`${ newValue }px`
			);

			$( `.botiga-style-guide-headings-typography-data[data-heading="h${ index }"]` )
				.find( '.botiga-style-guide-typography-size' )
				.text( `${ newValue }px` );
		}
	};

	const applyBodyTypographyStyles = () => {
		applyFontData(
			'.botiga-style-guide-body-text',
			'.botiga-style-guide-body-typography-data',
			'botiga-style-guide-google-fonts-body-css',
			getActiveFontData(
				'botiga_body_font',
				'botiga_body_adobe_font',
				'botiga_body_custom_font',
				'botiga_body_custom_font_weight',
				400
			)
		);

		const bodyTypography = {
			body_font_size_desktop: 'font-size',
			body_font_style: 'font-style',
			body_line_height: 'line-height',
			body_letter_spacing: 'letter-spacing',
			body_text_transform: 'text-transform',
			body_text_decoration: 'text-decoration',
		};

		Object.keys( bodyTypography ).forEach( function( settingId ) {
			if ( ! customize( settingId ) ) {
				return;
			}

			const property = bodyTypography[ settingId ];
			const suffix =
				'font-size' === property || 'letter-spacing' === property ? 'px' : '';
			const newValue = customize( settingId )();

			$( '.botiga-style-guide-body-text' ).css( property, `${ newValue }${ suffix }` );

			if ( 'body_font_size_desktop' !== settingId ) {
				return;
			}

			$( '.botiga-style-guide-body-typography-data' )
				.find( '.botiga-style-guide-typography-size' )
				.text( `${ newValue }px` );
		} );
	};

	const applyTypographyStyles = () => {
		applyHeadingTypographyStyles();
		applyBodyTypographyStyles();
	};

	const applyColorSetting = ( settingId ) => {
		if ( ! customize( settingId ) ) {
			return;
		}

		const color = normalizePaletteColor( customize( settingId )() );
		const colorSwatch = $( `.botiga-style-guide-color-swatch[data-color-setting="${ settingId }"]` );

		colorSwatch.css( 'background-color', color );
		colorSwatch
			.closest( '.botiga-style-guide-color' )
			.find( '.botiga-style-guide-color-value' )
			.text( color );
	};

	const applyColorStyles = () => {
		for ( let index = 1; index <= 6; index++ ) {
			const settingId = `color_heading_${ index }`;

			applyColorSetting( settingId );

			$( `.botiga-style-guide h${ index }.botiga-style-guide-heading` ).css(
				'color',
				normalizePaletteColor( getSettingValue( settingId ) )
			);
		}

		[
			'background_color',
			'color_body_text',
			'content_cards_background',
			'color_link_default',
			'color_link_hover',
			'color_forms_text',
			'color_forms_background',
			'color_forms_borders',
			'color_forms_dividers',
			'color_forms_placeholder',
		].forEach( applyColorSetting );

		$( '.botiga-style-guide .botiga-style-guide-body-text' ).css(
			'color',
			normalizePaletteColor( getSettingValue( 'color_body_text' ) )
		);
	};

	const applyButtonStyles = () => {
		const button = $( '.botiga-style-guide-button' );

		if ( ! button.length ) {
			return;
		}

		const buttonStyles = {
			button_background_color: 'background-color',
			button_color: 'color',
			button_border_color: 'border-color',
			button_border_width: 'border-width',
			button_border_radius: 'border-radius',
			button_font_size_desktop: 'font-size',
			button_letter_spacing: 'letter-spacing',
			button_text_transform: 'text-transform',
			button_text_decoration: 'text-decoration',
		};

		Object.keys( buttonStyles ).forEach( function( settingId ) {
			if ( ! customize( settingId ) ) {
				return;
			}

			const property = buttonStyles[ settingId ];
			const needsPixels = [
				'border-width',
				'border-radius',
				'font-size',
				'letter-spacing',
			].includes( property );
			const normalizedValue = [
				'background-color',
				'color',
				'border-color',
			].includes( property )
				? normalizePaletteColor( customize( settingId )() )
				: customize( settingId )();
			const suffix = needsPixels ? 'px' : '';

			button.css( property, `${ normalizedValue }${ suffix }` );
		} );

		if ( customize( 'button_top_bottom_padding_desktop' ) ) {
			const value = customize( 'button_top_bottom_padding_desktop' )();

			button.css( {
				paddingTop: `${ value }px`,
				paddingBottom: `${ value }px`,
			} );
		}

		if ( customize( 'button_left_right_padding_desktop' ) ) {
			const value = customize( 'button_left_right_padding_desktop' )();

			button.css( {
				paddingLeft: `${ value }px`,
				paddingRight: `${ value }px`,
			} );
		}
	};

	const refreshStyleGuide = () => {
		updateSiteIdentity();
		updateActivePaletteSwatches();
		applyColorStyles();
		applyButtonStyles();
		applyTypographyStyles();
	};

	const openStyleGuide = () => {
		let styleGuide = $( '.botiga-style-guide' );

		if ( styleGuide.length && styleGuide.is( ':visible' ) ) {
			styleGuide.hide();
			return;
		}

		if ( styleGuide.length ) {
			styleGuide.show();
			refreshStyleGuide();
			return;
		}

		const template = wp.template( 'botiga-style-guide' );

		$( 'body' ).append( template );

		styleGuide = $( '.botiga-style-guide' );

		$( '.botiga-style-guide-close' ).on( 'click', function() {
			styleGuide.hide();
		} );

		refreshStyleGuide();
	};

	const focusCustomizerTarget = ( element ) => {
		const controlId = element.data( 'customizer-control' );
		const sectionId = element.data( 'customizer-section' );

		if ( controlId && customize.control( controlId ) ) {
			customize.control( controlId ).focus();
			return;
		}

		if ( sectionId && customize.section( sectionId ) ) {
			customize.section( sectionId ).focus();
		}
	};

	const bindColorSetting = ( settingId ) => {
		bindSetting( settingId, function( newValue ) {
			const color = normalizePaletteColor( newValue );
			const colorSwatch = $( `.botiga-style-guide-color-swatch[data-color-setting="${ settingId }"]` );

			colorSwatch.css( 'background-color', color );
			colorSwatch
				.closest( '.botiga-style-guide-color' )
				.find( '.botiga-style-guide-color-value' )
				.text( color );
		} );
	};

	const bindSiteIdentity = () => {
		bindSetting( 'blogname', function() {
			updateSiteTitle();
		} );

		bindSetting( 'blogdescription', function() {
			updateSiteDescription();
		} );

		bindSetting( 'custom_logo', function() {
			updateLogo();
		} );

		bindSetting( 'site_icon', function() {
			updateSiteIcon();
		} );
	};

	const bindColors = () => {
		for ( let index = 1; index <= 8; index++ ) {
			const settingId = `custom_color${ index }`;

			bindSetting( settingId, function() {
				schedulePaletteSwatchUpdate();
			} );
		}

		bindSetting( 'color_palettes', function() {
			schedulePaletteSwatchUpdate( getPresetPaletteColors() );
		} );

		bindSetting( 'custom_palette_toggle', function() {
			schedulePaletteSwatchUpdate();
		} );

		$( document ).on(
			'change input',
			'#customize-control-custom_palette .botiga-color-input',
			function() {
				schedulePaletteSwatchUpdate();
			}
		);

		$( document ).on(
			'click mouseup keyup',
			'#customize-control-custom_palette .botiga-color-picker, #customize-control-custom_palette .pcr-app, #customize-control-custom_palette .pcr-result',
			function() {
				schedulePaletteSwatchUpdate();
			}
		);

		for ( let index = 1; index <= 6; index++ ) {
			bindColorSetting( `color_heading_${ index }` );

			bindSetting( `color_heading_${ index }`, function( newValue ) {
				$( `.botiga-style-guide h${ index }.botiga-style-guide-heading` ).css(
					'color',
					normalizePaletteColor( newValue )
				);
			} );
		}

		[
			'background_color',
			'color_body_text',
			'content_cards_background',
			'color_link_default',
			'color_link_hover',
			'color_forms_text',
			'color_forms_background',
			'color_forms_borders',
			'color_forms_dividers',
			'color_forms_placeholder',
		].forEach( bindColorSetting );

		bindSetting( 'color_body_text', function( newValue ) {
			$( '.botiga-style-guide .botiga-style-guide-body-text' ).css(
				'color',
				normalizePaletteColor( newValue )
			);
		} );

		updateActivePaletteSwatches();
	};

	const bindHeadingFont = () => {
		[
			'fonts_library',
			'botiga_headings_font',
			'botiga_headings_adobe_font',
			'botiga_headings_custom_font',
			'botiga_headings_custom_font_weight',
		].forEach( function( settingId ) {
			bindSetting( settingId, function() {
				applyHeadingTypographyStyles();
			} );
		} );
	};

	const bindBodyFont = () => {
		[
			'fonts_library',
			'botiga_body_font',
			'botiga_body_adobe_font',
			'botiga_body_custom_font',
			'botiga_body_custom_font_weight',
		].forEach( function( settingId ) {
			bindSetting( settingId, function() {
				applyBodyTypographyStyles();
			} );
		} );
	};

	const bindTypography = () => {
		bindHeadingFont();
		bindBodyFont();

		[
			'headings_line_height',
			'headings_letter_spacing',
			'headings_text_transform',
			'headings_text_decoration',
			'headings_font_style',
		].forEach( function( settingId ) {
			bindSetting( settingId, function() {
				applyHeadingTypographyStyles();
			} );
		} );

		for ( let index = 1; index <= 6; index++ ) {
			bindSetting( `h${ index }_font_size_desktop`, function() {
				applyHeadingTypographyStyles();
			} );
		}

		[
			'body_font_size_desktop',
			'body_font_style',
			'body_line_height',
			'body_letter_spacing',
			'body_text_transform',
			'body_text_decoration',
		].forEach( function( settingId ) {
			bindSetting( settingId, function() {
				applyBodyTypographyStyles();
			} );
		} );
	};

	const bindButtons = () => {
		[
			'button_background_color',
			'button_color',
			'button_border_color',
			'button_border_width',
			'button_border_radius',
			'button_font_size_desktop',
			'button_letter_spacing',
			'button_text_transform',
			'button_text_decoration',
			'button_top_bottom_padding_desktop',
			'button_left_right_padding_desktop',
		].forEach( function( settingId ) {
			bindSetting( settingId, function() {
				applyButtonStyles();
			} );
		} );
	};

	const bindNavigation = () => {
		$( document ).on( 'click', '.botiga-style-guide-navigation a', function( event ) {
			const target = $( this ).attr( 'href' );
			const targetElement = target ? $( target ) : $();

			if ( ! target || '#' !== target.charAt( 0 ) || ! targetElement.length ) {
				return;
			}

			event.preventDefault();

			targetElement.get( 0 ).scrollIntoView( {
				behavior: 'smooth',
				block: 'start',
			} );
		} );
	};

	$( document ).ready( function() {
		$( document ).on( 'click', '.botiga-style-guide-toggle-button', function( event ) {
			event.preventDefault();
			openStyleGuide();
		} );

		$( document ).on( 'click', '.botiga-style-guide-customizer-link', function( event ) {
			event.preventDefault();
			focusCustomizerTarget( $( this ) );
		} );

		bindNavigation();
		bindSiteIdentity();
		bindColors();
		bindTypography();
		bindButtons();
	} );
} )( jQuery, window.wp || {} );
