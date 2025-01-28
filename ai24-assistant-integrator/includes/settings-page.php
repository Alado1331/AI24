<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Enqueue admin scripts
function AI24AI_enqueue_admin_scripts($hook) {
    if ('toplevel_page_AI24-assistant-integrator' !== $hook) {
        return;
    }

    wp_enqueue_media();
    
    wp_enqueue_script(
        'AI24AI-toggle-functions',
        AI24AI_PLUGIN_URL . 'assets/js/toggle-functions.js',
        array('jquery'),
        false,
        true
    );

    // Enqueue the script for dynamic fields
    wp_enqueue_script(
        'AI24AI-admin-dynamic-fields',
        AI24AI_PLUGIN_URL . 'assets/js/admin-dynamic-fields.js',
        array('jquery'),
        false,
        true
    );

    // Enqueue the main admin script and add inline JavaScript
    /*
    wp_enqueue_script(
        'AI24AI-admin-script',
        AI24AI_PLUGIN_URL . 'assets/js/admin-main.js',
        array(),
        false,
        true
    );
    wp_add_inline_script('AI24AI-admin-script', 'function toggleVisibility(fieldId) {...}'); // Inline JS code omitted for brevity
    */

    if (isset($_GET['tab']) && 'assistant_styling' === $_GET['tab']) {
        wp_enqueue_script(
            'AI24AI-image-uploader',
            AI24AI_PLUGIN_URL . 'assets/js/image-uploader.js',
            array('jquery'), // make sure jQuery is listed as a dependency
            false,
            true
        );
        wp_add_inline_script('AI24AI-image-uploader', sprintf(
            'var uploadImageText = "%s"; var useThisImageText = "%s";',
            esc_js(esc_html__('Upload Image', 'ai24-assistant-integrator')),
            esc_js(esc_html__('Use this image', 'ai24-assistant-integrator'))
        ));
    }

    // Specific scripts for the 'styling' tab
    if (isset($_GET['tab']) && $_GET['tab'] === 'styling') {
        wp_enqueue_script( 
            'AI24AI-image-uploader-toggle',
            AI24AI_PLUGIN_URL . 'assets/js/image-uploader-toggle.js',
            array('jquery'),
            false,
            true
        );
        $localize_data = array(
            'uploadIconText' => esc_html__('Select or Upload Icon', 'ai24-assistant-integrator'),
            'useIconText' => esc_html__('Use this icon', 'ai24-assistant-integrator')
        );
        wp_localize_script('AI24AI-image-uploader-toggle', 'ai24aiToggleImage', $localize_data);
    }

    // Conditionally enqueue the toggle-sidebar.js script for the 'styling' tab
    if (isset($_GET['tab']) && 'styling' === $_GET['tab']) {
        wp_enqueue_script(
            'AI24AI-toggle-sidebar',
            AI24AI_PLUGIN_URL . 'assets/js/toggle-sidebar.js',
            array('jquery'),
            false,
            true
        );
    }

    // Conditionally enqueue the sliders.css file
    if (isset($_GET['tab']) && $_GET['tab'] === 'styling') {
        wp_enqueue_style(
            'AI24AI-sliders-css',
            AI24AI_PLUGIN_URL . 'assets/css/sliders.css',
            array(),
            false
        );
    }

    // Conditionally enqueue the extra-admin.js file
    if (isset($_GET['tab']) && $_GET['tab'] === 'styling') {
        wp_enqueue_script(
            'AI24AI-extra-admin-js',
            AI24AI_PLUGIN_URL . 'assets/js/extra-admin.js',
            array('jquery'),
            false,
            true
        );
    }
    
    wp_enqueue_style(
        'AI24AI-admin-css',
        AI24AI_PLUGIN_URL . 'assets/css/admin.css',
        array(),
        false  
    );
    
    wp_enqueue_script(
        'ai24ai-generate-functions',
        AI24AI_PLUGIN_URL . 'assets/js/generate-functions.js',
        array('jquery'),
        false,
        true
    );
    wp_enqueue_script(
        'ai24ai-regenerate-functions',
        AI24AI_PLUGIN_URL . 'assets/js/regenerate-functions.js',
        array('jquery'),
        false,
        true
    );
    wp_enqueue_script(
        'ai24ai-duplicate-functions',
        AI24AI_PLUGIN_URL . 'assets/js/duplicate-functions.js',
        array('jquery'),
        false,
        true
    );
}   
add_action('admin_enqueue_scripts', 'AI24AI_enqueue_admin_scripts');



function AI24AI_add_admin_menu() {
    $icon_path = __DIR__ . '/../assets/images/spy-agent.svg';
    $icon_data = file_get_contents($icon_path);
    if ($icon_data === false) {
        $icon_base64 = ''; 
    } else {
        $icon_base64 = base64_encode($icon_data);
    }
    add_menu_page(
        'AI24 Assistant Integrator Settings', // Page title
        'AI24 AI', // Menu title
        'manage_options', // Capability
        'AI24-assistant-integrator', // Menu slug
        'AI24AI_settings_page', // Callback function
        'data:image/svg+xml;base64,' . $icon_base64, // Icon URL
        3 // Position
    );

    add_submenu_page(
        'AI24-assistant-integrator', // Parent slug
        'API Settings', // Page title
        'API Settings', // Menu title
        'manage_options', // Capability
        'AI24-assistant-integrator&tab=main_settings', // Menu slug
        'AI24AI_settings_page' // Callback function
    );

    add_submenu_page(
        'AI24-assistant-integrator', // Parent slug
        'Widget Styling', // Page title
        'Widget Styling', // Menu title
        'manage_options', // Capability
        'AI24-assistant-integrator&tab=styling', // Menu slug
        'AI24AI_settings_page' // Callback function
    );

    add_submenu_page(
        'AI24-assistant-integrator', // Parent slug
        'Assistant Styling', // Page title
        'Assistant Styling', // Menu title
        'manage_options', // Capability
        'AI24-assistant-integrator&tab=assistant_styling', // Menu slug
        'AI24AI_settings_page' // Callback function
    );

    add_submenu_page(
        'AI24-assistant-integrator', // Parent slug
        'Tutorials', // Page title
        'Tutorials', // Menu title
        'manage_options', // Capability
        'AI24-assistant-integrator&tab=tutorials', // Menu slug
        'AI24AI_settings_page' // Callback function
    );

    // Remove the duplicate submenu item
    remove_submenu_page('AI24-assistant-integrator', 'AI24-assistant-integrator');
}

add_action('admin_menu', 'AI24AI_add_admin_menu');



