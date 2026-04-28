<?php
session_start();
include 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['loginEmail'] ?? '');
    $password = trim($_POST['loginPassword'] ?? '');
    $role = trim($_POST['loginRole'] ?? 'user');

    if ($email === '' || $password === '') {
        $message = "Please enter your email and password.";
    } else {
        if ($role === "admin") {
            $stmt = $conn->prepare("SELECT adminID, email, password FROM administrators WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();

                if ($password === $admin['password']) {
                    $_SESSION['adminID'] = $admin['adminID'];
                    $_SESSION['role'] = 'admin';

                    header("Location: admin-reports.php");
                    exit();
                }
            }

            $message = "Invalid admin email or password.";
        } else {
            $stmt = $conn->prepare("SELECT userID, email, password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    $_SESSION['userID'] = $user['userID'];
                    $_SESSION['role'] = 'user';

                    header("Location: reports.php");
                    exit();
                }
            }

            $message = "Invalid user email or password.";
        }
    }
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>UniFind | Login</title>
    <link rel="stylesheet" href="styles.css" />
  </head>

  <body data-page="login">
    <header class="main-header">
      <div class="container navbar">
        <a href="index.php" class="logo">
          <img src="images/logo.jpeg" alt="Unified Logo" class="logo-rect" />
        </a>

        <nav class="nav-links">
          <a href="index.php" class="nav-link-text">Home</a>
          <a href="register.php" class="nav-btn primary-btn">Sign Up</a>
        </nav>
      </div>
    </header>

    <main class="auth-page">
      <div class="container auth-wrapper">
        <div class="auth-card modern-auth-card">
          <h1>Login</h1>
          <p class="auth-subtitle">
            Welcome back! Sign in to access your reports, saved items, and
            profile.
          </p>

          <form id="loginForm" action="login.php" method="POST">
            <div class="form-group">
              <label for="loginEmail">Email Address</label>
              <div class="input-with-icon">
                <span class="input-icon">✉️</span>
                <input
                  type="email"
                  id="loginEmail"
                  name="loginEmail"
                  placeholder="Enter your email"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label for="loginPassword">Password</label>
              <div class="input-with-icon">
                <span class="input-icon">🔒</span>
                <input
                  type="password"
                  id="loginPassword"
                  name="loginPassword"
                  placeholder="Enter your password"
                  required
                />
              </div>
            </div>

            <input type="hidden" id="loginRole" name="loginRole" value="user" />

            <div class="login-action-stack">
              <button
                type="submit"
                class="primary-btn auth-action-btn"
                onclick="document.getElementById('loginRole').value='user'"
              >
                Login as User →
              </button>

              <button
                type="submit"
                class="secondary-btn auth-action-btn"
                onclick="document.getElementById('loginRole').value='admin'"
              >
                Login as Admin →
              </button>
            </div>

            <?php if ($message !== ""): ?>
              <p class="form-message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
          </form>

          <p class="auth-footer-text center-text">
            New to UniFind?
            <a href="register.php">Sign up</a>
          </p>

          <div class="mini-info-box">
            <strong>Admin demo account</strong><br />
            Email: admin@unifind.com<br />
            Password: admin123
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