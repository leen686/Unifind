<?php
session_start();
include 'db.php';

if (!isset($_SESSION['adminID']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? 'all');
$sort = trim($_GET['sort'] ?? 'newest');

$sql = "
  SELECT 
    r.reportID,
    r.item_name,
    r.description,
    r.date,
    r.location,
    r.imageURL,
    r.phone,
    c.catName,
    u.email AS ownerEmail
  FROM reports r
  JOIN categories c ON r.catID = c.catID
  JOIN users u ON r.userID = u.userID
  WHERE 1=1
";

$params = [];
$types = "";

if ($search !== '') {
    $sql .= " AND (
      r.item_name LIKE ? OR
      r.description LIKE ? OR
      r.location LIKE ? OR
      c.catName LIKE ? OR
      u.email LIKE ?
    )";

    $value = "%" . $search . "%";
    $params = [$value, $value, $value, $value, $value];
    $types = "sssss";
}

if ($category !== 'all') {
    $sql .= " AND c.catName = ?";
    $params[] = $category;
    $types .= "s";
}

if ($sort === "oldest") {
    $sql .= " ORDER BY r.date ASC, r.reportID ASC";
} else {
    $sql .= " ORDER BY r.date DESC, r.reportID DESC";
}

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$reports = $stmt->get_result();
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>UniFind | Admin Reports</title>
    <link rel="stylesheet" href="styles.css" />
  </head>

  <body data-page="admin-reports">
    <header class="main-header admin-header">
      <div class="container navbar">
        <a href="index.php" class="logo">
          <img src="images/logo.jpeg" alt="Unified Logo" class="logo-rect" />
        </a>

        <nav class="nav-links nav-links-full">
          <a href="admin-reports.php" class="nav-link-text active-link">All Reports</a>
          <a href="logout.php" class="nav-btn primary-btn">Sign Out</a>
        </nav>
      </div>
    </header>

    <main class="reports-page">
      <div class="container">
        <div class="page-heading admin-heading">
          <span class="admin-badge">Admin Panel</span>
          <h1>Manage All Reports</h1>
          <p>
            This page is for administrators only. You can view, search, edit,
            and delete all reports across the platform.
          </p>
        </div>

        <form class="filters-box" method="GET" action="admin-reports.php">
          <div class="filter-group">
            <label for="adminSearchInput">Search by Keyword</label>
            <div class="search-input-wrap">
              <span class="search-icon">🔍</span>
              <input
                type="text"
                id="adminSearchInput"
                name="search"
                value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Search item name, description, location, or owner"
              />
            </div>
          </div>

          <div class="filter-group">
            <label for="adminCategoryFilter">Category</label>
            <select id="adminCategoryFilter" name="category">
              <option value="all" <?php if ($category === 'all') echo 'selected'; ?>>All Categories</option>
              <option value="ID Card" <?php if ($category === 'ID Card') echo 'selected'; ?>>ID Card</option>
              <option value="Phone" <?php if ($category === 'Phone') echo 'selected'; ?>>Phone</option>
              <option value="Wallet" <?php if ($category === 'Wallet') echo 'selected'; ?>>Wallet</option>
              <option value="Keys" <?php if ($category === 'Keys') echo 'selected'; ?>>Keys</option>
              <option value="Bag" <?php if ($category === 'Bag') echo 'selected'; ?>>Bag</option>
              <option value="Other" <?php if ($category === 'Other') echo 'selected'; ?>>Other</option>
            </select>
          </div>

          <div class="filter-group">
            <label for="adminSortFilter">Sort by Time</label>
            <select id="adminSortFilter" name="sort">
              <option value="newest" <?php if ($sort === 'newest') echo 'selected'; ?>>Newest First</option>
              <option value="oldest" <?php if ($sort === 'oldest') echo 'selected'; ?>>Oldest First</option>
            </select>
          </div>

          <div class="filter-group filter-button-group">
            <label>&nbsp;</label>
            <button type="submit" class="primary-btn">Apply</button>
          </div>
        </form>

        <section>
          <div id="adminReportsList" class="reports-grid">
            <?php if ($reports->num_rows > 0): ?>
              <?php while ($report = $reports->fetch_assoc()): ?>
                <div class="report-card">
                  <img
                    src="<?php echo htmlspecialchars($report['imageURL']); ?>"
                    alt="<?php echo htmlspecialchars($report['item_name']); ?>"
                  />

                  <h3><?php echo htmlspecialchars($report['item_name']); ?></h3>

                  <div class="meta">
                    <strong>Category:</strong>
                    <?php echo htmlspecialchars($report['catName']); ?>
                  </div>

                  <div class="meta">
                    <strong>Date:</strong>
                    <?php echo htmlspecialchars($report['date']); ?>
                  </div>

                  <div class="meta">
                    <strong>Location:</strong>
                    <?php echo htmlspecialchars($report['location']); ?>
                  </div>

                  <div class="meta">
                    <strong>Owner:</strong>
                    <?php echo htmlspecialchars($report['ownerEmail']); ?>
                  </div>

                  <p><?php echo htmlspecialchars($report['description']); ?></p>

                  <div class="actions">
                    <a
                      href="admin-edit-report.php?id=<?php echo $report['reportID']; ?>"
                      class="primary-btn"
                    >
                      Edit
                    </a>

                    <a
                      href="admin-delete-report.php?id=<?php echo $report['reportID']; ?>"
                      class="secondary-btn"
                      onclick="return confirm('Are you sure you want to delete this report?');"
                    >
                      Delete
                    </a>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="empty-state">No reports found.</div>
            <?php endif; ?>
          </div>
        </section>
      </div>
    </main>

    <footer class="site-footer clean-footer">
      <div class="container clean-footer-content">
        <h3>
          <span class="footer-dark">Uni</span><span class="footer-blue">Find</span>
        </h3>
        <p>©️ 2026 UniFind. Built for students, by students.</p>

        <div class="clean-footer-links">
          <a href="#">Privacy</a>
          <a href="#">Terms</a>
          <a href="#">Contact</a>
        </div>
      </div>
    </footer>
  </body>
</html>