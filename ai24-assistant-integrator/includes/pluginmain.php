<?php
/*
Plugin Name: AI24AI Child
Plugin URI: https://site24.com.au/ai24ai-child
Description: A child plugin for custom functions.
Version: 1.1
Author: Site24
Author URI: https://site24.com.au
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: ai24ai-child
Domain Path: /languages
*/

if (!defined('ABSPATH')) exit;

// Define constants to use correct paths if they are not already defined
if (!defined('AI24AI_PLUGIN_DIR')) {
    define('AI24AI_PLUGIN_DIR', plugin_dir_path(__FILE__) . '../ai24-assistant-integrator/');
}
if (!defined('AI24AI_PLUGIN_URL')) {
    define('AI24AI_PLUGIN_URL', plugin_dir_url(__FILE__) . '../ai24-assistant-integrator/');
}
if (!defined('AI24AI_CHILD_FUNCTIONS_FILE')) {
    define('AI24AI_CHILD_FUNCTIONS_FILE', plugin_dir_path(__FILE__) . 'includes/functions.php');
}

// Include the functions file
require_once AI24AI_CHILD_FUNCTIONS_FILE;