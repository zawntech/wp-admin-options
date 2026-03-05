<?php
namespace AllegedWizard\WPAdminOptions\Fields;

class ExampleJsonMediaOption extends AbstractAdminOption
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

            <button type="button" class="button" @click="addItem()" :disabled="items.length >= maxItems">Add Button</button>

            <div v-if="items.length >= maxItems">
                <i>Max items assigned.</i>
            </div>

            <hr>

            <div class="wao-items">

                <p v-if="!items.length">No digital buttons defined.</p>

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

                        <div class="wao-image-field">
                            <div v-if="'' === item.image_id">
                                No image selected.
                            </div>
                            <div v-else>
                                <img :src="item.image_url" width="150">
                            </div>
                            <div class="wao-action-group">
                                <button type="button" class="button" @click="openFrame(item)">Select Image</button>
                                <button type="button" class="button" v-if="'' !== item.image_id" @click="item.image_id = ''; item.image_url = '';">Remove Image</button>
                            </div>
                        </div>

                        <label class="wao-field">
                            <span class="wao-field-label">Text</span>
                            <input type="text" placeholder="Text" v-model="item.text" class="widefat">
                        </label>
                        <label class="wao-field">
                            <span class="wao-field-label">Alt Text</span>
                            <input type="text" placeholder="Alt Text" v-model="item.image_alt_text" class="widefat">
                        </label>
                        <label class="wao-field">
                            <span class="wao-field-label">Button Text</span>
                            <input type="text" placeholder="Button Text" v-model="item.button_text" class="widefat">
                        </label>
                        <label class="wao-field">
                            <span class="wao-field-label">URL</span>
                            <input type="text" placeholder="Button URL" v-model="item.url" class="widefat">
                        </label>
                    </div>

                </div>
            </div>
        </div>
        <?php
    }

    public function render_script() {
        wp_enqueue_media();
        $key = esc_attr( $this->args['key'] );
        ?>
        <script>window.addEventListener('load', function() {
          /**
           * Example: extend WPAdminOptions with a custom JSON + Media option type.
           * Uses the shared _dragMixin, _itemMixin, and _merge helpers from the global namespace,
           * and adds wp.media() integration for per-item image selection.
           */
          WPAdminOptions.ExampleJsonMediaOption = function (config) {
            var $ = jQuery;
            var frame;
            var opts = WPAdminOptions._merge(
              WPAdminOptions._dragMixin(),
              WPAdminOptions._itemMixin(null, 'items'),
              {
                data: function () {
                  return {
                    items: config.items,
                    selectedItem: null,
                    maxItems: 2
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
                      url: '',
                      text: '',
                      icon_id: '',
                      image_id: '',
                      image_url: '',
                      button_text: '',
                      image_alt_text: '',
                    });
                  },
                  openFrame: function (item) {
                    var self = this;
                    self.selectedItem = item;
                    if (frame) {
                      frame.open();
                      return;
                    }
                    frame = wp.media({
                      title: 'Select Media',
                      button: { text: 'Select' },
                      multiple: false
                    });
                    frame.on('select', function () {
                      var attachment = frame.state().get('selection').first().toJSON();
                      self.selectedItem.image_id = attachment.id;
                      self.selectedItem.image_url = attachment.url;
                    });
                    frame.open();
                  }
                },
                mounted: function () {
                  $('#' + config.key + ' .option-wrap').fadeIn();
                }
              }
            );
            Vue.createApp(opts).mount('#' + config.key);
          };
          <?php $args = [ 'key' => $key, 'items' => $this->args['value'] ]; ?>
          WPAdminOptions.ExampleJsonMediaOption(<?= json_encode( $args ); ?>);
        });</script>
        <?php
    }

}