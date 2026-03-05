<?php

namespace Zawntech\WPAdminOptions\Bootstrap;

class WPAdminOptions
{
    const INIT_FLAG = 'ZAWNTECH_WP_ADMIN_OPTIONS_INITIALIZED';

    protected static $test_mode = false;

    /**
     * Enable test mode to inline CSS via <style> tags instead of enqueuing.
     */
    public static function enable_test_mode() {
        static::$test_mode = true;
    }

    /**
     * Enqueue Vue.js 3, Select2 JS/CSS via CDN on admin pages.
     */
    public static function init() {

        if ( defined( self::INIT_FLAG ) ) {
            return;
        }

        define( self::INIT_FLAG, true );

        if ( static::$test_mode ) {
            static::print_inline_assets();
        }

        add_action( 'admin_enqueue_scripts', [ static::class, 'enqueue_assets' ] );
    }

    /**
     * Get the URL to the package's assets directory.
     */
    public static function get_assets_url() {
        $package_dir = dirname( __DIR__, 2 );
        $abspath = wp_normalize_path( untrailingslashit( ABSPATH ) );
        $package_path = wp_normalize_path( $package_dir );
        $relative = str_replace( $abspath, '', $package_path );
        return site_url( $relative . '/assets/' );
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

        // WP Admin Options assets
        if ( ! static::$test_mode ) {
            $assets_url = static::get_assets_url();
            if ( ! wp_style_is( 'wp-admin-options', 'registered' ) ) {
                wp_register_style(
                    'wp-admin-options',
                    $assets_url . 'css/wp-admin-options.css',
                    [],
                    '1.0.0'
                );
            }
            wp_enqueue_style( 'wp-admin-options' );

            if ( ! wp_script_is( 'wp-admin-options', 'registered' ) ) {
                wp_register_script(
                    'wp-admin-options',
                    $assets_url . 'js/wp-admin-options.js',
                    [ 'jquery', 'vue', 'select2' ],
                    '1.0.0',
                    true
                );
            }
            wp_enqueue_script( 'wp-admin-options' );
        }
    }

    /**
     * Print all assets inline (test mode) – CSS via <style>, JS via <script src="">.
     */
    public static function print_inline_assets() {
        // Vue.js 3.5.22
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/3.5.22/vue.global.prod.min.js"></script>';

        // Select2 4.0.13
        echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>';

        // WP Admin Options stylesheet (inline from file)
        $css_file = dirname( __DIR__, 2 ) . '/assets/css/wp-admin-options.css';
        if ( file_exists( $css_file ) ) {
            echo '<style>' . file_get_contents( $css_file ) . '</style>';
        }

        // WP Admin Options JS (inline from file)
        $js_file = dirname( __DIR__, 2 ) . '/assets/js/wp-admin-options.js';
        if ( file_exists( $js_file ) ) {
            echo '<script>' . file_get_contents( $js_file ) . '</script>';
        }
    }
}
