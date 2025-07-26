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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $gender = $_POST['gender'];
    $height = $_POST['height'];
    $color = trim($_POST['color']);
    $breed = trim($_POST['breed']);
    $age = $_POST['age'];
    $price = $_POST['price'];
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);

    // Validate inputs
    if (empty($name) || empty($gender) || empty($height) || empty($color) || empty($breed) || empty($age) || empty($price) || empty($location)) {
        $error = 'All fields except description are required.';
    } elseif (!is_numeric($height) || !is_numeric($age) || !is_numeric($price)) {
        $error = 'Height, age, and price must be numbers.';
    } else {
        // Handle file upload if new image provided
        $image_path = $horse['image_path'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('horse_') . '.' . $file_ext;
            $target_path = $upload_dir . $file_name;
            
            // Check if image file is a actual image
            $check = getimagesize($_FILES['image']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    // Delete old image if it exists and isn't the default
                    if ($image_path && $image_path !== 'images/default_horse.jpg' && file_exists($image_path)) {
                        unlink($image_path);
                    }
                    $image_path = $target_path;
                } else {
                    $error = 'Sorry, there was an error uploading your file.';
                }
            } else {
                $error = 'File is not an image.';
            }
        }
        
        if (empty($error)) {
            // Update horse in database
            $stmt = $pdo->prepare("UPDATE horses SET name = ?, gender = ?, height = ?, color = ?, breed = ?, age = ?, price = ?, location = ?, description = ?, image_path = ? WHERE id = ?");
            if ($stmt->execute([$name, $gender, $height, $color, $breed, $age, $price, $location, $description, $image_path, $horse_id])) {
                $success = 'Horse updated successfully!';
                // Refresh horse data
                $stmt = $pdo->prepare("SELECT * FROM horses WHERE id = ?");
                $stmt->execute([$horse_id]);
                $horse = $stmt->fetch();
            } else {
                $error = 'Failed to update horse. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Horse - Horse Trading</title>
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
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="add_horse.php">Add Horse</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center">Edit Horse</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        <form method="POST" action="edit_horse.php?id=<?= $horse_id ?>" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Horse Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($horse['name']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="male" <?= $horse['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= $horse['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                                        <option value="gelding" <?= $horse['gender'] === 'gelding' ? 'selected' : '' ?>>Gelding</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="height" class="form-label">Height (in hands)</label>
                                    <input type="number" step="0.1" class="form-control" id="height" name="height" value="<?= htmlspecialchars($horse['height']) ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="color" class="form-label">Color</label>
                                    <input type="text" class="form-control" id="color" name="color" value="<?= htmlspecialchars($horse['color']) ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="breed" class="form-label">Breed</label>
                                    <input type="text" class="form-control" id="breed" name="breed" value="<?= htmlspecialchars($horse['breed']) ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="age" class="form-label">Age (years)</label>
                                    <input type="number" class="form-control" id="age" name="age" value="<?= htmlspecialchars($horse['age']) ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="price" class="form-label">Price ($)</label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= htmlspecialchars($horse['price']) ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($horse['location']) ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($horse['description']) ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="image" class="form-label">Horse Image</label>
                                <?php if ($horse['image_path']): ?>
                                    <div class="mb-2">
                                        <img src="<?= htmlspecialchars($horse['image_path']) ?>" class="img-thumbnail" style="max-height: 200px;">
                                    </div>
                                <?php endif; ?>
                                <input class="form-control" type="file" id="image" name="image" accept="image/*">
                                <div class="form-text">Leave blank to keep current image</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Update Horse</button>
                        </form>
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