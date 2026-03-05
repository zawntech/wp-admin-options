/**
 * WPAdminOptions — global namespace for admin option field scripts.
 *
 * Each public method mirrors a PHP option class name and accepts a config
 * object with server-side data.  Shared helpers are prefixed with underscore.
 *
 * @global {Object} window.WPAdminOptions
 */
(function ($, Vue) {

  'use strict';

  /* ------------------------------------------------------------------ */
  /*  Shared helpers                                                     */
  /* ------------------------------------------------------------------ */

  /**
   * Drag-and-drop mixin for Vue apps that manage a reorderable list.
   * Expects `this.items` (array) to exist on the Vue instance.
   */
  function _dragMixin() {
    return {
      data: {
        dragIndex: null,
        dragOverIndex: null
      },
      methods: {
        dragStart: function (index, event) {
          this.dragIndex = index;
          event.dataTransfer.effectAllowed = 'move';
        },
        dragOver: function (index) {
          this.dragOverIndex = index;
        },
        drop: function (index) {
          if (this.dragIndex === null || this.dragIndex === index) return;
          var item = this.items.splice(this.dragIndex, 1)[0];
          this.items.splice(index, 0, item);
          this.dragIndex = null;
          this.dragOverIndex = null;
        },
        dragEnd: function () {
          this.dragIndex = null;
          this.dragOverIndex = null;
        }
      }
    };
  }

  /**
   * Item management mixin (add from select, remove, reorder).
   *
   * @param {string}  key        - The option key (used for Select2 reset).
   * @param {string}  items      - The Vue data property name for the list (e.g. 'items', 'ids').
   * @param {string}  [alertText] - Alert message when no selection is made.
   */
  function _itemMixin(key, items, alertText) {
    return {
      methods: {
        addItem: function () {
          if ('' === this.selectedPost) {
            alert(alertText || 'Please select an item to add.');
            return;
          }
          if (-1 === this[items].indexOf(this.selectedPost)) {
            this[items].push(this.selectedPost);
          }
          this.selectedPost = '';
          $('#' + key + '-wrap .select2').val('').trigger('change');
        },

        removeItem: function (item) {
          this[items].splice(this[items].indexOf(item), 1);
        },

        canMoveUp: function (item) {
          return this[items].indexOf(item) > 0;
        },

        canMoveDown: function (item) {
          return this[items].indexOf(item) < this[items].length - 1;
        },

        moveUp: function (item) {
          var index = this[items].indexOf(item);
          if (this.canMoveUp(item)) {
            var prev = this[items][index - 1];
            this[items].splice(index - 1, 2, item, prev);
          }
        },

        moveDown: function (item) {
          var index = this[items].indexOf(item);
          if (this.canMoveDown(item)) {
            var next = this[items][index + 1];
            this[items].splice(index, 2, next, item);
          }
        }
      }
    };
  }

  /**
   * Select2 + Vue mount helper for multi-select option types.
   * Initialises Select2 inside the wrapper, binds selection to Vue data,
   * and fades in the wrapper element.
   *
   * @param {string} key - The option key (wrapper id = key + '-wrap').
   */
  function _select2Mount(key, options) {
    var s2opts = options || {};
    return function () {
      var select = $('#' + key + '-wrap .select2'),
        self = this;
      $('#' + key + '-wrap').fadeIn(function () {
        select.select2(s2opts);
        select.on('select2:select', function () {
          self.selectedPost = select.val();
        });
      });
    };
  }

  /**
   * Deep-merge Vue option objects.  Merges `data` return values,
   * combines `methods` / `computed`, and chains `mounted` callbacks.
   */
  function _merge(/* ...sources */) {
    var sources = Array.prototype.slice.call(arguments);
    var dataFns = [];
    var mounted = [];
    var methods = {};
    var computed = {};
    var extra = {};

    sources.forEach(function (src) {
      if (!src) return;
      if (src.data) {
        dataFns.push(typeof src.data === 'function' ? src.data : function () { return src.data; });
      }
      if (src.methods) { Object.assign(methods, src.methods); }
      if (src.computed) { Object.assign(computed, src.computed); }
      if (src.mounted) { mounted.push(src.mounted); }
      Object.keys(src).forEach(function (k) {
        if (['data', 'methods', 'computed', 'mounted'].indexOf(k) === -1) {
          extra[k] = src[k];
        }
      });
    });

    var merged = Object.assign({}, extra);

    merged.data = function () {
      var result = {};
      dataFns.forEach(function (fn) { Object.assign(result, fn.call(this)); }.bind(this));
      return result;
    };

    if (Object.keys(methods).length) { merged.methods = methods; }
    if (Object.keys(computed).length) { merged.computed = computed; }
    if (mounted.length) {
      merged.mounted = function () {
        var self = this;
        mounted.forEach(function (fn) { fn.call(self); });
      };
    }

    return merged;
  }

  /* ------------------------------------------------------------------ */
  /*  Color helpers                                                      */
  /* ------------------------------------------------------------------ */

  function _clamp(v, min, max) { return Math.max(min, Math.min(max, v)); }

  /**
   * Convert HSV to RGB.
   * @param {number} h  0-360
   * @param {number} s  0-1
   * @param {number} v  0-1
   * @returns {number[]} [r, g, b] each 0-255
   */
  function _hsvToRgb(h, s, v) {
    h = ((h % 360) + 360) % 360;
    var c = v * s;
    var x = c * (1 - Math.abs((h / 60) % 2 - 1));
    var m = v - c;
    var r = 0, g = 0, b = 0;
    if      (h < 60)  { r = c; g = x; }
    else if (h < 120) { r = x; g = c; }
    else if (h < 180) { g = c; b = x; }
    else if (h < 240) { g = x; b = c; }
    else if (h < 300) { r = x; b = c; }
    else              { r = c; b = x; }
    return [Math.round((r + m) * 255), Math.round((g + m) * 255), Math.round((b + m) * 255)];
  }

  /**
   * Convert RGB to HSV.
   * @param {number} r  0-255
   * @param {number} g  0-255
   * @param {number} b  0-255
   * @returns {number[]} [h, s, v]  h: 0-360, s: 0-1, v: 0-1
   */
  function _rgbToHsv(r, g, b) {
    r /= 255; g /= 255; b /= 255;
    var max = Math.max(r, g, b), min = Math.min(r, g, b);
    var d = max - min;
    var h = 0, s = max === 0 ? 0 : d / max, v = max;
    if (d !== 0) {
      switch (max) {
        case r: h = ((g - b) / d + (g < b ? 6 : 0)) * 60; break;
        case g: h = ((b - r) / d + 2) * 60; break;
        case b: h = ((r - g) / d + 4) * 60; break;
      }
    }
    return [h, s, v];
  }

  function _rgbToHex(r, g, b) {
    return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
  }

  /**
   * Format an RGBA colour as either #RRGGBB (opaque) or rgba() (translucent).
   */
  function _formatColor(r, g, b, a) {
    if (a < 1) {
      return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + (Math.round(a * 100) / 100) + ')';
    }
    return _rgbToHex(r, g, b);
  }

  /**
   * Parse a colour string (hex 3/6/8, rgb(), rgba()) into {r,g,b,a} or null.
   */
  function _parseColor(str) {
    if (!str) return null;
    str = str.trim();
    // #RGB, #RRGGBB, #RRGGBBAA
    var m = str.match(/^#([0-9a-f]{3,8})$/i);
    if (m) {
      var hex = m[1];
      if (hex.length === 3) return { r: parseInt(hex[0]+hex[0],16), g: parseInt(hex[1]+hex[1],16), b: parseInt(hex[2]+hex[2],16), a: 1 };
      if (hex.length === 6) return { r: parseInt(hex.slice(0,2),16), g: parseInt(hex.slice(2,4),16), b: parseInt(hex.slice(4,6),16), a: 1 };
      if (hex.length === 8) return { r: parseInt(hex.slice(0,2),16), g: parseInt(hex.slice(2,4),16), b: parseInt(hex.slice(4,6),16), a: parseInt(hex.slice(6,8),16)/255 };
    }
    // rgb(r,g,b) / rgba(r,g,b,a)
    m = str.match(/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*([\d.]+))?\s*\)$/);
    if (m) return { r: +m[1], g: +m[2], b: +m[3], a: m[4] !== undefined ? parseFloat(m[4]) : 1 };
    return null;
  }

  /**
   * Read pointer X/Y relative to an element, clamped to 0-1.
   */
  function _pointerRatio(el, e) {
    var touch = e.touches ? e.touches[0] : e;
    var rect = el.getBoundingClientRect();
    return {
      x: _clamp((touch.clientX - rect.left) / rect.width, 0, 1),
      y: _clamp((touch.clientY - rect.top) / rect.height, 0, 1)
    };
  }

  /* ------------------------------------------------------------------ */
  /*  Public API                                                         */
  /* ------------------------------------------------------------------ */

  window.WPAdminOptions = {

    /* Expose helpers for extension by consumer code */
    _dragMixin: _dragMixin,
    _itemMixin: _itemMixin,
    _select2Mount: _select2Mount,
    _merge: _merge,

    /* -------------------------------------------------------------- */
    /*  CopyButton                                                     */
    /* -------------------------------------------------------------- */

    /**
     * @param {Object} config
     * @param {string} config.key - The input element ID and copy-target attribute value.
     */
    CopyButton: function (config) {
      var btn = document.querySelector('[data-copy-target="' + config.key + '"]');
      if (!btn) return;
      btn.addEventListener('click', function () {
        var input = document.getElementById(config.key);
        if (!input) return;
        navigator.clipboard.writeText(input.value).then(function () {
          btn.querySelector('.wao-copy-icon').style.display = 'none';
          btn.querySelector('.wao-copy-done').style.display = '';
          setTimeout(function () {
            btn.querySelector('.wao-copy-icon').style.display = '';
            btn.querySelector('.wao-copy-done').style.display = 'none';
          }, 1500);
        });
      });
    },

    /* -------------------------------------------------------------- */
    /*  PasswordReveal                                                 */
    /* -------------------------------------------------------------- */

    /**
     * @param {Object} config
     * @param {string} config.key - The password input element ID.
     */
    PasswordReveal: function (config) {
      var btn = document.querySelector('[data-reveal-target="' + config.key + '"]');
      if (!btn) return;
      var input = document.getElementById(config.key);
      if (!input) return;
      btn.addEventListener('click', function () {
        var isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        btn.classList.toggle('wao-reveal-active', isPassword);
        btn.title = isPassword ? 'Hide password' : 'Show password';
      });
    },

    /* -------------------------------------------------------------- */
    /*  TelMask                                                        */
    /* -------------------------------------------------------------- */

    /**
     * @param {Object} config
     * @param {string} config.key    - The tel input element ID.
     * @param {string} config.format - 'US', 'International', or a custom mask pattern.
     */
    TelMask: function (config) {
      var input = document.getElementById(config.key);
      if (!input) return;

      var format = config.format;
      var digitsOnly = config.digitsOnly;
      var mask;

      if (format === 'US') {
        mask = '(###) ###-####';
      } else if (format === 'International') {
        mask = '+## ### #### ####';
      } else {
        mask = format;
      }

      // When digitsOnly is enabled, the visible input becomes display-only
      // and a hidden input carries the raw digit value for form submission.
      var hidden;
      if (digitsOnly) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = input.name;
        input.name = input.name + '_display';
        input.parentNode.insertBefore(hidden, input.nextSibling);
      }

      function getDigits(value) {
        return value.replace(/\D/g, '');
      }

      function applyMask(value) {
        var digits = getDigits(value);
        var result = '';
        var di = 0;
        for (var i = 0; i < mask.length && di < digits.length; i++) {
          if (mask[i] === '#') {
            result += digits[di++];
          } else {
            result += mask[i];
            // If the user typed a character that matches the mask literal, skip it
            if (digits[di] === mask[i]) di++;
          }
        }
        return result;
      }

      function syncHidden() {
        if (hidden) {
          hidden.value = getDigits(input.value);
        }
      }

      // Format initial value
      if (input.value) {
        input.value = applyMask(input.value);
      }
      syncHidden();

      input.addEventListener('input', function () {
        var pos = input.selectionStart;
        var oldLen = input.value.length;
        input.value = applyMask(input.value);
        var newLen = input.value.length;
        // Adjust cursor position after formatting
        var newPos = pos + (newLen - oldLen);
        input.setSelectionRange(newPos, newPos);
        syncHidden();
      });

      // Set maxlength based on mask length
      input.setAttribute('maxlength', mask.length);
    },

    /* -------------------------------------------------------------- */
    /*  BooleanCheckboxOption                                          */
    /* -------------------------------------------------------------- */

    /**
     * @param {Object} config
     * @param {string} config.key
     */
    BooleanCheckboxOption: function (config) {
      $('input#' + config.key).on('change', function () {
        var checked = $(this).is(':checked');
        $('input[name="' + config.key + '"]').val(checked ? 1 : 0);
      });
    },

    /* -------------------------------------------------------------- */
    /*  ColorOption                                                    */
    /* -------------------------------------------------------------- */

    /**
     * @param {Object} config
     * @param {string} config.key
     * @param {string} config.type - 'default', 'spectrum', or 'swatches'
     * @param {string[]} [config.swatches] - Array of color values (HEX or RGB/RGBA)
     * @param {string} [config.value] - Current selected value
     */
    ColorOption: function (config) {
      var selector = 'input[name="' + config.key + '"]';

      if (config.type === 'swatches') {
        var mountEl = document.getElementById('wao-swatches-' + config.key);
        if (!mountEl) return;

        Vue.createApp({
          data: function () {
            var parsed = _parseColor(config.value);
            var hsv = parsed ? _rgbToHsv(parsed.r, parsed.g, parsed.b) : [0, 1, 1];
            return {
              h: hsv[0], s: hsv[1], v: hsv[2],
              a: parsed ? parsed.a : 1,
              selected: config.value || '',
              swatches: config.swatches || [],
              pickerOpen: false,
              dragging: null,
              customInput: '',
              inputError: ''
            };
          },
          computed: {
            rgb: function () { return _hsvToRgb(this.h, this.s, this.v); },
            hueRgb: function () { return _hsvToRgb(this.h, 1, 1); },
            svStyle: function () {
              var c = this.hueRgb;
              return { backgroundColor: 'rgb(' + c[0] + ',' + c[1] + ',' + c[2] + ')' };
            },
            svCursorStyle: function () {
              return { left: (this.s * 100) + '%', top: ((1 - this.v) * 100) + '%' };
            },
            hueCursorStyle: function () {
              return { left: (this.h / 360 * 100) + '%' };
            },
            alphaGradientStyle: function () {
              var c = this.rgb;
              return {
                background: 'linear-gradient(to right, transparent, rgb(' + c[0] + ',' + c[1] + ',' + c[2] + '))'
              };
            },
            alphaCursorStyle: function () {
              return { left: (this.a * 100) + '%' };
            },
            previewStyle: function () {
              if (!this.selected) return { backgroundColor: 'transparent' };
              return { backgroundColor: this.selected };
            }
          },
          methods: {
            sync: function () {
              var c = this.rgb;
              this.selected = _formatColor(c[0], c[1], c[2], this.a);
              var input = document.querySelector('input[name="' + config.key + '"]');
              if (input) input.value = this.selected;
            },
            /* -- Drag handling -- */
            startDrag: function (area, e) {
              this.dragging = area;
              this.onDrag(e);
            },
            onDrag: function (e) {
              if (!this.dragging) return;
              var ref = this.$refs[this.dragging];
              if (!ref) return;
              var r = _pointerRatio(ref, e);
              switch (this.dragging) {
                case 'sv':    this.s = r.x; this.v = 1 - r.y; break;
                case 'hue':   this.h = r.x * 360; break;
                case 'alpha': this.a = r.x; break;
              }
              this.sync();
            },
            stopDrag: function () { this.dragging = null; },
            /* -- Actions -- */
            selectSwatch: function (color) {
              var parsed = _parseColor(color);
              if (!parsed) return;
              var hsv = _rgbToHsv(parsed.r, parsed.g, parsed.b);
              this.h = hsv[0]; this.s = hsv[1]; this.v = hsv[2];
              this.a = parsed.a;
              this.selected = color;
              var input = document.querySelector('input[name="' + config.key + '"]');
              if (input) input.value = color;
            },
            applyCustomInput: function () {
              var val = this.customInput.trim();
              if (!val) { this.inputError = ''; return; }
              var parsed = _parseColor(val);
              if (!parsed) {
                this.inputError = 'Invalid color. Use #RGB, #RRGGBB, or rgba(r,g,b,a).';
                return;
              }
              this.inputError = '';
              var hsv = _rgbToHsv(parsed.r, parsed.g, parsed.b);
              this.h = hsv[0]; this.s = hsv[1]; this.v = hsv[2];
              this.a = parsed.a;
              this.selected = _formatColor(parsed.r, parsed.g, parsed.b, parsed.a);
              this.customInput = this.selected;
              var input = document.querySelector('input[name="' + config.key + '"]');
              if (input) input.value = this.selected;
            },
            clear: function () {
              this.selected = '';
              this.pickerOpen = false;
              var input = document.querySelector('input[name="' + config.key + '"]');
              if (input) input.value = '';
            },
            closeOnOutsideClick: function (e) {
              if (this.$refs.wrap && !this.$refs.wrap.contains(e.target)) {
                this.pickerOpen = false;
              }
            }
          },
          mounted: function () {
            this.$el.style.display = '';
            var self = this;
            document.addEventListener('click', this.closeOnOutsideClick);
            document.addEventListener('mousemove', function (e) { self.onDrag(e); });
            document.addEventListener('mouseup', function () { self.stopDrag(); });
            document.addEventListener('touchmove', function (e) { self.onDrag(e); }, { passive: false });
            document.addEventListener('touchend', function () { self.stopDrag(); });
          },
          beforeUnmount: function () {
            document.removeEventListener('click', this.closeOnOutsideClick);
          },
          template:
            '<div class="wao-cp" ref="wrap" style="display:none">' +
              /* ---- Header: preview + text input + buttons ---- */
              '<div class="wao-cp-header">' +
                '<span class="wao-cp-preview" :class="{ \'wao-cp-preview-empty\': !selected }" :style="previewStyle"></span>' +
                '<input type="text" class="wao-cp-value" :value="selected" placeholder="No color" readonly>' +
                '<button type="button" class="button wao-cp-pick-btn" @click.stop="pickerOpen = !pickerOpen">{{ pickerOpen ? "Close" : "Pick" }}</button>' +
                '<button v-if="selected" type="button" class="button wao-cp-clear-btn" @click.stop="clear">&times;</button>' +
              '</div>' +
              /* ---- Picker panel ---- */
              '<div v-if="pickerOpen" class="wao-cp-panel" @mousedown.stop>' +
                /* SV area */
                '<div class="wao-cp-sv" ref="sv" :style="svStyle" @mousedown.prevent="startDrag(\'sv\',$event)" @touchstart.prevent="startDrag(\'sv\',$event)">' +
                  '<div class="wao-cp-sv-white"></div>' +
                  '<div class="wao-cp-sv-black"></div>' +
                  '<div class="wao-cp-sv-cursor" :style="svCursorStyle"></div>' +
                '</div>' +
                /* Hue bar */
                '<div class="wao-cp-hue" ref="hue" @mousedown.prevent="startDrag(\'hue\',$event)" @touchstart.prevent="startDrag(\'hue\',$event)">' +
                  '<div class="wao-cp-slider-thumb" :style="hueCursorStyle"></div>' +
                '</div>' +
                /* Alpha bar */
                '<div class="wao-cp-alpha" ref="alpha" @mousedown.prevent="startDrag(\'alpha\',$event)" @touchstart.prevent="startDrag(\'alpha\',$event)">' +
                  '<div class="wao-cp-alpha-fill" :style="alphaGradientStyle"></div>' +
                  '<div class="wao-cp-slider-thumb" :style="alphaCursorStyle"></div>' +
                '</div>' +
                /* Custom input */
                '<div class="wao-cp-custom">' +
                  '<div class="wao-cp-custom-row">' +
                    '<input type="text" class="wao-cp-custom-input" :class="{ \'wao-cp-custom-invalid\': inputError }" v-model="customInput" placeholder="#hex or rgba()" @keydown.enter="applyCustomInput">' +
                    '<button type="button" class="button wao-cp-apply-btn" @click.stop="applyCustomInput">Apply</button>' +
                  '</div>' +
                  '<div v-if="inputError" class="wao-cp-custom-error">{{ inputError }}</div>' +
                '</div>' +
                /* Swatches */
                '<div v-if="swatches.length" class="wao-cp-swatches">' +
                  '<button v-for="c in swatches" :key="c" type="button" class="wao-swatch-btn" :class="{\'wao-swatch-active\': selected === c}" :style="{backgroundColor: c}" :title="c" @click.stop="selectSwatch(c)"></button>' +
                '</div>' +
              '</div>' +
            '</div>'
        }).mount(mountEl);
      } else if (config.type === 'spectrum') {
        $(selector).spectrum({
          showInput: true,
          showAlpha: true,
          preferredFormat: 'hex',
          allowEmpty: true
        });
      } else {
        $(selector).wpColorPicker();
      }
    },

    /* -------------------------------------------------------------- */
    /*  SelectOption                                                   */
    /* -------------------------------------------------------------- */

    /**
     * @param {Object}  config
     * @param {string}  config.key
     * @param {string}  config.mode    - 'single' or 'multiple'
     * @param {Array}   [config.items] - Selected item values (multiple mode).
     * @param {Object}  [config.options] - value→label map (multiple mode).
     */
    SelectOption: function (config) {
      if (config.mode === 'single') {
        $('#' + config.key + '-wrap .select2').select2();
        return;
      }

      var opts = _merge(
        _dragMixin(),
        _itemMixin(config.key, 'items', 'Please select an item to add.'),
        {
          data: function () {
            return {
              selectedPost: '',
              items: config.items,
              posts: config.options
            };
          },
          computed: {
            json: function () { return JSON.stringify(this.items); }
          },
          methods: {
            formatPostTitle: function (postId) {
              return this.posts[postId];
            }
          },
          mounted: _select2Mount(config.key, { placeholder: config.placeholder || 'Choose option...', allowClear: false })
        }
      );
      Vue.createApp(opts).mount('#' + config.key + '-wrap');
    },

    /* -------------------------------------------------------------- */
    /*  PostTypeSelectOption                                           */
    /* -------------------------------------------------------------- */

    /**
     * @param {Object}  config
     * @param {string}  config.key
     * @param {string}  config.mode      - 'single' or 'multiple'
     * @param {Array}   [config.items]
     * @param {Object}  [config.options]
     * @param {string}  [config.adminUrl]
     * @param {string}  [config.homeUrl]
     */
    PostTypeSelectOption: function (config) {
      if (config.mode === 'single') {
        $('#' + config.key + '-wrap .select2').select2();
        return;
      }

      var opts = _merge(
        _dragMixin(),
        _itemMixin(config.key, 'items', 'Please select a post to add.'),
        {
          data: function () {
            return {
              selectedPost: '',
              items: config.items,
              posts: config.options
            };
          },
          computed: {
            json: function () { return JSON.stringify(this.items); }
          },
          methods: {
            formatPostTitle: function (postId, index) {
              var count = (index + 1),
                postTitle = this.posts[postId],
                editPostUrl = config.adminUrl + 'post.php?post=' + postId + '&action=edit',
                editPostLink = '<a href="' + editPostUrl + '" target="_blank" class="wao-link">[Edit]</a>',
                viewPostUrl = config.homeUrl + '?p=' + postId,
                viewPostLink = '<a href="' + viewPostUrl + '" target="_blank" class="wao-link">[View]</a>';
              return ['#' + count, '-', postTitle, editPostLink, viewPostLink].join(' ');
            }
          },
          mounted: _select2Mount(config.key)
        }
      );
      Vue.createApp(opts).mount('#' + config.key + '-wrap');
    },

    /* -------------------------------------------------------------- */
    /*  TaxonomySelectOption                                           */
    /* -------------------------------------------------------------- */

    /**
     * @param {Object}  config
     * @param {string}  config.key
     * @param {string}  config.mode
     * @param {Array}   [config.items]
     * @param {Object}  [config.options]
     * @param {string}  [config.adminUrl]
     * @param {string}  [config.homeUrl]
     * @param {string}  [config.taxonomy]
     */
    TaxonomySelectOption: function (config) {
      if (config.mode === 'single') {
        $('#' + config.key + '-wrap .select2').select2();
        return;
      }

      var taxonomy = config.taxonomy;
      var opts = _merge(
        _dragMixin(),
        _itemMixin(config.key, 'items', 'Please select a term to add.'),
        {
          data: function () {
            return {
              selectedPost: '',
              items: config.items,
              posts: config.options
            };
          },
          computed: {
            json: function () { return JSON.stringify(this.items); }
          },
          methods: {
            formatPostTitle: function (postId, index) {
              var count = (index + 1),
                postTitle = this.posts[postId],
                editPostUrl = config.adminUrl + 'term.php?taxonomy=' + taxonomy + '&tag_ID=' + postId,
                editPostLink = '<a href="' + editPostUrl + '" target="_blank" class="wao-link">[Edit]</a>';
              return ['#' + count, '-', postTitle, editPostLink].join(' ');
            }
          },
          mounted: _select2Mount(config.key)
        }
      );
      Vue.createApp(opts).mount('#' + config.key + '-wrap');
    },

    /* -------------------------------------------------------------- */
    /*  UserSelectOption                                               */
    /* -------------------------------------------------------------- */

    /**
     * @param {Object}  config
     * @param {string}  config.key
     * @param {string}  config.mode
     * @param {Array}   [config.items]
     * @param {Object}  [config.options]
     * @param {string}  [config.adminUrl]
     */
    UserSelectOption: function (config) {
      if (config.mode === 'single') {
        $('#' + config.key + '-wrap .select2').select2();
        return;
      }

      var opts = _merge(
        _dragMixin(),
        _itemMixin(config.key, 'items', 'Please select a user to add.'),
        {
          data: function () {
            return {
              selectedPost: '',
              items: config.items,
              posts: config.options
            };
          },
          computed: {
            json: function () { return JSON.stringify(this.items); }
          },
          methods: {
            formatPostTitle: function (postId, index) {
              var count = (index + 1),
                postTitle = this.posts[postId],
                editPostUrl = config.adminUrl + 'user-edit.php?user_id=' + postId + '&action=edit',
                editPostLink = '<a href="' + editPostUrl + '" target="_blank" class="wao-link">[Edit]</a>';
              return ['#' + count, '-', postTitle, editPostLink].join(' ');
            }
          },
          mounted: _select2Mount(config.key)
        }
      );
      Vue.createApp(opts).mount('#' + config.key + '-wrap');
    },

    /* -------------------------------------------------------------- */
    /*  AttachmentOption                                               */
    /* -------------------------------------------------------------- */

    /**
     * @param {Object}  config
     * @param {string}  config.key
     * @param {Array}   config.ids
     * @param {Array}   config.data
     * @param {boolean} config.multiple
     * @param {Array}   config.mediaTypes
     * @param {string}  config.label
     */
    AttachmentOption: function (config) {
      var frame;

      var dragData = _dragMixin();
      // AttachmentOption uses `ids` array instead of `items` for drag reordering.
      dragData.methods.drop = function (index) {
        if (this.dragIndex === null || this.dragIndex === index) return;
        var id = this.ids.splice(this.dragIndex, 1)[0];
        this.ids.splice(index, 0, id);
        this.dragIndex = null;
        this.dragOverIndex = null;
      };

      var opts = _merge(
        dragData,
        {
          data: function () {
            return {
              ids: config.ids,
              data: config.data
            };
          },

          methods: {

            clear: function () {
              this.ids = [];
              this.data = [];
            },

            openFrame: function () {
              if (frame) {
                frame.open();
              }
              frame = wp.media({
                frame: 'select',
                title: config.label,
                button: { text: 'Select' },
                multiple: config.multiple,
                library: { type: config.mediaTypes }
              })
              .on('select', this.selectItems);
              frame.open();
            },

            selectItems: function () {
              if (!config.multiple) {
                this.clear();
              }
              var attachments = frame.state().get('selection').toJSON();
              for (var i in attachments) {
                var id = Number(attachments[i].id);
                if (-1 !== this.ids.indexOf(id)) {
                  continue;
                }
                this.ids.push(id);
                this.data.push(attachments[i]);
              }
              frame.close();
            },

            removeItem: function (id) {
              id = Number(id);
              this.ids.splice(this.ids.indexOf(id), 1);
            },

            canMoveUp: function (id) {
              id = Number(id);
              return this.ids.indexOf(id) > 0;
            },

            canMoveDown: function (id) {
              id = Number(id);
              return this.ids.indexOf(id) < this.ids.length - 1;
            },

            moveUp: function (id) {
              id = Number(id);
              var index = this.ids.indexOf(id);
              if (this.canMoveUp(id)) {
                var prev = this.ids[index - 1];
                this.ids.splice(index - 1, 2, id, prev);
              }
            },

            moveDown: function (id) {
              id = Number(id);
              var index = this.ids.indexOf(id);
              if (this.canMoveDown(id)) {
                var next = this.ids[index + 1];
                this.ids.splice(index, 2, next, id);
              }
            },

            getType: function (item) {
              switch (item.type) {
                case 'image': return 'Image';
                case 'video': return 'Video';
                default: return 'Other';
              }
            }
          },

          computed: {
            json: function () {
              return JSON.stringify(this.ids);
            },

            media: function () {
              var data = this.data;
              return this.ids.map(function (id) {
                for (var i in data) {
                  if (data[i] && id == data[i].id) {
                    return data[i];
                  }
                }
              }).filter(function (item) {
                return item;
              });
            }
          }
        }
      );

      Vue.createApp(opts).mount('#' + config.key);
    },

    /* -------------------------------------------------------------- */
    /*  OptionsContainer                                               */
    /* -------------------------------------------------------------- */

    /**
     * @param {Object} config
     * @param {string} config.key - The container key for localStorage persistence.
     */
    OptionsContainer: function (config) {
      var container = document.getElementById('wao-container-' + config.key);
      if (!container) return;

      var storageKey = 'wao_container_' + config.key;
      var header = container.querySelector('.wao-container-header');

      // Restore collapsed state from localStorage.
      var stored = localStorage.getItem(storageKey);
      if (stored === 'collapsed') {
        container.classList.add('wao-collapsed');
      } else if (stored === 'expanded') {
        container.classList.remove('wao-collapsed');
      }

      header.addEventListener('click', function (e) {
        // Don't toggle if clicking inside interactive elements within the header.
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'A') return;
        container.classList.toggle('wao-collapsed');
        localStorage.setItem(storageKey, container.classList.contains('wao-collapsed') ? 'collapsed' : 'expanded');
      });
    },

    /* -------------------------------------------------------------- */
    /*  DateTimeOption                                                 */
    /* -------------------------------------------------------------- */

    /**
     * @param {Object} config
     * @param {string} config.key
     * @param {string} config.date
     * @param {string} config.time
     */
    DateTimeOption: function (config) {
      Vue.createApp({
        data: function () {
          return {
            date: config.date,
            time: config.time
          };
        },
        computed: {
          json: function () {
            var timeString = this.date + ' ' + this.time;
            return timeString.trim();
          }
        },
        mounted: function () {
          $('#' + config.key + ' .option-wrap').fadeIn();
        }
      }).mount('#' + config.key);
    },

    /* -------------------------------------------------------------- */
    /*  DurationOption                                                 */
    /* -------------------------------------------------------------- */

    /**
     * @param {Object} config
     * @param {string} config.key
     * @param {number} config.value - Total duration in minutes.
     */
    DurationOption: function (config) {
      Vue.createApp({
        data: function () {
          return {
            minutes: 0,
            hours: 0,
            days: '',
            custom: false,
            options: {
              minutes: [0, 15, 30, 45],
              hours: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
            }
          };
        },
        computed: {
          json: function () {
            return JSON.stringify(this.value);
          },
          value: function () {
            return this.minutes + (60 * this.hours);
          }
        },
        mounted: function () {
          $('#' + config.key + ' .option-wrap').fadeIn();
          var total = Number(config.value),
            hours = Math.floor(total / 60),
            mins = total % 60;
          this.hours = hours;
          this.minutes = mins;
          if (this.options.hours.indexOf(hours) === -1 || this.options.minutes.indexOf(mins) === -1) {
            this.custom = true;
          }
        }
      }).mount('#' + config.key);
    }

  };

})(jQuery, Vue);
