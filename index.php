<?php
require_once 'includes/config.php';
function formatTime($time) {
    if (!$time) return '';
    return date("g:i A", strtotime($time));
}
$activePage = 'dashboard';
$pageTitle = 'Dashboard';
$db = getDB();

// Summary stats
$totalRooms     = $db->query("SELECT COUNT(*) FROM rooms WHERE is_active=1")->fetchColumn();
$totalProfs     = $db->query("SELECT COUNT(*) FROM professors WHERE is_active=1")->fetchColumn();
$totalSubjects  = $db->query("SELECT COUNT(*) FROM subjects WHERE is_active=1")->fetchColumn();
$totalSchedules = $db->query("SELECT COUNT(*) FROM schedules WHERE is_active=1")->fetchColumn();

// Today's day
$todayDay = date('l');
$allowedDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
if (!in_array($todayDay, $allowedDays)) $todayDay = 'Monday';

// Today's schedule
$todaySched = $db->prepare("
    SELECT s.*, p.first_name, p.last_name, sub.name AS subject_name, sub.code AS subject_code,
           sub.units, r.name AS room_name, r.room_type
    FROM schedules s
    JOIN professors p ON s.professor_id = p.id
    JOIN subjects sub ON s.subject_id = sub.id
    JOIN rooms r ON s.room_id = r.id
    WHERE s.day_of_week = ? AND s.is_active = 1
    ORDER BY s.start_time
");
$todaySched->execute([$todayDay]);
$todayClasses = $todaySched->fetchAll();

// Count conflicts
$conflicts = $db->query("SELECT COUNT(*) FROM conflict_log WHERE resolved=0")->fetchColumn();

// Available rooms right now
$nowTime = date('H:i:s');
$occupiedRoomIds = $db->prepare("
    SELECT DISTINCT room_id FROM schedules
    WHERE day_of_week = ? AND start_time <= ? AND end_time > ? AND is_active=1
");
$occupiedRoomIds->execute([$todayDay, $nowTime, $nowTime]);
$occupiedIds = array_column($occupiedRoomIds->fetchAll(), 'room_id');
$availableNow = $totalRooms - count($occupiedIds);

// Recent schedules
$recentSchedules = $db->query("
    SELECT s.*, p.first_name, p.last_name, sub.name AS subject_name,
           sub.code AS sub_code, r.name AS room_name, s.day_of_week
    FROM schedules s
    JOIN professors p ON s.professor_id = p.id
    JOIN subjects sub ON s.subject_id = sub.id
    JOIN rooms r ON s.room_id = r.id
    WHERE s.is_active=1
    ORDER BY s.created_at DESC LIMIT 8
")->fetchAll();

$dayColors = [
    'Monday' => '#4f7df9', 'Tuesday' => '#22c55e', 'Wednesday' => '#f59e0b',
    'Thursday' => '#ef4444', 'Friday' => '#8b5cf6', 'Saturday' => '#06b6d4'
];

include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p>Welcome back — <?= date('l, F j, Y') ?></p>
    </div>
    <a href="pages/schedule.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Schedule
    </a>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon blue"><i class="fas fa-door-open"></i></div>
        <div class="stat-value"><?= $availableNow ?></div>
        <div class="stat-label">Rooms Available Now</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green"><i class="fas fa-chalkboard-teacher"></i></div>
        <div class="stat-value"><?= $totalProfs ?></div>
        <div class="stat-label">Active Professors</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-icon amber"><i class="fas fa-book"></i></div>
        <div class="stat-value"><?= $totalSubjects ?></div>
        <div class="stat-label">Subjects</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon purple"><i class="fas fa-calendar-alt"></i></div>
        <div class="stat-value"><?= $totalSchedules ?></div>
        <div class="stat-label">Scheduled Classes</div>
    </div>
    <div class="stat-card <?= $conflicts > 0 ? 'red' : 'green' ?>">
        <div class="stat-icon <?= $conflicts > 0 ? 'red' : 'green' ?>"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-value"><?= $conflicts ?></div>
        <div class="stat-label">Active Conflicts</div>
    </div>
</div>

<div class="grid-2">
    <!-- Today's Classes -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-clock"></i> Today's Classes (<?= $todayDay ?>)</div>
            <span class="badge badge-blue"><?= count($todayClasses) ?> classes</span>
        </div>
        <?php if (empty($todayClasses)): ?>
            <div class="empty-state"><i class="fas fa-calendar-times"></i><p>No classes today</p></div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead><tr>
                        <th>Time</th><th>Subject</th><th>Professor</th><th>Room</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($todayClasses as $cls):
                        $color = $dayColors[$cls['day_of_week']] ?? '#4f7df9';
                    ?>
                        <tr>
                            <td class="mono" style="font-size:11px;white-space:nowrap;">
                                <?= formatTime($cls['start_time']) ?>–<?= formatTime($cls['end_time']) ?>
                            </td>
                            <td>
                                <div style="font-weight:600;"><?= htmlspecialchars($cls['subject_code']) ?></div>
                                <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($cls['subject_name']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($cls['last_name'] . ', ' . $cls['first_name']) ?></td>
                            <td><span class="badge badge-blue"><?= $cls['room_name'] ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Schedules -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-history"></i> Recently Added</div>
            <a href="pages/schedule.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <?php if (empty($recentSchedules)): ?>
            <div class="empty-state"><i class="fas fa-inbox"></i><p>No schedules yet</p></div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:8px;">
        <?php foreach ($recentSchedules as $s):
            $color = $dayColors[$s['day_of_week']] ?? '#4f7df9';
        ?>
            <div style="display:flex;align-items:center;gap:12px;padding:8px;border-radius:6px;background:var(--bg-hover);">
                <div style="width:6px;height:40px;border-radius:3px;background:<?= $color ?>;flex-shrink:0;"></div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($s['sub_code']) ?> — <?= htmlspecialchars($s['room_name']) ?></div>
                    <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($s['last_name']) ?> · <?= $s['day_of_week'] ?> <?= formatTime($s['start_time']) ?>–<?= formatTime($s['end_time']) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Room Quick-View -->
<div class="card mt-2">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-door-open"></i> Room Status — Right Now</div>
        <a href="pages/room_availability.php" class="btn btn-outline btn-sm">Full View</a>
    </div>
    <div class="room-grid" id="room-status-grid">
        <div class="empty-state"><i class="fas fa-spinner fa-spin"></i></div>
    </div>
</div>

<script>
async function loadRoomStatus() {
    const res = await fetch('api/rooms.php?action=status_now');
    const data = await res.json();
    const grid = document.getElementById('room-status-grid');
    if (!data.rooms || !data.rooms.length) {
        grid.innerHTML = '<div class="empty-state"><i class="fas fa-door-open"></i><p>No rooms found</p></div>';
        return;
    }
    grid.innerHTML = data.rooms.map(r => `
        <div class="room-card ${r.occupied ? 'occupied' : 'available'}">
            <div class="room-name">${r.name}</div>
            <div class="room-type">${r.room_type === 'lab' ? '🖥 Lab' : '📖 Lecture'}</div>
            <div class="room-equip">
                ${r.has_projector ? '<span class="equip-icon" title="Projector">📽</span>' : ''}
                ${r.has_computers ? '<span class="equip-icon" title="Computers">💻</span>' : ''}
                ${r.has_ac ? '<span class="equip-icon" title="AC">❄</span>' : ''}
            </div>
            <div class="room-status ${r.occupied ? 'occupied' : 'available'}">
                ${r.occupied ? '● Occupied' : '● Available'}
            </div>
            ${r.occupied && r.current_class ? `<div style="font-size:10px;color:var(--text-muted);margin-top:4px;">${r.current_class}</div>` : ''}
        </div>
    `).join('');
}
loadRoomStatus();
setInterval(loadRoomStatus, 30000); // refresh every 30s
</script>

<?php include 'includes/footer.php'; ?>
