-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Feb 01, 2026 at 12:06 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pragmanx_onelife_distributor`
--

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `add_admin`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_admin` (IN `p_name` VARCHAR(100), IN `p_username` VARCHAR(100), IN `p_email` VARCHAR(100), IN `p_password` VARCHAR(100), IN `p_contact` VARCHAR(100), IN `p_status` VARCHAR(100))   BEGIN
    INSERT INTO admin (
        name, username, email, password, contact, status
    )
    VALUES (
        p_name, p_username, p_email, p_password, p_contact, p_status
    );
END$$

DROP PROCEDURE IF EXISTS `add_asm_attendance`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_asm_attendance` (IN `p_asm_id` INT, IN `p_attendance` VARCHAR(50), IN `p_location` VARCHAR(100))   BEGIN
	INSERT INTO asm_attendance (asm_id, attendance, location) VALUES 
    (p_asm_id, p_attendance, p_location);
END$$

DROP PROCEDURE IF EXISTS `add_batch_distributor`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_batch_distributor` (IN `p_assigned_by` INT, IN `p_assigned_to` INT, IN `p_status` VARCHAR(50))   BEGIN
	INSERT INTO batch_distributor (assigned_by, assigned_to, status)
    VALUES (p_assigned_by, p_assigned_to, p_status);
END$$

DROP PROCEDURE IF EXISTS `add_batch_retailer`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_batch_retailer` (IN `p_assigned_by` INT, IN `p_assigned_to` INT, IN `p_status` VARCHAR(50))   BEGIN
	INSERT INTO batch_retailer (assigned_by, assigned_to, status)
    VALUES (p_assigned_by, p_assigned_to, p_status);
END$$

DROP PROCEDURE IF EXISTS `add_city`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_city` (IN `p_state_id` INT, IN `p_city_name` VARCHAR(255), IN `p_status` VARCHAR(50))   BEGIN
	INSERT INTO city (state_id, city_name, status)
    VALUES (p_state_id, p_city_name, p_status);
END$$

DROP PROCEDURE IF EXISTS `add_offers`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_offers` (IN `p_title` VARCHAR(100), IN `p_img` VARCHAR(100), IN `p_description` VARCHAR(200), IN `p_status` VARCHAR(100))   BEGIN
	INSERT INTO offers (title, img, description, status)
    VALUES (p_title, p_img, p_description, p_status);
END$$

DROP PROCEDURE IF EXISTS `add_product`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_product` (IN `p_product_category_id` INT, IN `p_product_name` VARCHAR(255), IN `p_sku_id` VARCHAR(255), IN `p_added_by` VARCHAR(100), IN `p_status` VARCHAR(100))   BEGIN
    INSERT INTO products (
        product_category_id, product_name, sku_id, added_by, status
    ) VALUES (
        p_product_category_id, p_product_name, p_sku_id, p_added_by, p_status
    );
END$$

DROP PROCEDURE IF EXISTS `add_product_assigned_dist`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_product_assigned_dist` (IN `p_batch_id` INT, IN `p_product_id` INT, IN `p_quantity` INT, IN `p_status` VARCHAR(200))   BEGIN
    INSERT INTO product_assigned_dist (
        batch_id, product_id, quantity, status
    ) VALUES (
        p_batch_id, p_product_id, p_quantity, p_status
    );
END$$

DROP PROCEDURE IF EXISTS `add_product_assigned_retailer`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_product_assigned_retailer` (IN `p_batch_id` INT, IN `p_product_id` INT, IN `p_quantity` INT, IN `p_status` VARCHAR(200))   BEGIN
    INSERT INTO product_assigned_retailer (
        batch_id, product_id, quantity, status
    ) VALUES (
        p_batch_id, p_product_id, p_quantity, p_status
    );
END$$

DROP PROCEDURE IF EXISTS `add_product_assignments_backup`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_product_assignments_backup` (IN `p_batch_id` INT, IN `p_product_id` INT, IN `p_quantity` INT, IN `p_status` VARCHAR(200), IN `p_operation` VARCHAR(50))   BEGIN
    INSERT INTO product_assignments_backup (
        batch_id, product_id, quantity, status, operation
    ) VALUES (
        p_batch_id, p_product_id, p_quantity, p_status, p_operation
    );
END$$

DROP PROCEDURE IF EXISTS `add_product_category`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_product_category` (IN `p_category_name` VARCHAR(100), IN `p_status` VARCHAR(255), IN `p_added_by` VARCHAR(255))   BEGIN
    INSERT INTO product_category (
        category_name, status, added_by
    )
    VALUES (
        p_category_name, p_status, p_added_by
    );
END$$

DROP PROCEDURE IF EXISTS `add_requested_product`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_requested_product` (IN `p_name` VARCHAR(100), IN `p_category` INT, IN `p_color` VARCHAR(100), IN `p_specifications` VARCHAR(100), IN `p_quantity` INT, IN `p_status` VARCHAR(100), IN `p_retailer_id` INT, IN `p_distributor_id` INT)   BEGIN
    INSERT INTO requested_products (
        name, category, color, specifications, quantity, status, retailer_id, distributor_id
    ) VALUES (
        p_name, p_category, p_color, p_specifications, p_quantity, p_status, p_retailer_id, p_distributor_id
    );
END$$

DROP PROCEDURE IF EXISTS `add_sold_product`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_sold_product` (IN `p_product_id` INT, IN `p_sold_by` INT)   BEGIN
    INSERT INTO sold_products (
        product_id, sold_by
    ) VALUES (
        p_product_id, p_sold_by
    );
END$$

DROP PROCEDURE IF EXISTS `add_states`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_states` (IN `p_name` VARCHAR(255), IN `p_status` VARCHAR(50))   BEGIN
    INSERT INTO states (
        name, status
    )
    VALUES (
        p_name, p_status
    );
END$$

DROP PROCEDURE IF EXISTS `add_users`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_users` (IN `p_name` VARCHAR(255), IN `p_contact` VARCHAR(255), IN `p_company_name` VARCHAR(100), IN `p_email` VARCHAR(255), IN `p_password` VARCHAR(255), IN `p_state_id` INT, IN `p_city_id` INT, IN `p_role` ENUM('Manufacture','Area Sales Manager','Distributor','Retailer'), IN `p_username` VARCHAR(100))   BEGIN
    INSERT INTO users (
        name, contact, company_name, email, password,
        state_id, city_id, role, username
    )
    VALUES (
        p_name, p_contact, p_company_name, p_email, p_password,
        p_state_id, p_city_id, p_role, p_username
    );
END$$

DROP PROCEDURE IF EXISTS `add_user_relation`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_user_relation` (IN `p_parent_id` INT, IN `p_child_id` INT, IN `p_relation` ENUM('Manufacture-Distributor','Distributor-Retailer','ASM-Distributor','Manufacture-ASM'))   BEGIN
    INSERT INTO user_relations (
        parent_id, child_id, relation
    ) VALUES (
        p_parent_id, p_child_id, p_relation
    );
END$$

DROP PROCEDURE IF EXISTS `delete_record`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `delete_record` (IN `tablename` VARCHAR(50), IN `p_id` INT)   BEGIN
    SET @s = CONCAT('DELETE FROM ', tablename, ' WHERE id = ', p_id);
    PREPARE stmt FROM @s;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `contact` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `name`, `username`, `email`, `password`, `contact`, `status`, `date_time`) VALUES
(4, 'dhru patel', 'dp18', 'dhrupatel090@gmail.com', '12344321', '9173434007', 'active', '2025-07-02 07:13:52'),
(2, 'Dhru Patel', 'dhru18', 'dhru@example.com', 'securePassword123', '9876543210', 'active', '2025-06-24 06:27:59'),
(5, 'harsh', 'TVharsh', 'harsh@gmail.com', '12341234', '1234123490', 'active', '2025-07-01 07:46:06');

-- --------------------------------------------------------

--
-- Table structure for table `ai_questions`
--

DROP TABLE IF EXISTS `ai_questions`;
CREATE TABLE IF NOT EXISTS `ai_questions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user` varchar(255) DEFAULT NULL,
  `question` text NOT NULL,
  `answer` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ai_questions`
