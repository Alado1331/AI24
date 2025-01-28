<?php

if (!defined('ABSPATH')) exit;

// Define constants to use correct paths if they are not already defined
if (!defined('AI24AI_PLUGIN_DIR')) {
    define('AI24AI_PLUGIN_DIR', plugin_dir_path(__FILE__) . '../../ai24-assistant-integrator/');
}
if (!defined('AI24AI_PLUGIN_URL')) {
    define('AI24AI_PLUGIN_URL', plugin_dir_url(__FILE__) . '../../ai24-assistant-integrator/');
}

// Enqueue settings and API version
require_once AI24AI_PLUGIN_DIR . 'includes/settings-page.php';

// Get options
$options = get_option('AI24AI_api_option');

// Determine handler type
$handler_type = is_array($options) && isset($options['handler_type']) ? $options['handler_type'] : 'apiv2';

// Conditionally include the appropriate API handler
if ($handler_type === 'apiv1') {
    require_once AI24AI_PLUGIN_DIR . 'includes/api-handler-v1.php';
} else {
    require_once AI24AI_PLUGIN_DIR . 'includes/api-handler-v2.php';
}

// Make sure you are not having issues with your API keys. They will be accessible in your debug.log if so. Please download and delete old logs. 
if (!function_exists('AI24AI_get_api_key_by_identifier')) {
    function AI24AI_get_api_key_by_identifier($identifier) {
        $apiKeys = get_option('AI24AI_api_keys');
        //error_log('Retrieved API Keys: ' . print_r($apiKeys, true)); // Debug log for API keys

        if (is_array($apiKeys)) {
            foreach ($apiKeys as $details) {
                //error_log('Checking API key: ' . print_r($details, true)); // Debug log for each key
                if ($details['name'] === $identifier) {
                    //error_log('API key found for identifier: ' . $identifier); // Log success
                    return $details['key'];
                }
            }
        }
        //error_log('API key not found for identifier: ' . $identifier); // Log failure
        return false; 
    }
}

// Example to call your identifier 
// $desired_call_name_here = get_api_key_by_identifier('identifier_name_here');
// if (!$desired_call_name_here) {

// FUNCTIONS PLACED BELOW THIS LINE
// MAKE SURE THE PLUGIN IS DEACTIVATED WHEN YOU ARE MAKING CHANGES TO THE CODE! THE FILE SAVE WILL GLITCH AND YOU WILL NEED TO REGENERATE THE FILES