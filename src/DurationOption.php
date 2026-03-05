<?php

namespace Zawntech\WPAdminOptions;

class DurationOption extends AbstractAdminOption
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
        <div class="wao-duration">

            <template v-if="!custom">
                <label class="wao-duration-field">
                    <span class="wao-field-label">Hours</span>
                    <select v-model="hours">
                        <option v-for="v in options.hours" :value="v">{{ v }}</option>
                    </select>
                </label>

                <label class="wao-duration-field">
                    <span class="wao-field-label">Minutes</span>
                    <select v-model="minutes">
                        <option v-for="v in options.minutes" :value="v">{{ v }}</option>
                    </select>
                </label>
            </template>

            <template v-else>
                <label class="wao-duration-field">
                    <span class="wao-field-label">Hours</span>
                    <input type="number" v-model.number="hours" min="0" class="wao-input wao-duration-input">
                </label>

                <label class="wao-duration-field">
                    <span class="wao-field-label">Minutes</span>
                    <input type="number" v-model.number="minutes" min="0" max="59" class="wao-input wao-duration-input">
                </label>
            </template>

            <button type="button" class="button" @click="custom = !custom">{{ custom ? 'Presets' : 'Custom' }}</button>

        </div>
        <?php
    }

    public function render_script() {
        $key = esc_attr( $this->args['key'] );
        ?>
        <script>window.addEventListener('load', function() {
            <?php $args = [ 'key' => $key, 'value' => $this->args['value'] ]; ?>
            WPAdminOptions.DurationOption(<?= json_encode( $args ); ?>);
        });</script>
        <?php
    }

}