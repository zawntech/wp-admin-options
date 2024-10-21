<?php

namespace Zawntech\WPAdminOptions;

abstract class AbstractAdminOption
{
    protected $args = [

        // General
        'context' => 'admin-table',
        'value' => '',
        'type' => 'text',
        'key' => '_option_key',
        'label' => 'Option Label',
        'css_classes' => ['widefat'],
        'description' => '',
        'default' => '',
        'readonly' => false,
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
        $this->args = wp_parse_args( $args, $this->args );
        if ( empty( $this->args['value'] ) && '' !== $this->args['default'] ) {
            $this->args['value'] = $args['default'];
        }
        $this->render();
    }

    public function render() {
        switch ( $this->args['context'] ) {
            case 'admin-table':
                $this->render_admin_table();
                break;

            case 'taxonomy':
                $this->render_taxonomy_field();
                break;
        }
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
        if ( !empty( $min ) ) {
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

    public function render_taxonomy_field() {
        $key = esc_attr( $this->args['key'] );
        $description = trim( $this->args['description'] );
        $input_attributes = $this->prepare_input_attributes();
        do_action( 'before_admin_option', $key );
        ?>
        <div class="form-field" id="row-<?= $key; ?>">
            <?php $this->render_option_label( false ); ?>
            <?php
            printf( '<input %s>', $input_attributes );
            if ( !empty( $description ) ) {
                printf( '%s', $description );
            }
            ?>
        </div>
        <?php
        do_action( 'after_admin_option', $key );
    }

    public function render_admin_table() {
        $key = esc_attr( $this->args['key'] );
        $description = trim( $this->args['description'] );
        $input_attributes = $this->prepare_input_attributes();
        do_action( 'before_admin_option', $key );
        ?>
        <tr id="row-<?= $key; ?>">
            <?php $this->render_option_label(); ?>
            <td>
                <?php
                printf( '<input %s>', $input_attributes );
                if ( !empty( $description ) ) {
                    printf( '<p><code>%s</code></p>', $description );
                }
                ?>
            </td>
        </tr>
        <?php
        do_action( 'after_admin_option', $key );
    }

    /**
     * Render the <th>...</th> option label HTML.
     */
    public function render_option_label( $table = true ) {
        $key = esc_attr( $this->args['key'] );
        $label = esc_attr( $this->args['label'] );
        $help = $this->args['help'];
        $help_text = sprintf( '<span class="help-text">%s</span>', $help );
        $help_icon = empty( $help ) ? '' : sprintf( ' <a href="#" class="help"><span class="icon">?</span> %s</a>', $help_text );
        echo $table ? '<th>' : '';
        ?>
        <label for="<?= $key; ?>"><?= $label; ?><?= $help_icon; ?></label>
        <?php
        echo $table ? '</th>' : '';

        // Inject tooltip CSS.
        add_action( 'admin_footer', [$this, 'maybe_add_tooltip_assets'] );
    }

    protected static $tooltip_assets_loaded = false;

    public function maybe_add_tooltip_assets() {
        if ( static::$tooltip_assets_loaded ) {
            return;
        }
        $this->render_tooltip_assets();
        static::$tooltip_assets_loaded = true;
    }

    /**
     * Tool tip CSS.
     */
    protected function render_tooltip_assets() {
        ?>
        <style>
            .help {
                top: 3px;
                left: 5px;
                width: 17px;
                height: 17px;
                color: white;
                border-radius: 50%;
                position: relative;
                display: inline-block;
                text-decoration: none;
                background-color: #565656;
            }

            .help .help-text {
                display: none;
                padding: 10px;
                font-size: 12px;
                margin-top: 24px;
                max-width: 320px;
                width: max-content;
                position: absolute;
                background-color: #4e4e4e;
                -webkit-box-shadow: 10px 10px 26px 0px rgba(0, 0, 0, 0.35);
                -moz-box-shadow: 10px 10px 26px 0px rgba(0, 0, 0, 0.35);
                box-shadow: 10px 10px 26px 0px rgba(0, 0, 0, 0.35);
                border: 1px solid #454545;
                z-index: 999999;
            }

            .help:hover {
                color: white;
            }

            .help:hover .help-text {
                display: block;
            }

            .help .icon {
                left: 5px;
                top: -1px;
                font-weight: bold;
                position: absolute;
            }
        </style>
        <?php
    }
}
