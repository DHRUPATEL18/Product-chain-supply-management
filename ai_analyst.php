<?php
require_once 'auth_check.php';

$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_NAME = getenv('DB_NAME') ?: 'pragmanx_onelife_distributor';
$OLLAMA_URL = getenv('OLLAMA_URL') ?: 'http://127.0.0.1:11434/api/generate';
$OLLAMA_MODEL = getenv('OLLAMA_MODEL') ?: 'gemma3:1b';

function dbConnect($host, $user, $pass, $name) {
	$conn = @new mysqli($host, $user, $pass, $name);
	if ($conn && !$conn->connect_error) {
		$conn->set_charset('utf8mb4');
		return $conn;
	}
	return null;
}

function getAllowedTablesByRole($role) {
	if ($role === 'Manufacture') {
		return ["admin", "asm_attendance", "batch_distributor", "batch_retailer", "city", "states", "offers", "product_category", "products", "sold_products", "users", "user_relations", "product_assigned_dist", "requested_products", "product_assignments_backup", "product_assigned_retailer"];
	} elseif ($role === 'Distributor') {
		return ["batch_retailer", "product_assigned_dist", "product_assigned_retailer", "requested_products", "products", "sold_products", "states", "city", "users", "user_relations", "offers"];
	} elseif ($role === 'Retailer') {
		return ["product_assigned_retailer", "requested_products", "products", "user_relations", "offers"];
	} elseif ($role === 'Area Sales Manager') {
		return ["asm_attendance", "batch_distributor", "city", "offers", "product_category", "products", "sold_products", "states", "users", "user_relations", "product_assigned_dist", "product_assigned_retailer", "product_assignments_backup"];
	}
	return [];
}

function listExistingTables($conn) {
	$tables = [];
	$res = mysqli_query($conn, 'SHOW TABLES');
	if ($res) {
		while ($row = mysqli_fetch_array($res)) {
			$tables[] = $row[0];
		}
	}
	return $tables;
}

function getCountForTable($conn, $table) {
	$count = 0;
	$q = "SELECT COUNT(*) AS total FROM `" . $conn->real_escape_string($table) . "`";
	$res = mysqli_query($conn, $q);
	if ($res && $row = mysqli_fetch_assoc($res)) {
		$count = (int)$row['total'];
	}
	return $count;
}

function fetchSampleRows($conn, $table, $limit = 5) {
	$rows = [];
	$q = "SELECT * FROM `" . $conn->real_escape_string($table) . "` ORDER BY 1 DESC LIMIT " . (int)$limit;
	$res = mysqli_query($conn, $q);
	if ($res) {
		while ($r = mysqli_fetch_assoc($res)) {
			$rows[] = $r;
		}
	}
	return $rows;
}

function buildContextSummary($conn, $role) {
	$existing = listExistingTables($conn);
	$allowed = getAllowedTablesByRole($role);
	$visible = array_values(array_intersect($existing, $allowed));
	if (empty($visible)) {
		return "No accessible tables for role: " . $role . ".";
	}

	$parts = [];
	foreach ($visible as $table) {
		$count = getCountForTable($conn, $table);
		$samples = fetchSampleRows($conn, $table, 3);
		$sampleText = '';
		foreach ($samples as $row) {
			$kv = [];
			$colIdx = 0;
			foreach ($row as $k => $v) {
				$kv[] = $k . '=' . (is_null($v) ? 'NULL' : (is_scalar($v) ? substr((string)$v, 0, 60) : '[obj]'));
				$colIdx++;
				if ($colIdx >= 4) break;
			}
			$sampleText .= '[' . implode(', ', $kv) . "]\n";
		}
		$parts[] = "Table: " . $table . " (count: " . $count . ")\n" . ($sampleText ?: "");
	}
	return implode("\n", $parts);
}

