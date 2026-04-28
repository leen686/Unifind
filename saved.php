<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

$stmt = $conn->prepare("
    SELECT 
        r.reportID,
        r.item_name,
        r.description,
        r.date,
        r.location,
        r.imageURL,
        r.phone,
        c.catName
    FROM saved_reports sr
    JOIN reports r ON sr.reportID = r.reportID
    JOIN categories c ON r.catID = c.catID
    WHERE sr.userID = ?
    ORDER BY sr.savedAt DESC
");

$stmt->bind_param("i", $userID);
$stmt->execute();
$reports = $stmt->get_result();
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>UniFind | Saved Reports</title>
    <link rel="stylesheet" href="styles.css" />
  </head>

  <body data-page="saved">
    <header class="main-header">
      <div class="container navbar">
        <a href="index.php" class="logo">
          <img src="images/logo.jpeg" alt="Unified Logo" class="logo-rect" />
        </a>

        <nav class="nav-links nav-links-full">
          <a href="index.php" class="nav-link-text">Home</a>
          <a href="reports.php" class="nav-link-text">Reports</a>
          <a href="add-report.php" class="nav-link-text">Add Report</a>
          <a href="saved.php" class="nav-link-text active-link">Saved</a>
          <a href="my-reports.php" class="nav-link-text">My Reports</a>
          <a href="profile.php" class="nav-link-text">Profile</a>
          <a href="logout.php" class="nav-btn primary-btn">Sign Out</a>
        </nav>
      </div>
    </header>

    <main class="reports-page">
      <div class="container">
        <div class="page-heading">
          <h1>Saved Reports</h1>
          <p>View the reports you saved to revisit them later.</p>
        </div>

        <section>
          <div id="savedList" class="reports-grid">
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

                    <a
                      href="toggle-save.php?id=<?php echo $report['reportID']; ?>&action=unsave&return=saved"
                      class="secondary-btn"
                    >
                      Remove
                    </a>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="empty-state">No saved reports yet.</div>
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