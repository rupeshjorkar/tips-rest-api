<?php

/**

 * The plugin is Used For Rest API

 *

 * @link              https://www.c-metric.com/

 * @since             1.0.0

 * @package           tips-rest-api

 *

 * @wordpress-plugin

 * Plugin Name:       Tips Rest API

 * Plugin URI:        https://www.c-metric.com/

 * Description:       This plugin allows you to handle all TIPs-related post types and taxonomies from your WordPress database using the WordPress Rest API. This plugin makes it easy to interact with TIPs data, enabling you to fetch, manage, and integrate your custom post types and taxonomies programmatically.

 * Version:           1.0.0

 * Author:            cmetric

 * Author URI:        https://www.c-metric.com/

 * License:           GPL-2.0+

 */

// If this file is called directly, abort.

if (!defined('ABSPATH')) {

	die("Please don't try to access this file directly.");
}
if (!defined('TIPS_REST_API_VERSION')) {

	define('TIPS_REST_API_VERSION', '0.0.1');
}
if (!defined('TIPS_REST_API_PLUGIN_URL')) {

	define('TIPS_REST_API_PLUGIN_URL', __FILE__);
}

if (!defined('TIPS_REST_API_DIR')) {

	define('TIPS_REST_API_DIR', plugin_dir_path(TIPS_REST_API_PLUGIN_URL));
}

if (!defined('TIPS_REST_API_URL')) {

	define('TIPS_REST_API_URL', plugin_dir_url(TIPS_REST_API_PLUGIN_URL));
}

if (!defined('TIPS_REST_API_BASENAME')) {

	define('TIPS_REST_API_BASENAME', plugin_basename(TIPS_REST_API_PLUGIN_URL));
}

if (!defined('TIPS_REST_API_TEXT_DOMAIN')) {

	define('TIPS_REST_API_TEXT_DOMAIN', 'tips_rest_api_management');
}

if (!defined('TIPS_REST_API_SLUG')) {

	define('TIPS_REST_API_SLUG', 'tips-rest-api');
}

/**

 * The core plugin class that is used to define internationalization,

 * admin-specific hooks, and public-facing site hooks.

 */
require TIPS_REST_API_DIR . 'includes/tips-rest-api-books.php';
require TIPS_REST_API_DIR . 'includes/tips-rest-api-book-chapter-numbers.php';
require TIPS_REST_API_DIR . 'includes/tips-rest-api-book-chapter-verses.php';
require TIPS_REST_API_DIR . 'includes/tips-rest-api-verse-related-stories.php';
require TIPS_REST_API_DIR . 'includes/tips-rest-api-story-detail-page.php';
require TIPS_REST_API_DIR . 'includes/tips-rest-api-source-detail-page.php';
require TIPS_REST_API_DIR . 'includes/tips-rest-api-tree-view-page.php';
require TIPS_REST_API_DIR . 'includes/tips-rest-insights-number-api.php';
require TIPS_REST_API_DIR . 'includes/tips-rest-verses-number-api.php';
require TIPS_REST_API_DIR . 'includes/tips-rest-languages-number-api.php';
require TIPS_REST_API_DIR . 'includes/tips-rest-api-serach.php';
require TIPS_REST_API_DIR . 'includes/tips-rest-api-verse-search.php';
require TIPS_REST_API_DIR . 'includes/tips-rest-api-verse-search-redirect.php';
require TIPS_REST_API_DIR . 'includes/class-tips-common-setting.php';





require TIPS_REST_API_DIR . 'includes/class-tips-common.php';
require TIPS_REST_API_DIR . 'includes/tips-rest-api-common-functions.php';

$tips_common = new Tips_Common();
