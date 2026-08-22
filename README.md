# 🌍 GlobeTrotter

> A smart travel planning and trip management web application built for the Odoo LDCE Hackathon 2026.

GlobeTrotter is a web-based travel planner that helps users create and manage trips, explore destinations, manage travel expenses, discover activities and tourist spots, and organize their travel plans from one place.

The project focuses on making travel planning simple, organized, and interactive.

---

## ✨ Features

### 🔐 Authentication
- User registration and login
- Session-based authentication
- Secure logout
- Protected pages for authenticated users
- User profile management

### 🧳 Trip Management
- Create new trips
- View existing trips
- View detailed trip information
- Manage trip stops
- Track planned destinations
- View trip details and itinerary

### 💰 Budget & Expenses
- Add travel expenses
- Categorize expenses
- Track expense amounts
- Associate expenses with trips
- View total expenses
- Store expense information in MySQL

### 📍 Destinations & Tourist Spots
- Explore cities
- View tourist spots
- Add tourist spots
- View tourist spot details
- Discover places to visit during a trip

### 🎯 Activities
- Browse available activities
- Add activities to travel plans
- Organize activities around trips

### ❤️ Favorites
- Save favorite destinations/tourist spots
- Remove saved favorites
- Manage favorite places

### 👤 Profile
- View user profile
- Edit profile information
- Manage personal travel information

### 👨‍💼 Admin Panel
- Admin dashboard
- Administrative management features
- Manage application-related data

### 🤝 Trip Collaboration
- Support for trip collaborators
- Share trip planning with other users

---

## 🛠️ Technologies Used

### Frontend
- HTML5
- CSS3
- JavaScript
- Lucide Icons
- Google Fonts

### Backend
- PHP
- PHP Sessions
- PDO

### Database
- MySQL
- phpMyAdmin

### Development Environment
- XAMPP
- Apache
- MySQL
- Visual Studio Code

### Version Control
- Git
- GitHub

---

## 📁 Project Structure

```text
GlobeTrotter/
│
├── admin/
│   └── Admin-related files
│
├── api/
│   └── API endpoints
│
├── .gitignore
├── SETUP.md
├── database.sql
├── db.php
│
├── index.php
├── index.html
├── homepage.php
│
├── login.php
├── signup.php
├── register.php
├── logout.php
│
├── dashboard.php
├── profile.php
├── profile.html
├── profile.css
├── edit-profile.php
│
├── create-trip.php
├── my-trips.php
├── trip-details.php
├── select-trip-stop.php
│
├── cities.php
├── tourist-spot.php
├── add-tourist-spot.php
├── activities.php
│
├── expenses.php
│
├── toggle-favorite.php
│
├── app.js
├── styles.css
├── auth.css
│
└── README.md
