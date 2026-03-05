<?php
namespace AllegedWizard\WPAdminOptions\Fields;

class EditorOption extends AbstractAdminOption
{
    public function render_taxonomy_field() {
        $key = esc_attr( $this->args['key'] );
        $value = $this->args['value'];
        $description = trim( $this->args['description'] );
        ?>
        <div class="form-field" id="row-<?= $key; ?>">
            <?php $this->render_option_label( false ); ?>
            <?php wp_editor( $value, $key ); ?>
            <?php
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
        ?>
        <tr id="row-<?= $key; ?>">
            <?php $this->render_option_label(); ?>
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