# 🏥 Barangay Health Outreach Management System

A web-based digital health center workspace designed for Philippine local government units (LGUs) and Barangay Health Centers. The system streamlines beneficiary profiling, outreach program coordination, attendance tracking, clinical service documentation, appointment scheduling, and medicine inventory management.

---

## 📋 Features

* **Real-Time Dashboard**: High-level metrics for total residents, active missions, registered beneficiaries, and clinical services served, along with automated shortage warning banners for low-stock medicines.
* **Resident Records & Profiling**: Demographic tracking with automatic age computation and visual category pills (`Senior Citizen 60+`, `Child / Pediatric`, `Adult`). Features an interactive Health Dossier modal displaying a resident's complete outreach history.
* **Health Programs & Outreach**: Schedule and monitor community medical missions, dental drives, feeding programs, and vaccination campaigns with real-time participant counts.
* **Beneficiary Registrations**: Register residents for specific programs with duplicate prevention and **1-Click Attendance Check-In** (`✓ Mark Attended`).
* **Clinic Appointments**: Patient consultation scheduling with attending physician assignments and instant status updates (`✓ Complete` / `✕ Cancel`).
* **Clinical Service Records**: Detailed treatment and prescription documentation for outreach attendees.
* **Supplies & Medicine Inventory**: Stock monitoring with quick-dispense (`-10`) and restock (`+10`, `+50`) actions and automatic status indicators (`Adequate`, `Low Stock`, `Out of Stock`).
* **Personnel & Staff Directory**: Manage municipal health officers, nurses, and Barangay Health Workers (BHWs) with role-based access control.
* **Reports & Analytics**: Visual demographic summaries, attendance rate computations, **instant CSV export**, and print-ready summary layouts.
* **Security & Authentication**: Bcrypt password hashing, session fixation prevention, profile dropdown, and a self-service **Change Password** modal.

---

## 🛠️ Tech Stack

* **Backend**: PHP 8.0+ (Vanilla PDO, REST JSON API architecture)
* **Database**: MySQL / MariaDB (InnoDB, UTF-8 MB4)
* **Frontend**: HTML5, CSS3 (Custom Design System, Mobile Responsive), Vanilla JavaScript (ES6+ Fetch API)
* **Local Server**: XAMPP (Apache & MySQL)

---

## 🚀 How to Run Locally