// Filter to add 'current' class to active submenu item
function AI24AI_highlight_submenu($parent_file) {
    global $submenu_file, $current_screen, $pagenow;
    
    // Check if we're on the settings page
    if ($pagenow === 'admin.php' && isset($_GET['page']) && $_GET['page'] === 'AI24-assistant-integrator') {
        $submenu_file = 'AI24-assistant-integrator';
        if (isset($_GET['tab'])) {
            switch ($_GET['tab']) {
                case 'main_settings':
                    $submenu_file = 'AI24-assistant-integrator&tab=main_settings';
                    break;
                case 'styling':
                    $submenu_file = 'AI24-assistant-integrator&tab=styling';
                    break;
                case 'assistant_styling':
                    $submenu_file = 'AI24-assistant-integrator&tab=assistant_styling';
                    break;
                case 'tutorials':
                    $submenu_file = 'AI24-assistant-integrator&tab=tutorials';
                    break;
            }
        }
        $parent_file = 'AI24-assistant-integrator';
    }

    return $parent_file;
}

add_filter('parent_file', 'AI24AI_highlight_submenu');



//Function to add the settings page 
function AI24AI_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $default_tab = 'main_settings';
    $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : $default_tab;

    $base_url = admin_url('admin.php?page=AI24-assistant-integrator');

    $tabs = [
        'main_settings' => 'API settings',
        'styling' => 'Widget Styling',
        'assistant_styling' => 'Assistant Styling',
        'tutorials' => 'Tutorials',
    ];

    ?>
    <div class="wrap">
        <h2>AI24 Assistant Integrator Settings</h2>
        <h2 class="nav-tab-wrapper">
            <?php foreach ($tabs as $tab_id => $tab_label):
                $tab_url = esc_url(add_query_arg(['tab' => $tab_id], $base_url));
            ?>
                <a href="<?php echo esc_url($tab_url); ?>" class="nav-tab <?php echo esc_attr($active_tab === $tab_id ? 'nav-tab-active' : ''); ?>">
                    <?php echo esc_html($tab_label); ?>
                </a>
            <?php endforeach; ?>
        </h2>
        <form action="options.php" method="POST">
            <?php
            wp_nonce_field('AI24AI_settings_action', 'AI24AI_settings_nonce');
            
            if ($active_tab === 'main_settings') {
                settings_fields('AI24AI_options_group');
                do_settings_sections('AI24AI-main-settings');
            } elseif ($active_tab === 'styling') {
                settings_fields('AI24AI_styling_group');
                do_settings_sections('AI24AI-styling');
            } elseif ($active_tab === 'assistant_styling') {
                settings_fields('AI24AI_assistant_styling_group');
                do_settings_sections('AI24AI-assistant-styling');
            } elseif ($active_tab === 'tutorials') {
                settings_fields('AI24AI_tutorials_group');
                do_settings_sections('AI24AI-tutorials');
            }

            if ($active_tab !== 'tutorials') {
                submit_button();
            }
            ?>
        </form>
    </div>
    <?php 
}



