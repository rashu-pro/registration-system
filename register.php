<?php
/**
 * Template Name: Registration
 */
session_start();
require_once 'config.php';
global $conn;

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            margin: 0;
            padding: 0;
        }

        .top-logo {
            text-align: center;
            padding: 20px 0 10px;
        }

        .top-logo img {
            width: 50px;
            border-radius: 10px;
        }

        .register-wrapper {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: calc(100vh - 80px);
            padding: 20px 15px 40px;
        }

        .register-box {
            border: 1px solid #ddd;
            border-radius: 8px;
            max-width: 380px;
            width: 100%;
            padding: 30px 25px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .register-box h2 {
            font-size: 22px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .register-box label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
            font-size: 14px;
        }

        .register-box input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .register-box button {
            width: 100%;
            background-color: #FFD814;
            color: #111;
            padding: 10px;
            border: none;
            font-weight: bold;
            border-radius: 20px;
            margin-top: 15px;
            cursor: pointer;
        }

        .register-box button:hover {
            background-color: #f7ca00;
        }

        .info-text {
            font-size: 12px;
            color: #555;
            margin-top: 5px;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        .footer {
            border-top: 1px solid #ddd;
            padding: 15px 0;
            text-align: center;
            font-size: 12px;
            background-color: #fff;
            color: #555;
            position: relative; /* changed for zoom fix */
            margin-top: auto;
        }

        .footer a {
            color: #0066c0;
            text-decoration: none;
            margin: 0 10px;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 576px) {
            .register-wrapper {
                padding-top: 1px !important;
                padding-bottom: 10px !important;
                justify-content: center;
            }

            .register-box {
                padding: 25px 20px;
                margin-top: 40px;
            }

            .top-logo img {
                width: 60px;
            }

            .footer {
                margin-top: -30px !important;
            }
        }

        @media (max-width: 768px) {
            .register-wrapper {
                padding-top: 50px;
                padding-bottom: 10px;
            }

            .footer {
                margin-top: -20px !important;
            }
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 16px;
            color: #999;
        }

        .password-match {
            font-size: 12px;
            margin-top: 3px;
        }

        .match {
            color: green;
        }

        .mismatch {
            color: red;
        }
    </style>
</head>
<body>

<div class="top-logo">
    <a href="/"><img src="https://flacofy.com/wp-content/uploads/2025/01/logo.png" alt="Logo"></a>
</div>

<div class="register-wrapper">
    <div class="register-box">
        <div class="logo">
            <h2><strong>SIGN UP</strong></h2>
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $error = "Security validation failed. Please try again.";
            } else {
                $full_name = htmlspecialchars(trim($_POST['full_name']));
                $contact = htmlspecialchars(trim($_POST['contact']));
                $password = trim($_POST['password']);
                $confirm_password = trim($_POST['confirm_password']);

                $error = null;

                if (empty($full_name) || empty($contact) || empty($password) || empty($confirm_password)) {
                    $error = "All fields are required!";
                } elseif (strlen($password) < 6) {
                    $error = "Password must be at least 6 characters!";
                } elseif ($password !== $confirm_password) {
                    $error = "Passwords do not match!";
                } else {
                    $is_phone = preg_match('/^01[3-9]\d{8}$/', $contact);
                    $is_email = filter_var($contact, FILTER_VALIDATE_EMAIL);

                    if (!$is_phone && !$is_email) {
                        $error = "Invalid phone number or email address!";
                    } else {
                        $field = $is_phone ? 'phone' : 'email';

                        $checkStmt = $conn->prepare("SELECT id FROM users WHERE phone = ? OR email = ?");
                        $checkStmt->bind_param("ss", $contact, $contact);
                        $checkStmt->execute();
                        $checkStmt->store_result();

                        if ($checkStmt->num_rows > 0) {
                            $error = "Contact already registered!";
                        } else {
                            $otp = random_int(100000, 999999); // ✅ আসল OTP
                            $hashed_otp = hash('sha256', $otp); // ✅ হ্যাশ করলাম DB-র জন্য
                            $expires_at = date('Y-m-d H:i:s', time() + 60);

                            $deleteStmt = $conn->prepare("DELETE FROM otps WHERE $field = ?");
                            $deleteStmt->bind_param("s", $contact);
                            $deleteStmt->execute();
                            $deleteStmt->close();

                            $insertStmt = $conn->prepare("INSERT INTO otps ($field, otp_code, expires_at) VALUES (?, ?, ?)");
                            $insertStmt->bind_param("sss", $contact, $hashed_otp, $expires_at);


                            if ($insertStmt->execute()) {
                        // 🧼 Clean previous forgot password session
                                unset($_SESSION['reset_contact']);
                                unset($_SESSION['otp_flow']); // optional, if you're using it

                        // ✅ Now set reg_data session for registration
                               $_SESSION['reg_data'] = [
                                'full_name' => $full_name,
                                'contact' => $contact,
                                'contact_type' => $field,
                                'password' => password_hash($password, PASSWORD_DEFAULT)
                            ];


                                if ($is_email) {
                                    require 'send_email_otp.php';
                                    sendEmailOtp($contact, $otp);
                                } else {
                                    require 'send_sms_otp.php';
                                    sendSmsOtp($contact, $otp);
                                }
                        // Store the contact information and OTP flow type
                           $_SESSION['contact'] = $contact;
                           $_SESSION['otp_flow'] = 'registration'; // Registration flow

                        // Redirect to OTP verification page
                        header("Location: https://flacofy.com/otp-verification/");
                        exit();

                            } else {
                                $error = "Error generating OTP!";
                            }
                            $insertStmt->close();
                        }
                        $checkStmt->close();
                    }
                }
            }
        }
        ?>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <label for="contact">Enter mobile number or email</label>
            <input type="text" id="contact" name="contact" placeholder="01XXXXXXXXX or email@example.com" required>

            <label for="full_name">Your name</label>
            <input type="text" id="full_name" name="full_name" placeholder="First and last name" required>

            <label for="password">Password (at least 6 characters)</label>
            <div class="password-toggle">
                <input type="password" id="password" name="password" minlength="6" required>
                <span class="toggle-password" onclick="togglePassword('password', this)">👁️</span>
            </div>

            <label for="confirm_password">Re-enter password</label>
            <div class="password-toggle">
                <input type="password" id="confirm_password" name="confirm_password" minlength="6" required oninput="checkPasswordMatch()">
                <span class="toggle-password" onclick="togglePassword('confirm_password', this)">👁️</span>
                <div id="password-match" class="password-match"></div>
            </div>

            <button type="submit">Continue</button>

            <div class="info-text mt-2">
                Already a customer? <a href="#">Sign in instead</a><br>
                By creating an account, you agree to FLACOFY's <a href="#">Conditions of Use</a> and <a href="#">Privacy Notice</a>.
            </div>
        </form>
    </div>
</div>

<div class="footer">
    <a href="#">Conditions of Use</a>
    <a href="#">Privacy Notice</a>
    <a href="#">Help</a><br>
    © 1996-2025, FLACOFY.com, Inc. or its affiliates
</div>

<script>
function togglePassword(fieldId, icon) {
    const input = document.getElementById(fieldId);
    if (input.type === "password") {
        input.type = "text";
        icon.textContent = "👁️‍🗨️";
    } else {
        input.type = "password";
        icon.textContent = "👁️";
    }
}

function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const matchElement = document.getElementById('password-match');

    if (confirmPassword.length === 0) {
        matchElement.textContent = '';
        matchElement.className = 'password-match';
    } else if (password === confirmPassword) {
        matchElement.textContent = '✓ Passwords match';
        matchElement.className = 'password-match match';
    } else {
        matchElement.textContent = '✗ Passwords do not match';
        matchElement.className = 'password-match mismatch';
    }
}
</script>

</body>
</html>
