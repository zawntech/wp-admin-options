<?php

namespace Zawntech\WPAdminOptions;

class PostTypeSelectOption extends AbstractAdminOption
{
    protected $option_args = [];

    public function render_admin_table() {
        $multiple = $this->args['multiple'];
        if ( $multiple ) {
            $this->render_multiple();
        } else {
            $this->render_single();
        }
    }

    public function get_args() {

        if ( ! empty( $this->option_args ) ) {
            return $this->option_args;
        }

        $value = $this->args['value'];
        $key = esc_attr( $this->args['key'] );
        $label = esc_html( $this->args['label'] );
        $description = trim( $this->args['description'] );
        $css_classes = esc_attr( trim( implode( ' ', $this->args['css_classes'] ) ) );

        $post_args = wp_parse_args( [
            'post_type' => $this->args['post_type'],
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ] );

        $options = [
            '' => 'Select ' . esc_attr( $this->get_post_type_label() ) . '...'
        ];

        $query = new \WP_Query( $post_args );
        foreach ( $query->posts as $post ) {
            $options[$post->ID] = $post->post_title;
        }

        $this->option_args = [
            'value' => $value,
            'key' => $key,
            'label' => $label,
            'description' => $description,
            'css_classes' => $css_classes,
            'options' => $options
        ];

        return $this->option_args;
    }

    public function render_single() {
        $args = $this->get_args();
        $key = esc_attr( $args['key'] );
        ?>
        <tr id="row-<?= $key; ?>">
            <?php $this->render_option_label(); ?>
            <td>
                <select
                    id="<?= $args['key']; ?>"
                    name="<?= $args['key']; ?>"
                    class="<?= $args['css_classes']; ?> select2">
                    <?php
                    foreach ( $args['options'] as $_value => $label ) {
                        $selected = $args['value'] == $_value ? ' selected="selected"' : '';
                        $_value = esc_attr( $_value );
                        $label = esc_html( $label );
                        printf( '<option value="%s"%s>%s</option>', $_value, $selected, $label );
                    }
                    ?>
                </select>
                <?php
                if ( !empty( $args['description'] ) ) {
                    printf( '<p><code>%s</code></p>', $args['description'] );
                }
                ?>
                <script>
                  jQuery(document).ready(function ($) {
                    $('#<?= $key; ?>.select2').select2();
                  })
                </script>
            </td>
        </tr>
        <?php
    }

    public function render_multiple() {
        $args = $this->get_args();
        $key = esc_attr( $args['key'] );

        ?>
        <tr id="row-<?= $key; ?>">
            <?php $this->render_option_label(); ?>
            <td id="<?= $args['key']; ?>-wrap">
                <div class="post-control" style="display: flex; justify-content: space-between;">
                    <select
                        id="<?= $args['key']; ?>"
                        name="<?= $args['key']; ?>"
                        class="<?= $args['css_classes']; ?> select2">
                        <?php
                        foreach ( $args['options'] as $_value => $label ) {
                            $selected = $args['value'] == $_value ? ' selected="selected"' : '';
                            $_value = esc_attr( $_value );
                            $label = esc_html( $label );
                            printf( '<option value="%s"%s>%s</option>', $_value, $selected, $label );
                        }
                        ?>
                    </select>
                    <button type="button" class="button" @click="addItem()">Add</button>
                </div>
                <hr>
                <div class="items">
                    <p v-if="!items.length">
                        No <?= strtolower( $this->get_post_type_label('plural') ); ?> have been selected.
                    </p>
                    <div v-for="item, i in items" class="item" :key="item">

                        <span v-html="formatPostTitle(item, i)"></span>

                        <div class="controls">
                            <button type="button" class="button" :disabled="!canMoveUp(item)" @click="moveUp(item)">&#x25B2;</button>
                            <button type="button" class="button" :disabled="!canMoveDown(item)" @click="moveDown(item)">&#x25BC;</button>
                            <button type="button" class="button" @click="removeItem(item)">×</button>
                        </div>
                    </div>
                </div>
                <?php
                if ( !empty( $args['description'] ) ) {
                    printf( '<p><code>%s</code></p>', $args['description'] );
                }
                ?>
                <input type="hidden" name="<?= $args['key']; ?>" :value="json">
            </td>
        </tr>
        <?php
        add_action( 'admin_footer', [$this, 'render_scripts'] );
    }

    public function render_scripts() {
        $key = esc_attr( $this->args['key'] );

        ?>
        <script>

          console.log({key: <?= $key; ?>})
          jQuery(document).ready(function ($) {

            var app = new Vue({

              el: '#<?= $key; ?>-wrap',

              data: function () {
                return {
                  selectedPost: '',
                  items: <?= json_encode( $this->get_args()['value'] ); ?>,
                  posts: <?= json_encode( $this->get_args()['options'] ); ?>,
                }
              },

              computed: {
                json: function() {
                  return JSON.stringify(this.items);
                }
              },

              methods: {

                formatPostTitle: function (postId, index) {
                  var count = (index+1),
                    postTitle = this.posts[postId],
                    editPostUrl = '<?= admin_url(); ?>post.php?post=' + postId + '&action=edit',
                    editPostLink = '<a href="' + editPostUrl + '" target="_blank" class="link">[Edit]</a>',
                    viewPostUrl = '<?= home_url(); ?>?p=' + postId,
                    viewPostLink = '<a href="' + viewPostUrl + '" target="_blank" class="link">[View]</a>';
                  return [
                    '#' + count,
                    '-',
                    postTitle,
                    editPostLink,
                    viewPostLink
                  ].join(' ');
                },

                addItem: function () {
                  if ( '' === this.selectedPost ) {
                    alert('Please select a post to add.');
                    return;
                  }
                  if ( -1 === this.items.indexOf(this.selectedPost) ) {
                    this.items.push(this.selectedPost);
                  }
                  this.selectedPost = '';
                  $('#<?= $key; ?>.select2').val('').trigger('change');
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
              },

              mounted: function () {

                var select = $('#<?= $key; ?>.select2'),
                  self = this;
                $('#<?= $key; ?>-wrap').fadeIn(function () {
                  select.select2();
                  select.on('select2:select', function (e) {
                    self.selectedPost = select.val();
                  });
                });
              }
            });
          })
        </script>
        <style>
            #<?= $key; ?>-wrap {
                display: none;
            }
            #<?= $key; ?>-wrap .post-control {
                display: flex;
                justify-content: space-between;
            }

            #<?= $key; ?>-wrap .items .item {
                margin-bottom: 5px;
                padding: 5px;
                border: 1px dashed #D2D2D2;
            }

            #<?= $key; ?>-wrap .items .item .link {
                display: none;
            }

            #<?= $key; ?>-wrap .items .item:hover .link {
                display: inline;
            }

            #<?= $key; ?>-wrap .item .controls {
                padding-top: 5px;
                text-align: right;
            }

            #<?= $key; ?>-wrap .option-wrap {
                display: none;
            }
        </style>
        <?php
    }

    /**
     * Get the singular or plural post type label for this option.
     *
     * @param string $type
     * @return string
     */
    public function get_post_type_label( $type = 'singular' ) {
        $post_type = $this->args['post_type'];
        $post_type_object = get_post_type_object( $post_type );
        $post_label = 'singular' === $type ? 'post' : 'posts';
        if ( is_a( $post_type_object, \WP_Post_Type::class ) ) {
            if ( 'singular' === $type ) {
                $post_label = $post_type_object->labels->singular_name;
            }
            if ( 'plural' === $type ) {
                $post_label = $post_type_object->labels->name;
            }
        }
        return strtolower( $post_label );
    }
}