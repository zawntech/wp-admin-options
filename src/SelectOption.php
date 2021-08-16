<?php
namespace Zawntech\WPAdminOptions;

class SelectOption extends AbstractAdminOption
{
    public function render_admin_table() {
        $value = $this->args['value'];
        $options = $this->args['options'];
        $key = esc_attr( $this->args['key'] );
        $description = trim( $this->args['description'] );
        $css_classes = esc_attr( trim( implode( ' ', $this->args['css_classes'] ) ) );
        ?>
        <tr>
            <?php $this->render_option_label(); ?>
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

            </td>
        </tr>
        <?php
        $this->maybe_trigger_select2();
    }

    ////////

    protected static $has_triggered_select2 = false;

    protected function maybe_trigger_select2() {
        if ( !static::$has_triggered_select2 ) {
            add_action( 'admin_footer', [$this, 'trigger_select2'] );
        }
    }

    public function trigger_select2() {
        ?>
        <script>
          jQuery(document).ready(function ($) {
            $('.select2').select2();
          })
        </script>
        <?php
    }
}