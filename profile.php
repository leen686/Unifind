<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$message = "";
$messageType = "error";

function isValidPhone($phone) {
    return preg_match('/^05[0-9]{8}$/', $phone);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST['profileName'] ?? '');
    $email = trim($_POST['profileEmail'] ?? '');
    $phone = trim($_POST['profilePhone'] ?? '');

    if ($fullName === '') {
        $message = "Full name is required.";
    } elseif (strlen($fullName) < 3) {
        $message = "Full name must be at least 3 characters.";
    } elseif ($email === '') {
        $message = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif ($phone === '') {
        $message = "Phone number is required.";
    } elseif (!isValidPhone($phone)) {
        $message = "Phone number must start with 05 and contain 10 digits.";
    } else {
        $check = $conn->prepare("SELECT userID FROM users WHERE email = ? AND userID != ?");
        $check->bind_param("si", $email, $userID);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            $message = "This email is already used by another account.";
        } else {
            $nameParts = explode(' ', $fullName, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '-';

            $stmt = $conn->prepare("UPDATE users SET firstName = ?, lastName = ?, email = ?, phone = ? WHERE userID = ?");
            $stmt->bind_param("ssssi", $firstName, $lastName, $email, $phone, $userID);

            if ($stmt->execute()) {
                $message = "Profile updated successfully.";
                $messageType = "success";
            } else {
                $message = "Failed to update profile. Please try again.";
            }
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

$displayName = $_POST['profileName'] ?? $fullName;
$displayEmail = $_POST['profileEmail'] ?? $user['email'];
$displayPhone = $_POST['profilePhone'] ?? $user['phone'];
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
              <h2 id="profileDisplayName"><?php echo htmlspecialchars($displayName); ?></h2>
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
                value="<?php echo htmlspecialchars($displayName); ?>" 
                disabled 
                required
              />
            </div>

            <div class="form-group">
              <label for="profileEmail">Email</label>
              <input 
                type="email" 
                id="profileEmail" 
                name="profileEmail"
                value="<?php echo htmlspecialchars($displayEmail); ?>" 
                disabled 
                required
              />
            </div>

            <div class="form-group">
              <label for="profilePhone">Phone Number</label>
              <input 
                type="text" 
                id="profilePhone" 
                name="profilePhone"
                value="<?php echo htmlspecialchars($displayPhone); ?>" 
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
              <p class="form-message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
              </p>
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
      const emailInput = document.getElementById("profileEmail");
      const phoneInput = document.getElementById("profilePhone");

      editBtn.addEventListener("click", function () {
        nameInput.disabled = false;
        emailInput.disabled = false;
        phoneInput.disabled = false;
        editBtn.classList.add("hidden");
        saveBtn.classList.remove("hidden");
      });

      document.getElementById("profileForm").addEventListener("submit", function () {
        nameInput.disabled = false;
        emailInput.disabled = false;
        phoneInput.disabled = false;
      });
    </script>
  </body>
</html>