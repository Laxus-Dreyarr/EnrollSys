# EnrollSys - EVSU Online Enrollment System

<div align="center">

[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)](https://www.mysql.com/)
![Status](https://img.shields.io/badge/Status-In%20Development-orange)

</div>

A web-based online enrollment system designed specifically for the Computer Studies Department of Eastern Visayas State University (EVSU) - Ormoc City Campus. This project aims to digitize and streamline the enrollment process for students and administrators.

---

## ✨ Features

### For Students
-   **User Authentication** - Secure login and registration for students.
-   **Course Catalog** - View available courses/subjects for the semester.
-   **Online Enrollment** - Select and enroll in desired subjects.
-   **Class Schedule** - View and manage personal class schedule.

### For Administrators/Faculty
-   **Dashboard** - Overview of enrollment statistics.
-   **Student Management** - View, add, edit, and manage student records.
-   **Course/Subject Management** - CRUD operations for courses/subjects.
-   **Section Management** - Create and assign class sections.
-   **Enrollment Approval** - Process and approve student enrollment requests.
-   **Report Generation** - Generate reports for enrollment data.

*(More features to be added as development progresses)*

---

## 🏗️ System Architecture & Technology Stack

-   **Frontend:** HTML, CSS, JavaScript, Bootstrap
-   **Backend:** PHP Laravel
-   **Database:** MySQL
-   **Web Server:** XAMPP / WAMP / LAMP
-   **Version Control:** Git & GitHub

---

## 🚀 Installation & Setup

To run this project locally, follow these steps:

1.  **Prerequisites:**
    -   Install [XAMPP](https://www.apachefriends.org/) or similar (WAMP, LAMP).

2.  **Clone the repository:**
    ```bash
    git clone https://github.com/Laxus-Dreyarr/EnrollSys.git
    ```

3.  **Setup:**
    -   Move the cloned folder to your server's root directory (e.g., `xampp/htdocs/`).
    -   Start Apache and MySQL modules from your XAMPP Control Panel.

4.  **Database:**
    -   Open phpMyAdmin (`http://localhost/phpmyadmin`).
    -   Create a new database named `enrollsys_db`.
    -   Import the SQL file located in the project's `database/` folder (if provided).

5.  **Configuration:**
    -   Update the database connection settings in `/includes/config.php` with your credentials.

6.  **Run:**
    -   Open your browser and go to `http://localhost/EnrollSys`.

---

## 📁 Project Structure



---

## 🤝 Contributing

This is an ongoing project for academic purposes. Contributions, suggestions, and bug reports are welcome! Feel free to fork this project and submit a pull request.

1.  Fork the Project
2.  Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3.  Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4.  Push to the Branch (`git push origin feature/AmazingFeature`)
5.  Open a Pull Request

---

## 📜 License

This project is licensed under a **Non-Commercial Use License** - see the [![License: Non-Commercial](https://img.shields.io/badge/License-Non--Commercial-blue.svg)](LICENSE) file for details. It is developed as an academic requirement for Eastern Visayas State University.
- **You may:** Use, copy, modify, and distribute the software for **non-commercial purposes** (like learning and development).
- **You may not:** Sell or distribute the software for **commercial purposes** without the express written permission of the copyright holder (Carl James P. Duallo).

---

## 👨‍💻 Developer

**Carl James P. Duallo**
-   GitHub: [@Laxus-Dreyarr](https://github.com/Laxus-Dreyarr)
-   Institution: Eastern Visayas State University - Ormoc City Campus
-   Course: Bachelor of Science in Information Technology (BSIT)

---

> **Note:** This system is a capstone project and is currently under development. Features and structure are subject to change.
