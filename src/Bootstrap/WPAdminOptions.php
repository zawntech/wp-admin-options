<?php

namespace Zawntech\WPAdminOptions\Bootstrap;

use Zawntech\WPAdminOptions\AttachmentOption;
use Zawntech\WPAdminOptions\BooleanCheckboxOption;
use Zawntech\WPAdminOptions\ColorOption;
use Zawntech\WPAdminOptions\DateTimeOption;
use Zawntech\WPAdminOptions\DurationOption;
use Zawntech\WPAdminOptions\EditorOption;
use Zawntech\WPAdminOptions\ExampleJsonMediaOption;
use Zawntech\WPAdminOptions\ExampleJsonOption;
use Zawntech\WPAdminOptions\HtmlOption;
use Zawntech\WPAdminOptions\InputOption;
use Zawntech\WPAdminOptions\OptionsContainer;
use Zawntech\WPAdminOptions\PostTypeSelectOption;
use Zawntech\WPAdminOptions\SelectOption;
use Zawntech\WPAdminOptions\TaxonomySelectOption;
use Zawntech\WPAdminOptions\TextareaOption;
use Zawntech\WPAdminOptions\UserSelectOption;

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

        // Auto-enable test mode when the package lives outside the WP installation
        // (e.g. Composer path repository), since wp_enqueue_* URLs won't resolve.
        if ( ! static::$test_mode && ! static::is_local_package() ) {
            static::$test_mode = true;
        }

        if ( static::$test_mode ) {
            add_action( 'admin_footer', [ static::class, 'print_inline_assets' ], 1 );
        } elseif ( did_action( 'admin_enqueue_scripts' ) ) {
            static::enqueue_assets();
        } else {
            add_action( 'admin_enqueue_scripts', [ static::class, 'enqueue_assets' ] );
        }
    }

    /**
     * Whether the package assets directory lives inside the WordPress installation.
     */
    public static function is_local_package() {
        $abspath = wp_normalize_path( untrailingslashit( ABSPATH ) );
        $package_path = wp_normalize_path( dirname( __DIR__, 2 ) );
        return strpos( $package_path, $abspath ) === 0;
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
     * Render a test page with all available admin option types.
     */
    public static function render_test_options() {

        // InputOption — Text Types
        new OptionsContainer([
            'key'         => 'input_text',
            'title'       => 'InputOption — Text Types',
            'description' => 'InputOption with type: text, email, url, password, tel.',
            'fields'      => function() {
                new InputOption([
                    'key'         => '_test_input_text',
                    'label'       => 'Input (Text)',
                    'type'        => 'text',
                    'value'       => 'Hello World',
                    'description' => 'A standard text input.',
                    'placeholder' => 'Enter text...',
                    'help'        => 'Enter any plain text value. This field accepts alphanumeric characters and common punctuation.',
                ]);
                new InputOption([
                    'key'         => '_test_input_email',
                    'label'       => 'Input (Email)',
                    'type'        => 'email',
                    'value'       => 'admin@example.com',
                    'description' => 'An email input with browser validation.',
                    'placeholder' => 'user@example.com',
                    'help'        => 'Must be a valid email address. The browser will validate the format before submission.',
                ]);
                new InputOption([
                    'key'         => '_test_input_url',
                    'label'       => 'Input (URL)',
                    'type'        => 'url',
                    'value'       => 'https://example.com',
                    'description' => 'A URL input with browser validation.',
                    'placeholder' => 'https://',
                    'help'        => 'Enter a fully qualified URL including the protocol (e.g. https://).',
                ]);
                new InputOption([
                    'key'         => '_test_input_password',
                    'label'       => 'Input (Password)',
                    'type'        => 'password',
                    'value'       => 'supersecret',
                    'description' => 'A password input with reveal toggle.',
                    'placeholder' => 'Enter password...',
                    'help'        => 'Passwords are stored securely. Click the eye icon to temporarily reveal the value.',
                ]);
                new InputOption([
                    'key'            => '_test_input_password_no_reveal',
                    'label'          => 'Input (Password, No Reveal)',
                    'type'           => 'password',
                    'value'          => 'encrypted_hash_value',
                    'prevent_reveal' => true,
                    'description'    => 'A secured password input with reveal disabled.',
                ]);
                new InputOption([
                    'key'              => '_test_input_tel_us',
                    'label'            => 'Input (Tel, US)',
                    'type'             => 'tel',
                    'value'            => '(555) 123-4567',
                    'telephone_format' => 'US',
                    'description'      => 'A US-formatted telephone input.',
                    'placeholder'      => '(555) 000-0000',
                    'help'             => 'Automatically formats as a US phone number. Type digits only — formatting is applied as you type.',
                ]);
                new InputOption([
                    'key'              => '_test_input_tel_intl',
                    'label'            => 'Input (Tel, International)',
                    'type'             => 'tel',
                    'value'            => '+44 20 7946 0958',
                    'telephone_format' => 'International',
                    'description'      => 'An international telephone input.',
                    'placeholder'      => '+## ### #### ####',
                ]);
                new InputOption([
                    'key'              => '_test_input_tel_digits',
                    'label'            => 'Input (Tel, Digits Only)',
                    'type'             => 'tel',
                    'value'            => '5551234567',
                    'telephone_format' => 'US',
                    'digits_only'      => true,
                    'description'      => 'Displays formatted but submits digits only.',
                    'placeholder'      => '(555) 000-0000',
                ]);
                new InputOption([
                    'key'              => '_test_input_tel_custom',
                    'label'            => 'Input (Tel, Custom)',
                    'type'             => 'tel',
                    'value'            => '1 (800) 555-0199',
                    'telephone_format' => '# (###) ###-####',
                    'description'      => 'A custom-format telephone input.',
                    'placeholder'      => '# (###) ###-####',
                ]);
                new InputOption([
                    'key'         => '_test_input_readonly',
                    'label'       => 'Input (Readonly)',
                    'type'        => 'text',
                    'value'       => 'pk_live_abc123xyz',
                    'readonly'    => true,
                    'description' => 'A readonly input with copy button.',
                    'help'        => 'This value cannot be edited. Use the copy button to copy it to your clipboard.',
                ]);
            },
        ]);

        // InputOption — Numeric Types
        new OptionsContainer([
            'key'         => 'input_numeric',
            'title'       => 'InputOption — Numeric Types',
            'description' => 'InputOption with type: number, range.',
            'fields'      => function() {
                new InputOption([
                    'key'         => '_test_input_number',
                    'label'       => 'Input (Number)',
                    'type'        => 'number',
                    'value'       => '42',
                    'min'         => '0',
                    'max'         => '100',
                    'step'        => '1',
                    'description' => 'A number input with min/max/step.',
                    'help'        => 'Accepts values between 0 and 100. Use the spinner or type a value directly.',
                ]);
                new InputOption([
                    'key'         => '_test_input_range',
                    'label'       => 'Input (Range)',
                    'type'        => 'range',
                    'value'       => '60',
                    'min'         => '0',
                    'max'         => '100',
                    'step'        => '5',
                    'description' => 'A range slider input.',
                    'help'        => 'Drag the slider to select a value. Increments in steps of 5 between 0 and 100.',
                ]);
            },
        ]);

        // Textarea
        new OptionsContainer([
            'key'         => 'textarea',
            'title'       => 'TextareaOption',
            'description' => 'Multi-line text area fields.',
            'fields'      => function() {
                new TextareaOption([
                    'key'         => '_test_textarea',
                    'label'       => 'Textarea',
                    'value'       => "Line one.\nLine two.",
                    'rows'        => 4,
                    'description' => 'A multi-line text area.',
                    'help'        => 'Supports multi-line text. Line breaks are preserved when the value is saved.',
                ]);
            },
        ]);

        // Toggles & Choices
        new OptionsContainer([
            'key'         => 'toggles_choices',
            'title'       => 'Toggles & Choices',
            'description' => 'Boolean checkboxes and select dropdowns.',
            'fields'      => function() {
                new BooleanCheckboxOption([
                    'key'         => '_test_boolean',
                    'label'       => 'Boolean Checkbox',
                    'value'       => 1,
                    'description' => 'A toggle switch.',
                    'help'        => 'Toggle this switch to enable or disable the feature. The value is stored as 1 (on) or 0 (off).',
                ]);
                new SelectOption([
                    'key'         => '_test_select',
                    'label'       => 'Select (Single)',
                    'value'       => 'b',
                    'options'     => [
                        ''  => '— Select —',
                        'a' => 'Option A',
                        'b' => 'Option B',
                        'c' => 'Option C',
                    ],
                    'description' => 'A single-value select.',
                    'help'        => 'Choose one option from the dropdown. Uses Select2 for enhanced search and keyboard navigation.',
                ]);
                new SelectOption([
                    'key'         => '_test_select_multi',
                    'label'       => 'Select (Multiple)',
                    'value'       => ['a', 'c'],
                    'multiple'    => true,
                    'options'     => [
                        'a' => 'Alpha',
                        'b' => 'Beta',
                        'c' => 'Gamma',
                        'd' => 'Delta',
                    ],
                    'description' => 'A multi-value select.',
                    'help'        => 'Select multiple values. Click to add, click the × to remove. Values are stored as an array.',
                ]);
            },
        ]);

        // Colors
        new OptionsContainer([
            'key'         => 'colors',
            'title'       => 'Color Pickers',
            'description' => 'WordPress default, Spectrum, and swatch-based color pickers.',
            'fields'      => function() {
                new ColorOption([
                    'key'         => '_test_color_default',
                    'label'       => 'Color (Default)',
                    'value'       => '#3b82f6',
                    'description' => 'WordPress built-in color picker.',
                    'help'        => 'Click the swatch to open the color picker. Accepts hex color values.',
                ]);
                new ColorOption([
                    'key'         => '_test_color_spectrum',
                    'label'       => 'Color (Spectrum)',
                    'type'        => 'spectrum',
                    'value'       => '#ef4444',
                    'description' => 'Spectrum.js color picker with alpha.',
                    'help'        => 'Full-spectrum HSV picker with alpha channel support. Stores rgba() or hex values.',
                ]);
                new ColorOption([
                    'key'            => '_test_color_swatches',
                    'label'          => 'Color (Swatches)',
                    'type'           => 'swatches',
                    'value'          => '#3b82f6',
                    'color_swatches' => [
                        '#ef4444', '#f97316', '#eab308', '#22c55e',
                        '#3b82f6', '#8b5cf6', '#ec4899', '#111827',
                        'rgba(59,130,246,0.5)', 'rgba(0,0,0,0.25)',
                    ],
                    'description'    => 'A preset swatch color picker.',
                    'help'           => 'Pick from a curated set of color swatches. Supports hex and rgba values.',
                ]);
            },
        ]);

        // Date & Time
        new OptionsContainer([
            'key'         => 'date_time',
            'title'       => 'Date & Time',
            'description' => 'DateTime and duration pickers.',
            'fields'      => function() {
                new DateTimeOption([
                    'key'         => '_test_datetime',
                    'label'       => 'DateTime',
                    'value'       => '2026-03-04 14:30:00',
                    'description' => 'A date and time picker.',
                    'help'        => 'Select a date and time. Stored in Y-m-d H:i:s format.',
                ]);
                new DurationOption([
                    'key'         => '_test_duration',
                    'label'       => 'Duration',
                    'value'       => '01:30:00',
                    'description' => 'An hours/minutes duration picker.',
                    'help'        => 'Set a time duration in hours and minutes. Stored as HH:MM:SS.',
                ]);
            },
        ]);

        // Content
        new OptionsContainer([
            'key'         => 'content',
            'title'       => 'Content',
            'description' => 'Rich text editors and HTML output.',
            'fields'      => function() {
                new EditorOption([
                    'key'         => '_test_editor',
                    'label'       => 'Editor',
                    'value'       => '<p>Rich text content here.</p>',
                    'description' => 'A WordPress TinyMCE editor.',
                    'help'        => 'Rich text editor powered by TinyMCE. Supports formatting, links, and media embeds.',
                ]);
                new HtmlOption([
                    'key'         => '_test_html',
                    'label'       => 'HTML (Display Only)',
                    'value'       => '<div style="padding:8px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:4px;color:#166534;">This is raw HTML output.</div>',
                ]);
            },
        ]);

        // Relational Selectors
        new OptionsContainer([
            'key'         => 'relational',
            'title'       => 'Relational Selectors',
            'description' => 'Post, taxonomy, and user selection fields.',
            'fields'      => function() {
                new PostTypeSelectOption([
                    'key'              => '_test_post_select',
                    'label'            => 'Post Select (Single)',
                    'post_type'        => 'post',
                    'value'            => 0,
                    'select_post_text' => 'Select a post...',
                    'description'      => 'A searchable single post selector.',
                    'help'             => 'Search and select a single post. Uses AJAX to query posts by title.',
                ]);
                new PostTypeSelectOption([
                    'key'              => '_test_post_select_multi',
                    'label'            => 'Post Select (Multiple)',
                    'post_type'        => 'post',
                    'multiple'         => true,
                    'value'            => [],
                    'select_post_text' => 'Select posts...',
                    'description'      => 'A searchable multi-post selector.',
                ]);
                new TaxonomySelectOption([
                    'key'         => '_test_taxonomy_select',
                    'label'       => 'Taxonomy Select (Single)',
                    'taxonomy'    => 'category',
                    'value'       => 0,
                    'description' => 'A searchable single term selector.',
                    'help'        => 'Search and select a single taxonomy term from the dropdown.',
                ]);
                new TaxonomySelectOption([
                    'key'         => '_test_taxonomy_select_multi',
                    'label'       => 'Taxonomy Select (Multiple)',
                    'taxonomy'    => 'category',
                    'multiple'    => true,
                    'value'       => [],
                    'description' => 'A searchable multi-term selector.',
                ]);
                new UserSelectOption([
                    'key'         => '_test_user_select',
                    'label'       => 'User Select (Single)',
                    'value'       => 0,
                    'role'        => ['administrator'],
                    'description' => 'A searchable single user selector.',
                    'help'        => 'Search and select a user. Filtered by the administrator role.',
                ]);
                new UserSelectOption([
                    'key'         => '_test_user_select_multi',
                    'label'       => 'User Select (Multiple)',
                    'multiple'    => true,
                    'value'       => [],
                    'role'        => ['administrator'],
                    'description' => 'A searchable multi-user selector.',
                ]);
            },
        ]);

        // Media
        new OptionsContainer([
            'key'         => 'media',
            'title'       => 'Media',
            'description' => 'Attachment and media pickers.',
            'fields'      => function() {
                new AttachmentOption([
                    'key'         => '_test_attachment',
                    'label'       => 'Attachment (Single)',
                    'value'       => [],
                    'multiple'    => false,
                    'media_types' => ['image'],
                    'description' => 'A single image attachment picker.',
                    'help'        => 'Opens the WordPress media library. Select a single image to attach.',
                ]);
                new AttachmentOption([
                    'key'         => '_test_attachment_multi',
                    'label'       => 'Attachment (Multiple)',
                    'value'       => [],
                    'multiple'    => true,
                    'media_types' => ['image'],
                    'description' => 'A multi-image attachment picker.',
                    'help'        => 'Opens the WordPress media library. Select multiple images — drag to reorder.',
                ]);
            },
        ]);

        // JSON
        new OptionsContainer([
            'key'         => 'json',
            'title'       => 'JSON Options',
            'description' => 'Structured JSON data fields.',
            'collapsed'   => true,
            'fields'      => function() {
                new ExampleJsonOption([
                    'key'   => '_test_json',
                    'label' => 'JSON Option (Example)',
                    'value' => [
                        ['id' => 1, 'first_name' => 'Jane', 'last_name' => 'Doe', 'description' => 'An example entry.'],
                    ],
                ]);
                new ExampleJsonMediaOption([
                    'key'   => '_test_json_media',
                    'label' => 'JSON Media Option (Example)',
                    'value' => [],
                ]);
            },
        ]);
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
