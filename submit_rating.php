<?php
// Set timezone
date_default_timezone_set('UTC');

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the data from the POST request
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $date = isset($_POST['date']) ? $_POST['date'] : '';
    $option = isset($_POST['option']) ? intval($_POST['option']) : 0;
    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
    
    // Validate the data
    if ($rating < 1 || $rating > 5 || empty($date) || $option < 1 || $option > 2) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }
    
    // Check if the date is in the past or today
    $today = date('Y-m-d');
    if (strtotime($date) > strtotime($today)) {
        echo json_encode(['success' => false, 'message' => 'Cannot rate future dates']);
        exit;
    }
    
    // Get the user ID from the cookie
    $userId = isset($_COOKIE['user_id']) ? $_COOKIE['user_id'] : uniqid('user_', true);
    
    // Prepare the data to be saved
    $data = [
        $userId,
        $rating,
        $date,
        $option,
        $comment,
        date('Y-m-d H:i:s') // timestamp
    ];
    
    // Create the data directory if it doesn't exist
    if (!file_exists('data')) {
        mkdir('data', 0777, true);
    }
    
    // Check if the ratings file exists, if not create it with headers
    $fileExists = file_exists('data/ratings.csv');
    
    // Open the file for appending
    $handle = fopen('data/ratings.csv', 'a');
    
    // If the file was just created, add headers
    if (!$fileExists) {
        fputcsv($handle, ['user_id', 'rating', 'date', 'menu_option', 'comment', 'timestamp']);
    }
    
    // Write the data to the file
    fputcsv($handle, $data);
    
    // Close the file
    fclose($handle);
    
    // Return success
    echo json_encode(['success' => true, 'message' => 'Rating submitted successfully']);
} else {
    // Return error if not a POST request
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}