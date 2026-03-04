<?php

namespace Zawntech\WPAdminOptions\Bootstrap;

class WPAdminOptions
{
    const INIT_FLAG = 'ZAWNTECH_WP_ADMIN_OPTIONS_INITIALIZED';

    /**
     * Enqueue Vue.js 3, Select2 JS/CSS via CDN on admin pages.
     */
    public static function init() {
        if ( defined( self::INIT_FLAG ) ) {
            return;
        }

        define( self::INIT_FLAG, true );
        add_action( 'admin_enqueue_scripts', [ static::class, 'enqueue_assets' ] );
    }

    /**
     * Enqueue Vue.js and Select2 assets from CDN.
     */
    public static function enqueue_assets() {
        // Vue.js 3.5.22
        if ( ! wp_script_is( 'vue', 'registered' ) ) {
            wp_register_script(
                'vue',
                'https://cdnjs.cloudflare.com/ajax/libs/vue/3.5.22/vue.global.prod.min.js',
                [],
                '3.5.22',
                true
            );
        }
        wp_enqueue_script( 'vue' );

        // Select2 4.0.13
        if ( ! wp_style_is( 'select2', 'registered' ) ) {
            wp_register_style(
                'select2',
                'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css',
                [],
                '4.0.13'
            );
        }
        wp_enqueue_style( 'select2' );

        if ( ! wp_script_is( 'select2', 'registered' ) ) {
            wp_register_script(
                'select2',
                'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js',
                [ 'jquery' ],
                '4.0.13',
                true
            );
        }
        wp_enqueue_script( 'select2' );
    }
}
