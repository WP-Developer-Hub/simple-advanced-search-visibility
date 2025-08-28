<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('SASV_Visibility_Settings')) {
    class SASV_Visibility_Settings {
        public function __construct() {
            add_action('admin_menu', [$this, 'add_settings_page']);
            add_action('admin_init', [$this, 'register_settings']);
        }

        /**
         * Adds a settings page to the WordPress admin under the "Settings" menu.
         *
         * This page allows site administrators to configure the search visibility settings.
         */
        public function add_settings_page() {
            add_options_page( __('Global Search Settings', 'sasv'), __('Search Visibility', 'sasv'),
                'manage_options',
                'sas-visibility-settings',
                [$this, 'render_settings_page']
            );
        }

        /**
         * Registers settings, sections, and fields for the plugin's settings page.
         *
         * This method defines the options that will be stored in the database and their sanitization callbacks.
         */
        public function register_settings() {
            register_setting('sas_visibility_settings', 'sasv_exclude_password_protected', [
                 'type' => 'boolean',
                 'default' => 0,
                 'sanitize_callback' => 'absint',
            ]);

            register_setting('sas_visibility_settings', 'sasv_excluded_post_types', [
                 'type' => 'array',
                 'default' => [],
                 'sanitize_callback' => [$this, 'sanitize_post_types'],
            ]);
            
            add_settings_section('sas_visibility_main', __('Search Settings', 'sasv'), null, 'sas_visibility_settings');

            add_settings_field( 'sasv_exclude_password_protected', __('Exclude Protected Posts', 'sasv'),
               [$this, 'render_password_protected_field'],
               'sas_visibility_settings',
               'sas_visibility_main'
            );

            add_settings_field( 'sasv_excluded_post_types', __('Exclude Post Types', 'sasv'),
               [$this, 'render_post_types_field'],
               'sas_visibility_settings',
               'sas_visibility_main'
            );
        }

        /**
         * Sanitizes the array of excluded post types.
         *
         * Ensures the input is an array and that all values are sanitized as strings.
         *
         * @param mixed $input The input to sanitize.
         * @return array Sanitized array of post type names.
         */
        public function sanitize_post_types($input) {
            // Ensure input is an array, sanitize each value, and return the result.
            if (!is_array($input)) {
                return [];
            }
            return array_map('sanitize_text_field', $input);
        }

        /**
         * Renders the settings page in the WordPress admin.
         *
         * Outputs the HTML form for administrators to configure the plugin's settings.
         */
        public function render_settings_page() {
        ?>
            <div class="wrap">
                <h1><?php _e('Search Visibility Settings', 'sasv'); ?></h1>
                <form method="post" action="options.php">
                    <?php
                        settings_fields('sas_visibility_settings');
                        do_settings_sections('sas_visibility_settings');
                        submit_button();
                    ?>
                </form>
            </div>
        <?php
        }
        /**
         * Renders the checkbox field for excluding password-protected posts.
         *
         * Allows administrators to enable or disable the exclusion of password-protected posts from search results.
         */
        public function render_password_protected_field() {
            $value = get_option('sasv_exclude_password_protected', 0);
        ?>
            <table class="wp-list-table widefat fixed striped table-view-list posts">
                <tbody id="the-list">
                    <tr>
                        <th scope="row" class="check-column" style="width: 0.2rem;"></th>
                        <td>
                            <label>
                                <input type="checkbox" name="sasv_exclude_password_protected" value="1" <?php checked($value, 1); ?>>
                                <?php _e('Exclude all password-protected posts from all query for all available post types.', 'sasv');?>
                            </label>
                        </td>
                        <th scope="row" class="check-column" style="width: 0.2rem;"></th>
                    </tr>
                </tbody>
            </table>
        <?php
        }
        
        /**
         * Renders the checkbox fields for excluding specific post types from search.
         *
         * Lists all public post types (excluding attachments) and provides checkboxes for administrators to exclude them from search results.
         */
        public function render_post_types_field() {
            $post_types = get_post_types(['public' => true], 'objects');
            unset($post_types['attachment']);
            $excluded_types = get_option('sasv_excluded_post_types', []);
        ?>
            <div style="display: block; overflow-x: hidden; height: 250px; width: 100%; max-width: 100%; padding: 0 1px;">
                <table class="wp-list-table widefat fixed striped table-view-list posts">
                    <tbody id="the-list">
                        <?php foreach ($post_types as $type => $details) : ?>
                            <tr>
                                <th scope="row" class="check-column" style="width: 0.2rem;"></th>
                                    <td>
                                        <label for="sasv-exclude-<?php echo esc_attr($type); ?>">
                                            <input type="checkbox" name="sasv_excluded_post_types[]" value="<?php echo esc_attr($type); ?>" <?php checked(in_array($type, (array) $excluded_types)); ?>>
                                            <?php echo esc_html($details->label); ?>
                                        </label>
                                    </td>
                                <th scope="row" class="check-column" style="width: 0.2rem;"></th>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php
        }
    }
    new SASV_Visibility_Settings();
}
