<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('SASV_Visibility_Helper')) {
    class SASV_Visibility_Helper {
        /**
         * Retrieves the list of public post types excluding the ones specified in the settings.
         *
         * @return array Array of public post types not excluded by the settings.
         */
        public static function get_filtered_public_post_types() {
            $excluded_types = get_option('sasv_excluded_post_types', []);
            $public_post_types = get_post_types(['public' => true, 'exclude_from_search' => false]);
            return array_diff($public_post_types, $excluded_types);
        }
    }
}
