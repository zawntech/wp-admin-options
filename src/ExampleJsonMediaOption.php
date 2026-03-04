<?php
namespace Zawntech\WPAdminOptions;

class ExampleJsonMediaOption extends AbstractAdminOption
{
    public function render_admin_table() {
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
                                <button type="button" class="button" v-if="'' !== item.image_id">Remove Image</button>
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
        <script>
          jQuery(document).ready(function ($) {

            var frame;

            var app = Vue.createApp({

              mounted: function () {
                $('#<?= $key; ?> .option-wrap').fadeIn();
              },

              data: function () {
                return {
                  items: <?= json_encode( $this->args['value'] ); ?>,
                  selectedItem: null,
                  maxItems: 2,
                  dragIndex: null,
                  dragOverIndex: null,
                }
              },

              computed: {

                json: function () {
                  return JSON.stringify(this.items);
                }
              },

              methods: {

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

                  // Create a new media frame
                  frame = wp.media({
                    title: 'Select Media',
                    button: {
                      text: 'Select'
                    },
                    multiple: false  // Set to true to allow multiple files to be selected
                  });

                  frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON(),
                      id = attachment.id,
                      url = attachment.url;
                    self.selectedItem.image_id = id;
                    self.selectedItem.image_url = url;
                  });

                  frame.open();
                },

                removeItem: function (item) {
                  this.items.splice(this.items.indexOf(item), 1);
                },

                canMoveUp: function (item) {
                  var index = this.items.indexOf(item);
                  return index > 0;
                },

                canMoveDown: function (item) {
                  var index = this.items.indexOf(item);
                  return index < this.items.length - 1;
                },

                moveUp: function (item) {
                  var index = this.items.indexOf(item);
                  if (this.canMoveUp(item)) {
                    var prev = this.items[ index - 1 ];
                    this.items.splice(index - 1, 2, item, prev);
                  }
                },

                moveDown: function (item) {
                  var index = this.items.indexOf(item);
                  if (this.canMoveDown(item)) {
                    var next = this.items[ index + 1 ];
                    this.items.splice(index, 2, next, item);
                  }
                },

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
                },
              }
            }).mount('#<?= $key; ?>');
          });
        </script>
        <?php
    }

}