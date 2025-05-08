function generate_chat_response( $last_prompt, $conversation_history ) {

// OpenAI API URL and key
$api_url = '';
$api_key = 'AIzaSyDumkNL-gqahJImRsTa8kk87qnQQ0vGB_0';

// Headers for the OpenAI API
$headers = [
    'Content-Type' => 'application/json'
];

 

if (is_array($conversation_history)) {
        foreach ($conversation_history as $message) {
            $messages[] = [
                "role" => $message['role'],
                "parts" => [["text" => $message['content']]]
            ];
        }
    }

// Add the current prompt
$messages[] = [
        "role" => "user",
        "parts" => [["text" => $last_prompt]]
    ];
	

	
// Body for the Gemini API
$body = [
        "contents" => $messages
    ];

// Args for the WordPress HTTP API
$args = [
    'method' => 'POST',
    'headers' => $headers,
    'body' => json_encode($body),
    'timeout' => 30
];

// Send the request
$response = wp_remote_request($api_url, $args);

// Handle the response
if (is_wp_error($response)) {
    return $response->get_error_message();
} else {
    $response_body = wp_remote_retrieve_body($response);
    $data = json_decode($response_body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'message' => 'Invalid JSON in API response',
            'result' => ''
        ];
    } elseif (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        return [
            'success' => false,
            'message' => 'API request failed. Response: ' . $response_body,
            'result' => ''
        ];
    } else {
        $content = $data['candidates'][0]['content']['parts'][0]['text'];
        return [
            'success' => true,
            'message' => 'Response Generated',
            'result' => $content
        ];
    }
}
}

function generate_dummy_response( $last_prompt, $conversation_history ) {
// Dummy static response
$dummy_response = array(
    'success' => true,
    'message' => 'done',
    'result' => "here is my reply"
);

// Return the dummy response as an associative array
return $dummy_response;
}

function handle_chat_bot_request( WP_REST_Request $request ) {
$last_prompt = $request->get_param('last_prompt');
$conversation_history = $request->get_param('conversation_history');

$response = generate_chat_response($last_prompt, $conversation_history);
return $response;
}

function load_chat_bot_base_configuration(WP_REST_Request $request) {
// You can retrieve user data or other dynamic information here
$user_avatar_url = "https://raw.githubusercontent.com/haydengan/chatbot_image/main/profile.png"; // Implement this function
$bot_image_url = "https://raw.githubusercontent.com/haydengan/chatbot_image/main/NourishSpotLogo.png"; // Implement this function

$response = array(
'botStatus' => 1,
'StartUpMessage' => "Hi, How are you?",
'fontSize' => '16',
'userAvatarURL' => $user_avatar_url,
'botImageURL' => $bot_image_url,
// Adding the new field
'commonButtons' => array(
	array(
        'buttonText' => 'About Us',
        'buttonPrompt' => 'What is the Nourish Spot'
    ),
	
    array(
        'buttonText' => 'Order',
        'buttonPrompt' => 'I want to order'
    ),
    array(
        'buttonText' => 'Shop Our Merch',
        'buttonPrompt' => 'I want to shop your merch'
    ),
	
	array(
        'buttonText' => 'Catering',
        'buttonPrompt' => 'I want to know about catering'
    ),
	
	array(
        'buttonText' => 'Contact Us',
        'buttonPrompt' => 'How to contact you'
    )
)

);

$response = new WP_REST_Response($response, 200);

return $response;
}

add_action( 'rest_api_init', function () {
register_rest_route( 'myapi/v1', '/chat-bot/', array(
   'methods' => 'POST',
   'callback' => 'handle_chat_bot_request',
   'permission_callback' => '__return_true'
) );

register_rest_route('myapi/v1', '/chat-bot-config', array(
    'methods' => 'GET',
    'callback' => 'load_chat_bot_base_configuration',
));
} );
