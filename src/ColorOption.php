<?php

namespace Zawntech\WPAdminOptions;

class ColorOption extends AbstractAdminOption
{
    public function render_taxonomy_field() {
        $key = esc_attr( $this->args['key'] );
        $value = $this->args['value'];
        $description = trim( $this->args['description'] );
        ?>
        <div class="form-field" id="row-<?= $key; ?>">
            <?php $this->render_option_label( false ); ?>
            <input type="text" name="<?= $key; ?>" value="<?= esc_attr( $value ); ?>">
            <?php
            if ( !empty( $description ) ) {
                printf( '<p>%s</p>', $description );
            }
            ?>
        </div>
        <?php
        $this->scripts();
    }

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

        if ( 'spectrum' === $color_picker_type ) {
            wp_register_script( 'color-picker-spectrum', 'https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.1/spectrum.min.js' );
            wp_register_style( 'color-picker-spectrum', 'https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.1/spectrum.min.css' );
            wp_enqueue_style( 'color-picker-spectrum' );
            wp_enqueue_script( 'color-picker-spectrum' );
        } else {
            $color_picker_type = 'default';
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
        }

        add_action( 'admin_footer', function() use ( $color_picker_type ) {
            $key = esc_attr( $this->args['key'] );
            ?>
            <script>(function() {
                <?php $args = [ 'key' => $key, 'type' => $color_picker_type ]; ?>
                WPAdminOptions.ColorOption(<?= json_encode( $args ); ?>);
            })();</script>
            <?php
        } );
    }
}
