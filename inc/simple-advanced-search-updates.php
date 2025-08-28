<?php
/**
 * Provides automatic updates for the plugin via GitHub releases
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin Data
$plugin_org = SAS_VISIBILITY_PLUGIN_ORG;
$plugin_slug = SAS_VISIBILITY_PLUGIN_SLUG;
$api_url = 'https://api.github.com/repos/' . $plugin_org . '/' . $plugin_slug . '/releases/latest';

if (!class_exists('SAS_Visibility_Auto_Updates')) {
    class SAS_Visibility_Auto_Updates {
        private $api_endpoint = null;
        private $plugin_slug = null;
        private $plugin_file = null;

        public function __construct($api_url = '', $plugin_slug = '', $plugin_file = '') {
            $this->api_endpoint = $api_url;
            $this->plugin_slug = $plugin_slug;
            $this->plugin_file = $plugin_file;

            add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
        }

        private function get_local_version() {
            if (!function_exists('get_plugin_data')) {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->plugin_file);
            $version = isset($plugin_data['Version']) ? $plugin_data['Version'] : '';
            error_log('[Updater] Local plugin version: ' . $version);
            return $version;
        }

        private function call_api() {
            $args = array(
                'headers' => array(
                    'User-Agent' => 'WordPress/' . get_bloginfo('version'),
                ),
                'timeout' => 15,
            );
            $response = wp_remote_get($this->api_endpoint, $args);

            if (is_wp_error($response)) {
                error_log('[Updater] GitHub API error: ' . $response->get_error_message());
                return false;
            }
            $response_body = wp_remote_retrieve_body($response);
            error_log('[Updater] GitHub API response: ' . $response_body);
            return json_decode($response_body);
        }

        public function get_license_info() {
            $release = $this->call_api();
            if (!$release || empty($release->tag_name)) {
                error_log('[Updater] No release or tag_name found from GitHub API.');
                return false;
            }
            // Find the zip asset
            $package_url = '';
            if (!empty($release->assets) && is_array($release->assets)) {
                foreach ($release->assets as $asset) {
                    if (isset($asset->browser_download_url) && strpos($asset->browser_download_url, '.zip') !== false) {
                        $package_url = $asset->browser_download_url;
                        break;
                    }
                }
            }
            // Fallback to source zip
            if (empty($package_url) && isset($release->zipball_url)) {
                $package_url = $release->zipball_url;
            }
            error_log('[Updater] Latest GitHub version: ' . $release->tag_name . ' | Package: ' . $package_url);
            return (object) array(
                'version' => ltrim($release->tag_name, 'v'),
                'package' => $package_url,
                'url' => $release->html_url,
                'slug' => $this->plugin_slug,
                'icons' => $this->get_icon_urls(),
            );
        }

        private function is_api_error($response) {
            $is_error = ($response === false);
            if ($is_error) {
                error_log('[Updater] API response is error/false.');
            }
            return $is_error;
        }

        public function is_update_available() {
            $release_info = $this->get_license_info();
            if ($this->is_api_error($release_info)) {
                error_log('[Updater] No valid release info.');
                return false;
            }
            $local_version = $this->get_local_version();
            if (version_compare($release_info->version, $local_version, '>')) {
                error_log('[Updater] Update available! Remote: ' . $release_info->version . ' Local: ' . $local_version);
                return $release_info;
            }
            error_log('[Updater] No update available. Remote: ' . $release_info->version . ' Local: ' . $local_version);
            return false;
        }

        public function check_for_update($transient) {
            error_log('[Updater] Running check_for_update...');
            if (empty($transient->checked)) {
                error_log('[Updater] No checked plugins in transient.');
                return $transient;
            }
            $info = $this->is_update_available();
            if ($info !== false) {
                // Use the plugin file as the key in the transient response
                $transient->response[$this->plugin_file] = (object) array(
                    'new_version' => $info->version,
                    'package' => $info->package,
                    'url' => $info->url,
                    'slug' => $this->plugin_slug,
                    'icons' => $this->get_icon_urls(),
                );
                error_log('[Updater] Update array set in transient for ' . $this->plugin_file);
            } else {
                error_log('[Updater] No update set in transient.');
            }
            return $transient;
        }

        private function get_icon_urls() {
            $base_url = SAS_VISIBILITY_PLUGIN_URL . 'assets/';

            return array(
                '1x'  => $base_url . 'icon-128x128.png',
                '2x'  => $base_url . 'icon-256x256.png',
                'svg' => $base_url . 'icon.svg',
            );
        }
    }
    // Instantiate with all required parameters
    new SAS_Visibility_Auto_Updates($api_url, $plugin_slug, SAS_VISIBILITY_PLUGIN_BASENAME);
}
