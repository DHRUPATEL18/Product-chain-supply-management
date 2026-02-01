<?php
// Email Configuration
// Update these settings with your email server details

// SMTP Settings
define('SMTP_HOST', 'smtp.gmail.com'); // Your SMTP server
define('SMTP_PORT', 587); // SMTP port
define('SMTP_USERNAME', ''); // Your Gmail address
define('SMTP_PASSWORD', ''); // Your Gmail app password
define('SMTP_SECURE', 'tls'); // Security type: tls or ssl

// Email Settings
define('DEFAULT_FROM_EMAIL', ''); // Your Gmail address
define('DEFAULT_FROM_NAME', ''); // Default from name

// Optional: Enable email logging
define('ENABLE_EMAIL_LOGGING', true);
?>
