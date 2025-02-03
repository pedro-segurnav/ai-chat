<?php
require_once '../includes/init.php';

// Load configuration
$config = include '../includes/config.php';

// Fetch OpenAI API key from the configuration file
$openai_api_key = $config['openai_api_key'] ?? null;

if (!$openai_api_key) {
    error_log("Error: OpenAI API key is not set.");
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error: OpenAI API key is missing.']);
    exit;
}

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log incoming data for debugging
error_log("Incoming data: " . print_r($data, true));

$expected_token = 'abc123xyz'; // Replace with a dynamically generated token per bot/customer
$input_token = $data['token'] ?? null;

if ($input_token !== $expected_token) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized: Invalid token']);
    exit;
}

$bot_id = $data['bot_id'] ?? null;

// Normalize the incoming message
function normalize_message($message) {
    // Convert to lowercase
    $message = strtolower($message);

    // Remove punctuation
    $message = preg_replace('/[^\w\s]/', '', $message);

    return $message;
}

$message = normalize_message($data['message'] ?? '');

if (!$bot_id || !$message) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing bot_id or message']);
    exit;
}

try {
    // Fetch bot details (excluding openai_api_key)
    $stmt = $pdo->prepare("
        SELECT name, company_name, location, phone_number, email, critical_rules
        FROM bots WHERE id = ?
    ");
    $stmt->execute([$bot_id]);
    $bot = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bot) {
        http_response_code(404);
        echo json_encode(['error' => 'Bot not found']);
        exit;
    }

    $bot_name = $bot['name'];
    $companyName = $bot['company_name'] ?? "Your Company";
    $location = $bot['location'] ?? "Your Location";
    $phoneNumber = $bot['phone_number'] ?? "+1234567890";
    $email = $bot['email'] ?? "info@yourcompany.com";
    $criticalRules = $bot['critical_rules'] ?? "";

    // Track visitor statistics
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $country = 'Unknown'; // Use a GeoIP library or API to fetch the country
    $browser = $_SERVER['HTTP_USER_AGENT'];

    // Check if the visitor exists
    $stmt = $pdo->prepare("SELECT id, visits FROM visitors WHERE bot_id = ? AND ip_address = ?");
    $stmt->execute([$bot_id, $ip_address]);
    $visitor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($visitor) {
        // Update existing visitor
        $visits = $visitor['visits'] + 1;
        $stmt = $pdo->prepare("UPDATE visitors SET visits = ?, last_visit = NOW() WHERE id = ?");
        $stmt->execute([$visits, $visitor['id']]);
        $visitor_id = $visitor['id'];

        // Personalized greeting for returning visitors
        if ($visits > 1) {
            $greeting = "Welcome back! How can I assist you today?";
        } else {
            $greeting = "Hello! How can I assist you today?";
        }
    } else {
        // Insert new visitor
        $stmt = $pdo->prepare("INSERT INTO visitors (bot_id, ip_address, country, browser) VALUES (?, ?, ?, ?)");
        $stmt->execute([$bot_id, $ip_address, $country, $browser]);
        $visitor_id = $pdo->lastInsertId();

        // Default greeting for new visitors
        $greeting = "Hello! How can I assist you today?";
    }

    if (!$visitor_id) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to track visitor']);
        exit;
    }

    // Step 1: Check for predefined greetings
    $greetings = ['hi', 'hello', 'hey', 'good morning', 'good afternoon', 'good evening'];
    if (in_array($message, $greetings)) {
        $response = $greeting;
    } else {
        // Step 2: Check for exit phrases
        $exit_phrases = ['see you', 'goodbye', 'bye', 'take care'];
        if (in_array($message, $exit_phrases)) {
            $response = "Goodbye! Have a great day!";
        } else {
            // Step 3: Check the database for a predefined response (only for high-priority queries)
            $stmt = $pdo->prepare("SELECT response FROM bot_knowledge WHERE bot_id = ? AND key_phrase = ?");
            $stmt->execute([$bot_id, $message]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $response = $result['response'];
            } else {
                // Step 4: Handle inventory-related queries
                $inventory_table = "bot_inventory_$bot_id";

                // Fetch custom fields
                $stmt = $pdo->prepare("SELECT field_name FROM bot_inventory_fields WHERE bot_id = ?");
                $stmt->execute([$bot_id]);
                $fields = $stmt->fetchAll(PDO::FETCH_COLUMN);

                $inventorySummary = "";
                if (!empty($fields)) {
                    $stmt = $pdo->prepare("SELECT * FROM $inventory_table LIMIT 10"); // Limit to 10 vehicles for brevity
                    $stmt->execute();
                    $inventory_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($inventory_items as $item) {
                        $inventorySummary .= "- ";
                        foreach ($item as $key => $value) {
                            $inventorySummary .= "$key: $value, ";
                        }
                        $inventorySummary = rtrim($inventorySummary, ", ") . "\n";
                    }
                }

                // Handle specific inventory queries
                if (strpos($message, 'bmw x3') !== false) {
                    $stmt = $pdo->prepare("SELECT * FROM $inventory_table WHERE model LIKE ? LIMIT 1");
                    $stmt->execute(["%x3%"]);
                    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($vehicle) {
                        // Vehicle found in inventory
                        $response = "We currently have the following BMW X3 in stock:\n";
                        foreach ($vehicle as $key => $value) {
                            $response .= "$key: $value, ";
                        }
                        $response = rtrim($response, ", ") . "\n";
                    } else {
                        // Vehicle not found in inventory
                        $response = "I'm sorry, but the BMW X3 is not currently in our inventory. Would you like information on other models?";
                    }
                } elseif (strpos($message, 'what do you have in stock') !== false || strpos($message, 'cars in stock') !== false) {
                    // Fetch all vehicles in stock
                    $stmt = $pdo->prepare("SELECT model, make, year FROM $inventory_table LIMIT 10");
                    $stmt->execute();
                    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($vehicles)) {
                        $response = "Here are some vehicles currently in stock:\n";
                        foreach ($vehicles as $vehicle) {
                            $response .= "- {$vehicle['make']} {$vehicle['model']} ({$vehicle['year']})\n";
                        }
                    } else {
                        $response = "I'm sorry, but we currently don't have any vehicles in stock. Please check back later!";
                    }
                } else {
                    // Step 5: Fall back to OpenAI API for conversational responses
                    // Construct the system prompt for OpenAI
                    $system_prompt = "
You are $bot_name, a sales assistant for $companyName, a company located in $location.
CRITICAL RULES:
$criticalRules
CURRENT INVENTORY:
$inventorySummary
Business Hours:
- Monday to Friday: 9:00 AM - 6:00 PM
- Saturday: 9:00 AM - 3:00 PM
- Sunday: Closed
Contact:
- Phone: $phoneNumber
- Email: $email
- Location: $location

IMPORTANT: Be completely truthful. No speculation, no generalizations. Only discuss products or services we currently offer.

If asked about your identity, respond with: 'My name is $bot_name. I am here to assist you!'
If asked how to address you, respond with: 'You can call me $bot_name.'
If asked for the company address, respond with: 'Our company is located in $location. If you need directions or further assistance, feel free to ask.'

If asked about inventory (e.g., 'What do you have in stock?'), summarize the current inventory using the following format:
'We currently have the following vehicles in stock: [list of vehicles].'

If asked about a specific vehicle (e.g., 'BMW X3'), check the inventory and provide accurate details. If the vehicle is not in stock, respond with: 'I'm sorry, but the [vehicle] is not currently in our inventory. Would you like information on other models?'

If asked about car brands (e.g., 'Are BMW good cars?'), provide a balanced and factual response based on general knowledge, avoiding overly promotional language.
";

                    // Call OpenAI API
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $openai_api_key
                    ]);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                        'model' => 'gpt-3.5-turbo',
                        'messages' => [
                            ['role' => 'system', 'content' => $system_prompt],
                            ['role' => 'user', 'content' => $message]
                        ],
                        'max_tokens' => 100
                    ]));
                    $response = curl_exec($ch);
                    curl_close($ch);

                    // Parse the OpenAI response
                    $response_data = json_decode($response, true);

                    if (json_last_error() !== JSON_ERROR_NONE || !isset($response_data['choices'][0]['message']['content'])) {
                        error_log("OpenAI API error: " . $response);
                        $response = 'Sorry, I encountered an issue while processing your request.';
                    } else {
                        $response = $response_data['choices'][0]['message']['content'];
                    }
                }
            }
        }
    }

    // Log the chat message
    $stmt = $pdo->prepare("INSERT INTO chat_logs (bot_id, visitor_id, message, response) VALUES (?, ?, ?, ?)");
    $stmt->execute([$bot_id, $visitor_id, $message, $response]);

    // Include the bot's name in the response
    echo json_encode([
        'bot_name' => $bot_name,
        'response' => $response
    ]);
} catch (Exception $e) {
    error_log("Error in bot_response.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error: ' . $e->getMessage()]);
}
?>