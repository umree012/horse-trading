<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Fetch user's horses if seller or both
if ($user_type === 'seller' || $user_type === 'both') {
    $stmt = $pdo->prepare("SELECT * FROM horses WHERE seller_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $user_horses = $stmt->fetchAll();
}

// Fetch favorite horses if buyer or both
if ($user_type === 'buyer' || $user_type === 'both') {
    $stmt = $pdo->prepare("SELECT h.* FROM horses h JOIN favorites f ON h.id = f.horse_id WHERE f.user_id = ?");
    $stmt->execute([$user_id]);
    $favorite_horses = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Horse Trading</title>
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
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
                    <?php if($user_type === 'seller' || $user_type === 'both'): ?>
                        <li class="nav-item"><a class="nav-link" href="add_horse.php">Add Horse</a></li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <h1 class="mb-4">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>
        
        <?php if($user_type === 'seller' || $user_type === 'both'): ?>
            <section class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>Your Horses for Sale</h2>
                    <a href="add_horse.php" class="btn btn-primary">Add New Horse</a>
                </div>
                
                <?php if(empty($user_horses)): ?>
                    <div class="alert alert-info">You haven't listed any horses yet.</div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach($user_horses as $horse): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <img src="<?= htmlspecialchars($horse['image_path'] ?? 'images/default_horse.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($horse['name']) ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($horse['name']) ?></h5>
                                        <p class="card-text">
                                            <strong>Price:</strong> $<?= number_format($horse['price'], 2) ?><br>
                                            <strong>Location:</strong> <?= htmlspecialchars($horse['location']) ?>
                                        </p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="horse_details.php?id=<?= $horse['id'] ?>" class="btn btn-primary">View</a>
                                        <a href="edit_horse.php?id=<?= $horse['id'] ?>" class="btn btn-secondary">Edit</a>
                                        <a href="delete_horse.php?id=<?= $horse['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this horse?')">Delete</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
        
        <?php if($user_type === 'buyer' || $user_type === 'both'): ?>
            <section class="mb-5">
                <h2 class="mb-3">Your Favorite Horses</h2>
                
                <?php if(empty($favorite_horses)): ?>
                    <div class="alert alert-info">You haven't favorited any horses yet.</div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach($favorite_horses as $horse): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <img src="<?= htmlspecialchars($horse['image_path'] ?? 'images/default_horse.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($horse['name']) ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($horse['name']) ?></h5>
                                        <p class="card-text">
                                            <strong>Price:</strong> $<?= number_format($horse['price'], 2) ?><br>
                                            <strong>Location:</strong> <?= htmlspecialchars($horse['location']) ?>
                                        </p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="horse_details.php?id=<?= $horse['id'] ?>" class="btn btn-primary">View Details</a>
                                        <a href="remove_favorite.php?id=<?= $horse['id'] ?>" class="btn btn-danger">Remove</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2023 Horse Trading. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>