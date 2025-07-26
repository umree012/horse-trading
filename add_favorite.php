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

// Check if horse exists
$stmt = $pdo->prepare("SELECT id FROM horses WHERE id = ?");
$stmt->execute([$horse_id]);
if (!$stmt->fetch()) {
    header('Location: browse.php');
    exit();
}

// Add to favorites
try {
    $stmt = $pdo->prepare("INSERT INTO favorites (user_id, horse_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $horse_id]);
} catch (PDOException $e) {
    // Ignore duplicate entry errors
    if ($e->getCode() !== '23000') {
        throw $e;
    }
}

// Redirect back to previous page
$referer = $_SERVER['HTTP_REFERER'] ?? 'browse.php';
header("Location: $referer");
exit();
?>