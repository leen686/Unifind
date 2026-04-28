<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

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
        sr.reportID AS savedReportID
    FROM reports r
    JOIN categories c ON r.catID = c.catID
    LEFT JOIN saved_reports sr 
        ON r.reportID = sr.reportID AND sr.userID = ?
    WHERE 1 = 1
";

$params = [$userID];
$types = "i";

if ($search !== '') {
    $sql .= " AND (
        r.item_name LIKE ? OR
        r.description LIKE ? OR
        r.location LIKE ? OR
        c.catName LIKE ?
    )";

    $searchValue = "%" . $search . "%";
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $types .= "ssss";
}

if ($category !== 'all') {
    $sql .= " AND c.catName = ?";
    $params[] = $category;
    $types .= "s";
}

if ($sort === 'oldest') {
    $sql .= " ORDER BY r.date ASC, r.reportID ASC";
} else {
    $sql .= " ORDER BY r.date DESC, r.reportID DESC";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$reports = $stmt->get_result();
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>UniFind | Reports</title>
    <link rel="stylesheet" href="styles.css" />
  </head>

  <body data-page="reports">
    <header class="main-header">
      <div class="container navbar">
        <a href="index.php" class="logo">
          <img src="images/logo.jpeg" alt="Unified Logo" class="logo-rect" />
        </a>

        <nav class="nav-links nav-links-full">
          <a href="index.php" class="nav-link-text">Home</a>
          <a href="reports.php" class="nav-link-text active-link">Reports</a>
          <a href="add-report.php" class="nav-link-text">Add Report</a>
          <a href="saved.php" class="nav-link-text">Saved</a>
          <a href="my-reports.php" class="nav-link-text">My Reports</a>
          <a href="profile.php" class="nav-link-text">Profile</a>
          <a href="logout.php" class="nav-btn primary-btn">Sign Out</a>
        </nav>
      </div>
    </header>

    <main class="reports-page">
      <div class="container">
        <div class="page-heading">
          <h1>All Reports</h1>
          <p>
            Browse all reports in one place and use search, category filtering,
            and time sorting to find relevant items.
          </p>
        </div>

        <form class="filters-box" method="GET" action="reports.php">
          <div class="filter-group">
            <label for="searchInput">Search by Keyword</label>
            <div class="search-input-wrap">
              <span class="search-icon">🔍</span>
              <input
                type="text"
                id="searchInput"
                name="search"
                value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Search item name, description, or location"
              />
            </div>
          </div>

          <div class="filter-group">
            <label for="categoryFilter">Category</label>
            <select id="categoryFilter" name="category">
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
            <label for="sortFilter">Sort by Time</label>
            <select id="sortFilter" name="sort">
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
          <div id="reportsList" class="reports-grid">
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

                  <p><?php echo htmlspecialchars($report['description']); ?></p>

                  <div class="actions">
                    <a
                      href="report-details.php?id=<?php echo $report['reportID']; ?>"
                      class="primary-btn"
                    >
                      View
                    </a>

                    <?php if ($report['savedReportID']): ?>
                      <a
                        href="toggle-save.php?id=<?php echo $report['reportID']; ?>&action=unsave&return=reports"
                        class="secondary-btn"
                      >
                        Unsave
                      </a>
                    <?php else: ?>
                      <a
                        href="toggle-save.php?id=<?php echo $report['reportID']; ?>&action=save&return=reports"
                        class="secondary-btn"
                      >
                        Save
                      </a>
                    <?php endif; ?>
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