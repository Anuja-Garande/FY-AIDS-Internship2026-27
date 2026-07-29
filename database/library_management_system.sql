-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 20, 2026 at 09:50 PM
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
-- Database: `library_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'Admin', 'admin@gmail.com', 'admin123', '2026-07-19 15:49:59');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(100) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `category_id` int(11) NOT NULL,
  `publisher` varchar(100) NOT NULL,
  `publication_year` int(4) NOT NULL,
  `quantity` int(11) NOT NULL,
  `available_quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `title`, `author`, `isbn`, `category_id`, `publisher`, `publication_year`, `quantity`, `available_quantity`, `created_at`) VALUES
(2, 'Python Programming', 'John Zelle', 'ISBN001', 1, 'Pearson', 0, 10, 10, '2026-07-20 19:25:30'),
(3, 'Artificial Intelligence: A Modern Approach', 'Stuart Russell', 'ISBN002', 2, 'Pearson', 0, 8, 8, '2026-07-20 19:25:30'),
(4, 'Data Science from Scratch', 'Joel Grus', 'ISBN003', 3, 'O Reilly', 0, 12, 12, '2026-07-20 19:25:30'),
(5, 'Database System Concepts', 'Abraham Silberschatz', 'ISBN004', 4, 'McGraw Hill', 0, 7, 7, '2026-07-20 19:25:30'),
(6, 'HTML and CSS Design', 'Jon Duckett', 'ISBN005', 5, 'Wiley', 0, 15, 15, '2026-07-20 19:25:30'),
(7, 'Higher Engineering Mathematics', 'B.S. Grewal', 'ISBN006', 6, 'Khanna Publishers', 0, 20, 20, '2026-07-20 19:25:30'),
(8, 'Electronic Devices and Circuits', 'David Bell', 'ISBN007', 7, 'Oxford', 0, 9, 9, '2026-07-20 19:25:30'),
(9, 'Computer Networks', 'Andrew Tanenbaum', 'ISBN008', 8, 'Pearson', 0, 11, 11, '2026-07-20 19:25:30'),
(10, 'Cyber Security Essentials', 'Charles Brooks', 'ISBN009', 9, 'Cengage', 0, 6, 6, '2026-07-20 19:25:30'),
(11, 'Software Engineering', 'Ian Sommerville', 'ISBN010', 10, 'Pearson', 0, 10, 10, '2026-07-20 19:25:30');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `created_at`) VALUES
(42, 'Programming', 'Books related to programming languages like Python, Java, C++', '2026-07-20 19:24:39'),
(43, 'Artificial Intelligence', 'AI, Machine Learning and Deep Learning books', '2026-07-20 19:24:39'),
(44, 'Data Science', 'Data analysis, statistics and data science books', '2026-07-20 19:24:39'),
(45, 'Database Management', 'SQL, MySQL and database system books', '2026-07-20 19:24:39'),
(46, 'Web Development', 'HTML, CSS, JavaScript and PHP books', '2026-07-20 19:24:39'),
(47, 'Mathematics', 'Engineering mathematics and advanced mathematics books', '2026-07-20 19:24:39'),
(48, 'Electronics', 'Electronics and communication technology books', '2026-07-20 19:24:39'),
(49, 'Computer Networks', 'Networking and communication system books', '2026-07-20 19:24:39'),
(50, 'Cyber Security', 'Network security and ethical hacking books', '2026-07-20 19:24:39'),
(51, 'Software Engineering', 'Software development and project management books', '2026-07-20 19:24:39');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `rating` int(1) NOT NULL,
  `created_id` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `student_id`, `subject`, `message`, `rating`, `created_id`) VALUES
(2, 1, '', 'Library service is very good and books are easily available.', 5, '2026-07-20 19:29:19'),
(3, 2, '', 'Need more AI and Data Science books in the library.', 4, '2026-07-20 19:29:19'),
(4, 3, '', 'The digital library system is user friendly.', 5, '2026-07-20 19:29:19'),
(5, 4, '', 'Please add more reference books for engineering subjects.', 4, '2026-07-20 19:29:19'),
(6, 5, '', 'Book searching feature is helpful.', 5, '2026-07-20 19:29:19'),
(7, 6, '', 'Overall experience with library is excellent.', 5, '2026-07-20 19:29:19');

-- --------------------------------------------------------

--
-- Table structure for table `issued_books`
--

