-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 07, 2024 at 07:34 AM
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
-- Database: `a_4`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_code`, `course_name`) VALUES
(1, 'CS101', 'Introduction to Computer Science'),
(2, 'CS201', 'Data Structures'),
(3, 'ENG101', 'English Composition'),
(4, 'CS301', 'Operating Systems');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`enrollment_id`, `course_id`, `student_id`) VALUES
(32, 4, 'bc1902000044'),
(33, 4, 'bc1902000045'),
(34, 4, 'bc1902000044'),
(35, 4, 'bc1902000045'),
(36, 4, 'bc1902000046'),
(37, 1, 'bc1902000044'),
(38, 1, 'bc1902000045'),
(39, 1, 'bc1902000044'),
(40, 1, 'bc1902000045'),
(41, 1, 'bc1902000046'),
(52, 2, 'bc1902000044'),
(53, 2, 'bc1902000045'),
(54, 2, 'bc1902000044'),
(55, 2, 'bc1902000045'),
(56, 2, 'bc1902000046'),
(57, 3, 'bc19020000'),
(58, 3, 'bc19020000'),
(59, 3, 'bc19020000'),
(60, 3, 'bc19020000'),
(61, 3, 'bc19020000');

-- --------------------------------------------------------

--
-- Table structure for table `exam_halls`
--

CREATE TABLE `exam_halls` (
  `id` int(11) NOT NULL,
  `building` varchar(100) NOT NULL,
  `floor` int(11) NOT NULL,
  `hall_number` varchar(50) NOT NULL,
  `seating_capacity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_halls`
--

INSERT INTO `exam_halls` (`id`, `building`, `floor`, `hall_number`, `seating_capacity`) VALUES
(1, 'Main Building', 1, 'Hall 101', 5),
(2, 'Main Building', 2, 'Hall 201', 10),
(3, 'Science Block', 1, 'Lab 1', 2),
(6, 'test', 2, '3', 34);

-- --------------------------------------------------------

--
-- Table structure for table `exam_schedule`
--

CREATE TABLE `exam_schedule` (
  `course_code` varchar(50) DEFAULT NULL,
  `slot` int(11) DEFAULT NULL,
  `day` varchar(50) DEFAULT NULL,
  `exam_hall` varchar(100) DEFAULT NULL,
  `superintendent` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_schedules`
--

CREATE TABLE `exam_schedules` (
  `id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `exam_slot_id` int(11) NOT NULL,
  `scheduled_on` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_slots`
--

CREATE TABLE `exam_slots` (
  `id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `time_range` varchar(50) NOT NULL,
  `slot_number` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `superintendents`
--

CREATE TABLE `superintendents` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `superintendents`
--

INSERT INTO `superintendents` (`id`, `name`, `designation`, `department`) VALUES
(1, 'Dr. Adams', 'Professor', 'Computer Science'),
(2, 'Dr. Baker', 'Assistant Professor', 'English Department'),
(6, 'naveed', 'superitendent', 'science');

-- --------------------------------------------------------

--
-- Table structure for table `superintendent_courses`
--

CREATE TABLE `superintendent_courses` (
  `id` int(11) NOT NULL,
  `superintendent_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `semester` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `superintendent_courses`
--

INSERT INTO `superintendent_courses` (`id`, `superintendent_id`, `course_id`, `semester`) VALUES
(14, 1, 2, NULL),
(16, 2, 1, NULL),
(29, 6, 3, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `time_slots`
--

CREATE TABLE `time_slots` (
  `slot_id` int(11) NOT NULL,
  `slot_time` time NOT NULL,
  `weekday` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `time_slots`
--

INSERT INTO `time_slots` (`slot_id`, `slot_time`, `weekday`) VALUES
(1, '05:18:00', 'Saturday');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','coordinator','superintendent') NOT NULL,
  `approved` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `approved`) VALUES
(1, 'hamza@123', '$2y$10$8U0sLYfIe3hSOiqgxmmw4uLHOlKEIVoyy6LOief4iW/uID6C5UbL6', 'admin', 1),
(2, 'mansoor1', '$2y$10$29U8aGP7oNQclka4ISkG0O8hnysW4Weu8sevDwYfw15Mn6w0N0VoO', 'superintendent', 1),
(5, 'sultan123', '$2y$10$tgYextEhl3O.Zmpdhi/gkO3jdOgJt662p5Gtp8489rz821COOBSgW', 'coordinator', 1),
(7, 'hamza', '$2y$10$ykkVwH9zcIWFTmSQG0Zg.u.RZ.X5ZgHHiIZvlsZFqck0bIeSlXGXO', 'coordinator', 1),
(8, 'tab', '$2y$10$axIxwZOC5gHCYLG7jLYPs.PhUFy0HNCc25ptef1yhY2mn9G.7trsW', 'superintendent', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `exam_halls`
--
ALTER TABLE `exam_halls`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hall_number` (`hall_number`);

--
-- Indexes for table `exam_schedules`
--
ALTER TABLE `exam_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enrollment_id` (`enrollment_id`),
  ADD KEY `exam_slot_id` (`exam_slot_id`);

--
-- Indexes for table `exam_slots`
--
ALTER TABLE `exam_slots`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `superintendents`
--
ALTER TABLE `superintendents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `superintendent_courses`
--
ALTER TABLE `superintendent_courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `superintendent_id` (`superintendent_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`slot_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `exam_halls`
--
ALTER TABLE `exam_halls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `exam_schedules`
--
ALTER TABLE `exam_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_slots`
--
ALTER TABLE `exam_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `superintendents`
--
ALTER TABLE `superintendents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `superintendent_courses`
--
ALTER TABLE `superintendent_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `slot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `exam_schedules`
--
ALTER TABLE `exam_schedules`
  ADD CONSTRAINT `exam_schedules_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `exam_schedules_ibfk_2` FOREIGN KEY (`exam_slot_id`) REFERENCES `exam_slots` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `superintendent_courses`
--
ALTER TABLE `superintendent_courses`
  ADD CONSTRAINT `superintendent_courses_ibfk_1` FOREIGN KEY (`superintendent_id`) REFERENCES `superintendents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `superintendent_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
