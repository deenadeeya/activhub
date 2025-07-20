-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 20, 2025 at 08:29 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
('administrator', '$2y$10$y8xMdeCs/6l9TCtFLpnNluul8JqNg7u/vJH3B5p1vXWFkpp/0Wg0i'),
('ling', '$2y$10$atJiteCbJY7YOVHQrf4eA.tLpTizt6GYPHEVN7GGfu43lnM//nDBC');

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE `class` (
  `class_id` int(11) NOT NULL,
  `class_year` int(11) NOT NULL,
  `class_name` varchar(255) NOT NULL,
  `head_teacher` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`class_id`, `class_year`, `class_name`, `head_teacher`) VALUES
(1, 4, '4 Nilam', '770809147765'),
(2, 4, '4 Emas', '800811023984'),
(3, 5, '5 Nilam', '780513503890');

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
  `vice_secretary_ic` varchar(20) DEFAULT NULL,
  `vice_treasurer_ic` varchar(20) DEFAULT NULL,
  `exco_y6_ic` varchar(20) DEFAULT NULL,
  `exco_y5_ic` varchar(20) DEFAULT NULL,
  `exco_y4_ic` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cocurricular_groups`
--

INSERT INTO `cocurricular_groups` (`group_id`, `group_name`, `group_type`, `group_description`, `logo_path`, `advisor_name`, `advisor_ic`, `president_ic`, `vice_president_ic`, `secretary_ic`, `treasurer_ic`, `vice_secretary_ic`, `vice_treasurer_ic`, `exco_y6_ic`, `exco_y5_ic`, `exco_y4_ic`) VALUES
(3, 'Kelab Fotografi', 'clubs_associations', 'A club for students interested in photography and visual storytelling.', 'logos/photography.png', 'ONG LIN', '780513503890', '05010101008', '05010101007', '05010101016', '05010101001', NULL, NULL, NULL, NULL, NULL),
(6, 'Silat', 'sports', 'Mengajar murid-murid silat.', 'logos/1747660443_Silat.png', 'Encik Syed', '4235351616', NULL, '170101010101', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 'Bola Sepak', 'sports', 'Kelab Bola Sepak SRIAAWP', 'logos/1748274676_football.png', 'ENCIK AZMAN', '840404040404', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(33, 'Tunas Kadet Remaja Sekolah', 'uniform_bodies', 'Melatih murid-murid dalam latihan TKRS.', '../assets/logos/1753032657_tkrs.png', 'Wan Bin Abu Bakar', '910711-02-xxxx', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(34, 'Pengawas Sekolah', 'others', 'Menjaga ketenteraman sekolah.', '../assets/logos/1753032772_Club Logo.png', 'Siti Nur Aisyah Binti Aiman', '840518-04-xxxx', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(35, 'Persatuan Matematik dan Sains', 'clubs_associations', 'Melatih minat murid dalam STEM.', '../assets/logos/1753032686_sainsmath.png', 'Syed Abdullah', '850651-02-xxxx', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(36, 'Pengawas Perpustakaan', 'others', 'Menjaga Perpustakaan Sekolah', '../assets/logos/1753032785_Club Logo.png', 'Nurlia Binti Rasyid', '020361-10-xxxx', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(37, 'Memanah', 'sports', 'Kelab Memanah SRIAAWP', '../assets/logos/1753032672_memanah.png', 'Anis Zulaikha Binti Hamid', '990371-12-xxxx', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(38, 'Persatuan Bahasa Arab', 'clubs_associations', 'Melatih minat murid-murid dalam Bahasa Arab', '../assets/logos/1753032756_Club Logo.png', 'Wan Ali Bin Syed', '840518-04-xxxx', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Triggers `cocurricular_groups`
--
DELIMITER $$
CREATE TRIGGER `trg_cocurricular_groups_after_insert` AFTER INSERT ON `cocurricular_groups` FOR EACH ROW BEGIN
  DECLARE pos_ic VARCHAR(20);

  -- President
  IF NEW.president_ic IS NOT NULL THEN
    INSERT IGNORE INTO student_club_membership (student_ic, group_id, membership_role)
    VALUES (NEW.president_ic, NEW.group_id, 'president');
  END IF;

  -- Vice President
  IF NEW.vice_president_ic IS NOT NULL THEN
    INSERT IGNORE INTO student_club_membership (student_ic, group_id, membership_role)
    VALUES (NEW.vice_president_ic, NEW.group_id, 'vice_president');
  END IF;

  -- Secretary
  IF NEW.secretary_ic IS NOT NULL THEN
    INSERT IGNORE INTO student_club_membership (student_ic, group_id, membership_role)
    VALUES (NEW.secretary_ic, NEW.group_id, 'secretary');
  END IF;

  -- Vice Secretary
  IF NEW.vice_secretary_ic IS NOT NULL THEN
    INSERT IGNORE INTO student_club_membership (student_ic, group_id, membership_role)
    VALUES (NEW.vice_secretary_ic, NEW.group_id, 'vice_secretary');
  END IF;

  -- Treasurer
  IF NEW.treasurer_ic IS NOT NULL THEN
    INSERT IGNORE INTO student_club_membership (student_ic, group_id, membership_role)
    VALUES (NEW.treasurer_ic, NEW.group_id, 'treasurer');
  END IF;

  -- Vice Treasurer
  IF NEW.vice_treasurer_ic IS NOT NULL THEN
    INSERT IGNORE INTO student_club_membership (student_ic, group_id, membership_role)
    VALUES (NEW.vice_treasurer_ic, NEW.group_id, 'vice_treasurer');
  END IF;

  -- EXCO Year 6
  IF NEW.exco_y6_ic IS NOT NULL THEN
    INSERT IGNORE INTO student_club_membership (student_ic, group_id, membership_role)
    VALUES (NEW.exco_y6_ic, NEW.group_id, 'exco_y6');
  END IF;

  -- EXCO Year 5
  IF NEW.exco_y5_ic IS NOT NULL THEN
    INSERT IGNORE INTO student_club_membership (student_ic, group_id, membership_role)
    VALUES (NEW.exco_y5_ic, NEW.group_id, 'exco_y5');
  END IF;

  -- EXCO Year 4
  IF NEW.exco_y4_ic IS NOT NULL THEN
    INSERT IGNORE INTO student_club_membership (student_ic, group_id, membership_role)
    VALUES (NEW.exco_y4_ic, NEW.group_id, 'exco_y4');
  END IF;

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_cocurricular_groups_after_update` AFTER UPDATE ON `cocurricular_groups` FOR EACH ROW BEGIN
  -- For each position: If changed, update membership table accordingly

  -- President
  IF OLD.president_ic != NEW.president_ic THEN
    -- Delete old role
    DELETE FROM student_club_membership
    WHERE student_ic = OLD.president_ic AND group_id = NEW.group_id AND membership_role = 'president';
    -- Insert new role if not NULL
    IF NEW.president_ic IS NOT NULL THEN
      INSERT IGNORE INTO student_club_membership (student_ic, group_id, membership_role)
      VALUES (NEW.president_ic, NEW.group_id, 'president');
    END IF;
  END IF;

  -- Vice President
  IF OLD.vice_president_ic != NEW.vice_president_ic THEN
    DELETE FROM student_club_membership
    WHERE student_ic = OLD.vice_president_ic AND group_id = NEW.group_id AND membership_role = 'vice_president';
    IF NEW.vice_president_ic IS NOT NULL THEN
      INSERT IGNORE INTO student_club_membership (student_ic, group_id, membership_role)
      VALUES (NEW.vice_president_ic, NEW.group_id, 'vice_president');
    END IF;
  END IF;

  -- Secretary
  IF OLD.secretary_ic != NEW.secretary_ic THEN
    DELETE FROM student_club_membership
    WHERE student_ic = OLD.secretary_ic AND group_id = NEW.group_id AND membership_role = 'secretary';
    IF NEW.secretary_ic IS NOT NULL THEN
      INSERT IGNORE INTO student_club_membership (student_ic, group_id, membership_role)
      VALUES (NEW.secretary_ic, NEW.group_id, 'secretary');
    END IF;
  END IF;

  -- Vice Secretary
  IF OLD.vice_secretary_ic != NEW.vice_secretary_ic THEN
    DELETE FROM student_club_membership
    WHERE student_ic = OLD.vice_secretary_ic AND group_id = NEW.group_id AND membership_role = 'vice_secretary';
    IF NEW.vice_secretary_ic IS NOT NULL THEN
      INSERT IGNORE INTO student_club_membership (student_ic, group_id, membership_role)
      VALUES (NEW.vice_secretary_ic, NEW.group_id, 'vice_secretary');
    END IF;
  END IF;

  -- Treasurer
  IF OLD.treasurer_ic != NEW.treasurer_ic THEN
    DELETE FROM student_club_membership
    WHERE student_ic = OLD.treasurer_ic AND group_id = NEW.group_id AND membership_role = 'treasurer';
    IF NEW.treasurer_ic IS NOT NULL THEN
      INSERT IGNORE INTO student_club_membership (student_ic, group_id, membership_role)
      VALUES (NEW.treasurer_ic, NEW.group_id, 'treasurer');
    END IF;
  END IF;

  -- Vice Treasurer
  IF OLD.vice_treasurer_ic != NEW.vice_treasurer_ic THEN
    DELETE FROM student_club_membership
    WHERE student_ic = OLD.vice_treasurer_ic AND group_id = NEW.group_id AND membership_role = 'vice_treasurer';
    IF NEW.vice_treasurer_ic IS NOT NULL THEN
      INSERT IGNORE INTO student_club_membership (student_ic, group_id, membership_role)
      VALUES (NEW.vice_treasurer_ic, NEW.group_id, 'vice_treasurer');
    END IF;
  END IF;

  -- EXCO Year 6
  IF OLD.exco_y6_ic != NEW.exco_y6_ic THEN
    DELETE FROM student_club_membership
    WHERE student_ic = OLD.exco_y6_ic AND group_id = NEW.group_id AND membership_role = 'exco_y6';
    IF NEW.exco_y6_ic IS NOT NULL THEN
      INSERT IGNORE INTO student_club_membership (student_ic, group_id, membership_role)
      VALUES (NEW.exco_y6_ic, NEW.group_id, 'exco_y6');
    END IF;
  END IF;

  -- EXCO Year 5
  IF OLD.exco_y5_ic != NEW.exco_y5_ic THEN
    DELETE FROM student_club_membership
    WHERE student_ic = OLD.exco_y5_ic AND group_id = NEW.group_id AND membership_role = 'exco_y5';
    IF NEW.exco_y5_ic IS NOT NULL THEN
      INSERT IGNORE INTO student_club_membership (student_ic, group_id, membership_role)
      VALUES (NEW.exco_y5_ic, NEW.group_id, 'exco_y5');
    END IF;
  END IF;

  -- EXCO Year 4
  IF OLD.exco_y4_ic != NEW.exco_y4_ic THEN
    DELETE FROM student_club_membership
    WHERE student_ic = OLD.exco_y4_ic AND group_id = NEW.group_id AND membership_role = 'exco_y4';
    IF NEW.exco_y4_ic IS NOT NULL THEN
      INSERT IGNORE INTO student_club_membership (student_ic, group_id, membership_role)
      VALUES (NEW.exco_y4_ic, NEW.group_id, 'exco_y4');
    END IF;
  END IF;

