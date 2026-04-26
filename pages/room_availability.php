<?php
require_once '../includes/config.php';
$activePage = 'rooms';
$pageTitle = 'Room Availability';
$db = getDB();
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
include '../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Room Availability</h1>
        <p>Real-time room status and weekly availability grid</p>
    </div>
</div>

<!-- View Controls -->
<div class="card mb-2" style="padding:14px 20px;">
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
        <div style="display:flex;gap:0;border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;">
            <button class="btn btn-sm" id="view-now-btn"  onclick="setRoomView('now')"  style="border-radius:0;background:var(--accent);color:white;border:none;">Right Now</button>
            <button class="btn btn-sm" id="view-week-btn" onclick="setRoomView('week')" style="border-radius:0;background:var(--bg-card);color:var(--text-secondary);border:none;">Weekly Grid</button>
        </div>
        <div id="day-picker" style="display:none;">
            <select class="form-select" id="grid-day" style="width:150px;" onchange="loadWeekGrid()">
                <?php foreach ($days as $d): ?>
                    <option value="<?= $d ?>" <?= $d === date('l') ? 'selected' : '' ?>><?= $d ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="now-time-display" style="font-family:var(--font-mono);font-size:12px;color:var(--text-muted);" id="clock"></div>
    </div>
</div>

<div id="room-view-container">
    <div class="empty-state"><i class="fas fa-spinner fa-spin"></i></div>
</div>

<!-- Edit Room Modal -->
<div class="modal-overlay" id="room-edit-modal">
    <div class="modal" style="width:420px;">
        <div class="modal-header">
            <div class="modal-title" id="room-edit-title">Edit Room</div>
            <button class="modal-close" onclick="closeModal('room-edit-modal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="edit-room-id">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Room Type</label>
                    <select class="form-select" id="edit-room-type">
                        <option value="lecture">Lecture</option>
                        <option value="lab">Laboratory</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Capacity</label>
                    <input type="number" class="form-control" id="edit-capacity" min="1" max="200">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Equipment</label>
                <div class="form-check"><input type="checkbox" id="edit-projector"> <label for="edit-projector">📽 Projector</label></div>
                <div class="form-check"><input type="checkbox" id="edit-computers"> <label for="edit-computers">💻 Computers / Lab</label></div>
                <div class="form-check"><input type="checkbox" id="edit-ac"> <label for="edit-ac">❄ Air Conditioning</label></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('room-edit-modal')">Cancel</button>
            <button class="btn btn-primary" onclick="saveRoomEdit()"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>
</div>

<script>
let roomView = 'now';

function setRoomView(v) {
    roomView = v;
    document.getElementById('view-now-btn').style.background = v === 'now' ? 'var(--accent)' : 'var(--bg-card)';
    document.getElementById('view-now-btn').style.color = v === 'now' ? 'white' : 'var(--text-secondary)';
    document.getElementById('view-week-btn').style.background = v === 'week' ? 'var(--accent)' : 'var(--bg-card)';
    document.getElementById('view-week-btn').style.color = v === 'week' ? 'white' : 'var(--text-secondary)';
    document.getElementById('day-picker').style.display = v === 'week' ? 'block' : 'none';
    if (v === 'now') loadNowView();
    else loadWeekGrid();
}

function updateClock() {
    const now = new Date();
    document.getElementById('now-time-display').textContent = 'Current time: ' + now.toLocaleTimeString('en-US', {hour:'2-digit',minute:'2-digit',second:'2-digit'});
}
setInterval(updateClock, 1000); updateClock();

async function loadNowView() {
    const res = await fetch('../api/rooms.php?action=status_now');
    const data = await res.json();
    const wrap = document.getElementById('room-view-container');
    if (!data.rooms.length) { wrap.innerHTML = '<div class="empty-state"><i class="fas fa-door-open"></i><p>No rooms</p></div>'; return; }
    
    const available = data.rooms.filter(r => !r.occupied).length;
    const occupied  = data.rooms.filter(r => r.occupied).length;
    
    wrap.innerHTML = `
        <div style="display:flex;gap:16px;margin-bottom:20px;">
            <div class="stat-card green" style="flex:1;padding:14px;">
                <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                <div class="stat-value">${available}</div><div class="stat-label">Available Now</div>
            </div>
            <div class="stat-card red" style="flex:1;padding:14px;">
                <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
                <div class="stat-value">${occupied}</div><div class="stat-label">Currently Occupied</div>
            </div>
        </div>
        <div class="room-grid">
            ${data.rooms.map(r => `
                <div class="room-card ${r.occupied ? 'occupied' : 'available'}" ondblclick="editRoom(${r.id},'${r.name}',${r.has_projector},${r.has_computers},${r.has_ac},${r.capacity},'${r.room_type}')">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                        <div class="room-name">${r.name}</div>
                        <span style="font-size:10px;color:var(--text-muted);font-family:var(--font-mono);">${r.capacity} cap</span>
                    </div>
                    <div class="room-type">${r.room_type === 'lab' ? '🖥 Laboratory' : '📖 Lecture'}</div>
                    <div class="room-equip">
                        ${r.has_projector ? '<span class="equip-icon" title="Projector">📽</span>' : ''}
                        ${r.has_computers ? '<span class="equip-icon" title="Computers">💻</span>' : ''}
                        ${r.has_ac ? '<span class="equip-icon" title="AC">❄</span>' : ''}
                    </div>
                    <div class="room-status ${r.occupied ? 'occupied' : 'available'}">
                        ${r.occupied ? '● Occupied' : '● Available'}
                    </div>
                    ${r.current_class ? `<div style="font-size:10px;color:var(--text-muted);margin-top:4px;line-height:1.4;">${r.current_class}</div>` : ''}
                </div>
            `).join('')}
        </div>
        <div style="text-align:center;margin-top:12px;font-size:11px;color:var(--text-muted);">Double-click a room to edit equipment. Auto-refreshes every 30s.</div>
    `;
}

