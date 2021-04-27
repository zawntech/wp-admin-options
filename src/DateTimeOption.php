<?php

namespace Zawntech\WPAdminOptions;

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
        add_action( 'admin_footer', [$this, 'render_style'] );
        add_action( 'admin_footer', [$this, 'render_script'] );
    }

    public function render_html() {
        ?>
        <div class="datetime-option">
            <input type="date" v-model="date" required>
            <input type="time" v-model="time" required>
        </div>
        <?php
    }

    public function render_script() {
        $key = esc_attr( $this->args['key'] );
        $value = trim( $this->args['value'] );
        $time = '';
        if ( !empty( $value ) ) {
            $date = date( 'Y-m-d', strtotime( $value ) );
            $time = date( 'H:i:s', strtotime( $value ) );
        }
        ?>
        <script>
          jQuery(document).ready(function ($) {

            var app = new Vue({

              el: '#<?= $key; ?>',

              mounted: function () {
                $('#<?= $key; ?> .option-wrap').fadeIn();
              },

              data: function () {
                return {
                  date: <?= json_encode( $date ); ?>,
                  time: <?= json_encode( $time ); ?>,
                }
              },

              computed: {
                json: function () {
                  var timeString = this.date + ' ' + this.time;
                  return timeString.trim();
                }
              },

              methods: {
              }
            });
          });
        </script>
        <?php
    }

    public function render_style() {
        ?>
        <style>
            .datetime-option {
                display: flex;
            }
        </style>
        <?php
    }
}