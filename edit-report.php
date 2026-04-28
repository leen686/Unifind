<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
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
    header("Location: my-reports.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT r.*, c.catName 
    FROM reports r
    JOIN categories c ON r.catID = c.catID
    WHERE r.reportID = ? AND r.userID = ?
");
$stmt->bind_param("ii", $reportID, $userID);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows !== 1) {
    die("Report not found or unauthorized.");
}

$report = $res->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $itemName = trim($_POST['itemName'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $date = trim($_POST['reportDate'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($itemName === '') {
        $message = "Item name is required.";
    } elseif (strlen($itemName) < 2) {
        $message = "Item name must be at least 2 characters.";
    } elseif ($category === '') {
        $message = "Please select a category.";
    } elseif ($date === '') {
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
        $cstmt = $conn->prepare("SELECT catID FROM categories WHERE catName = ?");
        $cstmt->bind_param("s", $category);
        $cstmt->execute();
        $cres = $cstmt->get_result();

        if ($cres->num_rows !== 1) {
            $message = "Invalid category. Please select a valid category.";
        } else {
            $catID = $cres->fetch_assoc()['catID'];
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
                $ustmt = $conn->prepare("
                    UPDATE reports 
                    SET item_name=?, description=?, date=?, location=?, imageURL=?, phone=?, catID=?
                    WHERE reportID=? AND userID=?
                ");
                $ustmt->bind_param(
                    "ssssssiii",
                    $itemName,
                    $description,
                    $date,
                    $location,
                    $imagePath,
                    $phone,
                    $catID,
                    $reportID,
                    $userID
                );

                if ($ustmt->execute()) {
                    header("Location: my-reports.php");
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
    <title>UniFind | Edit Report</title>
    <link rel="stylesheet" href="styles.css" />
  </head>

  <body data-page="edit-report">
    <header class="main-header">
      <div class="container navbar">
        <a href="index.php" class="logo">
          <img src="images/logo.jpeg" alt="Unified Logo" class="logo-rect" />
        </a>

        <nav class="nav-links nav-links-full">
          <a href="index.php" class="nav-link-text">Home</a>
          <a href="reports.php" class="nav-link-text">Reports</a>
          <a href="add-report.php" class="nav-link-text">Add Report</a>
          <a href="saved.php" class="nav-link-text">Saved</a>
          <a href="my-reports.php" class="nav-link-text active-link">My Reports</a>
          <a href="profile.php" class="nav-link-text">Profile</a>
          <a href="logout.php" class="nav-btn primary-btn">Sign Out</a>
        </nav>
      </div>
    </header>

    <main class="form-page">
      <div class="container">
        <div class="page-heading">
          <h1>Edit Report</h1>
          <p>Update your report details when new or corrected information becomes available.</p>
        </div>

        <div class="form-card form-card-wide">
          <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
              <label>Item Name</label>
              <input 
                type="text" 
                name="itemName" 
                value="<?php echo htmlspecialchars($displayItemName); ?>" 
                required 
              />
            </div>

            <div class="form-row two-columns">
              <div class="form-group">
                <label>Category</label>
                <select name="category" required>
                  <?php
                  $cats = ["ID Card","Phone","Wallet","Keys","Bag","Other"];
                  foreach ($cats as $c) {
                      $sel = ($c === $displayCategory) ? "selected" : "";
                      echo "<option value=\"$c\" $sel>$c</option>";
                  }
                  ?>
                </select>
              </div>

              <div class="form-group">
                <label>Date</label>
                <input 
                  type="date" 
                  name="reportDate" 
                  value="<?php echo htmlspecialchars($displayDate); ?>" 
                  required 
                />
              </div>
            </div>

            <div class="form-group">
              <label>Location</label>
              <input 
                type="text" 
                name="location" 
                value="<?php echo htmlspecialchars($displayLocation); ?>" 
                required 
              />
            </div>

            <div class="form-group">
              <label>Description</label>
              <textarea name="description" rows="6" required><?php echo htmlspecialchars($displayDescription); ?></textarea>
            </div>

            <div class="form-group">
              <label>Phone Number</label>
              <input 
                type="text" 
                name="phone" 
                value="<?php echo htmlspecialchars($displayPhone); ?>" 
                required 
              />
            </div>

            <div class="form-group">
              <label>Replace Image (optional)</label><br />
              <img 
                src="<?php echo htmlspecialchars($report['imageURL']); ?>" 
                alt="Current image" 
                style="max-width:200px;margin-bottom:10px;border-radius:10px;" 
              />
              <input type="file" name="image" accept="image/*" />
            </div>

            <button type="submit" class="primary-btn form-btn">Save Changes</button>

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