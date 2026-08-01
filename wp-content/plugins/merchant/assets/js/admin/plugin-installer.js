"use strict";

(function ($) {
  'use strict';

  var merchantPluginInstaller = window.merchantPluginInstaller || {};
  merchantPluginInstaller = {
    installButtonSelector: '.merchant-install-plugin',
    init: function init() {
      this.events();
    },
    // Binds install button clicks across any dynamically rendered content.
    events: function events() {
      var self = this;
      $(document).on('click', self.installButtonSelector, function (e) {
        e.preventDefault();

        // External plugins come from a URL; wporg ones use a slug.
        var type = $(this).data('type') === 'external' ? 'external' : 'wporg';
        var plugin_name = $(this).data('plugin-name');
        var redirect_to = $(this).data('redirect-to');
        if (type === 'external') {
          var url = $(this).data('plugin-url');
          self.installExternalPlugin($(this), url, plugin_name, redirect_to);
        } else {
          var slug = $(this).data('plugin-slug');
          self.installPlugin($(this), slug, plugin_name, redirect_to);
        }
      });
    },
    // Handles wporg plugins — resolved server-side via the slug.
    installPlugin: function installPlugin(button, slug, plugin_name, redirect_to) {
      this._doInstall(button, {
        action: 'merchant_install_plugin',
        slug: slug,
        plugin_name: plugin_name,
        nonce: merchantPluginInstallerConfig.nonce
      }, redirect_to);
    },
    // Handles external plugins — the server downloads from the given URL.
    installExternalPlugin: function installExternalPlugin(button, url, plugin_name, redirect_to) {
      this._doInstall(button, {
        action: 'merchant_install_external_plugin',
        url: url,
        plugin_name: plugin_name,
        nonce: merchantPluginInstallerConfig.nonce
      }, redirect_to);
    },
    // Fires the AJAX request, manages button state, and handles the redirect.
    _doInstall: function _doInstall(button, data, redirect_to) {
      button.prop('disabled', true);
      button.text(merchantPluginInstallerConfig.i18n.installingText);
      $.post(ajaxurl, data, function (response) {
        if (!response.success) {
          button.prop('disabled', false);
          button.text(merchantPluginInstallerConfig.i18n.defaultText);
          alert(response.data.message);
          return;
        }
        button.text(merchantPluginInstallerConfig.i18n.activatingText);

        // Brief delay so the user sees the "Activating" state before the page changes.
        setTimeout(function () {
          if (redirect_to) {
            window.location.href = redirect_to;
          } else {
            window.location.reload();
          }
        }, 1000);
      }).fail(function () {
        // Network failure or a non-200 response — restore the button.
        button.prop('disabled', false);
        button.text(merchantPluginInstallerConfig.i18n.defaultText);
        alert(merchantPluginInstallerConfig.i18n.networkErrorText);
      });
    }
  };
  $(document).ready(function () {
    merchantPluginInstaller.init();
  });
})(jQuery);