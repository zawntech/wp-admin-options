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
            <td id="<?= $args['key']; ?>-wrap">
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
                <script>window.addEventListener('load', function() {
                    <?php $args = [ 'key' => $key, 'mode' => 'single' ]; ?>
                    WPAdminOptions.PostTypeSelectOption(<?= json_encode( $args ); ?>);
                });</script>
            </td>
        </tr>
        <?php
    }

    public function render_multiple() {
        if ( $this->render_array_error() ) return;
        $args = $this->get_args();
        $key = esc_attr( $args['key'] );

        ?>
        <tr id="row-<?= $key; ?>">
            <?php $this->render_option_label(); ?>
            <td id="<?= $args['key']; ?>-wrap" class="wao-vue-wrap">
                <div class="wao-post-control">
                    <select
                        id="<?= $args['key']; ?>"
                        name="<?= $args['key']; ?>"
                        class="<?= $args['css_classes']; ?> select2"
                        v-model="selectedPost">
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
                <div class="wao-items">
                    <p v-if="!items.length">
                        No <?= strtolower( $this->get_post_type_label('plural') ); ?> have been selected.
                    </p>
                    <div v-for="(item, i) in items" class="wao-item" :key="item"
                         :class="{'wao-dragging': dragIndex === i, 'wao-dragover': dragOverIndex === i}"
                         draggable="true"
                         @dragstart="dragStart(i, $event)"
                         @dragover.prevent="dragOver(i)"
                         @drop="drop(i)"
                         @dragend="dragEnd">
                        <div class="wao-item-row">
                            <span class="wao-drag-handle" title="Drag to reorder">&#x2630;</span>
                            <span class="wao-item-content" v-html="formatPostTitle(item, i)"></span>
                            <div class="wao-controls">
                                <button type="button" class="button" :disabled="!canMoveUp(item)" @click="moveUp(item)">&#x25B2;</button>
                                <button type="button" class="button" :disabled="!canMoveDown(item)" @click="moveDown(item)">&#x25BC;</button>
                                <button type="button" class="button" @click="removeItem(item)">×</button>
                            </div>
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
        <script>window.addEventListener('load', function() {
            <?php $args = [ 'key' => $key, 'mode' => 'multiple', 'items' => $this->get_args()['value'], 'options' => $this->get_args()['options'], 'adminUrl' => admin_url(), 'homeUrl' => home_url() ]; ?>
            WPAdminOptions.PostTypeSelectOption(<?= json_encode( $args ); ?>);
        });</script>
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
