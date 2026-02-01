<?php
session_start();

// Check if user has a valid reset email
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit;
}

$email = $_SESSION['reset_email'];
$otpVerified = isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #e0e0e0;
            padding: 30px;
            border-right: 1px solid #ccc;
        }

        .sidebar h2 {
            margin-top: 0;
            font-size: 24px;
            color: #333;
        }

        .form-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .reset-form {
            background-color: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 760px;
            border: 1px solid #e9eef5;
        }

        .reset-form h3 {
            margin-bottom: 20px;
            font-size: 22px;
            color: #444;
            text-align: center;
        }

        .reset-form label {
            display: block;
            margin-bottom: 8px;
            color: #223;
            font-weight: 600;
        }

        .reset-form input[type="text"],
        .reset-form input[type="password"],
        .reset-form input[type="email"] {
            width: 90%;
            height: 40px;
            padding: 10px 12px;
            margin-bottom: 18px;
            border: 1px solid #cfd8e3;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            background: #fff;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .reset-form input:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74,144,226,.15);
        }

        .reset-form input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .reset-form input[type="submit"]:hover {
            background-color: #45a049;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #4CAF50;
            text-decoration: none;
            font-size: 14px;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .password-field { position: relative; }

        .password-field input[type="password"],
        .password-field input[type="text"] { padding-right: 44px; }

        .toggle-visibility {
            position: absolute;
            right: 25px;
            top: 40%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            cursor: pointer;
            color: #607089;
            padding: 4px;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .toggle-visibility:hover { color: #334155; }

    .captcha-container {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    .captcha-image {
        border: 1px solid #aaa;
        border-radius: 4px;
        cursor: pointer;
    }

    .captcha-input {
        flex: 1;
        padding: 10px;
        border: 1px solid #aaa;
        border-radius: 4px;
        font-size: 14px;
        text-transform: uppercase;
    }

    .captcha-refresh {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }

    .captcha-refresh:hover {
        background-color: #0056b3;
    }

        .otp-input {
            text-align: center;
            font-size: 18px;
            letter-spacing: 5px;
        }

        .section-title {
            font-weight: bold;
            margin-top: 10px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .reset-form { max-width: 640px; }
        }
        @media (max-width: 768px) {
            .container { flex-direction: column; height: auto; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #ccc; }
            .form-container { padding: 20px; }
            .reset-form { max-width: 100%; padding: 28px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="sidebar">
        <h2>Reset Password</h2>
        <p>First verify the OTP sent to your email. After verification, you'll be able to set a new password.</p>
    </div>

    <div class="form-container">
        <div class="reset-form">
            <h3>Reset Password</h3>

            <?php
            if (isset($_SESSION['message'])) {
                echo '<div class="message ' . $_SESSION['message_type'] . '">' . $_SESSION['message'] . '</div>';
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            }
            ?>

            <?php if (!$otpVerified) { ?>
                <form action="process_password_reset.php" method="post">
                    <div class="section-title">Enter OTP:</div>
                    <input type="text" name="otp" id="otp" class="otp-input" maxlength="6" placeholder="000000" required>
                    <input type="submit" value="Verify OTP" name="verify_otp">
                    <div class="back-link">
                        <a href="forgot_password.php"><i class="fas fa-arrow-left"></i> Back to Forgot Password</a>
                    </div>
                </form>
            <?php } else { ?>
                <form action="process_password_reset.php" method="post">
                    <div class="section-title">Set New Password:</div>
                    <label for="new_password">New Password:</label>
                    <div class="password-field">
                        <input type="password" name="new_password" id="new_password" required>
                        <button type="button" class="toggle-visibility" aria-label="Show password" onclick="togglePassword('new_password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <label for="confirm_password">Confirm Password:</label>
                    <div class="password-field">
                        <input type="password" name="confirm_password" id="confirm_password" required>
                        <button type="button" class="toggle-visibility" aria-label="Show password" onclick="togglePassword('confirm_password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <label for="captcha">Enter the code shown below:</label>
                    <div class="captcha-container">
                        <img src="captcha_svg.php" alt="Captcha" class="captcha-image" id="captcha-image" onclick="refreshCaptcha()">
                        <input type="text" name="captcha" id="captcha" class="captcha-input" maxlength="5" required>
                        <button type="button" class="captcha-refresh" onclick="refreshCaptcha()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>

                    <input type="submit" value="Reset Password" name="reset_password">
                    <div class="back-link">
                        <a href="forgot_password.php"><i class="fas fa-arrow-left"></i> Back to Forgot Password</a>
                    </div>
                </form>
            <?php } ?>
        </div>
    </div>
</div>

<script>
// Auto-focus on OTP input if not verified; otherwise focus on new password
<?php if (!$otpVerified) { ?>
    const otpField = document.getElementById('otp');
    if (otpField) otpField.focus();
<?php } else { ?>
    const newPwd = document.getElementById('new_password');
    if (newPwd) newPwd.focus();
<?php } ?>

function refreshCaptcha() {
    var captchaImage = document.getElementById('captcha-image');
    var captchaInput = document.getElementById('captcha');
    if (!captchaImage) return;
    captchaImage.src = 'captcha_svg.php?' + new Date().getTime();
    if (captchaInput) {
        captchaInput.value = '';
        captchaInput.focus();
    }
}

function togglePassword(inputId, btn) {
    var input = document.getElementById(inputId);
    if (!input) return;
    var icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
        btn.setAttribute('aria-label', 'Hide password');
    } else {
        input.type = 'password';
        if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
        btn.setAttribute('aria-label', 'Show password');
    }
}
</script>

</body>
</html>