function AI24AI_register_settings() {
    // Check if we are on the specific settings page and the form has been submitted
    if (isset($_POST['AI24AI_settings_nonce']) && isset($_GET['page']) && $_GET['page'] === 'AI24-assistant-integrator') {
        $nonce = sanitize_text_field(wp_unslash($_POST['AI24AI_settings_nonce']));
        if (wp_verify_nonce($nonce, 'AI24AI_settings_action')) {
            error_log('Nonce verification passed for settings action.');
            // Process your settings update here
        } else {
            error_log('Nonce verification failed for settings action.');
            wp_die(esc_html__('Security check failed.', 'ai24-assistant-integrator'));
        }
    }

    // Register settings for main settings
    register_setting('AI24AI_options_group', 'AI24AI_functions_option');
    register_setting('AI24AI_options_group', 'AI24AI_functions_generate');
    //register_setting('AI24AI_options_group', 'AI24AI_functions_regenerate');
    //register_setting('AI24AI_options_group', 'AI24AI_functions_edit');
    register_setting('AI24AI_options_group', 'AI24AI_api_key');
    register_setting('AI24AI_options_group', 'AI24AI_assistant_id');
    register_setting('AI24AI_options_group', 'AI24AI_api_keys', 'AI24AI_sanitize_api_keys');

    // Add a new section for Function Settings under the main settings
    add_settings_section('AI24AI_function_settings', 'API Version', 'AI24AI_functions_section_callback', 'AI24AI-main-settings');
    add_settings_field('AI24AI_functions_field', 'API Handler Type', 'AI24AI_functions_field_callback', 'AI24AI-main-settings', 'AI24AI_function_settings');
    add_settings_field('AI24AI_functions_generate_button', 'Generate Functions File', 'AI24AI_generate_button_callback', 'AI24AI-main-settings', 'AI24AI_function_settings', array('label_for' => 'AI24AI_functions_generate_button', 'class' => 'AI24AI-class', 'description' => 'Click to generate the custom functions file.'));
    add_settings_field('AI24AI_functions_regenerate_button', 'Regenerate Functions File', 'AI24AI_regenerate_button_callback', 'AI24AI-main-settings', 'AI24AI_function_settings', array('label_for' => 'AI24AI_functions_regenerate_button', 'class' => 'AI24AI-class', 'description' => 'Click to regenerate the custom functions file.'));
    add_settings_field('AI24AI_functions_duplicate_button', 'Duplicate Functions File', 'AI24AI_duplicate_button_callback', 'AI24AI-main-settings', 'AI24AI_function_settings', array('label_for' => 'AI24AI_functions_duplicate_button', 'class' => 'AI24AI-class', 'description' => 'Click to duplicate the custom functions file.'));
    add_settings_field('AI24AI_functions_edit_button', 'Edit Functions File', 'AI24AI_edit_button_callback', 'AI24AI-main-settings', 'AI24AI_function_settings', array('label_for' => 'AI24AI_functions_edit_button', 'class' => 'AI24AI-class', 'description' => 'Click to edit the custom functions file.'));

    // Add settings section and fields for API Settings
    add_settings_section('AI24AI_api_settings', 'API Keys', null, 'AI24AI-main-settings');
    add_settings_field('AI24AI_api_key', 'OpenAI API Key', 'AI24AI_api_key_callback', 'AI24AI-main-settings', 'AI24AI_api_settings');
    add_settings_field('AI24AI_assistant_id', 'Assistant ID', 'AI24AI_assistant_id_callback', 'AI24AI-main-settings', 'AI24AI_api_settings');
    add_settings_field('AI24AI_api_keys', 'Secret Keys', 'AI24AI_api_keys_callback', 'AI24AI-main-settings', 'AI24AI_api_settings');



    // Register settings for widget styling
    register_setting('AI24AI_styling_group', 'AI24AI_enable_widget', 'sanitize_text_field');
    register_setting('AI24AI_styling_group', 'AI24AI_widget_pages', 'AI24AI_sanitize_pages');
    register_setting('AI24AI_styling_group', 'AI24AI_widget_show_hide_mode');
    register_setting('AI24AI_styling_group', 'AI24AI_header_color');
    register_setting('AI24AI_styling_group', 'AI24AI_widget_corner_color');
    register_setting('AI24AI_styling_group', 'AI24AI_toggle_image');
    register_setting('AI24AI_styling_group', 'AI24AI_input_border_glow_color');
    register_setting('AI24AI_styling_group', 'AI24AI_icon_color');
    register_setting('AI24AI_styling_group', 'AI24AI_widget_position');
    register_setting('AI24AI_styling_group', 'AI24AI_sidebar_content');
    register_setting('AI24AI_styling_group', 'AI24AI_sidebar_text', 'sanitize_text_field');
    register_setting('AI24AI_styling_group', 'AI24AI_text_options', 'AI24AI_sanitize_text_options');

    // Add settings section and fields for widget styling
    add_settings_section('AI24AI_widget_styling', 'Widget Styling', null, 'AI24AI-styling');
    add_settings_field('AI24AI_enable_widget', 'Enable Widget', 'AI24AI_enable_widget_callback', 'AI24AI-styling', 'AI24AI_widget_styling');
    add_settings_field('AI24AI_widget_pages', 'Select Pages for Widget', 'AI24AI_widget_pages_callback', 'AI24AI-styling', 'AI24AI_widget_styling');
    add_settings_field('AI24AI_widget_position', 'Widget Position', 'AI24AI_widget_position_callback', 'AI24AI-styling', 'AI24AI_widget_styling');
    add_settings_field('AI24AI_sidebar_content', 'Sidebar Content', 'AI24AI_sidebar_content_callback', 'AI24AI-styling', 'AI24AI_widget_styling');
    add_settings_field('AI24AI_header_color', 'Header Color', 'AI24AI_header_color_callback', 'AI24AI-styling', 'AI24AI_widget_styling');
    add_settings_field('AI24AI_widget_corner_color', 'Toggle Color', 'AI24AI_widget_corner_color_callback', 'AI24AI-styling', 'AI24AI_widget_styling');
    add_settings_field('AI24AI_icon_color', 'Toggle Icon/Text Color', 'AI24AI_icon_color_callback', 'AI24AI-styling', 'AI24AI_widget_styling');
    add_settings_field('AI24AI_toggle_image', 'Toggle Icon SVG', 'AI24AI_toggle_image_callback', 'AI24AI-styling', 'AI24AI_widget_styling');
    add_settings_field('AI24AI_input_border_glow_color', 'Input Border Glow Color', 'AI24AI_input_border_glow_color_callback', 'AI24AI-styling', 'AI24AI_widget_styling');

    // New font settings section and field
    //add_settings_section('AI24AI_font_settings', 'Font Settings', 'AI24AI_font_settings_callback', 'AI24AI-styling');
    //add_settings_field('AI24AI_font', 'Chatbot Font', 'AI24AI_font_field_callback', 'AI24AI-styling', 'AI24AI_font_settings');
    // Add settings section for text options
    add_settings_section('AI24AI_text_options', 'Text Options', 'AI24AI_text_options_callback', 'AI24AI-styling');
    add_settings_field('AI24AI_markdown_enabled', 'Enable Markdown', 'AI24AI_markdown_enabled_callback', 'AI24AI-styling', 'AI24AI_text_options');



    // Tutorials Section
    add_settings_section('AI24AI_tutorials_section', 'Tutorials', 'AI24AI_tutorials_section_callback', 'AI24AI-tutorials');
    add_settings_field('AI24AI_tutorial_video', 'What is AI24?', 'AI24AI_tutorial_video_callback', 'AI24AI-tutorials', 'AI24AI_tutorials_section');
    add_settings_field('AI24AI_tutorial_video2', 'What are functions?', 'AI24AI_tutorial_video2_callback', 'AI24AI-tutorials', 'AI24AI_tutorials_section');
    add_settings_field('AI24AI_tutorial_video3', 'How to create OpenAI assistant functions', 'AI24AI_tutorial_video3_callback', 'AI24AI-tutorials', 'AI24AI_tutorials_section');
    add_settings_field('AI24AI_tutorial_video4', 'How to create OpenAI assistants', 'AI24AI_tutorial_video4_callback', 'AI24AI-tutorials', 'AI24AI_tutorials_section');



    // First, create a new option group for assistant styling settings.
    register_setting('AI24AI_assistant_styling_group', 'AI24AI_assistant_name');
    register_setting('AI24AI_assistant_styling_group', 'AI24AI_assistant_description');
    register_setting('AI24AI_assistant_styling_group', 'AI24AI_assistant_image');
    register_setting('AI24AI_assistant_styling_group', 'AI24AI_assistant_context_message');
    register_setting('AI24AI_assistant_styling_group', 'AI24AI_chat_input_placeholder');
    register_setting('AI24AI_assistant_styling_group', 'AI24AI_exit_confirmation_text');
    register_setting('AI24AI_assistant_styling_group', 'AI24AI_confirm_exit_button');
    register_setting('AI24AI_assistant_styling_group', 'AI24AI_cancel_exit_button');
    // Register new settings for up to three context messages and their toggles
    for ($i = 1; $i <= 3; $i++) {
        register_setting('AI24AI_assistant_styling_group', "AI24AI_assistant_context_message_$i");
        register_setting('AI24AI_assistant_styling_group', "AI24AI_assistant_context_toggle_$i");
    }

     // Add settings section for assistant styling
    add_settings_section('AI24AI_assistant_styling_settings', 'Assistant Styling Settings', 'AI24AI_styling_settings_callback', 'AI24AI-assistant-styling');
    add_settings_field('AI24AI_assistant_name', 'Assistant Name', 'AI24AI_assistant_name_callback', 'AI24AI-assistant-styling', 'AI24AI_assistant_styling_settings');
    add_settings_field('AI24AI_assistant_description', 'Assistant Description', 'AI24AI_assistant_description_callback', 'AI24AI-assistant-styling', 'AI24AI_assistant_styling_settings');
    add_settings_field('AI24AI_assistant_image', 'Assistant Image', 'AI24AI_assistant_image_callback', 'AI24AI-assistant-styling', 'AI24AI_assistant_styling_settings');
    for ($i = 1; $i <= 3; $i++) {
         add_settings_field("AI24AI_assistant_context_message_$i", "Starting Message $i", 'AI24AI_context_message_callback', 'AI24AI-assistant-styling', 'AI24AI_assistant_styling_settings', array('index' => $i));
         add_settings_field("AI24AI_assistant_context_toggle_$i", "Enable Message $i", 'AI24AI_context_toggle_callback', 'AI24AI-assistant-styling', 'AI24AI_assistant_styling_settings', array('index' => $i));
    }
    add_settings_field('AI24AI_chat_input_placeholder', 'Chat Input Placeholder', 'AI24AI_chat_input_placeholder_callback', 'AI24AI-assistant-styling', 'AI24AI_assistant_styling_settings');
    add_settings_field('AI24AI_exit_confirmation_text', 'Exit Confirmation Text', 'AI24AI_exit_confirmation_text_callback', 'AI24AI-assistant-styling', 'AI24AI_assistant_styling_settings');
    add_settings_field('AI24AI_confirm_exit_button', 'Confirm Exit Button Text', 'AI24AI_confirm_exit_button_callback', 'AI24AI-assistant-styling', 'AI24AI_assistant_styling_settings');
    add_settings_field('AI24AI_cancel_exit_button', 'Cancel Exit Button Text', 'AI24AI_cancel_exit_button_callback', 'AI24AI-assistant-styling', 'AI24AI_assistant_styling_settings');

}
add_action('admin_init', 'AI24AI_register_settings');



