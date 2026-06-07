-- UniEquip Database Schema
-- Database: if0_39265998_system (converted to local: UniEquip)

SET FOREIGN_KEY_CHECKS = 0;

-- =============================================
-- TABLE: user (Student Accounts)
-- =============================================
CREATE TABLE IF NOT EXISTS `user` (
  `stud_num` varchar(50) PRIMARY KEY NOT NULL,
  `stud_name` varchar(255) NOT NULL,
  `stud_pass` varchar(255) NOT NULL,
  `stud_tel` varchar(20) NOT NULL,
  `stud_email` varchar(255) UNIQUE NOT NULL,
  `course_code` varchar(100),
  `faculty` varchar(255),
  `date_created` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- TABLE: admin (Staff/Admin Accounts)
-- =============================================
CREATE TABLE IF NOT EXISTS `admin` (
  `staff_num` varchar(50) PRIMARY KEY NOT NULL,
  `staff_name` varchar(255) NOT NULL,
  `staff_password` varchar(255) NOT NULL,
  `staff_tel` varchar(20) NOT NULL,
  `staff_email` varchar(255) UNIQUE NOT NULL,
  `date_created` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- TABLE: equipment
-- =============================================
CREATE TABLE IF NOT EXISTS `equipment` (
  `id_equipment` int AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) UNIQUE NOT NULL,
  `category` varchar(100) NOT NULL,
  `status` varchar(50) DEFAULT 'Available' COMMENT 'Available or Unavailable',
  `qty` int NOT NULL DEFAULT 0,
  `model` varchar(255),
  `picture` varchar(500),
  `date_created` timestamp DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_category` (`category`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- TABLE: club
-- =============================================
CREATE TABLE IF NOT EXISTS `club` (
  `id_club` int AUTO_INCREMENT PRIMARY KEY,
  `club_name` varchar(255) UNIQUE NOT NULL,
  `type` varchar(100) NOT NULL,
  `adv_name` varchar(255) NOT NULL,
  `adv_tel` varchar(20) NOT NULL,
  `adv_email` varchar(255) NOT NULL,
  `adv_num` varchar(50) NOT NULL,
  `status` varchar(50) DEFAULT 'active' COMMENT 'active or inactive',
  `date_created` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- TABLE: booking
-- =============================================
CREATE TABLE IF NOT EXISTS `booking` (
  `id_booking` int AUTO_INCREMENT PRIMARY KEY,
  `stud_num` varchar(50) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `status` varchar(50) DEFAULT 'pending' COMMENT 'pending, approved, rejected, borrowed, returned',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `club_name` varchar(255) NOT NULL,
  `return_date` date,
  `date_created` timestamp DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`stud_num`) REFERENCES `user` (`stud_num`) ON DELETE CASCADE,
  INDEX `idx_status` (`status`),
  INDEX `idx_start_date` (`start_date`),
  INDEX `idx_end_date` (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- TABLE: booking_equipment (Many-to-Many: Booking & Equipment)
-- =============================================
CREATE TABLE IF NOT EXISTS `booking_equipment` (
  `id_booking_equipment` int AUTO_INCREMENT PRIMARY KEY,
  `id_equipment` int NOT NULL,
  `id_booking` int NOT NULL,
  `qty` int NOT NULL DEFAULT 1,
  `date_added` timestamp DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_equipment`) REFERENCES `equipment` (`id_equipment`) ON DELETE CASCADE,
  FOREIGN KEY (`id_booking`) REFERENCES `booking` (`id_booking`) ON DELETE CASCADE,
  UNIQUE KEY `unique_booking_equipment` (`id_equipment`, `id_booking`),
  INDEX `idx_id_booking` (`id_booking`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- SAMPLE DATA (Optional - for testing)
-- =============================================

-- Sample Students
INSERT INTO `user` (`stud_num`, `stud_name`, `stud_pass`, `stud_tel`, `stud_email`, `course_code`, `faculty`) VALUES
('S001', 'John Smith', 'password123', '0123456789', 'john.smith@student.edu', 'CS101', 'Engineering'),
('S002', 'Jane Doe', 'password456', '0198765432', 'jane.doe@student.edu', 'CS102', 'Engineering'),
('S003', 'Ahmed Hassan', 'password789', '0111234567', 'ahmed.hassan@student.edu', 'BUS201', 'Business');

-- Sample Admin/Staff
INSERT INTO `admin` (`staff_num`, `staff_name`, `staff_password`, `staff_tel`, `staff_email`) VALUES
('A001', 'Mr. Ahmad Rahman', 'admin123', '0123456789', 'ahmad@university.edu'),
('A002', 'Mrs. Siti Aminah', 'admin456', '0198765432', 'siti@university.edu');

-- Sample Equipment
INSERT INTO `equipment` (`name`, `category`, `status`, `qty`, `model`, `picture`) VALUES
('LCD Projector', 'Visual Equipment', 'Available', 1, 'Epson EB-X41', 'uploads/1750611268_projector.jpg'),
('Banquet Chair', 'Furniture & Seating', 'Available', 100, 'NULL', 'uploads/1750611916_banquet_chair.jpeg'),
('Folding Table', 'Furniture & Seating', 'Maintenance', 20, 'NULL', 'uploads/1750611972_folding_table.jpeg'),
('10x10 Canopy', 'Tents & Canopies', 'Available', 5, 'NULL', 'uploads/1750647137_X7-10x10-Blue.webp'),
('Bunting Stand', 'Signage & Display', 'Available', 6, 'NULL', 'uploads/1750647387_t-stand-768x829.png'),
('Roll-Up Banner Stand', 'Signage & Display', 'Available', 4, 'NULL', 'uploads/1750647478_Luxury_Roll_Up_Stand_1724677648.jpg'),
('Chafing Dish', 'Catering Equipment', 'Available', 10, 'NULL', 'uploads/1750612743_dish.png'),
('Beverage Dispenser', 'Catering Equipment', 'Available', 6, 'NULL', 'uploads/1750647637_s-H200.jpg'),
('Standing Fan', 'Climate Control', 'Available', 12, 'NULL', 'uploads/1750611374_fan.jpeg'),
('Equipment Trolley', 'Transportation & Storage', 'Available', 4, 'NULL', 'uploads/1750612681_trolley.png'),
('Speaker 1.0', 'Audio Equipment', 'Available', 1, 'Havit SQ133BT 4', 'uploads/1750612439_havit.jpg'),
('Speaker 3.0', 'Audio Equipment', 'Maintenance', 2, 'MIPRO XL', 'uploads/1750612425_mipro.jpg'),
('Portable Aircond', 'Climate Control', 'Available', 1, 'DAIKIN', 'uploads/1750612568_portable_aircond.png'),
('Round Table', 'Furniture & Seating', 'Available', 10, 'NULL', 'uploads/1750648032_Lorell-Banquet-Folding-Table-5b.jpg'),
('Rostrum', 'Stage Equipment', 'Available', 2, 'NULL', 'uploads/eqp_6858c65de49316.82377521.jpg'),
('Coffee Table', 'Furniture & Seating', 'Available', 2, 'NULL', 'uploads/eqp_6858c716abb3a3.16918709.jpg'),
('Plastic Chair', 'Furniture & Seating', 'Available', 100, 'NULL', 'uploads/eqp_6859304e6f1759.52738448.jpg'),
('Sofa', 'Furniture & Seating', 'Available', 10, 'NULL', 'uploads/eqp_685931a49aec84.85509127.jpg'),
('Mini Stage 4'' x 4''', 'Stage Equipment', 'Available', 20, 'NULL', 'uploads/eqp_6859322515688.97805411.png'),
('Wired Microphone', 'Audio Equipment', 'Available', 2, 'Shure SM58', 'uploads/eqp_685932a03870c0.96299537.jpeg'),
('Microphone Stand', 'Audio Equipment', 'Available', 2, 'NULL', 'uploads/eqp_685933596aadd9.87567624.jpg');

-- Sample Clubs
INSERT INTO `club` (`club_name`, `type`, `adv_name`, `adv_tel`, `adv_email`, `adv_num`, `status`) VALUES
('DIPLOMA SAINS KOMPUTER (DISK)', 'Close', 'Pn. Lily', '0136103842', 'lily564@gmail.com', 'A35', 'Active'),
('AL-BIRUNI ASTRONOMY CLUB (AAC)', 'Open', 'En. Afuan', '0107461953', 'afuan100@gmail.com', 'A94', 'active'),
('ALPHA', 'Close', 'Pn. Amira', '0123580659', 'amira123@gmail.com', 'A15', 'Active'),
('APPLIED SCIENCE TAPAH (ASET)', 'Close', 'Ms. Azura', '0185129467', 'azura78@gmail.com', 'A90', 'Active'),
('BACHELOR OF COMPUTER SCIENCE SOCIETY (BASCO)', 'Close', 'Pn. Itaza Mohtar', '018-9988776', 'liyana.musa@example.com', 'A020', 'Active'),
('BADAN KESENIAN DAN KEBUDAYAAN (DANSENI)', 'Open', 'Ms. Fatimah', '0149256083', 'fatimah349@gmail.com', 'A87', 'Active'),
('BADAN PEER KAUNSELOR (PEERS)', 'Open', 'En. Azdi', '0127365408', 'azdi125@gmail.com', 'A56', 'Active'),
('BETA', 'Close', 'En. Zul', '0103859172', 'zul@gmail.com', 'A69', 'active'),
('BRIGED SUKARELAWAN MAHASISWA (BRISUMA)', 'Open', 'En. Amirul', '0107325849', 'amirul56@gmail.com', 'A23', 'Active'),
('DIPLOMA PERAKAUNAN SISTEM MAKLUMAT (PERDAIS)', 'Close', 'Pn. Siti', '0164298053', 'siti656@gmail.com', 'A76', 'Active'),
('FASILITATOR (U-FASI)', 'Open', 'Pn. Asma', '0138504917', 'asma900@gmail.com', 'A33', 'Active'),
('GAMMA', 'Close', 'En. Rizal', '0117295340', 'rizal90@gmail.com', 'A49', 'active'),
('GERAKAN PENGGUNA SISWA (GPS)', 'Open', 'En. Alif', '0197846501', 'alif567@gmail.com', 'A45', 'Active'),
('IKATAN MAHASISWA DINAMIK (IMAD)', 'Open', 'En. Zaki', '0104957281', 'zaki1@gmail.com', 'A23', 'Active'),
('KELANASISWA MALAYSIA (KSM)', 'Open', 'En. Shahril', '0189512763', 'shahril888@gmail.com', 'A70', 'active'),
('NON-RESIDENCES', 'Close', 'En. Syed', '0126394875', 'syed001@gmail.com', 'A91', 'active'),
('PERSATUAN TAEKWONDO TAPAH (PTT)', 'Open', 'Ms. Alia', '0113048276', 'alia8900@gmail.com', 'A65', 'Active'),
('PERTAHANAN AWAM (PERSPA)', 'Open', 'En. Haziq', '0194837025', 'haziqqqq@gmail.com', 'A34', 'Active'),
('PROGRAM SISWA SIHAT (PROSIS)', 'Open', 'Pn. Insyirah', '0176921358', 'insyirah354@gmail.com', 'A36', 'Active'),
('RAKAN SISWA YADIM (RSY)', 'Open', 'En. Afif', '0113951210', 'afif455@gmail.com', 'A67', 'Active'),
('SEKRETARIAT RUKUN NEGARA (SRN)', 'Open', 'Pn. Samsiah', '0173208496', 'samsiah@gmail.com', 'A12', 'active'),
('SENI SILAT CEKAK MALAYSIA (PSSCM)', 'Open', 'En. Jamal', '0125783492', 'jamal@gmail.com', 'A40', 'Active'),
('SENI SILAT LINCAH MALAYSIA (PSSLM)', 'Open', 'En. Mahdi', '0136908247', 'mahdi@gmail.com', 'A81', 'Active'),
('SISWA SISWI DIPLOMA PERAKAUNAN (PERSIDA)', 'Close', 'Pn. Iman', '0173672984', 'iman67@gmail.com', 'A77', 'Active'),
('SISWA SISWI STATISTIK (PESISTA)', 'Close', 'Pn. Mimi', '0148539721', 'mimi@gmail.com', 'A09', 'Active');
