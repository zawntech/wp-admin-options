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
        add_action( 'admin_footer', [$this, 'render_style'] );
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

            <div class="items">

                <p v-if="!items.length">No digital buttons defined.</p>

                <div v-for="item in items" class="item">

                    <div class="fields">

                        <div class="image">
                            <div v-if="'' === item.image_id">
                                No image selected.
                            </div>
                            <div v-else>
                                <img :src="item.image_url" width="150">
                            </div>
                            <button type="button" class="button" @click="openFrame(item)">Select Image</button>
                            <button type="button" class="button" v-if="'' !== item.image_id">Remove Image</button>
                        </div>

                        <input title="Text" type="text" placeholder="Text" v-model="item.text" class="widefat">
                        <input title="Alt Text" type="text" placeholder="Alt Text" v-model="item.image_alt_text" class="widefat">
                        <input title="Button text" type="text" placeholder="Button Text" v-model="item.button_text" class="widefat">
                        <input title="URL" type="text" placeholder="Button URL" v-model="item.url" class="widefat">
                    </div>

                    <div class="controls">
                        <button type="button" class="button" :disabled="!canMoveUp(item)" @click="moveUp(item)">&#x25B2;</button>
                        <button type="button" class="button" :disabled="!canMoveDown(item)" @click="moveDown(item)">&#x25BC;</button>
                        <button type="button" class="button" @click="removeItem(item)">×</button>
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

            var app = new Vue({

              el: '#<?= $key; ?>',

              mounted: function () {
                $('#<?= $key; ?> .option-wrap').fadeIn();
              },

              data: function () {
                return {
                  items: <?= json_encode( $this->args['value'] ); ?>,
                  selectedItem: null,
                  maxItems: 2
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
              }
            });
          });
        </script>
        <?php
    }

    public function render_style() {
        $key = esc_attr( $this->args['key'] );
        ?>
        <style>
            #<?= $key; ?> .items .item {
                margin-bottom: 5px;
                padding: 5px;
                border: 1px dashed #D2D2D2;
            }

            #<?= $key; ?> .item .controls {
                padding-top: 5px;
                text-align: right;
            }

            #<?= $key; ?> .option-wrap {
                display: none;
            }
        </style>
        <?php
    }
}