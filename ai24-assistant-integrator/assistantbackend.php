<?php

/*
Plugin Name: AI24 Assistant Integrator
Plugin URI: https://site24.com.au/ai24-assistant-integrator/
Description: The easiest way to integrate OpenAI Assistants into your WordPress site, for free. NOW WITH V2 API
Version: 1.0.9
Author: Site24
Author URI: https://site24.com.au/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Copyright 2024 Site24
AI24 Assistant Integrator is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or any later version.

AI24 Assistant Integrator is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with AI24 Assistant Integrator. If not, see <http://www.gnu.org/licenses/>.
*/



defined('ABSPATH') or die('Direct script access disallowed.');
define('AI24AI_VERSION', '1.0.9');



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



// Plugin Activation
if (!function_exists('AI24AI_activate')) {
    function AI24AI_activate() {
        error_log('AI24AI_activate called');
        
        // Get the previously stored version
        $previous_version = get_option('AI24AI_version');
        error_log('Previous version: ' . $previous_version);

        $new_plugin_dir = plugin_dir_path(__FILE__);
        $plugin_base_dir = dirname($new_plugin_dir); // Base directory where all plugin versions reside

        // Perform any necessary actions when updating from a previous version
        if ($previous_version && $previous_version !== AI24AI_VERSION) {
            // Any additional actions for version updates can be added here
            error_log('Updating from version: ' . $previous_version . ' to version: ' . AI24AI_VERSION);
        }

        // Update to the new version
        update_option('AI24AI_version', AI24AI_VERSION);
        error_log('Updated version to: ' . AI24AI_VERSION);

        // Ensure the widget option is set
        if (get_option('AI24AI_enable_widget') === false) {
            update_option('AI24AI_enable_widget', 'no');
            error_log('AI24AI_enable_widget set to no');
        }
    }
}
register_activation_hook(__FILE__, 'AI24AI_activate');



// Plugin Deactivation
if (!function_exists('AI24AI_deactivate')) {
    function AI24AI_deactivate() {
        update_option('AI24AI_active', 'no');
        error_log('AI24AI_deactivate called');
    }
}
register_deactivation_hook(__FILE__, 'AI24AI_deactivate');



// Check for plugin updates
if (!function_exists('AI24AI_check_version')) {
    function AI24AI_check_version() {
        $installed_version = get_option('AI24AI_version');
        //error_log('Checking version: ' . $installed_version);
        
        if ($installed_version !== AI24AI_VERSION) {
            AI24AI_activate();
        }
    }
}
add_action('plugins_loaded', 'AI24AI_check_version');



// Function to add the settings link
function ai24ai_add_plugin_settings_link($links) {
    // error_log('ai24ai_add_plugin_settings_link called'); 
    $settings_link = '<a href="admin.php?page=AI24-assistant-integrator">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ai24ai_add_plugin_settings_link');



require_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';
require_once plugin_dir_path(__FILE__) . 'assets/php/Parsedown.php';


$options = get_option('AI24AI_functions_option');
$handler_type = is_array($options) && isset($options['handler_type']) ? $options['handler_type'] : 'apiv2';



// Conditionally include the appropriate file
if ($handler_type === 'apiv1') {
    require_once plugin_dir_path(__FILE__) . 'includes/api-handler-v1.php';
} else {
    require_once plugin_dir_path(__FILE__) . 'includes/api-handler-v2.php';
}



