<?php
// Prevent caching of dashboard page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data-Base Grid</title>
    <link rel="stylesheet" href="main.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Home hero slider (inside #grid above dashboard) */
        .hero-slider {
            width: 100%;
            position: relative;
            overflow: hidden;
            background: #f4f6f8;
        }
        .hero-slider .slides {
            position: relative;
            width: 100%;
            height: 330px;
        }
        .hero-slider .slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity 600ms ease;
        }
        .hero-slider .slide.active {
            opacity: 1;
        }
        .hero-slider img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .hero-slider .control {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.45);
            color: #fff;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 999px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }
        .hero-slider .control:hover { background: rgba(0,0,0,0.6); }
        .hero-slider .prev { left: 12px; }
        .hero-slider .next { right: 12px; }
        .hero-slider .dots {
            position: absolute;
            left: 50%;
            bottom: 10px;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
            z-index: 2;
        }
        .hero-slider .dot {
            width: 16px;
            height: 8px;
            border-radius: 10px; /* rounded rectangle (pill) */
            background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.9); /* light circular-esque rim */
            box-shadow: 0 0 0 1px rgba(0,0,0,0.15) inset;
            cursor: pointer;
            transition: transform 150ms ease, background 150ms ease, border-color 150ms ease;
        }
        .hero-slider .dot:hover { transform: scale(1.1); }
        .hero-slider .dot.active {
            background: #fff;
            border-color: #fff;
        }
        @media (max-width: 768px) {
            .hero-slider .slides { height: 230px; }
        }
    </style>
</head>

