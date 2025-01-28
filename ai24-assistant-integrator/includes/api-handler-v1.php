<?php



if (!defined('ABSPATH')) exit;
if (file_exists(AI24AI_CHILD_FUNCTIONS_FILE)) {
    require_once AI24AI_CHILD_FUNCTIONS_FILE;
}



//Function to handle source tags in GPT responses
function AI24AI_clean_gpt_response($text) {
    $pattern = '/【\d+(:\d+)?†source】/';
    $cleaned_text = preg_replace($pattern, '', $text);
    return $cleaned_text;
}



// Define a function to get the headers
function get_openai_headers() {
    $api_key = get_option('AI24AI_api_key'); // Retrieve API key from WP options
    return [
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type' => 'application/json',
        'OpenAI-Beta' => 'assistants=v1'
    ];
}



function AI24AI_process_markdown_and_links($message) {
    $options = get_option('AI24AI_text_options', array('markdown_enabled' => 1));

    if (isset($options['markdown_enabled']) && $options['markdown_enabled']) {
        // Initialize Parsedown only once
        static $Parsedown = null;
        if ($Parsedown === null) {
            $Parsedown = new Parsedown();
        }
        // Convert Markdown to HTML
        $message = $Parsedown->text($message);
    } else {
        // Convert Markdown to plain text, keep links
        $message = AI24AI_strip_markdown($message);
        $message = AI24AI_convert_links($message);
    }

    return $message;
}



function AI24AI_strip_markdown($text) {
    // Strip Markdown syntax to plain text but keep links
    $text = preg_replace('/(\*\*|__)(.*?)\1/', '\2', $text); // bold
    $text = preg_replace('/(\*|_)(.*?)\1/', '\2', $text); // italic
    $text = preg_replace('/\#(.*?)\n/', '\1\n', $text); // headers
    $text = preg_replace('/\n\-(.*?)\n/', '\1\n', $text); // lists
    return wp_strip_all_tags($text); // Strip any remaining HTML tags
}



function AI24AI_convert_links($text) {
    // Convert Markdown links to HTML links
    $text = preg_replace('/\[([^\]]+)\]\((http[s]?:\/\/[^\s]+)\)/', '<a href="$2" target="_blank">$1</a>', $text);
    return $text;
}



// Function to execute cURL request
function AI24AI_execute_openai_request($url, $method = 'GET', $data = null) {
    $args = [
        'headers' => get_openai_headers(),
        'method' => $method,
    ];

    if ($data) {
        $args['body'] = wp_json_encode($data);
        $args['data_format'] = 'body';
    }

    $response = ($method === 'GET') ? wp_remote_get($url, $args) : wp_remote_post($url, $args);

    if (is_wp_error($response)) {
        error_log('OpenAI API request failed: ' . $response->get_error_message());
        return null;
    }

    $response_body = wp_remote_retrieve_body($response);
    $response_data = json_decode($response_body, true);
    if (isset($response_data['error'])) {
        error_log('OpenAI API Error: ' . $response_data['error']['message']);
        return null;
    }

    return $response_data;
}



// Function to create thread 
function AI24AI_create_thread($platform = 'AI24AI') {
    $base_api_url = "https://api.openai.com/v1";
    $endpoint = "/threads";
    $url = $base_api_url . $endpoint;

    $response_data = AI24AI_execute_openai_request($url, 'POST', new stdClass());

    if ($response_data) {
        $thread_id = $response_data['id'] ?? null;
        if ($thread_id) {
            if (function_exists('AI24AI_add_thread_to_db')) {
                AI24AI_add_thread_to_db($thread_id, $platform);
            }
            if (function_exists('AI24AI_store_thread_id_in_session')) {
                AI24AI_store_thread_id_in_session($thread_id);
            }
            return $thread_id;
        } else {
            error_log('Failed to get thread ID from response.');
            return null;
        }
    }
    return null;
}



