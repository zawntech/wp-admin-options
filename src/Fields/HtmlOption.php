<?php
namespace AllegedWizard\WPAdminOptions\Fields;

class HtmlOption extends AbstractAdminOption
{
    public function render_taxonomy_field() {
        $key = esc_attr( $this->args['key'] );
        $value = $this->args['value'];
        $description = trim( $this->args['description'] );
        ?>
        <div class="form-field" id="row-<?= $key; ?>">
            <?php
            if ( !empty( $this->args['label'] ) ) {
                $this->render_option_label( false );
            }
            echo $value;
            if ( !empty( $description ) ) {
                printf( '<p>%s</p>', $description );
            }
            ?>
        </div>
        <?php
    }

    public function render_admin_table() {
        $key = esc_attr( $this->args['key'] );
        $value = $this->args['value'];
        $description = trim( $this->args['description'] );
        $td_class = empty( $this->args['label'] ) ? ' class="wao-pl-0"' : '';
        ?>
        <tr id="row-<?= $key; ?>">
            <?php
            if ( !empty( $this->args['label'] ) ) {
                $this->render_option_label();
            }
            ?>
            <td<?= $td_class; ?>>
                <?php
                echo $value;
                if ( !empty( $description ) ) {
                    printf( '<p><code>%s</code></p>', $description );
                }
                ?>
            </td>
        </tr>
        <?php
    }
}