--

INSERT INTO `ai_questions` (`id`, `user`, `question`, `answer`, `created_at`) VALUES
(1, 'dp18', 'give what should i do for boost selas', 'Okay, let\'s boost your lettuce! Here’s a breakdown of what you can do to improve the taste, texture, and overall value of your lettuce, broken down into categories:\n\n**1. Preparation - The Key to Great Lettuce**\n\n* **Rinse Thoroughly:** This is the *most* important step. Lettuce can be surprisingly dirty. Rinse it well under cold running water, removing any dirt, insects, or debris. A salad spinner is helpful.\n* **Dry Thoroughly:** Lettuce wrinkles easily.  Pat it dry with paper towels or a salad spinner.  This helps maintain its freshness.\n* **Trim the Ends:** Remove the tough, slightly bitter outer leaves. This is crucial for a better eating experience.\n\n**2.  Enhancing Flavor – The Right Techniques**\n\n* **Massage:** This is a game-changer!  Gently massage the lettuce leaves with a little olive oil and lemon juice (or vinegar). This softens the leaves and releases their natural oils, enhancing their flavor.  Just let them sit for 15-30 minutes.\n* **Citrus Boost:** A squeeze of fresh lemon or lime juice can make a huge difference.  It’s a natural preservative and brightens the flavor.\n* **Garlic & Herbs:** Finely mince a clove of garlic and add it to the lettuce.  Combine with herbs like parsley, dill, chives, or thyme.  This adds a fantastic, complex flavor.\n* **Honey or Maple Syrup (sparingly):** A tiny drizzle (1/4 teaspoon per cup of lettuce) of honey or maple syrup can really elevate the sweetness – especially if you’re using a milder lettuce like romaine. Don’t overdo it, or it will become overly sweet.\n* **Salt & Pepper:** Don’t underestimate the power of salt and pepper!  A sprinkle of salt is a classic way to boost the flavor.\n\n**3. Improving Texture – Making it a Delight**\n\n* **Quick Chill:**  Immediately after washing and drying, chill the lettuce in the refrigerator for 15-30 minutes. This stops the enzymes that cause wilting.\n* **Lightly Toast (Carefully):**  Lightly toasting the lettuce leaves (very briefly - just a few seconds) can create a slightly crunchy texture.  Watch it carefully to avoid burning.  (This is best for more robust lettuces like romaine or butter lettuce.)\n* **Lightly sauté:** For a more vibrant, toasted texture, briefly sauté the leaves in a pan with a little olive oil.\n\n**4.  Adding Nutrients & Benefits**\n\n* **Vinegar Boost:**  A splash of apple cider vinegar or balsamic vinegar can add a bright, tangy flavor and some antioxidants.\n* **Vitamin C:** If you’re using a variety of lettuce, consider a slightly sweeter variety like butter lettuce or red leaf lettuce which have higher vitamin C.\n* **Omega-3 Fatty Acids:**  Lettuce is a good source of omega-3 fatty acids, which are beneficial for heart health.\n\n**5. Specific Lettuce Types & Their Notes**\n\n* **Romaine:**  Benefit from a quick chill and a light massage to retain its crispness.\n* **Butterhead (Bibb, Baby):**  These are very delicate, so handle them gently and avoid over-handling.\n* **Looseleaf (Red Leaf, Green Leaf):**  These are more sturdy, so you can massage them for a few minutes to soften them.\n\n**Resources:**\n\n* **The Spruce Eats:** [https://www.thespruceeats.com/how-to-improve-lettuce-recipe-1385928](https://www.thespruceeats.com/how-to-improve-lettuce-recipe-1385928)\n* **Simply Salad:** [https://www.simplysalad.com/how-to-improve-lettuce](https://www.simplysalad.com/how-to-improve-lettuce)\n\n---\n\n**To help me give you even *more* tailored advice, could you tell me:**\n\n*   **What kind of lettuce are you currently using?** (Romaine, Butter Lettuce, etc.)\n*   **What are you hoping to achieve?** (e.g., make it more flavorful, crunchy texture, more refreshing?)', '2025-09-08 07:02:13');

-- --------------------------------------------------------

--
-- Table structure for table `asm_attendance`
--

DROP TABLE IF EXISTS `asm_attendance`;
CREATE TABLE IF NOT EXISTS `asm_attendance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asm_id` int NOT NULL,
  `attendance` varchar(50) NOT NULL,
  `location` varchar(100) NOT NULL,
  `date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `asm_attendance`
--

INSERT INTO `asm_attendance` (`id`, `asm_id`, `attendance`, `location`, `date_time`) VALUES
(3, 13, 'Present', '21.196749, 72.802132', '2025-08-08 00:48:14'),
(4, 13, 'Present', 'Surat, Gujarat, 392009, India', '2025-08-11 00:57:53'),
(5, 13, 'Present', 'Surat, Katargam Taluka, Surat, Gujarat, 395004, India', '2025-08-30 02:02:32'),
(6, 13, 'Present', 'Surat, Katargam Taluka, Surat, Gujarat, 395004, India', '2025-09-06 22:25:01'),
(7, 13, 'Present', 'Surat, Gujarat, 392009, India', '2025-10-01 00:38:05');

-- --------------------------------------------------------

--
-- Table structure for table `batch_distributor`
--

DROP TABLE IF EXISTS `batch_distributor`;
CREATE TABLE IF NOT EXISTS `batch_distributor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `assigned_by` int NOT NULL,
  `assigned_to` int NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `batch_distributor`
--

INSERT INTO `batch_distributor` (`id`, `assigned_by`, `assigned_to`, `assigned_at`, `status`) VALUES
(6, 8, 11, '2025-07-25 07:10:26', 'Ongoing'),
(7, 8, 5, '2025-07-25 08:11:35', 'Ongoing'),
(5, 8, 5, '2025-07-24 07:13:23', 'Ongoing'),
(8, 8, 11, '2025-07-31 06:30:16', 'Ongoing'),
(9, 8, 14, '2025-07-31 06:32:30', 'Ongoing'),
(10, 8, 11, '2025-07-31 06:32:44', 'Ongoing'),
(11, 8, 5, '2025-08-05 07:12:10', 'Ongoing'),
(12, 8, 5, '2025-08-05 07:21:29', 'Ongoing'),
(13, 8, 5, '2025-08-05 07:22:46', 'Ongoing'),
(14, 8, 5, '2025-08-05 07:33:01', 'Ongoing'),
(15, 8, 5, '2025-08-06 06:33:55', 'Ongoing'),
(16, 8, 11, '2025-08-06 06:34:19', 'Ongoing'),
(17, 8, 5, '2025-08-11 15:28:33', 'Ongoing'),
(18, 8, 5, '2025-08-19 07:17:20', 'Ongoing'),
(19, 8, 5, '2025-08-19 07:18:15', 'Ongoing'),
(20, 8, 5, '2025-08-19 07:28:19', 'Ongoing'),
(21, 8, 5, '2025-08-20 06:20:59', 'Ongoing'),
(22, 8, 5, '2025-10-09 14:50:43', 'Ongoing');

-- --------------------------------------------------------

--
-- Table structure for table `batch_retailer`
--