// Generate Function file button callback
function AI24AI_generate_button_callback($args) {
    echo '<button type="button" id="AI24AI_functions_generate_button" class="button">Generate Functions File</button>';
}

// Regenerate Function file button callback
function AI24AI_regenerate_button_callback($args) {
    echo '<button type="button" id="AI24AI_functions_regenerate_button" class="button">Regenerate Functions File</button>';
}

// Duplicate Function file button callback
function AI24AI_duplicate_button_callback($args) {
    echo '<div id="duplicate-function-file">
            <label for="duplicate-file-name">Enter the new file name (without extension):</label>
            <input type="text" id="duplicate-file-name" name="duplicate-file-name" placeholder="custom-functions">
            <button type="button" id="AI24AI_functions_duplicate_button" class="button">Duplicate Functions File</button>
          </div>';
}

function AI24AI_edit_button_callback($args) {
    $plugin_base_dir = basename(WP_PLUGIN_DIR . '/AI24AI-child'); 
    $plugin_file_path = 'includes/functions.php';
    $plugin_editor_link = network_admin_url("plugin-editor.php") . '?file=' . urlencode($plugin_base_dir . '/' . $plugin_file_path) . '&plugin=' . urlencode($plugin_base_dir . '/pluginmain.php');
    
    echo '<a href="' . esc_url($plugin_editor_link) . '" class="button" target="_blank">Edit Functions File</a>';
}

// AJAX handler for generating the functions file
add_action('wp_ajax_AI24AI_generate_function', function() {
    // Paths for source and destination files
    $source_main_file = WP_PLUGIN_DIR . '/ai24-assistant-integrator/includes/pluginmain.php';
    $source_functions_file = WP_PLUGIN_DIR . '/ai24-assistant-integrator/includes/functions.php';
    $destination_dir = WP_PLUGIN_DIR . '/AI24AI-child';
    $destination_main_file = $destination_dir . '/pluginmain.php';
    $destination_functions_file = $destination_dir . '/includes/functions.php';

    // Ensure source files exist
    if (!file_exists($source_main_file) || !file_exists($source_functions_file)) {
        wp_send_json_error('Source file(s) do not exist.');
        return;
    }

    // Create destination directory if it doesn't exist
    if (!file_exists($destination_dir)) {
        mkdir($destination_dir, 0755, true);
    }

    // Create includes directory if it doesn't exist
    if (!file_exists($destination_dir . '/includes')) {
        mkdir($destination_dir . '/includes', 0755, true);
    }

    // Clean up any existing files in the destination directory
    if (file_exists($destination_main_file)) {
        unlink($destination_main_file);
    }
    if (file_exists($destination_functions_file)) {
        unlink($destination_functions_file);
    }

    // Copy main plugin file to the destination directory
    if (!copy($source_main_file, $destination_main_file)) {
        wp_send_json_error('Failed to generate main plugin file.');
        return;
    }

    // Copy functions file to the destination includes directory
    if (!copy($source_functions_file, $destination_functions_file)) {
        wp_send_json_error('Failed to generate functions file.');
        return;
    }

    wp_send_json_success('Plugin files generated successfully.');
});


// AJAX handler for regenerating the functions file
add_action('wp_ajax_AI24AI_regenerate_function', function() {
    // Paths for source and destination files
    $source_main_file = WP_PLUGIN_DIR . '/ai24-assistant-integrator/includes/pluginmain.php';
    $source_functions_file = WP_PLUGIN_DIR . '/ai24-assistant-integrator/includes/functions.php';
    $destination_main_file = WP_PLUGIN_DIR . '/AI24AI-child/pluginmain.php';
    $destination_functions_file = WP_PLUGIN_DIR . '/AI24AI-child/includes/functions.php';

    // Ensure source files exist
    if (!file_exists($source_main_file) || !file_exists($source_functions_file)) {
        wp_send_json_error('Source file(s) do not exist.');
        return;
    }

    // Clean up any existing files in the destination directory
    if (file_exists($destination_main_file)) {
        unlink($destination_main_file);
    }
    if (file_exists($destination_functions_file)) {
        unlink($destination_functions_file);
    }

    // Copy main plugin file to the destination directory
    if (!copy($source_main_file, $destination_main_file)) {
        wp_send_json_error('Failed to regenerate main plugin file.');
        return;
    }

    // Copy functions file to the destination includes directory
    if (!copy($source_functions_file, $destination_functions_file)) {
        wp_send_json_error('Failed to regenerate functions file.');
        return;
    }

    wp_send_json_success('Plugin files regenerated successfully.');
});


add_action('wp_ajax_AI24AI_duplicate_function_file', function() {
    // Check for required parameters
    if (!isset($_POST['file_name']) || empty($_POST['file_name'])) {
        wp_send_json_error('File name is required.');
        return;
    }

    $file_name = sanitize_text_field($_POST['file_name']);
    $source_file = WP_PLUGIN_DIR . '/AI24AI-child/includes/functions.php';
    $destination_file = WP_PLUGIN_DIR . '/AI24AI-child/includes/' . $file_name . '-' . time() . '.php';

    // Ensure the source file exists
    if (!file_exists($source_file)) {
        wp_send_json_error('Source file does not exist.');
        return;
    }

    // Copy the source file to the new destination
    if (!copy($source_file, $destination_file)) {
        wp_send_json_error('Failed to duplicate file.');
        return;
    }

    wp_send_json_success('File duplicated successfully.');
});



// Enable widget and page view handling

// Callback function for the enable/disable widget field
function AI24AI_enable_widget_callback() {
    $enable_widget = get_option('AI24AI_enable_widget', 'no'); 
    ?>
    <label class="switch">
        <input type="checkbox" id="AI24AI_enable_widget" name="AI24AI_enable_widget" value="yes" <?php checked($enable_widget, 'yes'); ?> />
        <span class="slider round"></span>
    </label>
    <label for="AI24AI_enable_widget">Enable Widget</label>
    <?php
}


// Sanitize the page selection input
function AI24AI_sanitize_pages($input) {
    return array_map('intval', (array) $input);
}



function AI24AI_widget_pages_callback() {
    $widget_pages = get_option('AI24AI_widget_pages', array());
    $pages = get_pages();
    $show_hide_mode = get_option('AI24AI_widget_show_hide_mode', 'show'); // Default to 'show'
    ?>
    <div class="switcher-list-container">
        <label for="show-hide-mode">Mode:</label>
        <select id="show-hide-mode" name="AI24AI_widget_show_hide_mode">
            <option value="show" <?php selected($show_hide_mode, 'show'); ?>>Show on selected pages</option>
            <option value="hide" <?php selected($show_hide_mode, 'hide'); ?>>Hide on selected pages</option>
        </select>
        <input type="text" id="page-search" placeholder="Search pages..." onkeyup="filterPages()">
        <div class="switcher-list-wrapper">
            <div class="switcher-list" id="switcher-list">
                <?php foreach ($pages as $page) : ?>
                    <div class="switcher-item">
                        <label class="switch">
                            <input type="checkbox" name="AI24AI_widget_pages[]" value="<?php echo esc_attr($page->ID); ?>" <?php checked(in_array($page->ID, $widget_pages)); ?> />
                            <span class="slider round"></span>
                        </label>
                        <span><?php echo esc_html($page->post_title); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <p class="description">Select the pages where the widget should be displayed or hidden based on the selected mode. Leave all unchecked to display on all pages.</p>
    <?php
}
 


