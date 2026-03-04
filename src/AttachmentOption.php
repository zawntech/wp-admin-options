<?php
namespace Zawntech\WPAdminOptions;

class AttachmentOption extends AbstractAdminOption
{
    protected function render_attachment_content() {
        $key = esc_attr( $this->args['key'] );
        $description = trim( $this->args['description'] );
        $multiple = $this->args['multiple'];
        ?>

        <div class="wao-action-group">
            <button type="button" class="button" @click="openFrame()">Select Media</button>
            <button type="button" class="button" @click="clear()">Clear</button>
        </div>

        <hr>

        <p v-if="!ids.length">
            No <?= $multiple ? 'attachments are' : 'attachment is'; ?> assigned.
        </p>

        <div v-if="ids.length">

            <div v-for="(item, i) in media" class="wao-attachment-item"
                 :class="{'wao-dragging': dragIndex === i, 'wao-dragover': dragOverIndex === i}"
                 draggable="true"
                 @dragstart="dragStart(i, $event)"
                 @dragover.prevent="dragOver(i)"
                 @drop="drop(i)"
                 @dragend="dragEnd">

                <?php if ( $multiple ) : ?>
                <div class="wao-item-row">
                    <span class="wao-drag-handle" title="Drag to reorder">&#x2630;</span>
                    <div class="wao-controls">
                        <button type="button" class="button" :disabled="!canMoveUp(item.id)" @click="moveUp(item.id)">&#x25B2;</button>
                        <button type="button" class="button" :disabled="!canMoveDown(item.id)" @click="moveDown(item.id)">&#x25BC;</button>
                        <button type="button" class="button" @click="removeItem(item.id)">&times;</button>
                    </div>
                </div>
                <?php endif; ?>

                <div v-if="'Image' == getType(item)">
                    <img :src="item.url">
                </div>

                <div v-if="'Video' == getType(item)">
                    <video controls>
                        <source :src="item.url">
                    </video>
                </div>

                <div v-if="'Other' == getType(item)">
                    <a :href="item.url" target="_blank">{{ item.title }}</a>
                </div>

                <span class="wao-type-badge">
                    <a :href="'<?= admin_url( 'upload.php?item=' ); ?>' + item.id" target="_blank">
                        {{ getType(item) }}
                    </a>
                </span>

            </div>

        </div>

        <input type="hidden" :value="json" name="<?= $key; ?>">

        <?php
        if ( !empty( $description ) ) {
            printf( '<p><code>%s</code></p>', $description );
        }
    }

    public function render_taxonomy_field() {
        wp_enqueue_media();
        $key = esc_attr( $this->args['key'] );
        ?>
        <div class="form-field term-slug-wrap">
            <?php $this->render_option_label(false); ?>
            <div id="<?= $key; ?>">
                <?php $this->render_attachment_content(); ?>
            </div>
        </div>
        <?php
        add_action( 'admin_footer', [$this, 'render_style'] );
        add_action( 'admin_footer', [$this, 'render_script'] );
    }

    public function render_admin_table() {
        wp_enqueue_media();
        $key = esc_attr( $this->args['key'] );
        ?>
        <tr id="<?= $key; ?>">
            <?php $this->render_option_label(); ?>
            <td>
                <?php $this->render_attachment_content(); ?>
            </td>
        </tr>
        <?php
        add_action( 'admin_footer', [$this, 'render_style'] );
        add_action( 'admin_footer', [$this, 'render_script'] );
    }

    public function render_script() {
        $key = esc_attr( $this->args['key'] );
        $multiple = esc_attr( $this->args['multiple'] );
        $media_types = $this->args['media_types'];

        $data = [];
        $ids = $this->args['value'];
        $ids = array_map( function( $id ) {
            return (int) $id;
        }, $ids );
        if ( is_array( $ids ) ) {
            foreach( $ids as $id ) {
                $data[] = wp_prepare_attachment_for_js( $id );
            }
        }

        ?>
        <script>
          jQuery(document).ready(function ($) {

            var frame;

            var app = Vue.createApp({

              data: function () {
                return {
                  ids: <?= json_encode( $ids ); ?>,
                  data: <?= json_encode( $data ); ?>,
                  dragIndex: null,
                  dragOverIndex: null,
                }
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
                    title: <?= json_encode( $this->args['label'] ); ?>,
                    button: { text: 'Select' },
                    multiple: <?= $multiple ? 'true' : 'false'; ?>,
                    library: {
                      type: <?= json_encode( $media_types ); ?>
                    }
                  })
                  .on('select', this.selectItems);
                  frame.open();
                },

                selectItems: function () {
                  <?php if ( ! $multiple ) : ?>
                  this.clear();
                  <?php endif; ?>
                  var attachments = frame.state().get('selection').toJSON();
                  for (var i in attachments) {
                    var id = Number(attachments[ i ].id);
                    if (-1 !== this.ids.indexOf(id)) {
                      continue;
                    }
                    this.ids.push(id);
                    this.data.push(attachments[ i ]);
                  }
                  frame.close();
                },

                removeItem: function (id) {
                  id = Number(id);
                  this.ids.splice(this.ids.indexOf(id), 1);
                },

                canMoveUp: function (id) {
                  id = Number(id);
                  var index = this.ids.indexOf(id);
                  return index > 0;
                },

                canMoveDown: function (id) {
                  id = Number(id);
                  var index = this.ids.indexOf(id);
                  return index < this.ids.length - 1;
                },

                moveUp: function (id) {
                  id = Number(id);
                  var index = this.ids.indexOf(id);
                  if (this.canMoveUp(id)) {
                    var prev = this.ids[ index - 1 ];
                    this.ids.splice(index - 1, 2, id, prev);
                  }
                },

                moveDown: function (id) {
                  id = Number(id);
                  var index = this.ids.indexOf(id);
                  if (this.canMoveDown(id)) {
                    var next = this.ids[ index + 1 ];
                    this.ids.splice(index, 2, next, id);
                  }
                },

                getType: function (item) {
                  switch (item.type) {
                    case 'image':
                      return 'Image';
                      break;
                    case 'video':
                      return 'Video';
                      break;
                    default:
                      //return item.type;
                      return 'Other';
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
                  var id = this.ids.splice(this.dragIndex, 1)[0];
                  this.ids.splice(index, 0, id);
                  this.dragIndex = null;
                  this.dragOverIndex = null;
                },
                dragEnd: function () {
                  this.dragIndex = null;
                  this.dragOverIndex = null;
                },
              },

              computed: {
                json: function () {
                  return JSON.stringify(this.ids);
                },

                media: function () {
                  var data = this.data;
                  return this.ids.map(function (id) {
                    for (var i in data) {
                      if (data[ i ] && id == data[ i ].id) {
                        return data[ i ];
                      }
                    }
                  }).filter(function (item) {
                    return item;
                  });
                }
              }
            }).mount('#<?= $key; ?>');
          });
        </script>
        <?php
    }

    public function render_style() {
        $color = $this->args['bg_color'];
        if ( '#FFFFFF' !== strtoupper( $color ) ) : ?>
        <style>
            #<?= esc_attr( $this->args['key'] ); ?> .wao-attachment-item {
                background-color: <?= esc_attr( $color ); ?>;
            }
        </style>
        <?php endif;
    }
}
