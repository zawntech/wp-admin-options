<?php

namespace Zawntech\WPAdminOptions;

class SelectOption extends AbstractAdminOption
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

        if ( !empty( $this->option_args ) ) {
            return $this->option_args;
        }

        $value = $this->args['value'];
        $options = $this->args['options'];
        $key = esc_attr( $this->args['key'] );
        $label = esc_html( $this->args['label'] );
        $description = trim( $this->args['description'] );
        $css_classes = esc_attr( trim( implode( ' ', $this->args['css_classes'] ) ) );

        $this->option_args = [
            'value' => $value,
            'key' => $key,
            'label' => $label,
            'description' => $description,
            'css_classes' => $css_classes,
            'options' => $options,
        ];

        return $this->option_args;
    }

    public function render_single() {
        $value = $this->args['value'];
        $options = $this->args['options'];
        $key = esc_attr( $this->args['key'] );
        $description = trim( $this->args['description'] );
        $css_classes = esc_attr( trim( implode( ' ', $this->args['css_classes'] ) ) );
        ?>
        <tr>
            <?php $this->render_option_label(); ?>
            <td id="<?= $key; ?>-wrap">
                <select
                    id="<?= $key; ?>"
                    name="<?= $key; ?>"
                    class="<?= $css_classes; ?> select2">
                    <?php
                    foreach ( $options as $_value => $label ) {
                        $selected = $value == $_value ? ' selected="selected"' : '';
                        $_value = esc_attr( $_value );
                        $label = esc_html( $label );
                        printf( '<option value="%s"%s>%s</option>', $_value, $selected, $label );
                    }
                    ?>
                </select>
                <?php
                if ( !empty( $description ) ) {
                    printf( '<p><code>%s</code></p>', $description );
                }
                ?>
            </td>
        </tr>
        <?php
        $this->maybe_trigger_select2();
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
                        No items have been selected.
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
                            <span class="wao-item-content" v-html="formatPostTitle(item)"></span>
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
        <script>(function() {
            <?php $args = [ 'key' => $key, 'mode' => 'multiple', 'items' => $this->get_args()['value'], 'options' => $this->get_args()['options'] ]; ?>
            WPAdminOptions.SelectOption(<?= json_encode( $args ); ?>);
        })();</script>
        <?php
    }


    ////////
    protected function maybe_trigger_select2() {
        add_action( 'admin_footer', [$this, 'trigger_select2'] );
    }

    public function trigger_select2() {
        $key = $this->args['key'];
        ?>
        <script>(function() {
            <?php $args = [ 'key' => $key, 'mode' => 'single' ]; ?>
            WPAdminOptions.SelectOption(<?= json_encode( $args ); ?>);
        })();</script>
        <?php
    }
}