function askLLMWithContext($ollamaUrl, $model, $userQuestion, $contextText, $sessionUser, $sessionRole) {
	$promptText = "You are an AI analyst for a distribution management system.\n"
		. "Respond in a strictly formatted way that is easy to skim.\n"
		. "Output MUST include BOTH:\n"
		. "1) A short, conversational summary (2-4 lines) that directly answers the question.\n"
		. "2) A fenced JSON block (```json ... ```), adhering to this schema exactly: {\n"
		. "  \"title\": string,\n"
		. "  \"summary\": string,\n"
		. "  \"table\": { \"columns\": [string], \"rows\": [ [values...] ] } | null,\n"
		. "  \"chart\": { \"type\": one of ['bar','line','pie'], \"labels\": [string], \"datasets\": [ { \"label\": string, \"data\": [number] } ] } | null\n"
		. "}\n"
		. "Rules:\n"
		. "- Prefer a compact table and a simple chart when helpful.\n"
		. "- If data is incomplete or unavailable, explicitly state this in the summary and set table to a single row describing required/missing fields. Do NOT fabricate numbers.\n"
		. "- Keep wording concise and avoid markdown outside the JSON fence except the short summary.\n\n"
		. "When the user asks for product recommendations, ALWAYS include a table named 'Recommended Products' with these columns exactly: ['Product', 'Category', 'Rationale', 'Target Segment', 'Confidence (0-100)']. Populate 5-10 rows using observed trends from the provided context; if you cannot ground a row in data, omit it. If insufficient data exists, include one final row with Product='[Need More Data]' and use the Rationale to list specific missing fields.\n\n"
		. "User: " . $sessionUser . " (Role: " . $sessionRole . ")\n"
		. "Question:\n" . $userQuestion . "\n\n"
		. "Database context (tables visible to this role):\n" . $contextText;

	$payload = [
		'model' => $model,
		'prompt' => $promptText,
		'stream' => false,
		'temperature' => 0.2,
		'max_tokens' => 900,
	];

	$attempts = 0;
	$maxAttempts = 2; // one retry on timeout
	$lastError = '';
	while ($attempts < $maxAttempts) {
		$attempts++;
		$ch = curl_init($ollamaUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
		// Increased timeouts for first-run model pulls
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);
		$response = curl_exec($ch);
		if ($response === false) {
			$errno = curl_errno($ch);
			$err = curl_error($ch);
			$lastError = $err;
			curl_close($ch);
			// Retry only on timeout (CURLE_OPERATION_TIMEDOUT = 28)
			if ($errno === 28 && $attempts < $maxAttempts) { sleep(1); continue; }
			return 'Error calling Ollama: ' . $err . ' (URL: ' . $ollamaUrl . ')';
		}
		curl_close($ch);
		$decoded = json_decode($response, true);
		$text = is_array($decoded) ? ($decoded['response'] ?? '') : '';
		if (!$text) { $text = $response; }
		return $text;
	}
	return 'Error calling Ollama (timeout). Last error: ' . $lastError . ' (URL: ' . $ollamaUrl . ')';
}

