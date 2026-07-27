
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


-- Database: `yawatim_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `booths`
--

CREATE TABLE `booths` (
  `booth_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `state` varchar(100) NOT NULL,
  `channel` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booths`
--

INSERT INTO `booths` (`booth_id`, `name`, `location`, `state`, `channel`, `status`, `created_at`) VALUES
(1, 'BSN Mid Valley', 'Mid Valley Megamall, KL', 'WP Kuala Lumpur', 'BSN', 'Active', '2026-07-13 05:38:05'),
(2, 'BSN SS2 PJ', 'Jalan SS2/24, Petaling Jaya', 'Selangor', 'BSN', 'Active', '2026-07-13 05:38:05'),
(3, 'BSN Shah Alam', 'Seksyen 14, Shah Alam', 'Selangor', 'BSN', 'Active', '2026-07-13 05:38:05'),
(4, 'Bank Rakyat KLCC', 'Suria KLCC, Kuala Lumpur', 'WP Kuala Lumpur', 'Bank Rakyat', 'Active', '2026-07-13 05:38:05'),
(5, 'Bank Rakyat Chow Kit', 'Jalan TAH, Kuala Lumpur', 'WP Kuala Lumpur', 'Bank Rakyat', 'Active', '2026-07-13 05:38:05'),
(6, 'Bank Rakyat Bangsar', 'Jalan Bangsar, Kuala Lumpur', 'WP Kuala Lumpur', 'Bank Rakyat', 'Active', '2026-07-13 05:38:05'),
(7, 'Pos Malaysia JB Sentral', 'Jalan Wong Ah Fook, JB', 'Johor', 'Pos Malaysia', 'Active', '2026-07-13 05:38:05'),
(8, 'Pos Malaysia Skudai', 'Jalan Skudai, Skudai', 'Johor', 'Pos Malaysia', 'Active', '2026-07-13 05:38:05'),
(9, 'Pos Malaysia Kluang', 'Jalan Besar, Kluang', 'Johor', 'Pos Malaysia', 'Active', '2026-07-13 05:38:05'),
(10, 'EBB George Town', 'Beach Street, George Town', 'Penang', 'EBB', 'Active', '2026-07-13 05:38:05'),
(11, 'EBB Butterworth', 'Jalan Bagan Luar, Butterworth', 'Penang', 'EBB', 'Active', '2026-07-13 05:38:05'),
(12, 'EBB Bayan Baru', 'Bayan Baru Commercial Centre', 'Penang', 'EBB', 'Active', '2026-07-13 05:38:05'),
(13, 'BSN Seremban', 'Jalan Tuanku Munawir, Seremban', 'Negeri Sembilan', 'BSN', 'Active', '2026-07-13 05:45:30'),
(14, 'BSN Ipoh', 'Jalan Sultan Idris Shah, Ipoh', 'Perak', 'BSN', 'Active', '2026-07-13 05:45:30'),
(15, 'BSN Alor Setar', 'Jalan Langgar, Alor Setar', 'Kedah', 'BSN', 'Active', '2026-07-13 05:45:30'),
(16, 'BSN Kota Bharu', 'Jalan Doktor, Kota Bharu', 'Kelantan', 'BSN', 'Active', '2026-07-13 05:45:30'),
(17, 'BSN Kuching', 'Jalan Tun Razak, Kuching', 'Sarawak', 'BSN', 'Active', '2026-07-13 05:45:30'),
(18, 'Bank Rakyat Johor Bahru', 'Jalan Dato Onn, JB', 'Johor', 'Bank Rakyat', 'Active', '2026-07-13 05:45:30'),
(19, 'Bank Rakyat Ipoh', 'Jalan CM Yusuf, Ipoh', 'Perak', 'Bank Rakyat', 'Active', '2026-07-13 05:45:30'),
(20, 'Bank Rakyat Kota Kinabalu', 'Jalan Gaya, KK', 'Sabah', 'Bank Rakyat', 'Active', '2026-07-13 05:45:30'),
(21, 'Bank Rakyat Kuantan', 'Jalan Mahkota, Kuantan', 'Pahang', 'Bank Rakyat', 'Active', '2026-07-13 05:45:30'),
(22, 'Bank Rakyat Kuching', 'Jalan Song, Kuching', 'Sarawak', 'Bank Rakyat', 'Active', '2026-07-13 05:45:30'),
(23, 'Pos Malaysia Penang', 'Lebuh Downing, George Town', 'Penang', 'Pos Malaysia', 'Active', '2026-07-13 05:45:30'),
(24, 'Pos Malaysia Seremban', 'Jalan Dato Abdul Wahab, Seremban', 'Negeri Sembilan', 'Pos Malaysia', 'Active', '2026-07-13 05:45:30'),
(25, 'Pos Malaysia Kota Kinabalu', 'Jalan Haji Saman, KK', 'Sabah', 'Pos Malaysia', 'Active', '2026-07-13 05:45:30'),
(26, 'Pos Malaysia Kuantan', 'Jalan Besar, Kuantan', 'Pahang', 'Pos Malaysia', 'Active', '2026-07-13 05:45:30'),
(27, 'Pos Malaysia Melaka', 'Jalan Kota, Melaka', 'Melaka', 'Pos Malaysia', 'Active', '2026-07-13 05:45:30'),
(28, 'EBB Kuala Lumpur', 'Jalan Raja Laut, KL', 'WP Kuala Lumpur', 'EBB', 'Active', '2026-07-13 05:45:30'),
(29, 'EBB Johor Bahru', 'Jalan Stulang Darat, JB', 'Johor', 'EBB', 'Active', '2026-07-13 05:45:30'),
(30, 'EBB Shah Alam', 'Jalan Kemajuan, Shah Alam', 'Selangor', 'EBB', 'Active', '2026-07-13 05:45:30'),
(31, 'EBB Kota Kinabalu', 'Jalan Lintas, KK', 'Sabah', 'EBB', 'Active', '2026-07-13 05:45:30'),
(32, 'EBB Kuching', 'Jalan Tabuan, Kuching', 'Sarawak', 'EBB', 'Active', '2026-07-13 05:45:30');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `donation_id` int(11) NOT NULL,
  `donor_name` varchar(255) NOT NULL,
  `donor_phone` varchar(100) DEFAULT NULL,
  `donor_email` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `donation_date` date NOT NULL,
  `donation_month` varchar(20) NOT NULL,
  `channel` varchar(255) DEFAULT NULL,
  `state` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `wakalah_id` int(11) DEFAULT NULL,
  `booth_id` int(11) DEFAULT NULL,
  `attachment_image` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`donation_id`, `donor_name`, `donor_phone`, `donor_email`, `amount`, `donation_date`, `donation_month`, `channel`, `state`, `location`, `wakalah_id`, `booth_id`, `attachment_image`, `created_at`) VALUES
(1, 'Ali bin Abu', '013-5431259', 'alibinabu0@example.com', 120.00, '2026-01-01', 'January', 'BSN', 'Selangor', '-', 5, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(2, 'Tan Kah Kee', '014-8720410', 'tankahkee1@example.com', 250.00, '2026-02-02', 'February', 'Bank Rakyat', 'WP Kuala Lumpur', 'Bank Rakyat Chow Kit', 2, 5, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(3, 'Ramasamy Pillay', '018-7404664', 'ramasamypillay2@example.com', 80.00, '2026-03-03', 'March', 'Pos Malaysia', 'Selangor', '-', 7, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(4, 'Fatimah Md Ali', '017-1567431', 'fatimahmdali3@example.com', 350.00, '2026-04-04', 'April', 'EBB', 'Penang', 'EBB George Town', 4, 10, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(5, 'Haji Ahmad', '012-8463424', 'hajiahmad4@example.com', 175.00, '2026-05-05', 'May', 'BSN', 'Penang', '-', 9, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(6, 'Nur Hafizah', '011-4882007', 'nurhafizah5@example.com', 95.00, '2026-06-06', 'June', 'Bank Rakyat', 'WP Kuala Lumpur', 'Bank Rakyat Bangsar', 2, 6, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(7, 'Lim Siew Ling', '018-7957766', 'limsiewling6@example.com', 210.00, '2026-01-07', 'January', 'Pos Malaysia', 'Selangor', '-', 11, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(8, 'Mohd Faisal', '014-6689209', 'mohdfaisal7@example.com', 145.00, '2026-02-08', 'February', 'EBB', 'Penang', 'EBB Butterworth', 4, 11, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(9, 'Siti Nor', '013-8052429', 'sitinor8@example.com', 420.00, '2026-03-09', 'March', 'BSN', 'Penang', '-', 13, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(10, 'Rajan Kumar', '015-1269878', 'rajankumar9@example.com', 60.00, '2026-04-10', 'April', 'Bank Rakyat', 'WP Kuala Lumpur', 'Bank Rakyat KLCC', 2, 4, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(11, 'Farah Husna', '013-3519569', 'farahhusna10@example.com', 180.00, '2026-05-11', 'May', 'Pos Malaysia', 'Selangor', '-', 15, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(12, 'Wong Ah Seng', '018-6928123', 'wongahseng11@example.com', 220.00, '2026-06-12', 'June', 'EBB', 'Penang', 'EBB Bayan Baru', 4, 12, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(13, 'Ahmad Faizal', '016-5160977', 'ahmadfaizal12@example.com', 130.00, '2026-01-13', 'January', 'BSN', 'Penang', '-', 17, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(14, 'Hana Mei', '011-8003503', 'hanamei13@example.com', 90.00, '2026-02-14', 'February', 'Bank Rakyat', 'WP Kuala Lumpur', 'Bank Rakyat Chow Kit', 2, 5, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(15, 'Suraya Zulkifli', '019-3368189', 'surayazulkifli14@example.com', 305.00, '2026-03-15', 'March', 'Pos Malaysia', 'WP Kuala Lumpur', '-', 19, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(16, 'Leong Wei', '014-9459672', 'leongwei15@example.com', 175.00, '2026-04-16', 'April', 'EBB', 'Penang', 'EBB George Town', 4, 10, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(17, 'Faizal Rahman', '019-4116562', 'faizalrahman16@example.com', 260.00, '2026-05-17', 'May', 'BSN', 'Penang', '-', 21, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(18, 'Chong Mei Yee', '015-3857058', 'chongmeiyee17@example.com', 330.00, '2026-06-18', 'June', 'Bank Rakyat', 'WP Kuala Lumpur', 'Bank Rakyat Bangsar', 2, 6, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(19, 'Zulkifli Omar', '018-4377597', 'zulkifliomar18@example.com', 145.00, '2026-01-19', 'January', 'Pos Malaysia', 'WP Kuala Lumpur', '-', 23, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(20, 'Nadia Binti Nor', '012-9550132', 'nadiabintinor19@example.com', 95.00, '2026-02-20', 'February', 'EBB', 'Penang', 'EBB Butterworth', 4, 11, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:05'),
(21, 'Ismail Putra', '011-8703301', 'ismailputra20@example.com', 210.00, '2026-03-21', 'March', 'BSN', 'Selangor', '-', 5, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(22, 'Marie Tan', '012-3764919', 'marietan21@example.com', 140.00, '2026-04-22', 'April', 'Bank Rakyat', 'WP Kuala Lumpur', 'Bank Rakyat KLCC', 2, 4, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(23, 'Aisyah Farid', '018-9084053', 'aisyahfarid22@example.com', 175.00, '2026-05-23', 'May', 'Pos Malaysia', 'Selangor', '-', 7, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(24, 'Kamarudin Osman', '016-1567892', 'kamarudinosman23@example.com', 290.00, '2026-06-24', 'June', 'EBB', 'Penang', 'EBB Bayan Baru', 4, 12, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(25, 'Joseph Lim', '013-8875153', 'josephlim24@example.com', 95.00, '2026-01-25', 'January', 'BSN', 'Penang', '-', 9, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(26, 'Faridah Mat', '013-3222144', 'faridahmat25@example.com', 205.00, '2026-02-26', 'February', 'Bank Rakyat', 'WP Kuala Lumpur', 'Bank Rakyat Chow Kit', 2, 5, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(27, 'Lee Chen', '013-5829369', 'leechen26@example.com', 330.00, '2026-03-27', 'March', 'Pos Malaysia', 'Selangor', '-', 11, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(28, 'Nadirah Jamal', '018-2190967', 'nadirahjamal27@example.com', 115.00, '2026-04-01', 'April', 'EBB', 'Penang', 'EBB George Town', 4, 10, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(29, 'Malar Krishnan', '016-1107649', 'malarkrishnan28@example.com', 240.00, '2026-05-02', 'May', 'BSN', 'Penang', '-', 13, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(30, 'Saleh Mat', '011-8677592', 'salehmat29@example.com', 185.00, '2026-06-03', 'June', 'Bank Rakyat', 'WP Kuala Lumpur', 'Bank Rakyat Bangsar', 2, 6, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(31, 'Robert Tan', '019-4654553', 'roberttan30@example.com', 500.00, '2026-01-04', 'January', 'Pos Malaysia', 'Selangor', '-', 15, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(32, 'Amirul Osman', '011-1002803', 'amirulosman31@example.com', 150.00, '2026-02-05', 'February', 'EBB', 'Penang', 'EBB Butterworth', 4, 11, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(33, 'Siti Aminah', '016-3112231', 'sitiaminah32@example.com', 200.00, '2026-03-06', 'March', 'BSN', 'Penang', '-', 17, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(34, 'Cheah Kok Wah', '017-3620797', 'cheahkokwah33@example.com', 850.00, '2026-04-07', 'April', 'Bank Rakyat', 'WP Kuala Lumpur', 'Bank Rakyat KLCC', 2, 4, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(35, 'Nor Hazimah', '018-6763714', 'norhazimah34@example.com', 75.00, '2026-05-08', 'May', 'Pos Malaysia', 'WP Kuala Lumpur', '-', 19, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(36, 'Vijay Singh', '017-2876429', 'vijaysingh35@example.com', 120.00, '2026-06-09', 'June', 'EBB', 'Penang', 'EBB Bayan Baru', 4, 12, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(37, 'Zainal Abidin', '018-2035289', 'zainalabidin36@example.com', 310.00, '2026-01-10', 'January', 'BSN', 'Penang', '-', 21, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(38, 'Lucy Liu', '012-1522302', 'lucyliu37@example.com', 400.00, '2026-02-11', 'February', 'Bank Rakyat', 'WP Kuala Lumpur', 'Bank Rakyat Chow Kit', 2, 5, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(39, 'Abu Bakar', '018-7602500', 'abubakar38@example.com', 180.00, '2026-03-12', 'March', 'Pos Malaysia', 'WP Kuala Lumpur', '-', 23, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(40, 'Nurul Izzah', '012-5097015', 'nurulizzah39@example.com', 220.00, '2026-04-13', 'April', 'EBB', 'Penang', 'EBB George Town', 4, 10, 'img/attachment receipt 2.jpg', '2026-07-13 05:38:06'),
(41, 'Azman Zain', '017-4712157', 'azmanzain0@example.com', 100.00, '2026-01-01', 'January', 'BSN', 'WP Kuala Lumpur', 'BSN Mid Valley', 1, 1, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(42, 'Harti Noor', '017-6405870', 'hartinoor1@example.com', 150.00, '2026-02-02', 'February', 'BSN', 'Selangor', 'BSN SS2 PJ', 1, 2, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(43, 'Kevin Lim', '019-5562321', 'kevinlim2@example.com', 200.00, '2026-03-03', 'March', 'BSN', 'Selangor', 'BSN Shah Alam', 1, 3, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(44, 'Priya Devi', '017-4265057', 'priyadevi3@example.com', 250.00, '2026-04-04', 'April', 'BSN', 'Negeri Sembilan', 'BSN Seremban', 1, 13, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(45, 'Salmi Aziz', '015-4706641', 'salmiaziz4@example.com', 300.00, '2026-05-05', 'May', 'BSN', 'Perak', 'BSN Ipoh', 1, 14, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(46, 'Osman Hamid', '015-4634886', 'osmanhamid5@example.com', 350.00, '2026-06-06', 'June', 'BSN', 'Kedah', 'BSN Alor Setar', 1, 15, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(47, 'Celine Wong', '012-4298880', 'celinewong6@example.com', 400.00, '2026-01-07', 'January', 'BSN', 'Kelantan', 'BSN Kota Bharu', 1, 16, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(48, 'Hafiz Jamal', '019-6575156', 'hafizjamal7@example.com', 180.00, '2026-02-08', 'February', 'BSN', 'Sarawak', 'BSN Kuching', 1, 17, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(49, 'Nurul Hana', '016-7345338', 'nurulhana8@example.com', 220.00, '2026-03-09', 'March', 'BSN', 'WP Kuala Lumpur', 'BSN Mid Valley', 1, 1, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(50, 'Bala Murugan', '013-7113766', 'balamurugan9@example.com', 270.00, '2026-04-10', 'April', 'BSN', 'Selangor', 'BSN SS2 PJ', 1, 2, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(51, 'Rashidah Ali', '018-8841607', 'rashidahali10@example.com', 320.00, '2026-05-11', 'May', 'BSN', 'Selangor', 'BSN Shah Alam', 1, 3, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(52, 'Thomas Go', '016-1155997', 'thomasgo11@example.com', 130.00, '2026-06-12', 'June', 'BSN', 'Negeri Sembilan', 'BSN Seremban', 1, 13, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(53, 'Salmah Nor', '018-1843696', 'salmahnor12@example.com', 160.00, '2026-01-13', 'January', 'BSN', 'Perak', 'BSN Ipoh', 1, 14, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(54, 'Razif Zainal', '012-8014799', 'razifzainal13@example.com', 190.00, '2026-02-14', 'February', 'BSN', 'Kedah', 'BSN Alor Setar', 1, 15, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(55, 'Jasmine Lee', '018-7385514', 'jasminelee14@example.com', 240.00, '2026-03-15', 'March', 'BSN', 'Kelantan', 'BSN Kota Bharu', 1, 16, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(56, 'Kamal Ibrahim', '017-6144569', 'kamalibrahim15@example.com', 110.00, '2026-01-01', 'January', 'Bank Rakyat', 'WP Kuala Lumpur', 'Bank Rakyat KLCC', 2, 4, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(57, 'Lily Chan', '019-4762056', 'lilychan16@example.com', 140.00, '2026-02-02', 'February', 'Bank Rakyat', 'WP Kuala Lumpur', 'Bank Rakyat Chow Kit', 2, 5, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(58, 'Wahab Salleh', '014-3165315', 'wahabsalleh17@example.com', 170.00, '2026-03-03', 'March', 'Bank Rakyat', 'WP Kuala Lumpur', 'Bank Rakyat Bangsar', 2, 6, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(59, 'Meena Raj', '014-2948158', 'meenaraj18@example.com', 210.00, '2026-04-04', 'April', 'Bank Rakyat', 'Johor', 'Bank Rakyat Johor Bahru', 2, 18, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(60, 'Adam Bakri', '016-9683713', 'adambakri19@example.com', 260.00, '2026-05-05', 'May', 'Bank Rakyat', 'Perak', 'Bank Rakyat Ipoh', 2, 19, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(61, 'Norain Daud', '019-8088899', 'noraindaud20@example.com', 310.00, '2026-06-06', 'June', 'Bank Rakyat', 'Sabah', 'Bank Rakyat Kota Kinabalu', 2, 20, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(62, 'Vincent Tan', '014-3578750', 'vincenttan21@example.com', 120.00, '2026-01-07', 'January', 'Bank Rakyat', 'Pahang', 'Bank Rakyat Kuantan', 2, 21, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(63, 'Rohani Kassim', '012-7216878', 'rohanikassim22@example.com', 155.00, '2026-02-08', 'February', 'Bank Rakyat', 'Sarawak', 'Bank Rakyat Kuching', 2, 22, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(64, 'Syukri Hassan', '019-7937060', 'syukrihassan23@example.com', 185.00, '2026-03-09', 'March', 'Bank Rakyat', 'WP Kuala Lumpur', 'Bank Rakyat KLCC', 2, 4, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(65, 'Joanna Lim', '017-3186814', 'joannalim24@example.com', 230.00, '2026-04-10', 'April', 'Bank Rakyat', 'WP Kuala Lumpur', 'Bank Rakyat Chow Kit', 2, 5, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(66, 'Faredz Murad', '015-1739440', 'faredzmurad25@example.com', 280.00, '2026-05-11', 'May', 'Bank Rakyat', 'WP Kuala Lumpur', 'Bank Rakyat Bangsar', 2, 6, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(67, 'Aishah Wahab', '012-3194043', 'aishahwahab26@example.com', 330.00, '2026-06-12', 'June', 'Bank Rakyat', 'Johor', 'Bank Rakyat Johor Bahru', 2, 18, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(68, 'Sambu Pillai', '017-2289100', 'sambupillai27@example.com', 145.00, '2026-01-13', 'January', 'Bank Rakyat', 'Perak', 'Bank Rakyat Ipoh', 2, 19, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(69, 'Zati Huda', '014-5252728', 'zatihuda28@example.com', 175.00, '2026-02-14', 'February', 'Bank Rakyat', 'Sabah', 'Bank Rakyat Kota Kinabalu', 2, 20, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(70, 'Marcus Ng', '019-4533910', 'marcusng29@example.com', 205.00, '2026-03-15', 'March', 'Bank Rakyat', 'Pahang', 'Bank Rakyat Kuantan', 2, 21, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(71, 'Hamdan Yusof', '013-3631285', 'hamdanyusof30@example.com', 100.00, '2026-01-01', 'January', 'Pos Malaysia', 'Johor', 'Pos Malaysia JB Sentral', 3, 7, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(72, 'Serene Loh', '014-7389295', 'sereneloh31@example.com', 150.00, '2026-02-02', 'February', 'Pos Malaysia', 'Johor', 'Pos Malaysia Skudai', 3, 8, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(73, 'Lutfi Zainuddin', '018-4434899', 'lutfizainuddin32@example.com', 200.00, '2026-03-03', 'March', 'Pos Malaysia', 'Johor', 'Pos Malaysia Kluang', 3, 9, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(74, 'Kavitha Samy', '017-6429373', 'kavithasamy33@example.com', 250.00, '2026-04-04', 'April', 'Pos Malaysia', 'Penang', 'Pos Malaysia Penang', 3, 23, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(75, 'Rizal Omar', '017-5916066', 'rizalomar34@example.com', 300.00, '2026-05-05', 'May', 'Pos Malaysia', 'Negeri Sembilan', 'Pos Malaysia Seremban', 3, 24, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(76, 'Wan Siti', '016-4243973', 'wansiti35@example.com', 350.00, '2026-06-06', 'June', 'Pos Malaysia', 'Sabah', 'Pos Malaysia Kota Kinabalu', 3, 25, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(77, 'Christopher Bay', '018-7187271', 'christopherbay36@example.com', 400.00, '2026-01-07', 'January', 'Pos Malaysia', 'Pahang', 'Pos Malaysia Kuantan', 3, 26, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(78, 'Halimah Hashim', '013-5463369', 'halimahhashim37@example.com', 180.00, '2026-02-08', 'February', 'Pos Malaysia', 'Melaka', 'Pos Malaysia Melaka', 3, 27, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(79, 'Nithia Kumar', '016-4754623', 'nithiakumar38@example.com', 220.00, '2026-03-09', 'March', 'Pos Malaysia', 'Johor', 'Pos Malaysia JB Sentral', 3, 7, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(80, 'Shahreen Mat', '014-7246771', 'shahreenmat39@example.com', 270.00, '2026-04-10', 'April', 'Pos Malaysia', 'Johor', 'Pos Malaysia Skudai', 3, 8, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(81, 'Datin Rosnah', '019-1184731', 'datinrosnah40@example.com', 320.00, '2026-05-11', 'May', 'Pos Malaysia', 'Johor', 'Pos Malaysia Kluang', 3, 9, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(82, 'Gerald Lim', '016-5887305', 'geraldlim41@example.com', 130.00, '2026-06-12', 'June', 'Pos Malaysia', 'Penang', 'Pos Malaysia Penang', 3, 23, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(83, 'Mastura Zahari', '011-1168784', 'masturazahari42@example.com', 160.00, '2026-01-13', 'January', 'Pos Malaysia', 'Negeri Sembilan', 'Pos Malaysia Seremban', 3, 24, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(84, 'Prem Raj', '018-2355031', 'premraj43@example.com', 190.00, '2026-02-14', 'February', 'Pos Malaysia', 'Sabah', 'Pos Malaysia Kota Kinabalu', 3, 25, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(85, 'Norleha Adam', '012-2296678', 'norlehaadam44@example.com', 240.00, '2026-03-15', 'March', 'Pos Malaysia', 'Pahang', 'Pos Malaysia Kuantan', 3, 26, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(86, 'Aminudin Talib', '018-7625579', 'aminudintalib45@example.com', 110.00, '2026-01-01', 'January', 'EBB', 'Penang', 'EBB George Town', 4, 10, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(87, 'Betty Tan', '019-5026884', 'bettytan46@example.com', 140.00, '2026-02-02', 'February', 'EBB', 'Penang', 'EBB Butterworth', 4, 11, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(88, 'Suhana Said', '015-9361562', 'suhanasaid47@example.com', 170.00, '2026-03-03', 'March', 'EBB', 'Penang', 'EBB Bayan Baru', 4, 12, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(89, 'Raj Kumar', '012-4300718', 'rajkumar48@example.com', 210.00, '2026-04-04', 'April', 'EBB', 'WP Kuala Lumpur', 'EBB Kuala Lumpur', 4, 28, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(90, 'Zalina Othman', '011-1541422', 'zalinaothman49@example.com', 260.00, '2026-05-05', 'May', 'EBB', 'Johor', 'EBB Johor Bahru', 4, 29, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(91, 'Ezwan Idris', '012-3076956', 'ezwanidris50@example.com', 310.00, '2026-06-06', 'June', 'EBB', 'Selangor', 'EBB Shah Alam', 4, 30, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(92, 'Pauline Yap', '016-7064243', 'paulineyap51@example.com', 120.00, '2026-01-07', 'January', 'EBB', 'Sabah', 'EBB Kota Kinabalu', 4, 31, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(93, 'Nor Alia Hamid', '011-1788594', 'noraliahamid52@example.com', 155.00, '2026-02-08', 'February', 'EBB', 'Sarawak', 'EBB Kuching', 4, 32, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(94, 'Senthil Nathan', '012-2185411', 'senthilnathan53@example.com', 185.00, '2026-03-09', 'March', 'EBB', 'Penang', 'EBB George Town', 4, 10, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(95, 'Rohaiza Zulkifli', '011-8856923', 'rohaizazulkifli54@example.com', 230.00, '2026-04-10', 'April', 'EBB', 'Penang', 'EBB Butterworth', 4, 11, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(96, 'Mahathir Nor', '019-5576709', 'mahathirnor55@example.com', 280.00, '2026-05-11', 'May', 'EBB', 'Penang', 'EBB Bayan Baru', 4, 12, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(97, 'Ivy Chong', '019-9338257', 'ivychong56@example.com', 330.00, '2026-06-12', 'June', 'EBB', 'WP Kuala Lumpur', 'EBB Kuala Lumpur', 4, 28, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(98, 'Syahril Bada', '011-6048499', 'syahrilbada57@example.com', 145.00, '2026-01-13', 'January', 'EBB', 'Johor', 'EBB Johor Bahru', 4, 29, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(99, 'Geetha Nair', '016-6227441', 'geethanair58@example.com', 175.00, '2026-02-14', 'February', 'EBB', 'Selangor', 'EBB Shah Alam', 4, 30, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(100, 'Aidil Firdaus', '014-9811862', 'aidilfirdaus59@example.com', 205.00, '2026-03-15', 'March', 'EBB', 'Sabah', 'EBB Kota Kinabalu', 4, 31, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(101, 'Azman Zain', '019-1938630', 'azmanzainind0@example.com', 559.00, '2026-01-01', 'January', 'BSN', 'Selangor', '-', 5, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(102, 'Harti Noor', '012-5694593', 'hartinoorind1@example.com', 604.00, '2026-02-02', 'February', 'BSN', 'Selangor', '-', 5, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(103, 'Kevin Lim', '018-5120822', 'kevinlimind2@example.com', 687.00, '2026-03-03', 'March', 'BSN', 'Selangor', '-', 5, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(104, 'Priya Devi', '013-5921611', 'priyadeviind3@example.com', 593.00, '2026-01-01', 'January', 'BSN', 'Selangor', '-', 6, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(105, 'Salmi Aziz', '011-5633019', 'salmiazizind4@example.com', 591.00, '2026-02-02', 'February', 'BSN', 'Selangor', '-', 6, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(106, 'Osman Hamid', '014-9103116', 'osmanhamidind5@example.com', 501.00, '2026-03-03', 'March', 'BSN', 'Selangor', '-', 6, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(107, 'Celine Wong', '016-9046372', 'celinewongind6@example.com', 715.00, '2026-04-04', 'April', 'BSN', 'Selangor', '-', 6, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(108, 'Hafiz Jamal', '018-3311298', 'hafizjamalind7@example.com', 223.00, '2026-01-01', 'January', 'Bank Rakyat', 'Selangor', '-', 7, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(109, 'Nurul Hana', '014-4197455', 'nurulhanaind8@example.com', 191.00, '2026-02-02', 'February', 'Bank Rakyat', 'Selangor', '-', 7, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(110, 'Bala Murugan', '019-1748204', 'balamuruganind9@example.com', 214.00, '2026-03-03', 'March', 'Bank Rakyat', 'Selangor', '-', 7, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(111, 'Rashidah Ali', '015-1810032', 'rashidahaliind10@example.com', 177.00, '2026-04-04', 'April', 'Bank Rakyat', 'Selangor', '-', 7, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(112, 'Thomas Go', '011-1301307', 'thomasgoind11@example.com', 175.00, '2026-05-05', 'May', 'Bank Rakyat', 'Selangor', '-', 7, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(113, 'Salmah Nor', '017-1802066', 'salmahnorind12@example.com', 999.00, '2026-01-01', 'January', 'Pos Malaysia', 'Selangor', '-', 8, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(114, 'Razif Zainal', '016-6963871', 'razifzainalind13@example.com', 1075.00, '2026-02-02', 'February', 'Pos Malaysia', 'Selangor', '-', 8, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(115, 'Jasmine Lee', '015-7536843', 'jasmineleeind14@example.com', 1026.00, '2026-03-03', 'March', 'Pos Malaysia', 'Selangor', '-', 8, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(116, 'Kamal Ibrahim', '017-9277925', 'kamalibrahimind15@example.com', 307.00, '2026-01-01', 'January', 'EBB', 'Selangor', '-', 9, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(117, 'Lily Chan', '017-2090305', 'lilychanind16@example.com', 318.00, '2026-02-02', 'February', 'EBB', 'Selangor', '-', 9, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(118, 'Wahab Salleh', '012-5578070', 'wahabsallehind17@example.com', 310.00, '2026-03-03', 'March', 'EBB', 'Selangor', '-', 9, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(119, 'Meena Raj', '016-5939515', 'meenarajind18@example.com', 265.00, '2026-04-04', 'April', 'EBB', 'Selangor', '-', 9, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(120, 'Adam Bakri', '012-3215358', 'adambakriind19@example.com', 133.00, '2026-01-01', 'January', 'BSN', 'Selangor', '-', 10, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(121, 'Norain Daud', '017-2281015', 'noraindaudind20@example.com', 146.00, '2026-02-02', 'February', 'BSN', 'Selangor', '-', 10, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(122, 'Vincent Tan', '017-2904820', 'vincenttanind21@example.com', 140.00, '2026-03-03', 'March', 'BSN', 'Selangor', '-', 10, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(123, 'Rohani Kassim', '017-9788057', 'rohanikassimind22@example.com', 172.00, '2026-04-04', 'April', 'BSN', 'Selangor', '-', 10, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(124, 'Syukri Hassan', '019-4320573', 'syukrihassanind23@example.com', 169.00, '2026-05-05', 'May', 'BSN', 'Selangor', '-', 10, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(125, 'Joanna Lim', '017-3365095', 'joannalimind24@example.com', 1050.00, '2026-01-01', 'January', 'Bank Rakyat', 'Selangor', '-', 11, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(126, 'Faredz Murad', '016-7031642', 'faredzmuradind25@example.com', 946.00, '2026-02-02', 'February', 'Bank Rakyat', 'Selangor', '-', 11, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(127, 'Aishah Wahab', '018-3230614', 'aishahwahabind26@example.com', 754.00, '2026-03-03', 'March', 'Bank Rakyat', 'Selangor', '-', 11, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(128, 'Sambu Pillai', '014-6324111', 'sambupillaiind27@example.com', 325.00, '2026-01-01', 'January', 'Pos Malaysia', 'Selangor', '-', 12, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(129, 'Zati Huda', '013-8402877', 'zatihudaind28@example.com', 386.00, '2026-02-02', 'February', 'Pos Malaysia', 'Selangor', '-', 12, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(130, 'Marcus Ng', '018-7885456', 'marcusngind29@example.com', 469.00, '2026-03-03', 'March', 'Pos Malaysia', 'Selangor', '-', 12, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(131, 'Hamdan Yusof', '018-5721198', 'hamdanyusofind30@example.com', 370.00, '2026-04-04', 'April', 'Pos Malaysia', 'Selangor', '-', 12, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(132, 'Serene Loh', '015-3683092', 'serenelohind31@example.com', 182.00, '2026-01-01', 'January', 'EBB', 'Selangor', '-', 13, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(133, 'Lutfi Zainuddin', '016-7706220', 'lutfizainuddinind32@example.com', 195.00, '2026-02-02', 'February', 'EBB', 'Selangor', '-', 13, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(134, 'Kavitha Samy', '014-7839198', 'kavithasamyind33@example.com', 191.00, '2026-03-03', 'March', 'EBB', 'Selangor', '-', 13, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(135, 'Rizal Omar', '018-6658468', 'rizalomarind34@example.com', 129.00, '2026-04-04', 'April', 'EBB', 'Selangor', '-', 13, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(136, 'Wan Siti', '011-9521614', 'wansitiind35@example.com', 183.00, '2026-05-05', 'May', 'EBB', 'Selangor', '-', 13, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(137, 'Christopher Bay', '013-7022474', 'christopherbayind36@example.com', 1154.00, '2026-01-01', 'January', 'BSN', 'Selangor', '-', 14, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(138, 'Halimah Hashim', '012-1012467', 'halimahhashimind37@example.com', 1175.00, '2026-02-02', 'February', 'BSN', 'Selangor', '-', 14, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(139, 'Nithia Kumar', '015-1786144', 'nithiakumarind38@example.com', 1071.00, '2026-03-03', 'March', 'BSN', 'Selangor', '-', 14, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(140, 'Shahreen Mat', '019-2358801', 'shahreenmatind39@example.com', 248.00, '2026-01-01', 'January', 'Bank Rakyat', 'Selangor', '-', 15, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(141, 'Datin Rosnah', '011-1312357', 'datinrosnahind40@example.com', 310.00, '2026-02-02', 'February', 'Bank Rakyat', 'Selangor', '-', 15, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(142, 'Gerald Lim', '012-2885965', 'geraldlimind41@example.com', 269.00, '2026-03-03', 'March', 'Bank Rakyat', 'Selangor', '-', 15, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(143, 'Mastura Zahari', '017-9842265', 'masturazahariind42@example.com', 273.00, '2026-04-04', 'April', 'Bank Rakyat', 'Selangor', '-', 15, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(144, 'Prem Raj', '019-4091143', 'premrajind43@example.com', 348.00, '2026-01-01', 'January', 'Pos Malaysia', 'Selangor', '-', 16, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(145, 'Norleha Adam', '013-6854112', 'norlehaadamind44@example.com', 473.00, '2026-02-02', 'February', 'Pos Malaysia', 'Selangor', '-', 16, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(146, 'Aminudin Talib', '017-4754994', 'aminudintalibind45@example.com', 460.00, '2026-03-03', 'March', 'Pos Malaysia', 'Selangor', '-', 16, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(147, 'Betty Tan', '014-3021201', 'bettytanind46@example.com', 382.00, '2026-04-04', 'April', 'Pos Malaysia', 'Selangor', '-', 16, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(148, 'Suhana Said', '011-3465706', 'suhanasaidind47@example.com', 387.00, '2026-05-05', 'May', 'Pos Malaysia', 'Selangor', '-', 16, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(149, 'Raj Kumar', '019-3190620', 'rajkumarind48@example.com', 214.00, '2026-01-01', 'January', 'EBB', 'Selangor', '-', 17, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(150, 'Zalina Othman', '016-9174726', 'zalinaothmanind49@example.com', 224.00, '2026-02-02', 'February', 'EBB', 'Selangor', '-', 17, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(151, 'Ezwan Idris', '018-7246712', 'ezwanidrisind50@example.com', 212.00, '2026-03-03', 'March', 'EBB', 'Selangor', '-', 17, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(152, 'Pauline Yap', '019-8079599', 'paulineyapind51@example.com', 462.00, '2026-01-01', 'January', 'BSN', 'Selangor', '-', 18, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(153, 'Nor Alia Hamid', '011-2890310', 'noraliahamidind52@example.com', 370.00, '2026-02-02', 'February', 'BSN', 'Selangor', '-', 18, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(154, 'Senthil Nathan', '012-2684328', 'senthilnathanind53@example.com', 472.00, '2026-03-03', 'March', 'BSN', 'Selangor', '-', 18, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(155, 'Rohaiza Zulkifli', '015-5390537', 'rohaizazulkifliind54@example.com', 396.00, '2026-04-04', 'April', 'BSN', 'Selangor', '-', 18, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(156, 'Mahathir Nor', '018-4335104', 'mahathirnorind55@example.com', 607.00, '2026-01-01', 'January', 'Bank Rakyat', 'Selangor', '-', 19, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(157, 'Ivy Chong', '017-5554245', 'ivychongind56@example.com', 480.00, '2026-02-02', 'February', 'Bank Rakyat', 'Selangor', '-', 19, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(158, 'Syahril Bada', '011-4000157', 'syahrilbadaind57@example.com', 674.00, '2026-03-03', 'March', 'Bank Rakyat', 'Selangor', '-', 19, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(159, 'Geetha Nair', '012-2392148', 'geethanairind58@example.com', 461.00, '2026-04-04', 'April', 'Bank Rakyat', 'Selangor', '-', 19, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(160, 'Aidil Firdaus', '014-8697287', 'aidilfirdausind59@example.com', 678.00, '2026-05-05', 'May', 'Bank Rakyat', 'Selangor', '-', 19, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(161, 'Azman Zain', '011-2681604', 'azmanzainind60@example.com', 454.00, '2026-01-01', 'January', 'Pos Malaysia', 'Selangor', '-', 20, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(162, 'Harti Noor', '016-4340709', 'hartinoorind61@example.com', 460.00, '2026-02-02', 'February', 'Pos Malaysia', 'Selangor', '-', 20, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(163, 'Kevin Lim', '018-8292864', 'kevinlimind62@example.com', 436.00, '2026-03-03', 'March', 'Pos Malaysia', 'Selangor', '-', 20, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(164, 'Priya Devi', '011-8719254', 'priyadeviind63@example.com', 120.00, '2026-01-01', 'January', 'EBB', 'Selangor', '-', 21, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(165, 'Salmi Aziz', '015-8489175', 'salmiazizind64@example.com', 121.00, '2026-02-02', 'February', 'EBB', 'Selangor', '-', 21, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(166, 'Osman Hamid', '014-4820557', 'osmanhamidind65@example.com', 101.00, '2026-03-03', 'March', 'EBB', 'Selangor', '-', 21, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(167, 'Celine Wong', '012-2021683', 'celinewongind66@example.com', 108.00, '2026-04-04', 'April', 'EBB', 'Selangor', '-', 21, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(168, 'Hafiz Jamal', '015-5311846', 'hafizjamalind67@example.com', 432.00, '2026-01-01', 'January', 'BSN', 'Selangor', '-', 22, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(169, 'Nurul Hana', '014-2549896', 'nurulhanaind68@example.com', 410.00, '2026-02-02', 'February', 'BSN', 'Selangor', '-', 22, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(170, 'Bala Murugan', '016-9438089', 'balamuruganind69@example.com', 472.00, '2026-03-03', 'March', 'BSN', 'Selangor', '-', 22, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(171, 'Rashidah Ali', '011-6332453', 'rashidahaliind70@example.com', 450.00, '2026-04-04', 'April', 'BSN', 'Selangor', '-', 22, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(172, 'Thomas Go', '013-1061804', 'thomasgoind71@example.com', 436.00, '2026-05-05', 'May', 'BSN', 'Selangor', '-', 22, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(173, 'Salmah Nor', '011-4549917', 'salmahnorind72@example.com', 219.00, '2026-01-01', 'January', 'Bank Rakyat', 'Selangor', '-', 23, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(174, 'Razif Zainal', '016-8881261', 'razifzainalind73@example.com', 257.00, '2026-02-02', 'February', 'Bank Rakyat', 'Selangor', '-', 23, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(175, 'Jasmine Lee', '018-5155252', 'jasmineleeind74@example.com', 304.00, '2026-03-03', 'March', 'Bank Rakyat', 'Selangor', '-', 23, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(176, 'Kamal Ibrahim', '015-8406838', 'kamalibrahimind75@example.com', 438.00, '2026-01-01', 'January', 'Pos Malaysia', 'Selangor', '-', 24, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(177, 'Lily Chan', '013-1822748', 'lilychanind76@example.com', 416.00, '2026-02-02', 'February', 'Pos Malaysia', 'Selangor', '-', 24, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(178, 'Wahab Salleh', '015-5136830', 'wahabsallehind77@example.com', 357.00, '2026-03-03', 'March', 'Pos Malaysia', 'Selangor', '-', 24, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30'),
(179, 'Meena Raj', '011-2178227', 'meenarajind78@example.com', 439.00, '2026-04-04', 'April', 'Pos Malaysia', 'Selangor', '-', 24, NULL, 'img/attachment receipt 2.jpg', '2026-07-13 05:45:30');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','wakalah_individual','wakalah_corporate') NOT NULL,
  `state` varchar(100) NOT NULL,
  `wakalah_id` int(11) DEFAULT NULL,
  `channel` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `role`, `state`, `wakalah_id`, `channel`, `created_at`) VALUES
(1, 'bsn@corp.com.my', '$2y$10$8dxgTfcRU8IP.gqURKcnZu2TfSj.BPh2ok0tCN/fUlEjVr00yaFd2', 'wakalah_corporate', 'Selangor', 1, 'BSN', '2026-07-13 05:38:05'),
(2, 'bankrakyat@corp.com.my', '$2y$10$ObvQ0aKTcncs1cZoexMiO.YSMRxb7omnYzn0TH1QDWZPdewtINjUW', 'wakalah_corporate', 'WP Kuala Lumpur', 2, 'Bank Rakyat', '2026-07-13 05:38:05'),
(3, 'posmalaysia@corp.com.my', '$2y$10$c.t/VzFiZBrmSudpVk9UgOCHFD94wpYbyHDhbv4LUnBDYvbZW8wuy', 'wakalah_corporate', 'Johor', 3, 'Pos Malaysia', '2026-07-13 05:38:05'),
(4, 'ebb@corp.com.my', '$2y$10$fvLIzN9spdFtJ2f5lVvbsezjXz87cknlPb9HEGuWKQGbir8ewAwgO', 'wakalah_corporate', 'Penang', 4, 'EBB', '2026-07-13 05:38:05'),
(5, 'safwan@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'Selangor', 5, 'BSN', '2026-07-13 05:38:05'),
(6, 'faizal@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'WP Kuala Lumpur', 6, 'BSN', '2026-07-13 05:38:05'),
(7, 'aisyah@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'Selangor', 7, 'Bank Rakyat', '2026-07-13 05:38:05'),
(8, 'zulkifli@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'Johor', 8, 'Pos Malaysia', '2026-07-13 05:38:05'),
(9, 'faridah@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'Penang', 9, 'EBB', '2026-07-13 05:38:05'),
(10, 'chong@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'WP Kuala Lumpur', 10, 'BSN', '2026-07-13 05:38:05'),
(11, 'rajan@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'Selangor', 11, 'Bank Rakyat', '2026-07-13 05:38:05'),
(12, 'aminah@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'Johor', 12, 'Pos Malaysia', '2026-07-13 05:38:05'),
(13, 'leechee@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'Penang', 13, 'EBB', '2026-07-13 05:38:05'),
(14, 'norashikin@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'WP Kuala Lumpur', 14, 'BSN', '2026-07-13 05:38:05'),
(15, 'abubakar@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'Selangor', 15, 'Bank Rakyat', '2026-07-13 05:38:05'),
(16, 'kalani@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'Johor', 16, 'Pos Malaysia', '2026-07-13 05:38:05'),
(17, 'hazwani@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'Penang', 17, 'EBB', '2026-07-13 05:38:05'),
(18, 'roslan@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'Selangor', 18, 'BSN', '2026-07-13 05:38:05'),
(19, 'shafiqah@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'WP Kuala Lumpur', 19, 'Bank Rakyat', '2026-07-13 05:38:05'),
(20, 'zarina@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'Johor', 20, 'Pos Malaysia', '2026-07-13 05:38:05'),
(21, 'gopalan@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'Penang', 21, 'EBB', '2026-07-13 05:38:05'),
(22, 'nadirah@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'Selangor', 22, 'BSN', '2026-07-13 05:38:05'),
(23, 'fadzillah@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'WP Kuala Lumpur', 23, 'Bank Rakyat', '2026-07-13 05:38:05'),
(24, 'suraya@yawatim.org.my', '$2y$10$5XwRsLPf0tFHEFeBZMDrAujvyhgOKjhypVazbBG51FvpW6zN6JDrK', 'wakalah_individual', 'Johor', 24, 'Pos Malaysia', '2026-07-13 05:38:05'),
(25, 'admin@yawatim.org.my', '$2y$10$u8wHOcFOph.PCaGjRubMKe/ApQmba4jOv9uKWJWO3BQ7DvlpzlJkK', 'admin', 'Selangor', NULL, NULL, '2026-07-13 05:38:05');

-- --------------------------------------------------------

--
-- Table structure for table `wakalah`
--

CREATE TABLE `wakalah` (
  `wakalah_id` int(11) NOT NULL,
  `type` enum('individual','corporate') NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `ic_number` varchar(100) DEFAULT NULL,
  `company_representative` varchar(255) DEFAULT NULL,
  `ssm_number` varchar(100) DEFAULT NULL,
  `hq_address` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `channel` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wakalah`
