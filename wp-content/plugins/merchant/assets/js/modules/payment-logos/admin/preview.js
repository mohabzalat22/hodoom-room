"use strict";

;
(function ($) {
  'use strict';

  var $widthInput = $('input[name="merchant[image-max-width]"]');
  var $heightInput = $('input[name="merchant[image-max-height]"]');
  var imageDimensionPrev = {
    width: $widthInput.val(),
    height: $heightInput.val()
  };
  $(document).on('save.merchant', function (e, module) {
    if (module === 'payment-logos') {
      var logosStr = $('input[name="merchant[logos]"]').val();
      var logosArr = logosStr.split(',');
      regenerate_images(logosArr);
    }
  });
  function regenerate_images(attachments) {
    var _merchant, _merchant2;
    var imageDimensionNext = {
      width: $widthInput.val(),
      height: $heightInput.val()
    };
    var isDimensionChanged = imageDimensionPrev.width !== imageDimensionNext.width || imageDimensionPrev.height !== imageDimensionNext.height;
    if (isDimensionChanged) {
      imageDimensionPrev.height = imageDimensionNext.height;
      imageDimensionPrev.width = imageDimensionNext.width;
    }
    $.ajax({
      type: 'POST',
      url: (_merchant = merchant) === null || _merchant === void 0 ? void 0 : _merchant.ajax_url,
      data: {
        action: 'merchant_regenerate_images',
        nonce: (_merchant2 = merchant) === null || _merchant2 === void 0 ? void 0 : _merchant2.nonce,
        is_dimension_changed: isDimensionChanged,
        attachments: attachments
      },
      error: function error(_error) {
        console.log(_error);
      }
    });
  }

  /**
   * Container styling live preview.
   */
  function updateContainerPreview() {
    var $container = $('.merchant-payment-logos');
    if (!$container.length) {
      return;
    }
    var bgColor = $('input[name="merchant[container_bg_color]"]').val() || 'transparent';
    var padding = $('input[name="merchant[container_padding]"]').val() || 0;
    var borderRadius = $('input[name="merchant[container_border_radius]"]').val() || 0;
    var borderColor = $('input[name="merchant[container_border_color]"]').val() || 'transparent';
    var borderWidth = $('input[name="merchant[container_border_width]"]').val() || 0;
    $container.css({
      'background-color': bgColor,
      'padding': padding + 'px',
      'border-radius': borderRadius + 'px',
      'border': borderWidth > 0 && borderColor !== 'transparent' ? borderWidth + 'px solid ' + borderColor : 'none'
    });
  }

  // Range sliders have no name attr — target by parent wrapper class instead.
  $(document).on('change.merchant input.merchant change input', '.merchant-field-container_bg_color input, .merchant-field-container_padding input, .merchant-field-container_border_radius input, .merchant-field-container_border_color input, .merchant-field-container_border_width input', function () {
    updateContainerPreview();
  });
  updateContainerPreview();

  /**
   * Library icons live preview.
   *
   * Syncs the "Select payment icons" checkboxes with the preview panel.
   * Relies on `merchant.payment_logos` localized from PHP.
   */
  function updateLibraryIconsPreview() {
    var _merchant3;
    var $imagesContainer = $('.merchant-payment-logos-images.is-library');
    if (!$imagesContainer.length) {
      return;
    }
    var selectedKeys = [];
    $('input[name="merchant[library_icons][]"]:checked').each(function () {
      selectedKeys.push($(this).val());
    });
    $imagesContainer.empty();
    var iconsUrlMap = ((_merchant3 = merchant) === null || _merchant3 === void 0 || (_merchant3 = _merchant3.payment_logos) === null || _merchant3 === void 0 ? void 0 : _merchant3.icons_url_map) || {};
    selectedKeys.forEach(function (key) {
      if (iconsUrlMap[key] && iconsUrlMap[key].url) {
        var label = iconsUrlMap[key].label || key;
        $imagesContainer.append($('<img>').attr('src', iconsUrlMap[key].url).attr('alt', label));
      }
    });
    $imagesContainer.closest('.merchant-payment-logos').toggle(selectedKeys.length > 0);
  }
  $(document).on('change.merchant', 'input[name="merchant[library_icons][]"]', function () {
    updateLibraryIconsPreview();
  });
})(jQuery);