// Plugin code below this line
function AI24AI_enqueue_scripts() {
    $options = get_option('AI24AI_font_settings', array('font' => 'AI24AIKumbhsans'));
    $font_family = isset($options['font']) ? $options['font'] : 'AI24AIKumbhsans';

    if ($font_family === 'AI24AIKumbhsans') {
        wp_enqueue_style('AI24AI-kumbhsans-font', plugin_dir_url(__FILE__) . 'assets/css/ai24ai-kumbhsans.css', array(), null);
    } else {
        //wp_enqueue_style('AI24AI-custom-font', plugin_dir_url(__FILE__) . 'assets/fonts/' . $font_family . '.css', array(), null);
    }

    // Enqueue front-end styles
    wp_enqueue_style(
        'AI24AI-style', 
        AI24AI_PLUGIN_URL . 'assets/css/AI24AI-style.css', 
        array(), 
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/AI24AI-style.css')
    );

    // Enqueue front-end script
    wp_enqueue_script(
        'AI24AI-script', 
        AI24AI_PLUGIN_URL . 'assets/js/AI24AI-script.js', 
        array('jquery', 'lottie'), 
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/AI24AI-script.js'), 
        true
    );

    // previous code here
    /**Atharva was here */
    // Add React app 
    wp_enqueue_script(
        'AI24AI-chat-main',
        plugins_url('assets/js/chat-main.js', __FILE__),
        array('wp-element'),
        '1.0',
        true
    );
    wp_script_add_data('AI24AI-chat-main', 'type', 'module');
    
    /**Atharva was here */

    // Add inline style to apply the selected font
    wp_add_inline_style('AI24AI-style', "
    #AI24AI-chatbot-container, #AI24AI-chatbot-container * {
        font-family: 'AI24AIKumbhsans', sans-serif !important;
    }, 999");

    // Welcome messages array
    $context_messages = array();
    for ($i = 1; $i <= 3; $i++) {
        $is_toggle_on = $i === 1 ? true : get_option("AI24AI_assistant_context_toggle_$i", 'off') === 'on';
        if ($is_toggle_on) {
            $context_message = get_option("AI24AI_assistant_context_message_$i", '');
            if (!empty($context_message)) { // Additional check to ensure message isn't empty
                $context_messages[] = $context_message;
            }
        }
    }

    $nonce = wp_create_nonce('AI24AI_nonce');
    
    // Localize the front-end script with necessary data
    $assistant_styling = array(
        'name' => get_option('AI24AI_assistant_name'),
        'description' => get_option('AI24AI_assistant_description'),
        'image' => get_option('AI24AI_assistant_image'),
        'contextMessages' => $context_messages, 
        'typingAnimationPath' => plugins_url('assets/images/TypingAnimation.json', __FILE__), 
    );

    wp_localize_script('AI24AI-script', 'AI24AI_params', array_merge(
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => $nonce,
        ),
        $assistant_styling
    ));
}
add_action('wp_enqueue_scripts', 'AI24AI_enqueue_scripts');



// Function to enqueue Lottie from local assets
function AI24AI_enqueue_lottie_script() {
    wp_enqueue_script('lottie', plugins_url('assets/js/lottie.min.js', __FILE__), array(), '5.12.2', true);
}
add_action('wp_enqueue_scripts', 'AI24AI_enqueue_lottie_script');

// Function to add 'defer' attribute to specific scripts
function AI24AI_add_defer_attribute($tag, $handle) {
    if ('lottie' === $handle) {
        return str_replace(' src', ' defer="defer" src', $tag);
    }
    return $tag;
}
add_filter('script_loader_tag', 'AI24AI_add_defer_attribute', 10, 2);



//AJAX HANDLER
function AI24AI_handle_chat_request() {
    check_ajax_referer('AI24AI_nonce', 'nonce');

    $user_input = sanitize_text_field($_POST['user_input']);
    $start_new_thread = isset($_POST['start_new_thread']) && $_POST['start_new_thread'] === 'true';
    $assistant_id = get_option('AI24AI_assistant_id'); 

    if ($start_new_thread) {
        $thread_id = AI24AI_create_thread();
    } else {
        $thread_id = isset($_POST['thread_id']) ? sanitize_text_field($_POST['thread_id']) : '';
    }

    if (empty($thread_id)) {
        wp_send_json_error(['error' => 'Failed to manage the conversation thread.']);
        return;
    }

    $message_id = AI24AI_send_message_to_thread($thread_id, $user_input);

    if (!$message_id) {
        wp_send_json_error(['error' => 'Failed to send message.']);
        return;
    }

    // Initiating a run after sending a message
    $run_id = AI24AI_create_run($thread_id, $assistant_id);

    if (!$run_id) {
        wp_send_json_error(['error' => 'Failed to initiate run.']);
        return;
    }

    $messages = AI24AI_poll_for_response_and_retrieve_details($thread_id, $run_id);

    if (is_array($messages) && !empty($messages)) {
        wp_send_json_success(['thread_id' => $thread_id, 'messages' => $messages]);
    } else {
        wp_send_json_error(['error' => 'Failed to retrieve messages from the run.']);
    }
}

add_action('wp_ajax_AI24AI_chat', 'AI24AI_handle_chat_request');
add_action('wp_ajax_nopriv_AI24AI_chat', 'AI24AI_handle_chat_request');



