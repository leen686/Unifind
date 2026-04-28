<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $itemName = trim($_POST['itemName'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $reportDate = trim($_POST['reportDate'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (
        $itemName === '' ||
        $category === '' ||
        $reportDate === '' ||
        $location === '' ||
        $description === '' ||
        $phone === ''
    ) {
        $message = "Please fill all required fields.";
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
        $message = "Image is required.";
    } else {
        $catStmt = $conn->prepare("SELECT catID FROM categories WHERE catName = ?");
        $catStmt->bind_param("s", $category);
        $catStmt->execute();
        $catResult = $catStmt->get_result();

        if ($catResult->num_rows !== 1) {
            $message = "Invalid category.";
        } else {
            $catRow = $catResult->fetch_assoc();
            $catID = $catRow['catID'];

            $imageName = time() . "_" . basename($_FILES["image"]["name"]);
            $targetPath = "uploads/" . $imageName;

            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {
                $message = "Failed to upload image.";
            } else {
                $userID = $_SESSION['userID'];
                $adminID = null;

                $stmt = $conn->prepare(
                    "INSERT INTO reports 
                    (item_name, description, date, location, imageURL, phone, userID, catID, adminID)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );

                $stmt->bind_param(
                    "ssssssiii",
                    $itemName,
                    $description,
                    $reportDate,
                    $location,
                    $targetPath,
                    $phone,
                    $userID,
                    $catID,
                    $adminID
                );

                if ($stmt->execute()) {
                    header("Location: my-reports.php");
                    exit();
                } else {
                    $message = "Failed to submit report.";
                }
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>UniFind | Add Report</title>
    <link rel="stylesheet" href="styles.css" />
  </head>

  <body data-page="add-report">
    <header class="main-header">
      <div class="container navbar">
        <a href="index.php" class="logo">
          <img src="images/logo.jpeg" alt="Unified Logo" class="logo-rect" />
        </a>

        <nav class="nav-links nav-links-full">
          <a href="index.php" class="nav-link-text">Home</a>
          <a href="reports.php" class="nav-link-text">Reports</a>
          <a href="add-report.php" class="nav-link-text active-link">Add Report</a>
          <a href="saved.php" class="nav-link-text">Saved</a>
          <a href="my-reports.php" class="nav-link-text">My Reports</a>
          <a href="profile.php" class="nav-link-text">Profile</a>
          <a href="logout.php" class="nav-btn primary-btn">Sign Out</a>
        </nav>
      </div>
    </header>

    <main class="form-page">
      <div class="container">
        <div class="page-heading">
          <h1>Add Item Report</h1>
          <p>Submit a new item report using the structured form below.</p>
        </div>

        <div class="form-card form-card-wide">
          <form id="reportForm" action="add-report.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
              <label for="itemName">Item Name</label>
              <input
                type="text"
                id="itemName"
                name="itemName"
                placeholder="Enter item name"
                required
              />
            </div>

            <div class="form-row two-columns">
              <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                  <option value="">Select Category</option>
                  <option value="ID Card">ID Card</option>
                  <option value="Phone">Phone</option>
                  <option value="Wallet">Wallet</option>
                  <option value="Keys">Keys</option>
                  <option value="Bag">Bag</option>
                  <option value="Other">Other</option>
                </select>
              </div>

              <div class="form-group">
                <label for="reportDate">Date</label>
                <input type="date" id="reportDate" name="reportDate" required />
              </div>
            </div>

            <div class="form-group">
              <label for="location">Location</label>
              <input
                type="text"
                id="location"
                name="location"
                placeholder="Enter location"
                required
              />
            </div>

            <div class="form-group">
              <label for="description">Description</label>
              <textarea
                id="description"
                name="description"
                rows="6"
                placeholder="Describe the item clearly"
                required
              ></textarea>
            </div>

            <div class="form-group">
              <label for="phone">Phone Number</label>
              <input
                type="text"
                id="phone"
                name="phone"
                placeholder="Enter phone number"
                required
              />
            </div>

            <div class="form-group">
              <label for="image">Upload Image</label>
              <input type="file" id="image" name="image" accept="image/*" required />
            </div>

            <button type="submit" class="primary-btn form-btn">
              Submit Report
            </button>

            <?php if ($message !== ""): ?>
              <p class="form-message"><?php echo htmlspecialchars($message); ?></p>
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