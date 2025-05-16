<?php
session_start();
require '../config/database.php';

// Ensure only Admin or Superadmin can access
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../login.php");
    exit();
}

$message = "";
$messageType = "";

// Handle product addition
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);
    $barcode = trim($_POST['barcode']);
    $brand = trim($_POST['brand']);
    $gender = trim($_POST['gender']);
    $description = trim($_POST['description']); // New field for description

    // Handle new category
    if ($category === 'new') {
        $newCategory = trim($_POST['newCategory']);
        if (!empty($newCategory)) {
            $category = $newCategory; // Use the new category directly
        } else {
            $message = "New category name cannot be empty.";
            $messageType = "error";
        }
    }

    // Handle new brand
    if ($brand === 'new') {
        $newBrand = trim($_POST['newBrand']);
        if (!empty($newBrand)) {
            $brand = $newBrand; // Use the new brand directly
        } else {
            $message = "New brand name cannot be empty.";
            $messageType = "error";
        }
    }
    
    // Image upload handling
    $imagePath = "";
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        
        // Create directory if it doesn't exist
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $imageName = time() . "_" . uniqid() . "." . $imageFileType;
        $imagePath = $targetDir . $imageName;

        // Check file type
        $allowedTypes = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $allowedTypes)) {
            $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $messageType = "error";
        } else {
            // Move uploaded file
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
                $message = "Failed to upload image.";
                $messageType = "error";
            }
        }
    }

    // Only proceed if there's no error message
    if (empty($messageType) || $messageType !== "error") {
        try {
            // Insert product into database
            $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock, barcode, brand, gender, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $category, $price, $stock, $barcode, $brand, $gender, $description, $imagePath])) {
                $message = "Product added successfully!";
                $messageType = "success";
            } else {
                $message = "Failed to add product.";
                $messageType = "error";
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Get categories for dropdown
try {
    $categoryStmt = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category");
    $categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = [];
}

// Get brands for dropdown
try {
    $brandStmt = $pdo->query("SELECT DISTINCT brand FROM products ORDER BY brand");
    $brands = $brandStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $brands = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="../assets/css/add_product.css">
</head>
<body>

<div class="container">
    <h2>Add Product</h2>
    
    <?php if ($message): ?>
        <div class="<?= $messageType === 'error' ? 'error-message' : 'success-message' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form action="add_product.php" method="POST" enctype="multipart/form-data" id="addProductForm">
        <div class="form-group">
            <label for="name" class="required">Product Name</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="category" class="required">Category</label>
            <div class="custom-select">
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <option value="new">+ Add New Category</option>
                </select>
            </div>
        </div>

        <div class="form-group" id="newCategoryGroup" style="display: none;">
            <label for="newCategory">New Category Name</label>
            <input type="text" id="newCategory" name="newCategory">
        </div>

        <div class="form-group">
            <label for="price" class="required">Price ($)</label>
            <input type="number" id="price" name="price" step="0.01" min="0" required>
        </div>

        <div class="form-group">
            <label for="stock" class="required">Stock Quantity</label>
            <input type="number" id="stock" name="stock" min="0" required>
        </div>

        <div class="form-group">
            <label for="barcode" class="required">Barcode</label>
            <input type="text" id="barcode" name="barcode" required>
        </div>

        <div class="form-group">
            <label for="brand" class="required">Brand</label>
            <div class="custom-select">
                <select id="brand" name="brand" required>
                    <option value="">Select Brand</option>
                    <?php if (!empty($brands)): ?>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <option value="new">+ Add New Brand</option>
                </select>
            </div>
        </div>

        <div class="form-group" id="newBrandGroup" style="display: none;">
            <label for="newBrand">New Brand Name</label>
            <input type="text" id="newBrand" name="newBrand">
        </div>

        <div class="form-group">
            <label for="gender" class="required">Gender</label>
            <div class="custom-select">
                <select id="gender" name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Men">Men</option>
                    <option value="Women">Women</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" placeholder="Enter product description"></textarea>
        </div>

        <div class="form-group full-width">
            <label for="image">Product Image</label>
            <div class="file-input-container">
                <label for="image" class="file-input-label">
                    <span id="file-name">Choose a file...</span>
                </label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>
            <div class="image-preview" id="imagePreview">
                <span class="preview-placeholder">Image preview will appear here</span>
            </div>
        </div>

        <button type="submit">Add Product</button>
    </form>

    <a href="manage_products.php" class="back-btn">Back to Manage Products</a>
</div>

<script>
// Handle file input display
document.getElementById('image').addEventListener('change', function(e) {
    const fileName = e.target.files[0] ? e.target.files[0].name : 'Choose a file...';
    document.getElementById('file-name').textContent = fileName;
    
    // Image preview
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    if (e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            preview.appendChild(img);
        }
        reader.readAsDataURL(e.target.files[0]);
    } else {
        preview.innerHTML = '<span class="preview-placeholder">Image preview will appear here</span>';
    }
});

// Handle new category option
document.getElementById('category').addEventListener('change', function() {
    const newCategoryGroup = document.getElementById('newCategoryGroup');
    if (this.value === 'new') {
        newCategoryGroup.style.display = 'block';
        document.getElementById('newCategory').setAttribute('required', 'required');
    } else {
        newCategoryGroup.style.display = 'none';
        document.getElementById('newCategory').removeAttribute('required');
    }
});

// Handle new brand option
document.getElementById('brand').addEventListener('change', function() {
    const newBrandGroup = document.getElementById('newBrandGroup');
    if (this.value === 'new') {
        newBrandGroup.style.display = 'block';
        document.getElementById('newBrand').setAttribute('required', 'required');
    } else {
        newBrandGroup.style.display = 'none';
        document.getElementById('newBrand').removeAttribute('required');
    }
});

// Form validation
document.getElementById('addProductForm').addEventListener('submit', function(e) {
    let isValid = true;
    
    // Check if new category is selected but not filled
    if (document.getElementById('category').value === 'new' && 
        document.getElementById('newCategory').value.trim() === '') {
        isValid = false;
        alert('Please enter a name for the new category');
    }
    
    // Check if new brand is selected but not filled
    if (document.getElementById('brand').value === 'new' && 
        document.getElementById('newBrand').value.trim() === '') {
        isValid = false;
        alert('Please enter a name for the new brand');
    }
    
    if (!isValid) {
        e.preventDefault();
    }
});
</script>

</body>
</html>
