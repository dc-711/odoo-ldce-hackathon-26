CREATE DATABASE IF NOT EXISTS globetrotter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE globetrotter;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE trips (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  destination VARCHAR(160) DEFAULT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_trips_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE stops (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  trip_id INT UNSIGNED NOT NULL,
  city VARCHAR(120) NOT NULL,
  country VARCHAR(120) DEFAULT NULL,
  arrival_date DATE DEFAULT NULL,
  departure_date DATE DEFAULT NULL,
  position INT UNSIGNED DEFAULT 0,
  CONSTRAINT fk_stops_trip FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE
);

CREATE TABLE activities (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  stop_id INT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  activity_type VARCHAR(80) DEFAULT NULL,
  estimated_cost DECIMAL(10,2) DEFAULT 0,
  start_time TIME DEFAULT NULL,
  notes TEXT,
  CONSTRAINT fk_activities_stop FOREIGN KEY (stop_id) REFERENCES stops(id) ON DELETE CASCADE
);

CREATE TABLE expenses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  trip_id INT UNSIGNED NOT NULL,
  category ENUM('transport', 'stay', 'activities', 'meals', 'other') NOT NULL,
  label VARCHAR(160) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_expenses_trip FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE
);

INSERT INTO users (name, email, password_hash)
VALUES ('Alex Morgan', 'alex@example.com', '$2y$10$g97Z6n/Q1pCByUXf1dYWhulmUqcnhciezbX88taxx35GNRPwmE.RC');

INSERT INTO users (name, email, password_hash, role)
VALUES ('GlobeTrotter Admin', 'admin@globetrotter.local', '$2y$10$aPTbDTXJuCh9yTcjDt5sNumA1UrT.XqnAUc0qU8FI0Kn4aeHp/LlW', 'admin');

INSERT INTO trips (user_id, name, start_date, end_date, destination, description)
VALUES (1, 'La dolce vita', '2026-10-03', '2026-10-11', 'Amalfi Coast, Italy', 'A slow journey along the coast.');
