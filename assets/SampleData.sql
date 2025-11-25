-- Use the CollegeLibrary Database
USE CollegeLibrary;

-- Insert Sample Data into User Table
INSERT INTO User (user_Id, password, role) VALUES
('14004240001', 'admin123', 'Admin'),
('14004240002', 'staff456', 'Staff'),
('14004240003', 'admin789', 'Admin'),
('14004240004', 'staff101', 'Staff');

-- Insert Sample Data into Student Table
INSERT INTO Student (Student_Id, Name, Year, Department, Email, Phone_Number, DOB) VALUES
('14004230001', 'Amit Sharma', 'SY', 'Information Technology', 'amit.sharma@email.com', '9876543210', '2002-05-15'),
('14004230002', 'Priya Patel', 'TY', 'Information Technology', 'priya.patel@email.com', '8765432109', '2001-08-22'),
('14004230003', 'Rahul Verma', 'SY', 'Information Technology', 'rahul.verma@email.com', '7654321098', '2002-11-30'),
('14004230004', 'Sneha Gupta', 'TY', 'Information Technology', 'sneha.gupta@email.com', '6543210987', '2001-03-10');

-- Insert Sample Data into Book Table
INSERT INTO Book (Book_Id, Name, Category, Publisher, ISBN, Author, Price) VALUES
('B001', 'Introduction to Python Programming', 'Information Technology', 'BPB Publications', '9789386551931', 'Reema Thareja', 499.00),
('B002', 'Theory of Machines', 'Mechanical', 'S. Chand Publishing', '9788121925242', 'S.S. Rattan', 650.00),
('B003', 'Engineering Mathematics', 'Mathematics', 'S. Chand Publishing', '9788121900393', 'B.S. Grewal', 799.00),
('B004', 'Ikigai: The Japanese Secret to a Long and Happy Life', 'Self-Help', 'Penguin Random House', '9781786330895', 'Héctor García', 599.00);

-- Insert Sample Data into Report Table
INSERT INTO Report (Student_Id, Book_Id, Issue_Date, Return_Date, Due_Date, Status) VALUES
('14004230001', 'B001', '2025-07-01', NULL, '2025-07-15', 'Not Returned'),
('14004230002', 'B001', '2025-07-05', '2025-07-12', '2025-07-19', 'Returned'),
('14004230003', 'B003', '2025-07-10', NULL, '2025-07-24', 'Not Returned'),
('14004230004', 'B004', '2025-07-15', '2025-07-20', '2025-07-29', 'Returned');