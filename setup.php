<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduling — Setup</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d1117; --surface: #161b22; --card: #1c2330;
            --border: #2d3748; --accent: #3b82f6; --accent-2: #10b981;
            --danger: #ef4444; --text: #e2e8f0; --muted: #64748b;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Sora', sans-serif; background: var(--bg); color: var(--text); display: grid; place-items: center; min-height: 100vh; padding: 24px; }
        .setup-card { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 36px; width: 540px; max-width: 100%; }
        .logo { display: flex; align-items: center; gap: 12px; margin-bottom: 28px; }
        .logo-icon { width: 44px; height: 44px; background: var(--accent); border-radius: 10px; display: grid; place-items: center; font-size: 20px; }
        h1 { font-size: 22px; font-weight: 800; }
        p  { color: var(--muted); font-size: 13px; margin-top: 4px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 11px; font-weight: 600; color: #94a3b8; margin-bottom: 6px; text-transform: uppercase; font-family: 'IBM Plex Mono', monospace; letter-spacing: .06em; }
        input { width: 100%; padding: 9px 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 13.5px; font-family: 'Sora', sans-serif; outline: none; }
        input:focus { border-color: var(--accent); }
        input::placeholder { color: var(--muted); }
        .btn { width: 100%; padding: 11px; background: var(--accent); color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; font-family: 'Sora', sans-serif; }
        .btn:hover { background: #2563eb; }
        .result { margin-top: 20px; padding: 14px; border-radius: 8px; font-size: 13px; line-height: 1.7; }
        .result.success { background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.3); color: #6ee7b7; }
        .result.error   { background: rgba(239,68,68,.1);  border: 1px solid rgba(239,68,68,.3);  color: #fca5a5; }
        .step { display: flex; align-items: center; gap: 8px; margin: 3px 0; font-size: 12px; }
        .divider { border: none; border-top: 1px solid var(--border); margin: 20px 0; }
        .alt-note { font-size: 12px; color: var(--muted); text-align: center; margin-top: 14px; }
        a { color: var(--accent); }
    </style>
</head>
<body>
<div class="setup-card">
    <div class="logo">
        <div class="logo-icon">📅</div>
        <div>
            <div style="font-size:18px;font-weight:800;">Scheduling</div>
            <div style="font-size:12px;color:var(--muted);font-family:'IBM Plex Mono',monospace;">Database Setup</div>
        </div>
    </div>

    <div class="form-group">
        <label>DB Host</label>
        <input type="text" id="db-host" value="localhost">
    </div>
    <div class="form-group">
        <label>DB Username</label>
        <input type="text" id="db-user" value="root">
    </div>
    <div class="form-group">
        <label>DB Password</label>
        <input type="password" id="db-pass" placeholder="Leave blank if no password">
    </div>
    <div class="form-group">
        <label>Database Name</label>
        <input type="text" id="db-name" value="scheduling_db">
    </div>

    <button class="btn" onclick="runSetup()">🚀 Initialize Database</button>

    <div id="result" style="display:none;"></div>

    <hr class="divider">
    <div class="alt-note">
        Alternatively, import <code>database.sql</code> manually via phpMyAdmin<br>
        then go to <a href="index.php">index.php</a>
    </div>
</div>

<script>
async function runSetup() {
    const btn = document.querySelector('.btn');
    btn.textContent = '⏳ Setting up...';
    btn.disabled = true;

    const payload = {
        host: document.getElementById('db-host').value,
        user: document.getElementById('db-user').value,
        pass: document.getElementById('db-pass').value,
        name: document.getElementById('db-name').value
    };

    const res = await fetch('setup_run.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    const data = await res.json();
    const div = document.getElementById('result');
    div.style.display = 'block';
    if (data.success) {
        div.className = 'result success';
        div.innerHTML = '<strong>✅ Setup Complete!</strong><br>' +
            data.steps.map(s => `<div class="step">✓ ${s}</div>`).join('') +
            '<br><a href="index.php" style="color:#6ee7b7;font-weight:700;">→ Go to Dashboard</a>';
    } else {
        div.className = 'result error';
        div.innerHTML = '<strong>❌ Setup Failed</strong><br>' + (data.error || 'Unknown error');
        btn.textContent = '🚀 Initialize Database';
        btn.disabled = false;
    }
}
</script>
</body>
</html>
