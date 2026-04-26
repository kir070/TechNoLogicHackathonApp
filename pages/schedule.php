<?php
require_once '../includes/config.php';
$activePage = 'schedule';
$pageTitle = 'Schedule Builder';
$db = getDB();
$professors = $db->query("SELECT id, first_name, last_name FROM professors WHERE is_active=1 ORDER BY last_name")->fetchAll();
// Removed year_level and section from the query select
$subjects   = $db->query("SELECT id, code, name, units FROM subjects WHERE is_active=1 ORDER BY code")->fetchAll();
$rooms      = $db->query("SELECT id, name, room_type FROM rooms WHERE is_active=1 ORDER BY name")->fetchAll();
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$timeSlots = $db->query("SELECT * FROM time_slots ORDER BY start_time")->fetchAll();
$dayColors = ['Monday'=>'#4f7df9','Tuesday'=>'#22c55e','Wednesday'=>'#f59e0b','Thursday'=>'#ef4444','Friday'=>'#8b5cf6','Saturday'=>'#06b6d4'];
include '../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Schedule Builder</h1>
        <p>Create and manage class schedules with auto-matching and conflict detection</p>
    </div>
    <div style="display:flex;gap:10px;">
        <button class="btn btn-outline" onclick="openAutoMatch()">
            <i class="fas fa-magic"></i> Match-Schedule
        </button>
        <button class="btn btn-primary" onclick="openAddSchedule()">
            <i class="fas fa-plus"></i> Add Schedule
        </button>
        <button class="btn btn-success" onclick="runAutoSchedule()">
            Auto Schedule
        </button>
    </div>
</div>

