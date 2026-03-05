<?php

namespace Zawntech\WPAdminOptions;

/**
 * A checkbox option that stores 1 or 0.
 *
 * Class BooleanCheckboxOption
 * @package Zawntech\WPAdminOptions
 */
class BooleanCheckboxOption extends AbstractAdminOption
{
    public function render_taxonomy_field() {
        $key = esc_attr( $this->args['key'] );
        $value = $this->args['value'];
        $description = trim( $this->args['description'] );
        ?>
        <div class="form-field" id="row-<?= $key; ?>">
            <?php $this->render_option_label( false ); ?>
            <input type="checkbox" id="<?= $key; ?>" class="wao-toggle" <?= $value ? 'checked="checked"' : ''; ?>>
            <input type="hidden" name="<?= $key; ?>" value="<?= $value ? 1 : 0; ?>">
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
                <input type="checkbox" id="<?= $key; ?>" class="wao-toggle" <?= $value ? 'checked="checked"' : ''; ?>>
                <input type="hidden" name="<?= $key; ?>" value="<?= $value ? 1 : 0; ?>">
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
        add_action( 'admin_footer', function() {
            $key = esc_attr( $this->args['key'] );
            ?>
            <script>window.addEventListener('load', function() {
                <?php $args = [ 'key' => $key ]; ?>
                WPAdminOptions.BooleanCheckboxOption(<?= json_encode( $args ); ?>);
            });</script>
            <?php
        } );
    }
}