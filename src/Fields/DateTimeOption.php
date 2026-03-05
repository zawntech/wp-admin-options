<?php

namespace AllegedWizard\WPAdminOptions\Fields;

class DatetimeOption extends AbstractAdminOption
{
    public function render_admin_table() {
        $key = esc_attr( $this->args['key'] );
        ?>
        <tr id="<?= $key; ?>">
            <?php $this->render_option_label(); ?>
            <td>
                <?php $this->render_html(); ?>
                <input type="hidden" name="<?= $key; ?>" :value="json">
            </td>
        </tr>
        <?php
        add_action( 'admin_footer', [$this, 'render_script'] );
    }

    public function render_html() {
        ?>
        <div class="wao-datetime">
            <input type="date" v-model="date" required>
            <input type="time" v-model="time" required>
        </div>
        <?php
    }

    public function render_script() {
        $key = esc_attr( $this->args['key'] );
        $value = trim( $this->args['value'] );
        $date = '';
        $time = '';
        if ( !empty( $value ) ) {
            $date = date( 'Y-m-d', strtotime( $value ) );
            $time = date( 'H:i:s', strtotime( $value ) );
        }
        ?>
        <script>window.addEventListener('load', function() {
            <?php $args = [ 'key' => $key, 'date' => $date, 'time' => $time ]; ?>
            WPAdminOptions.DateTimeOption(<?= json_encode( $args ); ?>);
        });</script>
        <?php
    }

}