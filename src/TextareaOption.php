<?php
namespace Zawntech\WPAdminOptions;

class TextareaOption extends AbstractAdminOption
{
    public function render_admin_table() {
        $readonly = $this->args['readonly'];
        $key = esc_attr( $this->args['key'] );
        $value = esc_textarea( $this->args['value'] );
        $css_classes = esc_attr( trim( implode( ' ', $this->args['css_classes'] ) ) );
        $description = trim( $this->args['description'] );
        $rows = esc_attr( $this->args['rows'] );
        ?>
        <tr id="row-<?= $key; ?>">
            <?php $this->render_option_label(); ?>
            <td>
                <textarea
                    id="<?= $key; ?>"
                    name="<?= $key; ?>"
                    rows="<?= $rows; ?>"
                    value="<?= $value; ?>"
                    class="<?= $css_classes; ?>"
                    <?php if ( $readonly ) : ?>readonly="readonly"<?php endif; ?>
                ><?= $value; ?></textarea>
                <?php
                if ( ! empty( $description ) ) {
                    printf( '<p><code>%s</code></p>', $description );
                }
                ?>
            </td>
        </tr>
        <?php
    }
}