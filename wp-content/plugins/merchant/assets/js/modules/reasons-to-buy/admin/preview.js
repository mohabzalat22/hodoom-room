"use strict";

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
(function ($) {
  'use strict';

  var moduleSelector = '.merchant-flexible-content-control.reasons-to-buy-style';

  /**
   * Parse the icon SVG library from the sortable-repeater-icons control.
   *
   * @param {jQuery} $layout Active layout element.
   * @return {Object} Icon key → SVG markup map.
   */
  function getIconLibrary($layout) {
    try {
      return JSON.parse($layout.find('.merchant-sortable-repeater-icons-control').attr('data-icons') || '{}');
    } catch (e) {
      return {};
    }
  }

  /**
   * Read the list items JSON from the hidden input.
   *
   * @param {jQuery} $layout Active layout element.
   * @return {Array} Parsed items array.
   */
  function getListItems($layout) {
    try {
      return JSON.parse($layout.find('.merchant-sortable-repeater-icons-control .merchant-sortable-repeater-input').val());
    } catch (e) {
      return [];
    }
  }

  /**
   * Rebuild the admin preview box from the active layout's field values.
   */
  function initPreview() {
    var $layout = $(moduleSelector).find('.layout.active');
    var $previewBox = $('.merchant-reasons-list-preview').find('.merchant-reasons-list');
    if (!$layout.length || !$previewBox.length) {
      return;
    }

    // Title.
    var titleText = $layout.find('.merchant-field-title input').val();
    var titleColor = $layout.find('.merchant-field-title_color input').val();
    $previewBox.find('.merchant-reasons-list-title').text(titleText).css({
      color: titleColor
    });

    // Field values.
    var listItems = getListItems($layout);
    var itemsColor = $layout.find('.merchant-field-items_color input').val();
    var iconsColor = $layout.find('.merchant-field-icon_color input').val();
    var spacing = $layout.find('.merchant-field-spacing input').val();
    var campaignIcon = $layout.find('.merchant-field-icon .merchant-choices-icon input:checked').val() || '';
    var isIconEnabled = $layout.find('.merchant-field-display_icon .toggle-switch-checkbox').is(':checked');
    var iconLibrary = getIconLibrary($layout);

    // Rebuild items.
    $previewBox.find('.merchant-reasons-list-item').remove();
    listItems.forEach(function (value, index) {
      var itemText, itemIcon;
      if (_typeof(value) === 'object' && value !== null) {
        itemText = value.text || '';
        itemIcon = value.icon || campaignIcon;
      } else {
        itemText = value;
        itemIcon = campaignIcon;
      }
      if (!itemText.trim()) {
        return;
      }
      var $listItem = $('<div class="merchant-reasons-list-item" data-index="' + index + '" style="margin-top: ' + spacing + 'px"></div>');
      var $text = $('<p class="merchant-reasons-list-item-text" style="color: ' + itemsColor + '"></p>').text(itemText);
      if (isIconEnabled && itemIcon && iconLibrary[itemIcon]) {
        $('<div class="merchant-reasons-list-item-icon" style="color: ' + iconsColor + '">').html(iconLibrary[itemIcon]).appendTo($listItem);
      }
      $listItem.append($text);
      $previewBox.append($listItem);
    });

    // Keep data-index in sync on the sortable rows.
    $layout.find('.merchant-sortable-repeater-icons .repeater').each(function (index) {
      $(this).attr('data-index', index);
    });
  }

  // ── Event bindings ──────────────────────────────────────────────────────

  // Page load.
  $(function () {
    $(moduleSelector + ' .merchant-flexible-content .layout:first').addClass('active');
    initPreview();
  });

  // Campaign switch.
  $(document).on('click', moduleSelector + ' .merchant-flexible-content .layout', function () {
    $(this).closest(moduleSelector).find('.layout').removeClass('active');
    $(this).addClass('active');
    initPreview();
  });

  // New campaign added / deferred campaign hydrated.
  $(document).on('merchant-flexible-content-added', function (e, $layout) {
    $(moduleSelector).find('.layout').removeClass('active');
    $layout.addClass('active');
    initPreview();
  });

  // Sortable order, add, or delete.
  $(document).on('sortable.repeater.change', function () {
    initPreview();
  });

  // Any field change (color pickers, selects, toggle, etc.).
  $(document).on('change.merchant keyup', function (e) {
    if ($(e.target).hasClass('repeater-input')) {
      return; // Handled by the fast-path below.
    }
    initPreview();
  });

  // Fast-path: live text update while typing (in-place, no full re-render).
  $(document).on('input', '.merchant-sortable-repeater-icons-control .repeater-input', function () {
    var index = $(this).closest('.repeater').attr('data-index');
    var $textEl = $('.merchant-reasons-list-item[data-index="' + index + '"]').find('.merchant-reasons-list-item-text');
    if ($textEl.length) {
      $textEl.text($(this).val());
    }
  });

  // Fast-path: in-place icon swap when picking from the dropdown.
  $(document).on('click', '.merchant-sortable-repeater-icons-control .merchant-icon-option', function () {
    var $repeater = $(this).closest('.repeater');
    var index = $repeater.attr('data-index');
    var iconKey = $(this).data('icon') || '';
    var $layout = $(moduleSelector).find('.layout.active');
    var iconLibrary = getIconLibrary($layout);
    var iconsColor = $layout.find('.merchant-field-icon_color input').val();
    var isIconEnabled = $layout.find('.merchant-field-display_icon .toggle-switch-checkbox').is(':checked');
    var campaignIcon = $layout.find('.merchant-field-icon .merchant-choices-icon input:checked').val() || '';
    var effectiveIcon = iconKey || campaignIcon;
    var $item = $('.merchant-reasons-list-item[data-index="' + index + '"]');
    if (!$item.length) {
      return;
    }
    $item.find('.merchant-reasons-list-item-icon').remove();
    if (isIconEnabled && effectiveIcon && iconLibrary[effectiveIcon]) {
      $('<div class="merchant-reasons-list-item-icon" style="color: ' + iconsColor + '">').html(iconLibrary[effectiveIcon]).prependTo($item);
    }
  });
})(jQuery);