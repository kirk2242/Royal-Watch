
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

// Pagination setup
$perPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Build the WHERE clause and params
$where = "role != 'superadmin'";
$params = [];

if (!empty($search)) {
    $where .= " AND (username LIKE ? OR id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($roleFilter)) {
    $where .= " AND role = ?";
    $params[] = $roleFilter;
}

// Count total filtered users for pagination
$countQuery = "SELECT COUNT(*) FROM users WHERE $where";
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalUsers = $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalUsers / $perPage));

// Fetch users for current page
$query = "SELECT * FROM users WHERE $where ORDER BY role ASC, username ASC LIMIT $perPage OFFSET $offset";
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
                <?php if ($page > 1): ?>
                    <input type="hidden" name="page" value="<?= $page ?>">
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
        (Page <?= $page ?> of <?= $totalPages ?>)
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
    
    <!-- Pagination controls -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php
        // Build base URL for pagination links
        $baseUrl = strtok($_SERVER["REQUEST_URI"], '?');
        $queryParams = $_GET;
        ?>
        <?php if ($page > 1): ?>
            <?php $queryParams['page'] = $page - 1; ?>
            <a href="<?= htmlspecialchars($baseUrl . '?' . http_build_query($queryParams)) ?>">&laquo; Prev</a>
        <?php endif; ?>
        <?php
        // Show up to 5 page links centered around current page
        $start = max(1, $page - 2);
        $end = min($totalPages, $page + 2);
        if ($start > 1) echo '<span>...</span>';
        for ($i = $start; $i <= $end; $i++):
            $queryParams['page'] = $i;
        ?>
            <a href="<?= htmlspecialchars($baseUrl . '?' . http_build_query($queryParams)) ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($end < $totalPages) echo '<span>...</span>'; ?>
        <?php if ($page < $totalPages): ?>
            <?php $queryParams['page'] = $page + 1; ?>
            <a href="<?= htmlspecialchars($baseUrl . '?' . http_build_query($queryParams)) ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
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

    // Reset to first page on filter change
    let pageInput = searchForm.querySelector('input[name="page"]');
    if (pageInput) {
        searchForm.removeChild(pageInput);
    }
    
    searchForm.submit();
});

// Submit search on enter key
document.querySelector('.search-box input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        // Reset to first page on search
        let pageInput = document.querySelector('#searchForm input[name="page"]');
        if (pageInput) {
            pageInput.parentNode.removeChild(pageInput);
        }
        document.getElementById('searchForm').submit();
    }
});
</script>

</body>
</html>
