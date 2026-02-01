# Project Setup Guide

This repository contains a demo / tutorial project with **no pre-filled data**.
You must configure your **own database** and **insert your own data** to run the project.

---

## ⚠️ Important Notice

- This project does **NOT** include a ready-to-use database
- A **blank database** is provided (structure only)
- You must **create your own database**
- You must **insert your own data**
- This project is **NOT FREE for commercial use**

---

## 🛠 Requirements

- Web Server (XAMPP / WAMP / LAMP)
- PHP 7.4+
- MySQL / MariaDB
- Web Browser
- Basic programming knowledge

---

## 📦 Project Setup Steps

### 1️⃣ Download or Clone Repository

git clone <your-repository-url>

OR download ZIP and extract.

---

### 2️⃣ Create Your Own Database

Open phpMyAdmin and create a database:

CREATE DATABASE your_database_name;

---

### 3️⃣ Import Database Structure

- Import the provided `.sql` file
- Tables only, **no data included**

---

### 4️⃣ Update Database Configuration

Edit database connection file:

$host = "localhost";
$user = "your_username";
$password = "your_password";
$database = "your_database_name";

Default credentials will not work.

---

### 5️⃣ Insert Your Own Data

- Insert data manually via phpMyAdmin
- OR write your own insert scripts
- Project will not work without data

---

## 🚀 Run the Project

- Move project folder to `htdocs`
- Start Apache & MySQL
- Open browser:

http://localhost/project-folder-name

---

## 📌 Notes

- This project is for **learning and practice**
- Code logic is kept simple
- Modify according to your needs
- No guarantee of technical support

---

## 🔒 License

All Rights Reserved

- ❌ Commercial use not allowed
- ❌ Redistribution not allowed
- ❌ Reselling not allowed
- ✅ Personal learning allowed

---

## 👨‍💻 Author

Your Name