// Function that controls the placement of the widget on the front end
function AI24AI_output_chatbox() {  
    $widget_enabled = get_option('AI24AI_enable_widget', 'yes');
    $widget_pages = get_option('AI24AI_widget_pages', array());

    if ($widget_enabled !== 'yes') {
        return; 
    }

    if (!empty($widget_pages) && !is_page($widget_pages)) {
        return;
    }
  
    $va_name = get_option('AI24AI_assistant_name', 'Voxel Virtual Assistant');
    $va_image = get_option('AI24AI_assistant_image');
    $va_description = get_option('AI24AI_assistant_description', '');
    $chat_input_placeholder = get_option('AI24AI_chat_input_placeholder');

    
    if (empty($chat_input_placeholder)) {
        $chat_input_placeholder = 'Type your message here...';
    }


    $exit_confirmation_text = get_option('AI24AI_exit_confirmation_text');
    if (empty($exit_confirmation_text)) {
        $exit_confirmation_text = 'Are you sure you want to exit? Your current conversation will be lost.';
    }


    $confirm_exit_button_text = get_option('AI24AI_confirm_exit_button');
    if (empty($confirm_exit_button_text)) {
        $confirm_exit_button_text = 'Yes, exit';
    }


    $cancel_exit_button_text = get_option('AI24AI_cancel_exit_button');
    if (empty($cancel_exit_button_text)) {
        $cancel_exit_button_text = 'No, cancel';
    }


    $toggle_image_url = get_option('AI24AI_toggle_image');
    $send_icon_url = plugins_url('assets/images/site24-send-button.svg', __FILE__);
    if (empty($toggle_image_url)) {
        if (defined('AI24AI_PLUGIN_BASE_URL')) {
            $toggle_image_url = get_option('AI24AI_toggle_image', AI24AI_PLUGIN_BASE_URL . 'assets/images/messagebubble.svg');
        } else {
            // Fallback if AI24AI_PLUGIN_BASE_URL is not defined
            $toggle_image_url = get_option('AI24AI_toggle_image', plugins_url('assets/images/messagebubble.svg', __DIR__));
        }
    }    

    $sidebar_content = get_option('AI24AI_sidebar_content', 'icon');
    $sidebar_text = get_option('AI24AI_sidebar_text', ''); 
    
    ?>
    <!-- Toggle Button -->
    <div id="AI24AI-chatbot-toggle">
        <?php if ($sidebar_content === 'text' && !empty($sidebar_text)): ?>
            <span class="AI24AI-chatbot-toggle-text"><?php echo esc_html($sidebar_text); ?></span>
        <?php else: ?>
            <img src="<?php echo esc_url($toggle_image_url); ?>" alt="Chat Toggle">
        <?php endif; ?>
    </div>
    
    <!-- Chat Interface -->
    <div id="AI24AI-chatbot-container">
        <!-- Chatbox Header -->
        <div id="chatbox-header">
            <div id="chatbox-left">
                <?php if ($va_image): ?>
                    <img src="<?php echo esc_url($va_image); ?>" alt="<?php echo esc_attr($va_name); ?>" class="va-header-image">
                <?php endif; ?>
            </div>
            <div id="chatbox-title"><?php echo esc_html($va_name); ?></div>
            <div id="chatbox-controls">
                <button id="chatbox-minimize">—</button>
                <button id="chatbox-close">✕</button>
            </div>
        </div>
    
        <!-- Chat Messages and VA Info Section -->
        <div id="chat-messages">
            <!-- VA Info Section -->
            <div class="va-info-section">
                <!-- VA Image and Name -->
                <div class="va-top-info">
                    <?php if ($va_image): ?>
                        <img src="<?php echo esc_url($va_image); ?>" alt="<?php echo esc_attr($va_name); ?>" class="va-image">
                    <?php endif; ?>
                    <div class="va-name"><?php echo esc_html($va_name); ?></div>
                    <!-- VA Description -->
                    <?php if ($va_description): ?>
                        <div class="va-description"><?php echo esc_html($va_description); ?></div>
                    <?php endif; ?>
                    <!-- Separator Line -->
                    <div class="va-info-separator"></div>
                </div>
            </div>
            
            <!-- Placeholder where messages will be dynamically appended -->
            <div class="messages-content">
                <!-- Assistant and user messages will be appended here by the JavaScript -->
            </div>
        </div>
    
        <!-- Chat Input Container -->
        <div id="chat-input-container">
            <textarea id="chat-input" placeholder="<?php echo esc_attr($chat_input_placeholder); ?>"></textarea>
            <div class="send-button-container">
                <img src="<?php echo esc_url($send_icon_url); ?>" id="send-button" alt="Send" class="chat-send-icon" />
            </div>
            <!-- Attribution Text -->
            <div id="powered-by">
                Powered by <a href="https://site24.com.au/ai-services/" target="_blank">AI24</a>
            </div>
        </div>

        <!-- Exit Confirmation Modal -->
        <div id="exit-confirmation-modal" class="modal">
            <div class="modal-content">
                <p><?php echo wp_kses_post($exit_confirmation_text); ?></p>
                <button id="confirm-exit"><?php echo esc_html($confirm_exit_button_text); ?></button>
                <button id="cancel-exit"><?php echo esc_html($cancel_exit_button_text); ?></button>
            </div>
        </div>
    </div>
    <?php
}

add_action('wp_footer', 'AI24AI_output_chatbox', 100);