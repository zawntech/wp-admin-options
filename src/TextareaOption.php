<?php
namespace Zawntech\WPAdminOptions;

class TextareaOption extends AbstractAdminOption
{
    public function render_taxonomy_field() {
        $readonly = $this->args['readonly'];
        $key = esc_attr( $this->args['key'] );
        $value = esc_textarea( $this->args['value'] );
        $css_classes = esc_attr( trim( implode( ' ', $this->args['css_classes'] ) ) );
        $description = trim( $this->args['description'] );
        $rows = esc_attr( $this->args['rows'] );
        do_action( 'before_admin_option', $key );
        ?>
        <div class="form-field" id="row-<?= $key; ?>">
            <?php $this->render_option_label( false ); ?>
            <textarea
                id="<?= $key; ?>"
                name="<?= $key; ?>"
                rows="<?= $rows; ?>"
                value="<?= $value; ?>"
                class="<?= $css_classes; ?>"
                <?php if ( $readonly ) : ?>readonly="readonly"<?php endif; ?>
                ><?= $value; ?></textarea>
            <?php
            if ( !empty( $description ) ) {
                printf( '<p>%s</p>', $description );
            }
            ?>
        </div>
        <?php
        do_action( 'after_admin_option', $key );
    }

    public function render_admin_table() {
        $readonly = $this->args['readonly'];
        $key = esc_attr( $this->args['key'] );
        $value = esc_textarea( $this->args['value'] );
        $css_classes = esc_attr( trim( implode( ' ', $this->args['css_classes'] ) ) );
        $description = trim( $this->args['description'] );
        $rows = esc_attr( $this->args['rows'] );
        do_action( 'before_admin_option', $key );
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
        do_action( 'after_admin_option', $key );
    }
}
