<?php
session_start();
include 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST['registerName'] ?? '');
    $email = trim($_POST['registerEmail'] ?? '');
    $phone = trim($_POST['registerPhone'] ?? '');
    $password = trim($_POST['registerPassword'] ?? '');

    if ($fullName === '' || $email === '' || $phone === '' || $password === '') {
        $message = "Please fill all required fields.";
    } else {
        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '-';

        $check = $conn->prepare("SELECT userID FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $message = "This email is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (firstName, lastName, email, password, phone)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param("sssss", $firstName, $lastName, $email, $hashedPassword, $phone);

            if ($stmt->execute()) {
                $_SESSION['userID'] = $stmt->insert_id;
                $_SESSION['role'] = 'user';
                header("Location: reports.php");
                exit();
            } else {
                $message = "Registration failed.";
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
    <title>UniFind | Register</title>
    <link rel="stylesheet" href="styles.css" />
  </head>

  <body data-page="register">
    <header class="main-header">
      <div class="container navbar">
        <a href="index.php" class="logo">
          <img src="images/logo.jpeg" alt="Unified Logo" class="logo-rect" />
        </a>

        <nav class="nav-links">
          <a href="index.php" class="nav-link-text">Home</a>
          <a href="login.php" class="nav-btn secondary-btn">Sign In</a>
        </nav>
      </div>
    </header>

    <main class="auth-page">
      <div class="container auth-wrapper">
        <div class="auth-card modern-auth-card">
          <h1>Create Account</h1>
          <p class="auth-subtitle">
            Create your UniFind account to submit reports, save items, and
            manage your profile.
          </p>

          <form id="registerForm" action="register.php" method="POST">
            <div class="form-group">
              <label for="registerName">Full Name</label>
              <div class="input-with-icon">
                <span class="input-icon">👤</span>
                <input
                  type="text"
                  id="registerName"
                  name="registerName"
                  placeholder="Enter your full name"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label for="registerEmail">Email</label>
              <div class="input-with-icon">
                <span class="input-icon">✉️</span>
                <input
                  type="email"
                  id="registerEmail"
                  name="registerEmail"
                  placeholder="Enter your email"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label for="registerPhone">Phone Number</label>
              <div class="input-with-icon">
                <span class="input-icon">📱</span>
                <input
                  type="text"
                  id="registerPhone"
                  name="registerPhone"
                  placeholder="Enter your phone number"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label for="registerPassword">Password</label>
              <div class="input-with-icon">
                <span class="input-icon">🔒</span>
                <input
                  type="password"
                  id="registerPassword"
                  name="registerPassword"
                  placeholder="Create a password"
                  required
                />
              </div>
            </div>

            <button type="submit" class="primary-btn form-btn">Sign Up</button>

            <?php if ($message !== ""): ?>
              <p class="form-message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
          </form>

          <p class="auth-footer-text center-text">
            Already have an account?
            <a href="login.php">Sign in</a>
          </p>
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