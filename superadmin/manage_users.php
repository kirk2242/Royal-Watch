
<?php
session_start();
require '../config/database.php';

// Ensure only Superadmin can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit();
}

// Handle search and filtering
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$roleFilter = isset($_GET['role']) ? $_GET['role'] : '';

// Build the query based on filters
$query = "SELECT * FROM users WHERE role != 'superadmin'";
$params = [];

if (!empty($search)) {
    $query .= " AND (username LIKE ? OR id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($roleFilter)) {
    $query .= " AND role = ?";
    $params[] = $roleFilter;
}

$query .= " ORDER BY role ASC, username ASC";

// Prepare and execute the query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get distinct roles for the filter dropdown
$roleStmt = $pdo->query("SELECT DISTINCT role FROM users WHERE role != 'superadmin' ORDER BY role");
$roles = $roleStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/css/manage_users.css">
</head>
<body>

<div class="container">
    <h2>Manage Users</h2>
    
    <div class="filter-section">
        <div class="search-box">
            <form action="" method="GET" id="searchForm">
                <input type="text" name="search" placeholder="Search by username or ID" value="<?= htmlspecialchars($search) ?>">
                <?php if (!empty($roleFilter)): ?>
                    <input type="hidden" name="role" value="<?= htmlspecialchars($roleFilter) ?>">
                <?php endif; ?>
            </form>
        </div>
        
        <select class="filter-dropdown" id="roleFilter">
            <option value="">All Roles</option>
            <?php foreach ($roles as $role): ?>
                <option value="<?= $role ?>" <?= $roleFilter === $role ? 'selected' : '' ?>><?= ucfirst($role) ?></option>
            <?php endforeach; ?>
        </select>
        
        <a href="add_user.php" class="btn">Add New User</a>
    </div>
    
    <div class="user-count">
        Showing <?= count($users) ?> user<?= count($users) !== 1 ? 's' : '' ?>
        <?= !empty($search) ? " matching \"" . htmlspecialchars($search) . "\"" : "" ?>
        <?= !empty($roleFilter) ? " with role \"" . ucfirst(htmlspecialchars($roleFilter)) . "\"" : "" ?>
    </div>
    
    <?php if (count($users) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td>
                            <span class="role-badge role-<?= $user['role'] ?>">
                                <?= ucfirst($user['role']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn">Edit</a>
                            <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <p>No users found matching your criteria.</p>
            <a href="manage_users.php" class="btn">Clear Filters</a>
        </div>
    <?php endif; ?>
    
    <!-- Pagination placeholder - implement if needed -->
    <!--
    <div class="pagination">
        <a href="#" class="active">1</a>
        <a href="#">2</a>
        <a href="#">3</a>
        <a href="#">Next</a>
    </div>
    -->
    
    <a href="dashboard_superadmin.php" class="btn back-btn">Back to Dashboard</a>
</div>

<script>
// Handle role filter changes
document.getElementById('roleFilter').addEventListener('change', function() {
    const searchForm = document.getElementById('searchForm');
    const searchValue = searchForm.querySelector('input[name="search"]').value;
    
    // Create or update the role hidden input
    let roleInput = searchForm.querySelector('input[name="role"]');
    if (!roleInput && this.value) {
        roleInput = document.createElement('input');
        roleInput.type = 'hidden';
        roleInput.name = 'role';
        searchForm.appendChild(roleInput);
    }
    
    if (roleInput) {
        if (this.value) {
            roleInput.value = this.value;
        } else {
            searchForm.removeChild(roleInput);
        }
    }
    
    searchForm.submit();
});

// Submit search on enter key
document.querySelector('.search-box input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('searchForm').submit();
    }
});
</script>

</body>
</html>
</qodoArtifact>