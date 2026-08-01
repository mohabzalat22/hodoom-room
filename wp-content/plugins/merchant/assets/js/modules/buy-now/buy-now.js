"use strict";

;
(function ($) {
  'use strict';

  // ═══════════════════════════════════════════════════════════════════
  // VARIATION STATE
  // Enables/disables Buy Now button based on WC variation selection.
  // ═══════════════════════════════════════════════════════════════════
  var VariationState = {
    /**
     * Bind change listeners on all variation forms found in the DOM.
     */
    init: function init() {
      this.bindForms($('form.variations_form'));

      // Re-bind when Quick View loads new content.
      window.addEventListener('merchant.quickview.ajax.loaded', function () {
        VariationState.bindForms($('form.variations_form'));
      });
    },
    /**
     * Attach variation_id change handler to each form.
     *
     * @param {jQuery} $forms The variation forms.
     */
    bindForms: function bindForms($forms) {
      $forms.each(function () {
        var $form = $(this);
        var $buyBtn = $form.find('.merchant-buy-now-button');
        $form.find('input[name="variation_id"]').on('change woocommerce_variation_has_changed', function () {
          var selectedVariationId = +$form.find('.variation_id').val();
          $buyBtn.toggleClass('disabled', !selectedVariationId).attr('disabled', !selectedVariationId);
          $buyBtn.val(selectedVariationId);
        });
      });
    }
  };

  // ═══════════════════════════════════════════════════════════════════
  // BUY NOW POPUP INTEGRATION
  // ═══════════════════════════════════════════════════════════════════

  var BuyNowPopupIntegration = {
    redirectUrl: '',
    init: function init() {
      if (typeof merchant_added_to_cart_popup === 'undefined' || merchant_added_to_cart_popup.can_show !== 'yes') {
        return;
      }
      this.bindEvents();
    },
    bindEvents: function bindEvents() {
      $(document).on('click', '.merchant-buy-now-button[data-show-popup="1"]', function (e) {
        BuyNowPopupIntegration.handleClick(e, $(this));
      });
      $(document).on('merchant_close_added_to_cart_popup', function () {
        if (BuyNowPopupIntegration.redirectUrl) {
          window.location.href = BuyNowPopupIntegration.redirectUrl;
        }
      });
    },
    /**
     * @param {Event}  e    The click event.
     * @param {jQuery} $btn The clicked button.
     */
    handleClick: function handleClick(e, $btn) {
      e.preventDefault();
      e.stopPropagation();
      if ($btn.hasClass('is-loading')) {
        return;
      }
      var isArchiveLink = $btn.is('a');
      var params = typeof merchant_buy_now_params !== 'undefined' ? merchant_buy_now_params : {};
      var formData = {
        action: 'merchant_buy_now_add_to_cart',
        product_id: $btn.val() || '',
        quantity: 1,
        variation_id: 0,
        campaign_id: $btn.data('campaign-id') || '',
        _wpnonce: params.nonce || ''
      };

      // Extract product ID from href for archive links.
      if (isArchiveLink) {
        var match = ($btn.attr('href') || '').match(/merchant-buy-now=(\d+)/);
        if (match) {
          formData.product_id = match[1];
        }
      } else {
        var $form = $btn.closest('form');
        if ($form.length) {
          formData.quantity = parseInt($form.find('[name="quantity"]').val()) || 1;
          formData.variation_id = parseInt($form.find('[name="variation_id"]').val()) || 0;
        }
      }
      $btn.addClass('is-loading');
      $.post(params.ajax_url || '', formData, function (response) {
        $btn.removeClass('is-loading');
        if (!response.success) {
          // Fallback: redirect directly.
          if (response.data && response.data.redirect_url) {
            window.location.href = response.data.redirect_url;
          }
          return;
        }
        BuyNowPopupIntegration.redirectUrl = response.data.redirect_url || '';

        // Trigger WC added_to_cart event so the popup module picks it up.
        $(document.body).trigger('added_to_cart', [response.data.fragments || {}, response.data.cart_hash || '', $btn]);
      }).fail(function () {
        $btn.removeClass('is-loading');
      });
    }
  };

  // ═══════════════════════════════════════════════════════════════════
  // INIT
  // ═══════════════════════════════════════════════════════════════════

  $(document).ready(function () {
    VariationState.init();
    BuyNowPopupIntegration.init();
  });
})(jQuery);