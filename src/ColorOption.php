<?php

namespace Zawntech\WPAdminOptions;

class ColorOption extends AbstractAdminOption
{
    public function render_admin_table() {
        $key = esc_attr( $this->args['key'] );
        $value = $this->args['value'];
        $description = trim( $this->args['description'] );
        ?>
        <tr id="row-<?= $key; ?>">
            <?php $this->render_option_label(); ?>
            <td>
                <input type="text" name="<?= $key; ?>" value="<?= esc_attr( $value ); ?>">
                <?php
                if ( !empty( $description ) ) {
                    printf( '<p><code>%s</code></p>', $description );
                }
                ?>
            </td>
        </tr>
        <?php

        $this->scripts();
    }

    public function scripts() {

        $color_picker_type = $this->args['type'] ?? '';

        if ( empty( $color_picker_type ) || 'default' === $color_picker_type ) {
            $this->default_color_picker_type();
        }

        if ( 'spectrum' === $color_picker_type ) {
            $this->color_picker_type_spectrum();
        }
    }

    public function color_picker_type_default() {
        // Enqueue color picker CSS and JS
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );

        // Bind color picker.
        add_action( 'admin_footer', function() {
            $key = esc_attr( $this->args['key'] );
            ?>
            <script>
              jQuery(document).ready(function ($) {
                $('input[name="<?= $key; ?>"]').wpColorPicker();
              });
            </script>
            <?php
        } );
    }

    public function color_picker_type_spectrum() {

        wp_register_script( 'color-picker-spectrum', 'https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.1/spectrum.min.js' );
        wp_register_style( 'color-picker-spectrum', 'https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.1/spectrum.min.css' );

        wp_enqueue_style( 'color-picker-spectrum' );
        wp_enqueue_script( 'color-picker-spectrum' );

        // Bind color picker.
        add_action( 'admin_footer', function() {
            $key = esc_attr( $this->args['key'] );
            ?>
            <script>
              jQuery(document).ready(function ($) {
                $('input[name="<?= $key; ?>"]').spectrum({
                  showInput: true,
                  showAlpha: true,
                  preferredFormat: 'hex',
                  allowEmpty: true,
                });
              });
            </script>
            <?php
        } );
    }
}