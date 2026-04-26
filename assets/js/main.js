// =============================================
// ACADSCHED PRO — MAIN JAVASCRIPT
// =============================================

// ─── TOAST NOTIFICATIONS ─────────────────────
function showToast(msg, type = 'info', duration = 3500) {
    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle' };
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fas ${icons[type]}"></i><span>${msg}</span>`;
    document.getElementById('toast-container').appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateX(30px)'; t.style.transition = '.3s'; setTimeout(() => t.remove(), 300); }, duration);
}

// ─── MODAL HELPERS ───────────────────────────
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.addEventListener('click', e => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('open');
    }
});

// ─── SIDEBAR TOGGLE ──────────────────────────
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}

// ─── CONFLICT BADGE ──────────────────────────
async function loadConflictCount() {
    try {
        const res = await fetch('../api/conflicts.php?count=1');
        const data = await res.json();
        const badge = document.getElementById('conflict-count');
        if (data.count > 0) {
            badge.textContent = data.count;
            badge.style.display = 'inline';
        }
    } catch(e) {}
}

// ─── GENERIC DELETE ──────────────────────────
async function deleteRecord(endpoint, id, label, onSuccess) {
    if (!confirm(`Delete this ${label}? This cannot be undone.`)) return;
    try {
        const res = await fetch(`${endpoint}?id=${id}`, { method: 'DELETE' });
        const data = await res.json();
        if (data.success) {
            showToast(`${label} deleted.`, 'success');
            if (onSuccess) onSuccess();
        } else {
            showToast(data.error || 'Delete failed.', 'error');
        }
    } catch(e) {
        showToast('Network error.', 'error');
    }
}

// ─── EXPORT HELPERS ──────────────────────────
function downloadCSV(data, filename) {
    const blob = new Blob([data], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href = url; a.download = filename;
    a.click(); URL.revokeObjectURL(url);
}

// ─── TIME DISPLAY ─────────────────────────────
function formatTime(t) {
    if (!t) return '';
    const [h, m] = t.split(':');
    const hr = parseInt(h);
    const ampm = hr >= 12 ? 'PM' : 'AM';
    const h12 = hr % 12 || 12;
    return `${h12}:${m} ${ampm}`;
}

// ─── DEBOUNCE ─────────────────────────────────
function debounce(fn, delay) {
    let timer;
    return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), delay); };
}

// Load conflict count on page load
document.addEventListener('DOMContentLoaded', loadConflictCount);

// ─── TOAST NOTIFICATIONS ─────────────────────
function showToast(msg, type = 'info', duration = 3500) {
    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle' };
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fas ${icons[type]}"></i><span>${msg}</span>`;
    const container = document.getElementById('toast-container');
    if (container) container.appendChild(t);
    setTimeout(() => { 
        t.style.opacity = '0'; 
        t.style.transform = 'translateX(30px)'; 
        t.style.transition = '.3s'; 
        setTimeout(() => t.remove(), 300); 
    }, duration);
}

// ─── DATA FETCHING ───────────────────────────
// async function loadSchedule() {
//     const display = document.getElementById('schedule-display');
//     if (!display) return;
//     try {
//         const res = await fetch('../api/schedules.php?action=list');
//         const data = await res.json();
//         renderSchedule(data.schedules || []);
//     } catch (e) {
//         display.innerHTML = '<div class="empty-state">Error connecting to server.</div>';
//     }
// }

// function renderSchedule(schedules) {
//     const display = document.getElementById('schedule-display');
//     if (!display) return;
//     if (schedules.length === 0) {
//         display.innerHTML = '<div class="empty-state">No schedules found.</div>';
//         return;
//     }
//     display.innerHTML = '<div class="schedule-list">Schedules Loaded!</div>';
// }
async function saveSchedule(event) {
    
    const form = document.getElementById('schedule-form');
    const formData = new FormData(form);
    
    // Convert FormData to a standard object
    const data = Object.fromEntries(formData.entries());

    try {
        const res = await fetch('../api/schedules.php?action=save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await res.json();

        if (result.success) {
            showToast('Schedule saved successfully!', 'success');
            closeModal('sched-modal');
            loadSchedulePage(); // Refresh the list so you see the new entry
        } else {
            showToast(result.error || 'Failed to save schedule', 'error');
        }
    } catch (e) {
        showToast('Network error. Please check your connection.', 'error');
    }
}

// // Start loading when page is ready
// document.addEventListener('DOMContentLoaded', () => {
//     if (document.getElementById('schedule-display')) {
//         loadSchedulePage();
//     }
// });