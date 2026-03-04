<?php

namespace Zawntech\WPAdminOptions;

abstract class AbstractAdminOption
{
    protected static $initialized = false;

    protected $args = [

        // General
        'context' => 'admin-table',
        'value' => '',
        'type' => 'text',
        'key' => '_option_key',
        'label' => 'Option Label',
        'css_classes' => ['widefat', 'wao-input'],
        'description' => '',
        'default' => '',
        'readonly' => false,
        'enable_copy' => null,
        'help' => '',
        'placeholder' => '',

        // Number
        'step' => '',
        'min' => '',
        'max' => '',

        // Textarea
        'rows' => 4,

        // Select
        'options' => [],

        // Attachments
        'media_types' => ['image'],
        'multiple' => false,
        'bg_color' => '#FFFFFF',

        // PostTypeSelect
        'post_type' => ['post'],
        'query_args' => [],
        'select_post_text' => 'Select post...',

        // User select options
        'role' => [],
        'role__in' => [],
        'meta_key' => '',
        'meta_value' => '',
        'meta_compare' => '',
    ];

    public function __construct( $args = [] ) {
        if ( ! static::$initialized ) {
            Bootstrap\WPAdminOptions::init();
            static::$initialized = true;
        }

        $this->args = wp_parse_args( $args, $this->args );
        if ( empty( $this->args['value'] ) && '' !== $this->args['default'] ) {
            $this->args['value'] = $args['default'];
        }
        $this->render();
    }

    public function render() {
        $key = esc_attr( $this->args['key'] );
        do_action( 'before_admin_option', $key );
        switch ( $this->args['context'] ) {
            case 'admin-table':
                $this->render_admin_table();
                break;

            case 'taxonomy':
                $this->render_taxonomy_field();
                break;
        }
        do_action( 'after_admin_option', $key );
    }

    /**
     * Returns an array of key => value pairs as an HTML string.
     * @param array $array
     * @return string
     */
    protected function array_to_attributes( $array = [] ) {
        $attributes_strings = [];
        foreach ( $array as $key => $value ) {
            if ( !empty( $value ) || '0' === $value ) {
                $attributes_strings[] = sprintf( '%s="%s"', $key, esc_attr( $value ) );
            }
        }
        $attributes = implode( ' ', $attributes_strings );
        return $attributes;
    }

    public function prepare_input_attributes() {
        $key = esc_attr( $this->args['key'] );
        $min = esc_attr( $this->args['min'] );
        $max = esc_attr( $this->args['max'] );
        $step = esc_attr( $this->args['step'] );
        $type = esc_attr( $this->args['type'] );
        $value = esc_attr( $this->args['value'] );
        $placeholder = esc_attr( $this->args['placeholder'] );
        $readonly = esc_html( $this->args['readonly'] );
        $css_classes = esc_attr( trim( implode( ' ', $this->args['css_classes'] ) ) );

        // Prepare <input> tag attributes.
        $input_attributes = [];
        if ( !empty( $key ) ) {
            $input_attributes['id'] = $key;
            $input_attributes['name'] = $key;
        }
        if ( !empty( $type ) ) {
            $input_attributes['type'] = $type;
        }
        if ( !empty( $value ) || '0' === $value ) {
            $input_attributes['value'] = $value;
        }
        if ( !empty( $readonly ) ) {
            $input_attributes['readonly'] = 'readonly';
        }
        if ( !empty( $css_classes ) ) {
            $input_attributes['class'] = $css_classes;
        }
        if ( !empty( $min ) && '0' !== $min && 0 !== $min ) {
            $input_attributes['min'] = $min;
        }
        if ( !empty( $max ) ) {
            $input_attributes['max'] = $max;
        }
        if ( !empty( $step ) ) {
            $input_attributes['step'] = $step;
        }
        if ( !empty( $placeholder ) ) {
            $input_attributes['placeholder'] = $placeholder;
        }

        return $this->array_to_attributes( $input_attributes );
    }

    /**
     * Whether the copy button should be shown for this input.
     */
    protected function should_enable_copy() {
        $enable_copy = $this->args['enable_copy'];
        if ( null !== $enable_copy ) {
            return (bool) $enable_copy;
        }
        return ! empty( $this->args['readonly'] );
    }

    protected function render_copy_button( $key ) {
        ?>
        <button type="button" class="wao-copy-btn" data-copy-target="<?= $key; ?>" title="Copy to clipboard">
            <span class="wao-copy-icon">&#x2398;</span>
            <span class="wao-copy-done" style="display:none;">&#x2713;</span>
        </button>
        <script>
        (function(){
            var btn = document.querySelector('[data-copy-target="<?= $key; ?>"]');
            if (!btn) return;
            btn.addEventListener('click', function() {
                var input = document.getElementById('<?= $key; ?>');
                if (!input) return;
                navigator.clipboard.writeText(input.value).then(function() {
                    btn.querySelector('.wao-copy-icon').style.display = 'none';
                    btn.querySelector('.wao-copy-done').style.display = '';
                    setTimeout(function() {
                        btn.querySelector('.wao-copy-icon').style.display = '';
                        btn.querySelector('.wao-copy-done').style.display = 'none';
                    }, 1500);
                });
            });
        })();
        </script>
        <?php
    }

    public function render_taxonomy_field() {
        $key = esc_attr( $this->args['key'] );
        $description = trim( $this->args['description'] );
        $input_attributes = $this->prepare_input_attributes();
        $show_copy = $this->should_enable_copy();
        ?>
        <div class="form-field" id="row-<?= $key; ?>">
            <?php $this->render_option_label( false ); ?>
            <?php if ( $show_copy ) : ?>
            <div class="wao-copy-wrap">
                <?php printf( '<input %s>', $input_attributes ); ?>
                <?php $this->render_copy_button( $key ); ?>
            </div>
            <?php else : ?>
            <?php printf( '<input %s>', $input_attributes ); ?>
            <?php endif; ?>
            <?php
            if ( !empty( $description ) ) {
                printf( '%s', $description );
            }
            ?>
        </div>
        <?php
    }

    public function render_admin_table() {
        $key = esc_attr( $this->args['key'] );
        $description = trim( $this->args['description'] );
        $input_attributes = $this->prepare_input_attributes();
        $show_copy = $this->should_enable_copy();
        ?>
        <tr id="row-<?= $key; ?>">
            <?php $this->render_option_label(); ?>
            <td>
                <?php if ( $show_copy ) : ?>
                <div class="wao-copy-wrap">
                    <?php printf( '<input %s>', $input_attributes ); ?>
                    <?php $this->render_copy_button( $key ); ?>
                </div>
                <?php else : ?>
                <?php printf( '<input %s>', $input_attributes ); ?>
                <?php endif; ?>
                <?php
                if ( !empty( $description ) ) {
                    printf( '<p><code>%s</code></p>', $description );
                }
                ?>
            </td>
        </tr>
        <?php
    }

    /**
     * Render the <th>...</th> option label HTML.
     */
    public function render_option_label( $table = true ) {
        $key = esc_attr( $this->args['key'] );
        $label = esc_attr( $this->args['label'] );
        $help = $this->args['help'];
        $help_text = sprintf( '<span class="wao-help-text">%s</span>', $help );
        $help_icon = empty( $help ) ? '' : sprintf( ' <a href="#" class="wao-help">?%s</a>', $help_text );
        echo $table ? '<th>' : '';
        ?>
        <label for="<?= $key; ?>"><?= $label; ?><?= $help_icon; ?></label>
        <?php
        echo $table ? '</th>' : '';
    }
}
