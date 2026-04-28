<?php
session_start();
include 'db.php';

if (!isset($_SESSION['adminID']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$reportID = intval($_GET['id'] ?? 0);
$message = "";
$messageType = "error";

function isValidPhone($phone) {
    return preg_match('/^05[0-9]{8}$/', $phone);
}

function isValidImage($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    return isset($file['type']) && in_array($file['type'], $allowedTypes);
}

if ($reportID <= 0) {
    header("Location: admin-reports.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT r.*, c.catName
    FROM reports r
    JOIN categories c ON r.catID = c.catID
    WHERE r.reportID = ?
");
$stmt->bind_param("i", $reportID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Report not found.");
}

$report = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $itemName = trim($_POST['itemName'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $reportDate = trim($_POST['reportDate'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($itemName === '') {
        $message = "Item name is required.";
    } elseif (strlen($itemName) < 2) {
        $message = "Item name must be at least 2 characters.";
    } elseif ($category === '') {
        $message = "Please select a category.";
    } elseif ($reportDate === '') {
        $message = "Date is required.";
    } elseif ($location === '') {
        $message = "Location is required.";
    } elseif (strlen($location) < 2) {
        $message = "Location must be at least 2 characters.";
    } elseif ($description === '') {
        $message = "Description is required.";
    } elseif (strlen($description) < 10) {
        $message = "Description must be at least 10 characters.";
    } elseif ($phone === '') {
        $message = "Phone number is required.";
    } elseif (!isValidPhone($phone)) {
        $message = "Phone number must start with 05 and contain 10 digits.";
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] === 0 && !isValidImage($_FILES['image'])) {
        $message = "Only JPG, PNG, JPEG, or WEBP images are allowed.";
    } else {
        $catStmt = $conn->prepare("SELECT catID FROM categories WHERE catName = ?");
        $catStmt->bind_param("s", $category);
        $catStmt->execute();
        $catResult = $catStmt->get_result();

        if ($catResult->num_rows !== 1) {
            $message = "Invalid category. Please select a valid category.";
        } else {
            $catID = $catResult->fetch_assoc()['catID'];
            $imagePath = $report['imageURL'];

            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $imageName = time() . "_" . basename($_FILES["image"]["name"]);
                $targetPath = "uploads/" . $imageName;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {
                    $imagePath = $targetPath;
                } else {
                    $message = "Failed to upload image. Please try again.";
                }
            }

            if ($message === "") {
                $adminID = $_SESSION['adminID'];

                $update = $conn->prepare("
                    UPDATE reports
                    SET item_name = ?, description = ?, date = ?, location = ?, imageURL = ?, phone = ?, catID = ?, adminID = ?
                    WHERE reportID = ?
                ");

                $update->bind_param(
                    "ssssssiii",
                    $itemName,
                    $description,
                    $reportDate,
                    $location,
                    $imagePath,
                    $phone,
                    $catID,
                    $adminID,
                    $reportID
                );

                if ($update->execute()) {
                    header("Location: admin-reports.php");
                    exit();
                } else {
                    $message = "Failed to update report. Please try again.";
                }
            }
        }
    }
}

$displayItemName = $_POST['itemName'] ?? $report['item_name'];
$displayCategory = $_POST['category'] ?? $report['catName'];
$displayDate = $_POST['reportDate'] ?? $report['date'];
$displayLocation = $_POST['location'] ?? $report['location'];
$displayDescription = $_POST['description'] ?? $report['description'];
$displayPhone = $_POST['phone'] ?? $report['phone'];
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>UniFind | Admin Edit Report</title>
    <link rel="stylesheet" href="styles.css" />
  </head>

  <body data-page="admin-edit-report">
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

    <main class="form-page">
      <div class="container">
        <div class="page-heading admin-heading">
          <span class="admin-badge">Admin Panel</span>
          <h1>Edit Report</h1>
          <p>Update any report and the changes will appear for all users.</p>
        </div>

        <div class="form-card form-card-wide">
          <form method="POST" action="admin-edit-report.php?id=<?php echo $reportID; ?>" enctype="multipart/form-data">
            <div class="form-group">
              <label for="adminEditItemName">Item Name</label>
              <input
                type="text"
                id="adminEditItemName"
                name="itemName"
                value="<?php echo htmlspecialchars($displayItemName); ?>"
                required
              />
            </div>

            <div class="form-row two-columns">
              <div class="form-group">
                <label for="adminEditCategory">Category</label>
                <select id="adminEditCategory" name="category" required>
                  <?php
                    $categories = ["ID Card", "Phone", "Wallet", "Keys", "Bag", "Other"];
                    foreach ($categories as $cat) {
                        $selected = ($cat === $displayCategory) ? "selected" : "";
                        echo "<option value=\"$cat\" $selected>$cat</option>";
                    }
                  ?>
                </select>
              </div>

              <div class="form-group">
                <label for="adminEditReportDate">Date</label>
                <input
                  type="date"
                  id="adminEditReportDate"
                  name="reportDate"
                  value="<?php echo htmlspecialchars($displayDate); ?>"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label for="adminEditLocation">Location</label>
              <input
                type="text"
                id="adminEditLocation"
                name="location"
                value="<?php echo htmlspecialchars($displayLocation); ?>"
                required
              />
            </div>

            <div class="form-group">
              <label for="adminEditDescription">Description</label>
              <textarea
                id="adminEditDescription"
                name="description"
                rows="6"
                required
              ><?php echo htmlspecialchars($displayDescription); ?></textarea>
            </div>

            <div class="form-group">
              <label for="adminEditPhone">Phone Number</label>
              <input
                type="text"
                id="adminEditPhone"
                name="phone"
                value="<?php echo htmlspecialchars($displayPhone); ?>"
                required
              />
            </div>

            <div class="form-group">
              <label for="adminEditImage">Replace Image</label><br />
              <img
                src="<?php echo htmlspecialchars($report['imageURL']); ?>"
                alt="Current report image"
                style="max-width: 220px; border-radius: 12px; margin-bottom: 12px;"
              />
              <input type="file" id="adminEditImage" name="image" accept="image/*" />
            </div>

            <button type="submit" class="primary-btn form-btn">
              Save Changes
            </button>

            <?php if ($message !== ""): ?>
              <p class="form-message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
              </p>
            <?php endif; ?>
          </form>
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