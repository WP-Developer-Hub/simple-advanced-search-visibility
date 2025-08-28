<?php
/**
 * Plugin Name: Simple Advanced Search Visibility
 * Description: Search Visibility Simplified - A plugin that excludes post types and individual posts from search results and adds "noindex" meta tags to pages.
 * Version: 1.9
 * Author: DJABhipHop
 * Requires PHP: 7.2
 * Requires at least: 6.0
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sasv
 * Domain Path: /languages
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

define('SASV_VISIBILITY_PLUGIN_ORG', 'WP-Developer-Hub');
define('SASV_VISIBILITY_PLUGIN_SLUG', 'simple-advanced-search-visibility');
define('SASV_VISIBILITY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SASV_VISIBILITY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SASV_VISIBILITY_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('SASV_VISIBILITY_PLUGIN_IS_DEBUG_ON', (defined('WP_DEBUG') && WP_DEBUG));

if (!class_exists('SASV_Visibility')) {
    class SASV_Visibility {
        public function __construct() {
            require_once(SASV_VISIBILITY_PLUGIN_DIR . 'inc/simple-advanced-search-helper.php');
            require_once(SASV_VISIBILITY_PLUGIN_DIR . 'inc/simple-advanced-search-settings.php');
            require_once(SASV_VISIBILITY_PLUGIN_DIR . 'inc/simple-advanced-search-meta-boxes.php');

            if (defined('SASV_VISIBILITY_PLUGIN_IS_DEBUG_ON') && !SASV_VISIBILITY_PLUGIN_IS_DEBUG_ON) {
                require_once(SASV_VISIBILITY_PLUGIN_DIR . 'inc/simple-advanced-search-updates.php');
            }

            add_action('wp_robots', [$this, 'add_noindex_meta_tag']);
            add_action('pre_get_posts', [$this, 'modify_search_query']);
            add_filter('plugin_action_links_' . SASV_VISIBILITY_PLUGIN_BASENAME, [$this, 'add_settings_link']);
        }


        /**
         * Add noindex meta tag to head if the post is set to noindex.
         */
        public function add_noindex_meta_tag($robots) {
            if (is_singular()) {
                global $post;
                
                if ('no' === get_post_meta($post->ID, 'sasv_no_index', true)) {
                    unset($robots['max-image-preview']);
                    $robots['follow'] = true;
                    $robots['noindex'] = true;
                    if ('no' === get_post_meta($post->ID, 'sasv_no_archive', true)) {
                        $robots['noarchive'] = true;
                    }
                }
            }
            return $robots;
        }

        /**
         * Modify Search Query.
         */
        public function modify_search_query($query) {
            if (!is_admin()) {
                $post_has_password = get_option('sasv_exclude_password_protected', 0) ? false : null;

                if ($query->is_search() && $query->is_main_query()) {
                    $filtered_post_types = SASV_Visibility_Helper::get_filtered_public_post_types();

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
    }
    new SASV_Visibility();
}
