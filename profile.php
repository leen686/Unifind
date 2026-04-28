<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST['profileName'] ?? '');
    $phone = trim($_POST['profilePhone'] ?? '');

    if ($fullName === '' || $phone === '') {
        $message = "Please fill all required fields.";
    } else {
        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '-';

        $stmt = $conn->prepare("UPDATE users SET firstName = ?, lastName = ?, phone = ? WHERE userID = ?");
        $stmt->bind_param("sssi", $firstName, $lastName, $phone, $userID);

        if ($stmt->execute()) {
            $message = "Profile updated successfully.";
        } else {
            $message = "Failed to update profile.";
        }
    }
}

$stmt = $conn->prepare("SELECT firstName, lastName, email, phone FROM users WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("User not found.");
}

$user = $result->fetch_assoc();
$fullName = trim($user['firstName'] . " " . $user['lastName']);
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>UniFind | Profile</title>
    <link rel="stylesheet" href="styles.css" />
  </head>

  <body data-page="profile">
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
          <a href="my-reports.php" class="nav-link-text">My Reports</a>
          <a href="profile.php" class="nav-link-text active-link">Profile</a>
          <a href="logout.php" class="nav-btn primary-btn">Sign Out</a>
        </nav>
      </div>
    </header>

    <main class="form-page">
      <div class="container">
        <div class="page-heading">
          <h1>Profile</h1>
          <p>View your account information and edit it only when needed.</p>
        </div>

        <div class="form-card profile-card">
          <div class="profile-top">
            <div class="profile-avatar">👤</div>
            <div>
              <h2 id="profileDisplayName"><?php echo htmlspecialchars($fullName); ?></h2>
              <p class="profile-subtext">Manage your personal information</p>
            </div>
          </div>

          <form id="profileForm" method="POST" action="profile.php">
            <div class="form-group">
              <label for="profileName">Full Name</label>
              <input 
                type="text" 
                id="profileName" 
                name="profileName"
                value="<?php echo htmlspecialchars($fullName); ?>" 
                disabled 
                required
              />
            </div>

            <div class="form-group">
              <label for="profileEmail">Email</label>
              <input 
                type="email" 
                id="profileEmail" 
                value="<?php echo htmlspecialchars($user['email']); ?>" 
                disabled 
              />
            </div>

            <div class="form-group">
              <label for="profilePhone">Phone Number</label>
              <input 
                type="text" 
                id="profilePhone" 
                name="profilePhone"
                value="<?php echo htmlspecialchars($user['phone']); ?>" 
                disabled 
                required
              />
            </div>

            <div class="profile-actions">
              <button type="button" id="editProfileBtn" class="primary-btn">
                Edit Profile
              </button>

              <button
                type="submit"
                id="saveProfileBtn"
                class="secondary-btn hidden"
              >
                Save Changes
              </button>
            </div>

            <?php if ($message !== ""): ?>
              <p class="form-message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
          </form>

          <a href="logout.php" class="secondary-btn form-btn logout-btn">
            Sign Out
          </a>
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

    <script>
      const editBtn = document.getElementById("editProfileBtn");
      const saveBtn = document.getElementById("saveProfileBtn");
      const nameInput = document.getElementById("profileName");
      const phoneInput = document.getElementById("profilePhone");

      editBtn.addEventListener("click", function () {
        nameInput.disabled = false;
        phoneInput.disabled = false;
        editBtn.classList.add("hidden");
        saveBtn.classList.remove("hidden");
      });

      document.getElementById("profileForm").addEventListener("submit", function () {
        nameInput.disabled = false;
        phoneInput.disabled = false;
      });
    </script>
  </body>
</html>