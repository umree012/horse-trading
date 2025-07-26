<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'buyer' && $_SESSION['user_type'] !== 'both')) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: browse.php');
    exit();
}

$horse_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Remove from favorites
$stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND horse_id = ?");
$stmt->execute([$user_id, $horse_id]);

// Redirect back to previous page
$referer = $_SERVER['HTTP_REFERER'] ?? 'browse.php';
header("Location: $referer");
exit();
?>