--

INSERT INTO `wakalah` (`wakalah_id`, `type`, `name`, `email`, `phone`, `state`, `status`, `ic_number`, `company_representative`, `ssm_number`, `hq_address`, `address`, `channel`, `created_at`) VALUES
(1, 'corporate', 'BSN', 'bsn@corp.com.my', '03-5551234', 'Selangor', 'Active', NULL, 'Mohd Yusof', 'BSN-001', 'Level 20, Menara BSN, KL', NULL, 'BSN', '2026-07-13 05:38:05'),
(2, 'corporate', 'Bank Rakyat', 'bankrakyat@corp.com.my', '03-2144567', 'WP Kuala Lumpur', 'Active', NULL, 'Sarah Tan', 'BR-001', 'Level 5, Bank Rakyat Tower, KL', NULL, 'Bank Rakyat', '2026-07-13 05:38:05'),
(3, 'corporate', 'Pos Malaysia', 'posmalaysia@corp.com.my', '07-3338899', 'Johor', 'Active', NULL, 'Kumar Rao', 'POS-001', 'No. 10, Jalan Wong Ah Fook, JB', NULL, 'Pos Malaysia', '2026-07-13 05:38:05'),
(4, 'corporate', 'EBB', 'ebb@corp.com.my', '04-8889900', 'Penang', 'Active', NULL, 'Lim Lee', 'EBB-001', 'No. 77, Beach Street, George Town', NULL, 'EBB', '2026-07-13 05:38:05'),
(5, 'individual', 'Ahmad Safwan', 'safwan@yawatim.org.my', '012-3456789', 'Selangor', 'Active', '890102-10-5433', NULL, NULL, NULL, 'No. 1, Jalan Ahmad Safwan, Selangor', 'BSN', '2026-07-13 05:38:05'),
(6, 'individual', 'Mohd Faizal', 'faizal@yawatim.org.my', '013-1122334', 'WP Kuala Lumpur', 'Active', '880215-14-2233', NULL, NULL, NULL, 'No. 1, Jalan Mohd Faizal, WP Kuala Lumpur', 'BSN', '2026-07-13 05:38:05'),
(7, 'individual', 'Nurul Aisyah', 'aisyah@yawatim.org.my', '019-3334444', 'Selangor', 'Active', '920303-10-4455', NULL, NULL, NULL, 'No. 1, Jalan Nurul Aisyah, Selangor', 'Bank Rakyat', '2026-07-13 05:38:05'),
(8, 'individual', 'Zulkifli Hassan', 'zulkifli@yawatim.org.my', '017-5556661', 'Johor', 'Active', '850506-01-6677', NULL, NULL, NULL, 'No. 1, Jalan Zulkifli Hassan, Johor', 'Pos Malaysia', '2026-07-13 05:38:05'),
(9, 'individual', 'Faridah Ahmad', 'faridah@yawatim.org.my', '018-2223334', 'Penang', 'Active', '910707-07-7788', NULL, NULL, NULL, 'No. 1, Jalan Faridah Ahmad, Penang', 'EBB', '2026-07-13 05:38:05'),
(10, 'individual', 'Chong Mei Ling', 'chong@yawatim.org.my', '016-8885555', 'WP Kuala Lumpur', 'Active', '870808-08-8899', NULL, NULL, NULL, 'No. 1, Jalan Chong Mei Ling, WP Kuala Lumpur', 'BSN', '2026-07-13 05:38:05'),
(11, 'individual', 'Rajan Kumar', 'rajan@yawatim.org.my', '011-22334455', 'Selangor', 'Active', '930909-09-9900', NULL, NULL, NULL, 'No. 1, Jalan Rajan Kumar, Selangor', 'Bank Rakyat', '2026-07-13 05:38:05'),
(12, 'individual', 'Siti Aminah', 'aminah@yawatim.org.my', '012-4445555', 'Johor', 'Active', '940101-10-1011', NULL, NULL, NULL, 'No. 1, Jalan Siti Aminah, Johor', 'Pos Malaysia', '2026-07-13 05:38:05'),
(13, 'individual', 'Lee Chee Kong', 'leechee@yawatim.org.my', '014-9998888', 'Penang', 'Active', '880202-11-2122', NULL, NULL, NULL, 'No. 1, Jalan Lee Chee Kong, Penang', 'EBB', '2026-07-13 05:38:05'),
(14, 'individual', 'Norashikin Said', 'norashikin@yawatim.org.my', '016-7772222', 'WP Kuala Lumpur', 'Active', '910303-12-3233', NULL, NULL, NULL, 'No. 1, Jalan Norashikin Said, WP Kuala Lumpur', 'BSN', '2026-07-13 05:38:05'),
(15, 'individual', 'Abu Bakar Sidek', 'abubakar@yawatim.org.my', '019-1112222', 'Selangor', 'Active', '860404-13-4344', NULL, NULL, NULL, 'No. 1, Jalan Abu Bakar Sidek, Selangor', 'Bank Rakyat', '2026-07-13 05:38:05'),
(16, 'individual', 'Kalani Tan', 'kalani@yawatim.org.my', '017-3339990', 'Johor', 'Active', '950505-14-5455', NULL, NULL, NULL, 'No. 1, Jalan Kalani Tan, Johor', 'Pos Malaysia', '2026-07-13 05:38:05'),
(17, 'individual', 'Hazwani Yusof', 'hazwani@yawatim.org.my', '013-5566778', 'Penang', 'Active', '900606-15-6566', NULL, NULL, NULL, 'No. 1, Jalan Hazwani Yusof, Penang', 'EBB', '2026-07-13 05:38:05'),
(18, 'individual', 'Roslan Daud', 'roslan@yawatim.org.my', '012-9993333', 'Selangor', 'Active', '870707-01-7677', NULL, NULL, NULL, 'No. 1, Jalan Roslan Daud, Selangor', 'BSN', '2026-07-13 05:38:05'),
(19, 'individual', 'Shafiqah Osman', 'shafiqah@yawatim.org.my', '011-55443322', 'WP Kuala Lumpur', 'Active', '920808-02-8788', NULL, NULL, NULL, 'No. 1, Jalan Shafiqah Osman, WP Kuala Lumpur', 'Bank Rakyat', '2026-07-13 05:38:05'),
(20, 'individual', 'Zarina Baharum', 'zarina@yawatim.org.my', '018-9994444', 'Johor', 'Active', '880909-03-9899', NULL, NULL, NULL, 'No. 1, Jalan Zarina Baharum, Johor', 'Pos Malaysia', '2026-07-13 05:38:05'),
(21, 'individual', 'Gopalan Nair', 'gopalan@yawatim.org.my', '014-5556666', 'Penang', 'Active', '930101-04-0010', NULL, NULL, NULL, 'No. 1, Jalan Gopalan Nair, Penang', 'EBB', '2026-07-13 05:38:05'),
(22, 'individual', 'Nadirah Jamal', 'nadirah@yawatim.org.my', '012-7771112', 'Selangor', 'Active', '950202-05-1121', NULL, NULL, NULL, 'No. 1, Jalan Nadirah Jamal, Selangor', 'BSN', '2026-07-13 05:38:05'),
(23, 'individual', 'Fadzillah Razak', 'fadzillah@yawatim.org.my', '016-4447777', 'WP Kuala Lumpur', 'Active', '870303-06-2232', NULL, NULL, NULL, 'No. 1, Jalan Fadzillah Razak, WP Kuala Lumpur', 'Bank Rakyat', '2026-07-13 05:38:05'),
(24, 'individual', 'Suraya Latif', 'suraya@yawatim.org.my', '017-1112223', 'Johor', 'Active', '910404-07-3343', NULL, NULL, NULL, 'No. 1, Jalan Suraya Latif, Johor', 'Pos Malaysia', '2026-07-13 05:38:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booths`
--
ALTER TABLE `booths`
  ADD PRIMARY KEY (`booth_id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`donation_id`),
  ADD KEY `idx_donations_wakalah_id` (`wakalah_id`),
  ADD KEY `idx_donations_booth_id` (`booth_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_wakalah_id` (`wakalah_id`);

--
-- Indexes for table `wakalah`
--
ALTER TABLE `wakalah`
  ADD PRIMARY KEY (`wakalah_id`),
  ADD UNIQUE KEY `uq_wakalah_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booths`
--
ALTER TABLE `booths`
  MODIFY `booth_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `donation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=180;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `wakalah`
--
ALTER TABLE `wakalah`
  MODIFY `wakalah_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `fk_donations_booth` FOREIGN KEY (`booth_id`) REFERENCES `booths` (`booth_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_donations_wakalah` FOREIGN KEY (`wakalah_id`) REFERENCES `wakalah` (`wakalah_id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_wakalah` FOREIGN KEY (`wakalah_id`) REFERENCES `wakalah` (`wakalah_id`) ON DELETE CASCADE;
COMMIT;

