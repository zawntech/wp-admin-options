<?php
namespace Zawntech\WPAdminOptions;

class TextareaOption extends AbstractAdminOption
{
    public function render() {
        $readonly = $this->args['readonly'];
        $key = esc_attr( $this->args['key'] );
        $value = esc_textarea( $this->args['value'] );
        $label = esc_html( $this->args['label'] );
        $css_classes = esc_attr( trim( implode( ' ', $this->args['css_classes'] ) ) );
        $description = trim( $this->args['description'] );
        $rows = esc_attr( $this->args['rows'] );
        ?>
        <tr>
            <th>
                <label for="<?= $key; ?>"><?= $label; ?></label>
            </th>
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