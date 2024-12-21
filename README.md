=== Simple Advanced Search Visibility ===
Contributors: DJABhipHop
Tags: search, visibility, noindex, exclude posts, SEO, search engine, settings
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.2
Stable tag: 1.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: sas-visibility
Domain Path: /languages

== Description ==

The Simple Advanced Search Visibility plugin allows WordPress administrators to easily control search visibility by excluding specific post types and individual posts from the search results, while also providing the option to add a "noindex" meta tag to pages and posts. This helps to control the indexability of content by search engines, making your website's search engine optimization (SEO) more granular.

== Features ==

- Exclude specific post types from WordPress search results.
- Exclude individual posts from search using custom post meta.
- Add a "noindex" meta tag to pages to prevent them from being indexed by search engines.
- Easily manage settings via the WordPress admin interface.
- Option to exclude password-protected posts from all query.
- Lightweight and user-friendly, with no unnecessary bloat.

== Installation ==

1. Upload the `simple-advanced-search-visibility` folder to your WordPress `wp-content/plugins` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the "Settings" > "Search Visibility" page in the WordPress admin menu to configure your settings.

== Usage ==

Once activated, you can configure the following settings:

1. **Exclude Post Types**: Choose which public post types (e.g., posts, pages, custom post types) should be excluded from search results.
2. **Exclude Password-Protected Posts**: Optionally exclude password-protected posts from appearing in search results.
3. **Noindex Meta Tag for Pages**: Prevent specific pages from being indexed by search engines (useful for pages like "Thank You", "Privacy Policy", etc.).
4. **Exclude Specific Posts**: While editing posts or pages, you can choose to exclude them from search results.

== Changelog ==

= 1.1 =
* Improved user interface for settings page.
* Added compatibility with the latest version of WordPress.
* Fixed minor bugs with the search query modification.
* Enhanced error handling for "noindex" meta tags.

= 1.0 =
* Initial release with basic functionality to exclude post types and individual posts from search results.
* Added support for "noindex" meta tags for pages and posts.

== Frequently Asked Questions ==

= Can I exclude individual posts from the search results? =

Yes, you can exclude individual posts by editing the post and selecting the "Exclude from WordPress search" option in the meta box.

= How do I prevent a page from being indexed by search engines? =

Edit the page and use the "Search Engine Visibility" meta box to select "No" for allowing search engines to index the page. This will add a "noindex" meta tag to that page.

= Will this plugin affect the visibility of posts on my site? =

No, this plugin only affects the visibility of posts in WordPress search results and prevents certain pages from being indexed by search engines. It does not alter how posts are displayed on your site.

== Support ==

For support, please visit the plugin page or open an issue in the [GitHub repository](https://github.com/your-repository-link).

== Screenshots ==

1. **Settings Page**: Configure which post types and posts are excluded from the search.
2. **Meta Box for Pages**: Control the "noindex" setting for pages.
3. **Meta Box for Posts**: Choose to exclude individual posts from search results.

== Upgrade Notice ==

= 1.1 =
This update improves compatibility with the latest WordPress version and provides bug fixes for the search query and meta tag handling. Update to ensure better performance and enhanced functionality.