function createWordFile($content, $filename = 'AI_Analyst_Response.docx') {
	$wordContent = '<html><body><h2>AI Analyst Response</h2><p>'
		. nl2br(htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'))
		. '</p><hr><div><em>Note: The response may include an optional JSON block for table/chart rendering.</em></div></body></html>';
	header('Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	echo $wordContent;
}

$answer = '';
$question = '';
$action = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['action'] ?? 'generate') : 'generate';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
	$question = trim($_POST['prompt'] ?? '');
	$conn = dbConnect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
	$context = $conn ? buildContextSummary($conn, $_SESSION['role'] ?? '') : 'DB connection failed';
	if ($conn) { $conn->close(); }
	$answer = askLLMWithContext($OLLAMA_URL, $OLLAMA_MODEL, $question, $context, $_SESSION['user_name'] ?? 'Unknown', $_SESSION['role'] ?? 'Unknown');
	if ($action === 'download') { createWordFile($answer); exit; }
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>AI Analyst (Ollama: gemma3:1b)</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>
		:root { --bg:#0f172a; --panel:#111827; --muted:#6b7280; --text:#e5e7eb; --brand:#22d3ee; --accent:#10b981; --border:#1f2937; }
		* { box-sizing: border-box; }
		body { font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 0; background: var(--bg); color: var(--text); }
		a { color: inherit; text-decoration: none; }
		.nav { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-bottom:1px solid var(--border); background: linear-gradient(180deg, #0b1223 0%, #0f172a 100%); position: sticky; top:0; z-index: 10; }
		.nav .left { display:flex; align-items:center; gap:12px; }
		.brand { display:flex; align-items:center; gap:10px; font-weight:600; }
		.brand .dot { width:10px; height:10px; border-radius:50%; background: var(--brand); box-shadow:0 0 12px var(--brand); }
		.nav .links a { margin-right: 12px; padding:6px 10px; border:1px solid var(--border); border-radius:8px; color:#cbd5e1; }
		.nav .links a:hover { border-color:#334155; background:#0b1223; }
		.nav .right { display:flex; align-items:center; gap:12px; color:#94a3b8; }
		.badge { padding:4px 8px; border:1px solid var(--border); border-radius:999px; font-size:12px; color:#a3e635; }
		.container { max-width: 1200px; margin: 20px auto; padding: 0 16px; }
		.grid { display:grid; grid-template-columns: 300px 1fr; gap:16px; }
		@media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }
		.panel { background: var(--panel); border: 1px solid var(--border); border-radius: 12px; padding: 14px; }
		.panel h3 { margin: 0 0 10px 0; font-size: 16px; color:#e2e8f0; }
		.hint { color: var(--muted); font-size: 12px; }
		textarea { width: 100%; background:#0b1223; color:#e5e7eb; border:1px solid #1f2937; border-radius:10px; padding:10px; outline:none; }
		textarea:focus { border-color:#334155; box-shadow:0 0 0 3px rgba(51,65,85,.3); }
		.actions { margin-top: 8px; display:flex; gap:8px; }
		.actions button { background:#0b1223; color:#e5e7eb; border:1px solid #1f2937; padding:8px 12px; border-radius:10px; cursor:pointer; }
		.actions button:hover { border-color:#334155; }
		.qp-list { display:flex; flex-direction:column; gap:8px; }
		.qp-item { background:#0b1223; border:1px solid #1f2937; border-radius:10px; padding:8px 10px; cursor:pointer; color:#cbd5e1; }
		.qp-item:hover { border-color:#334155; }
		.summary { margin-top:8px; color:#cbd5e1; font-size:14px; white-space:pre-wrap; }
		#tableOut { margin-top: 8px; overflow-x: auto; }
		table.ai { border-collapse: collapse; width: 100%; }
		table.ai th, table.ai td { border: 1px solid #1f2937; padding: 6px 8px; font-size: 14px; }
		table.ai th { background: #0b1223; text-align: left; color:#cbd5e1; }
		#chartWrap { margin-top: 12px; }
		.footer-note { margin-top:8px; color:#94a3b8; font-size:12px; }
	</style>
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
	<div class="nav">
		<div class="left">
			<div class="brand"><span class="dot"></span> <span>AI Analyst</span></div>
			<div class="links">
				<a href="tablegrid.php">← Back to Dashboard</a>
			</div>
		</div>
		<div class="right">
			<span class="hint">User: <?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></span>
			<span class="hint">Role: <?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?></span>
			<span class="badge">Model: <?php echo htmlspecialchars($OLLAMA_MODEL); ?></span>
		</div>
	</div>

	<div class="container">
		<div class="grid">
			<div class="panel">
				<h3>Quick Prompts</h3>
				<div class="qp-list">
					<div class="qp-item" data-q="Top 5 products by quantity in sold_products (overall).">Top 5 products by sales</div>
					<div class="qp-item" data-q="Count of pending requested_products by distributor (top 5).">Pending requests by distributor</div>
					<div class="qp-item" data-q="ASM present count today from asm_attendance.">ASM present today</div>
					<div class="qp-item" data-q="Total assigned vs sold quantities (product_assigned_dist vs sold_products).">Assigned vs sold</div>
					<div class="qp-item" data-q="Offers active count from offers by status.">Active offers</div>
				</div>
				<div class="footer-note">Click to fill the prompt, then Generate.</div>
			</div>

			<div class="panel">
				<h3>Ask a Question</h3>
				<form method="POST">
					<div class="row">
						<label for="prompt" class="hint">Be specific. Time range helps (e.g., this month, today).</label><br>
						<textarea id="prompt" name="prompt" rows="4" placeholder="Ask for concise findings. The answer will include a short summary, and only when useful a small table/chart."><?php echo htmlspecialchars($question, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
					</div>
					<div class="row" style="display:none;">
						<label for="answer" class="hint">Raw</label><br>
						<textarea id="answer" name="answer" rows="10" readonly><?php echo htmlspecialchars($answer, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
					</div>
					<div id="summaryOut" class="summary"></div>
					<div id="tableOut"></div>
					<div id="chartWrap"><canvas id="chartOut"></canvas></div>
					<div class="actions">
						<input type="hidden" name="action" id="action" value="generate">
						<button type="submit" name="submit" value="1" onclick="document.getElementById('action').value='generate'">Generate</button>
						<button type="submit" name="submit" value="1" onclick="document.getElementById('action').value='download'">Generate & Download Word</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<script>
		document.querySelectorAll('.qp-item').forEach(function(el){
			el.addEventListener('click', function(){
				var v = el.getAttribute('data-q') || '';
				document.getElementById('prompt').value = v;
				document.getElementById('prompt').focus();
			});
		});

		(function renderFromAnswer(){
			var raw = document.getElementById('answer').value || '';
			if (!raw) return;
			document.getElementById('summaryOut').innerHTML = '';
			document.getElementById('tableOut').innerHTML = '';
			var chartCanvas = document.getElementById('chartOut');
			if (chartCanvas && chartCanvas._chart_instance) { chartCanvas._chart_instance.destroy(); }

			var summaryText = '';
			var jsonBlock = '';
			try {
				var trimmed = raw.trim();
				if (trimmed.startsWith('```')) {
					summaryText = '';
					jsonBlock = trimmed;
				} else {
					var fenceStart = trimmed.indexOf('```');
					if (fenceStart >= 0) {
						summaryText = trimmed.substring(0, fenceStart).trim();
						jsonBlock = trimmed.substring(fenceStart).trim();
					} else {
						summaryText = trimmed;
					}
				}
			} catch(e) {
				summaryText = raw;
			}
			if (summaryText) {
				document.getElementById('summaryOut').textContent = summaryText;
			}

			if (jsonBlock) {
				try {
					var jb = jsonBlock;
					if (jb.startsWith('```')) {
						var first = jb.indexOf('{');
						var last = jb.lastIndexOf('}');
						if (first >= 0 && last > first) jb = jb.substring(first, last + 1);
					}
					var data = JSON.parse(jb);
					if (!summaryText && data.summary) {
						document.getElementById('summaryOut').textContent = data.summary;
					}
					if (data.title) {
						var h = document.createElement('h3');
						h.textContent = data.title;
						document.getElementById('tableOut').appendChild(h);
					}
					if (data.table && Array.isArray(data.table.columns) && Array.isArray(data.table.rows)) {
						var rows = data.table.rows;
						if (!Array.isArray(rows[0])) { rows = [rows]; }
						var html = '<table class="ai"><thead><tr>' + data.table.columns.map(function(c){return '<th>' + String(c) + '</th>';}).join('') + '</tr></thead><tbody>' + rows.map(function(r){return '<tr>' + r.map(function(v){return '<td>' + (v === null ? '' : String(v)) + '</td>';}).join('') + '</tr>';}).join('') + '</tbody></table>';
						document.getElementById('tableOut').insertAdjacentHTML('beforeend', html);
					}
					if (data.chart && data.chart.labels && data.chart.datasets && data.chart.type) {
						var ctx = document.getElementById('chartOut').getContext('2d');
						var chart = new Chart(ctx, {
							type: data.chart.type,
							data: { labels: data.chart.labels, datasets: (data.chart.datasets || []).map(function(ds){ return { label: ds.label || '', data: ds.data || [], backgroundColor: 'rgba(34,211,238,0.4)', borderColor: 'rgba(34,211,238,1)', borderWidth: 1 }; }) },
							options: { responsive: true, plugins: { legend: { display: true } }, scales: { y: { beginAtZero: true } } }
						});
						document.getElementById('chartOut')._chart_instance = chart;
					}
				} catch(e) {
					// ignore JSON errors
				}
			}
		})();
	</script>
</body>
</html>
