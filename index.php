<?php
// Set timezone
date_default_timezone_set('UTC');

// Get current day of the week (1 = Monday, 7 = Sunday)
$currentDayOfWeek = date('N');
$today = date('Y-m-d');

// Read menu data
$menuData = [];
if (($handle = fopen("data/menu.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $menuData[] = $data;
    }
    fclose($handle);
}

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
            <?php foreach ($menuData as $index => $menu): ?>
                <?php 
                $menuDate = $menu[0];
                $dayName = date('l', strtotime($menuDate));
                $isRatable = strtotime($menuDate) <= strtotime($today);
                ?>
                <div class="menu-card <?php echo $isRatable ? 'ratable' : 'future'; ?>">
                    <div class="day-header">
                        <h2><?php echo $dayName; ?> <span class="date"><?php echo $menuDate; ?></span></h2>
                        <?php if (!$isRatable): ?>
                            <span class="future-tag">Coming Soon</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="menu-options">
                        <?php for ($i = 1; $i <= 2; $i++): ?>
                            <div class="menu-option">
                                <h3>Option <?php echo $i; ?>: <?php echo $menu[$i]; ?></h3>
                                
                                <?php if ($isRatable): ?>
                                    <?php 
                                    $ratingKey = $menuDate . '-' . $i;
                                    $userRating = isset($userRatings[$ratingKey]) ? $userRatings[$ratingKey] : 0;
                                    ?>
                                    <div class="rating-container">
                                        <div class="stars" data-date="<?php echo $menuDate; ?>" data-option="<?php echo $i; ?>">
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
                                        <textarea placeholder="Add a comment (optional)" class="comment" data-date="<?php echo $menuDate; ?>" data-option="<?php echo $i; ?>"></textarea>
                                        <button class="submit-rating" data-date="<?php echo $menuDate; ?>" data-option="<?php echo $i; ?>">Submit Rating</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Company Cafeteria. All rights reserved.</p>
        </footer>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>