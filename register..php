<?php
session_start();
include 'db.php';

$firstName = $_POST['firstName'] ?? '';
$lastName = $_POST['lastName'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$phone = $_POST['phone'] ?? '';

if (!$firstName || !$lastName || !$email || !$password) {
    die("Please fill all required fields.");
}

$check = $conn->prepare("SELECT userID FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    die("Email already exists.");
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (firstName, lastName, email, password, phone) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $firstName, $lastName, $email, $hashedPassword, $phone);

if ($stmt->execute()) {
    $_SESSION['userID'] = $stmt->insert_id;
    $_SESSION['role'] = 'user';
    header("Location: ../reports.html");
    exit();
} else {
    echo "Registration failed.";
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
        <a href="index.html" class="logo">
          <img src="images/logo.jpeg" alt="Unified Logo" class="logo-rect" />
        </a>

        <nav class="nav-links">
          <a href="index.html" class="nav-link-text">Home</a>
          <a href="login.html" class="nav-btn secondary-btn">Sign In</a>
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
                  placeholder="Create a password"
                  required
                />
              </div>
            </div>

            <button type="submit" class="primary-btn form-btn">Sign Up</button>
            <p id="registerMessage" class="form-message"></p>
          </form>

          <p class="auth-footer-text center-text">
            Already have an account?
            <a href="login.html">Sign in</a>
          </p>
        </div>
      </div>
    </main>

    <footer class="site-footer clean-footer">
      <div class="container clean-footer-content">
        <h3>
          <span class="footer-dark">Uni</span
          ><span class="footer-blue">Find</span>
        </h3>
        <p>© 2026 UniFind. Built for students, by students.</p>

        <div class="clean-footer-links">
          <a href="#">Privacy</a>
          <a href="#">Terms</a>
          <a href="#">Contact</a>
        </div>
      </div>
    </footer>

    <script src="app.js"></script>
  </body>
</html>
