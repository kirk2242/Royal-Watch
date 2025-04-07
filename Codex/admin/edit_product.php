
<?php
session_start();
require '../config/database.php';

// Check if user is logged in and is Admin or Superadmin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../login.php");
    exit();
}

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_products.php");
    exit();
}

$product_id = $_GET['id'];

// Fetch product details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: manage_products.php");
    exit();
}

$message = "";
$messageType = "";

// Update Product Logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $barcode = trim($_POST['barcode']);
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);
    $brand = trim($_POST['brand']);
    $gender = trim($_POST['gender']);

    // Image Upload Handling
    $imagePath = $product['image']; // Default to existing image
    
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
            // Update query
            $update_stmt = $pdo->prepare("UPDATE products SET barcode=?, name=?, category=?, price=?, stock=?, brand=?, gender=?, image=?, updated_at=NOW() WHERE id=?");
            if ($update_stmt->execute([$barcode, $name, $category, $price, $stock, $brand, $gender, $imagePath, $product_id])) {
                $message = "Product updated successfully!";
                $messageType = "success";
                
                // Refresh product data
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $message = "Failed to update product!";
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
    <title>Edit Product</title>
    <link rel="stylesheet" href="../assets/css/edit_product.css">
</head>
<body>

<div class="container">
    <h2>Edit Product <span class="product-id">ID: <?= $product_id ?></span></h2>
    
    <?php if ($message): ?>
        <div class="<?= $messageType === 'error' ? 'error-message' : 'success-message' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="editProductForm">
        <div class="form-group">
            <label for="barcode" class="required">Barcode</label>
            <input type="text" id="barcode" name="barcode" value="<?= htmlspecialchars($product['barcode']) ?>" required>
        </div>

        <div class="form-group">
            <label for="name" class="required">Product Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>

        <div class="form-group">
            <label for="category" class="required">Category</label>
            <div class="custom-select">
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <?php 
                    $categoryFound = false;
                    if (!empty($categories)): 
                        foreach ($categories as $cat): 
                            $selected = ($cat === $product['category']) ? 'selected' : '';
                            if ($cat === $product['category']) $categoryFound = true;
                    ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $selected ?>><?= htmlspecialchars($cat) ?></option>
                    <?php 
                        endforeach; 
                    endif; 
                    
                    // If current category is not in the list, add it
                    if (!$categoryFound && !empty($product['category'])):
                    ?>
                        <option value="<?= htmlspecialchars($product['category']) ?>" selected><?= htmlspecialchars($product['category']) ?></option>
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
            <input type="number" id="price" name="price" step="0.01" min="0" value="<?= htmlspecialchars($product['price']) ?>" required>
        </div>

        <div class="form-group">
            <label for="stock" class="required">Stock Quantity</label>
            <input type="number" id="stock" name="stock" min="0" value="<?= htmlspecialchars($product['stock']) ?>" required>
        </div>

        <div class="form-group">
            <label for="brand" class="required">Brand</label>
            <div class="custom-select">
                <select id="brand" name="brand" required>
                    <option value="">Select Brand</option>
                    <?php 
                    $brandFound = false;
                    if (!empty($brands)): 
                        foreach ($brands as $b): 
                            $selected = ($b === $product['brand']) ? 'selected' : '';
                            if ($b === $product['brand']) $brandFound = true;
                    ?>
                        <option value="<?= htmlspecialchars($b) ?>" <?= $selected ?>><?= htmlspecialchars($b) ?></option>
                    <?php 
                        endforeach; 
                    endif; 
                    
                    // If current brand is not in the list, add it
                    if (!$brandFound && !empty($product['brand'])):
                    ?>
                        <option value="<?= htmlspecialchars($product['brand']) ?>" selected><?= htmlspecialchars($product['brand']) ?></option>
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
                    <option value="male" <?= $product['gender'] === 'male' ? 'selected' : '' ?>>Men</option>
                    <option value="female" <?= $product['gender'] === 'female' ? 'selected' : '' ?>>Women</option>
                    <option value="unisex" <?= $product['gender'] === 'unisex' ? 'selected' : '' ?>>Unisex</option>
                </select>
            </div>
        </div>

        <div class="form-group full-width">
            <label for="image">Product Image</label>
            
            <?php if (!empty($product['image'])): ?>
                <div class="current-image">
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="Current product image">
                    <span class="current-image-label">Current image</span>
                </div>
            <?php endif; ?>
            
            <div class="file-input-container">
                <label for="image" class="file-input-label">
                    <span id="file-name">Choose a new image...</span>
                </label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>
            <div class="image-preview" id="imagePreview">
                <span class="preview-placeholder">New image preview will appear here</span>
            </div>
        </div>

        <div class="button-group">
            <button type="submit">Update Product</button>
            <a href="manage_products.php" class="back-btn">Back to Manage Products</a>
        </div>
    </form>
</div>

<script>
// Handle file input display
document.getElementById('image').addEventListener('change', function(e) {
    const fileName = e.target.files[0] ? e.target.files[0].name : 'Choose a new image...';
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
        preview.innerHTML = '<span class="preview-placeholder">New image preview will appear here</span>';
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
document.getElementById('editProductForm').addEventListener('submit', function(e) {
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

// Confirm before leaving with unsaved changes
let formChanged = false;
const formInputs = document.querySelectorAll('input, select');
formInputs.forEach(input => {
    input.addEventListener('change', function() {
        formChanged = true;
    });
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
    }
});

// Reset the formChanged flag when form is submitted
document.getElementById('editProductForm').addEventListener('submit', function() {
    formChanged = false;
});
</script>

</body>
</html>
</qodoArtifact>