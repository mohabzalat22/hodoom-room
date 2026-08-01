"use strict";

;
(function ($) {
  'use strict';

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
  var rotationTimer = null;
  var rotationIndex = 0;
  var scrollTimer = null;
  var scrollDelayTimer = null;

  // Sample values for admin preview.
  var sampleData = {
    cart_count: '3',
    cart_total: '$45.00',
    site_name: typeof merchantItmPreview !== 'undefined' && merchantItmPreview.siteName || 'Your Store'
  };

  /**
   * Replace dynamic variable placeholders with sample values.
   */
  function interpolate(text) {
    return text.replace(/\{cart_count\}/g, sampleData.cart_count).replace(/\{cart_total\}/g, sampleData.cart_total).replace(/\{site_name\}/g, sampleData.site_name);
  }

  /**
   * Detect if a string is predominantly RTL (Arabic, Hebrew, etc.).
   */
  function isRTL(text) {
    return /[\u0591-\u07FF\uFB1D-\uFDFD\uFE70-\uFEFC]/.test(text);
  }

  /**
   * Stop any running scroll ticker and short-message delay timer.
   */
  function stopScroll() {
    if (scrollTimer) {
      clearInterval(scrollTimer);
      scrollTimer = null;
    }
    if (scrollDelayTimer) {
      clearTimeout(scrollDelayTimer);
      scrollDelayTimer = null;
    }
  }

  /**
   * Set the preview tab title, starting a scroll ticker when enabled.
   *
   * @param {string}   text       Already-interpolated message text.
   * @param {Object}   titleEl    jQuery element for the tab title node.
   * @param {Function} onComplete Optional callback fired after one full scroll cycle.
   */
  function setPreviewTitle(text, titleEl, onComplete) {
    var enabled = getCheckedVal('enable_scroll') === '1';
    stopScroll();
    if (!enabled || !text) {
      titleEl.text(text);
      if (onComplete && !enabled) {
        var rotInterval = (parseInt(getCheckedVal('rotation_interval'), 10) || 3) * 1000;
        scrollDelayTimer = setTimeout(onComplete, rotInterval);
      }
      return;
    }

    // Don't scroll short messages — they fit in the tab without truncating.
    // Browser tabs typically show ~12-15 characters before truncation.
    var SCROLL_THRESHOLD = 15;
    if (text.length <= SCROLL_THRESHOLD) {
      titleEl.text(text);
      // Short message with callback: display for rotationInterval, then advance.
      if (onComplete) {
        var rotInterval = (parseInt(getCheckedVal('rotation_interval'), 10) || 3) * 1000;
        scrollDelayTimer = setTimeout(onComplete, rotInterval);
      }
      return;
    }
    var rtl = isRTL(text);
    var pos = 0;
    var isMultiWord = text.indexOf(' ') !== -1;

    // Fixed scroll speed — matches frontend (1 character per second).
    var SCROLL_SPEED = 1000;
    titleEl.text(text);
    scrollTimer = setInterval(function () {
      pos += 1;

      // Full cycle complete — all characters consumed.
      if (pos >= text.length) {
        clearInterval(scrollTimer);
        scrollTimer = null;
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
        clearInterval(scrollTimer);
        scrollTimer = null;
        if (onComplete) {
          onComplete();
        }
        return;
      }
      titleEl.text(remaining);
    }, SCROLL_SPEED);
  }
  function getField(id) {
    return $('[name="merchant[' + id + ']"]');
  }
  function getCheckedVal(id) {
    var el = getField(id);
    if (el.attr('type') === 'radio') {
      return el.filter(':checked').val();
    }
    if (el.attr('type') === 'checkbox') {
      return el.is(':checked') ? '1' : '0';
    }
    return el.val();
  }

  /**
   * Read rotation messages from the sortable repeater hidden input.
   */
  function getRotationMessages() {
    var raw = getField('rotation_messages').val();
    if (!raw) {
      return [];
    }
    try {
      var parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed.filter(function (m) {
        return m && m.trim();
      }) : [];
    } catch (e) {
      return [];
    }
  }

  // ── Favicon preview ──────────────────────────────────────────────
  function updateFavicon() {
    var enabled = getCheckedVal('enable_favicon') === '1';
    var type = getCheckedVal('favicon_type') || 'emoji';
    var emojiKey = getCheckedVal('favicon_emoji') || 'wave';
    var faviconEl = $('.mrc-inactive-tab-preview-tab__favicon--swap');
    if (!faviconEl.length) {
      return;
    }
    if (enabled && type === 'emoji') {
      var emoji = emojiMap[emojiKey] || emojiMap.wave;
      faviconEl.html('<span class="mrc-inactive-tab-favicon-emoji">' + emoji + '</span>');
    } else if (enabled && type === 'image') {
      // Read the uploaded image thumbnail from the upload field's preview.
      var uploadImg = $('[data-id="favicon_url"] .merchant-upload-image img');
      if (uploadImg.length) {
        faviconEl.empty().append($('<img />', {
          'class': 'mrc-inactive-tab-favicon-custom',
          src: uploadImg.attr('src'),
          alt: ''
        }));
      } else {
        // No image uploaded yet — show default site favicon.
        var activeImg = $('.mrc-inactive-tab-preview-tab--active .mrc-inactive-tab-preview-tab__favicon img');
        if (activeImg.length) {
          faviconEl.empty().append($('<img />', {
            src: activeImg.attr('src'),
            alt: ''
          }));
        }
      }
    } else {
      // Favicon disabled — show default site favicon.
      var activeImg = $('.mrc-inactive-tab-preview-tab--active .mrc-inactive-tab-preview-tab__favicon img');
      if (activeImg.length) {
        faviconEl.empty().append($('<img />', {
          src: activeImg.attr('src'),
          alt: ''
        }));
      }
    }
  }

  // ── Rotation preview ─────────────────────────────────────────────
  function stopRotation() {
    if (rotationTimer) {
      clearInterval(rotationTimer);
      rotationTimer = null;
    }
    stopScroll();
    rotationIndex = 0;
  }
  function startRotation() {
    stopRotation();
    var messages = getRotationMessages();
    var titleEl = $('.mrc-inactive-tab-message-text');
    if (!messages.length || !titleEl.length) {
      return;
    }
    var enableScroll = getCheckedVal('enable_scroll') === '1';
    if (enableScroll) {
      // Chain: scroll current message → on complete → advance → next.
      rotationIndex = 0;
      var _showCurrent = function showCurrent() {
        setPreviewTitle(interpolate(messages[rotationIndex]), titleEl, function () {
          rotationIndex = (rotationIndex + 1) % messages.length;
          _showCurrent();
        });
      };
      _showCurrent();
    } else {
      // Show first message immediately.
      setPreviewTitle(interpolate(messages[0]), titleEl);
      var interval = (parseInt(getCheckedVal('rotation_interval'), 10) || 3) * 1000;
      rotationTimer = setInterval(function () {
        rotationIndex = (rotationIndex + 1) % messages.length;
        setPreviewTitle(interpolate(messages[rotationIndex]), titleEl);
      }, interval);
    }
  }
  function updateRotation() {
    var enabled = getCheckedVal('enable_rotation') === '1';
    var titleEl = $('.mrc-inactive-tab-message-text');
    if (enabled) {
      startRotation();
    } else {
      stopRotation();
      // Restore single message with looping scroll.
      if (titleEl.length) {
        var text = interpolate(getField('message').val() || '');
        var enableScroll = getCheckedVal('enable_scroll') === '1';
        if (enableScroll && text.length > 15) {
          // Loop: scroll → restart → scroll → …
          var _loop = function loop() {
            setPreviewTitle(text, titleEl, _loop);
          };
          _loop();
        } else {
          setPreviewTitle(text, titleEl);
        }
      }
    }
  }

  // ── Init ─────────────────────────────────────────────────────────
  $(document).ready(function () {
    // Favicon bindings.
    getField('enable_favicon').on('change', updateFavicon);
    getField('favicon_type').on('change', updateFavicon);
    getField('favicon_emoji').on('change', updateFavicon);

    // Favicon custom image upload binding.
    $(document).on('change', '[data-id="favicon_url"] .merchant-upload-input', updateFavicon);
    $(document).on('click', '[data-id="favicon_url"] .merchant-upload-remove', function () {
      setTimeout(updateFavicon, 100);
    });

    // Single message binding.
    getField('message').on('keyup input', function () {
      if (getCheckedVal('enable_rotation') !== '1') {
        setPreviewTitle(interpolate($(this).val() || ''), $('.mrc-inactive-tab-message-text'));
      }
    });

    // Scroll binding — restart when toggled.
    getField('enable_scroll').on('change', function () {
      if (getCheckedVal('enable_rotation') === '1') {
        startRotation();
      } else {
        var text = interpolate(getField('message').val() || '');
        var titleEl = $('.mrc-inactive-tab-message-text');
        var enableScroll = getCheckedVal('enable_scroll') === '1';
        if (enableScroll && text.length > 15) {
          var _loop2 = function loop() {
            setPreviewTitle(text, titleEl, _loop2);
          };
          _loop2();
        } else {
          setPreviewTitle(text, titleEl);
        }
      }
    });

    // Rotation bindings.
    getField('enable_rotation').on('change', updateRotation);
    getField('rotation_messages').on('change', updateRotation);
    getField('rotation_interval').on('input change', updateRotation);

    // The range slider has name="" so getField won't find it.
    // Listen to it via the parent container.
    $(document).on('input', '[data-id="rotation_interval"] .merchant-range-input', updateRotation);

    // Also listen to the visible repeater text inputs (they update the hidden input).
    $(document).on('input', '.merchant-sortable-repeater .repeater-input', function () {
      // Small delay so the hidden input is synced first.
      setTimeout(updateRotation, 50);
    });

    // Listen for add/remove repeater items.
    $(document).on('click', '.customize-control-sortable-repeater-add, .customize-control-sortable-repeater-delete', function () {
      setTimeout(updateRotation, 100);
    });

    // Initial state.
    updateFavicon();
    updateRotation();
  });
})(jQuery);