CREATE TABLE `issued_books` (
  `issue_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `fine` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `issued_books`
--

INSERT INTO `issued_books` (`issue_id`, `student_id`, `book_id`, `issue_date`, `due_date`, `return_date`, `status`, `fine`) VALUES
(2, 1, 1, '2026-07-16', '0000-00-00', '2026-07-30', 'Issued', 0.00),
(3, 2, 2, '2026-07-17', '0000-00-00', '2026-07-31', 'Issued', 0.00),
(4, 3, 3, '2026-07-18', '0000-00-00', '2026-08-01', 'Returned', 0.00),
(5, 4, 4, '2026-07-19', '0000-00-00', '2026-08-02', 'Issued', 0.00),
(6, 5, 5, '2026-07-20', '0000-00-00', '2026-08-03', 'Issued', 0.00),
(7, 6, 6, '2026-07-21', '0000-00-00', '2026-08-04', 'Returned', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `librarian`
--

CREATE TABLE `librarian` (
  `librarian_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `address` varchar(255) NOT NULL,
  `created_id` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `librarian`
--

INSERT INTO `librarian` (`librarian_id`, `name`, `email`, `password`, `phone`, `address`, `created_id`) VALUES
(2, 'Rahul Patil', 'rahul.librarian@gmail.com', 'rahul123', '9876543210', '', '2026-07-20 19:21:56'),
(3, 'Sneha Joshi', 'sneha.librarian@gmail.com', 'sneha123', '9876543211', '', '2026-07-20 19:21:56'),
(4, 'Amit Shah', 'amit.librarian@gmail.com', 'amit123', '9876543212', '', '2026-07-20 19:21:56');

-- --------------------------------------------------------

--
-- Table structure for table `returned_books`
--

CREATE TABLE `returned_books` (
  `return_id` int(11) NOT NULL,
  `issue_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `return_date` date NOT NULL,
  `fine_paid` decimal(10,2) NOT NULL,
  `remarks` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returned_books`
--

INSERT INTO `returned_books` (`return_id`, `issue_id`, `student_id`, `book_id`, `return_date`, `fine_paid`, `remarks`) VALUES
(2, 3, 3, 3, '2026-07-25', 0.00, ''),
(3, 6, 6, 6, '2026-07-28', 0.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` varchar(100) NOT NULL,
  `year` int(2) NOT NULL,
  `division` varchar(10) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `created_id` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `name`, `email`, `password`, `department`, `year`, `division`, `phone`, `created_id`) VALUES
(1, 'Komal Ghandge', 'komal@gmail.com', 'student123', 'AI & Data Science', 2, 'E', '9876543211', '2026-07-19 16:18:37'),
(2, 'Komal Ghandge', 'komal.ghandge@gmail.com', 'komal123', 'AI & Data Science', 0, 'E', '9876500011', '2026-07-20 19:23:53'),
(3, 'Janhavi Kadu', 'janhavi.kadu@gmail.com', 'janhavi123', 'AI & Data Science', 0, 'E', '9876500012', '2026-07-20 19:23:53'),
(4, 'Janhavi Konnur', 'janhavi.konnur@gmail.com', 'janhavi123', 'AI & Data Science', 0, 'E', '9876500013', '2026-07-20 19:23:53'),
(5, 'Radha Ghogare', 'radha.ghogare@gmail.com', 'radha123', 'AI & Data Science', 0, 'E', '9876500014', '2026-07-20 19:23:53'),
(6, 'Tejas Kolhe', 'tejas.kolhe@gmail.com', 'tejas123', 'AI & Data Science', 0, 'E', '9876500015', '2026-07-20 19:23:53'),
(7, 'Om Jagtap', 'om.jagtap@gmail.com', 'om123', 'AI & Data Science', 0, 'E', '9876500016', '2026-07-20 19:23:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD UNIQUE KEY `isbn` (`isbn`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`);

--
-- Indexes for table `issued_books`
--
ALTER TABLE `issued_books`
  ADD PRIMARY KEY (`issue_id`);

--
-- Indexes for table `librarian`
--
ALTER TABLE `librarian`
  ADD PRIMARY KEY (`librarian_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `returned_books`
--
ALTER TABLE `returned_books`
  ADD PRIMARY KEY (`return_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `issued_books`
--
ALTER TABLE `issued_books`
  MODIFY `issue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `librarian`
--
ALTER TABLE `librarian`
  MODIFY `librarian_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `returned_books`
--
ALTER TABLE `returned_books`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
