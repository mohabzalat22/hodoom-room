/**
 * Merchant Inactive Tab Message.
 *
 * Handles tab title change, dynamic variables, message rotation,
 * favicon swap, and return-to-tab welcome message.
 */

'use strict';

var merchant = merchant || {};
merchant.modules = merchant.modules || {};
(function ($) {
  merchant.modules.inactiveTabMessage = {
    // State.
    defaultTitle: '',
    originalFaviconElements: [],
    faviconSentinel: null,
    faviconSwapped: false,
    rotationTimer: null,
    returnTimer: null,
    scrollTimer: null,
    scrollDelayTimer: null,
    currentRotationIndex: 0,
    cartCount: 0,
    cartTotalRaw: 0,
    cartTotal: '',
    siteName: '',
    settings: {},
    /**
     * Initialize the module.
     */
    init: function init() {
      var s = merchant.setting || {};

      // Read settings.
      this.settings = {
        message: s.inactive_tab_message || '',
        abandonedMessage: s.inactive_tab_abandoned_message || '',
        highValueMessage: s.inactive_tab_high_value_message || '',
        highValueThreshold: parseFloat(s.inactive_tab_high_value_threshold) || 0,
        enableRotation: parseInt(s.inactive_tab_enable_rotation, 10) || 0,
        rotationMessages: s.inactive_tab_rotation_messages || [],
        rotationInterval: (parseInt(s.inactive_tab_rotation_interval, 10) || 3) * 1000,
        enableFavicon: parseInt(s.inactive_tab_enable_favicon, 10) || 0,
        faviconType: s.inactive_tab_favicon_type || 'emoji',
        faviconEmoji: s.inactive_tab_favicon_emoji || '',
        faviconUrl: s.inactive_tab_favicon_url || '',
        returnMessage: s.inactive_tab_return_message || '',
        returnDuration: (parseInt(s.inactive_tab_return_duration, 10) || 2) * 1000,
        enableScroll: parseInt(s.inactive_tab_enable_scroll, 10) || 0
      };
      this.cartCount = parseInt(s.inactive_tab_cart_count, 10) || 0;
      this.cartTotal = s.inactive_tab_cart_total || '';
      this.cartTotalRaw = parseFloat(s.inactive_tab_cart_total_raw) || 0;
      this.siteName = s.inactive_tab_site_name || '';
      this.defaultTitle = document.title;

      // Prepare favicon sentinel element for swap (created once, never recreated).
      this.faviconSentinel = document.createElement('link');
      this.faviconSentinel.rel = 'icon';

      // Cart fragment tracking.
      this.bindCartEvents();

      // Visibility change.
      document.addEventListener('visibilitychange', this.onVisibilityChange.bind(this));
    },
    // ── Cart Tracking ─────────────────────────────────────

    /**
     * Listen for WooCommerce cart AJAX events.
     */
    bindCartEvents: function bindCartEvents() {
      var self = this;
      $(document.body).on('added_to_cart removed_from_cart updated_wc_div', function (event, data) {
        if (data && data['.merchant_cart_count'] !== undefined) {
          self.cartCount = parseInt(data['.merchant_cart_count'], 10) || 0;
          self.cartTotalRaw = parseFloat(data['.merchant_cart_total_raw']) || self.cartTotalRaw;

          // Use pre-formatted total from PHP if available, otherwise format in JS.
          if (data['.merchant_cart_total']) {
            self.cartTotal = data['.merchant_cart_total'];
          } else {
            self.cartTotal = self.formatCurrency(self.cartTotalRaw);
          }
        } else {
          // Cart page fallback.
          self.cartCount = $('.woocommerce-cart-form tr.cart_item').length;
        }
      });
    },
    // ── Visibility Handler ────────────────────────────────

    /**
     * Handle tab visibility change.
     */
    onVisibilityChange: function onVisibilityChange() {
      if (document.hidden) {
        this.onTabHidden();
      } else {
        this.onTabVisible();
      }
    },
    /**
     * Tab became hidden.
     */
    onTabHidden: function onTabHidden() {
      // Swap favicon if enabled.
      if (this.settings.enableFavicon) {
        this.swapFavicon();
      }

      // Start rotation or show single message.
      if (this.settings.enableRotation && this.settings.rotationMessages.length > 0) {
        this.startRotation();
      } else {
        var self = this;
        var msg = this.selectMessage();
        if (msg) {
          // Loop: scroll → restart → scroll → … (indefinitely while hidden).
          var _loop = function loop() {
            self.setTitle(msg, _loop);
          };
          _loop();
        }
      }
    },
    /**
     * Tab became visible again.
     */
    onTabVisible: function onTabVisible() {
      // Restore favicon.
      if (this.settings.enableFavicon) {
        this.restoreFavicon();
      }

      // Stop scroll ticker.
      this.stopScroll();

      // Stop rotation.
      this.stopRotation();

      // Show return message or restore title.
      if (this.settings.returnMessage) {
        this.showReturnMessage();
      } else {
        document.title = this.defaultTitle;
      }
    },
    // ── Message Selection ─────────────────────────────────

    /**
     * Select the appropriate message based on cart state.
     * Three tiers: empty → has-items → high-value.
     *
     * @return {string} The selected message.
     */
    selectMessage: function selectMessage() {
      if (this.cartCount === 0) {
        return this.settings.message;
      }

      // High-value check.
      if (this.settings.highValueThreshold > 0 && this.cartTotalRaw >= this.settings.highValueThreshold && this.settings.highValueMessage) {
        return this.settings.highValueMessage;
      }
      return this.settings.abandonedMessage;
    },
    // ── Dynamic Variables ─────────────────────────────────

    /**
     * Interpolate {cart_count}, {cart_total}, {site_name} in a message.
     *
     * @param {string} message The message template.
     * @return {string} The interpolated message.
     */
    interpolate: function interpolate(message) {
      if (!message) {
        return '';
      }
      return message.replace(/\{cart_count\}/g, this.cartCount).replace(/\{cart_total\}/g, this.cartTotal).replace(/\{site_name\}/g, this.siteName);
    },
    /**
     * Set document.title with interpolation and entity decoding.
     * When scroll is enabled, starts the ticker instead of setting directly.
     *
     * @param {string}   message    The raw message.
     * @param {Function} onComplete Optional callback fired after one full scroll cycle.
     * @param {boolean}  skipScroll If true, always set title statically (used for return message).
     */
    setTitle: function setTitle(message, onComplete, skipScroll) {
      var resolved = this.interpolate(message).replaceAll('&#039;', "'");
      if (this.settings.enableScroll && !skipScroll) {
        this.startScroll(resolved, onComplete);
      } else {
        this.stopScroll();
        document.title = resolved;
      }
    },
    // ── Scroll (Ticker / Marquee) ─────────────────────────

    /**
     * Detect if a string is predominantly RTL (Arabic, Hebrew, etc.).
     *
     * Covers Arabic, Hebrew, Thaana, N'Ko, Samaritan and their
     * Unicode presentation forms.
     *
     * @param {string} text The message string.
     * @return {boolean} True if RTL.
     */
    isRTL: function isRTL(text) {
      return /[\u0591-\u07FF\uFB1D-\uFDFD\uFE70-\uFEFC]/.test(text);
    },
    /**
     * Start a consuming scroll ticker for the given message.
     * Removes one character per tick from the leading edge; direction is RTL-aware.
     *
     * @param {string}   text       Resolved message text.
     * @param {Function} onComplete Optional callback fired after one full scroll cycle.
     */
    startScroll: function startScroll(text, onComplete) {
      this.stopScroll();

      // Don't scroll short messages — they fit in the tab without truncating.
      // Browser tabs typically show ~12-15 characters before truncation.
      var SCROLL_THRESHOLD = 15;
      if (text.length <= SCROLL_THRESHOLD) {
        document.title = text;

        // Short message with callback: display for rotationInterval, then advance.
        if (onComplete) {
          this.scrollDelayTimer = setTimeout(onComplete, this.settings.rotationInterval);
        }
        return;
      }
      var self = this;
      var rtl = this.isRTL(text);
      var pos = 0;
      var isMultiWord = text.indexOf(' ') !== -1;

      // Fixed scroll speed — browsers throttle background tab timers to ~1s,
      // so configurable speed has no practical effect.
      var SCROLL_SPEED = 1000;
      document.title = text;
      this.scrollTimer = setInterval(function () {
        pos += 1;

        // Full cycle complete — all characters consumed.
        if (pos >= text.length) {
          clearInterval(self.scrollTimer);
          self.scrollTimer = null;
          if (onComplete) {
            onComplete();
          }
          return;
        }
        var remaining;
        if (rtl) {
          remaining = text.slice(0, text.length - pos);
        } else {
          remaining = text.slice(pos);
        }

        // Multi-word text: once only the last word remains, complete immediately.
        if (isMultiWord && remaining.indexOf(' ') === -1) {
          clearInterval(self.scrollTimer);
          self.scrollTimer = null;
          if (onComplete) {
            onComplete();
          }
          return;
        }
        document.title = remaining;
      }, SCROLL_SPEED);
    },
    /**
     * Stop the scroll ticker and any short-message delay timer.
     */
    stopScroll: function stopScroll() {
      if (this.scrollTimer) {
        clearInterval(this.scrollTimer);
        this.scrollTimer = null;
      }
      if (this.scrollDelayTimer) {
        clearTimeout(this.scrollDelayTimer);
        this.scrollDelayTimer = null;
      }
    },
    // ── Message Rotation ──────────────────────────────────

    /**
     * Start cycling through rotation messages.
     * When scroll is active, each message finishes one full scroll cycle
     * before advancing. Otherwise uses a fixed interval timer.
     */
    startRotation: function startRotation() {
      var self = this;
      var msgs = this.settings.rotationMessages;
      this.currentRotationIndex = 0;
      if (this.settings.enableScroll) {
        // Chain: scroll current message → on complete → advance → next.
        var _showCurrent = function showCurrent() {
          self.setTitle(msgs[self.currentRotationIndex], function () {
            self.currentRotationIndex = (self.currentRotationIndex + 1) % msgs.length;
            _showCurrent();
          });
        };
        _showCurrent();
      } else {
        this.setTitle(msgs[0]);
        this.rotationTimer = setInterval(function () {
          self.currentRotationIndex = (self.currentRotationIndex + 1) % msgs.length;
          self.setTitle(msgs[self.currentRotationIndex]);
        }, this.settings.rotationInterval);
      }
    },
    /**
     * Stop rotation timer and any active scroll ticker.
     */
    stopRotation: function stopRotation() {
      if (this.rotationTimer) {
        clearInterval(this.rotationTimer);
        this.rotationTimer = null;
      }
      this.stopScroll();
      this.currentRotationIndex = 0;
    },
    // ── Favicon Management (using favico.js) ─────────────

    /**
     * Swap favicon to the configured alternative.
     * Gracefully skips on Safari due to caching issues.
     */
    swapFavicon: function swapFavicon() {
      // Safari detection — skip favicon swap.
      if (this.isSafari()) {
        return;
      }
      var href = '';

      // Map string keys to emoji characters.
      var emojiMap = {
        wave: '👋',
        bell: '🔔',
        cart: '🛒',
        clock: '⏰',
        alert: '❗',
        money: '💰',
        fire: '🔥',
        star: '⭐'
      };
      if (this.settings.faviconType === 'image' && this.settings.faviconUrl) {
        href = this.settings.faviconUrl;
      } else if (this.settings.faviconType === 'emoji' && this.settings.faviconEmoji) {
        var emoji = emojiMap[this.settings.faviconEmoji] || this.settings.faviconEmoji;
        href = this.emojiToDataUrl(emoji);
      }
      if (href) {
        this.setFaviconHref(href);
      }
    },
    /**
     * Restore the original favicon links.
     * Detaches the sentinel and re-appends all original link elements.
     */
    restoreFavicon: function restoreFavicon() {
      if (this.isSafari()) {
        return;
      }
      if (!this.faviconSwapped) {
        return;
      }

      // Detach the sentinel.
      if (this.faviconSentinel.parentNode) {
        this.faviconSentinel.parentNode.removeChild(this.faviconSentinel);
      }

      // Re-attach originals.
      for (var i = 0; i < this.originalFaviconElements.length; i++) {
        document.head.appendChild(this.originalFaviconElements[i]);
      }
      this.faviconSwapped = false;
    },
    /**
     * Set the favicon href using a reusable sentinel element.
     * On the first call, detaches all original favicon links and appends
     * the sentinel. On subsequent calls, just flips the sentinel's href.
     *
     * @param {string} href The favicon URL or data URL.
     */
    setFaviconHref: function setFaviconHref(href) {
      // First swap: detach originals and append sentinel.
      if (!this.faviconSwapped) {
        var existing = document.querySelectorAll('link[rel="icon"], link[rel="shortcut icon"]');
        this.originalFaviconElements = [];
        for (var i = 0; i < existing.length; i++) {
          this.originalFaviconElements.push(existing[i]);
          existing[i].parentNode.removeChild(existing[i]);
        }
        document.head.appendChild(this.faviconSentinel);
        this.faviconSwapped = true;
      }

      // Cache-bust for regular URLs (not data URIs).
      if (href.indexOf('data:') !== 0) {
        href = href + (href.indexOf('?') === -1 ? '?' : '&') + 't=' + Date.now();
      }
      this.faviconSentinel.setAttribute('href', href);
    },
    /**
     * Convert an emoji character to a 32×32 canvas data URL.
     *
     * @param {string} emoji The emoji character.
     * @return {string} Data URL of the rendered emoji.
     */
    emojiToDataUrl: function emojiToDataUrl(emoji) {
      var canvas = document.createElement('canvas');
      canvas.width = 32;
      canvas.height = 32;
      var ctx = canvas.getContext('2d');
      ctx.font = '28px serif';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillText(emoji, 16, 18);
      return canvas.toDataURL('image/png');
    },
    /**
     * Detect Safari browser.
     *
     * @return {boolean} True if Safari.
     */
    isSafari: function isSafari() {
      return /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
    },
    // ── Return Message ────────────────────────────────────

    /**
     * Show a brief welcome-back message, then restore the original title.
     * Always displayed statically — scrolling a brief welcome-back defeats the purpose.
     */
    showReturnMessage: function showReturnMessage() {
      var self = this;

      // Skip scroll — return message is always static.
      this.setTitle(this.settings.returnMessage, null, true);

      // Clear previous return timer if any.
      if (this.returnTimer) {
        clearTimeout(this.returnTimer);
      }
      this.returnTimer = setTimeout(function () {
        document.title = self.defaultTitle;
        self.returnTimer = null;
      }, this.settings.returnDuration);
    },
    // ── Currency Formatting ───────────────────────────────

    /**
     * Format a numeric value as a currency string using WooCommerce settings.
     * Uses merchant.general for symbol, position, separators, and decimals.
     *
     * @param {number} amount The raw numeric amount.
     * @return {string} Formatted currency string.
     */
    formatCurrency: function formatCurrency(amount) {
      var g = merchant.general || {};
      var symbol = g.wooCurrencySymbol || '$';
      var position = g.wooCurrencyPosition || 'left';
      var thousands = g.wooThousandsSeparator || ',';
      var decimal = g.wooDecimalSeparator || '.';
      var decimals = parseInt(g.wooNumberOfDecimals, 10);
      if (isNaN(decimals)) {
        decimals = 2;
      }

      // Format the number.
      var fixed = parseFloat(amount).toFixed(decimals);
      var parts = fixed.split('.');
      var intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousands);
      var formatted = decimals > 0 ? intPart + decimal + parts[1] : intPart;

      // Apply currency position.
      switch (position) {
        case 'left':
          return symbol + formatted;
        case 'right':
          return formatted + symbol;
        case 'left_space':
          return symbol + ' ' + formatted;
        case 'right_space':
          return formatted + ' ' + symbol;
        default:
          return symbol + formatted;
      }
    }
  };
  merchant.modules.inactiveTabMessage.init();
})(jQuery);