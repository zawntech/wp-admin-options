<?php

namespace Zawntech\WPAdminOptions;

class ExampleJsonOption extends AbstractAdminOption
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
          jQuery(document).ready(function ($) {

            var app = Vue.createApp({

              mounted: function () {
                $('#<?= $key; ?> .option-wrap').fadeIn();
              },

              data: function () {
                return {
                  items: <?= json_encode( $this->args['value'] ); ?>,
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