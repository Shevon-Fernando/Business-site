<?php
/**
 * noontech - Login Page
 * Mimics OpenAI's official "Welcome back" screen interface.
 */
session_start();
include 'includes/db.php';

// If user is already logged in, redirect to home
if (isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit;
}

$error_msg = "";
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($email) || empty($password)) {
        $error_msg = "Please fill in all standard inputs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Please enter a valid email address.";
    } else {
        if ($conn && empty($db_connection_error)) {
            $sql = "SELECT id, password_hash, username, is_verified FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->bind_result($user_id, $hash, $db_username, $is_verified);

                if ($stmt->fetch() && password_verify($password, $hash)) {
                    if ($is_verified == 0) {
                        header("Location: verify.php?email=" . urlencode($email) . "&error=notverified");
                        exit;
                    }

                    $_SESSION['user_email'] = $email;
                    $_SESSION['username'] = $db_username;
                    $_SESSION['contact_success'] = "Welcome back, $db_username!";
                    header("Location: index.php");
                    exit;
                } else {
                    $error_msg = "Incorrect credentials.";
                }
                $stmt->close();
            } else {
                $error_msg = "Database statement error.";
            }
        } else {
            $error_msg = "Database connection error.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome back - noontech</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <div class="auth-wrapper">
        <div class="auth-card">
            <!-- Logo Mark -->
            <div class="auth-logo">
                <a href="index.php"
                    style="text-decoration: none; color: inherit; display: inline-flex; align-items: center; gap: 8px;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                        style="color: var(--text-primary);">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </a>
            </div>

            <h1 class="auth-title">Welcome back</h1>
            <p class="auth-subtitle">Login with your credentials or test with standard mock entries</p>

            <?php if (!empty($error_msg)): ?>
                <div
                    style="background-color: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 12px; border-radius: var(--radius-md); font-size: 13.5px; margin-bottom: 20px; text-align: left;">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <!-- Standard HTML Form -->
            <form action="login.php" method="POST" class="auth-form">
                <input type="hidden" name="action" value="login">

                <div class="form-group">
                    <label for="loginEmail" class="form-label">Email Address</label>
                    <input type="email" id="loginEmail" name="email" class="form-input" placeholder="you@domain.com"
                        required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="loginPassword" class="form-label">Password</label>
                    <input type="password" id="loginPassword" name="password" class="form-input" placeholder="••••••••"
                        required>
                </div>

                <button type="submit" class="btn btn-primary auth-btn">Continue</button>
            </form>

            <p class="auth-footer-text">
                Don't have an account? <a href="signup.php">Sign up</a>
            </p>

            <p style="margin-top: 24px; font-size: 11px; color: var(--text-tertiary);">
                <a href="index.php" style="color: inherit; text-decoration: underline;">Return to Homepage</a>
            </p>
        </div>
    </div>

</body>

</html>