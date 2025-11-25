-- Create the Database
CREATE DATABASE IF NOT EXISTS CollegeLibrary;

-- Use the Database
USE CollegeLibrary;

-- Create User Table
CREATE TABLE IF NOT EXISTS User (
    user_Id VARCHAR(50) PRIMARY KEY,
    password VARCHAR(50) NOT NULL,
    role ENUM('Admin', 'Staff') NOT NULL
);

-- Create Student Table
CREATE TABLE IF NOT EXISTS Student (
    Student_Id VARCHAR(50) PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Year VARCHAR(10),
    Department VARCHAR(50),
    Email VARCHAR(100) NOT NULL,
    Phone_Number VARCHAR(15) NOT NULL,
    DOB DATE
);

-- Create Book Table
CREATE TABLE IF NOT EXISTS Book (
    Book_Id VARCHAR(50) PRIMARY KEY,
    Name VARCHAR(200) NOT NULL,
    Category VARCHAR(50) NOT NULL,
    Publisher VARCHAR(100),
    ISBN VARCHAR(13),
    Author VARCHAR(100) NOT NULL,
    Price DECIMAL(10,2)
);

-- Create Report Table
CREATE TABLE IF NOT EXISTS Report (
    Reg_no INT AUTO_INCREMENT PRIMARY KEY,
    Student_Id VARCHAR(50),
    Book_Id VARCHAR(50),
    Issue_Date DATE NOT NULL,
    Return_Date DATE DEFAULT NULL,
    Due_Date DATE NOT NULL,
    Status ENUM('Not Returned', 'Returned') DEFAULT 'Not Returned',
    FOREIGN KEY (Student_Id) REFERENCES Student(Student_Id),
    FOREIGN KEY (Book_Id) REFERENCES Book(Book_Id)
);