<?php

namespace Zawntech\WPAdminOptions;

class ExampleJsonOption extends AbstractAdminOption
{
    public function render_admin_table() {
        if ( $this->render_array_error() ) return;
        $key = esc_attr( $this->args['key'] );
        ?>
        <tr id="<?= $key; ?>">
            <?php $this->render_option_label(); ?>
            <td>
                <?php $this->render_html(); ?>
                <input type="hidden" name="<?= $key; ?>" :value="json">
            </td>
        </tr>
        <?php
        add_action( 'admin_footer', [$this, 'render_script'] );
    }

    public function render_html() {
        ?>
        <div class="example-json-option-wrap">

            <button type="button" class="button" @click="addItem()">Add</button>

            <hr>

            <div class="wao-items">

                <p v-if="!items.length">No example items are assigned.</p>

                <div v-for="(item, i) in items" class="wao-item"
                     :class="{'wao-dragging': dragIndex === i, 'wao-dragover': dragOverIndex === i}"
                     draggable="true"
                     @dragstart="dragStart(i, $event)"
                     @dragover.prevent="dragOver(i)"
                     @drop="drop(i)"
                     @dragend="dragEnd">

                    <div class="wao-item-row">
                        <span class="wao-drag-handle" title="Drag to reorder">&#x2630;</span>
                        <div class="wao-controls">
                            <button type="button" class="button" :disabled="!canMoveUp(item)" @click="moveUp(item)">&#x25B2;</button>
                            <button type="button" class="button" :disabled="!canMoveDown(item)" @click="moveDown(item)">&#x25BC;</button>
                            <button type="button" class="button" @click="removeItem(item)">×</button>
                        </div>
                    </div>

                    <div class="wao-json-fields">
                        <label class="wao-field">
                            <span class="wao-field-label">Day</span>
                            <input type="text" placeholder="Day" v-model="item.day" class="widefat">
                        </label>
                        <label class="wao-field">
                            <span class="wao-field-label">Time</span>
                            <input type="text" placeholder="Time" v-model="item.time" class="widefat">
                        </label>
                        <label class="wao-field">
                            <span class="wao-field-label">Description</span>
                            <textarea placeholder="Description" v-model="item.description" class="widefat"></textarea>
                        </label>
                    </div>

                </div>
            </div>
        </div>
        <?php
    }

    public function render_script() {
        $key = esc_attr( $this->args['key'] );
        ?>
        <script>
          /**
           * Example: extend WPAdminOptions with a custom JSON option type.
           * Uses the shared _dragMixin, _itemMixin, and _merge helpers from the global namespace.
           */
          WPAdminOptions.ExampleJsonOption = function (config) {
            jQuery(document).ready(function ($) {
              var opts = WPAdminOptions._merge(
                WPAdminOptions._dragMixin(),
                WPAdminOptions._itemMixin(null, 'items'),
                {
                  data: function () {
                    return {
                      items: config.items
                    };
                  },
                  computed: {
                    json: function () {
                      return JSON.stringify(this.items);
                    }
                  },
                  methods: {
                    // Override _itemMixin's addItem to push a new object instead of selecting from a list.
                    addItem: function () {
                      this.items.push({
                        id: Date.now(),
                        first_name: '',
                        last_name: '',
                        description: ''
                      });
                    }
                  },
                  mounted: function () {
                    $('#' + config.key + ' .option-wrap').fadeIn();
                  }
                }
              );
              Vue.createApp(opts).mount('#' + config.key);
            });
          };
          window.addEventListener('load', function() {
            <?php $args = [ 'key' => $key, 'items' => $this->args['value'] ]; ?>
            WPAdminOptions.ExampleJsonOption(<?= json_encode( $args ); ?>);
          });
        </script>
        <?php
    }

}