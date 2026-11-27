-- Database: `kruta_shop`
CREATE DATABASE IF NOT EXISTS `kruta_shop` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kruta_shop`;

-- Users table (Customers & Admins)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fullname` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NULL,
  `google_id` VARCHAR(100) NULL,
  `avatar` VARCHAR(255) DEFAULT 'default-avatar.png',
  `phone` VARCHAR(30) NULL,
  `address` TEXT NULL,
  `role` ENUM('customer', 'admin') DEFAULT 'customer',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `icon` VARCHAR(50) DEFAULT 'bi-bookmark-star',
  `color` VARCHAR(20) DEFAULT '#FF6B6B'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `description` TEXT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 10,
  `image` VARCHAR(255) NOT NULL,
  `badge` VARCHAR(50) DEFAULT 'HOT',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_code` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT NULL,
  `customer_name` VARCHAR(150) NOT NULL,
  `customer_phone` VARCHAR(30) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT 'promptpay',
  `payment_slip` VARCHAR(255) NULL,
  `status` ENUM('pending', 'paid', 'shipped', 'cancelled') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order Items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `product_name` VARCHAR(200) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `quantity` INT NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Categories
INSERT INTO `categories` (`id`, `name`, `icon`, `color`) VALUES
(1, 'ดินสอ & ปากกาฟรุ้งฟริ้ง', 'bi-pen-fill', '#FF6B6B'),
(2, 'สมุด & ไดอารี่สุดคิ้วท์', 'bi-journal-bookmark-fill', '#4ECDC4'),
(3, 'สี & อุปกรณ์ศิลปะ', 'bi-palette-fill', '#FFE66D'),
(4, 'ไฮไลท์ & เทปตกแต่ง Pastel', 'bi-brush-fill', '#FF8ED4'),
(5, 'แฟ้ม & กระเป๋าเครื่องเขียน', 'bi-bag-heart-fill', '#6C5CE7')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Seed Admin & Demo Users (password: 123456)
-- Hash for 123456 is $2y$10$44i7s4rOtdg2r1F47wI60uB0Bq0.JgQzVj629qYm02j6c.Oq3xLh. or standard bcrypt
INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `role`) VALUES
(1, 'ครูต๊ะ แอดมินสุดปัง (Kruta Admin)', 'admin@kruta.com', '$2y$10$wT5a0tB7h8eR59sI.oekf.Q6rZzI0hM2QfHj9bUqHh6J4o7p6cT4W', 'admin'),
(2, 'น้องนักเรียน ตัวอย่าง', 'student@kruta.com', '$2y$10$wT5a0tB7h8eR59sI.oekf.Q6rZzI0hM2QfHj9bUqHh6J4o7p6cT4W', 'customer')
ON DUPLICATE KEY UPDATE `fullname`=VALUES(`fullname`);

-- Seed Products
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image`, `badge`) VALUES
(1, 1, 'ปากกาเจล Pastel Macaron หมึกน้ำเงินลื่นปรื๊ด (เซ็ต 6 ด้าม)', 'ปากกาเจลสัมผัสนุ่ม ด้ามพาสเทล เขียนลื่นไม่สะดุด จดโน้ตเกรด 4 แน่นอน!', 89.00, 50, 'https://images.unsplash.com/photo-1585336261026-62024b3cf68c?w=500&auto=format&fit=crop&q=80', '🔥 ขายดีที่สุด'),
(2, 1, 'ดินสอกดไส้ไม่หัก ลายหมีสุดนุ่มนิ่ม 0.5mm', 'เทคโนโลยีกันไส้หัก ปลอกยางซิลิโคนจับกระชับมือ เขียนทั้งวันไม่เมื่อยนิ้ว', 45.00, 35, 'https://images.unsplash.com/photo-1569683795645-b62e50fbf103?w=500&auto=format&fit=crop&q=80', '✨ แนะนำ'),
(3, 2, 'สมุดกริดถนอมสายตา B5 หนา 120 แกรม', 'กระดาษเนื้อดีเขียนไฮไลท์ไม่ซึม ปกเคลือบด้านกันน้ำ เย็บกี่กางได้ 180 องศา', 69.00, 40, 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=500&auto=format&fit=crop&q=80', '📚 ยอดฮิต'),
(4, 2, 'ไดอารี่ Planner วางแผนอ่านสอบ ลาย Galaxy', 'จัดตารางอ่านหนังสือสอบพิชิตคะแนนท็อป สติ๊กเกอร์ฟรีในเล่ม!', 129.00, 20, 'https://images.unsplash.com/photo-1531346878377-a5be20888e57?w=500&auto=format&fit=crop&q=80', '⚡ ลดพิเศษ'),
(5, 3, 'สีไม้เกรดพรีเมียม 36 สี ระบายเนียนนุ่มลื่น', 'สีสด ละเอียด เกลี่ยง่าย ไส้ใหญ่แข็งแรง มาพร้อมกล่องเหล็กลายวินเทจ', 250.00, 15, 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=500&auto=format&fit=crop&q=80', '🎨 สุดคุ้ม'),
(6, 4, 'เซ็ตปากกาไฮไลท์สีพาสเทลละมุนตา (6 สีหวาน)', 'เน้นข้อความไม่ปวดตา หัวตัดเขียนเส้นบางและหนาได้ในด้ามเดียว', 79.00, 60, 'https://images.unsplash.com/photo-1583485088034-697b5bc54ccd?w=500&auto=format&fit=crop&q=80', '💖 น่ารักมาก'),
(7, 4, 'วาชิเทป Washi Tape ลายเจ้าหญิง & ขนมหวาน', 'เทปกระดาษญี่ปุ่นสำหรับตกแต่งไดอารี่ ลอกออกไม่ทิ้งคราบกาว', 35.00, 80, 'https://images.unsplash.com/photo-1516962215378-7fa2e137ae93?w=500&auto=format&fit=crop&q=80', '🎀 Hit'),
(8, 5, 'กระเป๋าดินสอความจุไซส์ยักษ์ ทรงเกาหลี 3 ชั้น', 'ใส่ปากกาได้จุใจกว่า 50 แท่ง ช่องแบ่งเป็นระเบียบ กันละอองน้ำ', 159.00, 25, 'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?w=500&auto=format&fit=crop&q=80', '🎒 จุสะใจ')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);