<body>
    <?php
    require_once 'auth_check.php';
    
    $role = $_SESSION['role'];
    $user = $_SESSION['user_name'];
    ?>

    <nav>
        <a href="tablegrid.php">Data Grid</a>
        <ul>
            <li>Data Base : pragmanx_onelife_distributor</li>
        </ul>

        <div class="right-nav">

            <!-- Notification Bell -->
            <div class="notification-dropdown-container" style="position: relative;">
                <button class="notification-btn" onclick="toggleNotifications()" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="notification-badge" style="display: none;">0</span>
                </button>
                <div class="notification-dropdown" id="notification-dropdown">
                    <div class="notification-header">
                        <h4>Notifications</h4>
                        <button class="mark-all-read" onclick="markAllAsRead()">Mark all as read</button>
                    </div>
                    <div class="notification-list" id="notification-list">
                        <div class="notification-empty">Loading notifications...</div>
                    </div>
                </div>
            </div>

            <!-- AI Analyst Button -->
            <button class="ai-btn" onclick="window.location.href='ai_analyst.php'" title="AI Analyst">
                <i class="fas fa-robot"></i> AI Analyst
            </button>

            <?php if ($role === 'Manufacture' || $role === 'Distributor') { ?>
                <button class="mail-btn" id="openMailBtn" title="Send Mail">
                    <i class="bx bx-envelope"></i> Send Mail
                </button>
            <?php } ?>
            <div class="select-menu">
                <div class="select-btn">
                    <span class="sBtn-text">Account Info</span>
                    <i class="bx bx-chevron-down"></i>
                </div>
                <ul class="options">
                    <li class="option user">
                        <i class="bx bx-user"></i>
                        <span class="option-text">User : <?= $user ?></span>
                    </li>
                    <li class="option role">
                        <i class="bx bx-briefcase-alt-2"></i>
                        <span class="option-text">Role : <?= $role ?></span>
                    </li>
                    <li class="option logout">
                        <i class="bx bx-log-out"></i>
                        <a href="logout.php" class="option-text">Log Out</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <?php
    // Fetch offer images for slider (from Uploads directory)
    $slider_imgs = [];
    $cn_slider = @mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");
    if ($cn_slider) {
        // Prefer active offers if such a status is used; fallback simply filters non-empty images
        $qr_slider = "SELECT img FROM offers WHERE img IS NOT NULL AND img != '' ORDER BY id DESC LIMIT 12";
        if ($res_slider = mysqli_query($cn_slider, $qr_slider)) {
            while ($r = mysqli_fetch_assoc($res_slider)) {
                $img = trim($r['img']);
                if ($img !== '') {
                    $slider_imgs[] = $img;
                }
            }
            mysqli_free_result($res_slider);
        }
        mysqli_close($cn_slider);
    }
    ?>

    

    <div class="con">
        <div class="sidebar collapsed">
            <ul>
                <li class="list-head" style="display:flex; align-items:center; justify-content:space-between; gap:8px; cursor: pointer;" onclick="toggleSidebar()"> =
                    <span class="sidebar-title">
                        Table List
                    </span>
                </li>
                <?php
                if ($role === "Manufacture") {
                    $tbarr = ["admin", "asm_attendance", "batch_distributor", "batch_retailer", "city", "states", "offers", "product_category", "products", "sold_products", "users", "user_relations", "product_assigned_dist", "requested_products", "product_assignments_backup", "product_assigned_retailer"];
                } elseif ($role === "Distributor") {
                    $tbarr = ["batch_retailer", "product_assigned_dist", "product_assigned_retailer", "requested_products", "products", "sold_products", "states", "city", "users", "user_relations", "offers"];
                } elseif ($role === "Retailer") {
                    $tbarr = ["product_assigned_retailer", "requested_products", "products", "user_relations", "offers"];
                } elseif ($role === "Area Sales Manager") {
                    $tbarr = ["asm_attendance", "batch_distributor", "city", "offers", "product_category", "products", "sold_products", "states", "users", "user_relations", "product_assigned_dist", "product_assigned_retailer", "product_assignments_backup"];
                }

                $cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");
                // Icon map for sidebar items (collapsed view)
                $li_icons = [
                    'admin' => '👤',
                    'asm_attendance' => '📅',
                    'batch_distributor' => '📦',
                    'batch_retailer' => '🏪',
                    'city' => '🏙',
                    'states' => '🗺',
                    'offers' => '🎯',
                    'product_category' => '📂',
                    'products' => '📱',
                    'sold_products' => '💰',
                    'users' => '👥',
                    'user_relations' => '🔗',
                    'product_assigned_dist' => '📋',
                    'requested_products' => '📝',
                    'product_assignments_backup' => '💾',
                    'product_assigned_retailer' => '🛒'
                ];

                $qr = "SHOW TABLES";
                $res = mysqli_query($cn, $qr);
                while ($row = mysqli_fetch_array($res)) {
                    $rowVal = $row[0];
                    if (in_array($rowVal, $tbarr)) {
                        $label = ucwords(str_replace('_', ' ', $rowVal));
                        $_SESSION['tn'] = $label;
                        $liicon = isset($li_icons[$rowVal]) ? $li_icons[$rowVal] : '📊';
                        echo "<span><li class='list-item' onclick=\"dbload('$rowVal')\"><span class='li-icon'>$liicon</span><span class='li-label'>$label</span></li></span>";
                    }
                }

                // Ensure Batch Distributor link is always visible for Manufacture role
                if ($role === 'Manufacture') {
                    echo "<span><li class='list-item' onclick=\"dbload('batch_distributor')\"><span class='li-icon'>📦</span><span class='li-label'>Batch Distributor</span></li></span>";
                }

                // Add quick links for logical/synthetic views not backed by a physical table
                if ($role === 'Retailer') {
                    // Stock view
                    echo "<span><li class='list-item' onclick=\"dbload('stock')\"><span class='li-icon'>🧾</span><span class='li-label'>Stock</span></li></span>";
                    // Retailed products view
                    echo "<span><li class='list-item' onclick=\"dbload('retailed_product')\"><span class='li-icon'>💰</span><span class='li-label'>Retailed Product</span></li></span>";
                } else {
                    // For other roles, expose retailed products list
                    echo "<span><li class='list-item' onclick=\"dbload('retailed_product')\"><span class='li-icon'>💰</span><span class='li-label'>Retailed Product</span></li></span>";
                }
                ?>
            </ul>
        </div>

        <div class="grid main-content" id="grid">

            <?php if (!empty($slider_imgs) && in_array($role, ['Manufacture', 'Retailer', 'Area Sales Manager'])) { ?>
            <div class="hero-slider" id="heroSlider" aria-label="Offer images slider">
                <button class="control prev" type="button" aria-label="Previous slide">&#10094;</button>
                <button class="control next" type="button" aria-label="Next slide">&#10095;</button>
                <div class="slides">
                    <?php foreach ($slider_imgs as $idx => $img) { ?>
                        <div class="slide<?= $idx === 0 ? ' active' : '' ?>">
                            <img src="Uploads/<?= htmlspecialchars($img) ?>" alt="Offer Image <?= $idx + 1 ?>">
                        </div>
                    <?php } ?>
                </div>
                <div class="dots">
                    <?php foreach ($slider_imgs as $idx => $img) { ?>
                        <div class="dot<?= $idx === 0 ? ' active' : '' ?>" data-index="<?= $idx ?>" aria-label="Go to slide <?= $idx + 1 ?>"></div>
                    <?php } ?>
                </div>
            </div>
            <script>
                (function() {
                    const slider = document.getElementById('heroSlider');
                    if (!slider) return;
                    const slides = slider.querySelectorAll('.slide');
                    const prevBtn = slider.querySelector('.prev');
                    const nextBtn = slider.querySelector('.next');
                    const dots = slider.querySelectorAll('.dot');
                    if (!slides || slides.length === 0) {
                        console.warn('[Slider] No slides found.');
                        return;
                    }
                    let current = 0;
                    let timerId;

                    function setActive(index) {
                        slides[current].classList.remove('active');
                        dots[current]?.classList.remove('active');
                        current = index;
                        slides[current].classList.add('active');
                        dots[current]?.classList.add('active');
                    }

                    function next() { setActive((current + 1) % slides.length); }
                    function prev() { setActive((current - 1 + slides.length) % slides.length); }

                    function tick() {
                        // advance once, then schedule next
                        next();
                        timerId = setTimeout(tick, 5000);
                    }
                    function startAuto() {
                        stopAuto();
                        console.log('[Slider] Autoplay start. slides:', slides.length);
                        // start after a small delay to ensure first paint
                        timerId = setTimeout(tick, 5000);
                    }
                    function stopAuto() {
                        if (timerId) clearInterval(timerId);
                        timerId = null;
                    }

                    nextBtn.addEventListener('click', function() { next(); startAuto(); });
                    prevBtn.addEventListener('click', function() { prev(); startAuto(); });
                    dots.forEach(function(dot) {
                        dot.addEventListener('click', function() {
                            const i = parseInt(dot.getAttribute('data-index')) || 0;
                            setActive(i);
                            startAuto();
                        });
                    });

                    slider.addEventListener('mouseenter', stopAuto);
                    slider.addEventListener('mouseleave', startAuto);

                    if (slides.length > 1) {
                        // ensure first slide is active
                        setActive(0);
                        startAuto();
                    } else {
                        // Hide controls and dots if only one slide
                        prevBtn.style.display = 'none';
                        nextBtn.style.display = 'none';
                        const dotsWrap = slider.querySelector('.dots');
                        if (dotsWrap) dotsWrap.style.display = 'none';
                    }
                })();
            </script>
            <?php } ?>

            <br>

            <?php
            if ($role === "Manufacture") {
            ?>

                <div class="dashboard-welcome">
                    <div class="dashboard-left">
                        <h1>Database Management Dashboard</h1>
                        <p>Monitor and manage your database tables efficiently</p>
                    </div>
                    <div class="dashboard-right">
                        <button class="btn-report" onclick="generateReport()">
                            <i class="bx bx-download"></i>
                            Get Report
                        </button>
                    </div>
                </div>

            <?php
            } elseif ($role === "Distributor") {
            ?>

                <div class="dashboard-welcome">
                    <div class="dashboard-left">
                        <h1>Database Management Dashboard</h1>
                        <p>Monitor and manage your database tables efficiently</p>
                    </div>
                    <div class="dashboard-right">
                        <button class="btn-report" onclick="generateReportdis()">
                            <i class="bx bx-download"></i>
                            Get Report
                        </button>
                    </div>
                </div>

            <?php
            }
            ?>

            <div class="tcards" id="tcards">
                <?php
                $table_icons = [
                    'admin' => '👤',
                    'asm_attendance' => '📅',
                    'batch_distributor' => '📦',
                    'batch_retailer' => '🏪',
                    'city' => '🏙',
                    'states' => '🗺',
                    'offers' => '🎯',
                    'product_category' => '📂',
                    'products' => '📱',
                    'sold_products' => '💰',
                    'users' => '👥',
                    'user_relations' => '🔗',
                    'product_assigned_dist' => '📋',
                    'requested_products' => '📝',
                    'product_assignments_backup' => '💾',
                    'product_assigned_retailer' => '🛒'
                ];

                $table_trends = [
                    'admin' => 'System administrators',
                    'asm_attendance' => 'Attendance tracking',
                    'batch_distributor' => 'Distributor batches',
                    'batch_retailer' => 'Retailer batches',
                    'city' => 'Cities covered',
                    'states' => 'States in system',
                    'offers' => 'Active offers',
                    'product_category' => 'Product categories',
                    'products' => 'Available products',
                    'sold_products' => 'Sales records',
                    'users' => 'Active users',
                    'user_relations' => 'User connections',
                    'product_assigned_dist' => 'Distributor assignments',
                    'requested_products' => 'Pending requests',
                    'product_assignments_backup' => 'Backup records',
                    'product_assigned_retailer' => 'Retailer assignments'
                ];

                foreach ($tbarr as $table) {
                    $count_qr = "SELECT COUNT(*) as total FROM $table";
                    $count_res = mysqli_query($cn, $count_qr);
                    $count = 0;
                    if ($count_res && $row = mysqli_fetch_assoc($count_res)) {
                        $count = $row['total'];
                    }

                    $label = ucwords(str_replace('_', ' ', $table));
                    $icon = isset($table_icons[$table]) ? $table_icons[$table] : '📊';
                    $trend = isset($table_trends[$table]) ? $table_trends[$table] : 'Data records';
                    $trend_class = $count > 0 ? 'neutral' : 'negative';

                    echo "<div class='card' onclick=\"dbload('$table')\">";
                    echo "<div class='card-header'>";
                    echo "<h3>$label</h3>";
                    echo "<div class='card-icon'>$icon</div>";
                    echo "</div>";
                    echo "<div class='card-body'>";
                    echo "<div class='card-value'>$count</div>";
                    echo "<div class='card-label'>Total Records</div>";
                    echo "</div>";
                    echo "<div class='card-footer'>";
                    echo "<div class='card-trend $trend_class'>$trend</div>";
                    echo "</div>";
                    echo "</div>";
                }

                // Manufacture: add dashboard card shortcut if not already included
                if ($role === 'Manufacture' && !in_array('batch_distributor', $tbarr)) {
                    $count_qr = "SELECT COUNT(*) as total FROM batch_distributor";
                    $count_res = mysqli_query($cn, $count_qr);
                    $count = 0;
                    if ($count_res && $row = mysqli_fetch_assoc($count_res)) { $count = $row['total']; }
                    echo "<div class='card' onclick=\"dbload('batch_distributor')\">";
                    echo "<div class='card-header'>";
                    echo "<h3>Batch Distributor</h3>";
                    echo "<div class='card-icon'>📦</div>";
                    echo "</div>";
                    echo "<div class='card-body'>";
                    echo "<div class='card-value'>$count</div>";
                    echo "<div class='card-label'>Total Records</div>";
                    echo "</div>";
                    echo "<div class='card-footer'>";
                    echo "<div class='card-trend neutral'>Distributor batches</div>";
                    echo "</div>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Email Modal (move outside grid/main-content for global access) -->
    <div id="emailModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-envelope"></i> Send Email</h2>
                <span class="close" onclick="closeEmailModal()">&times;</span>
            </div>
            <form id="emailForm" onsubmit="sendEmail(event)">
                <div class="form-group">
                    <label for="fromEmail">From:</label>
                    <?php require_once 'email_config.php'; ?>
                    <input type="email" id="fromEmail" name="fromEmail"
                        value="<?= htmlspecialchars(DEFAULT_FROM_EMAIL) ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="toEmail">To:</label>
                    <select id="toEmail" name="toEmail" required>
                        <option value="">Select Recipient</option>
                        <?php
                        if ($_SESSION['role'] === 'Manufacture' || $_SESSION['role'] === 'Distributor') {
                            echo '<optgroup label="Manufactures">';
                            $manufactures = mysqli_query($cn, "SELECT name, email FROM users WHERE role = 'Manufacture' AND email IS NOT NULL AND email != ''");
                            while ($manu = mysqli_fetch_assoc($manufactures)) {
                                echo '<option value="' . htmlspecialchars($manu['email']) . '">' . htmlspecialchars($manu['name']) . ' (Manufacture)</option>';
                            }
                            echo '</optgroup>';
                        }
                        if ($_SESSION['role'] === 'Manufacture') {
                            echo '<optgroup label="Distributors">';
                            $distributors = mysqli_query($cn, "SELECT name, email FROM users WHERE role = 'Distributor' AND email IS NOT NULL AND email != ''");
                            while ($dist = mysqli_fetch_assoc($distributors)) {
                                echo '<option value="' . htmlspecialchars($dist['email']) . '">' . htmlspecialchars($dist['name']) . ' (Distributor)</option>';
                            }
                            echo '</optgroup>';
                        }
                        if ($_SESSION['role'] === 'Manufacture' || $_SESSION['role'] === 'Distributor') {
                            echo '<optgroup label="Retailers">';
                            $retailers = mysqli_query($cn, "SELECT name, email FROM users WHERE role = 'Retailer' AND email IS NOT NULL AND email != ''");
                            while ($ret = mysqli_fetch_assoc($retailers)) {
                                echo '<option value="' . htmlspecialchars($ret['email']) . '">' . htmlspecialchars($ret['name']) . ' (Retailer)</option>';
                            }
                            echo '</optgroup>';
                        }
                        if ($_SESSION['role'] === 'Manufacture') {
                            echo '<optgroup label="Area Sales Managers">';
                            $asms = mysqli_query($cn, "SELECT name, email FROM users WHERE role = 'Area Sales Manager' AND email IS NOT NULL AND email != ''");
                            while ($asm = mysqli_fetch_assoc($asms)) {
                                echo '<option value="' . htmlspecialchars($asm['email']) . '">' . htmlspecialchars($asm['name']) . ' (ASM)</option>';
                            }
                            echo '</optgroup>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="subject">Subject:</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="message">Message:</label>
                    <textarea id="message" name="message" required placeholder="Write your message here..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeEmailModal()">Cancel</button>
                    <button type="submit" class="btn-send">
                        <i class="fas fa-paper-plane"></i> Send Email
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Modal -->
    <div id="viewModal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center; overflow:auto;">
        <div
            style="background:#fff; padding:25px; border-radius:10px; max-width:90%; max-height:90vh; overflow:auto; position:relative;">
            <span onclick="closeModal()"
                style="position:absolute; top:10px; right:15px; font-size:22px; cursor:pointer;">&times;</span>
            <div id="modalContent" style="max-height:70vh; overflow:auto;">Loading...</div>
        </div>
    </div>

    <?php $defaultTable = $_GET['tn'] ?? ''; ?>

    <script>
        // Prevent browser back button access to login page
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function(event) {
            // If user tries to go back, redirect to dashboard instead
            window.history.pushState(null, null, window.location.href);
            // Optional: Show a message that they're already logged in
            console.log('You are already logged in. Staying on dashboard.');
        };

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Email submit via AJAX to avoid page reload and keep modal open
        function sendEmail(event) {
            event.preventDefault();
            const form = document.getElementById('emailForm');
            const btn = form.querySelector('.btn-send');
            const formData = new URLSearchParams();
            formData.append('fromEmail', document.getElementById('fromEmail').value);
            formData.append('toEmail', document.getElementById('toEmail').value);
            formData.append('subject', document.getElementById('subject').value);
            formData.append('message', document.getElementById('message').value);

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

            fetch('send_email.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            })
            .then(res => res.text())
            .then(text => {
                console.log('send_email.php response:', text);
                // Do not render raw server text inside modal to avoid Xdebug/HTML clutter
                const ok = (text || '').toLowerCase().includes('email sent successfully');
                if (ok) {
                    alert('Email sent successfully.');
                    closeEmailModal();
                } else {
                    alert('Email not sent. Check console for details.');
                }
            })
            .catch(err => {
                console.error('Email send failed:', err);
                alert('Email send failed. Check console for details.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Email';
            });
        }

        function dbload(tn) {
            fetch("process.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "tn=" + encodeURIComponent(tn)
                })
                .then(res => res.text())
                .then(data => {
                    document.getElementById("grid").innerHTML = data;
                });
        }
        const defaultTable = "<?= $defaultTable ?>";
        if (defaultTable) {
            dbload(defaultTable);
        }

        // Open modal when "Send Mail" button is clicked
        document.getElementById("openMailBtn")?.addEventListener("click", function() {
            document.getElementById("emailModal").style.display = "flex"; // or "block"
        });

        // Close modal function
        function closeEmailModal() {
            document.getElementById("emailModal").style.display = "none";
        }

        // Optional: close modal if user clicks outside modal content
        window.addEventListener("click", function(event) {
            const modal = document.getElementById("emailModal");
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            const openBtn = document.getElementById("openMailBtn");
            const modal = document.getElementById("emailModal");
            const closeBtn = modal.querySelector(".close");
            // Sidebar toggle controls (desktop)
            const sidebar = document.querySelector('.sidebar');
            function updateSidebarToggleLabel() {
                if (!sidebar) return;
                const isCollapsed = sidebar.classList.contains('collapsed');
                const chevronIcon = sidebar.querySelector('.fa-chevron-left');
                if (chevronIcon) {
                    // Collapsed: show right chevron; Expanded: show left chevron
                    chevronIcon.className = isCollapsed 
                        ? 'fa-solid fa-chevron-right' 
                        : 'fa-solid fa-chevron-left';
                }
            }
            if (sidebar) {
                updateSidebarToggleLabel();
            }
            // (Removed centered launcher; table list shows by default)

            if (openBtn) {
                openBtn.addEventListener("click", function() {
                    modal.style.display = "flex"; // Show modal
                });
            }

            if (closeBtn) {
                closeBtn.addEventListener("click", function() {
                    modal.style.display = "none"; // Hide modal
                });
            }

            // Close if user clicks outside content
            window.addEventListener("click", function(event) {
                if (event.target === modal) {
                    modal.style.display = "none";
                }
            });

            
        });

        // Notification variables
        let notificationDropdownOpen = false;
        let notificationUpdateInterval;

        // Initialize notifications
        document.addEventListener('DOMContentLoaded', function() {
            loadNotifications();
            notificationUpdateInterval = setInterval(loadNotifications, 30000);
        });

        // Toggle notification dropdown
        function toggleNotifications() {
            const dropdown = document.getElementById('notification-dropdown');
            notificationDropdownOpen = !notificationDropdownOpen;

            if (notificationDropdownOpen) {
                dropdown.classList.add('active'); // use CSS class
                loadNotifications(); // Refresh when opening
            } else {
                dropdown.classList.remove('active');
            }
        }

        // Load notifications from server
        function loadNotifications() {
            fetch('get_notifications.php?action=get')
                .then(response => response.json())
                .then(data => {
                    updateNotificationBadge(data.unread_count);
                    updateNotificationList(data.notifications);
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                });
        }

        // Update notification badge
        function updateNotificationBadge(count) {
            const badge = document.getElementById('notification-badge');
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }

        // Update notification list
        function updateNotificationList(notifications) {
            const list = document.getElementById('notification-list');

            if (!notifications || notifications.length === 0) {
                list.innerHTML = '<div class="notification-empty">No notifications</div>';
                return;
            }

            list.innerHTML = notifications.map(notification => `
        <div class="notification-item ${notification.is_read ? '' : 'unread'}" 
             onclick="markNotificationAsRead(${notification.id})">
            <div class="notification-icon" style="background-color: ${notification.color}">
                <i class="${notification.icon}"></i>
            </div>
            <div class="notification-content">
                <div class="notification-message">${notification.message}</div>
                <div class="notification-time">${notification.time}</div>
            </div>
        </div>
    `).join('');
        }

        // Mark single notification as read
        function markNotificationAsRead(notificationId) {
            fetch('get_notifications.php?action=mark_read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `notification_id=${notificationId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadNotifications(); // Refresh the list
                    }
                })
                .catch(error => {
                    console.error('Error marking notification as read:', error);
                });
        }

        // Mark all notifications as read
        function markAllAsRead() {
            fetch('get_notifications.php?action=mark_all_read')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadNotifications(); // Refresh the list
                    }
                })
                .catch(error => {
                    console.error('Error marking all notifications as read:', error);
                });
        }

        // Close notification dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const notificationContainer = document.querySelector('.notification-dropdown-container');
            const dropdown = document.getElementById('notification-dropdown');

            if (!notificationContainer.contains(event.target) && notificationDropdownOpen) {
                notificationDropdownOpen = false;
                dropdown.classList.remove('active'); // hide using class
            }
        });

        function openImportProducts() {
            const xhr = new XMLHttpRequest();
            xhr.onload = function() {
                document.getElementById("modalContent").innerHTML = xhr.responseText;
                document.getElementById("viewModal").style.display = "flex";
            };
            xhr.open("GET", "Insert/import_products.php", true);
            xhr.send();
        }

        function importProductsSubmit(event, formEl) {
            event.preventDefault();
            const formData = new FormData(formEl);
            fetch(formEl.action, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(html => {
                    const container = document.getElementById('modalContent');
                    if (container) container.innerHTML = html;
                })
                .catch(err => {
                    const container = document.getElementById('modalContent');
                    if (container) container.innerHTML = '<div style="color:#f44336;">Upload failed. Please try again.</div>';
                    console.error(err);
                });
            return false;
        }

        // Sidebar toggle function
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('collapsed');
                updateSidebarToggleLabel();
            }
        }

        function updateSidebarToggleLabel() {
            const sidebar = document.querySelector('.sidebar');
            if (!sidebar) return;
            const isCollapsed = sidebar.classList.contains('collapsed');
            const chevronIcon = document.getElementById('sidebarChevron');
            if (chevronIcon) {
                // Collapsed: hide icon; Expanded: show left chevron (◀)
                if (isCollapsed) {
                    chevronIcon.style.display = 'none';
                } else {
                    chevronIcon.style.display = 'inline';
                    chevronIcon.className = 'fa-solid fa-chevron-left';
                }
            }
        }
    </script>

    <script src="script.js"></script>
    <script src="Insert/insert_script.js"></script>
    <script>
        // Define operational fallbacks only if functions are still missing
        if (typeof window.requestProduct !== 'function') {
            window.requestProduct = function(productId, productName) {
                if (!confirm(`Request product: ${productName}?`)) { return; }
                const params = new URLSearchParams();
                params.append('product_id', productId);
                fetch('request_product.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: params.toString()
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.success) {
                        alert('Request sent successfully');
                        try { dbload('requested_products'); } catch (e) {}
                    } else {
                        alert((data && data.error) || 'Request failed');
                    }
                })
                .catch(() => alert('Network error while sending request'));
            };
        }
        if (typeof window.markAsRetailed !== 'function') {
            window.markAsRetailed = function(productId, productName, skuId, sourceType, sourceId) {
                const priceStr = prompt(`Enter selling price for: ${productName}`, '0');
                if (priceStr === null) return;
                const price = parseFloat(priceStr);
                if (isNaN(price) || price < 0) { alert('Invalid price'); return; }
                const params = new URLSearchParams({ product_id: productId, product_name: productName, sku_id: skuId || '', price: price, source_type: sourceType || '', source_id: sourceId || 0 });
                fetch('retailed_product_mark.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: params.toString()
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.success) {
                        alert('Marked as sold successfully');
                        try { dbload('stock'); } catch (e) {}
                        setTimeout(() => { try { dbload('retailed_product'); } catch (e) {} }, 300);
                    } else {
                        alert((data && data.error) || 'Operation failed');
                    }
                })
                .catch(() => alert('Network error while marking as sold'));
            };
        }
        if (typeof window.revertRetailed !== 'function') {
            window.revertRetailed = function(retailedId, productId, productName, sourceType, sourceId) {
                if (!confirm(`Revert sale for: ${productName}?`)) return;
                const params = new URLSearchParams({ id: retailedId });
                fetch('revert_retailed_product.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: params.toString()
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.success) {
                        alert('Reverted successfully');
                        try { dbload('stock'); } catch (e) {}
                        setTimeout(() => { try { dbload('retailed_product'); } catch (e) {} }, 300);
                    } else {
                        alert((data && data.error) || 'Revert failed');
                    }
                })
                .catch(() => alert('Network error while reverting'));
            };
        }

        // Distributor/Manufacturer actions fallbacks
        if (typeof window.checkAndForwardRequest !== 'function') {
            window.checkAndForwardRequest = function(requestId) {
                if (!confirm('Check distributor stock and forward to manufacturer if unavailable?')) { return; }
                const params = new URLSearchParams(); params.append('request_id', requestId);
                fetch('handle_request_availability.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: params.toString() })
                .then(res => res.json())
                .then(data => {
                    if (data && data.success) { alert(data.message || 'Processed'); try { dbload('requested_products'); } catch (e) {} }
                    else { alert((data && data.error) || 'Operation failed'); }
                })
                .catch(() => alert('Network error'));
            };
        }
        if (typeof window.sendToRetailer !== 'function') {
            window.sendToRetailer = function(requestId) {
                if (!confirm('Send this product to the retailer and approve the request?')) { return; }
                const params = new URLSearchParams(); params.append('request_id', requestId);
                fetch('send_request_retailer.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: params.toString() })
                .then(res => res.json())
                .then(data => {
                    if (data && data.success) { alert(data.message || 'Sent to retailer'); try { dbload('requested_products'); } catch (e) {} }
                    else { alert((data && data.error) || 'Operation failed'); }
                })
                .catch(() => alert('Network error'));
            };
        }
        if (typeof window.approveRequestByManufacturer !== 'function') {
            window.approveRequestByManufacturer = function(requestId) {
                if (!confirm('Approve this distributor request?')) { return; }
                const params = new URLSearchParams(); params.append('request_id', requestId);
                fetch('approve_request_manufacturer.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: params.toString() })
                .then(res => res.json())
                .then(data => {
                    if (data && data.success) { alert(data.message || 'Approved'); try { dbload('requested_products'); } catch (e) {} }
                    else { alert((data && data.error) || 'Approval failed'); }
                })
                .catch(() => alert('Network error'));
            };
        }
    </script>
</body>

</html>