// Function to send messages to thread 
function AI24AI_send_message_to_thread($thread_id, $user_input) {
    $base_api_url = "https://api.openai.com/v1";
    $endpoint = "/threads/{$thread_id}/messages";
    $url = $base_api_url . $endpoint;

    $data = [
        'role' => 'user',
        'content' => $user_input
    ];

    $response_data = AI24AI_execute_openai_request($url, 'POST', $data);

    if ($response_data) {
        // Assuming successful message sending, log the message ID
        $message_id = $response_data['id'] ?? null;
        if ($message_id) {
            return $message_id;
        } else {
            error_log('Failed to get message ID from response.');
            return null;
        }
    }
    return null;
}



// Function to create a run with designated ASSISTANT ID
function AI24AI_create_run($thread_id, $assistant_id) {
    $base_api_url = "https://api.openai.com/v1";
    $create_endpoint = "/threads/{$thread_id}/runs";
    $create_url = $base_api_url . $create_endpoint;

    // Data for creating the run
    $create_data = [
        'assistant_id' => $assistant_id,
        // Optionally add 'model', 'instructions', 'tools' here if needed
    ];

    // Create run
    $create_response_data = AI24AI_execute_openai_request($create_url, 'POST', $create_data);

    if ($create_response_data) {
        $run_id = $create_response_data['id'] ?? null;
        if (!$run_id) {
            error_log('Failed to get run ID from response.');
            return null;
        }

        // Retrieve run for detailed logging
        $retrieve_endpoint = "/threads/{$thread_id}/runs/{$run_id}";
        $retrieve_url = $base_api_url . $retrieve_endpoint;
        $retrieve_response_data = AI24AI_execute_openai_request($retrieve_url, 'GET');

        if ($retrieve_response_data) {
            return $run_id;
        } else {
            error_log('Failed to retrieve run details.');
            return $run_id;
        }
    }

    return null;
}



