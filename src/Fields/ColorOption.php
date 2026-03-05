<?php

namespace AllegedWizard\WPAdminOptions\Fields;

class ColorOption extends AbstractAdminOption
{
    public function render_taxonomy_field() {
        $key = esc_attr( $this->args['key'] );
        $value = $this->args['value'];
        $description = trim( $this->args['description'] );
        $color_picker_type = $this->args['type'] ?? '';

        if ( 'swatches' === $color_picker_type ) {
            if ( empty( $this->args['color_swatches'] ) || ! is_array( $this->args['color_swatches'] ) ) {
                ?>
                <div class="form-field" id="row-<?= $key; ?>">
                    <?php $this->render_option_label( false ); ?>
                    <div class="wao-error">The "color_swatches" property is required and must be a non-empty array for the swatches color picker.</div>
                </div>
                <?php
                return;
            }
            ?>
            <div class="form-field" id="row-<?= $key; ?>">
                <?php $this->render_option_label( false ); ?>
                <div id="wao-swatches-<?= $key; ?>" class="wao-swatches-wrap"></div>
                <input type="hidden" name="<?= $key; ?>" value="<?= esc_attr( $value ); ?>">
                <?php
                if ( !empty( $description ) ) {
                    printf( '<p>%s</p>', $description );
                }
                ?>
            </div>
            <?php
        } else {
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
        }

        $this->scripts();
    }

    public function render_admin_table() {
        $key = esc_attr( $this->args['key'] );
        $value = $this->args['value'];
        $description = trim( $this->args['description'] );
        $color_picker_type = $this->args['type'] ?? '';

        if ( 'swatches' === $color_picker_type ) {
            if ( empty( $this->args['color_swatches'] ) || ! is_array( $this->args['color_swatches'] ) ) {
                ?>
                <tr id="row-<?= $key; ?>">
                    <?php $this->render_option_label(); ?>
                    <td>
                        <div class="wao-error">The "color_swatches" property is required and must be a non-empty array for the swatches color picker.</div>
                    </td>
                </tr>
                <?php
                return;
            }
            ?>
            <tr id="row-<?= $key; ?>">
                <?php $this->render_option_label(); ?>
                <td>
                    <div id="wao-swatches-<?= $key; ?>" class="wao-swatches-wrap"></div>
                    <input type="hidden" name="<?= $key; ?>" value="<?= esc_attr( $value ); ?>">
                    <?php
                    if ( !empty( $description ) ) {
                        printf( '<p><code>%s</code></p>', $description );
                    }
                    ?>
                </td>
            </tr>
            <?php
        } else {
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
        }

        $this->scripts();
    }

    public function scripts() {
        $color_picker_type = $this->args['type'] ?? '';

        if ( 'swatches' === $color_picker_type ) {
            // No external libraries needed for swatches.
        } elseif ( 'spectrum' === $color_picker_type ) {
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
            $args = [ 'key' => $key, 'type' => $color_picker_type ];

            if ( 'swatches' === $color_picker_type ) {
                $args['swatches'] = $this->args['color_swatches'];
                $args['value'] = $this->args['value'];
            }
            ?>
            <script>window.addEventListener('load', function() {
                WPAdminOptions.ColorOption(<?= json_encode( $args ); ?>);
            });</script>
            <?php
        } );
    }
}
