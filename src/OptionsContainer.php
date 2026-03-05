<?php

namespace Zawntech\WPAdminOptions;

class OptionsContainer
{
    protected $args = [
        'key'         => '',
        'title'       => '',
        'description' => '',
        'collapsed'   => false,
        'fields'      => null,
    ];

    public function __construct( $args = [] ) {
        $this->args = wp_parse_args( $args, $this->args );
        Bootstrap\WPAdminOptions::init();
        $this->render();
    }

    public function render() {
        $key = esc_attr( $this->args['key'] );
        $title = esc_html( $this->args['title'] );
        $description = $this->args['description'];
        $collapsed = $this->args['collapsed'];
        $fields = $this->args['fields'];

        // Capture field HTML via output buffering.
        // Fields should be a callable that instantiates option classes.
        $fields_html = '';
        if ( is_callable( $fields ) ) {
            ob_start();
            call_user_func( $fields );
            $fields_html = ob_get_clean();
        }

        $collapsed_class = $collapsed ? ' wao-collapsed' : '';
        ?>
        <div class="wao-container<?= $collapsed_class; ?>" id="wao-container-<?= $key; ?>" data-container-key="<?= $key; ?>">
            <div class="wao-container-header">
                <div class="wao-container-title-group">
                    <h3 class="wao-container-title"><?= $title; ?></h3>
                    <?php if ( ! empty( $description ) ) : ?>
                        <p class="wao-container-desc"><?= esc_html( $description ); ?></p>
                    <?php endif; ?>
                </div>
                <button type="button" class="wao-container-toggle" aria-label="Toggle section">&#x25BE;</button>
            </div>
            <div class="wao-container-body">
                <table class="form-table wao-container-table">
                    <?= $fields_html; ?>
                </table>
            </div>
        </div>
        <?php
        add_action( 'admin_footer', [ $this, 'render_scripts' ] );
    }

    public function render_scripts() {
        $key = esc_attr( $this->args['key'] );
        ?>
        <script>window.addEventListener('load', function() {
            WPAdminOptions.OptionsContainer(<?= json_encode( [ 'key' => $key ] ); ?>);
        });</script>
        <?php
    }
}