async function loadWeekGrid() {
    const day = document.getElementById('grid-day').value;
    const res = await fetch(`../api/rooms.php?action=availability&day=${day}`);
    const data = await res.json();
    const wrap = document.getElementById('room-view-container');
    if (!data.grid.length) { wrap.innerHTML = '<div class="empty-state"><i class="fas fa-calendar"></i><p>No data</p></div>'; return; }

    const slots = data.grid[0].slots.map(s => s.slot);
    let html = `<div class="schedule-grid"><table class="sched-table" style="min-width:1100px;">
        <thead><tr>
            <th>Room</th>
            ${slots.map(s => `<th style="font-size:10px;">${formatTime(s.start_time)}</th>`).join('')}
        </tr></thead><tbody>`;

    data.grid.forEach(row => {
        html += `<tr><td style="font-weight:800;font-family:var(--font-mono);padding:8px 14px;">${row.room.name}
            <span style="font-size:9px;color:var(--text-muted);display:block;">${row.room.room_type}</span>
        </td>`;
        row.slots.forEach(slot => {
            if (slot.entry) {
                html += `<td class="sched-cell">
                    <div class="sched-entry" style="background:rgba(59,130,246,.15);border-left:3px solid #4f7df9;height:100%;">
                        <div class="entry-subject">${slot.entry.sub_code}</div>
                        <div class="entry-prof">${slot.entry.last_name}</div>
                    </div>
                </td>`;
            } else {
                html += `<td class="sched-cell" style="background:rgba(16,185,129,.05);"></td>`;
            }
        });
        html += `</tr>`;
    });

    html += `</tbody></table></div>
        <div style="display:flex;gap:14px;margin-top:12px;font-size:11px;color:var(--text-muted);">
            <span><span style="display:inline-block;width:12px;height:12px;border-radius:2px;background:rgba(59,130,246,.3);margin-right:4px;vertical-align:middle;"></span>Occupied</span>
            <span><span style="display:inline-block;width:12px;height:12px;border-radius:2px;background:rgba(16,185,129,.05);margin-right:4px;vertical-align:middle;border:1px solid rgba(16,185,129,.3);"></span>Available</span>
        </div>`;
    wrap.innerHTML = html;
}

function formatTime(t) {
    if (!t) return '';
    const [h,m] = t.split(':');
    const hr = parseInt(h);
    return `${hr%12||12}:${m} ${hr>=12?'PM':'AM'}`;
}

function editRoom(id, name, proj, comp, ac, cap, type) {
    document.getElementById('room-edit-title').textContent = `Edit Room: ${name}`;
    document.getElementById('edit-room-id').value = id;
    document.getElementById('edit-room-type').value = type;
    document.getElementById('edit-capacity').value = cap;
    document.getElementById('edit-projector').checked = proj == 1;
    document.getElementById('edit-computers').checked = comp == 1;
    document.getElementById('edit-ac').checked = ac == 1;
    openModal('room-edit-modal');
}

async function saveRoomEdit() {
    const payload = {
        id: document.getElementById('edit-room-id').value,
        room_type: document.getElementById('edit-room-type').value,
        capacity: parseInt(document.getElementById('edit-capacity').value),
        has_projector: document.getElementById('edit-projector').checked ? 1 : 0,
        has_computers: document.getElementById('edit-computers').checked ? 1 : 0,
        has_ac: document.getElementById('edit-ac').checked ? 1 : 0
    };
    const res = await fetch('../api/rooms.php?action=update', {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
        closeModal('room-edit-modal');
        showToast('Room updated!', 'success');
        loadNowView();
    } else {
        showToast('Update failed', 'error');
    }
}

loadNowView();
setInterval(() => { if (roomView === 'now') loadNowView(); }, 30000);
</script>

<?php include '../includes/footer.php'; ?>
