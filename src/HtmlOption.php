<?php
namespace Zawntech\WPAdminOptions;

class HtmlOption extends AbstractAdminOption
{
    public function render_admin_table() {
        $key = esc_attr( $this->args['key'] );
        $value = $this->args['value'];
        $description = trim( $this->args['description'] );
        do_action( 'before_admin_option', $key );
        ?>
        <tr id="row-<?= $key; ?>">
            <?php
            if ( !empty( $this->args['label'] ) ) {
                $this->render_option_label();
            }
            ?>
            <td style="padding-left: 0;">
                <?php
                echo $value;
                if ( !empty( $description ) ) {
                    printf( '<p><code>%s</code></p>', $description );
                }
                ?>
            </td>
        </tr>
        <?php
        do_action( 'after_admin_option', $key );
    }

}