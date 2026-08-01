"use strict";

/**
 * Botiga White Label (Settings > White Label)
 *
 * Locked (non-Agency): intercept the toggle and open the upgrade modal.
 * Functional (Agency): reflect the toggle on the notice/fields and save.
 *
 */
(function ($) {
  'use strict';

  $(document).ready(function () {
    var config = window.botigaWhiteLabel || {};
    var i18n = config.i18n || {};
    var $modal = $('#botiga-wl-modal');

    // Upgrade modal (locked state)
    function openModal() {
      $modal.addClass('is-active').attr('aria-hidden', 'false');
    }
    function closeModal() {
      $modal.removeClass('is-active').attr('aria-hidden', 'true');
    }
    $(document).on('click', '[data-wl-locked]', function (e) {
      e.preventDefault();
      openModal();
    });
    $(document).on('keydown', '[data-wl-locked]', function (e) {
      // Space / Enter also open the modal for keyboard users.
      if (13 === e.keyCode || 32 === e.keyCode) {
        e.preventDefault();
        openModal();
      }
    });

    // Locked state only (no functional toggle rendered): treat the whole
    // fields area as one upsell surface, so clicking a disabled field, the
    // textarea, or a checkbox opens the upgrade modal too — not just the
    // toggle. The '#botiga_wl_activate' absence is the locked-state signal, so
    // an Agency user whose fields are merely disabled never gets an upsell.
    if (!$('#botiga_wl_activate').length) {
      $(document).on('click', '.botiga-dashboard-white-label-fields', function (e) {
        // Let anything that handles itself (the locked toggle, links,
        // buttons) do its own thing.
        if ($(e.target).closest('[data-wl-locked], [data-wl-close], a, button').length) {
          return;
        }
        openModal();
      });
    }
    $modal.on('click', '[data-wl-close], .botiga-wl-modal-overlay', function (e) {
      e.preventDefault();
      closeModal();
    });
    $(document).on('keyup', function (e) {
      if (27 === e.keyCode) {
        closeModal();
      }
    });

    // Reflect the activate toggle live: show the notice and enable the fields
    // only while White Label is on.
    var $activate = $('#botiga_wl_activate');
    var $bookmark = $('#botiga-wl-bookmark');
    var $fields = $('.botiga-dashboard-white-label-field, .botiga-dashboard-white-label-check');
    if ($activate.length) {
      var syncState = function syncState() {
        var on = $activate.is(':checked');
        $bookmark.toggle(on);
        $fields.prop('disabled', !on);
      };
      $activate.on('change', syncState);
      syncState();
    }

    // Save the fields (Agency only)
    var $button = $('#botiga-white-label-save');
    if (!$button.length) {
      return;
    }
    $button.on('click', function (e) {
      e.preventDefault();
      var $btn = $(this);
      var $wrapper = $btn.closest('.botiga-dashboard-white-label-fields');
      var $feedback = $wrapper.find('.botiga-dashboard-white-label-feedback');
      if ($btn.prop('disabled')) {
        return;
      }
      var payload = {
        action: 'botiga_white_label_save_handler',
        nonce: config.nonce,
        activate_white_label: $wrapper.find('#botiga_wl_activate').is(':checked') ? '1' : '0'
      };
      $wrapper.find('.botiga-dashboard-white-label-field').each(function () {
        payload[this.name] = this.value;
      });

      // Extension checkboxes (e.g. Botiga Pro's per-plugin hide toggles) —
      // sent as '1'/'0' so the server can store them like the old plugin.
      $wrapper.find('input.botiga-dashboard-white-label-check').each(function () {
        payload[this.name] = this.checked ? '1' : '0';
      });
      $btn.prop('disabled', true).text(i18n.saving || 'Saving...');
      $feedback.removeClass('is-success is-error').text('');
      $.post(config.ajax_url, payload).done(function (response) {
        if (response && response.success) {
          var msg = response.data && response.data.message ? response.data.message : i18n.saved || 'Saved!';
          $feedback.addClass('is-success').text(msg);
        } else {
          var err = response && response.data && response.data.message ? response.data.message : i18n.error || 'Something went wrong, please try again.';
          $feedback.addClass('is-error').text(err);
        }
      }).fail(function () {
        $feedback.addClass('is-error').text(i18n.error || 'Something went wrong, please try again.');
      }).always(function () {
        $btn.prop('disabled', false).text($btn.data('default-label') || i18n.save || 'Save Changes');
      });
    });
  });
})(jQuery);