<?php
require_once '../includes/config.php';
$activePage = 'subjects';
$pageTitle = 'Subjects';
$db = getDB();
$colleges = $db->query("SELECT * FROM colleges ORDER BY name")->fetchAll();
$yearLevels = [1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year'];
include '../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Subjects</h1>
        <p>Manage subjects, units, year levels, and room requirements</p>
    </div>
    <button class="btn btn-primary" onclick="openAddSubject()">
        <i class="fas fa-plus"></i> Add Subject
    </button>
</div>

<div class="card mb-2" style="padding:14px 20px;">
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
        <div class="search-bar" style="flex:1;min-width:180px;">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control" id="search-subj" placeholder="Search subjects…" oninput="loadSubjects()">
        </div>
        <select class="form-select" id="filter-year" style="width:140px;" onchange="loadSubjects()">
            <option value="">All Years</option>
            <?php foreach ($yearLevels as $y => $label): ?>
                <option value="<?= $y ?>"><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <select class="form-select" id="filter-col" style="width:220px;" onchange="loadSubjects()">
            <option value="">All Colleges</option>
            <?php foreach ($colleges as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="card">
    <div id="subj-table-wrap">
        <div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div>
    </div>
</div>

<div class="modal-overlay" id="subj-modal">
    <div class="modal" style="width:620px;">
        <div class="modal-header">
            <div class="modal-title" id="subj-modal-title">Add Subject</div>
            <button class="modal-close" onclick="closeModal('subj-modal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="subj-id">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Subject Code *</label>
                    <input type="text" class="form-control" id="subj-code" placeholder="e.g. CS101">
                </div>
                <div class="form-group">
                    <label class="form-label">Units *</label>
                    <input type="number" class="form-control" id="subj-units" value="3" min="1" max="9" onchange="updateHoursDisplay()">
                    <small style="color:var(--text-muted);font-size:11px;" id="hours-info">= 3 hours per session</small>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Subject Name *</label>
                <input type="text" class="form-control" id="subj-name" placeholder="e.g. Introduction to Programming">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Year Level *</label>
                    <select class="form-select" id="subj-year">
                        <option value="">Select Year</option>
                        <?php foreach ($yearLevels as $y => $label): ?>
                            <option value="<?= $y ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">College</label>
                    <select class="form-select" id="subj-college">
                        <option value="">Select College</option>
                        <?php foreach ($colleges as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <hr class="section-divider">
            <div style="font-size:12px;font-weight:700;color:var(--text-secondary);margin-bottom:12px;font-family:var(--font-mono);letter-spacing:.08em;text-transform:uppercase;">Room Requirements</div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Room Type Required</label>
                    <select class="form-select" id="subj-roomtype">
                        <option value="any">Any</option>
                        <option value="lecture">Lecture Room</option>
                        <option value="lab">Laboratory</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Equipment Needed</label>
                    <div class="form-check"><input type="checkbox" id="req-proj"> <label for="req-proj">Projector</label></div>
                    <div class="form-check"><input type="checkbox" id="req-comp"> <label for="req-comp">Computers / Lab</label></div>
                    <div class="form-check"><input type="checkbox" id="req-ac">   <label for="req-ac">Air Conditioning</label></div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Description / Notes</label>
                <textarea class="form-control" id="subj-desc" rows="2" placeholder="Optional notes"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('subj-modal')">Cancel</button>
            <button class="btn btn-primary" onclick="saveSubject()"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>
</div>

<script>
const yearLevels = {1:'1st Year',2:'2nd Year',3:'3rd Year',4:'4th Year'};

function updateHoursDisplay() {
    const u = parseInt(document.getElementById('subj-units').value) || 1;
    document.getElementById('hours-info').textContent = `= ${u} hour${u>1?'s':''} per session`;
}

function openAddSubject() {
    document.getElementById('subj-modal-title').textContent = 'Add Subject';
    document.getElementById('subj-id').value = '';
    ['code','name','desc'].forEach(f => document.getElementById('subj-'+f).value = '');
    document.getElementById('subj-units').value = 3;
    document.getElementById('subj-year').value = '';
    document.getElementById('subj-college').value = '';
    document.getElementById('subj-roomtype').value = 'any';
    ['proj','comp','ac'].forEach(f => document.getElementById('req-'+f).checked = false);
    updateHoursDisplay();
    openModal('subj-modal');
}

function editSubject(s) {
    document.getElementById('subj-modal-title').textContent = 'Edit Subject';
    document.getElementById('subj-id').value = s.id;
    document.getElementById('subj-code').value = s.code;
    document.getElementById('subj-name').value = s.name;
    document.getElementById('subj-units').value = s.units;
    document.getElementById('subj-year').value = s.year_level;
    document.getElementById('subj-college').value = s.college_id || '';
    document.getElementById('subj-roomtype').value = s.room_type_required || 'any';
    document.getElementById('req-proj').checked = s.requires_projector == 1;
    document.getElementById('req-comp').checked = s.requires_computers == 1;
    document.getElementById('req-ac').checked   = s.requires_ac == 1;
    document.getElementById('subj-desc').value  = s.description || '';
    updateHoursDisplay();
    openModal('subj-modal');
}

async function saveSubject() {
    const payload = {
        id: parseInt(document.getElementById('subj-id').value) || 0,
        code: document.getElementById('subj-code').value.trim(),
        name: document.getElementById('subj-name').value.trim(),
        units: parseInt(document.getElementById('subj-units').value),
        year_level: parseInt(document.getElementById('subj-year').value),
        college_id: document.getElementById('subj-college').value,
        room_type_required: document.getElementById('subj-roomtype').value,
        requires_projector: document.getElementById('req-proj').checked ? 1 : 0,
        requires_computers: document.getElementById('req-comp').checked ? 1 : 0,
        requires_ac: document.getElementById('req-ac').checked ? 1 : 0,
        description: document.getElementById('subj-desc').value.trim()
    };
    
    if (!payload.code || !payload.name) { showToast('Code and Name required', 'error'); return; }
    if (!payload.year_level) { showToast('Year level required', 'error'); return; }

    const res = await fetch('../api/subjects.php', {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
        closeModal('subj-modal');
        showToast('Subject saved!', 'success');
        loadSubjects();
    } else {
        showToast(data.error || 'Save failed', 'error');
    }
}

const roomTypeColors = { lab: 'badge-purple', lecture: 'badge-blue', any: 'badge-gray' };
const yearBadges = { 1: 'badge-blue', 2: 'badge-green', 3: 'badge-amber', 4: 'badge-red' };

async function loadSubjects() {
    const q = document.getElementById('search-subj').value;
    const year = document.getElementById('filter-year').value;
    const col = document.getElementById('filter-col').value;
    const res = await fetch(`../api/subjects.php?q=${encodeURIComponent(q)}&year=${year}&college=${col}`);
    const data = await res.json();
    const wrap = document.getElementById('subj-table-wrap');
    if (!data.subjects.length) {
        wrap.innerHTML = '<div class="empty-state"><i class="fas fa-book"></i><p>No subjects found</p></div>';
        return;
    }
    wrap.innerHTML = `
        <div class="table-wrapper">
        <table>
            <thead><tr>
                <th>Code</th><th>Subject Name</th><th>Units/Hrs</th><th>Year</th><th>Room Type</th><th>Equipment</th><th>College</th><th>Actions</th>
            </tr></thead>
            <tbody>
            ${data.subjects.map(s => `
                <tr>
                    <td class="mono" style="font-weight:700;">${s.code}</td>
                    <td>${s.name}</td>
                    <td class="mono"><span class="badge badge-blue">${s.units}u / ${s.units}h</span></td>
                    <td><span class="badge ${yearBadges[s.year_level]}">${{1:'1st',2:'2nd',3:'3rd',4:'4th'}[s.year_level]} Year</span></td>
                    <td><span class="badge ${roomTypeColors[s.room_type_required] || 'badge-gray'}">${s.room_type_required}</span></td>
                    <td style="font-size:13px;">
                        ${s.requires_projector?'📽 ':''}${s.requires_computers?'💻 ':''}${s.requires_ac?'❄':''}${(!s.requires_projector&&!s.requires_computers&&!s.requires_ac)?'<span class="text-muted">—</span>':''}
                    </td>
                    <td style="font-size:11px;color:var(--text-muted);">${s.college_name || '—'}</td>
                    <td>
                        <button class="btn btn-outline btn-sm btn-icon" onclick='editSubject(${JSON.stringify(s)})' title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger btn-sm btn-icon" onclick="deleteRecord('../api/subjects.php',${s.id},'subject',loadSubjects)" title="Delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `).join('')}
            </tbody>
        </table>
        </div>
    `;
}

loadSubjects();
</script>

<?php include '../includes/footer.php'; ?>