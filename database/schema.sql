
CREATE DATABASE barangay_health_db;

USE barangay_health_db;

-- 1. Residents
CREATE TABLE residents (
    resident_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    birth_date DATE,
    sex ENUM('Male', 'Female') NOT NULL,
    address VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Health Programs
CREATE TABLE programs (
    program_id INT AUTO_INCREMENT PRIMARY KEY,
    program_name VARCHAR(150) NOT NULL,
    description TEXT,
    program_type VARCHAR(100),
    venue VARCHAR(255),
    start_date DATETIME NOT NULL,
    end_date DATETIME,
    program_status ENUM(
        'Upcoming',
        'Ongoing',
        'Completed',
        'Cancelled'
    ) DEFAULT 'Upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Program Registrations
CREATE TABLE registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    program_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    attendance_status ENUM(
        'Registered',
        'Attended',
        'Absent',
        'Cancelled'
    ) DEFAULT 'Registered',

    FOREIGN KEY (resident_id)
        REFERENCES residents(resident_id),

    FOREIGN KEY (program_id)
        REFERENCES programs(program_id),

    UNIQUE (resident_id, program_id)
);

-- 4. Service Records
CREATE TABLE service_records (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL,
    service_description TEXT NOT NULL,
    service_date DATETIME NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (registration_id)
        REFERENCES registrations(registration_id)
);

-- 5. System Users
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Staff', 'BHW') DEFAULT 'Staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Consultations & Appointments
CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    appointment_date DATETIME NOT NULL,
    purpose VARCHAR(255) NOT NULL,
    physician_name VARCHAR(150) DEFAULT 'Dr. Maria Ramos',
    status ENUM('Scheduled', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(resident_id)
);

-- 7. Supplies & Medicine Inventory
CREATE TABLE inventory (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(150) NOT NULL,
    category VARCHAR(100) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    unit VARCHAR(50) NOT NULL,
    stock_status ENUM('Adequate', 'Low Stock', 'Out of Stock') DEFAULT 'Adequate',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
