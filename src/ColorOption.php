<?php

namespace Zawntech\WPAdminOptions;

class ColorOption extends AbstractAdminOption
{
    public function render() {
        $key = esc_attr( $this->args['key'] );
        $value = $this->args['value'];
        $label = esc_html( $this->args['label'] );
        $description = trim( $this->args['description'] );
        ?>
        <tr id="row-<?= $key; ?>">
            <th>
                <label for="<?= $key; ?>"><?= $label; ?></label>
            </th>
            <td>
                <input type="text" name="<?= $key; ?>" value="<?= esc_attr( $value ); ?>">
                <?php

                ?>
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
}