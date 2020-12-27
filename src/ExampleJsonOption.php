<?php

namespace Zawntech\WPAdminOptions;

class ExampleJsonOption extends AbstractAdminOption
{
    public function render() {
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

            <button type="button" class="button" @click="addItem()">Add</button>

            <hr>

            <div class="items">

                <p v-if="!items.length">No example items are assigned.</p>

                <div v-for="item in items" class="item">

                    <div class="fields">
                        <input title="First Name" type="text" placeholder="Day" v-model="item.day" class="widefat">
                        <input title="Last Name" type="text" placeholder="Time" v-model="item.time" class="widefat">
                        <textarea title="Description" placeholder="Description" v-model="item.description" class="widefat"></textarea>
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
        $key = esc_attr( $this->args['key'] );
        ?>
        <script>
          jQuery(document).ready(function ($) {

            var app = new Vue({

              el: '#<?= $key; ?>',

              mounted: function () {
                $('#<?= $key; ?> .option-wrap').fadeIn();
              },

              data: function () {
                return {
                  items: <?= json_encode( $this->args['value'] ); ?>
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
                    first_name: '',
                    last_name: '',
                    description: '',
                  });
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