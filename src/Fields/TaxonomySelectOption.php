<?php

namespace AllegedWizard\WPAdminOptions\Fields;

class TaxonomySelectOption extends AbstractAdminOption
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

        $options = [
            '' => 'Select ' . esc_attr( $this->get_taxonomy_label() ) . '...'
        ];

        $terms = get_terms([
            'taxonomy' => $this->args['taxonomy'],
            'hide_empty' => false,
        ]);

        foreach( $terms as $term ) {
            $options[$term->term_id] = $term->name;
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
            <td id="<?= $key; ?>-wrap">
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
                    <?php
                    $args = [
                        'key' => $key,
                        'mode' => 'single',
                    ];
                    ?>
                    WPAdminOptions.TaxonomySelectOption(<?= json_encode( $args ); ?>);
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
                <div class="wao-items">
                    <p v-if="!items.length">
                        No <?= strtolower( $this->get_taxonomy_label('plural') ); ?> have been selected.
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
            <?php
            $args = [
                'key' => $key,
                'mode' => 'multiple',
                'items' => $this->get_args()['value'],
                'options' => $this->get_args()['options'],
                'adminUrl' => admin_url(),
                'homeUrl' => home_url(),
                'taxonomy' => $this->args['taxonomy']
            ];
            ?>
            WPAdminOptions.TaxonomySelectOption(<?= json_encode( $args ); ?>);
        });</script>
        <?php
    }

    public function get_taxonomy_label( $type = 'singular' ) {
        $taxonomy = get_taxonomy( $this->args['taxonomy'] );
        $labels = get_taxonomy_labels( $taxonomy );
        switch ( $type ) {
            case 'singular':
                return $labels->singular_name;
            case 'plural':
                return $labels->name;
        }
        return $labels;
    }
}