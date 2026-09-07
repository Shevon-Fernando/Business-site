<?php
session_start();
include 'includes/db.php';

$error_msg = "";
$success_msg = "";

$email = isset($_GET['email']) ? htmlspecialchars(trim($_GET['email'])) : '';

if (isset($_GET['error']) && $_GET['error'] === 'notverified') {
    $error_msg = "Your email has not been verified yet. Please enter the OTP sent to your email to verify your account.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'verify') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';

    if (empty($email) || empty($otp)) {
        $error_msg = "Please provide both email and OTP.";
    } elseif ($conn && empty($db_connection_error)) {
        $sql = "SELECT id, otp_code, otp_expires_at FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // Check expiry
                if (strtotime($user['otp_expires_at']) < time()) {
                    $error_msg = "Your OTP has expired. Please request a new one.";
                } elseif ($user['otp_code'] === $otp) {
                    // Verify success
                    $update_sql = "UPDATE users SET is_verified = 1, otp_code = NULL, otp_expires_at = NULL WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("i", $user['id']);
                    $update_stmt->execute();

                    $_SESSION['contact_success'] = "Email verified successfully! You can now log in.";
                    header("Location: login.php");
                    exit;
                } else {
                    $error_msg = "Invalid 6-digit code. Please try again.";
                }
            } else {
                $error_msg = "Account with this email not found.";
            }
            $stmt->close();
        } else {
            $error_msg = "Database statement error.";
        }
    } else {
        $error_msg = "Database connection error.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - noontech</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .otp-input {
            letter-spacing: 4px;
            font-size: 18px;
            text-align: center;
        }
    </style>
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

            <h1 class="auth-title">Verify your email</h1>
            <p class="auth-subtitle">We sent a 6-digit code to <strong>
                    <?php echo htmlspecialchars($email); ?>
                </strong></p>

            <?php if (!empty($error_msg)): ?>
                <div
                    style="background-color: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 12px; border-radius: var(--radius-md); font-size: 13.5px; margin-bottom: 20px;">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <form action="verify.php?email=<?php echo urlencode($email); ?>" method="POST" class="auth-form">
                <input type="hidden" name="action" value="verify">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

                <div class="form-group">
                    <label for="otpCode" class="form-label">6-Digit Code</label>
                    <input type="text" id="otpCode" name="otp" class="form-input otp-input" placeholder="000000"
                        maxlength="6" pattern="\d{6}" required>
                </div>

                <button type="submit" class="btn btn-primary auth-btn">Verify Account</button>
            </form>

            <p style="margin-top: 24px; font-size: 11px; color: var(--text-tertiary); text-align: center;">
                <a href="login.php" style="color: inherit; text-decoration: underline;">Return to Login</a>
            </p>
        </div>
    </div>
</body>

</html>