<?php
session_start();
require 'db.php';

// Fetch featured horses
$stmt = $pdo->query("SELECT * FROM horses ORDER BY created_at DESC LIMIT 6");
$featured_horses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horse Trading - Buy & Sell Horses</title>
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
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
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

    <header class="hero bg-primary text-white py-5">
        <div class="container text-center">
            <h1>Find Your Perfect Horse</h1>
            <p class="lead">Buy and sell horses with trusted sellers across the country</p>
            <a href="browse.php" class="btn btn-light btn-lg">Browse Horses</a>
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="btn btn-outline-light btn-lg">Register Now</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="container my-5">
        <section class="mb-5">
            <h2 class="text-center mb-4">Featured Horses</h2>
            <div class="row">
                <?php foreach($featured_horses as $horse): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?= htmlspecialchars($horse['image_path'] ?? 'images/default_horse.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($horse['name']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($horse['name']) ?></h5>
                                <p class="card-text">
                                    <strong>Breed:</strong> <?= htmlspecialchars($horse['breed']) ?><br>
                                    <strong>Gender:</strong> <?= htmlspecialchars($horse['gender']) ?><br>
                                    <strong>Price:</strong> $<?= number_format($horse['price'], 2) ?>
                                </p>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="horse_details.php?id=<?= $horse['id'] ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2023 Horse Trading. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>