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
  function _select2Mount(key) {
    return function () {
      var select = $('#' + key + '-wrap .select2'),
        self = this;
      $('#' + key + '-wrap').fadeIn(function () {
        select.select2();
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
     * @param {string} config.type - 'default' or 'spectrum'
     */
    ColorOption: function (config) {
      var selector = 'input[name="' + config.key + '"]';
      if (config.type === 'spectrum') {
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
          mounted: _select2Mount(config.key)
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
