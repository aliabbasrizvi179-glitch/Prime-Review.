<?php
// Load composer dependencies
require_once 'vendor/autoload.php';

// Initialize error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', _DIR_ . '/error.log');

// Load environment variables (if using)
$botToken = getenv('BOT_TOKEN');
$adminId = getenv('ADMIN_ID');
$webhookUrl = getenv('WEBHOOK_URL');

// If using webhook method (recommended for production)
if (!empty($webhookUrl)) {
    // Webhook setup code would go here
}

// Initialize Telegram Bot API
use Telegram\Bot\Api;

try {
    $telegram = new Api($botToken);
    
    // Get update from Telegram
    $update = $telegram->getWebhookUpdate();
    
    // Process the update
    if ($update->getMessage()) {
        $message = $update->getMessage();
        $chatId = $message->getChat()->getId();
        $text = $message->getText();
        
        // Load users data
        $users = loadUsersData();
        
        // Handle /start command
        if ($text === '/start') {
            showStartMenu($telegram, $chatId, $users);
        }
        
        // Handle other commands based on your business logic
        // ... (your existing bot logic goes here)
        
        // Save users data
        saveUsersData($users);
    }
} catch (Exception $e) {
    error_log('Telegram Bot Error: ' . $e->getMessage());
}

/**
 * Load users data from JSON file
 */
function loadUsersData() {
    if (!file_exists('users.json')) {
        // Initialize with empty structure
        $initialData = [
            'users' => [],
            'brands' => [],
            'campaigns' => [],
            'reviews' => [],
            'wallet' => [],
            'admin' => [
                'id' => getenv('ADMIN_ID') ?: '123456789' // Default admin ID
            ]
        ];
        file_put_contents('users.json', json_encode($initialData, JSON_PRETTY_PRINT));
    }
    
    $data = file_get_contents('users.json');
    return json_decode($data, true);
}

/**
 * Save users data to JSON file
 */
function saveUsersData($data) {
    file_put_contents('users.json', json_encode($data, JSON_PRETTY_PRINT));
    // Ensure proper permissions
    chmod('users.json', 0644);
}

/**
 * Show start menu with Brand/User options
 */
function showStartMenu($telegram, $chatId, $users) {
    $keyboard = [
        ['1️⃣ I am Brand'],
        ['2️⃣ I am User']
    ];
    
    $replyMarkup = $telegram->replyKeyboardMarkup([
        'keyboard' => $keyboard,
        'resize_keyboard' => true,
        'one_time_keyboard' => true
    ]);
    
    $telegram->sendMessage([
        'chat_id' => $chatId,
        'text' => "Welcome to the Review Business Bot! Please choose your role:",
        'reply_markup' => $replyMarkup
    ]);
}

// Additional functions for handling brand menu, user menu, admin flow, etc.
// ... (your existing business logic functions)

// For webhook verification (if using)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process Telegram webhook update
} else {
    // Display simple message for browser access
    echo "Telegram Bot is running!";
}
?>