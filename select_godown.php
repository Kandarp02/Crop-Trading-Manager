<?php
session_start();
include 'includes/db.php';
include 'includes/auth.php';

$admin_username = $_SESSION['admin_username'] ?? 'Unknown';
$message = "";

// Handle add godown
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_godown'])) {
    $newGodown = trim($_POST['new_godown_name']);
    if ($newGodown !== "") {
        $stmt = $conn->prepare("INSERT INTO godowns (name) VALUES (?)");
        $stmt->bind_param("s", $newGodown);
        $stmt->execute();
        $stmt->close();
        $_SESSION['toast'] = "✅ New godown added successfully!";
        header("Location: select_godown.php");
        exit();
    }
}

// Handle update godown
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_godown'])) {
    $id = intval($_POST['update_id']);
    $newName = trim($_POST['update_name']);
    if ($newName !== "") {
        $stmt = $conn->prepare("UPDATE godowns SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $newName, $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['toast'] = "✅ Godown updated successfully!";
        header("Location: select_godown.php");
        exit();
    }
}

// Handle delete godown
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_godown'])) {
    $id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM godowns WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['toast'] = "✅ Godown deleted successfully!";
    header("Location: select_godown.php");
    exit();
}

// Handle godown selection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['godown_id'])) {
    $_SESSION['godown_id'] = intval($_POST['godown_id']);
    header("Location: dashboard.php");
    exit();
}

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$searchSql = $search ? "WHERE name LIKE ?" : "";
$searchParam = $search ? "%" . $search . "%" : "";

// Pagination
$perPage = 6;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Count total godowns
$countQuery = $conn->prepare("SELECT COUNT(*) FROM godowns $searchSql");
if ($search) $countQuery->bind_param("s", $searchParam);
$countQuery->execute();
$countQuery->bind_result($totalGodowns);
$countQuery->fetch();
$countQuery->close();

$totalPages = ceil($totalGodowns / $perPage);

// Fetch paginated godowns
$godowns = [];
$query = $search
    ? $conn->prepare("SELECT id, name FROM godowns WHERE name LIKE ? ORDER BY id ASC LIMIT ?, ?")
    : $conn->prepare("SELECT id, name FROM godowns ORDER BY id ASC LIMIT ?, ?");

if ($search) {
    $query->bind_param("sii", $searchParam, $offset, $perPage);
} else {
    $query->bind_param("ii", $offset, $perPage);
}
$query->execute();
$result = $query->get_result();
while ($row = $result->fetch_assoc()) {
    $godowns[] = $row;
}
$query->close();

// Show toast if search yields no result
if ($search !== "" && count($godowns) === 0) {
    $_SESSION['toast'] = "❌ Godown name not found!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Select Godown - Chamunda Mata Traders</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="theme.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    :root {
        --primary: #2e7d32;
        --accent: #a5d6a7;
        --dark: #1b5e20;
        --bg: #f1f8e9;
    }

    body {
        background-color: var(--bg);
        font-family: 'Segoe UI', sans-serif;
        margin: 0;
        padding-bottom: 80px;
    }

    .container {
        max-width: 960px;
    }

    .godown-card {
        background-color: white;
        border: 2px solid var(--accent);
        border-radius: 10px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .godown-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .text-primary {
        color: var(--primary) !important;
        font-weight: bold;
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-primary:hover {
        background-color: #256b28;
        border-color: #1f5d23;
    }

    .btn-success {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-success:hover {
        background-color: #256b28;
        border-color: #1f5d23;
    }

    .page-link {
        color: var(--primary);
    }

    .page-item.active .page-link {
        background-color: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .modal-header {
        background-color: var(--accent);
    }

    .modal-title {
        color: var(--dark);
        font-weight: 600;
    }

    footer {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: var(--dark);
        color: white;
        text-align: center;
        font-size: 13px;
        padding: 10px 12px;
        z-index: 999;
    }

    footer a {
        color: white;
        text-decoration: none;
    }

    .toast {
        font-size: 0.95rem;
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    @media (max-width: 576px) {
        .container {
            padding: 0 15px;
        }

        .btn {
            margin-top: 5px;
        }

        .godown-card {
            text-align: center;
        }
    }
</style>

</head>
<body>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted">🔐 Logged in as: <strong><?= htmlspecialchars($admin_username) ?></strong></p>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>

    <h2 class="text-center text-primary mb-3">🏢 Select a Godown</h2>

    <!-- Search and Reset -->
    <form method="GET" class="d-flex justify-content-center mb-3">
        <input type="text" name="search" class="form-control w-50 me-2" placeholder="Search godown..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-primary me-2">Search</button>
        <a href="select_godown.php" class="btn btn-secondary">Reset</a>
    </form>

    <!-- Toast message -->
    <?php if (isset($_SESSION['toast'])): ?>
        <div class="toast align-items-center text-white bg-danger border-0 position-fixed top-0 end-0 m-4 show"
             id="toast" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">
                    <?= $_SESSION['toast']; unset($_SESSION['toast']); ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Godown cards -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
        <?php foreach ($godowns as $godown): ?>
            <div class="col">
                <div class="card godown-card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?= htmlspecialchars($godown['name']) ?></h5>

                        <!-- Select Form -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="godown_id" value="<?= $godown['id'] ?>">
                            <button type="submit" class="btn btn-outline-primary btn-sm mt-2">Select</button>
                        </form>

                        <!-- Edit Button -->
                        <button type="button" class="btn btn-warning btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#updateModal<?= $godown['id'] ?>">✏️ Edit</button>

                        <!-- Delete Form -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?= $godown['id'] ?>">
                            <button type="submit" name="delete_godown" class="btn btn-danger btn-sm mt-2" onclick="return confirm('Delete this godown?')">🗑️ Delete</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="updateModal<?= $godown['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <form method="POST" class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Update Godown</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="text" name="update_name" value="<?= htmlspecialchars($godown['name']) ?>" class="form-control" required>
                            <input type="hidden" name="update_id" value="<?= $godown['id'] ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="update_godown" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Add Button -->
    <div class="text-center mt-4">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addGodownModal">➕ Add New Godown</button>
    </div>

    <!-- Pagination -->
    <nav class="d-flex justify-content-center mt-4">
        <ul class="pagination">
            <?php if ($page > 1): ?>
                <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">« Prev</a></li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next »</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addGodownModal" tabindex="-1" aria-labelledby="addGodownModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGodownModalLabel">Add New Godown</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" name="new_godown_name" class="form-control" placeholder="Enter godown name" required>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_godown" class="btn btn-success">Add Godown</button>
            </div>
        </form>
    </div>
</div>

<footer>
    <p>&copy; 2025 Chamunda Mata Traders.<br> Built by Kandarp Patil | 📧 <a href="mailto:kandarppatil2@gmail.com">kandarppatil2@gmail.com</a></p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Redirect to first page when using back button
    window.addEventListener('pageshow', function (event) {
        if (event.persisted || performance.navigation.type === 2) {
            const params = new URLSearchParams(window.location.search);
            if (!params.has('page') || params.get('page') != 1) {
                window.location.href = 'select_godown.php?page=1';
            }
        }
    });

    // Auto show toast
    document.addEventListener('DOMContentLoaded', function () {
        const toastEl = document.getElementById('toast');
        if (toastEl) {
            new bootstrap.Toast(toastEl, { delay: 3000 }).show();
        }
    });
</script>

</body>
</html>
