"use strict";

(function ($) {
  'use strict';

  // ── Campaign Selection (BXGY pattern) ────────────────────────────
  $(document).on('click', '.merchant-flexible-content-control.buy-now-style .layout', function () {
    var $this = $(this);
    var $parent = $this.closest('.merchant-flexible-content-control.buy-now-style');
    $parent.find('.layout').removeClass('active');
    $this.addClass('active');
    initPreview();
  });
  $(document).on('change.merchant keyup', function () {
    initPreview();
    updatePopupPreview();
  });

  // ── Button Size Preset Logic ─────────────────────────────────────
  var autoSelect = false;
  var sizes = {
    small: {
      padding_top_bottom: 5,
      padding_left_right: 10
    },
    medium: {
      padding_top_bottom: 12,
      padding_left_right: 24
    },
    large: {
      padding_top_bottom: 15,
      padding_left_right: 30
    }
  };
  $(document).on('change input', '.merchant-field-button-size select', function () {
    if (autoSelect) return;
    var size = $(this).val();
    if (size !== 'custom' && sizes[size]) {
      $('[name="merchant[padding_top_bottom]"]').val(sizes[size].padding_top_bottom);
      $('[name="merchant[padding_left_right]"]').val(sizes[size].padding_left_right);
    }
  });
  $(document).on('change input', '.merchant-field-padding_top_bottom input, .merchant-field-padding_left_right input', function () {
    var field = $('.merchant-field-button-size select');
    if (field.val() !== 'custom') {
      autoSelect = true;
      field.val('custom').trigger('change');
      autoSelect = false;
    }
  });

  // ── Preview Updater ──────────────────────────────────────────────
  function initPreview() {
    var layout = $('.merchant-flexible-content-control.buy-now-style').find('.layout.active');
    if (!layout.length) return;

    // Campaign-level fields.
    var buttonText = layout.find('.merchant-field-button_text input').val() || 'Buy Now';
    var buttonIcon = layout.find('.merchant-field-button_icon select').val() || 'none';
    var iconPosition = layout.find('.merchant-field-button_icon_position select').val() || 'before';
    var customSvg = layout.find('.merchant-field-button_icon_svg textarea').val() || '';
    var $btn = $('.merchant-buy-now-button');

    // Update button text — preserve icon spans.
    updateButtonContent($btn, buttonText, buttonIcon, iconPosition, customSvg);

    // Global fields (Button Style section) — colors, font-size, border-radius.
    var textColor = $('[name="merchant[text-color]"]').val();
    var bgColor = $('[name="merchant[background-color]"]').val();
    var borderColor = $('[name="merchant[border-color]"]').val();
    var fontSize = $('[name="merchant[font-size]"]').filter('[type=number]').val();
    var borderRadius = $('[name="merchant[border-radius]"]').filter('[type=number]').val();
    $btn.css({
      'color': textColor || '',
      'background-color': bgColor || '',
      'border-color': borderColor || '',
      'font-size': fontSize ? fontSize + 'px' : '',
      'border-radius': borderRadius ? borderRadius + 'px' : ''
    });
  }

  /**
   * Rebuild button inner HTML with text + optional icon.
   */
  function updateButtonContent($btn, text, iconKey, position, customSvg) {
    // Icon SVG map is localized from PHP.
    var icons = typeof merchantBuyNowPreview !== 'undefined' ? merchantBuyNowPreview.icons : {};
    var svg = '';
    if (iconKey === 'custom' && customSvg) {
      svg = '<span class="merchant-buy-now-icon">' + customSvg + '</span>';
    } else if (iconKey !== 'none' && icons[iconKey]) {
      svg = '<span class="merchant-buy-now-icon">' + icons[iconKey] + '</span>';
    }
    var iconAfter = '';
    var iconBefore = '';
    if (svg) {
      if (position === 'after') {
        iconAfter = svg.replace('merchant-buy-now-icon', 'merchant-buy-now-icon merchant-buy-now-icon--after');
      } else {
        iconBefore = svg;
      }
    }
    $btn.html(iconBefore + text + iconAfter);
  }

  // ── Preview Panel Switching (FBT pattern) ────────────────────────
  function showProductPagePreview() {
    $('.merchant-buy-now-preview-product-page').addClass('show');
    $('.merchant-buy-now-preview-popup').removeClass('show');
  }
  function showPopupPreview() {
    $('.merchant-buy-now-preview-popup').addClass('show');
    $('.merchant-buy-now-preview-product-page').removeClass('show');
    updatePopupPreview();
  }

  // Detect which settings section is open and switch preview accordingly.
  $(document).on('click', '.merchant-module-page-setting-box', function () {
    var $box = $(this);
    // Check if this box contains any popup style or layout fields.
    var isPopupSection = $box.find('[class*="merchant-field-popup_"], [class*="merchant-field-upsell_layout"], [class*="merchant-field-upsell_button_layout"]').length > 0;
    if (isPopupSection) {
      showPopupPreview();
    } else {
      showProductPagePreview();
    }
  });

  // ── Popup Style Live Preview ─────────────────────────────────────
  function updatePopupPreview() {
    var $popup = $('.merchant-buy-now-popup-preview-box');
    if (!$popup.length) return;

    // Colors.
    $popup.css('background-color', $('[name="merchant[popup_bg_color]"]').val() || '#ffffff');
    $popup.css('border-radius', ($('[name="merchant[popup_border_radius]"]').filter('[type=number]').val() || 12) + 'px');
    $popup.find('.merchant-buy-now-popup-preview-title').css('color', $('[name="merchant[popup_title_color]"]').val() || '#212121');
    $popup.find('.merchant-buy-now-popup-preview-description').css('color', $('[name="merchant[popup_description_color]"]').val() || '#757575');
    $popup.find('.product-name').css('color', $('[name="merchant[popup_product_name_color]"]').val() || '#212121');
    $popup.find('.product-price').css('color', $('[name="merchant[popup_price_color]"]').val() || '#212121');
    $popup.find('.merchant-buy-now-popup-preview-accept').css({
      'background-color': $('[name="merchant[popup_accept_bg_color]"]').val() || '#212121',
      'color': $('[name="merchant[popup_accept_text_color]"]').val() || '#ffffff'
    });
    $popup.find('.merchant-buy-now-popup-preview-decline').css('color', $('[name="merchant[popup_decline_text_color]"]').val() || '#757575');
    $('.merchant-buy-now-popup-preview-overlay').css('background-color', $('[name="merchant[popup_overlay_color]"]').val() || 'rgba(0,0,0,0.5)');

    // Sizing.
    $popup.css('max-width', ($('[name="merchant[popup_max_width]"]').filter('[type=number]').val() || 480) + 'px');
    $popup.css('padding', ($('[name="merchant[popup_padding]"]').filter('[type=number]').val() || 24) + 'px');
    var borderWidth = $('[name="merchant[popup_border_width]"]').filter('[type=number]').val() || 0;
    var borderColor = $('[name="merchant[popup_border_color]"]').val() || '#e0e0e0';
    $popup.css('border', borderWidth > 0 ? borderWidth + 'px solid ' + borderColor : 'none');

    // Typography.
    $popup.find('.merchant-buy-now-popup-preview-title').css('font-size', ($('[name="merchant[popup_title_font_size]"]').filter('[type=number]').val() || 18) + 'px');

    // Button border radius.
    var btnRadius = ($('[name="merchant[popup_accept_btn_radius]"]').filter('[type=number]').val() || 6) + 'px';
    $popup.find('.merchant-buy-now-popup-preview-accept, .merchant-buy-now-popup-preview-decline').css('border-radius', btnRadius);

    // Button font size.
    var btnFontSize = ($('[name="merchant[popup_btn_font_size]"]').filter('[type=number]').val() || 15) + 'px';
    $popup.find('.merchant-buy-now-popup-preview-accept, .merchant-buy-now-popup-preview-decline').css('font-size', btnFontSize);

    // Product layout (list / grid).
    var productLayout = $('[name="merchant[upsell_layout]"]').val() || 'list';
    var $productsContainer = $popup.find('.merchant-buy-now-popup-preview-products');
    $productsContainer.removeClass('is-layout-list is-layout-grid').addClass('is-layout-' + productLayout);

    // Button layout (stacked / stacked-reverse / side-by-side / accept-only).
    var btnLayout = $('[name="merchant[upsell_button_layout]"]').val() || 'stacked';
    var $actions = $popup.find('.merchant-buy-now-popup-preview-actions');
    $actions.removeClass('is-btn-stacked is-btn-stacked-reverse is-btn-side-by-side is-btn-accept-only').addClass('is-btn-' + btnLayout);

    // Timer countdown badge.
    var countdownClass = 'merchant-buy-now-popup-preview-countdown';
    $popup.find('.' + countdownClass).remove(); // Clear any existing.

    var timerEnabled = $('[name="merchant[popup_timer_enabled]"]').is(':checked');
    if (timerEnabled) {
      var timerAction = $('[name="merchant[popup_timer_action]"]').val() || 'auto_decline';
      var timerDuration = $('[name="merchant[popup_timer_duration]"]').filter('[type=number]').val() || 30;
      var badgeHtml = '<span class="' + countdownClass + '">' + '<svg viewBox="0 0 36 36"><circle class="countdown-track" cx="18" cy="18" r="16" />' + '<circle class="countdown-fill" cx="18" cy="18" r="16" stroke-dasharray="100.53" stroke-dashoffset="35" /></svg>' + '<span class="countdown-seconds">' + timerDuration + '</span></span>';
      var $target;
      if (timerAction === 'auto_accept') {
        $target = $popup.find('.merchant-buy-now-popup-preview-accept');
      } else if (timerAction === 'auto_decline') {
        $target = $popup.find('.merchant-buy-now-popup-preview-decline');
      } else {
        // do_nothing — show near the title area as a standalone badge.
        $target = $popup.find('.merchant-buy-now-popup-preview-title');
      }
      $target.append(badgeHtml);
    }
  }

  // ── Init: Select first campaign ──────────────────────────────────
  $(document).ready(function () {
    $('.merchant-flexible-content-control.buy-now-style .layout:first-child').addClass('active').trigger('click');
  });
})(jQuery);