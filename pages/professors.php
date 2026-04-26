<?php
require_once '../includes/config.php';
$activePage = 'professors';
$pageTitle = 'Professors';
$db = getDB();

$colleges = $db->query("SELECT * FROM colleges ORDER BY name")->fetchAll();
$subjects = $db->query("SELECT id, code, name, year_level FROM subjects WHERE is_active=1 ORDER BY code")->fetchAll();

include '../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Professors</h1>
        <p>Manage faculty members and their subject expertise</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i> Add Professor
    </button>
</div>

<!-- Filters -->
<div class="card mb-2" style="padding:14px 20px;">
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
        <div class="search-bar" style="flex:1;min-width:200px;">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control" id="search-input" placeholder="Search professors…" oninput="loadProfessors()">
        </div>
        <div style="width:220px;">
    <select class="form-select" id="filter-professor" onchange="loadProfessors()">
        <option value="">All Professors</option>
        <!-- dynamically filled -->
    </select>
</div>
        <select class="form-select" id="filter-college" style="width:220px;" onchange="loadProfessors()">
            <option value="">All Colleges</option>
            <?php foreach ($colleges as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div id="prof-table-wrap">
        <div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="prof-modal">
    <div class="modal" style="width:640px;">
        <div class="modal-header">
            <div class="modal-title" id="modal-title">Add Professor</div>
            <button class="modal-close" onclick="closeModal('prof-modal')"><i class="fas fa-times"></i></button>
        </div>

        <div class="modal-body">
            <input type="hidden" id="prof-id">

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">First Name *</label>
                    <input type="text" class="form-control" id="prof-fn">
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name *</label>
                    <input type="text" class="form-control" id="prof-ln">
                </div>
            </div>

            <!-- Employment Type -->
            <div class="form-group">
                <label class="form-label">Employment Type</label>
                <select class="form-select" id="prof-type">
                    <option value="">Select Type</option>
                    <option value="full-time">Full-time</option>
                    <option value="part-time">Part-time</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Max Units / Week</label>
                    <input type="number" class="form-control" id="prof-maxu" value="21">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" id="prof-email">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control" id="prof-phone">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">College</label>
                <select class="form-select" id="prof-college">
                    <option value="">Select College</option>
                    <?php foreach ($colleges as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Specialization / Notes</label>
                <textarea class="form-control" id="prof-spec" rows="2"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Subject Expertise (can teach)</label>
                <select class="form-select" id="expertise-select" onchange="addExpertise()">
                    <option value="">— Add a subject —</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>" data-code="<?= htmlspecialchars($s['code']) ?>" data-name="<?= htmlspecialchars($s['name']) ?>">
                            [Y<?= $s['year_level'] ?>] <?= htmlspecialchars($s['code']) ?> — <?= htmlspecialchars($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="tag-list mt-1" id="expertise-tags"></div>
                <input type="hidden" id="expertise-ids" value="[]">
            </div>

            <!-- ✅ ADDED: Availability Section -->
            <div class="form-group">
                <label class="form-label">Availability</label>
                <div id="availability-box" style="font-size:12px;color:#555;">
                    No availability loaded
                </div>

                <div style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;">

    <select id="avail-day" class="form-select" style="width:140px;">
        <option value="">Day</option>
        <option>Monday</option>
        <option>Tuesday</option>
        <option>Wednesday</option>
        <option>Thursday</option>
        <option>Friday</option>
        <option>Saturday</option>
        <option>Sunday</option>
    </select>

    <input type="time" id="avail-start" class="form-control" style="width:130px;">
    <input type="time" id="avail-end" class="form-control" style="width:130px;">

    <button type="button" class="btn btn-primary" onclick="saveAvailability()">
        Add
    </button>

</div>
            </div>

        </div>

        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('prof-modal')">Cancel</button>
            <button class="btn btn-primary" onclick="saveProfessor()">
                <i class="fas fa-save"></i> Save
            </button>
        </div>
    </div>
</div>

<script>
let expertiseList = [];

function openAddModal() {
    document.getElementById('modal-title').textContent = 'Add Professor';
    document.getElementById('prof-id').value = '';

    ['fn','ln','email','phone','spec'].forEach(f => {
        document.getElementById('prof-' + f).value = '';
    });

    document.getElementById('prof-type').value = '';
    document.getElementById('prof-maxu').value = 21;
    document.getElementById('prof-college').value = '';

    expertiseList = [];
    renderExpertiseTags();

    document.getElementById('availability-box').innerHTML = "No availability loaded";

    openModal('prof-modal');
}

function addExpertise() {
    const sel = document.getElementById('expertise-select');
    const id = parseInt(sel.value);
    if (!id) return;

    const opt = sel.selectedOptions[0];

    if (!expertiseList.find(e => e.id === id)) {
        expertiseList.push({ id, code: opt.dataset.code, name: opt.dataset.name });
        renderExpertiseTags();
    }

    sel.value = '';
}

function removeExpertise(id) {
    expertiseList = expertiseList.filter(e => e.id !== id);
    renderExpertiseTags();
}

function renderExpertiseTags() {
    document.getElementById('expertise-tags').innerHTML = expertiseList.map(e =>
        `<span class="tag">${e.code} <button class="tag-remove" onclick="removeExpertise(${e.id})">✕</button></span>`
    ).join('');

    document.getElementById('expertise-ids').value = JSON.stringify(expertiseList.map(e => e.id));
}

/* ✅ ADDED AVAILABILITY LOADER */
async function loadAvailability(id) {
    const res = await fetch(`../api/availability.php?professor_id=${id}`);
    const data = await res.json();

    const box = document.getElementById('availability-box');

    if (!data.availability || data.availability.length === 0) {
        box.innerHTML = "No availability set";
        return;
    }

    box.innerHTML = data.availability.map(a => `
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;padding:6px;border:1px solid #ddd;border-radius:6px;">
        
        <span>
            ${a.day_of_week} — ${a.start_time} to ${a.end_time}
        </span>

        <div style="display:flex;gap:6px;">
            
            <!-- EDIT BUTTON -->
            <button class="btn btn-outline btn-sm"
                onclick="editAvailability(${a.id}, '${a.day_of_week}', '${a.start_time}', '${a.end_time}')">
                ✏ Edit
            </button>

            <!-- DELETE BUTTON -->
            <button class="btn btn-danger btn-sm"
                onclick="deleteAvailability(${a.id}, ${data.professor_id || document.getElementById('prof-id').value})">
                ✕
            </button>

        </div>
    </div>
`).join('');
}

async function editProfessor(id) {
    const res = await fetch(`../api/professors.php?action=single&id=${id}`);
    const data = await res.json();
    const p = data.professor;

    document.getElementById('modal-title').textContent = 'Edit Professor';
    document.getElementById('prof-id').value = p.id;
    document.getElementById('prof-fn').value = p.first_name;
    document.getElementById('prof-ln').value = p.last_name;
    document.getElementById('prof-type').value = p.employment_type || '';
    document.getElementById('prof-email').value = p.email || '';
    document.getElementById('prof-phone').value = p.phone || '';
    document.getElementById('prof-college').value = p.college_id || '';
    document.getElementById('prof-maxu').value = p.max_units || 21;
    document.getElementById('prof-spec').value = p.specialization || '';

    expertiseList = (p.expertise || []).map(e => ({
        id: e.id,
        code: e.code,
        name: e.name
    }));

    renderExpertiseTags();

    loadAvailability(p.id); // ✅ IMPORTANT

    openModal('prof-modal');
}

async function saveProfessor() {
    const payload = {
        id: parseInt(document.getElementById('prof-id').value) || 0,
        first_name: document.getElementById('prof-fn').value.trim(),
        last_name: document.getElementById('prof-ln').value.trim(),
        employment_type: document.getElementById('prof-type').value,
        email: document.getElementById('prof-email').value.trim(),
        phone: document.getElementById('prof-phone').value.trim(),
        college_id: document.getElementById('prof-college').value,
        max_units: parseInt(document.getElementById('prof-maxu').value),
        specialization: document.getElementById('prof-spec').value.trim(),
        expertise: JSON.parse(document.getElementById('expertise-ids').value || '[]')
    };

    const res = await fetch('../api/professors.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(payload)
    });

    const data = await res.json();

    if (data.success) {
        closeModal('prof-modal');
        showToast('Professor saved!', 'success');
        loadProfessors();
    } else {
        showToast(data.error || 'Save failed', 'error');
    }
}

async function loadProfessors() {
    const search = document.getElementById('search-input').value;
    const college = document.getElementById('filter-college').value;
    const professorId = document.getElementById('filter-professor').value;

    const res = await fetch(
        `../api/professors.php?action=list&q=${encodeURIComponent(search)}&college=${college}`
    );

    const data = await res.json();

    let professors = data.professors;

    // filter dropdown (client-side safe)
    if (professorId) {
        professors = professors.filter(p => p.id == professorId);
    }

    document.getElementById('prof-table-wrap').innerHTML = `
        <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>College</th>
                    <th>Max Units</th>
                    <th>Specialization</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                ${professors.map(p => `
                    <tr>
                        <td class="mono">EMP-${String(p.id).padStart(3,'0')}</td>
                        <td><strong>${p.last_name}, ${p.first_name}</strong></td>
                        <td>${p.employment_type || '—'}</td>
                        <td>${p.college_name || '—'}</td>
                        <td>${p.max_units}</td>
                        <td>${p.specialization || '—'}</td>
                        <td>
                            <button class="btn btn-outline btn-sm btn-icon" onclick="editProfessor(${p.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-icon"
                                onclick="deleteRecord('../api/professors.php', ${p.id}, 'professor', loadProfessors)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
        </div>
    `;
}

loadProfessors();

async function saveAvailability() {
    const profId = document.getElementById('prof-id').value;

    const payload = {
        professor_id: profId,
        day_of_week: document.getElementById('avail-day').value,
        start_time: document.getElementById('avail-start').value,
        end_time: document.getElementById('avail-end').value
    };

    if (!payload.professor_id || !payload.day_of_week || !payload.start_time || !payload.end_time) {
        alert("Complete all fields first");
        return;
    }

    // ✅ CHECK IF EDIT MODE
    if (window.editingAvailabilityId) {
        payload.action = "update";
        payload.id = window.editingAvailabilityId;
    }

    const res = await fetch('../api/availability.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(payload)
    });

    const data = await res.json();
    console.log("SAVE RESPONSE:", data);

    window.editingAvailabilityId = null;

    loadAvailability(profId);

    document.getElementById('avail-day').value = '';
    document.getElementById('avail-start').value = '';
    document.getElementById('avail-end').value = '';
}

let editingAvailabilityId = null;

function editAvailability(id, day, start, end) {

    // safety for "08:00:00"
    if (start) start = start.substring(0,5);
    if (end) end = end.substring(0,5);

    document.getElementById('avail-day').value = day;
    document.getElementById('avail-start').value = start;
    document.getElementById('avail-end').value = end;

    window.editingAvailabilityId = id;

    console.log("Editing ID:", id);
}

async function deleteAvailability(availId, profId) {
    if (!confirm("Delete this availability?")) return;

    await fetch('../api/availability.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'delete',
            id: availId
        })
    });

    loadAvailability(profId);
}
</script>

<?php include '../includes/footer.php'; ?>