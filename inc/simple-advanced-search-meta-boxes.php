<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('SAS_Visibility_Meta_Boxes')) {
    class SAS_Visibility_Meta_Boxes {
        public function __construct() {
            add_action('save_post', [$this, 'save_meta_boxes']);
            add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        }

        /**
         * Add meta boxes.
         */
        public function add_meta_boxes() {
            global $wp_post_types;
            
            // Meta Box for Pages
            add_meta_box('sasv_no_index', __('Search Engine Visibility', 'sasv'), [$this, 'render_no_index_meta_box'],
                'page',
                'side',
                'default'
            );

            // Filter out the post types in $selected_excluded_type.
            $filtered_post_types = SAS_Visibility_Helper::get_filtered_public_post_types();
            foreach ($filtered_post_types as $post_type) {
                add_meta_box('sasv_exclude_post', __('Internal Search Visibility', 'sasv'), [$this, 'render_exclude_post_meta_box'],
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
            $no_index = get_post_meta($post->ID, 'sasv_no_index', true);
            $no_iarchive = get_post_meta($post->ID, 'sasv_no_archive', true);
            wp_nonce_field('sasv_no_index_nonce', 'sasv_no_index_nonce');
        ?>
            <div class="main">
                <p>
                    <label for="sasv_no_index">
                        <strong><?php _e('Index this page?', 'sasv'); ?></strong>
                    </label>
                </p>
                <p>
                <select id="sasv_no_index" name="sasv_no_index" class="widefat">
                    <option value="yes" <?php selected($no_index, 'yes'); ?>><?php _e('Yes', 'sasv'); ?></option>
                    <option value="no" <?php selected($no_index, 'no'); ?>><?php _e('No', 'sasv'); ?></option>
                </select>
                </p>
                <p>
                <?php _e('Selecting <strong>"No"</strong> will prevent this page from being indexed by search engines like Google, Bing, etc.', 'sasv'); ?>
                </p>
                <p>
                    <label for="sasv_no_index">
                        <strong><?php _e('Archive this page?', 'sasv'); ?></strong>
                    </label>
                </p>
                <p>
                    <select id="sasv_no_archive" name="sasv_no_archive" class="widefat">
                        <option value="yes" <?php selected($no_iarchive, 'yes'); ?>><?php _e('Yes', 'sasv'); ?></option>
                        <option value="no" <?php selected($no_iarchive, 'no'); ?>><?php _e('No', 'sasv'); ?></option>
                    </select>
                </p>
                <p>
                    <?php _e('Selecting <strong>"No"</strong> will prevent this page from being archive by search engines like Google, Bing, etc.', 'sasv'); ?>
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
            $post_type_label = $post_type_object ? $post_type_object->labels->singular_name : __('Post', 'sasv');
        ?>
            <div class="main">
                <p>
                    <label for="sasv_exclude_post">
                        <strong>
                            <?php echo esc_html(sprintf(__('Exclude from WordPress search', 'sasv'), strtolower($post_type_label))); ?>
                        </strong>
                    </label>
                </p>
                <p>
                    <select id="sasv_exclude_post" name="sasv_exclude_post" class="widefat">
                        <option value="no" <?php selected($value, 'no'); ?>><?php _e('No', 'sasv'); ?></option>
                        <option value="yes" <?php selected($value, 'yes'); ?>><?php _e('Yes', 'sasv'); ?></option>
                    </select>
                </p>
                <p>
                    <?php echo sprintf(__('Selecting <strong>"Yes"</strong> will prevent this %s from being shown in WordPress search.', 'sasv'), strtolower($post_type_label)); ?>
                </p>
            </div>
        <?php
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
                    
                    if (isset($_POST['sasv_no_archive'])) {
                        update_post_meta($post_id, 'sasv_no_archive', sanitize_text_field($_POST['sasv_no_archive']));
                    } else {
                        delete_post_meta($post_id, 'sasv_no_archive');
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
    }
    new SAS_Visibility_Meta_Boxes();
}