END
$$
DELIMITER ;

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
  `ach` varchar(255) DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` varchar(100) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejection_remarks` text DEFAULT NULL,
  `notification_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cocu_activities`
--

INSERT INTO `cocu_activities` (`id`, `student_ic`, `activity_name`, `activity_category`, `activity_date`, `award`, `activity_location`, `org`, `cert_path`, `created_at`, `ach`, `approval_status`, `approved_by`, `approved_at`, `rejection_remarks`, `notification_read`) VALUES
(62, '160406028234', 'Latihan Pertolongan Kecemasan', 'Acara Luar', '2025-07-28', 'Luar', 'Dewan SRIAAWP', NULL, NULL, '2025-07-20 17:42:18', 'Penyertaan', 'approved', NULL, NULL, NULL, 1),
(63, '160406028234', 'Acara Merentas Desa 2025', 'Acara Luar', '2025-08-07', 'Luar', 'Stadium Bukit Jalil', NULL, NULL, '2025-07-20 17:42:22', 'Penyertaan', 'approved', NULL, NULL, NULL, 1),
(64, '160406028234', 'Olahraga', 'Rumah Sukan', '2025-07-17', 'Daerah', 'Padang Semarak', 'UTM', 'uploads/certificates/DummyCertificate.pdf', '2025-07-20 17:43:48', 'Naib Johan', 'rejected', '770809147765', '2025-07-20 20:23:27', 'Sijil tidak jelas', 0),
(65, '160406028234', 'Gotong Royong Sekolah', 'Acara Sekolah', '2025-07-09', 'Sekolah', 'SRIAAWP', 'SRIAAWP', 'uploads/certificates/DummyCertificate.pdf', '2025-07-20 17:44:50', 'Penyertaan', 'pending', NULL, NULL, NULL, 1),
(66, '10403071555', 'Quiz Matematik', 'Aktiviti Kelas', '2025-07-17', 'Sekolah', 'SRIAAWP', 'Muallim Wan', 'uploads/certificates/DummyCertificate.pdf', '2025-07-20 18:10:30', 'Penyertaan', 'approved', NULL, NULL, NULL, 0),
(67, '160406028234', 'Quiz Matematik', 'Aktiviti Kelas', '2025-07-17', 'Sekolah', 'SRIAAWP', 'Muallim Wan', 'uploads/certificates/DummyCertificate.pdf', '2025-07-20 18:10:30', 'Penyertaan', 'approved', NULL, NULL, NULL, 1),
(68, '250909090909', 'Quiz Matematik', 'Aktiviti Kelas', '2025-07-17', 'Sekolah', 'SRIAAWP', 'Muallim Wan', 'uploads/certificates/DummyCertificate.pdf', '2025-07-20 18:10:30', 'Penyertaan', 'approved', NULL, NULL, NULL, 0),
(69, 'fffffsaffsafsaf', 'Quiz Matematik', 'Aktiviti Kelas', '2025-07-17', 'Sekolah', 'SRIAAWP', 'Muallim Wan', 'uploads/certificates/DummyCertificate.pdf', '2025-07-20 18:10:30', 'Penyertaan', 'approved', NULL, NULL, NULL, 0),
(70, '10203091555', 'Gotong Royong Sekolah', 'Acara Sekolah', '2025-07-11', 'Sekolah', 'SRIAAWP', 'SRIAAWP', 'uploads/certificates/DummyCertificate.pdf', '2025-07-20 18:12:12', 'Penyertaan', 'approved', NULL, NULL, NULL, 0),
(71, '110306132258', 'Gotong Royong Sekolah', 'Acara Sekolah', '2025-07-11', 'Sekolah', 'SRIAAWP', 'SRIAAWP', 'uploads/certificates/DummyCertificate.pdf', '2025-07-20 18:12:12', 'Penyertaan', 'approved', NULL, NULL, NULL, 0),
(72, '110505123364', 'Gotong Royong Sekolah', 'Acara Sekolah', '2025-07-11', 'Sekolah', 'SRIAAWP', 'SRIAAWP', 'uploads/certificates/DummyCertificate.pdf', '2025-07-20 18:12:12', 'Penyertaan', 'approved', NULL, NULL, NULL, 0),
(73, '110907023354', 'Gotong Royong Sekolah', 'Acara Sekolah', '2025-07-11', 'Sekolah', 'SRIAAWP', 'SRIAAWP', 'uploads/certificates/DummyCertificate.pdf', '2025-07-20 18:12:12', 'Penyertaan', 'approved', NULL, NULL, NULL, 0),
(74, '180202020202', 'Gotong Royong Sekolah', 'Acara Sekolah', '2025-07-11', 'Sekolah', 'SRIAAWP', 'SRIAAWP', 'uploads/certificates/DummyCertificate.pdf', '2025-07-20 18:12:12', 'Penyertaan', 'approved', NULL, NULL, NULL, 1),
(75, '160406028234', 'Kempen Kitar Semula', 'Kelab', '2025-07-05', 'Sekolah', 'SRIAAWP', 'Kelab Sains Dan Matematik', 'uploads/certificates/DummyCertificate.pdf', '2025-07-20 18:13:53', 'Penyertaan', 'approved', '770809147765', '2025-07-20 20:23:11', NULL, 0),
(76, '170101010101', 'Kempen Kitar Semula', 'Acara Sekolah', '2025-07-05', 'Sekolah', 'SRIAAWP', 'SRIAAWP', 'uploads/certificates/DummyCertificate.pdf', '2025-07-20 18:14:47', 'Penyertaan', 'pending', NULL, NULL, NULL, 0),
(77, '170101010101', 'Pertandingan Silat MSSD', 'Silat Acara Sekolah', '2025-08-02', 'Sekolah', 'Kuala Lumpur', NULL, NULL, '2025-07-20 18:15:13', 'Penyertaan', 'approved', NULL, NULL, NULL, 0),
(78, '170101010101', 'Pertandingan Hafazan', 'Acara Luar', '2025-07-25', 'Luar', 'Dewan SRIAAWP', NULL, NULL, '2025-07-20 18:15:18', 'Penyertaan', 'approved', NULL, NULL, NULL, 0),
(79, '170101010101', 'Pertandingan Robotik', 'Kelab', '2025-07-17', 'Kebangsaan', 'PWTC', 'Persatuan STEM Malaysia', 'uploads/certificates/DummyCertificate.pdf', '2025-07-20 18:16:55', 'Ketiga', 'approved', '770809147765', '2025-07-20 20:23:32', NULL, 0),
(80, '180202020202', 'Acara Merentas Desa 2025', 'Acara Luar', '2025-08-07', 'Luar', 'Stadium Bukit Jalil', NULL, NULL, '2025-07-20 18:20:05', 'Penyertaan', 'approved', NULL, NULL, NULL, 1),
(81, '180202020202', 'Latihan Pertolongan Kecemasan', 'Acara Luar', '2025-07-28', 'Luar', 'Dewan SRIAAWP', NULL, NULL, '2025-07-20 18:20:14', 'Penyertaan', 'approved', NULL, NULL, NULL, 1),
(82, '180202020202', 'Kempen Kitar Semula', 'Acara Sekolah', '2025-07-05', 'Sekolah', 'SRIAAWP', 'SRIAAWP', 'uploads/certificates/DummyCertificate.pdf', '2025-07-20 18:21:10', 'Penyertaan', 'pending', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cocu_events`
--

CREATE TABLE `cocu_events` (
  `id` int(11) NOT NULL,
  `student_ic` varchar(20) DEFAULT NULL,
  `group_name` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `activity_date` date DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `event_start_date` date NOT NULL,
  `event_end_date` date NOT NULL,
  `event_venue` varchar(255) NOT NULL,
  `event_description` text DEFAULT NULL,
  `event_type` enum('meeting','competition','training','social','other') DEFAULT 'other',
  `is_mandatory` tinyint(1) DEFAULT 0,
  `auto_register_members` tinyint(1) DEFAULT 0,
  `visibility` enum('public','club_only','private') DEFAULT 'public',
  `max_participants` int(11) DEFAULT NULL,
  `created_by` varchar(12) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','cancelled','completed') DEFAULT 'active',
  `registration_deadline` date DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `eligible_years` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `event_name`, `event_start_date`, `event_end_date`, `event_venue`, `event_description`, `event_type`, `is_mandatory`, `auto_register_members`, `visibility`, `max_participants`, `created_by`, `created_at`, `status`, `registration_deadline`, `contact_number`, `group_id`, `eligible_years`) VALUES
(21, 'Pertandingan Hafazan', '2025-07-25', '2025-07-25', 'Dewan SRIAAWP', 'Pertandingan Hafizan untuk murid-murid SRIAAWP.', 'competition', 0, 0, 'public', NULL, '770809147765', '2025-07-20 17:03:44', 'active', '2025-07-24', '012 xxxxxxxxx', NULL, NULL),
(22, 'Acara Merentas Desa 2025', '2025-08-07', '2025-08-07', 'Stadium Bukit Jalil', 'Acara Merentas Desa 2025 akan diadakan di kawasan Bukit Jalil.', 'competition', 0, 0, 'public', NULL, '770809147765', '2025-07-20 17:05:15', 'active', '2025-08-06', '014 xxxxxxxxx', NULL, NULL),
(23, 'Pertandingan Silat MSSD', '2025-08-02', '2025-08-04', 'Kuala Lumpur', '', 'competition', 0, 0, 'club_only', NULL, '770809147765', '2025-07-20 17:08:28', 'active', '2025-08-01', '014 xxxxxxxxx', 6, NULL),
(24, 'Latihan Pertolongan Kecemasan', '2025-07-28', '2025-07-28', 'Dewan SRIAAWP', 'Bertujuan untuk melatih murid-murid dalam asas bantuan kecemasan.', 'training', 0, 0, 'public', NULL, '770809147765', '2025-07-20 17:10:08', 'active', '2025-07-27', '019 xxxxxxxxx', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `event_notifications`
--

CREATE TABLE `event_notifications` (
  `notification_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `student_ic` varchar(12) NOT NULL,
  `notification_type` enum('event_created','event_updated','event_cancelled','reminder') DEFAULT 'event_created',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `registration_id` int(11) NOT NULL,
  `student_ic` varchar(12) NOT NULL,
  `registration_type` enum('manual','auto') DEFAULT 'manual',
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `attendance_status` enum('registered','present','absent','late') DEFAULT 'registered',
  `attendance_marked_by` varchar(12) DEFAULT NULL,
  `attendance_marked_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_registrations`
--

INSERT INTO `event_registrations` (`registration_id`, `student_ic`, `registration_type`, `registration_date`, `attendance_status`, `attendance_marked_by`, `attendance_marked_at`, `notes`, `event_id`) VALUES
(24, '160406028234', 'manual', '2025-07-20 17:42:18', 'registered', NULL, NULL, NULL, 24),
(25, '160406028234', 'manual', '2025-07-20 17:42:22', 'registered', NULL, NULL, NULL, 22),
(26, '170101010101', 'manual', '2025-07-20 18:15:13', 'registered', NULL, NULL, NULL, 23),
(27, '170101010101', 'manual', '2025-07-20 18:15:18', 'registered', NULL, NULL, NULL, 21),
(28, '180202020202', 'manual', '2025-07-20 18:20:05', 'registered', NULL, NULL, NULL, 22),
(29, '180202020202', 'manual', '2025-07-20 18:20:14', 'registered', NULL, NULL, NULL, 24);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_ic` varchar(20) NOT NULL,
  `user_role` enum('student','teacher','admin') NOT NULL,
  `type` enum('event','activity','announcement','registration','deadline') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `related_id` int(11) DEFAULT NULL,
  `related_table` varchar(50) DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_ic`, `user_role`, `type`, `title`, `message`, `related_id`, `related_table`, `is_read`, `created_at`, `expires_at`) VALUES
