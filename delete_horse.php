<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'seller' && $_SESSION['user_type'] !== 'both')) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$horse_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify horse belongs to user
$stmt = $pdo->prepare("SELECT * FROM horses WHERE id = ? AND seller_id = ?");
$stmt->execute([$horse_id, $user_id]);
$horse = $stmt->fetch();

if (!$horse) {
    header('Location: dashboard.php');
    exit();
}

// Delete horse
$stmt = $pdo->prepare("DELETE FROM horses WHERE id = ?");
if ($stmt->execute([$horse_id])) {
    // Delete image if it exists and isn't the default
    if ($horse['image_path'] && $horse['image_path'] !== 'images/default_horse.jpg' && file_exists($horse['image_path'])) {
        unlink($horse['image_path']);
    }
    // Delete favorites associated with this horse
    $pdo->prepare("DELETE FROM favorites WHERE horse_id = ?")->execute([$horse_id]);
}

header('Location: dashboard.php');
exit();
?>