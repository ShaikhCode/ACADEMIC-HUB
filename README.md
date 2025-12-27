<h1 align="center">🎓 Academic Hub – Web-Based Academic Management Platform</h1>
<p align="center">
  A complete academic digitalization system built for colleges & schools — featuring multi-role dashboards for Admin, Staff & Students, enabling attendance, marks, feedback & academic reporting automation.
</p>

<p align="center">
  <img src="img/favicon.png" width="20%" style="border-radius: 7px;>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-Backend-blue">
  <img src="https://img.shields.io/badge/MySQL-Database-orange">
  <img src="https://img.shields.io/badge/HTML-CSS--JavaScript-Frontend-yellow">
  <img src="https://img.shields.io/badge/Role--Based-System-green">
  <img src="https://img.shields.io/badge/Version-1.0-lightgrey">
</p>

---

## 🧭 Table of Contents
- 🔥 About
- 🎯 Features
- 🧠 Workflow Diagram
- 🎨 UI Screenshots
- 🧩 Folder Structure
- 🚀 Tech Stack
- 🛠 Installation
- 🌍 Hosting via Ngrok
- 🧪 Demo Logins
- 🔐 Security
- 🗄 Database Schema
- 🧱 Future Enhancements
- 👥 Team

---

## 🔥 About the Project
Academic Hub replaces outdated academic registers and paperwork with a centralized connected academic management ecosystem — enabling fast, transparent, and digital workflows.

---

## 🎯 Core Features
| Module | Capabilities |
|--------|--------------|
| 👨‍💼 Admin | Add/manage staff, subjects, classes, students, view feedback & analytics |
| 👨‍🏫 Staff | Mark attendance, upload marks, manage class list, generate reports |
| 🎓 Student | View attendance %, marks, leaderboard, submit feedback, view profile |
| 🔐 Core System | Login authentication, password hashing, secure SQL queries |

---

## 🎨 UI Screenshots

---

### 🏠 1️⃣ Landing Screen (index.php)

<p align="center">
  <img src="readimg/l1.png" width="45%" ><br>
  <img src="readimg/l2.png" width="45%" ><br>
  <img src="readimg/l3.png" width="45%" ><br>
</p>
---

### 🔐 2️⃣ Authentication Pages
#### Login & Register

<p align="center">
  <img src="readimg/l4.png" width="45%" style="border-radius:7px; margin-right:10px;">
  <img src="readimg/l5.png" width="45%" style="border-radius:7px;">
</p>

---

## 🧑‍💼 3️⃣ Admin Panel (/admin/)

### Main Dashboard
<p align="center">
  <img src="readimg/a1.png" width="70%" style="border-radius:7px;">
</p>

### Admin Tools
<p align="center">
  <img src="readimg/a2.png" width="45%" style="border-radius:7px; margin-right:10px;">
  <img src="readimg/a3.png" width="45%" style="border-radius:7px;">
</p>

<p align="center">
  <img src="readimg/a4.png" width="45%" style="border-radius:7px; margin-right:10px;">
  <img src="readimg/a5.png" width="45%" style="border-radius:7px;">
</p>

---

## 🧑‍🏫 4️⃣ Staff Panel (/staff/)

### Dashboard
<p align="center">
  <img src="readimg/f1.png" width="70%" style="border-radius:7px;">
</p>

### Class & Performance Management
<p align="center">
  <img src="readimg/f2.png" width="45%" style="border-radius:7px; margin-right:10px;">
  <img src="readimg/f3.png" width="45%" style="border-radius:7px;">
</p>

<p align="center">
  <img src="readimg/f4.png" width="45%" style="border-radius:7px; margin-right:10px;">
  <img src="readimg/f5.png" width="45%" style="border-radius:7px;">
</p>

<p align="center">
  <img src="readimg/f6.png" width="45%" style="border-radius:7px; margin-right:10px;">
  <img src="readimg/f7.png" width="45%" style="border-radius:7px;">
</p>

---

## 🎓 5️⃣ Student Panel (/student/)

### Dashboard
<p align="center">
  <img src="readimg/s1.png" width="70%" style="border-radius:7px;">
</p>

### Academic Features
<p align="center">
  <img src="readimg/s2.png" width="45%" style="border-radius:7px; margin-right:10px;">
  <img src="readimg/s3.png" width="45%" style="border-radius:7px;">
</p>

<p align="center">
  <img src="readimg/s4.png" width="45%" style="border-radius:7px; margin-right:10px;">
  <img src="readimg/s5.png" width="45%" style="border-radius:7px;">
</p>

<p align="center">
  <img src="readimg/s6.png" width="70%" style="border-radius:7px;">
</p>

---


# 🧩 Folder Structure<br>
Academic-Hub/<br>
│── index.php<br>
│── login.php<br>
│── register.php<br>
│── structure.txt<br>
│<br>
├── admin/           # Admin pages<br>
├── staff/           # Staff pages<br>
├── student/         # Student pages<br>
├── sapi/            # PHP API handlers<br>
├── connect/         # DB config & auth<br>
├── css/             # Stylesheets<br>
├── img/             # Images<br>

# 🚀 Tech Stack
Frontend:  HTML, CSS, JavaScript<br>
Backend:   PHP<br>
Database:  MySQL (phpMyAdmin)<br>
Runtime:   XAMPP (Apache + MySQL)<br>
Extra:     Ngrok (Public Hosting), PHPMailer (Email)<br>
<br>

# 🛠 Installation (Localhost)
1️⃣ Install XAMPP<br>
2️⃣ Move project folder → C:\xampp\htdocs\Academic-Hub\<br>
3️⃣ Start Apache + MySQL<br>
4️⃣ phpMyAdmin → Create DB: hub<br>
5️⃣ Import SQL → EmptyDB.sql<br>
6️⃣ Browser → http://localhost/Academic-Hub/<br><br>

# 🔐 Security Features

Password hashing using password_hash()<br>
Sessions & role-based authorization<br>
Prepared SQL statements (injection safe)<br>
No cross-role access<br><br>

# 🧱 Future Enhancements

📱 Mobile App (Android/iOS)<br>
🤖 AI marks prediction<br>
📨 Email/SMS alert system<br>
🌐 Multi-language dashboards<br>
💬 Chatbot assistant<br><br>

# 👥 Team
| Name                 | Role              |
| -------------------- | ----------------- |
| Mohd. Hussain Shaikh | Lead Developer/Backend Developer/ UI/UX    |
| Sharvil Raut         | UI Developer |
| Yug Bari             | esting/QA        |
| Sujal Champaneri     | Testing/QA        |
<br>


Faculty Guide – Prof. Mohammed Raza Baig<br>
Principal – Dr. Sayyad Layak

# ⭐ Support
⭐ Star this repository if it helped you!


---
