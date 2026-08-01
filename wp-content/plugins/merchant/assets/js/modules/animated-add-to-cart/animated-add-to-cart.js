/**
 * Animated Add To Cart.
 */

'use strict';

var merchant = merchant || {};
merchant.modules = merchant.modules || {};
(function ($) {
  // Named "aatc-" not "merchant-" — Botiga's theme CSS stretches any [class*="merchant-"]
  // element in the add-to-cart wrapper to full width.
  var SUPPRESS_TRIGGER_CLASS = 'aatc-suppress-trigger';
  merchant.modules.animatedAddToCart = {
    init: function init() {
      this.initScrollTrigger();
      this.initSuccessAnimation();
    },
    initScrollTrigger: function initScrollTrigger() {
      var setting = window.merchant && window.merchant.setting;
      if (!setting || setting.animated_add_to_cart_trigger !== 'on-scroll-into-view' || typeof IntersectionObserver === 'undefined') {
        return;
      }
      var buttons = document.querySelectorAll('.add_to_cart_button:not(.merchant-buy-now-button), .product_type_grouped:not(.merchant-buy-now-button), .single_add_to_cart_button:not(.merchant-buy-now-button)');
      if (!buttons.length) {
        return;
      }
      var observer = new IntersectionObserver(function (entries, obs) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('aatc-animate-in');
            obs.unobserve(entry.target);
          }
        });
      });
      buttons.forEach(function (button) {
        observer.observe(button);
      });
    },
    initSuccessAnimation: function initSuccessAnimation() {
      var setting = window.merchant && window.merchant.setting;
      if (!setting || !setting.animated_add_to_cart_success_enabled) {
        return;
      }
      var className = setting.animated_add_to_cart_success_style || 'aatc-success-flash';

      // Page-load/scroll-into-view are one-shot, so suppress for good. Hover repeats.
      var suppressTriggerAfterSuccess = setting.animated_add_to_cart_trigger !== 'on-mouse-hover';
      var playSuccessAnimation = function playSuccessAnimation($button) {
        if (!$button || !$button.length) {
          return;
        }
        $button.removeClass('aatc-success-flash aatc-success-bounce ' + SUPPRESS_TRIGGER_CLASS).addClass(className);
        $button.one('animationend', function () {
          $button.removeClass(className);
          if (suppressTriggerAfterSuccess) {
            $button.addClass(SUPPRESS_TRIGGER_CLASS);
          }
        });
      };
      $(document.body).on('added_to_cart', function (event, fragments, cartHash, $button) {
        playSuccessAnimation($button);
      });

      // Single product page add-to-cart is a normal form submit, not AJAX, so there's
      // no "added_to_cart" event here — the server flags it for us instead.
      if (setting.animated_add_to_cart_just_added) {
        playSuccessAnimation($('.single_add_to_cart_button'));
      }
    }
  };
  $(document).ready(function () {
    merchant.modules.animatedAddToCart.init();
  });
})(jQuery);