// Callback functions for context message and toggle 
function AI24AI_context_message_callback($args) {
    $index = $args['index'];
    // Set a default message for the first context message box
    $default_message = $index == 1 ? 'Hello, how are you! What can I do for you today?' : '';
    $context_message = get_option("AI24AI_assistant_context_message_$index", $default_message);
    
    echo "<textarea id='" . esc_attr("AI24AI_assistant_context_message_$index") . "' name='" . esc_attr("AI24AI_assistant_context_message_$index") . "' class='large-text'>" . esc_textarea($context_message) . "</textarea>";
}

//function AI24AI_widget_styling_callback() {
//    echo '<h1 class="widget-styling-title">Widget Styling</h1>';
//}

// Callback for the 'Widget Position' setting field
function AI24AI_widget_position_callback() {
    $widget_position = get_option('AI24AI_widget_position', 'corner');
    ?>
    <fieldset>
        <label>
            <input type="radio" name="AI24AI_widget_position" value="corner" <?php checked($widget_position, 'corner'); ?>> Corner
        </label><br>
        <label>
            <input type="radio" name="AI24AI_widget_position" value="sidebar" <?php checked($widget_position, 'sidebar'); ?>> Sidebar
        </label>
    </fieldset>
    <?php
}

function AI24AI_context_toggle_callback($args) {
    $index = $args['index'];
    $context_toggle = get_option("AI24AI_assistant_context_toggle_$index", 'off');
    
    // Always on and disabled for the first context message
    if ($index == 1) {
        echo "<input type='checkbox' id='" . esc_attr("AI24AI_assistant_context_toggle_$index") . "' name='" . esc_attr("AI24AI_assistant_context_toggle_$index") . "' value='on' checked disabled />";
        // Optionally, add a hidden field to ensure the value gets posted
        echo "<input type='hidden' name='" . esc_attr("AI24AI_assistant_context_toggle_$index") . "' value='on'>";
    } else {
        $checked = $context_toggle === 'on' ? 'checked' : '';
        echo "<input type='checkbox' id='" . esc_attr("AI24AI_assistant_context_toggle_$index") . "' name='" . esc_attr("AI24AI_assistant_context_toggle_$index") . "' value='on' " . esc_attr($checked) . " />";
    }
}

function AI24AI_api_keys_callback() {
    $apiKeys = get_option('AI24AI_api_keys');
    if (!is_array($apiKeys)) {
        $apiKeys = [];
    }
    echo '<p>Here you can add as many secret keys as you need.</p>';
    echo '<div id="api_keys_container">';

    foreach ($apiKeys as $index => $details) {
        echo '<div class="api_key_field" style="margin-bottom: 20px;">';
        echo '<div style="margin-bottom: 5px;">';
        echo '<label for="api_key_name_' . esc_attr($index) . '">Identifier:</label>';
        echo '<input type="text" id="api_key_name_' . esc_attr($index) . '" name="AI24AI_api_keys[' . esc_attr($index) . '][name]" value="' . (isset($details['name']) ? esc_attr($details['name']) : '') . '" class="regular-text" />';
        echo '</div>';

        echo '<div class="api_key_value_wrapper" style="margin-bottom: 5px;">';
        echo '<label for="api_key_value_' . esc_attr($index) . '">Secret:</label>';
        echo '<input type="password" id="api_key_value_' . esc_attr($index) . '" name="AI24AI_api_keys[' . esc_attr($index) . '][key]" value="' . (isset($details['key']) ? esc_attr($details['key']) : '') . '" class="regular-text" />';
        echo '<button type="button" class="toggle_visibility button">Show</button>'; // Show button
        echo '</div>';

        echo '<button type="button" class="remove_field button">Remove</button>';
        echo '</div>';
    }

    echo '<button type="button" id="add_more_keys" class="button">Add More Keys</button>';
    echo '</div>'; // Close the container
}



function AI24AI_api_key_callback() {
    $apiKey = get_option('AI24AI_api_key');
    $locked = !empty($apiKey);

    echo '<div class="api_key_field" style="margin-bottom: 20px;">';
    echo '<div class="api_key_value_wrapper" style="margin-bottom: 5px; display: flex; align-items: center;">';
    echo '<label for="AI24AI_api_key" style="margin-right: 10px;">OpenAI API Key:</label>';
    echo '<input type="password" id="AI24AI_api_key" name="AI24AI_api_key" value="' . esc_attr($apiKey) . '" class="regular-text" style="margin-right: 10px;" ' . ($locked ? 'readonly' : '') . ' />';
    echo '<button type="button" class="toggle_visibility button" style="margin-right: 10px;">Show</button>';
    echo '<label><input type="checkbox" onclick="toggleLock(this, \'#AI24AI_api_key\')" ' . ($locked ? '' : 'checked') . '> Unlock</label>';
    echo '</div>';
    echo '</div>';
}



function AI24AI_assistant_id_callback() {
    $assistantId = get_option('AI24AI_assistant_id');
    $locked = !empty($assistantId);

    echo '<div class="api_key_field" style="margin-bottom: 20px;">';
    echo '<div class="api_key_value_wrapper" style="margin-bottom: 5px; display: flex; align-items: center;">';
    echo '<label for="AI24AI_assistant_id" style="margin-right: 10px;">Assistant ID:</label>';
    echo '<input type="password" id="AI24AI_assistant_id" name="AI24AI_assistant_id" value="' . esc_attr($assistantId) . '" class="regular-text" style="margin-right: 10px;" ' . ($locked ? 'readonly' : '') . ' />';
    echo '<button type="button" class="toggle_visibility button" style="margin-right: 10px;">Show</button>';
    echo '<label><input type="checkbox" onclick="toggleLock(this, \'#AI24AI_assistant_id\')" ' . ($locked ? '' : 'checked') . '> Unlock</label>';
    echo '</div>';
    echo '</div>';
}



