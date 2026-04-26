<?php
require_once '../includes/config.php';
$activePage = 'conflicts';
$pageTitle  = 'Conflict Detection';
include '../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Conflict Detection</h1>
        <p>Real-time detection of scheduling conflicts and violations</p>
    </div>
    <button class="btn btn-outline" onclick="loadConflicts()">
        <i class="fas fa-sync-alt"></i> Refresh
    </button>
</div>

<div id="conflict-container">
    <div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Scanning for conflicts...</p></div>
</div>

<script>
async function loadConflicts() {
    const res  = await fetch('../api/conflicts.php');
    const data = await res.json();
    const wrap = document.getElementById('conflict-container');

    if (!data.conflicts || data.conflicts.length === 0) {
        wrap.innerHTML = `
            <div class="card">
                <div style="text-align:center;padding:48px 24px;">
                    <div style="font-size:56px;margin-bottom:16px;">✅</div>
                    <div style="font-size:20px;font-weight:800;color:var(--accent-2);margin-bottom:8px;">No Conflicts Detected</div>
                    <div style="color:var(--text-muted);font-size:13px;">All rooms and professors are properly scheduled.</div>
                </div>
            </div>`;
        document.getElementById('conflict-count').style.display = 'none';
        return;
    }

    const roomConflicts    = data.conflicts.filter(c => c.type === 'room_double_book');
    const teacherConflicts = data.conflicts.filter(c => c.type === 'teacher_double_book');

    let html = `
        <div class="stats-grid mb-2">
            <div class="stat-card red">
                <div class="stat-icon red"><i class="fas fa-door-open"></i></div>
                <div class="stat-value">${roomConflicts.length}</div>
                <div class="stat-label">Room Double-Bookings</div>
            </div>
            <div class="stat-card amber">
                <div class="stat-icon amber"><i class="fas fa-chalkboard-teacher"></i></div>
                <div class="stat-value">${teacherConflicts.length}</div>
                <div class="stat-label">Teacher Double-Bookings</div>
            </div>
            <div class="stat-card red">
                <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-value">${data.count}</div>
                <div class="stat-label">Total Conflicts</div>
            </div>
        </div>`;

    if (roomConflicts.length) {
        html += `<div class="card mb-2">
            <div class="card-header">
                <div class="card-title" style="color:var(--accent-danger);"><i class="fas fa-door-open"></i> Room Double-Bookings</div>
            </div>
            ${roomConflicts.map(c => conflictItem(c, '🚪')).join('')}
        </div>`;
    }

    if (teacherConflicts.length) {
        html += `<div class="card mb-2">
            <div class="card-header">
                <div class="card-title" style="color:var(--accent-3);"><i class="fas fa-chalkboard-teacher"></i> Teacher Double-Bookings</div>
            </div>
            ${teacherConflicts.map(c => conflictItem(c, '👨‍🏫')).join('')}
        </div>`;
    }

    html += `<div style="text-align:center;padding:16px;font-size:12px;color:var(--text-muted);">
        <i class="fas fa-info-circle"></i> Resolve conflicts by editing or deleting the conflicting schedules in the 
        <a href="schedule.php" style="color:var(--accent);">Schedule Builder</a>.
    </div>`;

    wrap.innerHTML = html;
}

function conflictItem(c, icon) {
    return `
        <div class="conflict-item">
            <div style="font-size:22px;">${icon}</div>
            <div style="flex:1;">
                <div class="conflict-desc">${c.desc}</div>
                <div class="conflict-meta">
                    <span style="background:var(--bg-hover);padding:2px 8px;border-radius:4px;margin-right:6px;">${c.day}</span>
                    <span style="font-family:var(--font-mono);">${c.time ? formatTime(c.time.split('–')[0]) + ' – ' + formatTime(c.time.split('–')[1]) : ''}</span>
                </div>
            </div>
            <div style="display:flex;gap:6px;">
                <a href="schedule.php" class="btn btn-outline btn-sm"><i class="fas fa-edit"></i> Fix</a>
            </div>
        </div>`;
}

function formatTime(t) {
    if (!t) return '';
    const parts = t.split(':');
    if (parts.length < 2) return t;
    const hr = parseInt(parts[0]);
    return `${hr%12||12}:${parts[1]} ${hr>=12?'PM':'AM'}`;
}

loadConflicts();
setInterval(loadConflicts, 60000);
</script>

<?php include '../includes/footer.php'; ?>
