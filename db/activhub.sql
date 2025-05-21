-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2025 at 03:16 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `activhub`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `uname_admin` varchar(255) NOT NULL,
  `pass_admin` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`uname_admin`, `pass_admin`) VALUES
('administrator', '$2y$10$y8xMdeCs/6l9TCtFLpnNluul8JqNg7u/vJH3B5p1vXWFkpp/0Wg0i');

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE `class` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`class_id`, `class_name`) VALUES
(2, '1 Al-Farabi'),
(3, '2 Al-Battani'),
(5, '2 Al-Hafiz'),
(6, '1 Al-Arabi');

-- --------------------------------------------------------

--
-- Table structure for table `cocurricular`
--

CREATE TABLE `cocurricular` (
  `student_ic` varchar(20) NOT NULL,
  `cocu_year` varchar(4) NOT NULL,
  `uniform_bodies` varchar(100) DEFAULT NULL,
  `uniform_bodies_role` varchar(100) DEFAULT NULL,
  `sports` varchar(100) DEFAULT NULL,
  `sports_role` varchar(100) DEFAULT NULL,
  `clubs_assoc` varchar(100) DEFAULT NULL,
  `clubs_assoc_role` varchar(100) DEFAULT NULL,
  `activity_others` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cocurricular`
--

INSERT INTO `cocurricular` (`student_ic`, `cocu_year`, `uniform_bodies`, `uniform_bodies_role`, `sports`, `sports_role`, `clubs_assoc`, `clubs_assoc_role`, `activity_others`) VALUES
('160406028234', '2025', 'PBSM', 'Ahli Biasa', 'Badminton', 'Presiden', 'Kelab Alam Sekitar', 'Setiausaha', 'Pengawas Sekolah');

-- --------------------------------------------------------

--
-- Table structure for table `cocurricular_groups`
--

CREATE TABLE `cocurricular_groups` (
  `group_id` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `group_type` enum('uniform_bodies','sports','clubs_associations','others') NOT NULL,
  `group_description` text DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `advisor_name` varchar(100) DEFAULT NULL,
  `advisor_ic` varchar(20) DEFAULT NULL,
  `president_ic` varchar(20) DEFAULT NULL,
  `vice_president_ic` varchar(20) DEFAULT NULL,
  `secretary_ic` varchar(20) DEFAULT NULL,
  `treasurer_ic` varchar(20) DEFAULT NULL,
  `total_members` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cocurricular_groups`
--

INSERT INTO `cocurricular_groups` (`group_id`, `group_name`, `group_type`, `group_description`, `logo_path`, `advisor_name`, `advisor_ic`, `president_ic`, `vice_president_ic`, `secretary_ic`, `treasurer_ic`, `total_members`) VALUES
(1, 'Red Crescent Youth', 'uniform_bodies', NULL, 'logos/red_crescent.png', 'SITI NUR AISYAH BINTI AIMAN', '910711028452', '05010101004', '05010101014', '05010101005', '05010101002', 30),
(2, 'Football Team', 'sports', NULL, 'logos/football.png', 'AIMAN MISKIN BIN ABU', '800811023984', '05010101006', '05010101011', '05010101015', '05010101003', 25),
(3, 'Photography Club', 'clubs_associations', 'A club for students interested in photography and visual storytelling.', 'logos/photography.png', 'ONG LIN', '780513503890', '05010101008', '05010101007', '05010101016', '05010101001', 15),
(4, 'Prefects Guild', 'others', NULL, 'logos/prefects.png', 'MUALLIM WAN BIN ABU BAKAR', '770809147765', '05010101012', '05010101013', '05010101009', '05010101010', 40),
(6, 'Silat', 'sports', 'Mengajar murid-murid silat.', 'logos/1747660443_Silat.png', 'Encik Syed', '4235351616', '16164364616', '146143636161', '16146666346136', '16436666116', 52);

-- --------------------------------------------------------

--
-- Table structure for table `cocu_activities`
--

CREATE TABLE `cocu_activities` (
  `id` int(11) NOT NULL,
  `student_ic` varchar(20) NOT NULL,
  `activity_name` varchar(255) NOT NULL,
  `activity_category` varchar(100) DEFAULT NULL,
  `activity_date` date DEFAULT NULL,
  `award` varchar(255) DEFAULT NULL,
  `activity_location` varchar(255) DEFAULT NULL,
  `org` varchar(255) DEFAULT NULL,
  `cert_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ach` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cocu_activities`
--

INSERT INTO `cocu_activities` (`id`, `student_ic`, `activity_name`, `activity_category`, `activity_date`, `award`, `activity_location`, `org`, `cert_path`, `created_at`, `ach`) VALUES
(1, '160406028234', 'Melawat Zoo', 'Lawatan', '2025-05-12', 'Penyertaan', 'Zoo Negara', 'SRIAAWP', 'uploads/certificates/zoo.pdf', '2025-05-19 10:49:01', NULL),
(2, '160406028234', 'eweeq', 'eqwewqe', '2025-05-28', 'eqeqe', 'ewqe', 'eweqe', 'uploads/certificates/zoo.pdf', '2025-05-19 10:49:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_ic` varchar(255) NOT NULL,
  `student_pass` varchar(255) NOT NULL,
  `student_fname` text NOT NULL,
  `student_class` int(11) NOT NULL,
  `student_dob` date DEFAULT NULL,
  `student_doe` date DEFAULT NULL,
  `student_address` text DEFAULT NULL,
  `student_emergency` varchar(255) DEFAULT NULL,
  `guardian_ic` varchar(255) DEFAULT NULL,
  `guardian_name` text DEFAULT NULL,
  `relationship` text DEFAULT NULL,
  `guardian_address` text DEFAULT NULL,
  `contact_num` varchar(255) DEFAULT NULL,
  `teacher_incharge` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_ic`, `student_pass`, `student_fname`, `student_class`, `student_dob`, `student_doe`, `student_address`, `student_emergency`, `guardian_ic`, `guardian_name`, `relationship`, `guardian_address`, `contact_num`, `teacher_incharge`) VALUES
('160406028234', '$2y$10$weFCXO2tRx7R1PRNNxozmu812GupTehK/Hpyauqc//9H5Rn905oie', 'Mimi Liyana Bint Muhammad Arif', 3, '2016-04-06', '2025-02-17', 'Kuala Lumpur', '0196530274', '900101029831', 'Muhammad Arif Bin Syukri', 'Father', 'Kuala Lumpur', '0165432261', '770809147765');

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `teacher_ic` varchar(255) NOT NULL,
  `teacher_pass` varchar(255) NOT NULL,
  `teacher_fname` text NOT NULL,
  `teacher_contact` varchar(255) NOT NULL,
  `teacher_email` varchar(255) DEFAULT NULL,
  `teacher_dob` date DEFAULT NULL,
  `teacher_doe` date DEFAULT NULL,
  `teacher_address` text DEFAULT NULL,
  `class` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`teacher_ic`, `teacher_pass`, `teacher_fname`, `teacher_contact`, `teacher_email`, `teacher_dob`, `teacher_doe`, `teacher_address`, `class`) VALUES
('770809147765', '$2y$10$y3qgTGE/PPs6wEfMc7KuueO3ih0aSmrsacImDct4MzYOGjEA2HZ4m', 'MUALLIM WAN BIN ABU BAKAR', '0132658897', 'muallimwan@gmail.com', '1977-08-09', '2012-02-01', '21st Floor Plaza Sentral Block C', 3),
('780513503890', '$2y$10$HBdnmEBVZqiUGf3VcRjjWe6np7Dikh4tHvZ6HVf52hcIm7xAJP1sa', 'ONG LIN', '0182331874', NULL, NULL, NULL, '', 3),
('800811023984', '$2y$10$JSMcFkpvYvQ8Lc4rPfOJcOugdaQrVDpKj0QxgSaNXI.GijvDV9OtW', 'AIMAN MISKIN BIN ABU', '0165320012', NULL, NULL, NULL, '', 5),
('910711028452', '$2y$10$H8RmUT21kjqekcIIy/IzY.4EKYLfEX3dGTKzz8wBEtiuQiMAHXTju', 'SITI NUR AISYAH BINTI AIMAN', '0196547821', NULL, NULL, NULL, '', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`uname_admin`);

--
-- Indexes for table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`class_id`);

--
-- Indexes for table `cocurricular`
--
ALTER TABLE `cocurricular`
  ADD PRIMARY KEY (`student_ic`,`cocu_year`);

--
-- Indexes for table `cocurricular_groups`
--
ALTER TABLE `cocurricular_groups`
  ADD PRIMARY KEY (`group_id`);

--
-- Indexes for table `cocu_activities`
--
ALTER TABLE `cocu_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_ic` (`student_ic`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_ic`),
  ADD KEY `student_class` (`student_class`),
  ADD KEY `teacher_incharge` (`teacher_incharge`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`teacher_ic`),
  ADD KEY `class` (`class`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cocurricular_groups`
--
ALTER TABLE `cocurricular_groups`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cocu_activities`
--
ALTER TABLE `cocu_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cocurricular`
--
ALTER TABLE `cocurricular`
  ADD CONSTRAINT `cocurricular_ibfk_1` FOREIGN KEY (`student_ic`) REFERENCES `student` (`student_ic`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cocu_activities`
--
ALTER TABLE `cocu_activities`
  ADD CONSTRAINT `cocu_activities_ibfk_1` FOREIGN KEY (`student_ic`) REFERENCES `student` (`student_ic`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`student_class`) REFERENCES `class` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_ibfk_2` FOREIGN KEY (`teacher_incharge`) REFERENCES `teacher` (`teacher_ic`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teacher`
--
ALTER TABLE `teacher`
  ADD CONSTRAINT `teacher_ibfk_1` FOREIGN KEY (`class`) REFERENCES `class` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