(157, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Pertandingan Hafazan', 'Pendaftaran untuk \'Pertandingan Hafazan\' akan ditutup dalam 3 hari.', 21, 'events', 1, '2025-07-20 17:39:08', NULL),
(158, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Latihan Pertolongan Kecemasan', 'Pendaftaran untuk \'Latihan Pertolongan Kecemasan\' akan ditutup dalam 6 hari.', 24, 'events', 1, '2025-07-20 17:39:08', NULL),
(159, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Pertandingan Hafazan', 'Pendaftaran untuk \'Pertandingan Hafazan\' akan ditutup dalam 3 hari.', 21, 'events', 1, '2025-07-20 17:39:08', NULL),
(160, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Latihan Pertolongan Kecemasan', 'Pendaftaran untuk \'Latihan Pertolongan Kecemasan\' akan ditutup dalam 6 hari.', 24, 'events', 1, '2025-07-20 17:39:08', NULL),
(161, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Pertandingan Hafazan', 'Pendaftaran untuk \'Pertandingan Hafazan\' akan ditutup dalam 3 hari.', 21, 'events', 1, '2025-07-20 17:39:16', NULL),
(162, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Latihan Pertolongan Kecemasan', 'Pendaftaran untuk \'Latihan Pertolongan Kecemasan\' akan ditutup dalam 6 hari.', 24, 'events', 1, '2025-07-20 17:39:16', NULL),
(163, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Pertandingan Hafazan', 'Pendaftaran untuk \'Pertandingan Hafazan\' akan ditutup dalam 3 hari.', 21, 'events', 1, '2025-07-20 17:39:17', NULL),
(164, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Latihan Pertolongan Kecemasan', 'Pendaftaran untuk \'Latihan Pertolongan Kecemasan\' akan ditutup dalam 6 hari.', 24, 'events', 1, '2025-07-20 17:39:17', NULL),
(165, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Pertandingan Hafazan', 'Pendaftaran untuk \'Pertandingan Hafazan\' akan ditutup dalam 3 hari.', 21, 'events', 1, '2025-07-20 17:39:17', NULL),
(166, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Latihan Pertolongan Kecemasan', 'Pendaftaran untuk \'Latihan Pertolongan Kecemasan\' akan ditutup dalam 6 hari.', 24, 'events', 1, '2025-07-20 17:39:17', NULL),
(167, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Pertandingan Hafazan', 'Pendaftaran untuk \'Pertandingan Hafazan\' akan ditutup dalam 3 hari.', 21, 'events', 1, '2025-07-20 17:39:29', NULL),
(168, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Latihan Pertolongan Kecemasan', 'Pendaftaran untuk \'Latihan Pertolongan Kecemasan\' akan ditutup dalam 6 hari.', 24, 'events', 1, '2025-07-20 17:39:29', NULL),
(169, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Pertandingan Hafazan', 'Pendaftaran untuk \'Pertandingan Hafazan\' akan ditutup dalam 3 hari.', 21, 'events', 1, '2025-07-20 17:39:29', NULL),
(170, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Latihan Pertolongan Kecemasan', 'Pendaftaran untuk \'Latihan Pertolongan Kecemasan\' akan ditutup dalam 6 hari.', 24, 'events', 1, '2025-07-20 17:39:29', NULL),
(171, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Pertandingan Hafazan', 'Pendaftaran untuk \'Pertandingan Hafazan\' akan ditutup dalam 3 hari.', 21, 'events', 1, '2025-07-20 17:39:40', NULL),
(172, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Latihan Pertolongan Kecemasan', 'Pendaftaran untuk \'Latihan Pertolongan Kecemasan\' akan ditutup dalam 6 hari.', 24, 'events', 1, '2025-07-20 17:39:40', NULL),
(173, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Pertandingan Hafazan', 'Pendaftaran untuk \'Pertandingan Hafazan\' akan ditutup dalam 3 hari.', 21, 'events', 1, '2025-07-20 17:39:42', NULL),
(174, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Latihan Pertolongan Kecemasan', 'Pendaftaran untuk \'Latihan Pertolongan Kecemasan\' akan ditutup dalam 6 hari.', 24, 'events', 1, '2025-07-20 17:39:42', NULL),
(175, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Pertandingan Hafazan', 'Pendaftaran untuk \'Pertandingan Hafazan\' akan ditutup dalam 3 hari.', 21, 'events', 1, '2025-07-20 17:39:42', NULL),
(176, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Latihan Pertolongan Kecemasan', 'Pendaftaran untuk \'Latihan Pertolongan Kecemasan\' akan ditutup dalam 6 hari.', 24, 'events', 1, '2025-07-20 17:39:42', NULL),
(181, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Pertandingan Hafazan', 'Pendaftaran untuk \'Pertandingan Hafazan\' akan ditutup dalam 3 hari.', 21, 'events', 1, '2025-07-20 17:40:15', NULL),
(182, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Latihan Pertolongan Kecemasan', 'Pendaftaran untuk \'Latihan Pertolongan Kecemasan\' akan ditutup dalam 6 hari.', 24, 'events', 1, '2025-07-20 17:40:15', NULL),
(183, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Pertandingan Hafazan', 'Pendaftaran untuk \'Pertandingan Hafazan\' akan ditutup dalam 3 hari.', 21, 'events', 1, '2025-07-20 17:40:15', NULL),
(184, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Latihan Pertolongan Kecemasan', 'Pendaftaran untuk \'Latihan Pertolongan Kecemasan\' akan ditutup dalam 6 hari.', 24, 'events', 1, '2025-07-20 17:40:15', NULL),
(185, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Pertandingan Hafazan', 'Pendaftaran untuk \'Pertandingan Hafazan\' akan ditutup dalam 3 hari.', 21, 'events', 1, '2025-07-20 17:40:53', NULL),
(186, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Latihan Pertolongan Kecemasan', 'Pendaftaran untuk \'Latihan Pertolongan Kecemasan\' akan ditutup dalam 6 hari.', 24, 'events', 1, '2025-07-20 17:40:53', NULL),
(187, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Pertandingan Hafazan', 'Pendaftaran untuk \'Pertandingan Hafazan\' akan ditutup dalam 3 hari.', 21, 'events', 1, '2025-07-20 17:40:53', NULL),
(188, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Latihan Pertolongan Kecemasan', 'Pendaftaran untuk \'Latihan Pertolongan Kecemasan\' akan ditutup dalam 6 hari.', 24, 'events', 1, '2025-07-20 17:40:53', NULL),
(189, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Pertandingan Hafazan', 'Pendaftaran untuk \'Pertandingan Hafazan\' akan ditutup dalam 3 hari.', 21, 'events', 1, '2025-07-20 17:41:35', NULL),
(190, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Latihan Pertolongan Kecemasan', 'Pendaftaran untuk \'Latihan Pertolongan Kecemasan\' akan ditutup dalam 6 hari.', 24, 'events', 1, '2025-07-20 17:41:35', NULL),
(191, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Pertandingan Hafazan', 'Pendaftaran untuk \'Pertandingan Hafazan\' akan ditutup dalam 3 hari.', 21, 'events', 1, '2025-07-20 17:41:39', NULL),
(192, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Latihan Pertolongan Kecemasan', 'Pendaftaran untuk \'Latihan Pertolongan Kecemasan\' akan ditutup dalam 6 hari.', 24, 'events', 1, '2025-07-20 17:41:39', NULL),
(193, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Pertandingan Hafazan', 'Pendaftaran untuk \'Pertandingan Hafazan\' akan ditutup dalam 3 hari.', 21, 'events', 1, '2025-07-20 17:41:39', NULL),
(194, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Latihan Pertolongan Kecemasan', 'Pendaftaran untuk \'Latihan Pertolongan Kecemasan\' akan ditutup dalam 6 hari.', 24, 'events', 1, '2025-07-20 17:41:39', NULL),
(195, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Pertandingan Hafazan', 'Pendaftaran untuk \'Pertandingan Hafazan\' akan ditutup dalam 3 hari.', 21, 'events', 0, '2025-07-20 17:41:45', NULL),
(196, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Latihan Pertolongan Kecemasan', 'Pendaftaran untuk \'Latihan Pertolongan Kecemasan\' akan ditutup dalam 6 hari.', 24, 'events', 0, '2025-07-20 17:41:45', NULL),
(197, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Pertandingan Hafazan', 'Pendaftaran untuk \'Pertandingan Hafazan\' akan ditutup dalam 3 hari.', 21, 'events', 0, '2025-07-20 17:41:45', NULL),
(198, 'administrator', 'admin', '', 'Tarikh Akhir Pendaftaran: Latihan Pertolongan Kecemasan', 'Pendaftaran untuk \'Latihan Pertolongan Kecemasan\' akan ditutup dalam 6 hari.', 24, 'events', 0, '2025-07-20 17:41:45', NULL),
(199, '160406028234', 'student', 'registration', 'Pendaftaran Acara Berjaya', 'Anda telah berjaya mendaftar untuk acara \'Latihan Pertolongan Kecemasan\'. Terima kasih!', NULL, NULL, 1, '2025-07-20 17:42:18', NULL),
(200, '160406028234', 'student', 'registration', 'Pendaftaran Acara Berjaya', 'Anda telah berjaya mendaftar untuk acara \'Acara Merentas Desa 2025\'. Terima kasih!', NULL, NULL, 1, '2025-07-20 17:42:22', NULL),
(201, '770809147765', 'teacher', '', 'Borang Aktiviti Baru', 'Mimi Liyana Binti Muhammad Arif telah menghantar borang aktiviti \'Olahraga\' untuk kelulusan anda.', 0, 'cocu_activities', 0, '2025-07-20 17:43:48', NULL),
(202, '770809147765', 'teacher', '', 'Borang Aktiviti Baru', 'Mimi Liyana Binti Muhammad Arif telah menghantar borang aktiviti \'Gotong Royong Sekolah\' untuk kelulusan anda.', 0, 'cocu_activities', 0, '2025-07-20 17:44:50', NULL),
(203, '770809147765', 'teacher', '', 'Borang Aktiviti Baru', 'Mimi Liyana Binti Muhammad Arif telah menghantar borang aktiviti \'Kempen Kitar Semula\' untuk kelulusan anda.', 0, 'cocu_activities', 0, '2025-07-20 18:13:53', NULL),
(204, '770809147765', 'teacher', '', 'Borang Aktiviti Baru', 'Ahmad Faiz Bin Ali telah menghantar borang aktiviti \'Kempen Kitar Semula\' untuk kelulusan anda.', 0, 'cocu_activities', 0, '2025-07-20 18:14:47', NULL),
(205, '170101010101', 'student', 'registration', 'Pendaftaran Acara Berjaya', 'Anda telah berjaya mendaftar untuk acara \'Pertandingan Silat MSSD\'. Terima kasih!', NULL, NULL, 0, '2025-07-20 18:15:13', NULL),
(206, '170101010101', 'student', 'registration', 'Pendaftaran Acara Berjaya', 'Anda telah berjaya mendaftar untuk acara \'Pertandingan Hafazan\'. Terima kasih!', NULL, NULL, 0, '2025-07-20 18:15:18', NULL),
(207, '770809147765', 'teacher', '', 'Borang Aktiviti Baru', 'Ahmad Faiz Bin Ali telah menghantar borang aktiviti \'Pertandingan Robotik\' untuk kelulusan anda.', 0, 'cocu_activities', 0, '2025-07-20 18:16:55', NULL),
(208, '180202020202', 'student', 'registration', 'Pendaftaran Acara Berjaya', 'Anda telah berjaya mendaftar untuk acara \'Acara Merentas Desa 2025\'. Terima kasih!', NULL, NULL, 0, '2025-07-20 18:20:05', NULL),
(209, '180202020202', 'student', 'registration', 'Pendaftaran Acara Berjaya', 'Anda telah berjaya mendaftar untuk acara \'Latihan Pertolongan Kecemasan\'. Terima kasih!', NULL, NULL, 0, '2025-07-20 18:20:14', NULL),
(210, '800811023984', 'teacher', '', 'Borang Aktiviti Baru', 'Nur Aisyah Binti Zainal telah menghantar borang aktiviti \'Kempen Kitar Semula\' untuk kelulusan anda.', 0, 'cocu_activities', 0, '2025-07-20 18:21:10', NULL),
(211, '160406028234', 'student', 'activity', 'Status Borang Aktiviti Dikemaskini', 'Borang Aktiviti \'Kempen Kitar Semula\' telah diluluskan.', NULL, NULL, 0, '2025-07-20 18:23:11', NULL),
(212, '160406028234', 'student', 'activity', 'Aktiviti Ditolak', 'Aktiviti \'Olahraga\' telah ditolak. Sebab: Sijil tidak jelas', 64, 'cocu_activities', 0, '2025-07-20 18:23:27', NULL),
(213, '170101010101', 'student', 'activity', 'Status Borang Aktiviti Dikemaskini', 'Borang Aktiviti \'Pertandingan Robotik\' telah diluluskan.', NULL, NULL, 0, '2025-07-20 18:23:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_ic` varchar(255) NOT NULL,
  `matrix` varchar(255) DEFAULT NULL,
  `student_pass` varchar(255) NOT NULL,
  `student_fname` text NOT NULL,
  `student_class` int(11) NOT NULL,
  `gender` text DEFAULT NULL,
  `student_dob` date DEFAULT NULL,
  `student_doe` date DEFAULT NULL,
  `contact_num` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_ic`, `matrix`, `student_pass`, `student_fname`, `student_class`, `gender`, `student_dob`, `student_doe`, `contact_num`) VALUES
('10203091555', '', '$2y$10$7pS91wkMpmS66n0j50SKCOyp3RrSAt9R8dwzjlN3VuKpsScD50AlW', 'Ahmad Faiyaz', 2, 'L', NULL, NULL, NULL),
('10403071555', 'A22MJ8001', '$2y$10$aq5vOBA6w2/27DKO6g1zOeeBrbOv1z8E9eoe4qFqUIU5ZgOvDNZQa', 'Muhammad Ali', 1, 'L', NULL, NULL, NULL),
('110306132258', 'A236698', '$2y$10$2Tom504fy8E6nzGkNuncZehvgGIrLlv8v.QjgpAfPzX3OA2qirAWu', 'Muhammad Harif Bin Abu', 2, 'L', NULL, NULL, NULL),
('110505123364', 'A236698', '$2y$10$iqMsSBslh0.JDWPzXl7rh.yVtqBayvBjmUmHtqEtdBWIfQSTVrv2m', 'Muhammad Alibaba Bin baba', 2, 'L', NULL, NULL, NULL),
('110809116654', 'A559632', '$2y$10$wdHlJQo55Dx2W4uxsjAFz.JAGZEkfLOKm/D6rskG3D.E9ZGkf.KvW', 'Amin Bin Halal', 2, 'L', NULL, NULL, NULL),
('110907023354', 'A559632', '$2y$10$mzPZMVoih9YrjPcdDs56ZeTgTCkEdePQ0yoJTW6rayFrzuxs5A8Pe', 'Muhammad Abadi Bin Kilal', 2, 'L', NULL, NULL, NULL),
('120301226698', 'A555683', '$2y$10$fIy0gxICw.O2ID9tHdRyAOE3uBAIafMUncItcHd50wekk7ygAS326', 'Siti Wowo Binti Xixi', 3, 'P', NULL, NULL, NULL),
('120322014498', 'A883096', '$2y$10$I430lTyeEyUipLHaZfDIa.SkuGXzzCqLiRReJnchpqbhojXhfNbsS', 'Nurul Hazwani Binti Said', 3, 'P', NULL, NULL, NULL),
('120605023367', 'A223098', '$2y$10$tlYXsmOopz50edWVlcIcKetqalP.zO3ysdPdan7BZ.ldZBnaCCRJC', 'Lily Manoban Sammy', 1, 'P', '2012-05-06', '2025-02-17', '0132264492'),
('120606135547', 'A555683', '$2y$10$CnouXqqTJOXnj85rZkGYquBX7bzpz/vYF52EwFduzf9THXRLy9MfC', 'Siti Maimunah Binti Haikal', 3, 'P', NULL, NULL, NULL),
('120711056302', 'A883096', '$2y$10$qJtWZoCBHnYYgLWUu7./tehrzSjp0R2agD2rWZulkM34SZRatYjJ6', 'Nurul Hitam Binti Lolo', 3, 'P', NULL, NULL, NULL),
('128394101283', 'A25MJ8001', '$2y$10$/z8vvclR8sGJAcafEPvELux6XAHRNGG5piHXPtHQ1WiXbqD6WVEg.', 'Ahmad Danel', 1, 'L', NULL, NULL, NULL),
('128732101287', 'A23WP2994', '$2y$10$45W33JJjV21adY0Kwc9cHe9QrtmFKWNS49oW/ZuOerCvQGWJiZE4y', 'Sofea Azlan', 3, 'P', '2025-07-04', '2025-07-03', '01957925647'),
('140219012568', 'A258961', '$2y$10$8ErQmlf60cNuq1VTgH7pH.iKzHq8O1U.HZ.aHUGKGl2gvqZOisrMm', 'Yap Jun Hao', 3, 'L', '2014-02-19', '2025-02-17', '0126698853'),
('160406028234', 'A040616', '$2y$10$Gc1wvjXu3oynRGAEiKw11uQF89eH33pC/NF20eYDC83MXU1n.Elty', 'Mimi Liyana Binti Muhammad Arif', 1, 'Lelaki', '2016-04-06', '2025-02-17', '0165432260'),
('170101010101', 'A012598', '$2y$10$B3MTMr0XLmuwAF376ACReeRhDDHUktdbiyjO.r5Ez7JLgGaBtM6I6', 'Ahmad Faiz Bin Ali', 1, 'L', '2017-01-01', '2025-02-17', '0123456789'),
('180202020202', 'A963257', '$2y$10$809gfbjKUhord7F04xIVX.1NpV/68LgeWYWuz2nC3RvOpY7lcoa1y', 'Nur Aisyah Binti Zainal', 2, 'P', '2018-02-02', '2025-02-17', '0198765432'),
('190303030303', 'A368521', '$2y$10$jmqtFq8AGBwjMsvo8OEhgeq3aq.7pDcOJMqOhgm5uGqxbGlW3AibK', 'Lim Wei Jie', 3, 'L', '2019-03-03', '2025-03-03', '0181234567'),
('210505050505', 'A279301', '$2y$10$2pjCSQBQ1aV3WjR9jfQ.4e4AdpIsd8xpdZRDpZRg9wh5DtzzBEWUy', 'Muhammad Danish Bin Hakim', 1, 'L', '2021-05-05', '2025-03-05', '0142222333'),
('220606060606', 'A239712', '$2y$10$kTicimPGHBaGyeRpxPV05.k2Lo.Rhu9z8GbH09F7rvs4PDV/bZiW6', 'Tan Mei Ling', 2, 'P', '2022-06-06', '2025-03-06', '0153333444'),
('230707070707', 'A360258', '$2y$10$YzncCh22jy8M9quYzcYFWewcD.Vuhyw7uyeQH4aUl4d8H/wN1kOHy', 'Arjun Kumar', 3, 'L', '2023-07-07', '2025-03-07', '0164444555'),
('240808080808', 'A239601', '$2y$10$otQwBrmMgXIyon20HYeRD.4SXnZZd2tlK6gH9xKIi597Ctk2Sr1na', 'Aisyah Humaira', 1, 'P', '2024-08-08', '2025-03-08', '0175555666'),
('250909090909', 'A253673', '$2y$10$r8sUhjGasiFuz4mRGeLoI.fDyB1EWLW0CsRzUPIQnqq56IsA5s02S', 'Lim Jia Hao', 1, 'L', '2025-09-09', '2025-03-09', '0186666777'),
('fffffsaffsafsaf', 'A22MJ8001', '$2y$10$dKAxvX4FC.b/4Cw1oVcCyug83ljuZMF9gT.WrCl2YouHE0pb7pztG', 'Ain Natasha halim', 1, 'P', '2025-07-05', '2025-07-05', '01957925647');

-- --------------------------------------------------------

--
-- Table structure for table `student_club_membership`
--

CREATE TABLE `student_club_membership` (
  `student_ic` varchar(255) NOT NULL,
  `group_id` int(11) NOT NULL,
  `membership_role` enum('member','president','vice_president','secretary','vice_secretary','treasurer','vice_treasurer','exco_y6','exco_y5','exco_y4') DEFAULT 'member'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_club_membership`
--

INSERT INTO `student_club_membership` (`student_ic`, `group_id`, `membership_role`) VALUES
('110505123364', 36, 'member'),
('160406028234', 6, 'treasurer'),
('160406028234', 33, 'president'),
('160406028234', 34, 'member'),
('160406028234', 35, 'member'),
('170101010101', 23, 'secretary'),
('170101010101', 33, 'vice_president'),
('170101010101', 34, 'vice_secretary'),
('210505050505', 33, 'member'),
('240808080808', 33, 'member'),
('240808080808', 36, 'member');

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `teacher_ic` varchar(255) NOT NULL,
  `teacher_uname` varchar(255) DEFAULT NULL,
  `teacher_pass` varchar(255) NOT NULL,
  `teacher_fname` text NOT NULL,
  `teacher_contact` varchar(255) NOT NULL,
  `teacher_email` varchar(255) DEFAULT NULL,
  `teacher_dob` date DEFAULT NULL,
  `teacher_doe` date DEFAULT NULL,
  `teacher_address` text DEFAULT NULL,
  `teacher_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`teacher_ic`, `teacher_uname`, `teacher_pass`, `teacher_fname`, `teacher_contact`, `teacher_email`, `teacher_dob`, `teacher_doe`, `teacher_address`, `teacher_pic`) VALUES
('770809147765', 'wanbakar', '$2y$10$Iz15vM2Hro36C2.Kp.SRx.G5XOzbDDuUX86zcGL1m/r3DW.LVBNDq', 'WAN BIN ABU BAKAR', '0132658897', 'muallimwan@gmail.com', '1977-08-09', '2012-02-01', '21st Floor Plaza Sentral Block C', 'img/uploads/10-profile-picture-ideas-to-make-you-stand-out.jpg'),
('780513503890', 'ong78', '$2y$10$66TPLFEJLJYz7n.M3U.njuQjGdlNdGoAtCyzHunX9oyjFXxRJOIua', 'ONG LIN HA', '0182331874', NULL, NULL, NULL, NULL, NULL),
('800811023984', 'aimanabu', '$2y$10$paCI1aCoQbBFDwZpQy5Z/OKGUz2lJ8jqDLzSZsZxls/Wjoeu4mDvK', 'AIMAN MISKIN BIN ABU LAI', '0165320012', NULL, NULL, NULL, NULL, NULL),
('910711028452', 'sitiaisyah', '$2y$10$0ELONZvsxJSV33SP4a6.Buq2SxI6L4M.2JXWghhwhdzQYrF0jiGPW', 'SITI NUR AISYAH BINTI AIMAN', '0196547821', NULL, NULL, NULL, NULL, NULL);

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
  ADD PRIMARY KEY (`class_id`),
  ADD KEY `class_ibfk_1` (`head_teacher`);

--
-- Indexes for table `cocurricular`
--
ALTER TABLE `cocurricular`
  ADD PRIMARY KEY (`student_ic`,`cocu_year`);

--
-- Indexes for table `cocurricular_groups`
--
ALTER TABLE `cocurricular_groups`
  ADD PRIMARY KEY (`group_id`),
  ADD KEY `fk_vice_secretary` (`vice_secretary_ic`),
  ADD KEY `fk_vice_treasurer` (`vice_treasurer_ic`),
  ADD KEY `fk_exco_y6` (`exco_y6_ic`),
  ADD KEY `fk_exco_y5` (`exco_y5_ic`),
  ADD KEY `fk_exco_y4` (`exco_y4_ic`);

--
-- Indexes for table `cocu_activities`
--
ALTER TABLE `cocu_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_ic` (`student_ic`);

--
-- Indexes for table `cocu_events`
--
ALTER TABLE `cocu_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `fk_events_group_id` (`group_id`);

--
-- Indexes for table `event_notifications`
--
ALTER TABLE `event_notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_student_ic` (`student_ic`),
  ADD KEY `idx_is_read` (`is_read`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD UNIQUE KEY `unique_event_student` (`event_id`,`student_ic`),
  ADD KEY `student_id` (`student_ic`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_student_ic` (`student_ic`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_ic` (`user_ic`),
  ADD KEY `idx_user_role` (`user_role`),
  ADD KEY `idx_is_read` (`is_read`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_ic`),
  ADD KEY `student_class` (`student_class`);

--
-- Indexes for table `student_club_membership`
--
ALTER TABLE `student_club_membership`
  ADD PRIMARY KEY (`student_ic`,`group_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`teacher_ic`),
  ADD UNIQUE KEY `teacher_uname` (`teacher_uname`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `cocurricular_groups`
--
ALTER TABLE `cocurricular_groups`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `cocu_activities`
--
ALTER TABLE `cocu_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `cocu_events`
--
ALTER TABLE `cocu_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `event_notifications`
--
ALTER TABLE `event_notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=214;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `class`
--
ALTER TABLE `class`
  ADD CONSTRAINT `class_ibfk_1` FOREIGN KEY (`head_teacher`) REFERENCES `teacher` (`teacher_ic`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `cocurricular`
--
ALTER TABLE `cocurricular`
  ADD CONSTRAINT `cocurricular_ibfk_1` FOREIGN KEY (`student_ic`) REFERENCES `student` (`student_ic`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cocurricular_groups`
--
ALTER TABLE `cocurricular_groups`
  ADD CONSTRAINT `fk_exco_y4` FOREIGN KEY (`exco_y4_ic`) REFERENCES `student` (`student_ic`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_exco_y5` FOREIGN KEY (`exco_y5_ic`) REFERENCES `student` (`student_ic`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_exco_y6` FOREIGN KEY (`exco_y6_ic`) REFERENCES `student` (`student_ic`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vice_secretary` FOREIGN KEY (`vice_secretary_ic`) REFERENCES `student` (`student_ic`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vice_treasurer` FOREIGN KEY (`vice_treasurer_ic`) REFERENCES `student` (`student_ic`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `cocu_activities`
--
ALTER TABLE `cocu_activities`
  ADD CONSTRAINT `cocu_activities_ibfk_1` FOREIGN KEY (`student_ic`) REFERENCES `student` (`student_ic`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_events_group_id` FOREIGN KEY (`group_id`) REFERENCES `cocurricular_groups` (`group_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`student_ic`) REFERENCES `student` (`student_ic`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`student_class`) REFERENCES `class` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_club_membership`
--
ALTER TABLE `student_club_membership`
  ADD CONSTRAINT `student_club_membership_ibfk_1` FOREIGN KEY (`student_ic`) REFERENCES `student` (`student_ic`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_club_membership_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `cocurricular_groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
