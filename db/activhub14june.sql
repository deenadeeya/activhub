-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 14, 2025 at 04:56 PM
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
CREATE DATABASE IF NOT EXISTS `activhub` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `activhub`;

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
(1, 'Red Crescent Youth', 'uniform_bodies', 'something', 'logos/red_crescent.png', 'SITI NUR AISYAH BINTI AIMAN', '910711028452', '170101010101', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Photography Club', 'clubs_associations', 'A club for students interested in photography and visual storytelling.', 'logos/photography.png', 'ONG LIN', '780513503890', '05010101008', '05010101007', '05010101016', '05010101001', NULL, NULL, NULL, NULL, NULL),
(4, 'Prefects Guild', 'others', NULL, 'logos/logo.png', 'MUALLIM WAN BIN ABU BAKAR', '770809147765', '05010101012', '05010101013', '05010101009', '05010101010', NULL, NULL, NULL, NULL, NULL),
(6, 'Silat', 'sports', 'Mengajar murid-murid silat.', 'logos/1747660443_Silat.png', 'Encik Syed', '4235351616', NULL, '170101010101', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'Chess Club', 'clubs_associations', 'Chess club for strategic minds.', 'logos/logo.png', 'ONG LIN', '780513503890', '170101010101', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'Netball Team', 'sports', 'Netball team for girls.', 'logos/logo.png', 'SITI NUR AISYAH BINTI AIMAN', '910711028452', '180202020202', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'Robotics Club', 'clubs_associations', 'Robotics and engineering club.', 'logos/logo.png', 'AIMAN MISKIN BIN ABU', '800811023984', '190303030303', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 'Badminton Club', 'sports', 'Badminton training and tournaments.', 'logos/logo.png', 'LEE CHONG WEI', '800101010101', '200404040404', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 'Drama Society', 'clubs_associations', 'Drama and performing arts.', 'logos/logo.png', 'FATIMAH YUSOF', '820202020202', '210505050505', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 'Science Club', 'clubs_associations', 'Science experiments and fairs.', 'logos/logo.png', 'DR. TAN', '830303030303', '220606060606', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 'Football Team', 'sports', 'School football team.', 'logos/1748274676_football.png', 'ENCIK AZMAN', '840404040404', '230707070707', '', '', '', NULL, NULL, NULL, NULL, NULL),
(24, 'Art Club', 'clubs_associations', 'Art and creativity club.', 'logos/logo.png', 'PUAN SITI', '850505050505', '240808080808', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(25, 'Music Band', 'clubs_associations', 'School music band.', 'logos/logo.png', 'MR. LIM', '860606060606', '250909090909', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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
  `notification_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cocu_activities`
--

INSERT INTO `cocu_activities` (`id`, `student_ic`, `activity_name`, `activity_category`, `activity_date`, `award`, `activity_location`, `org`, `cert_path`, `created_at`, `ach`, `approval_status`, `approved_by`, `approved_at`, `notification_read`) VALUES
(36, '170101010101', 'English Matsuri 2025', 'Lain-Lain', '2025-02-06', 'Kebangsaan', 'Kuala Lumpur', 'Universiti Teknologi Malaysia', 'uploads/certificates/2025 MJIIT Japan Day Volunteer.pdf', '2025-06-10 14:41:35', 'Johan', 'approved', '770809147765', '2025-06-10 16:41:49', 1),
(37, '170101010101', 'Test', 'Lain-Lain', '2222-02-22', 'Antarabangsa', 'Kuala Lumpur', 'Universiti Teknologi Malaysia', 'uploads/certificates/2025 MJIIT Japan Day Volunteer.pdf', '2025-06-10 15:01:54', 'Johan', 'approved', '770809147765', '2025-06-11 07:28:11', 1),
(38, '170101010101', 'Test 2', 'Acara Luar', '2026-03-02', 'Daerah', 'MJIIT', 'UTM', NULL, '2025-06-11 00:42:40', 'Saguhati', 'approved', NULL, NULL, 1),
(39, '170101010101', 'English Matsuri 2025', 'Lain-Lain', '0000-00-00', 'Kebangsaan', 'Kuala Lumpur', 'Universiti Teknologi Malaysia', 'uploads/certificates/2025 MJIIT Japan Day Volunteer.pdf', '2025-06-11 00:43:07', 'Johan', 'approved', '770809147765', '2025-06-11 02:43:48', 1),
(40, '170101010101', 'Sports Carnival', 'Acara Sekolah', '2025-09-05', 'Penyertaan (Sekolah)', 'Padang Sekolah', NULL, NULL, '2025-06-11 00:45:53', NULL, 'approved', NULL, NULL, 1),
(45, '170101010101', 'STEM Innovation Day', 'Science Club Acara Sekolah', '2025-08-10', 'Sekolah', 'Makmal Sains', NULL, NULL, '2025-06-11 02:05:23', 'Penyertaan', 'approved', NULL, NULL, 1),
(46, '170101010101', 'Olahraga', 'Sukan', '2025-06-05', 'Sekolah', 'Padang SRIAAWP', 'SRIAAWP', 'uploads/certificates/DummyCertificate.pdf', '2025-06-11 05:36:18', 'Johan', 'approved', '770809147765', '2025-06-11 09:53:49', 1),
(47, '170101010101', 'Arab Language Festival 2025', 'Chess Club Acara Sekolah', '2025-07-15', 'Sekolah', 'Dewan Bestari', NULL, NULL, '2025-06-11 07:51:31', 'Penyertaan', 'approved', NULL, NULL, 1),
(48, '170101010101', 'English Matsuri', 'Acara Luar', '2025-08-27', 'Luar', 'MJIIT', NULL, NULL, '2025-06-14 08:13:47', 'Penyertaan', 'approved', NULL, NULL, 1),
(54, '160406028234', 'Olahraga', 'Rumah Sukan', '2025-06-26', 'Daerah', 'SRIAAWP', 'SRIAAWP', 'uploads/certificates/DummyCertificate.pdf', '2025-06-14 12:24:35', 'Penyertaan', 'approved', NULL, NULL, 1),
(55, '160406028234', 'Melawat Zoo', 'Acara Sekolah', '2025-06-20', 'Sekolah', 'Zoo Negara', 'SRIAAWP', 'uploads/certificates/DummyCertificate.pdf', '2025-06-14 12:25:29', 'Penyertaan', 'approved', '770809147765', '2025-06-14 14:37:03', 1),
(56, '120605023367', 'Quiz Matematik', 'Kelab', '2025-06-20', 'Sekolah', 'SRIAAWP', 'SRIAAWP', 'uploads/certificates/DummyCertificate.pdf', '2025-06-14 13:09:07', 'Penyertaan', 'approved', NULL, NULL, 0),
(57, '160406028234', 'Quiz Matematik', 'Kelab', '2025-06-20', 'Sekolah', 'SRIAAWP', 'SRIAAWP', 'uploads/certificates/DummyCertificate.pdf', '2025-06-14 13:09:07', 'Penyertaan', 'approved', NULL, NULL, 1),
(58, '170101010101', 'Quiz Matematik', 'Kelab', '2025-06-20', 'Sekolah', 'SRIAAWP', 'SRIAAWP', 'uploads/certificates/DummyCertificate.pdf', '2025-06-14 13:09:07', 'Penyertaan', 'approved', NULL, NULL, 0),
(59, '210505050505', 'Quiz Matematik', 'Kelab', '2025-06-20', 'Sekolah', 'SRIAAWP', 'SRIAAWP', 'uploads/certificates/DummyCertificate.pdf', '2025-06-14 13:09:07', 'Penyertaan', 'approved', NULL, NULL, 0),
(60, '250909090909', 'Quiz Matematik', 'Kelab', '2025-06-20', 'Sekolah', 'SRIAAWP', 'SRIAAWP', 'uploads/certificates/DummyCertificate.pdf', '2025-06-14 13:09:07', 'Penyertaan', 'approved', NULL, NULL, 0),
(61, '160406028234', 'ewqedwq', 'Unit Beruniform', '2025-06-17', 'Negeri', 'rqrw', 'qwrqw', 'uploads/certificates/DummyCertificate.pdf', '2025-06-14 13:14:33', 'Johan', 'pending', NULL, NULL, 1);

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
  `registration_deadline` date DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `eligible_years` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `event_name`, `event_start_date`, `event_end_date`, `event_venue`, `registration_deadline`, `contact_number`, `group_id`, `eligible_years`) VALUES
(4, 'English Matsuri', '2025-08-27', '2026-02-23', 'MJIIT', '2025-09-29', '011-65546909', NULL, NULL),
(7, 'Arab Language Festival 2025', '2025-07-15', '2025-07-16', 'Dewan Bestari', '2025-07-10', '0123456789', 10, NULL),
(8, 'STEM Innovation Day', '2025-08-10', '2025-08-10', 'Makmal Sains', '2025-08-05', '0198765432', 22, NULL),
(9, 'Sports Carnival', '2025-09-05', '2025-09-06', 'Padang Sekolah', '2025-09-01', '0131111222', 20, NULL),
(10, 'Art & Music Night', '2025-11-12', '2025-11-12', 'Auditorium', '2025-11-05', '0186666777', 25, '5'),
(11, 'UNBOCS 2025', '2025-05-15', '2025-05-15', 'Dewan Bestari', '2025-05-10', '0123456789', 10, NULL),
(12, 'SUKMAS', '2025-05-20', '2025-05-20', 'Padang SRIAWWP', '2025-05-20', '0186666777', 25, NULL),
(14, 'English Matsuri', '2025-06-02', '2025-07-03', 'MJIIT', '2025-06-05', '011-65546909', 21, NULL),
(15, 'Test 2', '2026-03-02', '2026-03-04', 'MJIIT', '2026-03-03', '011-65546909', NULL, '4,5'),
(16, 'swim gala', '2025-06-14', '2025-06-15', 'bukit jalil', '2025-06-12', '0123456789', NULL, '3,4,5,6'),
(17, 'Pertandingan Hafazan', '2025-06-20', '2025-06-20', 'Dewan SRIAAWP', '2025-06-12', '0123456789', NULL, '1,2,3,4,5,6');

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `registration_id` int(11) NOT NULL,
  `student_id` varchar(255) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_registrations`
--

INSERT INTO `event_registrations` (`registration_id`, `student_id`, `event_id`) VALUES
(11, '160406028234', 10),
(12, '170101010101', 15),
(13, '170101010101', 9),
(18, '170101010101', 8),
(19, '170101010101', 7),
(20, '170101010101', 4);

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
('110306132258', 'A236698', '$2y$10$2Tom504fy8E6nzGkNuncZehvgGIrLlv8v.QjgpAfPzX3OA2qirAWu', 'Muhammad Harif Bin Abu', 2, 'L', NULL, NULL, NULL),
('110505123364', 'A236698', '$2y$10$iqMsSBslh0.JDWPzXl7rh.yVtqBayvBjmUmHtqEtdBWIfQSTVrv2m', 'Muhammad Alibaba Bin baba', 2, 'L', NULL, NULL, NULL),
('110809116654', 'A559632', '$2y$10$wdHlJQo55Dx2W4uxsjAFz.JAGZEkfLOKm/D6rskG3D.E9ZGkf.KvW', 'Amin Bin Halal', 2, 'L', NULL, NULL, NULL),
('110907023354', 'A559632', '$2y$10$mzPZMVoih9YrjPcdDs56ZeTgTCkEdePQ0yoJTW6rayFrzuxs5A8Pe', 'Muhammad Abadi Bin Kilal', 2, 'L', NULL, NULL, NULL),
('120301226698', 'A555683', '$2y$10$fIy0gxICw.O2ID9tHdRyAOE3uBAIafMUncItcHd50wekk7ygAS326', 'Siti Wowo Binti Xixi', 3, 'P', NULL, NULL, NULL),
('120322014498', 'A883096', '$2y$10$I430lTyeEyUipLHaZfDIa.SkuGXzzCqLiRReJnchpqbhojXhfNbsS', 'Nurul Hazwani Binti Said', 3, 'P', NULL, NULL, NULL),
('120605023367', 'A223098', '$2y$10$tlYXsmOopz50edWVlcIcKetqalP.zO3ysdPdan7BZ.ldZBnaCCRJC', 'Lily Manoban Sammy', 1, 'Lelaki', '2012-05-06', '2025-02-17', '0132264492'),
('120606135547', 'A555683', '$2y$10$CnouXqqTJOXnj85rZkGYquBX7bzpz/vYF52EwFduzf9THXRLy9MfC', 'Siti Maimunah Binti Haikal', 3, 'P', NULL, NULL, NULL),
('120711056302', 'A883096', '$2y$10$qJtWZoCBHnYYgLWUu7./tehrzSjp0R2agD2rWZulkM34SZRatYjJ6', 'Nurul Hitam Binti Lolo', 3, 'P', NULL, NULL, NULL),
('140219012568', 'A258961', '$2y$10$8ErQmlf60cNuq1VTgH7pH.iKzHq8O1U.HZ.aHUGKGl2gvqZOisrMm', 'Yap Jun Hao', 3, 'Lelaki', '2014-02-19', '2025-02-17', '0126698853'),
('160406028234', 'A040616', '$2y$10$4qbNHCqeCK51nZxhkf1sZevM0PogtIqzB7e/Ih6HXVlPztaY.xZLa', 'Mimi Liyana Binti Muhammad Arif', 1, 'Perempuan', '2016-04-06', '2025-02-17', '0165432260'),
('170101010101', 'A012598', '$2y$10$xCcXe6byfHWVw9k3IJsoZ.pofDpuad0nALU0oQPLTHewwj0t3yMDW', 'Ahmad Faiz Bin Ali', 1, 'Lelaki', '2017-01-01', '2025-02-17', '0123456789'),
('180202020202', 'A963257', '$2y$10$809gfbjKUhord7F04xIVX.1NpV/68LgeWYWuz2nC3RvOpY7lcoa1y', 'Nur Aisyah Binti Zainal', 2, 'Perempuan', '2018-02-02', '2025-02-17', '0198765432'),
('190303030303', 'A368521', '$2y$10$jmqtFq8AGBwjMsvo8OEhgeq3aq.7pDcOJMqOhgm5uGqxbGlW3AibK', 'Lim Wei Jie', 3, 'Lelaki', '2019-03-03', '2025-03-03', '0181234567'),
('210505050505', 'A279301', '$2y$10$2pjCSQBQ1aV3WjR9jfQ.4e4AdpIsd8xpdZRDpZRg9wh5DtzzBEWUy', 'Muhammad Danish Bin Hakim', 1, 'Lelaki', '2021-05-05', '2025-03-05', '0142222333'),
('220606060606', 'A239712', '$2y$10$kTicimPGHBaGyeRpxPV05.k2Lo.Rhu9z8GbH09F7rvs4PDV/bZiW6', 'Tan Mei Ling', 2, 'Perempuan', '2022-06-06', '2025-03-06', '0153333444'),
('230707070707', 'A360258', '$2y$10$YzncCh22jy8M9quYzcYFWewcD.Vuhyw7uyeQH4aUl4d8H/wN1kOHy', 'Arjun Kumar', 3, 'Lelaki', '2023-07-07', '2025-03-07', '0164444555'),
('240808080808', 'A239601', '$2y$10$otQwBrmMgXIyon20HYeRD.4SXnZZd2tlK6gH9xKIi597Ctk2Sr1na', 'Aisyah Humaira', 3, 'Perempuan', '2024-08-08', '2025-03-08', '0175555666'),
('250909090909', 'A253673', '$2y$10$r8sUhjGasiFuz4mRGeLoI.fDyB1EWLW0CsRzUPIQnqq56IsA5s02S', 'Lim Jia Hao', 1, 'Lelaki', '2025-09-09', '2025-03-09', '0186666777');

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
('110306132258', 6, ''),
('170101010101', 1, 'president'),
('170101010101', 3, ''),
('170101010101', 4, ''),
('170101010101', 6, 'vice_president');

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
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `event_id` (`event_id`);

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
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `cocu_activities`
--
ALTER TABLE `cocu_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `cocu_events`
--
ALTER TABLE `cocu_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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
  ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_ic`) ON DELETE CASCADE,
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
