# Student Project Portal for Internship (SPPI)

A comprehensive, responsive multi-role internship management web application built for an engineering college placement and academic cell.

---

## 🌟 Overview & Key Features

- **Multi-Role Authentication & Access Guards**: Dedicated interfaces and security guards for 4 user roles:
  1. **Student**: Self-register, edit profile & technical skills, upload resume PDF, browse/filter company listings, apply for openings, and track real-time application status.
  2. **Faculty / Mentor**: Review assigned students' applications, verify technical qualifications and resume PDFs, approve/reject applications, and provide written feedback remarks.
  3. **Company / Recruiter**: Create and manage internship job openings, view faculty-verified student candidates, download resumes, and mark status to Shortlisted or Selected/Hired.
  4. **Admin**: Manage master academic departments, register faculty members & company recruiter accounts, allocate mentors to self-registered students, and generate placement reports.

- **Design & UI/UX Excellence**: Built using a modern Navy Blue design system (`#0f172a`, `#1e293b`, `#2563eb`), Google Inter typography, status badge pills, responsive sidebar navigation, interactive metric cards, empty state illustrations, and client-side form validation feedback.

---

## 📂 Project Architecture & Folder Structure

```
student-project-portal/
├── config/
│   └── db_connect.php          # PDO MySQL database connection settings
├── includes/
│   ├── auth_check.php          # Session & role authorization guards, status badges
│   ├── header.php              # Responsive sidebar header layout
│   └── footer.php              # Global footer & JS scripts
├── assets/
│   ├── css/
│   │   ├── style.css           # Global design system & variables
│   │   ├── components.css      # Reusable cards, buttons, badges, tables, alerts
│   │   └── dashboard.css       # Sidebar layout & metric grid styles
│   └── js/
│       ├── validation.js       # Client-side form validation
│       └── main.js             # Navigation toggle & alert dismissals
├── uploads/
│   └── resumes/                # Uploaded student resume files (PDF, DOC, DOCX)
├── auth/
│   ├── register.php            # Student self-registration form
│   ├── login.php               # Unified role-based login (all 4 roles)
│   └── logout.php              # Session destruction & exit
├── student/
│   ├── dashboard.php           # Student overview & assigned mentor info
│   ├── profile.php             # Profile edit & PDF resume upload
│   ├── browse_internships.php  # Search and filter active internship openings
│   ├── apply.php               # Application submission with validation
│   └── my_applications.php     # Real-time status progression timeline
├── faculty/
│   ├── dashboard.php           # Faculty queue count & mentee stats
│   ├── review_applications.php # Student application review queue
│   └── give_feedback.php      # Approve/reject decision & written remarks
├── company/
│   ├── dashboard.php           # Recruiter stats & applicant counter
│   ├── post_internship.php     # Create & edit job postings
│   ├── manage_listings.php     # Manage active/closed listings
│   └── view_applicants.php     # Candidate review, resume download, shortlisting
├── admin/
│   ├── dashboard.php           # System statistics overview
│   ├── verify_students.php     # Student mentor allocation
│   ├── manage_departments.php  # Manage faculty mentor accounts
│   ├── manage_companies.php    # Register & manage partner companies
│   └── reports.php             # Department placement analytics & PDF print view
├── database/
│   └── sppi_schema.sql         # Database schema & sample seed data
├── index.php                   # Public landing page
└── README.md
```

---

## 🚀 Setup & Execution Instructions (XAMPP / Apache + MySQL)

### 1. Database Setup
1. Launch **XAMPP Control Panel** and start **Apache** and **MySQL** services.
2. Open phpMyAdmin (`http://localhost/phpmyadmin`).
3. Import the SQL script located at:
   `database/sppi_schema.sql`
   *(This automatically creates `sppi_db` with all 6 relational tables and initial test seed accounts)*.

### 2. File Deployment
1. Move the `student-project-portal` folder into your XAMPP `htdocs` directory (e.g., `C:\xampp\htdocs\student-project-portal`).
2. Open your browser and navigate to:
   `http://localhost/student-project-portal/`

---

## 💡 Pre-configured Test Accounts (Password for all: `Password123`)

| Role | Name | Email | Password |
| :--- | :--- | :--- | :--- |
| **Student** | Alex Johnson | `alex.j@student.sppi.edu` | `Password123` |
| **Faculty / Mentor** | Dr. Robert Vance | `rvance@sppi.edu` | `Password123` |
| **Company Recruiter** | TechNova HR | `recruitment@technova.com` | `Password123` |
| **Administrator** | System Admin | `admin@sppi.edu` | `Password123` |

---

## 🔒 Security Practices Implemented
- Passwords hashed using **bcrypt** (`password_hash` & `password_verify`).
- Prepared statements (`PDO::prepare`) used for all database queries to prevent **SQL Injection**.
- Output sanitized via `htmlspecialchars` to prevent **Cross-Site Scripting (XSS)**.
- Role authorization guards (`require_role`) enforced on every protected backend route.
