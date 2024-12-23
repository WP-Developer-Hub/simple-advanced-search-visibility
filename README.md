# Simple Advanced Search Visibility

********Author:******** DJABhipHop

## Description

The Simple Advanced Search Visibility plugin allows WordPress administrators to easily control search visibility by excluding specific post types and individual posts from the search results, while also providing the option to add a "noindex" meta tag to pages and posts. This helps to control the indexability of content by search engines, making your website's search engine optimization (SEO) more granular.

## Features

- Exclude specific post types from WordPress search results.
- Exclude individual pages from search using custom post meta.
- Exclude individual posts from search using custom post meta include individual posts form custom post types.
- Add a "noindex" meta tag to pages to prevent them from being indexed by search engines.
- Easily manage settings via the WordPress admin interface.
- Option to exclude password-protected posts from all query.
- Lightweight and user-friendly, with no unnecessary bloat.

## Installation

1. Upload the `simple-advanced-search-visibility` folder to your WordPress `wp-content/plugins` directory.

2. Activate the plugin through the 'Plugins' menu in WordPress.

3. Navigate to the "Settings" > "Search Visibility" page in the WordPress admin menu to configure your settings.

## Usage

Once activated, you can configure the following settings:

1. ****Exclude Post Types****: Choose which public post types (e.g., posts, pages, custom post types) should be excluded from search results.

2. ****Exclude Password-Protected Posts****: Optionally exclude password-protected posts from appearing in search results.

3. ****Noindex Meta Tag for Pages****: Prevent specific pages from being indexed by search engines (useful for pages like "Thank You", "Privacy Policy", etc.).

4. ****Exclude Specific Posts****: While editing posts or pages, you can choose to exclude them from search results.

## Changelog

= 1.6 =

* Adjusted `wp_head` hook priority for `add_noindex_meta_tag` to `PHP_MAX_INT` to ensure proper placement of the `<meta>` tag within the `<head>` section.
* Simplified the `add_noindex_meta_tag` method by removing the redundant variable assignment and directly fetching the meta value in the condition.




= 1.5 =

* Replaced plain labels and checkboxes with `wp-list-table` styled tables for consistency with WordPress admin UI.

* Updated the "Exclude password-protected posts" field to use a table structure, ensuring proper alignment and accessibility.

* Enhanced the "Excluded Post Types" section with a table layout for improved readability and organization.

* Added unique IDs for checkboxes and associated labels for better accessibility.

* Ensured compliance with WordPress styling standards using `wp-list-table` classes.



= 1.4 =

* Added a scrollable container for post type checkboxes to improve usability in the admin UI.

* Updated checkbox labels to use consistent padding and spacing for better styling.

* Removed unnecessary `<br>` tags for cleaner HTML structure.



= 1.2 =

* Simplified conditional branches for filtering post types and excluding posts.

* Consolidated the exclusion logic to reduce redundancy.

* Ensured consistent application of `has_password` and exclusion meta queries.

* Improved code readability and maintainability by reorganizing related conditions.

  

= 1.3 =

* Adjusted logic to prioritize filtered post types in query modifications.

* Improved handling of excluded posts based on the `sasv_exclude_post` meta key.

* Refined conditional checks for main queries to include search pages.

* Simplified and clarified code flow for better readability and maintainability.


= 1.2 =

* Adjusted logic to prioritize filtered post types in query modifications.

* Improved handling of excluded posts based on the `sasv_exclude_post` meta key.

* Refined conditional checks for main queries to include search pages.

* Simplified and clarified code flow for better readability and maintainability.


= 1.1 =

* Improved user interface for settings page.

* Added compatibility with the latest version of WordPress.

* Fixed minor bugs with the search query modification.

* Enhanced error handling for "noindex" meta tags.


= 1.0 =

* Initial release with basic functionality to exclude post types and individual posts from search results.

* Added support for "noindex" meta tags for pages and posts.


## Frequently Asked Questions
  
**Q**: Can I exclude individual posts from the search results? =
  
**A**: Yes, you can exclude individual posts by editing the post and selecting the "Exclude from WordPress search" option in the meta box.

**Q**: How do I prevent a page from being indexed by search engines? =

**A**: Edit the page and use the "Search Engine Visibility" meta box to select "No" for allowing search engines to index the page. This will add a "noindex" meta tag to that page.

**Q**: Will this plugin affect the visibility of posts on my site? =

**A**: No, this plugin only affects the visibility of posts in WordPress search results and prevents certain pages from being indexed by search engines. It does not alter how posts are displayed on your site.


## Support 

For support, please visit the plugin page or open an issue in the [GitHub repository](https://github.com/your-repository-link).

## Screenshots

1. ********Settings Page********:
![Imgur](https://imgur.com/yLW8Yog.png)

2. ********Meta Box for Pages********:
![Imgur](https://imgur.com/kqlsf1v.png)

3. ********Meta Box for Posts********:
![Imgur](https://imgur.com/vQEVs35.png)
