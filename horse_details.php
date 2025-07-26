<?php
session_start();
require 'db.php';

if (!isset($_GET['id'])) {
    header('Location: browse.php');
    exit();
}

$horse_id = $_GET['id'];

// Get horse details
$stmt = $pdo->prepare("SELECT h.*, u.username as seller_name FROM horses h JOIN users u ON h.seller_id = u.id WHERE h.id = ?");
$stmt->execute([$horse_id]);
$horse = $stmt->fetch();

if (!$horse) {
    header('Location: browse.php');
    exit();
}

// Check if horse is favorited by current user
$is_favorite = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND horse_id = ?");
    $stmt->execute([$_SESSION['user_id'], $horse_id]);
    $is_favorite = $stmt->fetch() !== false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($horse['name']) ?> - Horse Trading</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Horse Trading</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="browse.php">Browse Horses</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                        <?php if($_SESSION['user_type'] === 'seller' || $_SESSION['user_type'] === 'both'): ?>
                            <li class="nav-item"><a class="nav-link" href="add_horse.php">Add Horse</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <div class="row">
            <div class="col-md-6">
                <img src="<?= htmlspecialchars($horse['image_path'] ?? 'images/default_horse.jpg') ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($horse['name']) ?>">
            </div>
            <div class="col-md-6">
                <h1><?= htmlspecialchars($horse['name']) ?></h1>
                <h3 class="text-primary">$<?= number_format($horse['price'], 2) ?></h3>
                
                <div class="d-flex justify-content-between mb-4">
                    <?php if(isset($_SESSION['user_id']) && ($_SESSION['user_type'] === 'buyer' || $_SESSION['user_type'] === 'both')): ?>
                        <a href="<?= $is_favorite ? 'remove_favorite.php?id='.$horse['id'] : 'add_favorite.php?id='.$horse['id'] ?>" class="btn btn-outline-danger">
                            <?= $is_favorite ? '❤️ Remove Favorite' : '🤍 Add Favorite' ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] === $horse['seller_id']): ?>
                        <a href="edit_horse.php?id=<?= $horse['id'] ?>" class="btn btn-secondary">Edit</a>
                        <a href="delete_horse.php?id=<?= $horse['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this horse?')">Delete</a>
                    <?php endif; ?>
                </div>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Details</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Breed:</strong> <?= htmlspecialchars($horse['breed']) ?></li>
                            <li class="list-group-item"><strong>Gender:</strong> <?= htmlspecialchars($horse['gender']) ?></li>
                            <li class="list-group-item"><strong>Age:</strong> <?= htmlspecialchars($horse['age']) ?> years</li>
                            <li class="list-group-item"><strong>Height:</strong> <?= htmlspecialchars($horse['height']) ?> hands</li>
                            <li class="list-group-item"><strong>Color:</strong> <?= htmlspecialchars($horse['color']) ?></li>
                            <li class="list-group-item"><strong>Location:</strong> <?= htmlspecialchars($horse['location']) ?></li>
                            <li class="list-group-item"><strong>Listed by:</strong> <?= htmlspecialchars($horse['seller_name']) ?></li>
                        </ul>
                    </div>
                </div>
                
                <?php if(!empty($horse['description'])): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Description</h5>
                            <p><?= nl2br(htmlspecialchars($horse['description'])) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Contact Seller</h5>
                        <p>If you're interested in this horse, please contact the seller for more information.</p>
                        <a href="#" class="btn btn-primary">Contact Seller</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2023 Horse Trading. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>