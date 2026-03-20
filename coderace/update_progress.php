<?php
include 'db_config.php'; // Iyong database connection
session_start();

if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $xp_reward = $_POST['xp'];
    $new_level = $_POST['level_completed'] + 1;

    // Update XP and Level only if the player is moving forward
    $sql = "UPDATE users SET 
            xp = xp + $xp_reward, 
            current_level = GREATEST(current_level, $new_level) 
            WHERE id = $user_id";
            
    if($conn->query($sql)) {
        echo "Success";
    }
}
?>