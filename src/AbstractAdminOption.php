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
        $label = esc_html( $this->args['label'] );
        $readonly = esc_html( $this->args['readonly'] );
        $css_classes = esc_attr( trim( implode( ' ', $this->args['css_classes'] ) ) );
        $description = trim( $this->args['description'] );
        ?>
        <tr id="row-<?= $key; ?>">
            <th>
                <label for="<?= $key; ?>"><?= $label; ?></label>
            </th>
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
}