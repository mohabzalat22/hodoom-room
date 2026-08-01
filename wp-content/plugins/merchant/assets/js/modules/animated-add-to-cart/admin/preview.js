"use strict";

(function ($) {
  'use strict';

  var SUCCESS_STYLE_CLASSES = 'aatc-success-flash aatc-success-bounce';

  // Scoped to just these two fields, and transient (not left on the button), so it doesn't
  // permanently hijack the "animation" property (via !important) from the trigger preview.
  $(document).on('change', '[name="merchant[success_animation_enabled]"], [name="merchant[success_animation_style]"]', function () {
    var $button = $('.add_to_cart_button');
    var $enabled = $('[name="merchant[success_animation_enabled]"]');
    var style = $('[name="merchant[success_animation_style]"]').val();
    $button.removeClass(SUCCESS_STYLE_CLASSES);
    if (!$enabled.is(':checked')) {
      return;
    }
    $button.addClass(style);
    setTimeout(function () {
      $button.removeClass(style);
    }, 1000);
  });
})(jQuery);