<?php
// Prevent caching of login page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

session_start();

// Check if user is already logged in and redirect to dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && isset($_SESSION['login_time'])) {
    // Check session timeout (1 hour = 3600 seconds)
    if (time() - $_SESSION['login_time'] <= 3600) {
        // Check if IP address matches (basic security)
        if (isset($_SESSION['user_ip']) && $_SESSION['user_ip'] === $_SERVER['REMOTE_ADDR']) {
            // User is already logged in, redirect to dashboard
            header("Location: tablegrid.php");
            exit();
        }
    }
}

// Clear any existing session data
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Handle authentication errors
$error_message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'timeout':
            $error_message = 'Your session has expired. Please login again.';
            break;
        case 'security':
            $error_message = 'Security violation detected. Please login again.';
            break;
        case 'empty_fields':
            $error_message = 'Please fill in all required fields.';
            break;
        case 'invalid_password':
            $error_message = 'Invalid password. Please try again.';
            break;
        case 'user_not_found':
            $error_message = 'User not found. Please check your credentials.';
            break;
        case 'database_error':
            $error_message = 'Database error. Please try again later.';
            break;
    }
}

// Handle logout success
$success_message = '';
if (isset($_GET['success']) && $_GET['success'] === 'logout') {
    $success_message = 'You have been successfully logged out.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            min-height: 100vh;
        }

        .login-container {
            display: flex;
            height: 100vh;
            position: relative;
        }

        .sidebar {
            width: 300px;
            background: linear-gradient(135deg, #333, #1f1f1f);
            padding: 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .sidebar h2 {
            margin: 0 0 20px 0;
            font-size: 28px;
            font-weight: 600;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background-color: rgba(255, 255, 255, 0.9);
        }

        .login-form {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            transition: transform 0.3s ease;
        }

        .login-form:hover {
            transform: translateY(-5px);
        }

        .login-form h3 {
            margin-bottom: 30px;
            font-size: 24px;
            color: #333;
            text-align: center;
            font-weight: 600;
        }

        .login-form label {
            display: block;
            margin-bottom: 8px;
            color: #455A64;
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .login-form input[type="text"],
        .login-form input[type="password"],
        .login-form input[type="email"] {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 25px;
            border: 2px solid #E3F2FD;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .login-form input[type="text"]:focus,
        .login-form input[type="password"]:focus,
        .login-form input[type="email"]:focus {
            border-color: #caca;
            outline: none;
            box-shadow: 0 0 0 4px rgba(33, 150, 243, 0.1);
        }

        .login-form input[type="submit"],
        .login-form input[type="button"] {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1f1f1f, #444);
            color: white;
            font-weight: 600;
            font-size: 16px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .login-form input[type="submit"]:hover,
        .login-form input[type="button"]:hover {
            background: linear-gradient(135deg, #444, #1f1f1f);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .login-form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #aaa;
            border-radius: 4px;
            background-color: #fff;
            font-size: 14px;
            color: #333;
        }

        .login-form input[type="submit"]:active,
        .login-form input[type="button"]:active {
            transform: translateY(1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .login-form select {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 25px;
            border: 2px solid #E3F2FD;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: white;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%231976D2' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
        }

        .login-form select:focus {
            border-color: #333;
            outline: none;
            box-shadow: 0 0 0 4px rgba(33, 150, 243, 0.1);
        }

        .error-message {
            background-color: #FDE8E8;
            color: #DC2626;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #FCA5A5;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-message:before {
            content: "⚠️";
        }

        .success-message {
            background-color: #ECFDF5;
            color: #059669;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #A7F3D0;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .success-message:before {
            content: "✅";
        }

        #locationInfo {
            background: linear-gradient(135deg, #E3F2FD, #BBDEFB) !important;
            padding: 15px !important;
            border-radius: 10px !important;
            margin: 15px 0 !important;
            border: 1px solid #90CAF9;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #locationInfo p {
            margin: 0 !important;
            color: #1565C0 !important;
            font-size: 14px;
        }

        .login-form a {
            color: #2196F3 !important;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-block;
            margin-top: 20px;
        }

        .login-form a:hover {
            color: #1976D2 !important;
            transform: translateY(-1px);
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="sidebar">
            <h2>Login Portal</h2>
            <p>Access your dashboard and view database tables securely.</p>
        </div>

        <div class="form-container">
            <form class="login-form" id="loginForm" action="checkuser.php" method="post">
                <h3>User Login</h3>

                <?php if ($error_message): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>

                <label for="user">User Email or Username:</label>
                <input type="text" name="user" id="user" placeholder="Enter Your Email or Username" required>

                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>

                <label for="role">Role:</label>
                <select name="role" id="role" required onchange="handleRoleChange()">
                    <option value="">Select Role</option>
                    <option value="Manufacture">Manufacture</option>
                    <option value="Distributor">Distributor</option>
                    <option value="Retailer">Retailer</option>
                    <option value="Area Sales Manager">Area Sales Manager</option>
                </select>

                <div id="locationInfo"
                    style="display: none; background: #e8f5e8; padding: 10px; border-radius: 4px; margin: 10px 0;">
                    <p style="margin: 0; color: #2e7d32;">📍 Location tracking will be enabled for ASM login</p>
                </div>

                <input type="hidden" name="location" id="location">

                <input type="button" value="Login" id="loginBtn" onclick="handleLogin()">

                <div style="text-align: center; margin-top: 20px;">
                    <a href="forgot_password.php" style="color: #333; text-decoration: none; font-size: 14px;">Forgot
                        Password?</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Prevent browser back button access to login page
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function(event) {
            window.history.pushState(null, null, window.location.href);
        };

        // Additional security: Clear any cached login data
        if (window.performance && window.performance.navigation.type === 2) {
            // Page was loaded via back/forward button
            window.location.replace('login.php');
        }

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        function handleRoleChange() {
            const role = document.getElementById("role").value;
            const locationInfo = document.getElementById("locationInfo");
            const loginBtn = document.getElementById("loginBtn");

            if (role === "Area Sales Manager") {
                locationInfo.style.display = "block";
                loginBtn.value = "Login with Location";
                loginBtn.style.backgroundColor = "#FF9800";
            } else {
                locationInfo.style.display = "none";
                loginBtn.value = "Login";
                loginBtn.style.backgroundColor = "#4CAF50";
            }
        }

        async function handleLogin() {
            const role = document.getElementById("role").value;
            const loginBtn = document.getElementById("loginBtn");

            if (!role) {
                alert("Please select a role first.");
                return;
            }

            if (role === "Area Sales Manager" && navigator.geolocation) {
                loginBtn.value = "Getting Location...";
                loginBtn.disabled = true;

                try {
                    const pos = await new Promise((resolve, reject) => {
                        navigator.geolocation.getCurrentPosition(resolve, reject, {
                            enableHighAccuracy: true,
                            timeout: 15000,
                            maximumAge: 0
                        });
                    });

                    const lat = pos.coords.latitude.toFixed(6);
                    const lon = pos.coords.longitude.toFixed(6);

                    loginBtn.value = "Getting Address...";

                    try {
                        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json&zoom=18&addressdetails=1`, {
                            headers: { 'User-Agent': 'LoginApp/1.0' }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            const address = data.display_name || `${lat}, ${lon}`;
                            document.getElementById("location").value = address;
                        } else {
                            document.getElementById("location").value = `${lat}, ${lon}`;
                        }
                    } catch (geocodeErr) {
                        console.warn("Geocoding failed, using coordinates:", geocodeErr);
                        document.getElementById("location").value = `${lat}, ${lon}`;
                    }

                    loginBtn.value = "Logging In...";
                    document.getElementById("loginForm").submit();

                } catch (err) {
                    console.error("Location error:", err);
                    loginBtn.value = "Login with Location";
                    loginBtn.disabled = false;

                    if (err.code === 1) {
                        alert("Location access denied. Please allow location access and try again.");
                    } else if (err.code === 2) {
                        alert("Location unavailable. Please check your GPS signal.");
                    } else if (err.code === 3) {
                        alert("Location request timed out. Please try again.");
                    } else {
                        alert("Unable to get location. Please check your GPS/internet.");
                    }
                }

            } else {
                // Other roles
                loginBtn.value = "Logging In...";
                loginBtn.disabled = true;
                document.getElementById("loginForm").submit();
            }
        }
    </script>

</body>

</html>