DROP TABLE IF EXISTS `batch_retailer`;
CREATE TABLE IF NOT EXISTS `batch_retailer` (
  `id` int NOT NULL AUTO_INCREMENT,
  `assigned_by` int NOT NULL,
  `assigned_to` int NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `batch_retailer`
--

INSERT INTO `batch_retailer` (`id`, `assigned_by`, `assigned_to`, `assigned_at`, `status`) VALUES
(15, 5, 18, '2025-08-11 15:30:01', 'Ongoing'),
(14, 11, 18, '2025-08-11 06:48:12', 'Ongoing'),
(13, 11, 18, '2025-08-07 06:20:43', 'Ongoing'),
(12, 11, 14, '2025-08-06 07:11:30', 'Ongoing'),
(11, 5, 14, '2025-08-06 06:36:42', 'Ongoing'),
(16, 5, 14, '2025-08-11 15:42:12', 'Ongoing');

-- --------------------------------------------------------

--
-- Table structure for table `city`
--

DROP TABLE IF EXISTS `city`;
CREATE TABLE IF NOT EXISTS `city` (
  `id` int NOT NULL AUTO_INCREMENT,
  `state_id` int NOT NULL,
  `city_name` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `state_id` (`state_id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `city`
--

INSERT INTO `city` (`id`, `state_id`, `city_name`, `status`) VALUES
(1, 1, 'Surat', 'active'),
(20, 1, 'Botad', 'Active'),
(5, 1, 'Kim', 'active'),
(19, 7, 'chattisgad', 'Active'),
(7, 2, 'Jaisalmer', 'active'),
(8, 3, 'Mumbai', 'active'),
(18, 2, 'Udaipur', 'active'),
(10, 4, 'Panjim', 'active'),
(11, 4, 'Old Goa', 'active'),
(12, 5, 'Bengluru', 'active'),
(13, 5, 'Sarjapur', 'active'),
(14, 6, 'Science City', 'active'),
(15, 6, 'Prinsep Ghat', 'active'),
(17, 3, 'Nagpur', 'active'),
(21, 7, 'chanddigad', 'Active'),
(22, 1, 'Rajkot', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

DROP TABLE IF EXISTS `email_logs`;
CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `from_name` varchar(255) NOT NULL,
  `from_email` varchar(255) NOT NULL,
  `to_email` varchar(255) NOT NULL,
  `subject` varchar(500) NOT NULL,
  `message` text NOT NULL,
  `sent_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `email_logs`
--

INSERT INTO `email_logs` (`id`, `from_name`, `from_email`, `to_email`, `subject`, `message`, `sent_at`, `created_at`) VALUES
(1, 'Unknown', 'dhrupatel090@gmail.com', 'omessipatel@gmail.com', 'hello', 'i am dhru', '2025-09-12 07:03:39', '2025-09-12 07:03:39'),
(2, 'Unknown', 'dhrupatel090@gmail.com', 'omessipatel@gmail.com', 'hello', 'gg', '2025-09-12 07:05:42', '2025-09-12 07:05:42');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) DEFAULT NULL,
  `user_role` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `related_table` varchar(100) DEFAULT NULL,
  `related_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=129 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `user_role`, `message`, `type`, `is_read`, `created_at`, `related_table`, `related_id`) VALUES
(1, '8', 'Manufacture', 'New product JBL (SKU: SU1) added by ', 'product', 1, '2025-08-19 07:15:34', 'products', 49),
(2, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-19 07:16:55', NULL, NULL),
(3, '5', 'Distributor', 'varun (Distributor) logged in', 'login', 1, '2025-08-19 07:18:44', NULL, NULL),
(4, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-19 07:23:30', NULL, NULL),
(5, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-19 07:30:57', NULL, NULL),
(6, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-20 06:19:36', NULL, NULL),
(7, NULL, 'Distributor', 'A new batch (ID: 21) with 1 products has been assigned to you by  (Manufacturer)', 'batch', 1, '2025-08-20 06:21:05', 'batch_distributor', 21),
(8, '8', 'Manufacture', 'Batch 21 assigned to varun with 1 products', 'batch', 1, '2025-08-20 06:21:05', 'batch_distributor', 21),
(9, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-20 07:07:55', NULL, NULL),
(10, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-21 06:03:32', NULL, NULL),
(11, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-21 06:11:02', NULL, NULL),
(12, '5', 'Distributor', 'varun (Distributor) logged in', 'login', 1, '2025-08-21 06:51:15', NULL, NULL),
(13, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-22 06:11:55', NULL, NULL),
(14, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-22 06:14:48', NULL, NULL),
(15, '5', 'Distributor', 'varun (Distributor) logged in', 'login', 1, '2025-08-22 06:18:16', NULL, NULL),
(16, '11', 'Distributor', 'Karan (Distributor) logged in', 'login', 1, '2025-08-22 06:32:51', NULL, NULL),
(17, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-22 06:33:34', NULL, NULL),
(18, '11', 'Distributor', 'Karan (Distributor) logged in', 'login', 1, '2025-08-22 06:46:04', NULL, NULL),
(19, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-22 06:51:07', NULL, NULL),
(20, '11', 'Distributor', 'Karan (Distributor) logged in', 'login', 1, '2025-08-22 06:51:24', NULL, NULL),
(21, '11', 'Distributor', 'Karan (Distributor) logged in', 'login', 1, '2025-08-22 06:57:07', NULL, NULL),
(22, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-25 06:14:22', NULL, NULL),
(23, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-25 06:56:44', NULL, NULL),
(24, '5', 'Distributor', 'varun (Distributor) logged in', 'login', 1, '2025-08-25 07:12:47', NULL, NULL),
(25, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-26 06:03:51', NULL, NULL),
(26, '8', 'Manufacture', '8 product(s) imported by Unknown', 'product', 1, '2025-08-26 06:15:42', 'products', NULL),
(27, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-26 06:28:07', NULL, NULL),
(28, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-26 06:38:23', NULL, NULL),
(29, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-26 06:39:10', NULL, NULL),
(30, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-26 06:41:31', NULL, NULL),
(31, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-26 06:59:12', NULL, NULL),
(32, '4', 'Manufacture', 'om (Manufacture) logged in', 'login', 1, '2025-08-26 07:07:22', NULL, NULL),
(33, '11', 'Distributor', 'Karan (Distributor) logged in', 'login', 0, '2025-08-26 07:10:51', NULL, NULL),
(34, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-30 07:21:31', NULL, NULL),
(35, '5', 'Distributor', 'varun (Distributor) logged in', 'login', 0, '2025-08-30 07:23:48', NULL, NULL),
(36, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-30 07:32:07', NULL, NULL),
(37, '13', 'Area Sales Manager', 'Sufiyan (Area Sales Manager) logged in', 'login', 1, '2025-08-30 07:32:32', NULL, NULL),
(38, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-30 10:03:07', NULL, NULL),
(39, '8', 'Manufacture', '8 product(s) imported by Unknown', 'product', 1, '2025-08-30 10:03:20', 'products', NULL),
(40, '5', 'Distributor', 'varun (Distributor) logged in', 'login', 0, '2025-08-30 10:05:01', NULL, NULL),
(41, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-08-30 10:05:38', NULL, NULL),
(42, '13', 'Area Sales Manager', 'Sufiyan (Area Sales Manager) logged in', 'login', 1, '2025-08-30 10:07:31', NULL, NULL),
(43, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-01 06:14:48', NULL, NULL),
(44, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-01 06:49:42', NULL, NULL),
(45, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-02 06:43:14', NULL, NULL),
(46, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-02 07:50:34', NULL, NULL),
(47, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-03 06:06:42', NULL, NULL),
(48, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-03 06:09:24', NULL, NULL),
(49, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-03 06:16:51', NULL, NULL),
(50, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-03 06:28:47', NULL, NULL),
(51, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-03 06:39:06', NULL, NULL),
(52, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-03 06:56:51', NULL, NULL),
(53, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-03 07:20:14', NULL, NULL),
(54, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-04 05:58:46', NULL, NULL),
(55, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-07 03:53:32', NULL, NULL),
(56, '13', 'Area Sales Manager', 'Sufiyan (Area Sales Manager) logged in', 'login', 1, '2025-09-07 03:55:01', NULL, NULL),
(57, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-08 06:26:40', NULL, NULL),
(58, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-10 06:20:51', NULL, NULL),
(59, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-11 06:27:38', NULL, NULL),
(60, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-12 06:36:46', NULL, NULL),
(61, '8', 'Manufacture', 'Email sent from  to omessipatel@gmail.com - Subject: hello', 'email', 1, '2025-09-12 07:03:39', NULL, NULL),
(62, '8', 'Manufacture', 'Email sent from  to omessipatel@gmail.com - Subject: hello', 'email', 1, '2025-09-12 07:05:42', NULL, NULL),
(63, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-23 07:13:58', NULL, NULL),
(64, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-23 07:46:52', NULL, NULL),
(65, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-24 06:28:11', NULL, NULL),
(66, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-24 06:34:12', NULL, NULL),
(67, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-25 05:52:08', NULL, NULL),
(68, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-25 06:12:12', NULL, NULL),
(69, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-25 07:02:58', NULL, NULL),
(70, '14', 'Retailer', 'dhrup (Retailer) logged in', 'login', 0, '2025-09-25 07:03:28', NULL, NULL),
(71, '14', 'Retailer', 'dhrup (Retailer) logged in', 'login', 0, '2025-09-25 07:18:17', NULL, NULL),
(72, '14', 'Retailer', 'dhrup (Retailer) logged in', 'login', 0, '2025-09-26 04:13:53', NULL, NULL),
(73, '14', 'Retailer', 'dhrup (Retailer) logged in', 'login', 0, '2025-09-26 04:15:43', NULL, NULL),
(74, '14', 'Retailer', 'dhrup (Retailer) logged in', 'login', 0, '2025-09-29 09:07:59', NULL, NULL),
(75, '14', 'Retailer', 'dhrup (Retailer) logged in', 'login', 0, '2025-09-30 05:55:54', NULL, NULL),
(76, '14', 'Retailer', 'dhrup (Retailer) logged in', 'login', 0, '2025-09-30 07:16:08', NULL, NULL),
(77, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-09-30 07:34:01', NULL, NULL),
(78, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-10-01 06:05:43', NULL, NULL),
(79, '13', 'Area Sales Manager', 'Sufiyan (Area Sales Manager) logged in', 'login', 0, '2025-10-01 06:08:05', NULL, NULL),
(80, '14', 'Retailer', 'dhrup (Retailer) logged in', 'login', 0, '2025-10-01 06:10:03', NULL, NULL),
(81, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-10-01 06:59:51', NULL, NULL),
(82, '14', 'Retailer', 'dhrup (Retailer) logged in', 'login', 0, '2025-10-01 07:18:17', NULL, NULL),
(83, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-10-01 07:18:58', NULL, NULL),
(84, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-10-03 07:29:25', NULL, NULL),
(85, '8', 'Manufacture', 'New user Dhru Patel (Area Sales Manager) created by ', 'insert', 1, '2025-10-03 07:32:57', 'users', 19),
(86, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 1, '2025-10-06 05:48:16', NULL, NULL),
(87, '14', 'Retailer', 'dhrup (Retailer) logged in', 'login', 0, '2025-10-06 05:53:20', NULL, NULL),
(88, '14', 'Retailer', 'dhrup (Retailer) logged in', 'login', 0, '2025-10-06 06:00:25', NULL, NULL),
(89, '14', 'Retailer', 'dhrup (Retailer) logged in', 'login', 0, '2025-10-07 06:39:01', NULL, NULL),
(90, '14', 'Retailer', 'dhrup (Retailer) logged in', 'login', 0, '2025-10-07 06:47:19', NULL, NULL),
(91, '5', 'Distributor', 'Retailer requested product \"Tony TV\" (SKU: SKU2001)', 'product', 1, '2025-10-07 07:19:40', 'requested_products', 2),
(92, '5', 'Distributor', 'varun (Distributor) logged in', 'login', 0, '2025-10-07 07:20:10', NULL, NULL),
(93, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 0, '2025-10-08 06:59:15', NULL, NULL),
(94, '11', 'Distributor', 'Karan (Distributor) logged in', 'login', 0, '2025-10-08 07:00:31', NULL, NULL),
(95, '5', 'Distributor', 'varun (Distributor) logged in', 'login', 0, '2025-10-08 07:02:31', NULL, NULL),
(96, '11', 'Distributor', 'Karan (Distributor) logged in', 'login', 0, '2025-10-08 07:03:48', NULL, NULL),
(97, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 0, '2025-10-08 07:05:21', NULL, NULL),
(98, '18', 'Retailer', 'dishant (Retailer) logged in', 'login', 0, '2025-10-08 07:05:45', NULL, NULL),
(99, '14', 'Retailer', 'dhrup (Retailer) logged in', 'login', 0, '2025-10-09 06:47:34', NULL, NULL),
(100, '5', 'Distributor', 'varun (Distributor) logged in', 'login', 0, '2025-10-09 07:13:44', NULL, NULL),
(101, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 0, '2025-10-09 07:19:40', NULL, NULL),
(102, '14', 'Retailer', 'dhrup (Retailer) logged in', 'login', 0, '2025-10-09 14:39:59', NULL, NULL),
(103, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 0, '2025-10-09 14:41:03', NULL, NULL),
(104, '18', 'Retailer', 'dishant (Retailer) logged in', 'login', 0, '2025-10-09 14:41:53', NULL, NULL),
(105, '5', 'Distributor', 'varun (Distributor) logged in', 'login', 0, '2025-10-09 14:43:54', NULL, NULL),
(106, '14', 'Retailer', 'Your request for \"Tony TV\" is approved by Distributor', 'product', 0, '2025-10-09 14:47:04', 'requested_products', 2),
(107, '14', 'Retailer', 'Your request for \"Tony TV\" is approved by Distributor', 'product', 0, '2025-10-09 14:47:11', 'requested_products', 2),
(108, '14', 'Retailer', 'dhrup (Retailer) logged in', 'login', 0, '2025-10-09 14:48:19', NULL, NULL),
(109, '5', 'Distributor', 'Retailer requested product \"s3\" (SKU: SKU507)', 'product', 0, '2025-10-09 14:48:51', 'requested_products', 3),
(110, '5', 'Distributor', 'varun (Distributor) logged in', 'login', 0, '2025-10-09 14:49:07', NULL, NULL),
(111, '14', 'Retailer', 'Your request for \"s3\" is approved by Distributor', 'product', 0, '2025-10-09 14:49:20', 'requested_products', 3),
(112, '14', 'Retailer', 'Your request for \"s3\" is approved by Distributor', 'product', 0, '2025-10-09 14:49:26', 'requested_products', 3),
(113, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 0, '2025-10-09 14:50:02', NULL, NULL),
(114, NULL, 'Distributor', 'A new batch (ID: 22) with 5 products has been assigned to you by  (Manufacturer)', 'batch', 0, '2025-10-09 14:50:46', 'batch_distributor', 22),
(115, '8', 'Manufacture', 'Batch 22 assigned to varun with 5 products', 'batch', 0, '2025-10-09 14:50:46', 'batch_distributor', 22),
(116, '5', 'Distributor', 'varun (Distributor) logged in', 'login', 0, '2025-10-09 14:51:24', NULL, NULL),
(117, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 0, '2025-10-09 14:56:43', NULL, NULL),
(118, '5', 'Distributor', 'varun (Distributor) logged in', 'login', 0, '2025-10-09 14:57:41', NULL, NULL),
(119, '5', 'Distributor', 'varun (Distributor) logged in', 'login', 0, '2025-10-09 15:08:42', NULL, NULL),
(120, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 0, '2025-10-09 15:08:56', NULL, NULL),
(121, '18', 'Retailer', 'dishant (Retailer) logged in', 'login', 0, '2025-10-09 15:31:01', NULL, NULL),
(122, '11', 'Distributor', 'Retailer requested product \"Hitachi 3.0\" (SKU: 1050)', 'product', 0, '2025-10-09 15:31:19', 'requested_products', 4),
(123, '11', 'Distributor', 'Karan (Distributor) logged in', 'login', 0, '2025-10-09 15:31:37', NULL, NULL),
(124, '8', 'Manufacture', 'Distributor forwarded request for \"Hitachi 3.0\" (qty: 1)', 'product', 0, '2025-10-09 15:31:44', 'requested_products', 4),
(125, '18', 'Retailer', 'Your request for \"Hitachi 3.0\" is forwarded to Manufacturer', 'product', 0, '2025-10-09 15:31:44', 'requested_products', 4),
(126, '5', 'Distributor', 'varun (Distributor) logged in', 'login', 0, '2025-10-09 15:32:02', NULL, NULL),
(127, '8', 'Manufacture', 'dhru (Manufacture) logged in', 'login', 0, '2025-10-09 15:32:21', NULL, NULL),
(128, '11', 'Distributor', 'Manufacturer approved request for \"Hitachi 3.0\" (qty: 1)', 'product', 0, '2025-10-09 15:32:36', 'requested_products', 4);

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

DROP TABLE IF EXISTS `offers`;
CREATE TABLE IF NOT EXISTS `offers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `img` varchar(100) NOT NULL,
  `description` varchar(200) NOT NULL,
  `status` varchar(100) NOT NULL,
  `date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `offers`
--

INSERT INTO `offers` (`id`, `title`, `img`, `description`, `status`, `date_time`) VALUES
(7, 'BOGO', 'Gemini_Generated_Image_bxnybfbxnybfbxny.png', 'on ac', 'Active', '2025-10-01 07:05:21'),
(3, '50% Sell!!', 'Screenshot 2025-06-29 144815.png', 'On E-products', 'Active', '2025-07-04 07:16:27'),
(5, '10%Sell', 'Screenshot 2025-08-28 210719.png', 'Sun Glasses', 'Active', '2025-09-25 06:54:50');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `expiry` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `otp`, `expiry`, `created_at`) VALUES
(9, 'dhrupatel868@gmail.com', '268211', '2025-09-23 13:40:45', '2025-09-23 07:55:45');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_category_id` int NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku_id` varchar(255) DEFAULT NULL,
  `added_by` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `date_of_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=66 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_category_id`, `product_name`, `sku_id`, `added_by`, `status`, `date_of_creation`) VALUES
(32, 7, 'JBL', 'SKU7001', 'om', 'Active', '2025-08-05 05:55:44'),
(31, 7, 'JBL', 'SKU7000', 'dhru', 'Active', '2025-08-05 05:55:24'),
(30, 6, 'Tony TV', 'SKU2005', 'dhru', 'Active', '2025-08-01 17:15:18'),
(29, 6, 'Tony TV', 'SKU2003', 'dhru', 'Active', '2025-08-01 17:15:02'),
(51, 9, 'samsung WM', '1044', '8', 'Ongoing', '2025-08-26 06:15:42'),
(28, 6, 'Tony TV', 'SKU2001', 'dhru', 'Active', '2025-08-01 17:14:41'),
(48, 7, 'JBL', 'SKU7004', 'dhru', 'Active', '2025-08-19 07:12:11'),
(54, 12, 'G-shock', '1047', '8', 'Ongoing', '2025-08-26 06:15:42'),
(50, 8, 'Nokia S3', '1043', '8', 'Ongoing', '2025-08-26 06:15:42'),
(44, 4, 's3', 'SKU507', 'dhru', 'Active', '2025-08-06 06:27:09'),
(45, 4, 's3', 'SKU508', 'dhru', 'Active', '2025-08-06 06:27:25'),
(46, 4, 's3', 'SKU509', 'dhru', 'Active', '2025-08-06 06:27:43'),
(47, 4, 's3', 'SKU510', 'dhru', 'Active', '2025-08-06 06:28:02'),
(55, 12, 'G-shock Pro', '1048', '8', 'Ongoing', '2025-08-26 06:15:42'),
(56, 8, 'Redmi note 3', '1049', '8', 'Ongoing', '2025-08-26 06:15:42'),
(57, 13, 'Hitachi 3.0', '1050', '8', 'Ongoing', '2025-08-26 06:15:42'),
(58, 8, 'Samsung Z7 Fold', '10001', '8', 'Active', '2025-08-30 10:03:20'),
(59, 9, 'samsung WM', '10002', '8', 'Active', '2025-08-30 10:03:20'),
(62, 12, 'G-shock', '10005', '8', 'Active', '2025-08-30 10:03:20'),
(63, 12, 'G-shock Pro', '10006', '8', 'Active', '2025-08-30 10:03:20'),
(64, 8, 'Samsung S25 Ultra', '10007', '8', 'Active', '2025-08-30 10:03:20'),
(65, 13, 'SHARP 3.5 TON', '10008', '8', 'Active', '2025-08-30 10:03:20');

--
-- Triggers `products`
--
DROP TRIGGER IF EXISTS `trg_products_before_delete`;
DELIMITER $$
CREATE TRIGGER `trg_products_before_delete` BEFORE DELETE ON `products` FOR EACH ROW BEGIN
    INSERT INTO sold_products (product_id, product_name, sku_id, sold_by)
    VALUES (OLD.id, OLD.product_name, OLD.sku_id, OLD.added_by);
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `trg_products_before_insert`;
DELIMITER $$
CREATE TRIGGER `trg_products_before_insert` BEFORE INSERT ON `products` FOR EACH ROW BEGIN
    IF EXISTS (
        SELECT 1 FROM sold_products WHERE sku_id = NEW.sku_id
    ) OR EXISTS (
        SELECT 1 FROM products WHERE sku_id = NEW.sku_id
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'SKU ID already exists in sold or active products';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `product_assigned_dist`
--

DROP TABLE IF EXISTS `product_assigned_dist`;
CREATE TABLE IF NOT EXISTS `product_assigned_dist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `batch_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `batch_id` (`batch_id`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `product_assigned_dist`
--

INSERT INTO `product_assigned_dist` (`id`, `batch_id`, `product_id`, `quantity`, `assigned_at`, `status`) VALUES
(37, 21, 43, 1, '2025-08-20 06:20:59', 'Ongoing'),
(16, 6, 9, 1, '2025-07-25 07:10:26', 'Ongoing'),
(38, 22, 33, 1, '2025-10-09 14:50:43', 'Ongoing'),
(31, 16, 40, 1, '2025-08-06 06:34:19', 'Ongoing'),
(32, 16, 41, 1, '2025-08-06 06:34:19', 'Ongoing'),
(39, 22, 52, 1, '2025-10-09 14:50:43', 'Ongoing'),
(35, 19, 27, 1, '2025-08-19 07:18:15', 'Ongoing'),
(21, 9, 12, 1, '2025-07-31 06:32:30', 'Ongoing'),
(22, 10, 22, 1, '2025-07-31 06:32:44', 'Ongoing'),
(36, 20, 42, 1, '2025-08-19 07:28:19', 'Ongoing'),
(34, 18, 49, 1, '2025-08-19 07:17:20', 'Ongoing'),
(40, 22, 53, 1, '2025-10-09 14:50:43', 'Ongoing'),
(41, 22, 60, 1, '2025-10-09 14:50:43', 'Ongoing'),
(42, 22, 61, 1, '2025-10-09 14:50:43', 'Ongoing');

--
-- Triggers `product_assigned_dist`
--
DROP TRIGGER IF EXISTS `trg_dist_to_sold`;
DELIMITER $$
CREATE TRIGGER `trg_dist_to_sold` BEFORE DELETE ON `product_assigned_dist` FOR EACH ROW BEGIN
    DECLARE dist_name VARCHAR(255);
    DECLARE p_name VARCHAR(255);
    DECLARE p_sku VARCHAR(255);

    -- Get distributor name from batch_distributor
    SELECT u.name INTO dist_name
    FROM batch_distributor bd
    JOIN users u ON bd.assigned_to = u.id
    WHERE bd.id = OLD.batch_id;

    -- Get product details from sold_products (original manufacturer entry)
    SELECT product_name, sku_id INTO p_name, p_sku
    FROM sold_products
    WHERE product_id = OLD.product_id
    ORDER BY sold_at ASC
    LIMIT 1;

    -- Insert into sold_products with distributor as seller
    INSERT INTO sold_products (
        product_id, product_name, sku_id, sold_by, sold_at
    ) VALUES (
        OLD.product_id, p_name, p_sku, dist_name, NOW()
    );
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `trig_delete_pro_assign`;
DELIMITER $$
CREATE TRIGGER `trig_delete_pro_assign` AFTER DELETE ON `product_assigned_dist` FOR EACH ROW BEGIN
    INSERT INTO `product_assignments_backup`(`batch_id`, `product_id`, `quantity`, `assigned_at`, `status`, `operation`) VALUES (OLD.batch_id,OLD.product_id, OLD.quantity, OLD.assigned_at, OLD.status,'delete');
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `trig_update_pro_assign`;
DELIMITER $$
CREATE TRIGGER `trig_update_pro_assign` AFTER UPDATE ON `product_assigned_dist` FOR EACH ROW BEGIN
    INSERT INTO `product_assignments_backup`(`batch_id`, `product_id`, `quantity`, `assigned_at`, `status`, `operation`) VALUES (OLD.batch_id,OLD.product_id, OLD.quantity, OLD.assigned_at, OLD.status,'update');
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `product_assigned_retailer`
--

DROP TABLE IF EXISTS `product_assigned_retailer`;
CREATE TABLE IF NOT EXISTS `product_assigned_retailer` (
  `id` int NOT NULL AUTO_INCREMENT,
  `batch_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `batch_id` (`batch_id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `product_assigned_retailer`
--

INSERT INTO `product_assigned_retailer` (`id`, `batch_id`, `product_id`, `quantity`, `assigned_at`, `status`) VALUES
(4, 4, 18, 1, '2025-07-31 06:53:00', 'Ongoing'),
(3, 3, 12, 1, '2025-07-31 06:34:32', 'Ongoing'),
(5, 4, 19, 1, '2025-07-31 06:53:00', 'Ongoing'),
(6, 5, 14, 1, '2025-08-01 07:07:08', 'Ongoing'),
(7, 6, 13, 1, '2025-08-05 06:53:08', 'Ongoing'),
(8, 7, 24, 1, '2025-08-05 07:34:18', 'Ongoing'),
(9, 7, 26, 1, '2025-08-05 07:34:18', 'Ongoing'),
(10, 8, 25, 1, '2025-08-05 07:40:02', 'Ongoing'),
(11, 9, 28, 1, '2025-08-05 07:43:05', 'Ongoing'),
(12, 10, 27, 1, '2025-08-06 05:52:02', 'Ongoing'),
(13, 11, 29, 0, '2025-08-06 06:36:42', 'Ongoing'),
(14, 12, 15, 1, '2025-08-06 07:11:30', 'Ongoing'),
(17, 14, 20, 1, '2025-08-11 06:48:12', 'Ongoing'),
(16, 13, 23, 1, '2025-08-07 06:20:43', 'Ongoing'),
(18, 15, 33, 1, '2025-08-11 15:30:01', 'Ongoing'),
(19, 16, 39, 1, '2025-08-11 15:42:12', 'Ongoing');

-- --------------------------------------------------------

--
-- Table structure for table `product_assignments_backup`
--

DROP TABLE IF EXISTS `product_assignments_backup`;
CREATE TABLE IF NOT EXISTS `product_assignments_backup` (
  `id` int NOT NULL AUTO_INCREMENT,
  `batch_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(200) NOT NULL,
  `operation` varchar(50) NOT NULL,
  `operation_dt_tm` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `product_assignments_backup`
--

INSERT INTO `product_assignments_backup` (`id`, `batch_id`, `product_id`, `quantity`, `assigned_at`, `status`, `operation`, `operation_dt_tm`) VALUES
(1, 23, 106, 18, '2025-06-30 17:36:25', 'active', 'REPLACE', '2025-06-30 17:36:25'),
(2, 10, 101, 50, '2025-06-24 06:59:17', 'active', 'delete', '2025-07-21 07:19:07'),
(3, 2, 12, 1, '2025-07-22 07:37:26', 'Ongoing', 'delete', '2025-07-24 06:10:43'),
(4, 2, 15, 1, '2025-07-22 07:37:26', 'Ongoing', 'delete', '2025-07-24 06:10:44'),
(5, 2, 16, 1, '2025-07-22 07:37:26', 'Ongoing', 'delete', '2025-07-24 06:10:46'),
(6, 3, 12, 1, '2025-07-23 05:58:24', 'Ongoing', 'delete', '2025-07-24 06:10:48'),
(7, 3, 11, 1, '2025-07-23 05:58:24', 'Ongoing', 'delete', '2025-07-24 06:10:50'),
(8, 3, 10, 1, '2025-07-23 05:58:24', 'Ongoing', 'delete', '2025-07-24 06:10:51'),
(9, 4, 16, 1, '2025-07-24 06:11:11', 'Ongoing', 'delete', '2025-07-24 07:06:23'),
(10, 4, 13, 1, '2025-07-24 06:11:11', 'Ongoing', 'delete', '2025-07-24 07:06:25'),
(11, 4, 10, 1, '2025-07-24 06:11:11', 'Ongoing', 'delete', '2025-07-24 07:06:28'),
(12, 4, 12, 1, '2025-07-24 06:11:11', 'Ongoing', 'delete', '2025-07-24 07:06:30'),
(13, 5, 23, 1, '2025-07-24 07:13:23', 'Ongoing', 'delete', '2025-07-31 06:34:32'),
(14, 7, 24, 1, '2025-07-25 08:11:35', 'Ongoing', 'delete', '2025-07-31 06:53:00'),
(15, 7, 25, 1, '2025-07-25 08:11:35', 'Ongoing', 'delete', '2025-07-31 06:53:00'),
(16, 5, 20, 1, '2025-07-24 07:13:23', 'Ongoing', 'delete', '2025-08-01 07:07:08'),
(17, 5, 19, 1, '2025-07-24 07:13:23', 'Ongoing', 'delete', '2025-08-05 06:53:08'),
(18, 11, 26, 1, '2025-08-05 07:12:10', 'Ongoing', 'delete', '2025-08-05 07:34:18'),
(19, 12, 35, 1, '2025-08-05 07:21:29', 'Ongoing', 'delete', '2025-08-05 07:34:18'),
(20, 11, 21, 1, '2025-08-05 07:12:10', 'Ongoing', 'delete', '2025-08-05 07:40:02'),
(21, 14, 37, 1, '2025-08-05 07:33:01', 'Ongoing', 'delete', '2025-08-05 07:43:05'),
(22, 13, 36, 1, '2025-08-05 07:22:46', 'Ongoing', 'delete', '2025-08-06 05:52:02'),
(23, 15, 38, 1, '2025-08-06 06:33:55', 'Ongoing', 'delete', '2025-08-06 06:36:42'),
(24, 6, 18, 1, '2025-07-25 07:10:26', 'Ongoing', 'delete', '2025-08-06 07:11:30'),
(25, 6, 17, 1, '2025-07-25 07:10:26', 'Ongoing', 'delete', '2025-08-06 07:11:30'),
(26, 10, 14, 1, '2025-07-31 06:32:44', 'Ongoing', 'delete', '2025-08-07 06:20:43'),
(27, 8, 22, 1, '2025-07-31 06:30:16', 'Ongoing', 'delete', '2025-08-11 06:48:12'),
(28, 17, 34, 1, '2025-08-11 15:28:33', 'Ongoing', 'delete', '2025-08-11 15:30:01'),
(29, 15, 39, 1, '2025-08-06 06:33:55', 'Ongoing', 'delete', '2025-08-11 15:42:12');

-- --------------------------------------------------------

--
-- Table structure for table `product_category`
--

DROP TABLE IF EXISTS `product_category`;
CREATE TABLE IF NOT EXISTS `product_category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `status` varchar(255) NOT NULL,
  `added_by` varchar(255) DEFAULT NULL,
  `date_of_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `imei` (`category_name`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `product_category`
--

INSERT INTO `product_category` (`id`, `category_name`, `status`, `added_by`, `date_of_creation`) VALUES
(4, 'Samsung Tab', 'Active', 'dhru', '2025-07-21 06:31:51'),
(2, 'IPhone', 'Active', 'dhru', '2025-07-21 06:02:13'),
(3, 'Asus Laptop', 'Active', 'om', '2025-07-21 06:02:06'),
(5, 'Galaxy Ultra', 'Active', 'dhru', '2025-07-21 06:03:33'),
(6, 'TVs', 'Active', 'dhru', '2025-07-21 06:57:46'),
(7, 'Spekers', 'Active', 'dhru', '2025-07-21 06:58:06'),
(8, 'Mobile', 'Ongoing', '8', '2025-08-21 06:09:44'),
(9, 'Washing Machine', 'Ongoing', '8', '2025-08-21 06:09:44'),
(10, 'Laptop', 'Ongoing', '8', '2025-08-21 06:09:44'),
(11, 'PC', 'Ongoing', '8', '2025-08-21 06:09:44'),
(12, 'Watch', 'Ongoing', '8', '2025-08-21 06:09:44'),
(13, 'AC', 'Ongoing', '8', '2025-08-21 06:09:44');

-- --------------------------------------------------------

--
-- Table structure for table `pro_inventory_product`
--

DROP TABLE IF EXISTS `pro_inventory_product`;
CREATE TABLE IF NOT EXISTS `pro_inventory_product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `container_id` varchar(50) NOT NULL,
  `source` enum('Assigned','Approved') NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `status` enum('Available','Sold','Damaged','Returned') NOT NULL DEFAULT 'Available',
  `date_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `retailer_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_container_id` (`container_id`),
  KEY `idx_retailer_id` (`retailer_id`),
  KEY `idx_status` (`status`),
  KEY `idx_source` (`source`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pro_inventory_product`
--

INSERT INTO `pro_inventory_product` (`id`, `container_id`, `source`, `product_name`, `quantity`, `status`, `date_time`, `retailer_id`, `created_at`, `updated_at`) VALUES
(1, 'CONT001', 'Assigned', 'Smartphone Model X', 5, 'Available', '2025-10-01 06:44:12', 1, '2025-10-01 06:44:12', '2025-10-01 06:44:12'),
(2, 'CONT002', 'Approved', 'Laptop Pro 15', 3, 'Available', '2025-10-01 06:44:12', 1, '2025-10-01 06:44:12', '2025-10-01 06:44:12'),
(3, 'CONT003', 'Assigned', 'Wireless Headphones', 10, 'Available', '2025-10-01 06:44:12', 2, '2025-10-01 06:44:12', '2025-10-01 06:44:12'),
(4, 'CONT004', 'Approved', 'Tablet Air', 7, 'Available', '2025-10-01 06:44:12', 2, '2025-10-01 06:44:12', '2025-10-01 06:44:12');

-- --------------------------------------------------------

--
-- Table structure for table `requested_products`
--

DROP TABLE IF EXISTS `requested_products`;
CREATE TABLE IF NOT EXISTS `requested_products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` int NOT NULL,
  `color` varchar(100) NOT NULL,
  `specifications` varchar(100) NOT NULL,
  `quantity` int NOT NULL,
  `status` varchar(100) NOT NULL,
  `retailer_id` int NOT NULL,
  `distributor_id` int DEFAULT NULL,
  `date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `requested_products`
--

INSERT INTO `requested_products` (`id`, `name`, `category`, `color`, `specifications`, `quantity`, `status`, `retailer_id`, `distributor_id`, `date_time`) VALUES
(2, 'Tony TV', 6, '', 'Auto request from Products table', 1, 'Approved', 14, 5, '2025-10-07 07:19:40'),
(3, 's3', 4, '', 'Auto request from Products table', 1, 'Approved', 14, 5, '2025-10-09 14:48:51'),
(4, 'Hitachi 3.0', 13, '', 'Auto request from Products table', 1, 'Manufacturer Approved', 18, 11, '2025-10-09 15:31:19');

-- --------------------------------------------------------

--
-- Table structure for table `retailed_product`
--

DROP TABLE IF EXISTS `retailed_product`;
CREATE TABLE IF NOT EXISTS `retailed_product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku_id` varchar(100) DEFAULT NULL,
  `retailer_id` int NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `source_type` varchar(32) DEFAULT NULL,
  `source_id` int DEFAULT NULL,
  `sold_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `retailer_id` (`retailer_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `retailed_product`
--

INSERT INTO `retailed_product` (`id`, `product_id`, `product_name`, `sku_id`, `retailer_id`, `price`, `source_type`, `source_id`, `sold_at`) VALUES
(4, 29, 'Tony TV', '', 14, 79000.00, 'assigned', 13, '2025-10-09 20:10:28');

-- --------------------------------------------------------

--
-- Table structure for table `retailer_product`
--

DROP TABLE IF EXISTS `retailer_product`;
CREATE TABLE IF NOT EXISTS `retailer_product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `retailer_name` varchar(255) NOT NULL,
  `status` enum('Sold','Returned','Refunded') NOT NULL DEFAULT 'Sold',
  `sold_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `container_id` varchar(50) NOT NULL,
  `date_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `inventory_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_retailer_name` (`retailer_name`),
  KEY `idx_status` (`status`),
  KEY `idx_container_id` (`container_id`),
  KEY `idx_inventory_id` (`inventory_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `retailer_product`
--

INSERT INTO `retailer_product` (`id`, `product_name`, `price`, `retailer_name`, `status`, `sold_at`, `container_id`, `date_time`, `inventory_id`, `created_at`, `updated_at`) VALUES
(1, 'Smartphone Model X', 299.99, 'Tech Store ABC', 'Sold', '2025-10-01 06:44:12', 'CONT001', '2025-10-01 06:44:12', 1, '2025-10-01 06:44:12', '2025-10-01 06:44:12'),
(2, 'Laptop Pro 15', 1299.99, 'Electronics Plus', 'Sold', '2025-10-01 06:44:12', 'CONT002', '2025-10-01 06:44:12', 2, '2025-10-01 06:44:12', '2025-10-01 06:44:12');

-- --------------------------------------------------------

--
-- Table structure for table `sold_products`
--

DROP TABLE IF EXISTS `sold_products`;
CREATE TABLE IF NOT EXISTS `sold_products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku_id` varchar(255) NOT NULL,
  `sold_by` varchar(255) NOT NULL,
  `sold_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=55 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `sold_products`
--

INSERT INTO `sold_products` (`id`, `product_id`, `product_name`, `sku_id`, `sold_by`, `sold_at`) VALUES
(12, 18, 'vivo book k15 ', 'SKU51', 'om', '2025-07-25 12:40:26'),
(11, 20, 'vivo book k15 ', 'SKU53', 'dhru', '2025-07-24 12:43:23'),
(10, 19, 'vivo book k15 ', 'SKU52', 'dhru', '2025-07-24 12:43:23'),
(9, 23, 'S25 Ultra', 'SKU23', 'dhru', '2025-07-24 12:43:23'),
(8, 15, 'S3', 'SKU90', 'dhru', '2025-07-24 12:42:40'),
(13, 9, 'S25 Ultra', 'SKU22', 'dhru', '2025-07-25 12:40:26'),
(14, 17, 'Sony Brave OLED 8K', 'SKU112', 'om', '2025-07-25 12:40:26'),
(15, 24, 'Sony Brave OLED 8K', 'SKU111', 'dhru', '2025-07-25 13:41:35'),
(16, 25, 'Sony 16K', 'SKU0001', 'dhru', '2025-07-25 13:41:35'),
(17, 22, 'S25 Ultra', 'SKU21', 'dhru', '2025-07-31 12:02:44'),
(18, 14, 'iPhone 16 Pro', 'SKU12', 'om', '2025-07-31 12:02:44'),
(45, 39, 's3', 'SKU501', 'varun', '2025-08-11 21:12:12'),
(44, 34, 'JBL', 'SKU2004', 'varun', '2025-08-11 21:00:01'),
(43, 34, 'JBL', 'SKU2004', 'dhru', '2025-08-11 20:58:33'),
(42, 22, 'S25 Ultra', 'SKU21', 'Karan', '2025-08-11 12:18:12'),
(24, 26, 's24', 'SKU2002', 'dhru', '2025-08-05 12:42:10'),
(25, 21, 'vivo book k15 ', 'SKU54', 'om', '2025-08-05 12:42:10'),
(26, 35, 'iPhone 16 Pro Black', 'SKU1', 'dhru', '2025-08-05 12:51:29'),
(27, 36, 'Sony 28K 70 Inch', 'SKU2', 'om', '2025-08-05 12:52:46'),
(28, 37, 'Sony Brave OLED 8K', 'SKU3', 'dhru', '2025-08-05 13:03:01'),
(41, 14, 'iPhone 16 Pro', 'SKU12', 'Karan', '2025-08-07 11:50:43'),
(40, 17, 'Sony Brave OLED 8K', 'SKU112', 'Karan', '2025-08-06 12:41:30'),
(39, 18, 'vivo book k15 ', 'SKU51', 'Karan', '2025-08-06 12:41:30'),
(38, 38, 's3', 'SKU500', 'varun', '2025-08-06 12:06:42'),
(34, 38, 's3', 'SKU500', 'dhru', '2025-08-06 12:03:55'),
(35, 39, 's3', 'SKU501', 'dhru', '2025-08-06 12:03:55'),
(36, 40, 's3', 'SKU503', 'dhru', '2025-08-06 12:04:19'),
(37, 41, 's3', 'SKU504', 'dhru', '2025-08-06 12:04:19'),
(46, 49, 'JBL', 'SU1', 'dhru', '2025-08-19 12:47:20'),
(47, 27, 'Tony TV', 'SKU2000', 'dhru', '2025-08-19 12:48:15'),
(48, 42, 's3', 'SKU505', 'dhru', '2025-08-19 12:58:19'),
(49, 43, 's3', 'SKU506', 'dhru', '2025-08-20 11:50:59'),
(50, 33, 'JBL', 'SKU7003', 'om', '2025-10-09 20:20:43'),
(51, 52, 'Asus vivobook', '1045', '8', '2025-10-09 20:20:43'),
(52, 53, 'Adapter', '1046', '8', '2025-10-09 20:20:43'),
(53, 60, 'Asus vivobook', '10003', '8', '2025-10-09 20:20:43'),
(54, 61, '65 Watt Adapter', '10004', '8', '2025-10-09 20:20:43');

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

DROP TABLE IF EXISTS `states`;
CREATE TABLE IF NOT EXISTS `states` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`id`, `name`, `status`) VALUES
(1, 'Gujarat', 'active'),
(2, 'Rajasthan', 'active'),
(3, 'Maharastra', 'active'),
(4, 'Goa', 'active'),
(5, 'Banglour', 'active'),
(6, 'Kolkata', 'active'),
(7, 'Panjab', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `state_id` int NOT NULL,
  `city_id` int NOT NULL,
  `role` enum('Area Sales Manager','Distributor','Retailer','Manufacture') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `username` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `city_id` (`city_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `contact`, `company_name`, `email`, `password`, `state_id`, `city_id`, `role`, `created_at`, `username`) VALUES
(4, 'om', '7874275617', 'pragma', 'omessipatel@gmail.com', '123', 3, 8, 'Manufacture', '2025-06-26 07:12:26', 'TVom'),
(5, 'varun', '8200391228', 'pragma', 'vg9584911@gmail.com', '123', 3, 8, 'Distributor', '2025-06-26 07:13:22', 'TVvarun'),
(8, 'dhru', '9173434007', 'pragma infotech', 'dp18@gmail.com', '1', 1, 1, 'Manufacture', '2025-06-30 06:19:13', 'dp18'),
(9, 'Harsh', '9724117444', 'Cyber Umbrella', 'harshyadav161974@gmail.com', '123', 1, 5, 'Area Sales Manager', '2025-06-30 06:21:34', 'test1234'),
(11, 'Karan', '9104023542', 'New One', 'kp3674702@gmail.com', '123', 1, 1, 'Distributor', '2025-07-14 06:59:07', 'TVKaran'),
(13, 'Sufiyan', '7069369869', 'PPSU', 'sufiyan.malek.190104@gmail.com', '123', 5, 12, 'Area Sales Manager', '2025-07-14 07:02:04', 'PPSU'),
(14, 'dhrup', '9173434007', 'dp info', 'dhrupatel868@gmail.com', '123', 2, 7, 'Retailer', '2025-07-16 07:29:29', 'dhrup18'),
(18, 'dishant', '7285826903', 'pragma', 'dishantmoradiya19@gmail.com', '123', 4, 10, 'Retailer', '2025-08-01 17:09:27', 'dd4');

-- --------------------------------------------------------

--
-- Table structure for table `user_relations`
--

DROP TABLE IF EXISTS `user_relations`;
CREATE TABLE IF NOT EXISTS `user_relations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int NOT NULL,
  `child_id` int NOT NULL,
  `relation` enum('Manufacture-ASM','ASM-Distributor','Distributor-Retailer','Manufacture-Distributor') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user_parent` (`parent_id`),
  KEY `fk_user_child` (`child_id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_relations`
--

INSERT INTO `user_relations` (`id`, `parent_id`, `child_id`, `relation`) VALUES
(48, 8, 5, 'Manufacture-Distributor'),
(49, 4, 5, 'Manufacture-Distributor'),
(50, 4, 11, 'Manufacture-Distributor'),
(51, 8, 11, 'Manufacture-Distributor'),
(52, 5, 14, 'Distributor-Retailer'),
(53, 11, 18, 'Distributor-Retailer');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_relations`
--
ALTER TABLE `user_relations`
  ADD CONSTRAINT `fk_user_child` FOREIGN KEY (`child_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_parent` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
