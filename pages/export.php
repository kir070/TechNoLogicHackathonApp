<?php
require_once '../includes/config.php';
$activePage = 'export';
$pageTitle  = 'Export';
$db = getDB();

$professors = $db->query("SELECT id, first_name, last_name FROM professors WHERE is_active=1 ORDER BY last_name")->fetchAll();
$rooms      = $db->query("SELECT id, name FROM rooms WHERE is_active=1 ORDER BY name")->fetchAll();
include '../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Export</h1>
        <p>Download schedules and reports as CSV or printable PDF</p>
    </div>
</div>

<div class="grid-2">

    <!-- Full Schedule -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-calendar-alt"></i> Full Schedule</div>
        </div>
        <p style="color:var(--text-muted);font-size:13px;margin-bottom:18px;">Export the complete schedule for all professors and rooms.</p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="../api/export.php?type=schedule&format=csv" class="btn btn-success">
                <i class="fas fa-file-csv"></i> Download CSV
            </a>
            <a href="../api/export.php?type=schedule&format=pdf" target="_blank" class="btn btn-outline">
                <i class="fas fa-file-pdf"></i> View / Print PDF
            </a>
        </div>
    </div>

    <!-- Per Professor -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chalkboard-teacher"></i> Schedule per Professor</div>
        </div>
        <div class="form-group">
            <label class="form-label">Select Professor</label>
            <select class="form-select" id="export-prof">
                <option value="">All Professors</option>
                <?php foreach ($professors as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['last_name'].', '.$p['first_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:10px;">
            <button class="btn btn-success" onclick="exportProf('csv')">
                <i class="fas fa-file-csv"></i> Download CSV
            </button>
            <button class="btn btn-outline" onclick="exportProf('pdf')">
                <i class="fas fa-file-pdf"></i> View / Print PDF
            </button>
        </div>
    </div>

    <!-- Per Room -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-door-open"></i> Schedule per Room</div>
        </div>
        <div class="form-group">
            <label class="form-label">Select Room</label>
            <select class="form-select" id="export-room">
                <option value="">All Rooms</option>
                <?php foreach ($rooms as $r): ?>
                    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:10px;">
            <button class="btn btn-success" onclick="exportRoom('csv')">
                <i class="fas fa-file-csv"></i> Download CSV
            </button>
            <button class="btn btn-outline" onclick="exportRoom('pdf')">
                <i class="fas fa-file-pdf"></i> View / Print PDF
            </button>
        </div>
    </div>

    <!-- Teacher Load Report -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-bar"></i> Teacher Load Report</div>
        </div>
        <p style="color:var(--text-muted);font-size:13px;margin-bottom:18px;">Export total units, subjects, and load status for all professors.</p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="../api/teacher_load.php?format=csv" class="btn btn-success">
                <i class="fas fa-file-csv"></i> Download CSV
            </a>
        </div>
    </div>

</div>

<!-- Quick Stats -->
<div class="card mt-2">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-info-circle"></i> Export Summary</div>
    </div>
    <div id="export-summary">
        <div class="empty-state"><i class="fas fa-spinner fa-spin"></i></div>
    </div>
</div>

<script>
function exportProf(format) {
    const id = document.getElementById('export-prof').value;
    const url = `../api/export.php?type=schedule&format=${format}${id ? '&id='+id : ''}`;
    if (format === 'pdf') window.open(url, '_blank');
    else window.location.href = url;
}

function exportRoom(format) {
    const id = document.getElementById('export-room').value;
    const url = `../api/export.php?type=room&format=${format}${id ? '&id='+id : ''}`;
    if (format === 'pdf') window.open(url, '_blank');
    else window.location.href = url;
}

async function loadSummary() {
    const [schedRes, profRes, roomRes, conflictRes] = await Promise.all([
        fetch('../api/schedules.php'),
        fetch('../api/professors.php'),
        fetch('../api/rooms.php?action=list'),
        fetch('../api/conflicts.php?count=1')
    ]);
    const [sched, profs, rooms, conf] = await Promise.all([schedRes.json(), profRes.json(), roomRes.json(), conflictRes.json()]);

    document.getElementById('export-summary').innerHTML = `
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;">
            <div style="text-align:center;">
                <div style="font-size:28px;font-weight:800;font-family:var(--font-mono);color:var(--accent)">${(sched.schedules||[]).length}</div>
                <div style="font-size:12px;color:var(--text-muted);">Total Scheduled Classes</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:28px;font-weight:800;font-family:var(--font-mono);color:var(--accent-2)">${(profs.professors||[]).length}</div>
                <div style="font-size:12px;color:var(--text-muted);">Active Professors</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:28px;font-weight:800;font-family:var(--font-mono);color:var(--accent-3)">${(rooms.rooms||[]).length}</div>
                <div style="font-size:12px;color:var(--text-muted);">Rooms</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:28px;font-weight:800;font-family:var(--font-mono);color:${conf.count > 0 ? 'var(--accent-danger)' : 'var(--accent-2)'}">${conf.count}</div>
                <div style="font-size:12px;color:var(--text-muted);">Active Conflicts</div>
            </div>
        </div>
    `;
}

loadSummary();
</script>

<?php include '../includes/footer.php'; ?>