add_action('admin_init', function () {
    if (isset($_POST['option_page']) && $_POST['option_page'] == 'AI24AI_options_group') {
        // Sanitize and verify the nonce first to ensure the form submission is valid
        if (!isset($_POST['AI24AI_settings_nonce'])) {
            wp_die(esc_html__('Nonce not set.', 'ai24-assistant-integrator'));
        }

        $nonce = sanitize_text_field(wp_unslash($_POST['AI24AI_settings_nonce']));
        if (!wp_verify_nonce($nonce, 'AI24AI_settings_action')) {
            wp_die(esc_html__('Security check failed.', 'ai24-assistant-integrator'));
        }

        // Process the form since the nonce is verified.
        if (isset($_POST['AI24AI_api_key']) && $_POST['AI24AI_api_key'] !== '') {
            update_option('AI24AI_api_key', sanitize_text_field($_POST['AI24AI_api_key']));
        }
        if (isset($_POST['AI24AI_assistant_id']) && $_POST['AI24AI_assistant_id'] !== '') {
            update_option('AI24AI_assistant_id', sanitize_text_field($_POST['AI24AI_assistant_id']));
        }
    }
});

// Display the settings errors
add_action('admin_notices', function() {
    settings_errors('AI24AI_messages');
}); 

// Show a confirmation message when settings are saved
add_action('admin_notices', function () {
    if (get_transient('settings_saved')) {
        echo '<div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>';
        delete_transient('settings_saved');
    }
});

// Define callback functions for each field
function AI24AI_assistant_name_callback() {
    $name = get_option('AI24AI_assistant_name');
    echo '<input type="text" id="AI24AI_assistant_name" name="AI24AI_assistant_name" value="' . esc_attr($name) . '" class="regular-text" />';
}

function AI24AI_assistant_description_callback() {
    $description = get_option('AI24AI_assistant_description');
    echo '<input type="text" id="AI24AI_assistant_description" name="AI24AI_assistant_description" value="' . esc_attr($description) . '" class="regular-text" />';
}

function AI24AI_assistant_context_message_callback() {
    $context_message = get_option('AI24AI_assistant_context_message');
    echo '<textarea id="AI24AI_assistant_context_message" name="AI24AI_assistant_context_message" class="large-text">' . esc_textarea($context_message) . '</textarea>';
}

// Placeholder for settings section callback function
function AI24AI_styling_settings_callback() {
    // Output any description or header you want for the Assistant Styling section.
    echo '<p>Customize the appearance and behavior of the virtual assistant here.</p>';
}

function AI24AI_placement_settings_callback() {
    echo '<p>Select where you want the virtual assistant to appear on your site.</p>';
}

function AI24AI_placement_options_callback() {
    $placement_options = get_option('AI24AI_placement_options');
    // Assuming $placement_options is an array with the selected options
    ?>
    <select id="AI24AI_placement_options" name="AI24AI_placement_options[]" multiple>
        <option value="all_pages" <?php echo in_array('all_pages', $placement_options) ? 'selected' : ''; ?>>All Pages</option>
        <option value="front_page" <?php echo in_array('front_page', $placement_options) ? 'selected' : ''; ?>>Front Page</option>
        <!-- Add more options as needed -->
    </select>
    <p class="description">Hold down the Ctrl (Windows) or Command (Mac) button to select multiple options.</p>
    <?php
}

function AI24AI_assistant_image_callback() {
    $image = get_option('AI24AI_assistant_image');
    ?>
    <input type="text" id="AI24AI_assistant_image" name="AI24AI_assistant_image" value="<?php echo esc_attr($image); ?>" class="regular-text" />
    <button type="button" class="button" id="AI24AI_assistant_image_button"><?php esc_html_e('Upload Image', 'ai24-assistant-integrator'); ?></button>
    <?php
}

// Enqueue the color picker JS and styles
function AI24AI_enqueue_color_picker($hook_suffix) {
    if ('toplevel_page_AI24AI' !== $hook_suffix) {
        return;
    }
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    // You might want to add your own JavaScript file to initialize the color picker
    // wp_enqueue_script('AI24AI-color-picker-js', plugins_url('color-picker-init.js', __FILE__), array('wp-color-picker'), false, true);
}

add_action('admin_enqueue_scripts', 'AI24AI_enqueue_color_picker');

// Callback for the 'Header Color' field
function AI24AI_header_color_callback() {
    $header_color = get_option('AI24AI_header_color', '#632289'); // Default color
    echo '<input type="text" id="AI24AI_header_color" name="AI24AI_header_color" value="' . esc_attr($header_color) . '" class="my-color-field" data-default-color="#632289" />';
}

// Callback for the 'Widget Corner Color' field
function AI24AI_widget_corner_color_callback() {
    $widget_corner_color = get_option('AI24AI_widget_corner_color', '#632289'); // Default color
    echo '<input type="text" id="AI24AI_widget_corner_color" name="AI24AI_widget_corner_color" value="' . esc_attr($widget_corner_color) . '" class="my-color-field" data-default-color="#632289" />';
}

// Callback for the 'Input Border Glow Color' field
function AI24AI_input_border_glow_color_callback() {
    $input_border_glow_color = get_option('AI24AI_input_border_glow_color', '#632289'); // Default color
    echo '<input type="text" id="AI24AI_input_border_glow_color" name="AI24AI_input_border_glow_color" value="' . esc_attr($input_border_glow_color) . '" class="my-color-field" data-default-color="#632289" />';
}

function AI24AI_icon_color_callback() {
    $icon_text_color = get_option('AI24AI_icon_color', '#ffffff'); // Default to white

    ?>
    <fieldset>
        <legend class="screen-reader-text"><span>Icon and Text Color</span></legend>
        <p>Choose a color for the icon or text:</p>
        <label>
            <input type="radio" name="AI24AI_icon_color" value="#000000" <?php checked($icon_text_color, '#000000'); ?>>
            <span>Black</span>
        </label><br>
        <label>
            <input type="radio" name="AI24AI_icon_color" value="#ffffff" <?php checked($icon_text_color, '#ffffff'); ?>>
            <span>White</span>
        </label>
    </fieldset>
    <?php
}

function AI24AI_generate_global_custom_css() {
    $header_color = get_option('AI24AI_header_color', '#632289');
    $widget_corner_color = get_option('AI24AI_widget_corner_color', '#632289');
    $icon_text_color = get_option('AI24AI_icon_color', '#000000');
    $input_border_glow_color = get_option('AI24AI_input_border_glow_color', '#632289');
    $widget_position = get_option('AI24AI_widget_position', 'corner'); // Retrieve the widget position
    $sidebar_content = get_option('AI24AI_sidebar_content', 'icon');

    $custom_css = "
        #chatbox-header { background-color: " . esc_attr($header_color) . " !important; }
        #AI24AI-chatbot-toggle { background-color: " . esc_attr($widget_corner_color) . "; }
        #AI24AI-chatbot-toggle img { filter: " . esc_attr($icon_text_color === '#ffffff' ? 'invert(100%)' : 'none') . "; }
        #chat-input:focus { box-shadow: 0 0 5px " . esc_attr($input_border_glow_color) . "; }
        .AI24AI-chatbot-toggle-text {
            transform: rotate(-90deg);
            font-size: 1.2rem;
            font-weight: bold;
            position: absolute;
            text-align: center;
            line-height: 1;
        }
        .AI24AI-chatbot-toggle-text { color: " . esc_attr($icon_text_color) . " !important; }
    ";

    return $custom_css;
}

