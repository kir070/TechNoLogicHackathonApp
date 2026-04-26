<!DOCTYPE html>
<html lang="en">
<head>
    <script>
function setTheme(theme) {
    document.body.classList.toggle('light', theme === 'light');
    localStorage.setItem('theme', theme);
    updateThemeIcon(theme);
}

function toggleTheme() {
    const isLight = document.body.classList.contains('light');
    const newTheme = isLight ? 'dark' : 'light';
    setTheme(newTheme);
}

function updateThemeIcon(theme) {
    const icon = document.querySelector('#theme-icon');
    if (!icon) return;

    icon.className = theme === 'light'
        ? 'fas fa-sun'
        : 'fas fa-moon';
}

// Load saved theme on page load
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    setTheme(savedTheme);
});

// Core UI - Must be in header so buttons work before main.js loads
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.add('open');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('open');
}

function toggleSidebar() {
    const sb = document.getElementById('sidebar');
    if (sb) sb.classList.toggle('open');
}

// Function specifically for Professor Page
function openAddModal() {
    const form = document.getElementById('professor-form');
    if (form) form.reset();
    const idField = document.getElementById('prof-id');
    if (idField) idField.value = '';
    const title = document.getElementById('modal-title');
    if (title) title.textContent = 'Add New Professor';
    openModal('prof-modal');
}

// Function specifically for Schedule Page
function openAddSchedule() {
    const form = document.getElementById('schedule-form');
    if (form) form.reset();
    const idField = document.getElementById('sched-id');
    if (idField) idField.value = '';
    const title = document.getElementById('sched-modal-title');
    if (title) title.textContent = 'Add New Schedule';
    openModal('sched-modal');
}

function openAutoMatch() {
    openModal('automatch-modal');
}
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' — ' : '' ?>Scheduling</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500&family=Sora:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/scheduling_system/assets/css/style.css">
    </head>
<body>
<div class="app-shell">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="brand-text">
                <span class="brand-name">Scheduling</span>
                <span class="brand-sub">System</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Overview</div>
            <a href="../index.php" class="nav-item <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i><span>Dashboard</span>
            </a>
            <a href="pages/room_availability.php" class="nav-item <?= ($activePage ?? '') === 'rooms' ? 'active' : '' ?>">
                <i class="fas fa-door-open"></i><span>Room Availability</span>
            </a>
            <div class="nav-section-label">Management</div>
            <a href="professors.php" class="nav-item <?= ($activePage ?? '') === 'professors' ? 'active' : '' ?>">
                <i class="fas fa-chalkboard-teacher"></i><span>Professors</span>
            </a>
            <a href="subjects.php" class="nav-item <?= ($activePage ?? '') === 'subjects' ? 'active' : '' ?>">
                <i class="fas fa-book"></i><span>Subjects</span>
            </a>
            <a href="schedule.php" class="nav-item <?= ($activePage ?? '') === 'schedule' ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt"></i><span>Schedule Builder</span>
            </a>
            <div class="nav-section-label">Reports</div>
            <a href="teacher_load.php" class="nav-item <?= ($activePage ?? '') === 'teacher_load' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i><span>Teacher Load</span>
            </a>
            <a href="conflicts.php" class="nav-item <?= ($activePage ?? '') === 'conflicts' ? 'active' : '' ?>">
                <i class="fas fa-exclamation-triangle"></i><span>Conflicts</span>
                <span class="conflict-badge" id="conflict-count" style="display:none"></span>
            </a>
            <a href="export.php" class="nav-item <?= ($activePage ?? '') === 'export' ? 'active' : '' ?>">
                <i class="fas fa-file-export"></i><span>Export</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <span><?= CURRENT_SEMESTER ?> · <?= CURRENT_SY ?></span>
        </div>
    </aside>

    <!-- Main content -->
    <div class="main-content">
        <header class="topbar">
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="topbar-title"><?= isset($pageTitle) ? $pageTitle : 'Dashboard' ?></div>

    <div class="topbar-actions">
        <div class="realtime-indicator">
            <span class="pulse"></span>Live
        </div>
<button class="btn btn-outline btn-sm" onclick="toggleTheme()" title="Toggle Theme">
    <i id="theme-icon" class="fas fa-moon"></i>
</button>

        <span class="topbar-school">University Scheduling System</span>
    </div>
</header>
        <div class="page-content">
