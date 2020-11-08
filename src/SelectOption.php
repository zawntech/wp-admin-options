<?php
namespace Zawntech\WPAdminOptions;

class SelectOption extends AbstractAdminOption
{
    public function render() {
        $value = $this->args['value'];
        $options = $this->args['options'];
        $key = esc_attr( $this->args['key'] );
        $label = esc_html( $this->args['label'] );
        $description = trim( $this->args['description'] );
        $css_classes = esc_attr( trim( implode( ' ', $this->args['css_classes'] ) ) );
        ?>
        <tr>
            <th>
                <label for="<?= $key; ?>"><?= $label; ?></label>
            </th>
            <td>
                <select
                    id="<?= $key; ?>"
                    name="<?= $key; ?>"
                    class="<?= $css_classes; ?> select2">
                    <?php
                    foreach( $options as $_value => $label ) {
                        $selected = $value == $_value ? ' selected="selected"' : '';
                        $_value = esc_attr( $_value );
                        $label = esc_html( $label );
                        printf( '<option value="%s"%s>%s</option>', $_value, $selected, $label );
                    }
                    ?>
                </select>
                <?php
                if ( ! empty( $description ) ) {
                    printf( '<p><code>%s</code></p>', $description );
                }
                ?>
                <script>
                  jQuery(document).ready(function ($) {
                    $('.select2').select2();
                  })
                </script>
            </td>
        </tr>
        <?php
    }
}