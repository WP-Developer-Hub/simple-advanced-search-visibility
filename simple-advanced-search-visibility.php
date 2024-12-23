<?php
/**
 * Plugin Name: Simple Advanced Search Visibility
 * Description: Search Visibility Simplified - A plugin that excludes post types and individual posts from search results and adds "noindex" meta tags to pages.
 * Version: 1.7
 * Author: DJABhipHop
 * Requires PHP: 7.2
 * Requires at least: 6.0
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sas-visibility
 * Domain Path: /languages
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class SASV_Visibility {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_boxes']);
        add_action('pre_get_posts', [$this, 'modify_search_query']);
        add_action('wp_head', [$this, 'add_noindex_meta_tag'], PHP_MAX_INT);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_settings_link']);
    }

    /**
     * Retrieves the list of public post types excluding the ones specified in the settings.
     *
     * @return array Array of public post types not excluded by the settings.
     */
    private function get_filtered_public_post_types() {
        $excluded_types = get_option('sasv_excluded_post_types', []);
        $public_post_types = get_post_types(['public' => true]);
        return array_diff($public_post_types, $excluded_types);
    }

    /**
     * Adds a settings page to the WordPress admin under the "Settings" menu.
     *
     * This page allows site administrators to configure the search visibility settings.
     */
    public function add_settings_page() {
        add_options_page(
            __('Global Search Settings', 'sas-visibility'),
            __('Search Visibility', 'sas-visibility'),
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

        add_settings_section('sas_visibility_main', __('Search Settings', 'sas-visibility'), null, 'sas_visibility_settings');

        add_settings_field(
            'sasv_exclude_password_protected',
            __('Exclude Protected Posts', 'sas-visibility'),
            [$this, 'render_password_protected_field'],
            'sas_visibility_settings',
            'sas_visibility_main'
        );

        add_settings_field(
            'sasv_excluded_post_types',
            __('Exclude Post Types', 'sas-visibility'),
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
            <h1><?php _e('Search Visibility Settings', 'sas-visibility'); ?></h1>
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
                            <?php _e('Exclude all password-protected posts from all query for all available post types.', 'sas-visibility'); ?>
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
        unset($post_types['attachment']); // Exclude attachments
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


    /**
     * Add meta boxes.
     */
    public function add_meta_boxes() {
        global $wp_post_types;

        // Meta Box for Pages
        add_meta_box(
            'sasv_no_index',
            __('Search Engine Visibility', 'sas-visibility'),
            [$this, 'render_no_index_meta_box'],
            'page',
            'side',
            'default'
        );

        // Filter out the post types in $selected_excluded_type.
        $filtered_post_types = $this->get_filtered_public_post_types();

        foreach ($filtered_post_types as $post_type) {
            add_meta_box(
                'sasv_exclude_post',
                __('Internal Search Visibility', 'sas-visibility'),
                [$this, 'render_exclude_post_meta_box'],
                $post_type,
                'side',
                'default'
            );
        }
    }
    
    /**
     * Render No Index Meta Box.
     */
    public function render_no_index_meta_box($post) {
        $value = get_post_meta($post->ID, 'sasv_no_index', true);
        wp_nonce_field('sasv_no_index_nonce', 'sasv_no_index_nonce');
        ?>
        <div class="main">
            <p>
                <label for="sasv_no_index">
                    <strong><?php _e('Allow search engines to index this page', 'sas-visibility'); ?></strong>
                </label>
            </p>
            <p>
                <select id="sasv_no_index" name="sasv_no_index" class="widefat">
                    <option value="yes" <?php selected($value, 'yes'); ?>><?php _e('Yes', 'sas-visibility'); ?></option>
                    <option value="no" <?php selected($value, 'no'); ?>><?php _e('No', 'sas-visibility'); ?></option>
                </select>
            </p>
            <p>
                <?php _e('Selecting <strong>"No"</strong> will prevent this page from being indexed by search engines like Google, Bing, etc.', 'sas-visibility'); ?>
            </p>
        </div>
        <?php
    }
    
    /**
     * Render Exclude Post Meta Box.
     */
    public function render_exclude_post_meta_box($post) {
        $value = get_post_meta($post->ID, 'sasv_exclude_post', true);
        wp_nonce_field('sasv_exclude_post_nonce', 'sasv_exclude_post_nonce');
        $post_type_object = get_post_type_object(get_post_type($post->ID));
        $post_type_label = $post_type_object ? $post_type_object->labels->singular_name : __('Post', 'sas-visibility');
        ?>
        <div class="main">
            <p>
                <label for="sasv_exclude_post">
                    <strong><?php echo esc_html(sprintf(__('Exclude from WordPress search', 'sas-visibility'), strtolower($post_type_label))); ?></strong>
                </label>
            </p>
            <p>
                <select id="sasv_exclude_post" name="sasv_exclude_post" class="widefat">
                    <option value="no" <?php selected($value, 'no'); ?>><?php _e('No', 'sas-visibility'); ?></option>
                    <option value="yes" <?php selected($value, 'yes'); ?>><?php _e('Yes', 'sas-visibility'); ?></option>
                </select>
            </p>
            <p>
                <?php echo sprintf(__('Selecting <strong>"Yes"</strong> will prevent this %s from being shown in WordPress search.', 'sas-visibility'), strtolower($post_type_label)); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Modify Search Query.
     */
    public function modify_search_query($query) {
        if (!is_admin()) {
            $post_has_password = get_option('sasv_exclude_password_protected', 0) ? false : null;

            if ($query->is_search()) {
                $filtered_post_types = $this->get_filtered_public_post_types();

                if (!$query->is_singular()) {
                    $query->set('has_password', $post_has_password);
                }

                if ($filtered_post_types) {
                    $query->set('post_type', $filtered_post_types);
                }
                
                $excluded_post_types = get_posts([
                    'post_type' => $query->query_vars['post_type'],
                    'meta_key' => 'sasv_exclude_post',
                    'meta_value' => 'yes',
                    'fields' => 'ids',
                ]);
                $query->set('post__not_in', $excluded_post_types);
            }

            if ((($query->is_date() || $query->is_year() || $query->is_month() || $query->is_category() || $query->is_tag() || $query->is_author() || is_archive() || $query->is_home() || $query->is_front_page() || $query->is_404()) && $query->is_main_query())) {

                if (!$query->is_singular()) {
                    $query->set('has_password', $post_has_password);
                }
            }
        }
    }

    /**
     * Add settings link to the plugin action links.
     *
     * @param array $links Existing plugin action links.
     * @return array Modified plugin action links.
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=sas-visibility-settings') . '">' . __('Settings', 'sas-visibility') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Save the meta boxes.
     *
     * @param int $post_id Post ID.
     */
    public function save_meta_boxes($post_id) {
        // Check if we're on the post editing screen and not during an autosave or quick edit
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check if this is a valid request for the current screen
        $screen = get_current_screen();
        if ($screen && $screen->base !== 'page') {
            if (isset($_POST['sasv_no_index_nonce']) && wp_verify_nonce($_POST['sasv_no_index_nonce'], 'sasv_no_index_nonce')) {
                if (isset($_POST['sasv_no_index'])) {
                    update_post_meta($post_id, 'sasv_no_index', sanitize_text_field($_POST['sasv_no_index']));
                } else {
                    delete_post_meta($post_id, 'sasv_no_index');
                }
            }
        }

        // Verify sasv_exclude_post_nonce nonce
        if (isset($_POST['sasv_exclude_post_nonce']) && wp_verify_nonce($_POST['sasv_exclude_post_nonce'], 'sasv_exclude_post_nonce')) {
            if (isset($_POST['sasv_exclude_post'])) {
                update_post_meta($post_id, 'sasv_exclude_post', sanitize_text_field($_POST['sasv_exclude_post']));
            } else {
                delete_post_meta($post_id, 'sasv_exclude_post');
            }
        }
    }

    /**
     * Add noindex meta tag to head if the post is set to noindex.
     */
    public function add_noindex_meta_tag() {
        if (is_singular()) {
            global $post;

            if ('no' === get_post_meta($post->ID, 'sasv_no_index', true)) {
                echo '<meta name="robots" content="noindex, follow" />' . PHP_EOL;
            }
        }
    }
}

new SASV_Visibility();