function AI24AI_generate_sidebar_custom_css() {
    $custom_css = "
    @keyframes slideInToggle {
        from {
            right: -120px; /* Start from off-screen */
        }
        to {
            right: 0; /* Slide to its normal position on page load */
        }
    }
    
    @keyframes slideOutToggle {
        from {
            right: 0; /* Start from the visible position */
        }
        to {
            right: -120px; /* Slide off-screen */
        }
    }
    
    #AI24AI-chatbot-toggle {
        position: fixed;
        top: 50%;
        right: -120px; /* Start off-screen to prepare for the slide-in animation */
        width: 50px;
        height: 120px;
        border-radius: 18px 0 0 18px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        color: #ffffff;
        z-index: 5000;
        opacity: 1 !important;
        animation: slideInToggle 0.7s ease-out forwards; 
        animation-delay: 0.25s;
    }
    
    #AI24AI-chatbot-toggle.hide-toggle {
        animation: slideOutToggle 0.5s ease-out forwards; /* Animation to slide out when .hide-toggle is applied */
    }
    
    #AI24AI-chatbot-toggle:hover::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.2);
        border-radius: 18px 0 0 18px;
        pointer-events: none;
    }
    
        #AI24AI-chatbot-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 425px;
            height: 700px;
            max-width: 80%;
            max-height: 80vh;
            background-color: #fff;
            box-shadow: 0 8px 12px rgba(0,0,0,0.1);
            border-radius: 15px;
            z-index: 9998;
            overflow: hidden;
            transform: translateY(200px);
            opacity: 0;
            transition: transform 0.5s ease, opacity 0.6s ease;
            display: none;
            overflow: none;
        }
        @media (max-width: 520px) {
            html {
                font-size: 16px; /* Base font size adjustment */
            }
            body, html {
                margin: 0;
                padding: 0;
            }
            #AI24AI-chatbot-container {
                width: calc(100% - 20px); /* Full width minus the padding */
                height: calc(100% - 20px); /* Full height minus the padding */
                max-width: none; /* Override the max-width */
                max-height: none; /* Override the max-height */
                bottom: 10px; /* Padding from the bottom */
                right: 10px; /* Padding from the right */
                top: 10px; /* Padding from the top */
                left: 10px; /* Padding from the left */;
                z-index: 1000;
            }
            #chatbox-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px;
            }
        }
    ";
    return $custom_css;
}

function AI24AI_enqueue_front_end_styles() {
    // Retrieve the widget position from the options
    $widget_position = get_option('AI24AI_widget_position', 'corner');

    // Enqueue the primary CSS
    wp_enqueue_style(
        'AI24AI-style',
        AI24AI_PLUGIN_URL . 'assets/css/AI24AI-style.css',
        array(),
        filemtime(plugin_dir_path(__DIR__) . 'assets/css/AI24AI-style.css')
    );

    // Generate and enqueue global custom CSS
    $global_custom_css = AI24AI_generate_global_custom_css();
    wp_add_inline_style('AI24AI-style', $global_custom_css);

    // Check if the widget position is 'sidebar'
    if ($widget_position === 'sidebar') {
        // Define the full URL to the settings CSS file
        $css_file_url = AI24AI_PLUGIN_URL . 'assets/css/settings-style.css';

        // Define the full path to the settings CSS file
        $css_file_path = plugin_dir_path(__DIR__) . 'assets/css/settings-style.css';

        // Debugging output
        //error_log('CSS File URL: ' . $css_file_url);
        //error_log('CSS File Path: ' . $css_file_path);
        //error_log('File Exists: ' . (file_exists($css_file_path) ? 'Yes' : 'No'));

        // Check if the file exists
        if (file_exists($css_file_path)) {
            // Enqueue the style with a version number based on file modification time
            wp_enqueue_style(
                'AI24AI-settings-style',
                $css_file_url,
                array('AI24AI-style'),
                filemtime($css_file_path) // Use filemtime if the file exists
            );
            //error_log('Enqueued AI24AI-settings-style with version: ' . filemtime($css_file_path));
        } else {
            // Enqueue the style with a static version number as fallback
            wp_enqueue_style(
                'AI24AI-settings-style',
                $css_file_url,
                array('AI24AI-style'),
                '1.0.0'
            );
            error_log('Enqueued AI24AI-settings-style with static version 1.0.0');
        }

        // Generate and enqueue sidebar-specific custom CSS
        $sidebar_custom_css = AI24AI_generate_sidebar_custom_css();
        wp_add_inline_style('AI24AI-settings-style', $sidebar_custom_css);
    } else {
        //error_log('The widget position is not sidebar, so the sidebar-specific style was not enqueued.');
    }
}
add_action('wp_enqueue_scripts', 'AI24AI_enqueue_front_end_styles');

function AI24AI_toggle_image_callback() {
    // Retrieve the current value of the setting.
    $image_url = get_option('AI24AI_toggle_image', AI24AI_PLUGIN_URL . 'assets/images/messagebubble.svg');

    // Output the HTML for the setting.
    echo '<input type="text" id="AI24AI_toggle_image" name="AI24AI_toggle_image" value="' . esc_attr($image_url) . '" class="regular-text" />';
    echo '<button type="button" class="button" id="AI24AI_toggle_image_button">' . esc_html__('Upload Image', 'ai24-assistant-integrator') . '</button>';
    echo '<img src="' . esc_url($image_url) . '" id="AI24AI_toggle_image_preview" style="max-width: 100px; max-height: 100px; display: block; margin-top: 10px;" />';
}

function AI24AI_sidebar_content_callback() {
    $sidebar_content = get_option('AI24AI_sidebar_content', 'icon');
    $sidebar_text = get_option('AI24AI_sidebar_text', '');
    $initial_display_style = get_option('AI24AI_widget_position', 'corner') === 'corner' ? 'style="display: none;"' : '';
    ?>
    <div id="sidebar-content-section" <?php echo esc_attr($initial_display_style); ?>>
        <h2>Sidebar Content</h2>
        <fieldset>
            <legend class="screen-reader-text"><span>Sidebar Content</span></legend>
            <label>
                <input type="radio" name="AI24AI_sidebar_content" value="icon" <?php checked($sidebar_content, 'icon'); ?>>
                <span>Icon</span>
            </label><br>
            <label>
                <input type="radio" name="AI24AI_sidebar_content" value="text" <?php checked($sidebar_content, 'text'); ?>>
                <span>Text</span>
            </label>
        </fieldset>
        <div id="sidebar-content-text" style="display: none;">
            <label for="AI24AI_sidebar_text">Sidebar Text:</label>
            <input type="text" id="AI24AI_sidebar_text" name="AI24AI_sidebar_text" value="<?php echo esc_attr($sidebar_text); ?>" class="regular-text" maxlength="50" />
        </div>
    </div>
    <?php
}

// Callback function for the 'functions' section header.
function AI24AI_functions_section_callback() {
    echo '<p>Select the type of API handler you wish to use.</p>';
}

