<?php
require_once '../includes/config.php';
$activePage = 'teacher_load';
$pageTitle = 'Teacher Load Report';
$db = getDB();
include '../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Teacher Load Report</h1>
        <p>Unit loads, subject assignments, and overload alerts</p>
    </div>
    <div style="display:flex;gap:10px;">
        <button class="btn btn-outline" onclick="exportCSV()"><i class="fas fa-file-csv"></i> Export CSV</button>
    </div>
</div>

<div id="load-report-container">
    <div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Generating report...</p></div>
</div>

<script>
async function loadReport() {
    const res  = await fetch('../api/teacher_load.php');
    const data = await res.json();
    const wrap = document.getElementById('load-report-container');

    if (!data.professors || !data.professors.length) {
        wrap.innerHTML = '<div class="empty-state"><i class="fas fa-chalkboard-teacher"></i><p>No professors found</p></div>';
        return;
    }

    const total = data.professors.length;
    const overloaded = data.professors.filter(p => p.total_units > p.max_units).length;
    const normal = total - overloaded;

    wrap.innerHTML = `
        <div class="stats-grid mb-2">
            <div class="stat-card blue">
                <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                <div class="stat-value">${total}</div>
                <div class="stat-label">Total Faculty</div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon green"><i class="fas fa-check"></i></div>
                <div class="stat-value">${normal}</div>
                <div class="stat-label">Normal Load</div>
            </div>
            <div class="stat-card red">
                <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-value">${overloaded}</div>
                <div class="stat-label">Overloaded</div>
            </div>
        </div>

        ${data.professors.map(p => {
            const pct = Math.min(100, Math.round(p.total_units / p.max_units * 100));
            const barClass = pct >= 100 ? 'over' : pct >= 80 ? 'warn' : 'safe';
            const statusBadge = pct >= 100 ? 'badge-red' : pct >= 80 ? 'badge-amber' : 'badge-green';
            const statusLabel = pct >= 100 ? 'Overloaded' : pct >= 80 ? 'Near Limit' : 'Normal';

            return `
            <div class="card mb-2">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                    <div>
                        <div style="font-size:16px;font-weight:800;">${p.last_name}, ${p.first_name}</div>
                        <div style="font-size:12px;color:var(--text-muted);">${p.college_name || 'No College'} · ${p.employee_id || 'No ID'}</div>
                    </div>
                    <div style="text-align:right;">
                        <span class="badge ${statusBadge}">${statusLabel}</span>
                        <div style="font-family:var(--font-mono);font-size:22px;font-weight:800;margin-top:4px;">${p.total_units}<span style="font-size:14px;color:var(--text-muted);">/${p.max_units}</span></div>
                        <div style="font-size:11px;color:var(--text-muted);">units assigned</div>
                    </div>
                </div>
                <div class="load-bar-wrap">
                    <div class="load-bar ${barClass}" style="width:${pct}%;"></div>
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">${pct}% of max load</div>

                ${p.subjects && p.subjects.length ? `
                <div style="margin-top:14px;">
                    <div style="font-size:11px;font-family:var(--font-mono);color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;">Assigned Subjects</div>
                    <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Subject</th><th>Room</th><th>Day</th><th>Time</th><th>Units</th></tr></thead>
                        <tbody>
                        ${p.subjects.map(s => `
                            <tr>
                                <td><strong>${s.subject_code}</strong><br><span style="font-size:11px;color:var(--text-muted)">${s.subject_name}</span></td>
                                <td><span class="badge badge-blue">${s.room_name}</span></td>
                                <td><span class="badge" style="background:var(--bg-hover);color:var(--text-secondary)">${s.day_of_week}</span></td>
                                <td class="mono" style="font-size:11px;">${formatTime(s.start_time)}–${formatTime(s.end_time)}</td>
                                <td class="mono">${s.units}u</td>
                            </tr>
                        `).join('')}
                        </tbody>
                    </table>
                    </div>
                </div>
                ` : '<div style="color:var(--text-muted);font-size:13px;margin-top:12px;">No subjects assigned</div>'}
            </div>`;
        }).join('')}
    `;
}

function formatTime(t) {
    if (!t) return '';
    const [h,m] = t.split(':');
    const hr = parseInt(h);
    return `${hr%12||12}:${m} ${hr>=12?'PM':'AM'}`;
}

async function exportCSV() {
    const res = await fetch('../api/teacher_load.php?format=csv');
    const text = await res.text();
    const blob = new Blob([text], { type: 'text/csv' });
    const url  = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href = url;
    a.download = 'teacher_load_report.csv'; a.click();
    URL.revokeObjectURL(url);
}

loadReport();
</script>

<?php include '../includes/footer.php'; ?>