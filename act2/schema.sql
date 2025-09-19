CREATE DATABASE IF NOT EXISTS attendance_db;

USE attendance_db;

CREATE TABLE IF NOT EXISTS users (
id int(11) NOT NULL AUTO_INCREMENT,
name varchar(255) NOT NULL,
student_id varchar(50) NOT NULL,
role enum('student','admin') NOT NULL,
PRIMARY KEY (id),
UNIQUE KEY student_id (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS courses (
id int(11) NOT NULL AUTO_INCREMENT,
name varchar(255) NOT NULL,
year_level int(11) NOT NULL,
PRIMARY KEY (id),
UNIQUE KEY name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS attendance (
id int(11) NOT NULL AUTO_INCREMENT,
student_id int(11) NOT NULL,
course_id int(11) NOT NULL,
date date NOT NULL,
time time NOT NULL,
is_late tinyint(1) NOT NULL DEFAULT 0,
PRIMARY KEY (id),
UNIQUE KEY student_attendance (student_id,course_id,date),
KEY course_id (course_id),
CONSTRAINT attendance_ibfk_1 FOREIGN KEY (student_id) REFERENCES users (id) ON DELETE CASCADE,
CONSTRAINT attendance_ibfk_2 FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS excuse_letters (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    course_id INT(11) NOT NULL,
    absence_date DATE NOT NULL,
    reason TEXT NOT NULL,
    file_path VARCHAR(255) NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
