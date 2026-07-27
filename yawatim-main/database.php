<?php
// database.php - MySQL database connection and optional auto-init for YAWATIM system

$DB_HOST = getenv('DB_HOST');
if ($DB_HOST == false || $DB_HOST == '') {
    $DB_HOST = '127.0.0.1';
}

$DB_NAME = getenv('DB_NAME');
if ($DB_NAME == false || $DB_NAME == '') {
    $DB_NAME = 'yawatim_db';
}

$DB_USER = getenv('DB_USER');
if ($DB_USER == false || $DB_USER == '') {
    $DB_USER = 'root';
}

$DB_PASS = getenv('DB_PASS');
if ($DB_PASS == false && $DB_PASS !== '0') {
    $DB_PASS = '';
}

$DB_CHARSET = 'utf8mb4';

try {
    $dsn = "mysql:host=" . $DB_HOST . ";dbname=" . $DB_NAME . ";charset=" . $DB_CHARSET;
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    init_db($pdo);
} catch (PDOException $e) {
    die('Database Connection Error: ' . $e->getMessage());
}

function init_db($pdo) {
    // Run schema migrations for existing tables
    try {
        $has_wakalah = false;
        $check1 = $pdo->query("SHOW TABLES LIKE 'wakalah'");
        if ($check1->fetchColumn()) {
            $has_wakalah = true;
        }

        if ($has_wakalah == true) {
            $columns = $pdo->query("SHOW COLUMNS FROM wakalah LIKE 'branch_name'")->fetchAll();
            if (count($columns) == 0) {
                $pdo->exec("ALTER TABLE wakalah ADD COLUMN branch_name VARCHAR(255) DEFAULT NULL AFTER name");
            }
        }
        
        $has_donations = false;
        $check2 = $pdo->query("SHOW TABLES LIKE 'donations'");
        if ($check2->fetchColumn()) {
            $has_donations = true;
        }

        if ($has_donations == true) {
            $columns_don = $pdo->query("SHOW COLUMNS FROM donations LIKE 'address'")->fetchAll();
            if (count($columns_don) > 0) {
                $pdo->exec("ALTER TABLE donations DROP COLUMN address");
            }
            
            $cols_receipt = $pdo->query("SHOW COLUMNS FROM donations LIKE 'attachment_image'")->fetchAll();
            if (count($cols_receipt) == 0) {
                $pdo->exec("ALTER TABLE donations ADD COLUMN attachment_image VARCHAR(255) NULL AFTER booth_id");
            }
        }
    } catch (Exception $e) {
        // Safe check ignore
    }

    $tableCheck = false;
    $check_users = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($check_users->fetchColumn()) {
        $tableCheck = true;
    }
    
    if ($tableCheck == true) {
        return;
    }

    $pdo->beginTransaction();

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS wakalah (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type ENUM('individual', 'corporate') NOT NULL,
            name VARCHAR(255) NOT NULL,
            branch_name VARCHAR(255) DEFAULT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(100) NOT NULL,
            state VARCHAR(100) NOT NULL,
            status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
            ic_number VARCHAR(100) DEFAULT NULL,
            company_representative VARCHAR(255) DEFAULT NULL,
            ssm_number VARCHAR(100) DEFAULT NULL,
            hq_address TEXT DEFAULT NULL,
            address TEXT DEFAULT NULL,
            channel ENUM('BSN', 'Bank Rakyat', 'Pos Malaysia', 'EBB') DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_wakalah_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('admin', 'wakalah_individual', 'wakalah_corporate') NOT NULL,
            state VARCHAR(100) NOT NULL,
            wakalah_id INT DEFAULT NULL,
            channel ENUM('BSN', 'Bank Rakyat', 'Pos Malaysia', 'EBB') DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_users_email (email),
            KEY idx_users_wakalah_id (wakalah_id),
            CONSTRAINT fk_users_wakalah FOREIGN KEY (wakalah_id) REFERENCES wakalah(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS booths (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            location VARCHAR(255) NOT NULL,
            state VARCHAR(100) NOT NULL,
            channel ENUM('BSN', 'Bank Rakyat', 'Pos Malaysia', 'EBB') NOT NULL,
            status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS donations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            donor_name VARCHAR(255) NOT NULL,
            donor_phone VARCHAR(100) DEFAULT NULL,
            donor_email VARCHAR(255) DEFAULT NULL,
            amount DECIMAL(12,2) NOT NULL,
            donation_date DATE NOT NULL,
            donation_month VARCHAR(20) NOT NULL,
            channel ENUM('BSN', 'Bank Rakyat', 'Pos Malaysia', 'EBB') NOT NULL,
            state VARCHAR(100) NOT NULL,
            location VARCHAR(255) NOT NULL,
            wakalah_id INT DEFAULT NULL,
            booth_id INT DEFAULT NULL,
            attachment_image VARCHAR(255) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_donations_wakalah_id (wakalah_id),
            KEY idx_donations_booth_id (booth_id),
            CONSTRAINT fk_donations_wakalah FOREIGN KEY (wakalah_id) REFERENCES wakalah(id) ON DELETE SET NULL,
            CONSTRAINT fk_donations_booth FOREIGN KEY (booth_id) REFERENCES booths(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        seed_data($pdo);
        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function seed_data($pdo) {
    $admin_password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role, state, wakalah_id, channel) VALUES (?, ?, 'admin', 'Selangor', NULL, NULL)");
    $stmt->execute(['admin@yawatim.org.my', $admin_password_hash]);

    $individuals = [
        ['Ahmad Safwan', 'safwan@yawatim.org.my', '012-3456789', 'Selangor', 'Active', '890102-10-5433', 'Teacher', 'No. 12, Jalan Kenanga, Shah Alam, Selangor', 'staff123', 'BSN']
    ];

    $corporates = [
        ['BSN', 'Kuala Lumpur HQ', 'bsn@corp.com.my', '03-5551234', 'Selangor', 'Active', 'Mohd Yusof', '1234567-X', 'Level 20, Menara BSN, Kuala Lumpur', 'bsncorp123', 'BSN'],
        ['Bank Rakyat', 'Shah Alam Branch', 'bankrakyat@corp.com.my', '03-2144567', 'WP Kuala Lumpur', 'Active', 'Sarah Tan', '2234567-A', 'Level 5, Bank Rakyat Tower, Kuala Lumpur', 'rakyatcorp123', 'Bank Rakyat'],
        ['Pos Malaysia', 'Gurney Branch', 'posmalaysia@corp.com.my', '07-3338899', 'Johor', 'Active', 'Kumar Rao', '3234567-B', 'No. 10, Jalan Wong Ah Fook, Johor Bahru', 'poscorp123', 'Pos Malaysia'],
        ['EBB', 'Tebrau Branch', 'ebb@corp.com.my', '04-8889900', 'Penang', 'Active', 'Lim Lee', '4234567-C', 'No. 77, Beach Street, George Town, Penang', 'ebbcorp123', 'EBB']
    ];

    $stmt_wakalah_ind = $pdo->prepare("INSERT INTO wakalah (type, name, branch_name, email, phone, state, status, ic_number, address, channel) VALUES ('individual', ?, NULL, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_user_ind = $pdo->prepare("INSERT INTO users (email, password_hash, role, state, wakalah_id, channel) VALUES (?, ?, 'wakalah_individual', ?, ?, ?)");

    foreach ($individuals as $ind) {
        $stmt_wakalah_ind->execute([$ind[0], $ind[1], $ind[2], $ind[3], $ind[4], $ind[5], $ind[7], $ind[9]]);
        $wak_id = $pdo->lastInsertId();
        $hash = password_hash($ind[8], PASSWORD_DEFAULT);
        $stmt_user_ind->execute([$ind[1], $hash, $ind[3], $wak_id, $ind[9]]);
    }

    $stmt_wakalah_corp = $pdo->prepare("INSERT INTO wakalah (type, name, branch_name, email, phone, state, status, company_representative, ssm_number, hq_address, address, channel) VALUES ('corporate', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_user_corp = $pdo->prepare("INSERT INTO users (email, password_hash, role, state, wakalah_id, channel) VALUES (?, ?, 'wakalah_corporate', ?, ?, ?)");

    foreach ($corporates as $corp) {
        $stmt_wakalah_corp->execute([$corp[0], $corp[1], $corp[2], $corp[3], $corp[4], $corp[5], $corp[6], $corp[7], $corp[8], NULL, $corp[10]]);
        $wak_id = $pdo->lastInsertId();
        $hash = password_hash($corp[9], PASSWORD_DEFAULT);
        $stmt_user_corp->execute([$corp[2], $hash, $corp[4], $wak_id, $corp[10]]);
    }

    $booths = [
        ['BSN Mid Valley', 'Mid Valley Megamall, Kuala Lumpur', 'WP Kuala Lumpur', 'BSN', 'Active'],
        ['Bank Rakyat Shah Alam', 'Section 14, Shah Alam', 'Selangor', 'Bank Rakyat', 'Active'],
        ['Pos Malaysia Gurney', 'Gurney Plaza, George Town', 'Penang', 'Pos Malaysia', 'Active'],
        ['EBB Aeon Tebrau', 'Aeon Tebrau City, Johor Bahru', 'Johor', 'EBB', 'Active']
    ];

    $stmt_booth = $pdo->prepare("INSERT INTO booths (name, location, state, channel, status) VALUES (?, ?, ?, ?, ?)");
    foreach ($booths as $booth) {
        $stmt_booth->execute($booth);
    }

    $donations = [
        ['Ali bin Abu', '012-3334444', 'ali@example.com', 120.00, '2026-05-10', 'May', 'BSN', 'WP Kuala Lumpur', 'BSN Mid Valley', 1, 1],
        ['Tan Kah Kee', '014-9998888', 'tan@example.com', 250.00, '2026-04-15', 'April', 'Bank Rakyat', 'Selangor', 'Bank Rakyat Shah Alam', 3, 2],
        ['Ramasamy Pillay', '017-7776666', 'rama@example.com', 80.00, '2026-03-22', 'March', 'Pos Malaysia', 'Penang', 'Pos Malaysia Gurney', 4, 3],
        ['Fatimah binti Md Ali', '019-1112222', 'fatimah_ma@example.com', 350.00, '2026-06-02', 'June', 'EBB', 'Johor', 'EBB Aeon Tebrau', 5, 4],
        ['Haji Ahmad', '011-22334455', 'haji.ahmad@example.com', 175.00, '2026-02-08', 'February', 'BSN', 'Selangor', 'Bank Rakyat Shah Alam', 3, 2],
        ['Nur Aisyah', '013-8887777', 'aisyah@example.com', 95.00, '2026-05-21', 'May', 'BSN', 'WP Kuala Lumpur', 'BSN Mid Valley', 1, 1],
        ['Lim Siew Ling', '017-2223333', 'lim@example.com', 210.00, '2026-04-25', 'April', 'Pos Malaysia', 'Penang', 'Pos Malaysia Gurney', 4, 3],
        ['Mohd Faisal', '018-9994444', 'faisal@example.com', 145.00, '2026-03-30', 'March', 'Bank Rakyat', 'Selangor', 'Bank Rakyat Shah Alam', 3, 2],
        ['Siti Nor', '016-8885555', 'siti.nor@example.com', 420.00, '2026-06-13', 'June', 'EBB', 'Johor', 'EBB Aeon Tebrau', 5, 4],
        ['Rajan Kumar', '012-7778888', 'rajan@example.com', 60.00, '2026-03-10', 'March', 'Pos Malaysia', 'Penang', 'Pos Malaysia Gurney', 4, 3],
        ['Farah Husna', '019-3334444', 'farah@example.com', 180.00, '2026-02-18', 'February', 'BSN', 'WP Kuala Lumpur', 'BSN Mid Valley', 1, 1],
        ['Wong Ah Seng', '014-5556666', 'wong@example.com', 220.00, '2026-05-27', 'May', 'Bank Rakyat', 'Selangor', 'Bank Rakyat Shah Alam', 3, 2],
        ['Ahmad Faizal', '017-1112223', 'faizal@example.com', 130.00, '2026-04-08', 'April', 'BSN', 'WP Kuala Lumpur', 'BSN Mid Valley', 1, 1],
        ['Hana Mei', '016-7772222', 'hana@example.com', 90.00, '2026-03-15', 'March', 'Pos Malaysia', 'Penang', 'Pos Malaysia Gurney', 4, 3],
        ['Suraya Zulkifli', '012-4445555', 'suraya@example.com', 305.00, '2026-06-20', 'June', 'EBB', 'Johor', 'EBB Aeon Tebrau', 5, 4],
        ['Leong Wei', '013-5558888', 'leong@example.com', 175.00, '2026-05-05', 'May', 'Pos Malaysia', 'Penang', 'Pos Malaysia Gurney', 4, 3],
        ['Faizal Rahman', '018-3332222', 'faizalr@example.com', 260.00, '2026-04-12', 'April', 'BSN', 'WP Kuala Lumpur', 'BSN Mid Valley', 1, 1],
        ['Chong Mei Ling', '017-6667777', 'chong@example.com', 330.00, '2026-06-01', 'June', 'EBB', 'Johor', 'EBB Aeon Tebrau', 5, 4],
        ['Zulkifli Hassan', '019-4446666', 'zulkifli@example.com', 145.00, '2026-03-28', 'March', 'Bank Rakyat', 'Selangor', 'Bank Rakyat Shah Alam', 3, 2],
        ['Nadia Binti Omar', '011-6667777', 'nadia@example.com', 95.00, '2026-02-22', 'February', 'BSN', 'WP Kuala Lumpur', 'BSN Mid Valley', 1, 1],
        ['Ismail Putra', '018-2223334', 'ismail@example.com', 210.00, '2026-05-14', 'May', 'Bank Rakyat', 'Selangor', 'Bank Rakyat Shah Alam', 3, 2],
        ['Marie Tan', '012-9993333', 'marie@example.com', 140.00, '2026-04-05', 'April', 'Pos Malaysia', 'Penang', 'Pos Malaysia Gurney', 4, 3],
        ['Aisyah Farid', '016-4447777', 'aisyah.f@example.com', 175.00, '2026-03-19', 'March', 'BSN', 'WP Kuala Lumpur', 'BSN Mid Valley', 1, 1],
        ['Kamarudin Osman', '017-5556661', 'kamarudin@example.com', 290.00, '2026-06-17', 'June', 'EBB', 'Johor', 'EBB Aeon Tebrau', 5, 4],
        ['Joseph Lim', '014-3339999', 'joseph@example.com', 95.00, '2026-05-02', 'May', 'Pos Malaysia', 'Penang', 'Pos Malaysia Gurney', 4, 3],
        ['Faridah Ahmad', '019-8880001', 'faridah@example.com', 205.00, '2026-04-20', 'April', 'Bank Rakyat', 'Selangor', 'Bank Rakyat Shah Alam', 3, 2],
        ['Lee Chen', '013-2225555', 'leechen@example.com', 330.00, '2026-06-07', 'June', 'EBB', 'Johor', 'EBB Aeon Tebrau', 5, 4],
        ['Nadirah Jamal', '012-7771112', 'nadirah@example.com', 115.00, '2026-05-11', 'May', 'BSN', 'WP Kuala Lumpur', 'BSN Mid Valley', 1, 1],
        ['Malar Krishnan', '016-6668888', 'malar@example.com', 240.00, '2026-04-28', 'April', 'Pos Malaysia', 'Penang', 'Pos Malaysia Gurney', 4, 3],
        ['Saleh Mat', '017-3339990', 'saleh@example.com', 185.00, '2026-03-05', 'March', 'Bank Rakyat', 'Selangor', 'Bank Rakyat Shah Alam', 3, 2],
        ['Robert Downey', '012-3344556', 'robert@example.com', 500.00, '2026-06-25', 'June', 'Bank Rakyat', 'WP Kuala Lumpur', 'Bank Rakyat Shah Alam', 3, 2],
        ['Amirul Bin Osman', '019-8877665', 'amirul@example.com', 150.00, '2026-05-18', 'May', 'BSN', 'Selangor', 'BSN Mid Valley', 1, 1],
        ['Siti Aminah', '017-6655443', 'aminah@example.com', 200.00, '2026-04-10', 'April', 'Pos Malaysia', 'Penang', 'Pos Malaysia Gurney', 4, 3],
        ['Cheah Kok Wah', '013-1122334', 'cheahkw@example.com', 850.00, '2026-03-12', 'March', 'EBB', 'Johor', 'EBB Aeon Tebrau', 5, 4],
        ['Nor Hazimah', '011-55443322', 'nor.hazimah@example.com', 75.00, '2026-02-14', 'February', 'BSN', 'WP Kuala Lumpur', 'BSN Mid Valley', 1, 1],
        ['Vijay Singh', '012-9988776', 'vijay@example.com', 120.00, '2026-06-19', 'June', 'Bank Rakyat', 'Selangor', 'Bank Rakyat Shah Alam', 3, 2],
        ['Zainal Abidin', '018-7766554', 'zainal.a@example.com', 310.00, '2026-05-24', 'May', 'Pos Malaysia', 'Penang', 'Pos Malaysia Gurney', 4, 3],
        ['Lucy Liu', '014-4433221', 'lucy@example.com', 400.00, '2026-04-30', 'April', 'EBB', 'Johor', 'EBB Aeon Tebrau', 5, 4],
        ['Abu Bakar', '019-3322110', 'abubakar@example.com', 180.00, '2026-03-29', 'March', 'BSN', 'WP Kuala Lumpur', 'BSN Mid Valley', 1, 1],
        ['Nurul Izzah', '013-5566778', 'izzah@example.com', 220.00, '2026-06-11', 'June', 'Bank Rakyat', 'Selangor', 'Bank Rakyat Shah Alam', 3, 2]
    ];

    $stmt_donation = $pdo->prepare("INSERT INTO donations (donor_name, donor_phone, donor_email, amount, donation_date, donation_month, channel, state, location, wakalah_id, booth_id, attachment_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)");
    foreach ($donations as $don) {
        $stmt_donation->execute($don);
    }
}

