"use strict";

/**
 * Payment Logos - WooCommerce Blocks Integration
 *
 * Injects the payment logos HTML into block-based cart and checkout pages.
 *
 * @package Merchant
 * @since   2.2.0
 */

(function ($) {
  'use strict';

  var data = window.merchant_payment_logos_blocks_data;
  if (!data) {
    return;
  }

  /**
   * Map cart_position values to DOM selector + injection method.
   */
  var CART_MAP = {
    before_table: {
      selector: '.wc-block-cart',
      method: 'prepend'
    },
    after_table: {
      selector: '.wc-block-cart-items',
      method: 'after'
    },
    before_proceed_to_checkout: {
      selector: '.wc-block-components-totals-footer-item',
      method: 'after'
    },
    after_cart_totals: {
      selector: '.wc-block-cart__sidebar',
      method: 'append'
    }
  };

  /**
   * Map checkout_position values to DOM selector + injection method.
   */
  var CHECKOUT_MAP = {
    before_checkout_form: {
      selector: '.wc-block-checkout',
      method: 'prepend'
    },
    before_customer_details: {
      selector: '.wp-block-woocommerce-checkout-shipping-address-block',
      method: 'before'
    },
    after_customer_details: {
      selector: '.wp-block-woocommerce-checkout-shipping-address-block',
      method: 'after'
    },
    before_place_order: {
      selector: '.wp-block-woocommerce-checkout-actions-block',
      method: 'before'
    },
    after_checkout_form: {
      selector: '.wp-block-woocommerce-checkout-actions-block',
      method: 'after'
    }
  };

  /**
   * Inject HTML into the block layout.
   *
   * @param {string} html      The HTML string to inject.
   * @param {Object} map       The position-to-selector map to use.
   * @param {string} position  The configured position key.
   * @param {string} wrapperId The ID for the injected wrapper element.
   */
  function inject(html, map, position, wrapperId) {
    if (!html) {
      return;
    }
    if ($('#' + $.escapeSelector(wrapperId)).length) {
      return;
    }
    var config = map[position];
    if (!config) {
      return;
    }
    var $target = $(config.selector).first();
    if (!$target.length) {
      return;
    }
    var $wrapper = $('<div>', {
      id: wrapperId,
      html: html
    });
    $target[config.method]($wrapper);
  }

  /**
   * Run all configured injections.
   */
  function run() {
    if (data.cart_enabled) {
      inject(data.cart_html, CART_MAP, data.cart_position, 'merchant-payment-logos-cart-block');
    }
    if (data.checkout_enabled) {
      inject(data.checkout_html, CHECKOUT_MAP, data.checkout_position, 'merchant-payment-logos-checkout-block');
    }
  }
  var observer = null;
  function initObserver() {
    if (observer) {
      return;
    }
    var container = document.querySelector('.wc-block-cart') || document.querySelector('.wc-block-checkout') || document.body;
    observer = new MutationObserver(function (mutations) {
      var shouldUpdate = false;
      mutations.forEach(function (mutation) {
        if (mutation.type === 'childList') {
          shouldUpdate = true;
        }
      });
      if (shouldUpdate) {
        observer.disconnect();
        run();
        observer.observe(container, {
          childList: true,
          subtree: true
        });
      }
    });
    observer.observe(container, {
      childList: true,
      subtree: true
    });
  }
  $(document).ready(function () {
    run();
    initObserver();
  });
})(window.jQuery);