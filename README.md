# AcadSched Pro — Academic Scheduling System

A complete PHP/MySQL/XAMPP scheduling system with real-time room availability,
auto-matching, conflict detection, and export features.

---

## 📁 File Structure

```
scheduling_system/
├── index.php                  ← Dashboard
├── setup.php                  ← First-time DB setup UI
├── setup_run.php              ← Setup backend
├── database.sql               ← Full database schema + seed data
│
├── includes/
│   ├── config.php             ← DB connection + helpers
│   ├── header.php             ← Layout header (sidebar + topbar)
│   └── footer.php             ← Layout footer
│
├── pages/
│   ├── professors.php         ← Add/edit/delete professors + expertise
│   ├── subjects.php           ← Add/edit/delete subjects with requirements
│   ├── schedule.php           ← Schedule builder (grid + list + auto-match)
│   ├── room_availability.php  ← Real-time room status + weekly grid
│   ├── teacher_load.php       ← Load report with unit bars
│   ├── conflicts.php          ← Conflict detection page
│   └── export.php             ← Export interface (CSV + PDF)
│
├── api/
│   ├── colleges.php           ← Colleges list
│   ├── professors.php         ← CRUD + expertise
│   ├── subjects.php           ← CRUD + requirements
│   ├── schedules.php          ← CRUD + auto-match + conflict check
│   ├── rooms.php              ← Status + availability grid + edit
│   ├── conflicts.php          ← Real-time conflict scan
│   ├── teacher_load.php       ← Load report + CSV export
│   └── export.php             ← CSV + PDF export engine
│
└── assets/
    ├── css/style.css          ← Full dark theme stylesheet
    └── js/main.js             ← Toast, modals, helpers
```

---

## 🚀 Setup Instructions (XAMPP)

### Step 1 — Copy Files
Copy the entire `scheduling_system/` folder to:
```
C:\xampp\htdocs\scheduling_system\
```

### Step 2 — Start XAMPP
Start **Apache** and **MySQL** in the XAMPP Control Panel.

### Step 3 — Initialize Database

**Option A (Recommended — Web Setup):**
Open your browser and go to:
```
http://localhost/scheduling_system/setup.php
```
Fill in your DB credentials and click **Initialize Database**.

**Option B (Manual — phpMyAdmin):**
1. Go to `http://localhost/phpmyadmin`
2. Click **Import**
3. Choose `scheduling_system/database.sql`
4. Click **Go**

### Step 4 — Open the App
```
http://localhost/scheduling_system/index.php
```

---

## 🗄️ Database Details

- **Database:** `scheduling_db`
- **Default credentials:** root / (no password)
- Edit `includes/config.php` to change DB settings

---

## ✨ Features

| Feature | Description |
|---------|-------------|
| Dashboard | Live room status, today's classes, recent schedules |
| Room Availability | Real-time "right now" view + weekly time-slot grid |
| Professor Management | Add professors with expertise tags (which subjects they can teach) |
| Subject Management | Add subjects with units, year level (1–4), section (101–405), room requirements |
| Schedule Builder | Grid + list view, add schedules, auto-match button |
| Auto-Match | Select subject + day + time → system suggests available professors & rooms |
| Conflict Detection | Detects room double-bookings and teacher double-bookings in real time |
| Teacher Load Report | Shows units per teacher, progress bar, overload warnings |
| Export | CSV and printable PDF for full schedule, per professor, per room, teacher load |

---

## 📐 Business Rules

- **1 unit = 1 hour** — end time is auto-calculated from subject units
- **Rooms:** Lab1–Lab4 (computers), 3A–5E (lecture rooms), with projector/AC flags
- **Sections:** Year 1 → 101–105, Year 2 → 201–205, Year 3 → 301–305, Year 4 → 401–405
- **Colleges:** CCS, COA, CBA, COE, CED
- **Time slots:** 7:00 AM – 8:00 PM in 1-hour increments
- **Days:** Monday – Saturday

---

## ⚙️ Configuration

Edit `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'scheduling_db');
define('CURRENT_SEMESTER', '1st Semester');
define('CURRENT_SY', '2025-2026');
```
