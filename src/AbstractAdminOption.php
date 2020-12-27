<?php

namespace Zawntech\WPAdminOptions;

abstract class AbstractAdminOption
{
    protected $args = [

        // General
        'value' => '',
        'type' => 'text',
        'key' => '_option_key',
        'label' => 'Option Label',
        'css_classes' => ['widefat'],
        'description' => '',
        'default' => '',
        'readonly' => false,
        'help' => '',

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
    ];

    public function __construct( $args = [] ) {
        $this->args = wp_parse_args( $args, $this->args );
        if ( empty( $this->args['value'] ) && '' !== $this->args['default'] ) {
            $this->args['value'] = $args['default'];
        }
        $this->render();
    }

    public function render() {
        $key = esc_attr( $this->args['key'] );
        $type = esc_attr( $this->args['type'] );
        $value = esc_attr( $this->args['value'] );
        $readonly = esc_html( $this->args['readonly'] );
        $css_classes = esc_attr( trim( implode( ' ', $this->args['css_classes'] ) ) );
        $description = trim( $this->args['description'] );
        ?>
        <tr id="row-<?= $key; ?>">
            <?php $this->render_option_label(); ?>
            <td>
                <input type="<?= $type; ?>"
                       id="<?= $key; ?>"
                       name="<?= $key; ?>"
                       value="<?= $value; ?>"
                       <?php if ( $readonly ) : ?>readonly="readonly"<?php endif; ?>
                       class="<?= $css_classes; ?>">
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
    public function render_option_label() {
        $key = esc_attr( $this->args['key'] );
        $label = esc_attr( $this->args['label'] );
        $help = $this->args['help'];
        $help_text = sprintf( '<span class="help-text">%s</span>', $help );
        $help_icon = empty( $help ) ?: sprintf( ' <a href="#" class="help"><span class="icon">?</span> %s</a>', $help_text );
        ?>
        <th>
            <label for="<?= $key; ?>"><?= $label; ?><?= $help_icon; ?></label>
        </th>
        <?php

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