// Function to get dynamic plugin file path
function get_dynamic_plugin_file_path() {
    return 'includes/functions.php'; 
}

function AI24AI_functions_field_callback() {
    $options = get_option('AI24AI_functions_option');
    $handler_type = is_array($options) && isset($options['handler_type']) ? $options['handler_type'] : 'non_functions';
    ?>
    <input type="radio" id="non_functions" name="AI24AI_functions_option[handler_type]" value="non_functions" <?php checked('non_functions', $handler_type, true); ?>>
    <label for="non_functions">API handler V1</label><br>
    
    <input type="radio" id="functions" name="AI24AI_functions_option[handler_type]" value="functions" <?php checked('functions', $handler_type); ?>>
    <label for="functions">API handler V2</label>

    <?php
}

// Make sure to call register_settings in your admin_init hook
add_action('admin_init', 'AI24AI_register_settings');





// TUTORIALS SECTION

// Callback for the tutorials section
function AI24AI_tutorials_section_callback() {
    echo '<p>Video tutorials to help you make the most of the AI24 Assistant Integrator and OpenAI assistants in general.</p>';
}

// Callback for What is AI24
function AI24AI_tutorial_video_callback() {
    // Embed YouTube video https://youtu.be/blGeFZOvncY
    echo '<iframe width="560" height="315" src="https://www.youtube.com/embed/blGeFZOvncY" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
}

// The callback for what are OpenAI functions
function AI24AI_tutorial_video2_callback() {
    // Embed YouTube video https://youtu.be/JIv3dnx7S30
    echo '<iframe width="560" height="315" src="https://www.youtube.com/embed/JIv3dnx7S30" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
}

// The callback for How to make functions for OpenAI assistants 
function AI24AI_tutorial_video3_callback() {
    // Embed YouTube video https://youtu.be/pOIWTjgv9Sc
    echo '<iframe width="560" height="315" src="https://www.youtube.com/embed/pOIWTjgv9Sc" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
}

// The callback for How to ACTUALLY make an OpenAI assistant 
function AI24AI_tutorial_video4_callback() {
    // Embed YouTube video https://youtu.be/Io_HckHRxow
    echo '<iframe width="560" height="315" src="https://www.youtube.com/embed/Io_HckHRxow" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
}






function AI24AI_sanitize_api_keys($keys) {
    $sanitized_keys = [];

    if (is_array($keys)) {
        foreach ($keys as $key => $details) {
            if (is_array($details) && isset($details['name']) && isset($details['key'])) {
                $sanitized_key = [
                    'name' => sanitize_text_field($details['name']),
                    'key' => sanitize_text_field($details['key']), // Consider encrypting this value
                ];
                $sanitized_keys[$key] = $sanitized_key;
            }
        }
    }

    return $sanitized_keys;
}





//SETTINGS FOR TEXT OPTIONS

/*//Settings for all the font selection and forcing
// Font settings section callback
//function AI24AI_font_settings_callback() {
    echo '<p>' . __('Configure the font settings for the chatbot.', 'AI24AI') . '</p>';
}

// Sanitize settings input
function AI24AI_sanitize_settings($input) {
    $sanitized_input = array();
    $sanitized_input['font'] = sanitize_text_field($input['font']);
    error_log('Sanitized font: ' . $sanitized_input['font']);  // Debug log
    return $sanitized_input;
}

// Font field callback
function AI24AI_font_field_callback() {
    $options = get_option('AI24AI_font_settings');
    $fonts = array(
        'AI24AIKumbhsans' => 'Kumbh Sans',
        'Arial' => 'Arial',
        'Helvetica' => 'Helvetica',
        'Times New Roman' => 'Times New Roman',
        'Georgia' => 'Georgia',
        'Verdana' => 'Verdana',
        'Tahoma' => 'Tahoma',
        'Courier New' => 'Courier New',
        'Lucida Console' => 'Lucida Console',
        'Monaco' => 'Monaco',
        'serif' => 'Serif',
        'sans-serif' => 'Sans-serif',
        'monospace' => 'Monospace',
        'cursive' => 'Cursive',
        'fantasy' => 'Fantasy',
    );
    $selected_font = isset($options['font']) ? $options['font'] : 'AI24AIKumbhsans'; // Default to Kumbh Sans
    error_log('Current selected font in callback: ' . $selected_font);  // Debug log
    ?>
    <select name="AI24AI_font_settings[font]">
        <?php foreach ($fonts as $key => $value) { ?>
            <option value="<?php echo esc_attr($key); ?>" <?php selected($selected_font, $key); ?>><?php echo esc_html($value); ?></option>
        <?php } ?>
    </select>
    <p class="description">Select a font from the available options.</p>
    <?php
}
*/

//MARKDOWN SETTINGS
function AI24AI_text_options_callback() {
    echo '<p>' . __('Configure the text options for the chatbot.', 'AI24AI') . '</p>';
}

function AI24AI_markdown_enabled_callback() {
    $options = get_option('AI24AI_text_options');
    ?>
    <label class="switch">
        <input type="checkbox" name="AI24AI_text_options[markdown_enabled]" value="1" <?php checked(1, isset($options['markdown_enabled']) ? $options['markdown_enabled'] : 1); ?> />
        <span class="slider round"></span>
    </label>
    <p class="description">Enable or disable Markdown processing in the chatbot.</p>
    <?php
}

function AI24AI_sanitize_text_options($input) {
    $sanitized_input = array();
    $sanitized_input['markdown_enabled'] = isset($input['markdown_enabled']) ? (int)$input['markdown_enabled'] : 0;
    return $sanitized_input;
}



//FUNCTIONS/CALLBACKS TO DO WITH WIDGET TEXT



function AI24AI_exit_confirmation_text_callback() {
    $exit_text = get_option('AI24AI_exit_confirmation_text');
    echo '<input type="text" id="AI24AI_exit_confirmation_text" name="AI24AI_exit_confirmation_text" value="' . esc_attr($exit_text) . '" class="regular-text" placeholder="Are you sure you want to exit? Your current conversation will be lost." />';
}


function AI24AI_confirm_exit_button_callback() {
    $confirm_button = get_option('AI24AI_confirm_exit_button');
    echo '<input type="text" id="AI24AI_confirm_exit_button" name="AI24AI_confirm_exit_button" value="' . esc_attr($confirm_button) . '" class="regular-text" placeholder="Yes, exit" />';
}


function AI24AI_cancel_exit_button_callback() {
    $cancel_button = get_option('AI24AI_cancel_exit_button');
    echo '<input type="text" id="AI24AI_cancel_exit_button" name="AI24AI_cancel_exit_button" value="' . esc_attr($cancel_button) . '" class="regular-text" placeholder="No, cancel" />';
}


function AI24AI_chat_input_placeholder_callback() {
    $placeholder = get_option('AI24AI_chat_input_placeholder');
    echo '<input type="text" id="AI24AI_chat_input_placeholder" name="AI24AI_chat_input_placeholder" value="' . esc_attr($placeholder) . '" class="regular-text" placeholder="Type your message here..." />';
}