### 1. Prerequisites
Make sure you have **[XAMPP](https://www.apachefriends.org/)** installed on your computer with **PHP 8.0 or higher** and **MySQL**.

---

### 2. Place the Project in XAMPP
Place the project folder inside your XAMPP web root directory:
```text
C:\xampp\htdocs\barangay-health-system
```

---

### 3. Start Apache & MySQL
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.
Both modules should display a green background indicating they are running.

---

### 4. Create and Seed the Database

#### Option A: Using phpMyAdmin (Web Browser)
1. Open your browser and go to [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/).
2. Click **New** in the left sidebar to create a database.
3. Set the database name to:
   ```text
   barangay_health_db
   ```
   Select collation `utf8mb4_unicode_ci` and click **Create**.
4. With `barangay_health_db` selected, click the **Import** tab at the top.
5. Click **Choose File**, select `database/schema.sql`, and click **Import**.
6. Repeat step 5 to import `database/seed.sql` to populate sample residents, programs, and staff accounts.

#### Option B: Using the Command Prompt / Terminal
Open your terminal or command prompt and run:
```bash
# 1. Create database and import schema
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS barangay_health_db;"
C:\xampp\mysql\bin\mysql.exe -u root barangay_health_db < "C:\xampp\htdocs\barangay-health-system\database\schema.sql"

# 2. Import seed records
C:\xampp\mysql\bin\mysql.exe -u root barangay_health_db < "C:\xampp\htdocs\barangay-health-system\database\seed.sql"
```

---

### 5. Verify Database Configuration
The database configuration is located in [`config/database.php`](file:///C:/xampp/htdocs/barangay-health-system/config/database.php):
```php
$host     = "localhost";
$dbname   = "barangay_health_db";
$username = "root";
$password = ""; // Default XAMPP MySQL password is empty
```
If your local MySQL uses a custom password or port, update this file accordingly.

---

### 6. Launch the Application
Open your web browser and navigate to:
```text
http://localhost/barangay-health-system/
```
You will be greeted by the Sign-in screen.

---

## 🔑 Default Test Credentials

The database comes pre-seeded with 3 authorized accounts with different permission levels:

| Role | Username | Password | Permissions |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin123` | Full access: personnel creation, system settings, records |
| **Barangay Staff** | `staff` | `staff123` | Outreach management, resident records, supplies |
| **Health Worker** | `bhw` | `bhw123` | Field consultations, attendance check-in, service records |

> **Tip:** You can also click the quick **Demo Account Chips** on the login screen to automatically fill in the credentials.

---

## 📂 Project Directory Structure

```text
barangay-health-system/
│
├── api/                             # RESTful JSON Backend Endpoints
│   ├── appointments/index.php       # GET list, POST create & update status
│   ├── auth/change-password.php     # POST password updates
│   ├── dashboard/index.php          # GET high-level metrics & recent activities
│   ├── inventory/index.php          # GET list, POST create & adjust stock
│   ├── personnel/index.php          # GET list, POST register staff (Admin only)
│   ├── programs/index.php           # GET list, POST create outreach programs
│   ├── registrations/index.php      # GET list, POST register beneficiary & check-in
│   ├── residents/index.php          # GET list/dossier, POST create & edit
│   ├── services/index.php           # GET list, POST record clinical consultation
│   └── response.php                 # Shared JSON response & error utilities
│
├── config/
│   └── database.php                 # PDO database connector
│
├── database/
│   ├── schema.sql                   # MySQL database schema (7 normalized tables)
│   └── seed.sql                     # Test data & bcrypt user credentials
│
├── includes/                        # Reusable Server-Side Partials
│   ├── auth.php                     # Session verification & RBAC helper methods
│   ├── header.php                   # HTML <head> and asset inclusions
│   ├── sidebar.php                  # Harmonized SVG sidebar navigation
│   └── footer.php                   # Modals (creation forms, dossiers) & closing tags
│
├── public/                          # Web Document Root
│   ├── assets/
│   │   ├── css/style.css            # Custom CSS variables, components & print styles
│   │   └── js/script.js             # Client-side AJAX controller & modal manager
│   ├── pages/                       # Modular View Components
│   │   ├── dashboard.php            # Metric cards & shortage alerts
│   │   ├── residents.php            # Demographic table with age badges
│   │   ├── programs.php             # Outreach activities & schedule tracker
│   │   ├── registrations.php        # Beneficiary attendance table
│   │   ├── appointments.php         # Clinic visit scheduling
│   │   ├── services.php             # Clinical notes & medication records
│   │   ├── personnel.php            # Staff directory (Admin-guarded)
│   │   ├── inventory.php            # Pharmacy supply ledger & quick stock adjust
│   │   └── reports.php              # Summary reports & CSV exporter
│   ├── index.php                    # Authenticated workspace application shell
│   ├── login.php                    # Sign-in portal with validation & demo chips
│   └── logout.php                   # Session invalidation & redirect handler
│
├── index.php                        # Root entry redirector to public/
├── FOLDER.MD                        # Initial architecture roadmap
└── README.md                        # Documentation & setup guide
```

---

## 📡 REST API Summary

All API endpoints return standard JSON responses:

* `GET /api/residents/index.php` — List residents or search by query (`?search=...`).
* `GET /api/residents/index.php?resident_id={id}` — Retrieve full patient health dossier (registrations, services, appointments).
* `POST /api/residents/index.php` — Create or update resident details (`action: 'update'`).
* `GET /api/programs/index.php` — List health programs with registration counters.
* `POST /api/programs/index.php` — Create a new health program.
* `POST /api/registrations/index.php` — Enroll beneficiary or mark attendance (`action: 'update_status'`).
* `POST /api/appointments/index.php` — Book clinic consultation or resolve status (`action: 'update_status'`).
* `POST /api/inventory/index.php` — Add supply item or quick-adjust stock quantity (`action: 'adjust_stock'`).
* `POST /api/auth/change-password.php` — Authenticated password update.

---

## 📄 License
Designed for local government and community health centers. Open for educational and community development use.
