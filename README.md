# ApexCare - Hospital Appointment Booking System

A state-of-the-art, industry-level Hospital Appointment Booking System developed with **PHP**, **MySQL**, **HTML5/CSS3 (Liquid Glass Theme)**, and **JavaScript**. Designed for seamless deployment on **XAMPP**.

---

## 🌟 Key Features

1. **Liquid Glass Theme & Micro-Animations**
   - Backdrop blurs (`backdrop-filter`), glowing neon accents (`#00f2fe`, `#4facfe`), floating liquid gradient blobs.
   - Fully responsive design system with custom glass cards, interactive chips, and CSS grid layouts.

2. **Interactive Hospital Facility Map**
   - Embedded Leaflet.js interactive dark-themed map with custom glowing hospital pinpoint marker.

3. **Admin Control Panel (`yash` / `1234`)**
   - Real-time Analytics Dashboard (Total Doctors, Registered Patients, Appointments, Consultation Revenue).
   - **Doctor Management**:
     - ➕ Add New Doctor accounts (department, fee, experience, qualification, availability, bio, profile image).
     - ✏️ Edit Doctor info in real-time.
     - 🗑️ Delete Doctor accounts (cascading delete).
   - **Appointment Overseer**: Update status (`Pending`, `Confirmed`, `Completed`, `Cancelled`).

4. **Doctor Portal**
   - Personal consultation schedule, patient history, symptom details, and one-click status updates.

5. **Patient Portal & Booking Flow**
   - Browse doctors by department filters.
   - Real-time consultation fee calculation & slot lock.
   - Unique reference code generation (e.g. `APX-20260724-854`).
   - Track booking status and cancel upcoming visits.

---

## 🔑 Pre-Configured Demo Credentials

| Role | Username | Password | Key Capabilities |
| :--- | :--- | :--- | :--- |
| **Admin** | `yash` | `1234` | Full access: Add/Edit/Delete Doctors, view system stats & appointments |
| **Doctor** | `dr_sarah` | `1234` | View patient queue, mark appointments as Confirmed/Completed |
| **Patient** | `alex_j` | `1234` | Book new appointments, track booking history, cancel bookings |

*Note: On the login page, quick demo buttons are available to auto-fill these credentials in 1 click.*

---

## 🚀 How to Run in XAMPP (Step-by-Step)

### Step 1: Copy Project to XAMPP htdocs
Copy the `hospital_system` folder into your XAMPP `htdocs` directory:
`C:\xampp\htdocs\hospital_system`

### Step 2: Start XAMPP Control Panel
1. Open **XAMPP Control Panel**.
2. Click **Start** for **Apache**.
3. Click **Start** for **MySQL**.

### Step 3: Import Database (`database.sql`)
1. Open your browser and navigate to `http://localhost/phpmyadmin`
2. Click on **Import** in the top menu.
3. Click **Choose File** and select `hospital_system/database.sql`.
4. Click **Go** at the bottom to create `hospital_db` and insert sample data.

### Step 4: Open Website
Visit `http://localhost/hospital_system` in your browser!

---

## 📁 File Structure

```
hospital_system/
├── database.sql           # Database schema & pre-seeded data
├── config.php             # PDO database connection & sessions
├── index.php              # Public landing page with glass UI & Leaflet Map
├── login.php              # Unified login page with quick credentials fill
├── register.php           # Patient registration page
├── logout.php             # Session logout handler
├── admin_dashboard.php    # Admin control center (Doctor CRUD & appointments)
├── doctor_dashboard.php   # Doctor portal
├── patient_dashboard.php  # Patient portal & history
├── book_appointment.php   # Booking form & logic
├── assets/
│   ├── css/
│   │   └── style.css      # Liquid Glass design system & animations
│   └── js/
│       └── main.js        # Leaflet map & dynamic filtering logic
└── README.md              # Setup & documentation guide
```
