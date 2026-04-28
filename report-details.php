<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$reportID = intval($_GET['id'] ?? 0);

if ($reportID <= 0) {
    header("Location: reports.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT 
        r.reportID,
        r.item_name,
        r.description,
        r.date,
        r.location,
        r.imageURL,
        r.phone,
        r.userID AS ownerID,
        c.catName,
        sr.reportID AS savedReportID
    FROM reports r
    JOIN categories c ON r.catID = c.catID
    LEFT JOIN saved_reports sr 
        ON r.reportID = sr.reportID AND sr.userID = ?
    WHERE r.reportID = ?
");

$stmt->bind_param("ii", $userID, $reportID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Report not found.");
}

$report = $result->fetch_assoc();
$isOwner = ($report['ownerID'] == $userID);
$isSaved = !empty($report['savedReportID']);
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>UniFind | Report Details</title>
    <link rel="stylesheet" href="styles.css" />
  </head>

  <body data-page="report-details">
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

    <main class="details-page">
      <div class="container">
        <div id="reportDetails" class="details-wrapper">
          <div class="details-card">
            <div>
              <img
                class="details-image"
                src="<?php echo htmlspecialchars($report['imageURL']); ?>"
                alt="<?php echo htmlspecialchars($report['item_name']); ?>"
              />
            </div>

            <div class="details-content">
              <h1><?php echo htmlspecialchars($report['item_name']); ?></h1>

              <p><strong>Category:</strong> <?php echo htmlspecialchars($report['catName']); ?></p>
              <p><strong>Date:</strong> <?php echo htmlspecialchars($report['date']); ?></p>
              <p><strong>Location:</strong> <?php echo htmlspecialchars($report['location']); ?></p>
              <p><strong>Description:</strong> <?php echo htmlspecialchars($report['description']); ?></p>
              <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($report['phone']); ?></p>

              <div class="actions">
                <?php if ($isSaved): ?>
                  <a
                    href="toggle-save.php?id=<?php echo $report['reportID']; ?>&action=unsave&return=details"
                    class="secondary-btn"
                  >
                    Unsave
                  </a>
                <?php else: ?>
                  <a
                    href="toggle-save.php?id=<?php echo $report['reportID']; ?>&action=save&return=details"
                    class="secondary-btn"
                  >
                    Save
                  </a>
                <?php endif; ?>

                <a href="reports.php" class="secondary-btn">Back</a>

                <?php if ($isOwner): ?>
                  <a href="edit-report.php?id=<?php echo $report['reportID']; ?>" class="primary-btn">
                    Edit
                  </a>

                  <a
                    href="delete-report.php?id=<?php echo $report['reportID']; ?>"
                    class="secondary-btn"
                    onclick="return confirm('Are you sure you want to delete this report?');"
                  >
                    Delete
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
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