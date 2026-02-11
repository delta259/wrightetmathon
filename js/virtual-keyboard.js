/**
 * Virtual Keyboard — Tactile AZERTY for POS
 *
 * Auto-shows on touch devices when an input/textarea receives focus.
 * Supports AZERTY layout, symbol/number layout, and numeric keypad.
 * Long-press on vowels shows accent popup.
 */
(function () {
  'use strict';

  /* ====================================================================
     Configuration
     ==================================================================== */
  var LAYOUTS = {
    azerty: {
      alpha: [
        ['a', 'z', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p'],
        ['q', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm'],
        ['{shift}', 'w', 'x', 'c', 'v', 'b', 'n', '{backspace}'],
        ['{toggle:123}', '{space}', '{ok}']
      ],
      numpad: [
        ['7', '8', '9'],
        ['4', '5', '6'],
        ['1', '2', '3'],
        ['.', '0', '{backspace}']
      ]
    },
    symbols: [
      ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'],
      ['-', '/', ':', ';', '(', ')', '\u20AC', '&', '@', '"'],
      ['{toggle:ABC}', '.', ',', '?', '!', "'", '+', '=', '%', '{backspace}'],
      ['{space}', '{ok}']
    ],
    numeric: [
      ['7', '8', '9'],
      ['4', '5', '6'],
      ['1', '2', '3'],
      ['.', '0', '{backspace}'],
      ['{ok}']
    ]
  };

  var ACCENTS = {
    a: ['à', 'â', 'ä', 'æ'],
    e: ['é', 'è', 'ê', 'ë'],
    i: ['î', 'ï'],
    o: ['ô', 'ö', 'œ'],
    u: ['ù', 'û', 'ü'],
    c: ['ç'],
    A: ['À', 'Â', 'Ä', 'Æ'],
    E: ['É', 'È', 'Ê', 'Ë'],
    I: ['Î', 'Ï'],
    O: ['Ô', 'Ö', 'Œ'],
    U: ['Ù', 'Û', 'Ü'],
    C: ['Ç']
  };

  var LONG_PRESS_DELAY = 500; // ms

  /* ====================================================================
     State
     ==================================================================== */
  var activeInput = null;
  var currentLayout = 'azerty';
  var shifted = false;
  var kbEl = null;        // #vk-keyboard
  var accentsEl = null;   // #vk-accents
  var longPressTimer = null;
  var enabled = false;    // true when touch device or manually toggled
  var manualToggle = false;

  /* ====================================================================
     Detection
     ==================================================================== */
  function isTouchDevice() {
    return ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
  }

  /* ====================================================================
     DOM helpers
     ==================================================================== */
  function el(tag, cls, attrs) {
    var node = document.createElement(tag);
    if (cls) node.className = cls;
    if (attrs) {
      for (var k in attrs) {
        if (attrs.hasOwnProperty(k)) node.setAttribute(k, attrs[k]);
      }
    }
    return node;
  }

  /* ====================================================================
     Build keyboard DOM
     ==================================================================== */
  function buildKeyboard() {
    kbEl = el('div', '', { id: 'vk-keyboard' });

    // Close bar
    var closeBar = el('div', 'vk-close-bar');
    var closeBtn = el('div', 'vk-key vk-key-close', { 'data-action': 'close' });
    closeBtn.textContent = '\u2715';
    closeBar.appendChild(closeBtn);
    kbEl.appendChild(closeBar);

    // Content container (rows go here)
    var content = el('div', 'vk-content');
    kbEl.appendChild(content);

    document.body.appendChild(kbEl);

    // Accent popup
    accentsEl = el('div', '', { id: 'vk-accents' });
    document.body.appendChild(accentsEl);
  }

  /* ====================================================================
     Render a layout into the keyboard
     ==================================================================== */
  function renderLayout(layoutName) {
    currentLayout = layoutName;
    var content = kbEl.querySelector('.vk-content');
    content.innerHTML = '';

    // Numeric-only class toggle
    kbEl.classList.toggle('vk-numeric', layoutName === 'numeric');
    // Split layout class toggle (azerty has alpha + numpad side by side)
    kbEl.classList.toggle('vk-split', layoutName === 'azerty');

    var layout = LAYOUTS[layoutName];

    // Split layout: alpha left + numpad right
    if (layout.alpha && layout.numpad) {
      var splitEl = el('div', 'vk-split-wrap');

      var alphaEl = el('div', 'vk-alpha');
      renderRows(alphaEl, layout.alpha);
      splitEl.appendChild(alphaEl);

      var numpadEl = el('div', 'vk-numpad');
      renderRows(numpadEl, layout.numpad);
      splitEl.appendChild(numpadEl);

      content.appendChild(splitEl);
    } else {
      // Simple flat layout (symbols, numeric)
      renderRows(content, layout);
    }
  }

  function renderRows(container, rows) {
    for (var r = 0; r < rows.length; r++) {
      var rowEl = el('div', 'vk-row');
      for (var c = 0; c < rows[r].length; c++) {
        var keyEl = buildKey(rows[r][c]);
        rowEl.appendChild(keyEl);
      }
      container.appendChild(rowEl);
    }
  }

  function buildKey(def) {
    // Special keys
    if (def === '{shift}') {
      var k = el('div', 'vk-key vk-key-shift' + (shifted ? ' vk-active' : ''), { 'data-action': 'shift' });
      k.innerHTML = '\u21E7';
      return k;
    }
    if (def === '{backspace}') {
      var k = el('div', 'vk-key vk-key-backspace', { 'data-action': 'backspace' });
      k.innerHTML = '\u232B';
      return k;
    }
    if (def === '{ok}') {
      var k = el('div', 'vk-key vk-key-ok', { 'data-action': 'ok' });
      k.textContent = 'OK';
      return k;
    }
    if (def === '{space}') {
      var k = el('div', 'vk-key vk-key-space', { 'data-action': 'space' });
      k.textContent = '\u2423';
      return k;
    }
    if (def.indexOf('{toggle:') === 0) {
      var label = def.replace('{toggle:', '').replace('}', '');
      var k = el('div', 'vk-key vk-key-toggle', { 'data-action': 'toggle', 'data-target': label === '123' ? 'symbols' : 'azerty' });
      k.textContent = label;
      return k;
    }

    // Regular character key
    var ch = shifted ? def.toUpperCase() : def;
    var k = el('div', 'vk-key', { 'data-char': ch });
    k.textContent = ch;
    return k;
  }

  /* ====================================================================
     Input insertion
     ==================================================================== */
  function insertChar(ch) {
    if (!activeInput) return;

    var inp = activeInput;

    // Fire keydown BEFORE modifying value (autocomplete listens on keydown).
    // Use a safe generic keyCode (65 = 'A') that falls to the autocomplete's
    // default case (triggers onChange search). We must NOT use charCodeAt()
    // because charCodes differ from keyCodes (e.g. 'p'.charCodeAt(0)=112=F1
    // which would trigger jkey navigation shortcuts).
    fireKeyDown(inp, 65);

    var start = inp.selectionStart;
    var end = inp.selectionEnd;

    // If selectionStart is not available (some number inputs), append
    if (start == null) {
      inp.value += ch;
    } else {
      var before = inp.value.substring(0, start);
      var after = inp.value.substring(end);
      inp.value = before + ch + after;
      var newPos = start + ch.length;
      inp.setSelectionRange(newPos, newPos);
    }

    fireAfterInput(inp);
  }

  function doBackspace() {
    if (!activeInput) return;

    var inp = activeInput;

    // Fire keydown with BACKSPACE keyCode (8) before modifying value
    fireKeyDown(inp, 8);

    var start = inp.selectionStart;
    var end = inp.selectionEnd;

    if (start == null) {
      inp.value = inp.value.slice(0, -1);
    } else if (start !== end) {
      // Delete selection
      inp.value = inp.value.substring(0, start) + inp.value.substring(end);
      inp.setSelectionRange(start, start);
    } else if (start > 0) {
      inp.value = inp.value.substring(0, start - 1) + inp.value.substring(end);
      inp.setSelectionRange(start - 1, start - 1);
    }

    fireAfterInput(inp);
  }

  /**
   * Fire a namespaced keydown event on the input so that jQuery autocomplete
   * (which binds on keydown.autocomplete) triggers its onChange/search.
   *
   * IMPORTANT: We use 'keydown.autocomplete' namespace so the event ONLY
   * reaches the autocomplete handler. A plain 'keydown' would bubble up to
   * window and trigger jkey navigation shortcuts (F1=customers, F2=items…).
   */
  function fireKeyDown(inp, keyCode) {
    if (window.jQuery) {
      var evt = jQuery.Event('keydown.autocomplete', {
        keyCode: keyCode,
        which: keyCode
      });
      jQuery(inp).trigger(evt);
    }
  }

  /**
   * Fire input + keyup events after value has been modified.
   */
  function fireAfterInput(inp) {
    // Native input event
    var ev;
    if (typeof Event === 'function') {
      ev = new Event('input', { bubbles: true });
    } else {
      ev = document.createEvent('Event');
      ev.initEvent('input', true, true);
    }
    inp.dispatchEvent(ev);

    // jQuery events for any other plugins
    if (window.jQuery) {
      jQuery(inp).trigger('input').trigger('keyup');
    }
  }

  /* ====================================================================
     Show / Hide
     ==================================================================== */
  function show(input) {
    if (!kbEl) return;
    activeInput = input;

    // Determine layout
    var type = (input.getAttribute('type') || 'text').toLowerCase();
    var vkAttr = input.getAttribute('data-vk');

    if (vkAttr === 'numeric' || type === 'number') {
      renderLayout('numeric');
    } else {
      renderLayout('azerty');
      shifted = false;
    }

    kbEl.classList.add('vk-visible');

    // Scroll input into view above keyboard
    setTimeout(function () {
      var kbRect = kbEl.getBoundingClientRect();
      var inputRect = input.getBoundingClientRect();
      if (inputRect.bottom > kbRect.top) {
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }, 320);
  }

  function hide() {
    if (!kbEl) return;
    kbEl.classList.remove('vk-visible');
    hideAccents();
    activeInput = null;
  }

  /* ====================================================================
     Accent popup
     ==================================================================== */
  function showAccents(charKey, anchorRect) {
    var ch = charKey.getAttribute('data-char');
    if (!ch || !ACCENTS[ch]) return;

    accentsEl.innerHTML = '';
    var variants = ACCENTS[ch];
    for (var i = 0; i < variants.length; i++) {
      var ak = el('div', 'vk-key', { 'data-char': variants[i] });
      ak.textContent = variants[i];
      accentsEl.appendChild(ak);
    }

    // Position above the key
    var left = anchorRect.left;
    var top = anchorRect.top - 56;
    if (left + variants.length * 50 > window.innerWidth) {
      left = window.innerWidth - variants.length * 50 - 8;
    }
    if (top < 0) top = anchorRect.bottom + 4;

    accentsEl.style.left = left + 'px';
    accentsEl.style.top = top + 'px';
    accentsEl.classList.add('vk-accents-visible');
  }

  function hideAccents() {
    if (accentsEl) {
      accentsEl.classList.remove('vk-accents-visible');
      accentsEl.innerHTML = '';
    }
  }

  /* ====================================================================
     Event handling
     ==================================================================== */
  function onKeyAction(e) {
    var target = e.target;
    if (!target.classList.contains('vk-key')) return;

    e.preventDefault();
    e.stopPropagation();

    var action = target.getAttribute('data-action');
    var ch = target.getAttribute('data-char');

    if (action === 'close') {
      hide();
      return;
    }

    if (action === 'shift') {
      shifted = !shifted;
      renderLayout(currentLayout);
      return;
    }

    if (action === 'backspace') {
      doBackspace();
      return;
    }

    if (action === 'ok') {
      var inp = activeInput;
      if (inp) {
        // Fire change event
        var cev;
        if (typeof Event === 'function') {
          cev = new Event('change', { bubbles: true });
        } else {
          cev = document.createEvent('Event');
          cev.initEvent('change', true, true);
        }
        inp.dispatchEvent(cev);

        // For autocomplete search fields, fire Enter keydown so autocomplete
        // selects the current result and submits the form.
        // Use .autocomplete namespace to avoid triggering jkey shortcuts.
        if (window.jQuery) {
          var enterEvt = jQuery.Event('keydown.autocomplete', { keyCode: 13, which: 13 });
          jQuery(inp).trigger(enterEvt);
        }
      }
      hide();
      if (inp) inp.blur();
      return;
    }

    if (action === 'toggle') {
      var targetLayout = target.getAttribute('data-target');
      renderLayout(targetLayout);
      return;
    }

    if (action === 'space') {
      insertChar(' ');
      return;
    }

    // Regular character
    if (ch) {
      hideAccents();
      insertChar(ch);
      return;
    }
  }

  /* Long-press handling for accents */
  function onLongPressStart(e) {
    var target = e.target;
    if (!target.classList.contains('vk-key') || target.getAttribute('data-action')) return;

    var ch = target.getAttribute('data-char');
    if (!ch || !ACCENTS[ch]) return;

    clearTimeout(longPressTimer);
    longPressTimer = setTimeout(function () {
      var rect = target.getBoundingClientRect();
      showAccents(target, rect);
    }, LONG_PRESS_DELAY);
  }

  function onLongPressEnd(e) {
    clearTimeout(longPressTimer);
  }

  /* Click inside accent popup */
  function onAccentClick(e) {
    var target = e.target;
    if (!target.classList.contains('vk-key')) return;
    e.preventDefault();
    e.stopPropagation();

    var ch = target.getAttribute('data-char');
    if (ch) {
      insertChar(ch);
      hideAccents();
    }
  }

  /* Check if an element should trigger the virtual keyboard */
  function isVkTarget(t) {
    if (!t || !t.tagName) return false;
    var tag = t.tagName.toLowerCase();
    if (tag !== 'input' && tag !== 'textarea') return false;
    if (t.getAttribute('data-vk') === 'off') return false;
    if (tag === 'input') {
      var type = (t.getAttribute('type') || 'text').toLowerCase();
      var allowed = ['text', 'password', 'number', 'search', 'tel', 'url', 'email'];
      if (allowed.indexOf(type) === -1) return false;
    }
    return true;
  }

  /* Focus on inputs */
  function onFocusIn(e) {
    if (!enabled) return;
    if (isVkTarget(e.target)) show(e.target);
  }

  /* Touch/click on an input that already has focus (focusin won't re-fire).
     This handles the case where #item has autofocus + .focus() from JS,
     so tapping it doesn't trigger focusin but should still show the keyboard. */
  function onInputTouch(e) {
    if (!enabled) return;
    var t = e.target;
    if (!isVkTarget(t)) return;
    // If keyboard is already visible for this input, do nothing
    if (kbEl && kbEl.classList.contains('vk-visible') && activeInput === t) return;
    show(t);
  }

  /* Click outside to close */
  function onDocumentClick(e) {
    if (!kbEl || !kbEl.classList.contains('vk-visible')) return;

    // If click is on keyboard or accents, ignore
    if (kbEl.contains(e.target)) return;
    if (accentsEl && accentsEl.contains(e.target)) return;

    // If click is on an input that would open the keyboard, let focusin handle it
    var tag = e.target && e.target.tagName ? e.target.tagName.toLowerCase() : '';
    if (tag === 'input' || tag === 'textarea') return;

    hide();
  }

  /* ====================================================================
     Public API: VirtualKeyboard
     ==================================================================== */
  window.VirtualKeyboard = {
    /** Manually enable/disable the keyboard (for desktop testing) */
    toggle: function () {
      manualToggle = !manualToggle;
      enabled = isTouchDevice() || manualToggle;
      if (!enabled) hide();
      return enabled;
    },

    /** Force enable */
    enable: function () {
      manualToggle = true;
      enabled = true;
    },

    /** Force disable */
    disable: function () {
      manualToggle = false;
      enabled = isTouchDevice();
      if (!enabled) hide();
    },

    /** Check if currently enabled */
    isEnabled: function () {
      return enabled;
    },

    /** Programmatically show for a specific input */
    showFor: function (input) {
      if (input) {
        enabled = true;
        show(input);
      }
    },

    /** Programmatically hide */
    hide: function () {
      hide();
    }
  };

  /* ====================================================================
     Initialization
     ==================================================================== */
  function init() {
    enabled = isTouchDevice() || manualToggle;

    buildKeyboard();

    // Prevent input blur when interacting with keyboard (must be on mousedown)
    kbEl.addEventListener('mousedown', function (e) {
      e.preventDefault();
    });

    // Key action: use touchend on touch devices (avoids conflict with long-press),
    // fall back to click for mouse/desktop
    var touchHandled = false;
    kbEl.addEventListener('touchend', function (e) {
      var target = e.target;
      if (!target.classList.contains('vk-key')) return;
      // Only fire if accents popup is not showing for this key (long-press case)
      if (accentsEl && accentsEl.classList.contains('vk-accents-visible')) return;
      e.preventDefault();
      touchHandled = true;
      onKeyAction(e);
    });
    kbEl.addEventListener('click', function (e) {
      if (touchHandled) { touchHandled = false; return; }
      onKeyAction(e);
    });

    // Long-press for accents (touchstart / mousedown to start timer)
    kbEl.addEventListener('touchstart', onLongPressStart, { passive: true });
    kbEl.addEventListener('mousedown', onLongPressStart);
    kbEl.addEventListener('touchend', onLongPressEnd);
    kbEl.addEventListener('mouseup', onLongPressEnd);
    kbEl.addEventListener('touchcancel', onLongPressEnd);

    // Accent popup clicks
    accentsEl.addEventListener('mousedown', function (e) { e.preventDefault(); });
    var accentTouchHandled = false;
    accentsEl.addEventListener('touchend', function (e) {
      var target = e.target;
      if (!target.classList.contains('vk-key')) return;
      e.preventDefault();
      accentTouchHandled = true;
      onAccentClick(e);
    });
    accentsEl.addEventListener('click', function (e) {
      if (accentTouchHandled) { accentTouchHandled = false; return; }
      onAccentClick(e);
    });

    // Focus detection (new focus)
    document.addEventListener('focusin', onFocusIn, true);

    // Touch/click detection (re-open keyboard on already-focused input)
    document.addEventListener('touchstart', onInputTouch, true);
    document.addEventListener('mousedown', onInputTouch, true);

    // Click outside
    document.addEventListener('click', onDocumentClick, true);

    // Also hide accents on any touch outside
    document.addEventListener('touchstart', function (e) {
      if (accentsEl && accentsEl.classList.contains('vk-accents-visible')) {
        if (!accentsEl.contains(e.target)) {
          hideAccents();
        }
      }
    }, true);
  }

  // Run on DOMContentLoaded or immediately if already loaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
