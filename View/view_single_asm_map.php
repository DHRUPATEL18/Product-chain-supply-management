<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

if (!$cn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get the ASM ID from URL parameter
$asm_id = $_GET['id'] ?? '';

if (empty($asm_id)) {
    die("No ASM ID provided");
}

// Fetch the specific ASM attendance record
$query = "SELECT asm_id, location, date_time FROM asm_attendance 
          WHERE id = ? AND location != '' AND location != 'Unknown Location'";
$stmt = mysqli_prepare($cn, $query);
mysqli_stmt_bind_param($stmt, "i", $asm_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$location = mysqli_fetch_assoc($result);
mysqli_close($cn);

if (!$location) {
    die("ASM attendance record not found");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASM Location - <?php echo htmlspecialchars($location['asm_id']); ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f0f2f5;
            color: #333;
        }

        /* Back Button */
        .back-btn {
            background: linear-gradient(135deg, #1f1f1f, #444);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .back-btn:hover {
            background: linear-gradient(135deg, #444, #1f1f1f);
            transform: translateY(-2px);
        }

        /* Header Section */
        .header {
            background: white;
            padding: 20px 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .header h1 {
            margin-top: 0;
            font-size: 22px;
            font-weight: 600;
            color: #222;
        }

        /* Info Box */
        .info-box {
            background: #f9f9f9;
            padding: 15px 20px;
            border-radius: 8px;
            margin-top: 15px;
            border-left: 5px solid #2196F3;
            font-size: 15px;
            line-height: 1.6;
        }

        .info-box strong {
            color: #000;
        }

        /* Map Container */
        .map-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        #map {
            height: 500px;
            width: 100%;
        }

        /* Tooltip Styling */
        .leaflet-tooltip.custom-tooltip {
            background: #2196F3;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .header,
            .map-container {
                padding: 15px;
            }

            #map {
                height: 350px;
            }
        }
    </style>
</head>

<body>
    <a href="../tablegrid.php?tn=asm_attendance" class="back-btn">← Back to Table</a>

    <div class="header">
        <h1>ASM Location Details</h1>
        <div class="info-box">
            <strong>ASM:</strong> <?php echo htmlspecialchars($location['asm_id']); ?><br>
            <strong>Location:</strong> <?php echo htmlspecialchars($location['location']); ?><br>
            <strong>Login Time:</strong> <?php echo htmlspecialchars($location['date_time']); ?>
        </div>
    </div>

    <div class="map-container">
        <div id="map"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Get lat/lon from PHP
        const coords = <?php echo json_encode($location['location']); ?>;
        const [lat, lon] = coords.split(',').map(Number);

        const asmName = <?php echo json_encode($location['asm_id']); ?>;
        const loginTime = <?php echo json_encode($location['date_time']); ?>;

        // Initialize map centered on coordinates
        const map = L.map('map').setView([lat, lon], 15);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Create marker
        const marker = L.marker([lat, lon]).addTo(map);

        // Reverse geocode to get place name
        fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`, {
            headers: {
                'User-Agent': 'ASMMapApp/1.0'
            }
        })
            .then(res => res.json())
            .then(data => {
                const placeName = data.display_name || `${lat}, ${lon}`;

                // Popup with place name
                marker.bindPopup(`
                <div style="text-align: center; min-width: 200px;">
                    <h3 style="margin: 0 0 10px 0; color: #2196F3;">ASM ${asmName}</h3>
                    <p><strong>Location:</strong><br>${placeName}</p>
                    <p><strong>Login Time:</strong><br>${loginTime}</p>
                    <a href="https://www.google.com/maps/search/?api=1&query=${lat},${lon}"
                       target="_blank"
                       style="display: inline-block; margin-top: 10px; padding: 8px 16px;
                       background: #4CAF50; color: white; text-decoration: none; border-radius: 4px;">
                       View in Google Maps
                    </a>
                </div>
            `).openPopup();
            })
            .catch(() => {
                // Fallback popup if reverse geocoding fails
                marker.bindPopup(`
                <div style="text-align: center; min-width: 200px;">
                    <h3 style="margin: 0 0 10px 0; color: #2196F3;">ASM ${asmName}</h3>
                    <p><strong>Location:</strong><br>${lat}, ${lon}</p>
                    <p><strong>Login Time:</strong><br>${loginTime}</p>
                    <a href="https://www.google.com/maps/search/?api=1&query=${lat},${lon}"
                       target="_blank"
                       style="display: inline-block; margin-top: 10px; padding: 8px 16px;
                       background: #4CAF50; color: white; text-decoration: none; border-radius: 4px;">
                       View in Google Maps
                    </a>
                </div>
            `).openPopup();
            });

        // Tooltip
        const currentTime = new Date().toLocaleString();
        marker.bindTooltip(`
            <div style="text-align: center;">
                <strong>ASM ${asmName}</strong><br>
                <small>${currentTime}</small>
            </div>
        `, {
            permanent: false,
            direction: 'top',
            className: 'custom-tooltip'
        });
    </script>
</body>

</html>