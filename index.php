<?php
// Set timezone
date_default_timezone_set('UTC');

// Get current day of the week (1 = Monday, 7 = Sunday)
$currentDayOfWeek = date('N');
$today = date('Y-m-d');

// Read menu data from images in /data directory
$menuByDate = [];
$dataFiles = scandir('data');
foreach ($dataFiles as $file) {
    if (preg_match('/^dish(\d+)_(\d{4}-\d{2}-\d{2})\.png$/', $file, $matches)) {
        $dishNumber = $matches[1];
        $date = $matches[2];

        // Group dishes by date
        if (!isset($menuByDate[$date])) {
            $menuByDate[$date] = [];
        }

        // Improved dish name extraction
        $dishName = "Dish " . $dishNumber;
        if ($dishNumber == 1) {
            $dishName = "Veggy";
        } elseif ($dishNumber == 2) {
            $dishName = "Meat";
        }

        $menuByDate[$date][] = [
            'name' => $dishName,
            'image' => 'data/' . $file,
            'option' => count($menuByDate[$date]) + 1
        ];
    }
}
// Sort menus by date
krsort($menuByDate);

// Check if user has a cookie, if not create one
if (!isset($_COOKIE['user_id'])) {
    $userId = uniqid('user_', true);
    setcookie('user_id', $userId, time() + (86400 * 30), "/"); // 30 days
} else {
    $userId = $_COOKIE['user_id'];
}

// Get user's previous ratings
$userRatings = [];
if (file_exists('data/ratings.csv')) {
    if (($handle = fopen("data/ratings.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($data[0] === $userId) {
                $key = $data[2] . '-' . $data[3]; // date-menu_option
                $userRatings[$key] = $data[1]; // rating
            }
        }
        fclose($handle);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafeteria Menu Rating</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Cafeteria Menu Rating</h1>
            <p>Rate this week's menu items and help us improve!</p>
        </header>
        
        <main>
            <?php if (empty($menuByDate)): ?>
                <p>No menus found. Please check back later.</p>
            <?php else: ?>
                <?php foreach ($menuByDate as $date => $dishes): ?>
                    <?php
                    $dayName = date('l', strtotime($date));
                    $isRatable = strtotime($date) <= strtotime($today);
                    ?>
                    <div class="menu-card <?php echo $isRatable ? 'ratable' : 'future'; ?>">
                        <div class="day-header">
                            <h2><?php echo $dayName; ?> <span class="date"><?php echo $date; ?></span></h2>
                            <?php if (!$isRatable): ?>
                                <span class="future-tag">Coming Soon</span>
                            <?php endif; ?>
                        </div>

                        <div class="menu-options">
                            <?php foreach ($dishes as $dish): ?>
                                <div class="menu-option">
                                    <img src="<?php echo $dish['image']; ?>" alt="<?php echo $dish['name']; ?>" style="width:100%;height:auto;border-radius:var(--border-radius);">
                                    <h3><?php echo $dish['name']; ?></h3>

                                    <?php if ($isRatable): ?>
                                        <?php
                                        $ratingKey = $date . '-' . $dish['option'];
                                        $userRating = isset($userRatings[$ratingKey]) ? $userRatings[$ratingKey] : 0;
                                        ?>
                                        <div class="rating-container">
                                            <div class="stars" data-date="<?php echo $date; ?>" data-option="<?php echo $dish['option']; ?>">
                                                <?php for ($star = 1; $star <= 5; $star++): ?>
                                                    <i class="fa<?php echo $star <= $userRating ? ' fa-star' : 'r fa-star'; ?>" data-rating="<?php echo $star; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <div class="rating-status">
                                                <?php if ($userRating > 0): ?>
                                                    <span class="rated">You rated this <?php echo $userRating; ?> star<?php echo $userRating > 1 ? 's' : ''; ?></span>
                                                <?php else: ?>
                                                    <span class="not-rated">Not rated yet</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="comment-container">
                                            <textarea placeholder="Add a comment (optional)" class="comment" data-date="<?php echo $date; ?>" data-option="<?php echo $dish['option']; ?>"></textarea>
                                            <button class="submit-rating" data-date="<?php echo $date; ?>" data-option="<?php echo $dish['option']; ?>">Submit Rating</button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Company Cafeteria. All rights reserved.</p>
        </footer>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>