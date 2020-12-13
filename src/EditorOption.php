<?php
namespace Zawntech\WPAdminOptions;

class EditorOption extends AbstractAdminOption
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
                <?php
                wp_editor( $value, $key );
                ?>
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