<?php
session_start();
include 'db.php';

$message = "";
$messageType = "error";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['loginEmail'] ?? '');
    $password = trim($_POST['loginPassword'] ?? '');

    if ($email === '') {
        $message = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif ($password === '') {
        $message = "Password is required.";
    } else {
        $stmt = $conn->prepare("SELECT adminID, email, password FROM administrators WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $adminResult = $stmt->get_result();

        if ($adminResult->num_rows === 1) {
            $admin = $adminResult->fetch_assoc();

            if ($password === $admin['password']) {
                $_SESSION['adminID'] = $admin['adminID'];
                $_SESSION['role'] = 'admin';
                header("Location: admin-reports.php");
                exit();
            } else {
                $message = "Incorrect password. Please try again.";
            }
        } else {
            $stmt = $conn->prepare("SELECT userID, email, password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $userResult = $stmt->get_result();

            if ($userResult->num_rows === 1) {
                $user = $userResult->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    $_SESSION['userID'] = $user['userID'];
                    $_SESSION['role'] = 'user';
                    header("Location: reports.php");
                    exit();
                } else {
                    $message = "Incorrect password. Please try again.";
                }
            } else {
                $message = "No account found with this email. Please sign up first.";
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
            Sign in using your account email. The system will automatically detect whether you are a user or an admin.
          </p>

          <form action="login.php" method="POST">
            <div class="form-group">
              <label for="loginEmail">Email Address</label>
              <div class="input-with-icon">
                <span class="input-icon">✉️</span>
                <input
                  type="email"
                  id="loginEmail"
                  name="loginEmail"
                  placeholder="Enter your email"
                  value="<?php echo htmlspecialchars($_POST['loginEmail'] ?? ''); ?>"
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

            <button type="submit" class="primary-btn form-btn">Sign In</button>

            <?php if ($message !== ""): ?>
              <p class="form-message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
              </p>
            <?php endif; ?>
          </form>

          <p class="auth-footer-text center-text">
            New to UniFind?
            <a href="register.php">Sign up</a>
          </p>
        </div>
      </div>
    </main>

    <footer class="site-footer clean-footer">
      <div class="container clean-footer-content">
        <h3><span class="footer-dark">Uni</span><span class="footer-blue">Find</span></h3>
        <p>©️ 2026 UniFind. Built for students, by students.</p>
      </div>
    </footer>
  </body>
</html>