<div class="card mb-2" style="padding:14px 20px;">
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
        <div style="display:flex;gap:0;border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;">
            <button class="btn btn-sm" id="view-grid-btn" onclick="setView('grid')" style="border-radius:0;background:var(--accent);color:white;border:none;">Grid View</button>
            <button class="btn btn-sm" id="view-list-btn" onclick="setView('list')" style="border-radius:0;background:var(--bg-card);color:var(--text-secondary);border:none;">List View</button>
        </div>
        <select class="form-select" id="filter-day" style="width:150px;" onchange="applyFilters()">
            <option value="">All Days</option>
            <?php foreach ($days as $d): ?>
                <option value="<?= $d ?>"><?= $d ?></option>
            <?php endforeach; ?>
        </select>
        <select class="form-select" id="filter-prof" style="width:200px;" onchange="applyFilters()">
            <option value="">All Professors</option>
            <?php foreach ($professors as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['last_name'] . ', ' . $p['first_name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select class="form-select" id="filter-room" style="width:140px;" onchange="applyFilters()">
            <option value="">All Rooms</option>
            <?php foreach ($rooms as $r): ?>
                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="card">
    <div id="schedule-display">
        <div class="empty-state"><i class="fas fa-spinner fa-spin"></i></div>
    </div>
</div>

<div class="modal-overlay" id="sched-modal">
    <div class="modal" style="width:580px;">
        <div class="modal-header">
            <div class="modal-title" id="sched-modal-title">Add Schedule</div>
            <button class="modal-close" onclick="closeModal('sched-modal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="sched-id">
            <div class="form-group">
                <label class="form-label">Subject *</label>
                <select class="form-select" id="sched-subject">
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>" data-units="<?= $s['units'] ?>" data-code="<?= htmlspecialchars($s['code']) ?>">
                            <?= htmlspecialchars($s['code']) ?> — <?= htmlspecialchars($s['name']) ?> (<?= $s['units'] ?>u)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Day *</label>
                    <select class="form-select" id="sched-day">
                        <option value="">Select Day</option>
                        <?php foreach ($days as $d): ?>
                            <option value="<?= $d ?>"><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>START TIME *</label>
                    <select name="start_time" id="sched-start" class="form-control" required> 
                        <option value="">Select Time</option>
                        <?php
                        for ($h = 7; $h <= 19; $h++) {
                            for ($m = 0; $m < 60; $m += 30) {
                                $time = sprintf('%02d:%02d', $h, $m);
                                $displayTime = date("g:i A", strtotime($time));
                                echo "<option value='$time'>$displayTime</option>";
                            }
                        }
                        ?>
                    </select>
                    <div id="end-time-preview" style="font-size:11px; color:var(--accent); margin-top:5px; font-weight:600;"></div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Professor *</label>
                <select class="form-select" id="sched-prof">
                    <option value="">Select Professor</option>
                    <?php foreach ($professors as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['last_name'] . ', ' . $p['first_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Room *</label>
                <select class="form-select" id="sched-room">
                    <option value="">Select Room</option>
                    <?php foreach ($rooms as $r): ?>
                        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?> (<?= $r['room_type'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="conflict-preview" style="display:none;" class="conflict-item">
                <div class="conflict-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div><div class="conflict-desc" id="conflict-preview-msg"></div></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('sched-modal')">Cancel</button>
            <button class="btn btn-primary" onclick="saveScheduleData()">
                <i class="fas fa-save"></i> Save Schedule
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="automatch-modal">
    <div class="modal" style="width:640px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-magic" style="color:var(--accent-purple)"></i> Auto-Match Schedule</div>
            <button class="modal-close" onclick="closeModal('automatch-modal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p style="color:var(--text-muted);font-size:13px;margin-bottom:16px;">
                Select a subject, day, and start time — the system will suggest compatible professors and rooms.
            </p>
            <div class="form-group">
                <label class="form-label">Subject</label>
                <select class="form-select" id="am-subject">
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>" data-units="<?= $s['units'] ?>">
                            <?= htmlspecialchars($s['code']) ?> (<?= $s['units'] ?>u)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Day</label>
                    <select class="form-select" id="am-day">
                        <option value="">Select Day</option>
                        <?php foreach ($days as $d): ?>
                            <option value="<?= $d ?>"><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Start Time</label>
                    <select class="form-select" id="am-start">
                        <option value="">Select Time</option>
                        <?php foreach ($timeSlots as $ts): ?>
                            <option value="<?= $ts['start_time'] ?>"><?= date("g:i A", strtotime($ts['start_time'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button class="btn btn-primary w-100" id="run-automatch-btn"><i class="fas fa-search"></i> Find Matches</button>
            <div id="am-results" style="margin-top:16px;"></div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="detail-modal">
    <div class="modal" style="width:440px;">
        <div class="modal-header">
            <div class="modal-title">Schedule Details</div>
            <button class="modal-close" onclick="closeModal('detail-modal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="detail-content"></div>
        <div class="modal-footer" id="detail-actions"></div>
    </div>
</div>

<script>
let currentView = 'grid';
let scheduleData = [];

function setView(v) {
    currentView = v;
    document.getElementById('view-grid-btn').style.background = v === 'grid' ? 'var(--accent)' : 'var(--bg-card)';
    document.getElementById('view-grid-btn').style.color = v === 'grid' ? 'white' : 'var(--text-secondary)';
    document.getElementById('view-list-btn').style.background = v === 'list' ? 'var(--accent)' : 'var(--bg-card)';
    document.getElementById('view-list-btn').style.color = v === 'list' ? 'white' : 'var(--text-secondary)';
    renderSchedule(scheduleData);
}

const dayColors = {Monday:'#4f7df9',Tuesday:'#22c55e',Wednesday:'#f59e0b',Thursday:'#ef4444',Friday:'#8b5cf6',Saturday:'#06b6d4'};
const days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
const timeLabels = <?= json_encode(array_map(fn($t) => [
    'start' => $t['start_time'],
    'label' => date("g:i A", strtotime($t['start_time']))
], $timeSlots)) ?>;

function timeToMinutes(t) {
    const [h,m] = t.split(':').map(Number); return h*60+m;
}

function renderSchedule(schedules = []) {
    scheduleData = schedules;
    const display = document.getElementById('schedule-display');
    if (!display) return;
    if (!Array.isArray(schedules)) schedules = [];
    if (schedules.length === 0) {
        display.innerHTML = '<div class="empty-state">No schedules found.</div>';
        return;
    }
    if (currentView === 'list') { renderList(display); } 
    else { renderGrid(display); }
}

function renderList(wrap) {
    wrap.innerHTML = `
        <div class="table-wrapper">
        <table>
            <thead><tr><th>Day</th><th>Time</th><th>Subject</th><th>Professor</th><th>Room</th><th>Actions</th></tr></thead>
            <tbody>
            ${scheduleData.map(s => `
                <tr>
                    <td><span class="badge" style="background:${dayColors[s.day_of_week]}22;color:${dayColors[s.day_of_week]};border:1px solid ${dayColors[s.day_of_week]}44;">${s.day_of_week}</span></td>
                    <td class="mono" style="font-size:11px;white-space:nowrap;">${formatTime(s.start_time)}–${formatTime(s.end_time)}</td>
                    <td><strong>${s.subject_code}</strong><br><span style="font-size:11px;color:var(--text-muted)">${s.subject_name}</span></td>
                    <td>${s.last_name}, ${s.first_name}</td>
                    <td><span class="badge badge-blue">${s.room_name}</span></td>
                    <td>
                        <button class="btn btn-outline btn-sm btn-icon" onclick="editSchedule(${s.id})" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger btn-sm btn-icon" onclick="handleDelete(${s.id})" title="Delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `).join('')}
            </tbody>
        </table></div>`;
}

function renderGrid(wrap) {
    const filterDay = document.getElementById('filter-day').value;
    const daysToShow = filterDay ? [filterDay] : days;
    const allTimes = timeLabels;
    const lookup = {};
    daysToShow.forEach(d => { lookup[d] = {}; });
    scheduleData.forEach(s => {
        if (!lookup[s.day_of_week]) return;
        const sMin = timeToMinutes(s.start_time);
        const eMin = timeToMinutes(s.end_time);
        allTimes.forEach(t => {
            const tMin = timeToMinutes(t.start);
            if (tMin >= sMin && tMin < eMin) {
                if (!lookup[s.day_of_week][t.start]) lookup[s.day_of_week][t.start] = [];
                lookup[s.day_of_week][t.start].push(s);
            }
        });
    });

    let html = `<div class="schedule-grid"><table class="sched-table"><thead><tr>
        <th style="min-width:90px;">Time</th>
        ${daysToShow.map(d => `<th style="color:${dayColors[d]}">${d}</th>`).join('')}
    </tr></thead><tbody>`;

    allTimes.forEach(t => {
        html += `<tr><td class="time-col">${t.label}</td>`;
        daysToShow.forEach(d => {
            const entries = lookup[d][t.start] || [];
            html += `<td class="sched-cell">`;
            entries.forEach(s => {
                const color = dayColors[s.day_of_week];
                html += `<div class="sched-entry" style="background:${color}22;border-left:3px solid ${color};color:var(--text-primary);" onclick="showDetail(${s.id})">
                    <div class="entry-subject">${s.subject_code}</div>
                    <div class="entry-room">${s.room_name}</div>
                    <div class="entry-prof">${s.last_name}</div>
                </div>`;
            });
            html += `</td>`;
        });
        html += `</tr>`;
    });
    html += `</tbody></table></div>`;
    wrap.innerHTML = html;
}

function formatTime(t) {
    if (!t) return '';
    const [h,m] = t.split(':');
    const hr = parseInt(h);
    return `${hr%12||12}:${m} ${hr>=12?'PM':'AM'}`;
}

function applyFilters() {
    const day  = document.getElementById('filter-day').value;
    const prof = document.getElementById('filter-prof').value;
    const room = document.getElementById('filter-room').value;
    let filtered = scheduleData;
    if (day) filtered = filtered.filter(s => s.day_of_week === day);
    if (prof) filtered = filtered.filter(s => s.professor_id == prof);
    if (room) filtered = filtered.filter(s => s.room_id == room);
    renderSchedule(filtered);
}

async function loadSchedulePage() {
    try {
        const res = await fetch('../api/schedules.php?action=list');
        const data = await res.json();
        scheduleData = data?.schedules ?? [];
        renderSchedule(scheduleData);    
    } catch (e) {
        console.error(e);
        renderSchedule([]);
    }
}

async function handleDelete(id) {
    if (!confirm('Are you sure you want to delete this schedule?')) return;
    try {
        const res = await fetch(`../api/schedules.php?id=${id}`, { method: 'DELETE' });
        const data = await res.json();
        if (data.success) {
            showToast('Schedule deleted successfully', 'success');
            loadSchedulePage();
        } else {
            showToast(data.error || 'Failed to delete', 'error');
        }
    } catch (e) {
        showToast('System error occurred', 'error');
    }
}

function openAddSchedule() {
    document.getElementById('sched-modal-title').textContent = 'Add Schedule';
    document.getElementById('sched-id').value = '';
    ['subject','day','start','prof','room'].forEach(f => document.getElementById('sched-'+f).value = '');
    document.getElementById('end-time-preview').textContent = '';
    document.getElementById('conflict-preview').style.display = 'none';
    openModal('sched-modal');
}

function updateEndPreview() {
    const sel  = document.getElementById('sched-subject').selectedOptions[0];
    const start= document.getElementById('sched-start').value;
    if (!sel?.value || !start) { document.getElementById('end-time-preview').textContent=''; return; }
    const units = parseInt(sel.dataset.units);
    const [h,m] = start.split(':').map(Number);
    const endMin = h*60+m+units*60;
    const eh = Math.floor(endMin/60), em = endMin%60;
    const endStr = `${eh%12||12}:${String(em).padStart(2,'0')} ${eh>=12?'PM':'AM'}`;
    document.getElementById('end-time-preview').textContent = `⏱ Duration: ${units}h — Ends at ${endStr}`;
}

document.getElementById('sched-subject').addEventListener('change', updateEndPreview);
document.getElementById('sched-start').addEventListener('change', updateEndPreview);

async function saveScheduleData() {
    const payload = {
        id: parseInt(document.getElementById('sched-id').value)||0,
        subject_id: parseInt(document.getElementById('sched-subject').value),
        day_of_week: document.getElementById('sched-day').value,
        start_time: document.getElementById('sched-start').value,
        professor_id: parseInt(document.getElementById('sched-prof').value),
        room_id: parseInt(document.getElementById('sched-room').value)
    };
    if (!payload.subject_id||!payload.day_of_week||!payload.start_time||!payload.professor_id||!payload.room_id) {
        showToast('All fields required', 'error'); return;
    }
    const res  = await fetch('../api/schedules.php', {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
        closeModal('sched-modal');
        showToast('Schedule saved!', 'success');
        loadSchedulePage();
    } else {
        const prev = document.getElementById('conflict-preview');
        document.getElementById('conflict-preview-msg').textContent = data.error;
        prev.style.display = 'flex';
        showToast(data.error, 'error');
    }
}

async function editSchedule(id) {
    let s = scheduleData.find(x => x.id === id);
    if (!s) return;
    document.getElementById('sched-modal-title').textContent = 'Edit Schedule';
    document.getElementById('sched-id').value = s.id;
    document.getElementById('sched-subject').value = s.subject_id;
    document.getElementById('sched-day').value = s.day_of_week;
    document.getElementById('sched-start').value = s.start_time;
    document.getElementById('sched-prof').value = s.professor_id;
    document.getElementById('sched-room').value = s.room_id;
    document.getElementById('conflict-preview').style.display = 'none';
    updateEndPreview();
    openModal('sched-modal');
}

function showDetail(id) {
    const s = scheduleData.find(x => x.id === id);
    if (!s) return;
    const color = dayColors[s.day_of_week];
    document.getElementById('detail-content').innerHTML = `
        <div style="border-left:4px solid ${color};padding-left:14px;margin-bottom:16px;">
            <div style="font-size:20px;font-weight:800;">${s.subject_code}</div>
            <div style="color:var(--text-muted);font-size:13px;">${s.subject_name}</div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div><div style="font-size:10px;color:var(--text-muted);font-family:var(--font-mono);text-transform:uppercase;margin-bottom:3px;">Professor</div><div style="font-weight:600;">${s.last_name}, ${s.first_name}</div></div>
            <div><div style="font-size:10px;color:var(--text-muted);font-family:var(--font-mono);text-transform:uppercase;margin-bottom:3px;">Room</div><div style="font-weight:600;">${s.room_name} <span class="badge badge-purple" style="font-size:10px;">${s.room_type}</span></div></div>
            <div><div style="font-size:10px;color:var(--text-muted);font-family:var(--font-mono);text-transform:uppercase;margin-bottom:3px;">Day</div><div style="font-weight:600;color:${color}">${s.day_of_week}</div></div>
            <div><div style="font-size:10px;color:var(--text-muted);font-family:var(--font-mono);text-transform:uppercase;margin-bottom:3px;">Time</div><div style="font-weight:600;font-family:var(--font-mono);font-size:12px;">${formatTime(s.start_time)} – ${formatTime(s.end_time)}</div></div>
            <div><div style="font-size:10px;color:var(--text-muted);font-family:var(--font-mono);text-transform:uppercase;margin-bottom:3px;">Units / Hours</div><div style="font-weight:600;">${s.units}u / ${s.units}h</div></div>
        </div>
    `;
    document.getElementById('detail-actions').innerHTML = `
        <button class="btn btn-outline" onclick="closeModal('detail-modal')">Close</button>
        <button class="btn btn-primary" onclick="closeModal('detail-modal');editSchedule(${id})"><i class="fas fa-edit"></i> Edit</button>
        <button class="btn btn-danger" onclick="closeModal('detail-modal');handleDelete(${id})"><i class="fas fa-trash"></i> Delete</button>
    `;
    openModal('detail-modal');
}

function openAutoMatch() { openModal('automatch-modal'); document.getElementById('am-results').innerHTML = ''; }

async function runAutoMatch() {
    const subId = document.getElementById('am-subject').value;
    const day   = document.getElementById('am-day').value;
    const start = document.getElementById('am-start').value;
    if (!subId||!day||!start) { showToast('Fill all fields', 'error'); return; }
    document.getElementById('am-results').innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Searching...</p></div>';
    const res  = await fetch(`../api/schedules.php?action=automatch&subject_id=${subId}&day=${day}&start_time=${start}`);
    const data = await res.json();
    if (data.error) { document.getElementById('am-results').innerHTML = `<div class="conflict-item"><div class="conflict-icon"><i class="fas fa-times"></i></div><div>${data.error}</div></div>`; return; }
    
    let html = `<div style="background:var(--bg-hover);border-radius:var(--radius-sm);padding:10px 14px;margin-bottom:14px;font-size:12px;">
        Ends ${formatTime(data.end_time)}
    </div>`;
    html += `<div class="form-row">
        <div>
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:8px;font-family:var(--font-mono);text-transform:uppercase;">Available Professors</div>
            ${data.professors.length ? data.professors.map(p => `
                <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 12px;margin-bottom:6px;cursor:pointer;"
                     onclick="applyAutoMatch(${subId},'${day}','${start}',${p.id},null)">
                    <div style="font-weight:600;font-size:13px;">${p.last_name}, ${p.first_name}</div>
                </div>
            `).join('') : '<div class="text-muted">No professors</div>'}
        </div>
        <div>
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:8px;font-family:var(--font-mono);text-transform:uppercase;">Available Rooms</div>
            ${data.rooms.length ? data.rooms.map(r => `
                <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 12px;margin-bottom:6px;cursor:pointer;"
                     onclick="applyAutoMatch(${subId},'${day}','${start}',null,${r.id})">
                    <div style="font-weight:700;font-size:14px;">${r.name}</div>
                </div>
            `).join('') : '<div class="text-muted">No rooms</div>'}
        </div>
    </div>`;
    document.getElementById('am-results').innerHTML = html;
}

let amProfId = null, amRoomId = null;
function applyAutoMatch(subId, day, start, profId, roomId) {
    if (profId) amProfId = profId;
    if (roomId) amRoomId = roomId;
    if (amProfId && amRoomId) {
        closeModal('automatch-modal');
        document.getElementById('sched-id').value = '';
        document.getElementById('sched-subject').value = subId;
        document.getElementById('sched-day').value = day;
        document.getElementById('sched-start').value = start;
        document.getElementById('sched-prof').value = amProfId;
        document.getElementById('sched-room').value = amRoomId;
        amProfId = null; amRoomId = null;
        updateEndPreview();
        openModal('sched-modal');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadSchedulePage();
    const btn = document.getElementById('run-automatch-btn');
    if (btn) btn.addEventListener('click', runAutoMatch);
});

async function runAutoSchedule() {
    if (!confirm('This will automatically generate schedules. Proceed?')) return;
    const res = await fetch('../api/schedules.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ action: 'auto_schedule' })
    });
    const data = await res.json();
    if (data.success) {
        showToast("Auto schedule created!", "success");
        loadSchedulePage();
    } else {
        showToast(data.error || "Failed", "error");
    }
}
</script>

<?php include '../includes/footer.php'; ?>