// Function to poll for a response, retrieve run steps, and messages if completed
function AI24AI_poll_for_response_and_retrieve_details($thread_id, $run_id) {
    $api_key = get_option('AI24AI_api_key'); // Retrieve API key
    $base_api_url = "https://api.openai.com/v1";
    $polling_interval = 2;
    $max_attempts = 40; 
    $attempt = 0;

    while ($attempt < $max_attempts) {
        $attempt++;

        $run_status_response = AI24AI_execute_openai_request("{$base_api_url}/threads/{$thread_id}/runs/{$run_id}", 'GET');

        if (!$run_status_response) {
            error_log('Error retrieving run status.');
            break; // Exit the loop in case of error
        }

        $run_status_data = $run_status_response;
        if (!isset($run_status_data['status'])) {
            error_log('Run status key not found in response.');
            continue; // Continue polling if status key is missing
        }

        // Handle 'requires_action' status
        if ($run_status_data['status'] === 'requires_action') {
            if (file_exists(AI24AI_CHILD_FUNCTIONS_FILE)) {
                require_once AI24AI_CHILD_FUNCTIONS_FILE;
            } else {
                // Fallback to the original functions.php if the child file does not exist
                require_once AI24AI_PLUGIN_DIR . 'includes/functions.php';
            }
            $tool_outputs = [];

            if (!empty($run_status_data['required_action']['submit_tool_outputs'])) {
                foreach ($run_status_data['required_action']['submit_tool_outputs']['tool_calls'] as $tool_call) {
                    $function_name = $tool_call['function']['name'];
                    $arguments = json_decode($tool_call['function']['arguments'], true);

                    // Ensure arguments are structured correctly as associative array
                    if (!is_array($arguments)) {
                        $arguments = [];
                    }

                    // Check if the function expects a WP_REST_Request
                    if (function_exists($function_name)) {
                        // Call the function with the arguments
                        if (is_array($arguments)) {
                            $result = call_user_func_array($function_name, $arguments);
                        } else {
                            $result = call_user_func($function_name, $arguments);
                        }

                        // Handle WP_REST_Response correctly
                        if ($result instanceof WP_REST_Response) {
                            $data = $result->get_data();
                        } else {
                            $data = $result;
                        }

                        $tool_outputs[] = [
                            'tool_call_id' => $tool_call['id'],
                            'output' => is_array($data) ? wp_json_encode($data) : (string) $data
                        ];
                    } else {
                        error_log("Function $function_name does not exist");
                        $tool_outputs[] = [
                            'tool_call_id' => $tool_call['id'],
                            'output' => wp_json_encode(['error' => "Function $function_name does not exist"])
                        ];
                    }
                }
            }

            // Submit tool outputs to OpenAI after processing all required actions
            if (!empty($tool_outputs)) {
                error_log('Final tool_outputs: ' . print_r($tool_outputs, true));
                AI24AI_submit_tool_outputs_to_openai($thread_id, $run_id, $tool_outputs);
            }
        }

        // If the run is completed or in_progress, retrieve the steps
        if (isset($run_status_data['status']) && in_array($run_status_data['status'], ['completed', 'in_progress'])) {
            $steps_response = AI24AI_execute_openai_request("{$base_api_url}/threads/{$thread_id}/runs/{$run_id}/steps", 'GET');

            if (!$steps_response) {
                error_log('Error retrieving steps.');
                break; // Exit the loop in case of error
            }

            $steps_data = $steps_response;
            foreach ($steps_data['data'] as $step) {
                // Process each step if necessary
            }

            // If the run completed, retrieve messages and return
            if ($run_status_data['status'] === 'completed') {
                $messages_response = AI24AI_execute_openai_request("{$base_api_url}/threads/{$thread_id}/messages", 'GET');
                
                if (!$messages_response) {
                    error_log('Error retrieving messages.');
                    // Handle error appropriately
                }

                $messages_data = $messages_response;
                $filtered_messages = [];
                
                foreach ($messages_data['data'] as $message) {
                    foreach ($steps_data['data'] as $step) {
                        if (strpos($step['id'], $message['id']) !== false) { 
                            $filtered_messages[] = $message;
                            break; 
                        }
                    }
                }   

                // Process the messages
                if (isset($messages_data['data']) && !empty($messages_data['data'])) {
                    $structured_messages = array_map(function($message) {
                        if ($message['role'] === 'assistant') {
                            if (is_array($message['content'])) {
                                foreach ($message['content'] as &$contentItem) {
                                    if (isset($contentItem['text']['value'])) {
                                        $cleaned_content = AI24AI_clean_gpt_response($contentItem['text']['value']);
                                        $contentItem['text']['value'] = AI24AI_process_markdown_and_links($cleaned_content);
                                    }
                                }
                            } else {
                                $cleaned_content = AI24AI_clean_gpt_response($message['content']);
                                $message['content'] = AI24AI_process_markdown_and_links($cleaned_content);
                            }
                        }
                        return $message; 
                    }, $messages_data['data']);

                    wp_send_json_success(['thread_id' => $thread_id, 'messages' => $structured_messages]);
                } else {
                    wp_send_json_error(['error' => 'No messages found.']);
                }
            }

        } elseif ($run_status_data['status'] === 'failed') {
            error_log("Run $run_id failed or was cancelled.");
            wp_send_json_error(['error' => 'Run failed or was cancelled.']);
            return; 
        }

        sleep($polling_interval);
    }
    
    error_log("Run $run_id did not complete in expected time.");
    wp_send_json_error(['error' => 'Run did not complete in expected time.']);
}



// Function to submit tool outputs to OpenAI
function AI24AI_submit_tool_outputs_to_openai($thread_id, $run_id, $tool_outputs) {
    $url = "https://api.openai.com/v1/threads/{$thread_id}/runs/{$run_id}/submit_tool_outputs";
    $data = [
        'tool_outputs' => $tool_outputs  // Make sure tool_outputs are formatted correctly
    ];

    $response_data = AI24AI_execute_openai_request($url, 'POST', $data);

    if ($response_data) {
        $response_code = $response_data['status'] ?? null;
        if ($response_code != 200) {
            $response_body = wp_json_encode($response_data);
            error_log("Failed to submit tool outputs: HTTP Status Code {$response_code}, Response: {$response_body}");
        }
    } else {
        error_log("Error submitting tool outputs.");
    }
}



// API handler function to fetch messages from OpenAI
function AI24AI_fetch_messages_from_openai($thread_id) {
    $url = "https://api.openai.com/v1/threads/{$thread_id}/messages";
    $response_data = AI24AI_execute_openai_request($url, 'GET');

    if (is_array($response_data) && isset($response_data['data'])) {
        return $response_data['data'];
    } else {
        error_log('Failed to fetch messages from OpenAI API.');
        return null;
    }
}