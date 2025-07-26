<?php
session_start();
require 'db.php';

// Get search parameters
$search = $_GET['search'] ?? '';
$gender = $_GET['gender'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$location = $_GET['location'] ?? '';

// Build SQL query
$sql = "SELECT * FROM horses WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR breed LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($gender)) {
    $sql .= " AND gender = ?";
    $params[] = $gender;
}

if (!empty($min_price)) {
    $sql .= " AND price >= ?";
    $params[] = $min_price;
}

if (!empty($max_price)) {
    $sql .= " AND price <= ?";
    $params[] = $max_price;
}

if (!empty($location)) {
    $sql .= " AND location LIKE ?";
    $params[] = "%$location%";
}

$sql .= " ORDER BY created_at DESC";

// Execute query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$horses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Horses - Horse Trading</title>
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
                    <li class="nav-item"><a class="nav-link active" href="browse.php">Browse Horses</a></li>
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
        <h1 class="mb-4">Browse Horses</h1>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="browse.php">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Any</option>
                                <option value="male" <?= $gender === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= $gender === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="gelding" <?= $gender === 'gelding' ? 'selected' : '' ?>>Gelding</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($location) ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="price_range" class="form-label">Price Range</label>
                            <div class="input-group">
                                <input type="number" class="form-control" placeholder="Min" name="min_price" value="<?= htmlspecialchars($min_price) ?>">
                                <span class="input-group-text">to</span>
                                <input type="number" class="form-control" placeholder="Max" name="max_price" value="<?= htmlspecialchars($max_price) ?>">
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="browse.php" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="row">
            <?php if(empty($horses)): ?>
                <div class="col-12">
                    <div class="alert alert-info">No horses found matching your criteria.</div>
                </div>
            <?php else: ?>
                <?php foreach($horses as $horse): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?= htmlspecialchars($horse['image_path'] ?? 'images/default_horse.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($horse['name']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($horse['name']) ?></h5>
                                <p class="card-text">
                                    <strong>Breed:</strong> <?= htmlspecialchars($horse['breed']) ?><br>
                                    <strong>Gender:</strong> <?= htmlspecialchars($horse['gender']) ?><br>
                                    <strong>Age:</strong> <?= htmlspecialchars($horse['age']) ?> years<br>
                                    <strong>Height:</strong> <?= htmlspecialchars($horse['height']) ?> hands<br>
                                    <strong>Price:</strong> $<?= number_format($horse['price'], 2) ?><br>
                                    <strong>Location:</strong> <?= htmlspecialchars($horse['location']) ?>
                                </p>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="horse_details.php?id=<?= $horse['id'] ?>" class="btn btn-primary">View Details</a>
                                <?php if(isset($_SESSION['user_id']) && ($_SESSION['user_type'] === 'buyer' || $_SESSION['user_type'] === 'both')): ?>
                                    <a href="add_favorite.php?id=<?= $horse['id'] ?>" class="btn btn-outline-primary">❤️ Favorite</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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