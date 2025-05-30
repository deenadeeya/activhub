-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 28, 2025 at 04:10 PM
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
(1, 'Red Crescent Youth', 'uniform_bodies', 'something', 'logos/red_crescent.png', 'SITI NUR AISYAH BINTI AIMAN', '910711028452', '05010101004', '05010101014', '05010101005', '05010101002', NULL, NULL, NULL, NULL, NULL),
(3, 'Photography Club', 'clubs_associations', 'A club for students interested in photography and visual storytelling.', 'logos/photography.png', 'ONG LIN', '780513503890', '05010101008', '05010101007', '05010101016', '05010101001', NULL, NULL, NULL, NULL, NULL),
(4, 'Prefects Guild', 'others', NULL, 'logos/logo.png', 'MUALLIM WAN BIN ABU BAKAR', '770809147765', '05010101012', '05010101013', '05010101009', '05010101010', NULL, NULL, NULL, NULL, NULL),
(6, 'Silat', 'sports', 'Mengajar murid-murid silat.', 'logos/1747660443_Silat.png', 'Encik Syed', '4235351616', '16164364616', '146143636161', '16146666346136', '16436666116', NULL, NULL, NULL, NULL, NULL),
(10, 'Chess Club', 'clubs_associations', 'Chess club for strategic minds.', 'logos/logo.png', 'ONG LIN', '780513503890', '170101010101', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'Netball Team', 'sports', 'Netball team for girls.', 'logos/logo.png', 'SITI NUR AISYAH BINTI AIMAN', '910711028452', '180202020202', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'Robotics Club', 'clubs_associations', 'Robotics and engineering club.', 'logos/logo.png', 'AIMAN MISKIN BIN ABU', '800811023984', '190303030303', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 'Badminton Club', 'sports', 'Badminton training and tournaments.', 'logos/logo.png', 'LEE CHONG WEI', '800101010101', '200404040404', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 'Drama Society', 'clubs_associations', 'Drama and performing arts.', 'logos/logo.png', 'FATIMAH YUSOF', '820202020202', '210505050505', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 'Science Club', 'clubs_associations', 'Science experiments and fairs.', 'logos/logo.png', 'DR. TAN', '830303030303', '220606060606', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 'Football Team', 'sports', 'School football team.', 'logos/1748274676_football.png', 'ENCIK AZMAN', '840404040404', '230707070707', '', '', '', NULL, NULL, NULL, NULL, NULL),
(24, 'Art Club', 'clubs_associations', 'Art and creativity club.', 'logos/logo.png', 'PUAN SITI', '850505050505', '240808080808', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(25, 'Music Band', 'clubs_associations', 'School music band.', 'logos/logo.png', 'MR. LIM', '860606060606', '250909090909', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(29, 'Astra Militarum', 'uniform_bodies', 'Long live Nadeeya', 'logos/1748274973_s-l400.jpg', 'Lion El Johnson', '770809147765', '170101010101', '200404040404', '2', '220606060606', NULL, NULL, NULL, NULL, NULL),
(32, 'Ultramarines', 'others', 'For the Glory of Ultramar', 'logos/1748439938_gh-ultramarines-icon.png', 'ONG LIN', '780513503890', '210505050505', '160406028234', '220606060606', NULL, NULL, NULL, NULL, NULL, '250909090909');

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
(19, '160406028234', 'Test', 'Lain-Lain', '2222-02-22', 'Kebangsaan', 'Kuala Lumpur', 'Universiti Teknologi Malaysia', 'uploads/certificates/2025 MJIIT Japan Day Volunteer (1).pdf', '2025-05-24 08:48:29', 'Johan', 'rejected', NULL, NULL, 1),
(20, '160406028234', 'Test2', 'Lain-Lain', '2222-02-22', 'Kebangsaan', 'Kuala Lumpur', 'Universiti Teknologi Malaysia', 'uploads/certificates/2025 MJIIT Japan Day Volunteer (1).pdf', '2025-05-24 08:48:40', 'Johan', 'approved', '770809147765', '2025-05-24 10:51:49', 1),
(21, '160406028234', 'Test3', 'Lain-Lain', '2222-02-22', 'Kebangsaan', 'Kuala Lumpur', 'Universiti Teknologi Malaysia', 'uploads/certificates/2025 MJIIT Japan Day Volunteer (1).pdf', '2025-05-24 08:48:52', 'Johan', 'approved', '770809147765', '2025-05-24 10:49:10', 1),
(22, '160406028234', 'English Matsuri 2025', 'Lain-Lain', '2222-02-22', 'Kebangsaan', 'Kuala Lumpur', 'Universiti Teknologi Malaysia', 'uploads/certificates/2025 MJIIT Japan Day Volunteer (1).pdf', '2025-05-24 09:15:21', 'Johan', 'approved', '770809147765', '2025-05-24 11:16:21', 1),
(23, '160406028234', 'English Matsuri 2025 Spelling Bee', 'Lain-Lain', '2222-02-22', 'Kebangsaan', 'Kuala Lumpur', 'Universiti Teknologi Malaysia', 'uploads/certificates/2025 MJIIT Japan Day Volunteer (1).pdf', '2025-05-24 09:15:32', 'Johan', 'rejected', NULL, NULL, 1),
(24, '170101010101', 'Chess Tournament', 'Kelab', '2025-04-15', 'Naib Johan', 'Shah Alam', 'Chess Club', 'uploads/certificates/2025 MJIIT Japan Day Volunteer (1).pdf', '2025-05-25 15:08:36', 'Naib Johan', 'approved', '770809147765', '2025-05-01 10:00:00', 0),
(25, '180202020202', 'Netball Interclass', 'Sukan', '2025-03-20', 'Penyertaan', 'Petaling Jaya', 'Netball Team', 'uploads/certificates/2025 MJIIT Japan Day Volunteer (1).pdf', '2025-05-25 15:08:36', 'Penyertaan', 'approved', '910711028452', '2025-05-02 11:00:00', 1),
(26, '190303030303', 'Robotics Competition', 'Kelab', '2025-05-10', 'Johan', 'Kepong', 'Robotics Club', 'uploads/certificates/2025 MJIIT Japan Day Volunteer (1).pdf', '2025-05-25 15:08:36', 'Johan', 'approved', '800811023984', '2025-05-03 12:00:00', 0),
(27, '200404040404', 'Badminton Tournament', 'Sukan', '2025-04-10', 'Penyertaan', 'Klang', 'Badminton Club', 'uploads/certificates/2025 MJIIT Japan Day Volunteer (1).pdf', '2025-05-25 15:32:58', 'Penyertaan', 'approved', '910711028452', '2025-05-10 09:00:00', 0),
(28, '210505050505', 'Drama Competition', 'Kelab', '2025-03-15', 'Naib Johan', 'Subang Jaya', 'Drama Society', 'uploads/certificates/2025 MJIIT Japan Day Volunteer (1).pdf', '2025-05-25 15:32:58', 'Naib Johan', 'approved', '770809147765', '2025-05-11 10:00:00', 0),
(29, '220606060606', 'Science Fair', 'Kelab', '2025-05-20', 'Johan', 'Puchong', 'Science Club', 'uploads/certificates/2025 MJIIT Japan Day Volunteer (1).pdf', '2025-05-25 15:32:58', 'Johan', 'approved', '800811023984', '2025-05-12 11:00:00', 0),
(30, '230707070707', 'Football League', 'Sukan', '2025-06-01', 'Penyertaan', 'Kajang', 'Football Team', 'uploads/certificates/2025 MJIIT Japan Day Volunteer (1).pdf', '2025-05-25 15:32:58', 'Penyertaan', 'approved', '910711028452', '2025-05-13 12:00:00', 1),
(31, '240808080808', 'Art Exhibition', 'Kelab', '2025-04-25', 'Penyertaan', 'Ampang', 'Art Club', 'uploads/certificates/2025 MJIIT Japan Day Volunteer (1).pdf', '2025-05-25 15:32:58', 'Penyertaan', 'approved', '770809147765', '2025-05-14 13:00:00', 0),
(32, '250909090909', 'Music Festival', 'Kelab', '2025-05-30', 'Penyertaan', 'Cheras', 'Music Band', 'uploads/certificates/2025 MJIIT Japan Day Volunteer (1).pdf', '2025-05-25 15:32:58', 'Penyertaan', 'approved', '800811023984', '2025-05-15 14:00:00', 0),
(33, '210505050505', 'Memasak', 'Kelab', '2025-05-14', 'Sekolah', 'SRIAAWP', 'SRIAAWP', 'uploads/certificates/2025 MJIIT Japan Day Volunteer.pdf', '2025-05-25 15:52:01', 'Johan', 'approved', '770809147765', '2025-05-25 17:54:21', 0),
(34, '160406028234', 'Test4', 'Lain-Lain', '2025-02-22', 'Kebangsaan', 'Kuala Lumpur', 'Universiti Teknologi Malaysia', 'uploads/certificates/2025 MJIIT Japan Day Volunteer (1).pdf', '2025-05-26 15:26:37', 'Johan', 'approved', '770809147765', '2025-05-26 17:27:04', 1),
(35, '160406028234', 'English Matsuri 2025', 'Lain-Lain', '2025-06-02', 'Kebangsaan', 'Kuala Lumpur', 'Universiti Teknologi Malaysia', 'uploads/certificates/2025 MJIIT Japan Day Volunteer (1).pdf', '2025-05-27 07:46:59', 'Johan', 'approved', '770809147765', '2025-05-27 09:48:14', 1);

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
  `group_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `event_name`, `event_start_date`, `event_end_date`, `event_venue`, `registration_deadline`, `contact_number`, `group_id`) VALUES
(4, 'English Matsuri', '2025-08-27', '2026-02-23', 'MJIIT', '2025-09-29', '011-65546909', NULL),
(7, 'Arab Language Festival 2025', '2025-07-15', '2025-07-16', 'Dewan Bestari', '2025-07-10', '0123456789', 10),
(8, 'STEM Innovation Day', '2025-08-10', '2025-08-10', 'Makmal Sains', '2025-08-05', '0198765432', 22),
(9, 'Sports Carnival', '2025-09-05', '2025-09-06', 'Padang Sekolah', '2025-09-01', '0131111222', 20),
(10, 'Art & Music Night', '2025-11-12', '2025-11-12', 'Auditorium', '2025-11-05', '0186666777', 25),
(11, 'UNBOCS 2025', '2025-05-15', '2025-05-15', 'Dewan Bestari', '2025-05-10', '0123456789', 10),
(12, 'SUKMAS', '2025-05-20', '2025-05-20', 'Padang SRIAWWP', '2025-05-20', '0186666777', 25),
(13, 'wutduhelly', '2025-06-02', '2026-06-03', 'MJIIT', '2025-06-09', '011-65546909', 29),
(14, 'English Matsuri', '2025-06-02', '2025-07-03', 'MJIIT', '2025-06-05', '011-65546909', 21);

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
(4, '160406028234', 4),
(6, '160406028234', 10),
(7, '230707070707', 8),
(8, '230707070707', 7),
(9, '210505050505', 7),
(10, '160406028234', 14);

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
  `contact_num` varchar(255) DEFAULT NULL,
  `teacher_incharge` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_ic`, `student_pass`, `student_fname`, `student_class`, `student_dob`, `student_doe`, `contact_num`, `teacher_incharge`) VALUES
('160406028234', '$2y$10$BsXZ3ZeOn3XjR9fTSne5cOb4tTDRtZkSdeEdhJub225uI.EVh/swe', 'Mimi Liyana Bint Muhammad Arif', 3, '2016-04-06', '2025-02-17', '0165432261', '770809147765'),
('170101010101', '$2y$10$cnf6pzkcEFbMp1C3GJrKHeUcp8Apf7bzIynmkKi4hmBEzCUmF8YMC', 'Ahmad Faiz Bin Ali', 2, '2017-01-01', '2025-03-01', '0123456789', '910711028452'),
('180202020202', '$2y$10$NoVMnUCyh8lBLaz7vnjoiunYdfhI.85kp4LNjcvlHuxQnOix0WrhO', 'Nur Aisyah Binti Zainal', 3, '2018-02-02', '2025-03-02', '0198765432', '770809147765'),
('190303030303', '$2y$10$tMo/5qHgJVwIrRD4cGAzXOzoOrn79RGwp/7ORGBRMgWM61f2mftuu', 'Lim Wei Jie', 5, '2019-03-03', '2025-03-03', '0181234567', '800811023984'),
('200404040404', '$2y$10$waKXly.ZyzRD1t7Fyi3tkuEgG1BXfEGlWLyYt1aV.9dN.OWKivxrO', 'Siti Nurul Huda', 2, '2020-04-04', '2025-03-04', '0131111222', '910711028452'),
('210505050505', '$2y$10$c16sNTKgowE9w9TeC05qNeYwnLDg9i9Fk0Fp8d/rNpmAAIgv7.j.6', 'Muhammad Danish', 3, '2021-05-05', '2025-03-05', '0142222333', '770809147765'),
('220606060606', '$2y$10$axkq/1MCQjMiSJ7JnWVzyeJAy7ZJbJ2JRJM5Xpbzb1UOjtrPc2Qxy', 'Tan Mei Ling', 5, '2022-06-06', '2025-03-06', '0153333444', '800811023984'),
('230707070707', '$2y$10$bFxaP3T565NGnCSH3MyKleyicNcU7.qVxnP4yVFMxSFk7ED50I/gG', 'Arjun Kumar', 6, '2023-07-07', '2025-03-07', '0164444555', '910711028452'),
('240808080808', '$2y$10$j30jB855nn.wvJQwewONB.iAEjNf9t4s0VCUVrkdtvjokBhhO4Ty2', 'Aisyah Humaira', 2, '2024-08-08', '2025-03-08', '0175555666', '770809147765'),
('250909090909', '$2y$10$tog5yBBK/2G1ayftJrY5/OuAjCA.9LgbVOkj4QMyX22LgpPUy..4.', 'Lim Jia Hao', 3, '2025-09-09', '2025-03-09', '0186666777', '800811023984');

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
('160406028234', 32, 'vice_president'),
('170101010101', 10, 'president'),
('170101010101', 29, 'president'),
('170101010101', 32, 'member'),
('180202020202', 11, 'president'),
('190303030303', 12, 'president'),
('190303030303', 32, 'member'),
('200404040404', 20, 'president'),
('200404040404', 29, 'vice_president'),
('210505050505', 21, 'president'),
('210505050505', 32, 'president'),
('220606060606', 22, 'president'),
('220606060606', 29, 'treasurer'),
('220606060606', 32, 'secretary'),
('230707070707', 23, 'president'),
('240808080808', 24, 'president'),
('250909090909', 25, 'president'),
('250909090909', 32, 'exco_y4');

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
  `class` int(11) NOT NULL,
  `teacher_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`teacher_ic`, `teacher_pass`, `teacher_fname`, `teacher_contact`, `teacher_email`, `teacher_dob`, `teacher_doe`, `teacher_address`, `class`, `teacher_pic`) VALUES
('770809147765', '$2y$10$7/YlNtaMldl1mUFjWwsDkOqFkOoX/uZn.dfHlhF/aXHTSWWAV1DxS', ' MUALLIM WAN BIN ABU BAKAR', ' 0132658897', 'muallimwan@gmail.com', '1977-08-09', '2012-02-01', '21st Floor Plaza Sentral Block C', 3, NULL),
('780513503890', '$2y$10$PTJcWimtUme9rrEEyfeB..7CiVcUrRivEuMilk0AkMFT.NvcMYZK.', 'ONG LIN', '0182331874', NULL, NULL, NULL, '', 3, NULL),
('800811023984', '$2y$10$u.x56/7z1Aw2fd1C7LzT9evLovnYdIwGhqmCBEDb9829eKrsxweP6', 'AIMAN MISKIN BIN ABU', '0165320012', NULL, NULL, NULL, '', 5, NULL),
('910711028452', '$2y$10$EZNdDOrY8VwuMnOiAqkpEeJSXaXBg3lR9wRDCTARKoi9PFYb.SMyG', 'SITI NUR AISYAH BINTI AIMAN', '0196547821', NULL, NULL, NULL, '', 2, NULL);

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
  ADD KEY `student_class` (`student_class`),
  ADD KEY `teacher_incharge` (`teacher_incharge`);

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
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `cocu_activities`
--
ALTER TABLE `cocu_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `cocu_events`
--
ALTER TABLE `cocu_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

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
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`student_class`) REFERENCES `class` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_ibfk_2` FOREIGN KEY (`teacher_incharge`) REFERENCES `teacher` (`teacher_ic`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_club_membership`
--
ALTER TABLE `student_club_membership`
  ADD CONSTRAINT `student_club_membership_ibfk_1` FOREIGN KEY (`student_ic`) REFERENCES `student` (`student_ic`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_club_membership_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `cocurricular_groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teacher`
--
ALTER TABLE `teacher`
  ADD CONSTRAINT `teacher_ibfk_1` FOREIGN KEY (`class`) REFERENCES `class` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
