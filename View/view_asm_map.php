<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

if (!$cn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch all ASM attendance records with location data
$query = "SELECT asm_id, location, date_time FROM asm_attendance 
          WHERE location != '' AND location != 'Unknown Location'
          ORDER BY date_time DESC";
$result = mysqli_query($cn, $query);

$locations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $locations[] = $row;
}

mysqli_close($cn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASM Location Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        
        .header {
            background: #1f1f1f;
            color: white;
            padding: 15px;
            text-align: center;
        }
        
        .back-btn {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .back-btn:hover {
            background: #c0392b;
        }
        
        #map {
            height: calc(100vh - 60px);
            width: 100%;
        }
        
        .info-panel {
            position: absolute;
            top: 70px;
            right: 10px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 300px;
            z-index: 1000;
        }
        
        .info-panel h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        
        .legend {
            background: white;
            padding: 10px;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 10px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            margin: 5px 0;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255,255,255,0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 2000;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="../tablegrid.php" class="back-btn">← Back to Dashboard</a>
        <h1>ASM Location Tracking Map</h1>
    </div>
    
    <div class="info-panel">
        <h3>📊 ASM Locations</h3>
        <div class="legend">
            <div class="legend-item">
                <div class="legend-color" style="background: #e74c3c;"></div>
                <span>Today's Logins</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #f39c12;"></div>
                <span>This Week</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #3498db;"></div>
                <span>Older</span>
            </div>
        </div>
        <p><strong>Total ASM Records:</strong> <span id="totalRecords"><?php echo count($locations); ?></span></p>
        <p><strong>Mapped Locations:</strong> <span id="mappedCount">0</span></p>
        <p><strong>Last Updated:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        <p><small>Hover over markers to see ASM details</small></p>
    </div>
    
    <div id="loading" class="loading">
        <h3>🔄 Loading Locations...</h3>
        <p>Converting addresses to map coordinates...</p>
        <div id="progress">0 / <?php echo count($locations); ?> locations processed</div>
    </div>
    
    <div id="map"></div>

    <script>
        // Initialize the map
        const map = L.map('map').setView([20.5937, 78.9629], 5);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        const asmLocations = <?php echo json_encode($locations); ?>;
        let mappedCount = 0;
        const markers = [];
        
        const today = new Date().toDateString();
        const weekAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
        
        async function geocodeLocation(location) {
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(location)}&limit=1`, {
                    headers: { 'User-Agent': 'ASMMapApp/1.0' }
                });
                if (response.ok) {
                    const data = await response.json();
                    if (data.length > 0) {
                        return {
                            lat: parseFloat(data[0].lat),
                            lon: parseFloat(data[0].lon)
                        };
                    }
                }
                return null;
            } catch (error) {
                console.warn('Geocoding failed for:', location, error);
                return null;
            }
        }
        
        function createMarker(location, coords) {
            const loginDate = new Date(location.date_time);
            const loginDateString = loginDate.toDateString();
            
            let markerColor = '#3498db';
            if (loginDateString === today) {
                markerColor = '#e74c3c';
            } else if (loginDate >= weekAgo) {
                markerColor = '#f39c12';
            }
            
            const customIcon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="width: 20px; height: 20px; background-color: ${markerColor}; border: 2px solid white; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });
            
            const marker = L.marker([coords.lat, coords.lon], { icon: customIcon }).addTo(map);
            
            const popupContent = `
                <div style="min-width: 200px;">
                    <h4 style="margin: 0 0 10px 0; color: #2c3e50;">👤 ${location.asm_id}</h4>
                    <p style="margin: 5px 0;"><strong>📍 Location:</strong> ${location.location}</p>
                    <p style="margin: 5px 0;"><strong>🕒 Login Time:</strong> ${new Date(location.date_time).toLocaleString()}</p>
                    <p style="margin: 5px 0;"><strong>📅 Date:</strong> ${new Date(location.date_time).toLocaleDateString()}</p>
                    <div style="margin-top: 10px;">
                        <a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(location.location)}" target="_blank" style="background: #3498db; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px;">
                            📍 Open in Google Maps
                        </a>
                    </div>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            
            let asmName = location.asm_id;
            if (asmName.includes(' - ')) {
                asmName = asmName.split(' - ').slice(1).join(' - ').trim();
            }
            const currentTime = new Date().toLocaleString();
            marker.bindTooltip(`
                <div style="text-align: center;">
                    <strong>${asmName}</strong><br>
                    <small>${currentTime}</small>
                </div>
            `, { permanent: false, direction: 'top', className: 'custom-tooltip' });
            
            return marker;
        }
        
        async function processLocations() {
            for (let i = 0; i < asmLocations.length; i++) {
                const location = asmLocations[i];
                
                // Update progress properly
                document.getElementById('progress').textContent = `${i + 1} / ${asmLocations.length} locations processed`;

                let coords = null;

                // If already in "lat, lon" format
                const coordMatch = location.location.match(/^([-+]?[0-9]*\.?[0-9]+),\s*([-+]?[0-9]*\.?[0-9]+)$/);
                if (coordMatch) {
                    coords = { lat: parseFloat(coordMatch[1]), lon: parseFloat(coordMatch[2]) };
                } else {
                    coords = await geocodeLocation(location.location);
                }

                if (coords) {
                    const marker = createMarker(location, coords);
                    markers.push(marker);
                    mappedCount++;
                    document.getElementById('mappedCount').textContent = mappedCount;
                }

                if (i < asmLocations.length - 1) {
                    await new Promise(resolve => setTimeout(resolve, 1000));
                }
            }

            document.getElementById('loading').style.display = 'none';

            if (markers.length > 0) {
                const group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        }
        
        processLocations();
        
        const style = document.createElement('style');
        style.textContent = `
            .custom-tooltip {
                background: rgba(0,0,0,0.8);
                color: white;
                border: none;
                border-radius: 4px;
                padding: 5px 8px;
                font-size: 12px;
            }
            .custom-tooltip::before {
                border-top-color: rgba(0,0,0,0.8);
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
