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
        add_action( 'admin_footer', [$this, 'render_style'] );
        add_action( 'admin_footer', [$this, 'render_script'] );
    }

    public function render_html() {
        ?>
        <div class="duration-option">

            <select v-model="hours">
                <option v-for="v in options.hours" :value="v">{{ v }} hour{{ 1 == v ? '' : 's'}}</option>
            </select>

            <select v-model="minutes">
                <option v-for="v in options.minutes" :value="v">{{ v }} minutes</option>
            </select>

        </div>
        <?php
    }

    public function render_script() {
        $key = esc_attr( $this->args['key'] );
        ?>
        <script>
          jQuery(document).ready(function ($) {

            var app = new Vue({

              el: '#<?= $key; ?>',

              mounted: function () {
                $('#<?= $key; ?> .option-wrap').fadeIn();
                let minutes = Number(<?= json_encode( $this->args['value'] ); ?>),
                  hours = Math.floor( minutes / 60 ),
                  mins = minutes % 60;
                this.hours = hours;
                this.minutes = mins;
              },

              data: function () {
                return {
                  minutes: 0,
                  hours: 0,
                  days: '',

                  options: {
                    minutes: [0, 15, 30, 45],
                    hours: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                  }
                }
              },

              computed: {

                json: function () {
                  return JSON.stringify(this.value);
                },

                value() {
                  let minutes = this.minutes,
                    hours = 60 * this.hours;
                  return minutes + hours;
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
            #<?= $key; ?> .items .item {
                margin-bottom: 5px;
                padding: 5px;
                border: 1px dashed #D2D2D2;
            }

            #<?= $key; ?> .item .controls {
                padding-top: 5px;
                text-align: right;
            }

            #<?= $key; ?> .option-wrap {
                display: none;
            }
        </style>
        <?php
    }
}