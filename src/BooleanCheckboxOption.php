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
                <input type="checkbox" id="<?= $key; ?>" <?= $value ? 'checked="checked"' : ''; ?>>
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
            <script>
              jQuery(document).ready(function ($) {
                $('input#<?= $key; ?>').on('change', function () {
                  var checked = $(this).is(':checked');
                  $('input[name="<?= $key; ?>"]').val(checked ? 1 : 0);
                });
              });
            </script>
            <?php
        } );
    }
}