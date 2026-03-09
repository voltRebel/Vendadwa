<?php
/**
 * Database Setup Utility — Multi-Tenancy Edition
 * Run this file to initialize tables or migrate existing ones to Multi-Tenancy.
 */

require_once 'includes/db.php';

/**
 * Helper to check if a column exists in a table
 */
function columnExists($pdo, $table, $column) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $stmt->fetch() !== false;
    } catch (Exception $e) {
        return false;
    }
}

try {
    // 1. Create Companies Table
    $sql = "CREATE TABLE IF NOT EXISTS companies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        phone VARCHAR(20),
        address TEXT,
        website VARCHAR(255),
        logo VARCHAR(255),
        
        -- Tax Settings
        tax_name VARCHAR(50) DEFAULT 'Sales Tax',
        tax_rate DECIMAL(5,2) DEFAULT 10.00,
        tax_enabled TINYINT(1) DEFAULT 1,
        tax_included TINYINT(1) DEFAULT 0,
        
        -- Payment Methods (JSON or individual flags)
        payment_cash TINYINT(1) DEFAULT 1,
        payment_card TINYINT(1) DEFAULT 1,
        payment_mobile TINYINT(1) DEFAULT 1,
        payment_bank TINYINT(1) DEFAULT 0,
        
        -- Receipt Settings
        receipt_header TEXT,
        receipt_footer TEXT,
        receipt_autoprint TINYINT(1) DEFAULT 1,
        receipt_email TINYINT(1) DEFAULT 0,
        
        -- Currency Settings
        currency_code VARCHAR(10) DEFAULT 'USD',
        currency_symbol VARCHAR(5) DEFAULT '$',
        currency_decimals INT DEFAULT 2,
        
        -- Barcode Settings
        barcode_format VARCHAR(20) DEFAULT 'Code 128',
        barcode_prefix VARCHAR(10) DEFAULT 'VEN-',
        barcode_autogen TINYINT(1) DEFAULT 1,
        
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql);
    echo "Table 'companies' updated/created.<br>";

    // Ensure all columns exist for existing companies
    $newCols = [
        'website' => "VARCHAR(255) AFTER address",
        'tax_name' => "VARCHAR(50) DEFAULT 'Sales Tax' AFTER logo",
        'tax_rate' => "DECIMAL(5,2) DEFAULT 10.00 AFTER tax_name",
        'tax_enabled' => "TINYINT(1) DEFAULT 1 AFTER tax_rate",
        'tax_included' => "TINYINT(1) DEFAULT 0 AFTER tax_enabled",
        'payment_cash' => "TINYINT(1) DEFAULT 1 AFTER tax_included",
        'payment_card' => "TINYINT(1) DEFAULT 1 AFTER payment_cash",
        'payment_mobile' => "TINYINT(1) DEFAULT 1 AFTER payment_card",
        'payment_bank' => "TINYINT(1) DEFAULT 0 AFTER payment_mobile",
        'receipt_header' => "TEXT AFTER payment_bank",
        'receipt_footer' => "TEXT AFTER receipt_header",
        'receipt_autoprint' => "TINYINT(1) DEFAULT 1 AFTER receipt_footer",
        'receipt_email' => "TINYINT(1) DEFAULT 0 AFTER receipt_autoprint",
        'currency_code' => "VARCHAR(10) DEFAULT 'GHS' AFTER receipt_email",
        'currency_symbol' => "VARCHAR(5) DEFAULT 'GH₵' AFTER currency_code",
        'currency_decimals' => "INT DEFAULT 2 AFTER currency_symbol",
        'barcode_format' => "VARCHAR(20) DEFAULT 'Code 128' AFTER currency_decimals",
        'barcode_prefix' => "VARCHAR(10) DEFAULT 'VEN-' AFTER barcode_format",
        'barcode_autogen' => "TINYINT(1) DEFAULT 1 AFTER barcode_prefix"
    ];

    foreach ($newCols as $col => $definition) {
        if (!columnExists($pdo, 'companies', $col)) {
            $pdo->exec("ALTER TABLE companies ADD COLUMN $col $definition");
            echo "Added '$col' to 'companies' table.<br>";
        }
    }

    // Set default values for existing companies if they are empty
    $pdo->exec("UPDATE companies SET 
        currency_code = IFNULL(currency_code, 'GHS'), 
        currency_symbol = IFNULL(currency_symbol, 'GH₵'),
        currency_decimals = IFNULL(currency_decimals, 2),
        payment_cash = IFNULL(payment_cash, 1),
        payment_card = IFNULL(payment_card, 1),
        payment_mobile = IFNULL(payment_mobile, 1),
        payment_bank = IFNULL(payment_bank, 0),
        tax_enabled = IFNULL(tax_enabled, 1),
        tax_rate = IFNULL(tax_rate, 0.00),
        tax_name = IFNULL(tax_name, 'VAT'),
        receipt_header = NULL,
        receipt_footer = NULL
        WHERE 1");

    // 1b. Create Branches Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS branches (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        address TEXT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_branch_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'branches' created.<br>";

    // 2. Setup Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'cashier',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Migrate Users Table
    if (columnExists($pdo, 'users', 'email') && !columnExists($pdo, 'users', 'username')) {
        $pdo->exec("ALTER TABLE users CHANGE COLUMN email username VARCHAR(100) NOT NULL UNIQUE");
        echo "Renamed 'email' to 'username' in 'users' table.<br>";
    }
    
    // Ensure email is present
    if (!columnExists($pdo, 'users', 'email')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(100) NULL AFTER username");
        echo "Added 'email' to 'users' table.<br>";
    }
    
    // Ensure status is present
    if (!columnExists($pdo, 'users', 'status')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN status VARCHAR(20) DEFAULT 'active' AFTER role");
        echo "Added 'status' to 'users' table.<br>";
    }
    
    // Ensure last_login is present
    if (!columnExists($pdo, 'users', 'last_login')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN last_login DATETIME NULL AFTER status");
        echo "Added 'last_login' to 'users' table.<br>";
    }

    if (!columnExists($pdo, 'users', 'company_id')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN company_id INT NULL AFTER id");
        $pdo->exec("ALTER TABLE users ADD CONSTRAINT fk_user_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL");
        echo "Added 'company_id' to 'users' table.<br>";
    }
    
    // Ensure 'role' is VARCHAR and not ENUM
    $pdo->exec("ALTER TABLE users MODIFY COLUMN role VARCHAR(50) DEFAULT 'cashier'");
    echo "Updated 'users' table role column to VARCHAR.<br>";

    // 2b. Setup Roles Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        name VARCHAR(50) NOT NULL,
        permissions TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_role_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'roles' created or already exists.<br>";

    // Initialize Default Roles for existing companies
    $companies = $pdo->query("SELECT id FROM companies")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($companies as $cId) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE company_id = ?");
        $check->execute([$cId]);
        if ($check->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO roles (company_id, name, permissions) VALUES (?, ?, ?)");
            $stmt->execute([$cId, 'admin', json_encode(['dashboard'=>true,'pos'=>true,'products'=>true,'customers'=>true,'suppliers'=>true,'returns'=>true,'expenses'=>true,'reports'=>true,'users'=>true,'settings'=>true,'tools'=>true])]);
            $stmt->execute([$cId, 'manager', json_encode(['dashboard'=>true,'pos'=>true,'products'=>true,'customers'=>true,'suppliers'=>true,'returns'=>true,'expenses'=>true,'reports'=>true,'users'=>false,'settings'=>false,'tools'=>false])]);
            $stmt->execute([$cId, 'cashier', json_encode(['dashboard'=>false,'pos'=>true,'products'=>false,'customers'=>true,'suppliers'=>false,'returns'=>false,'expenses'=>false,'reports'=>false,'users'=>false,'settings'=>false,'tools'=>false])]);
        }
    }
    
    // 2c. Setup Activity Logs Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        user_id INT NOT NULL,
        action_type VARCHAR(50) NOT NULL,
        details TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_company (company_id),
        INDEX idx_user (user_id),
        CONSTRAINT fk_log_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Table 'activity_logs' created or already exists.<br>";

    // 3. Setup Categories Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_category_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    if (!columnExists($pdo, 'categories', 'description')) {
        $pdo->exec("ALTER TABLE categories ADD COLUMN description TEXT AFTER name");
        echo "Added 'description' to 'categories' table.<br>";
    }
    echo "Table 'categories' created or already exists.<br>";

    // Setup Stock Movements Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS stock_movements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        product_id INT NOT NULL,
        type ENUM('in','out','adjustment') NOT NULL,
        qty INT NOT NULL,
        before_qty INT DEFAULT 0,
        after_qty INT DEFAULT 0,
        supplier VARCHAR(100),
        ref_number VARCHAR(50),
        reason VARCHAR(100),
        notes TEXT,
        date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'stock_movements' created or already exists.<br>";

    // 4. Setup Units Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS units (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        name VARCHAR(50) NOT NULL,
        short_name VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_unit_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'units' created or already exists.<br>";

    // 5. Setup Products Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_product_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    if (!columnExists($pdo, 'products', 'image')) {
        if (!columnExists($pdo, 'products', 'sku')) {
            $pdo->exec("ALTER TABLE products ADD COLUMN sku VARCHAR(50) AFTER id");
        }
        if (!columnExists($pdo, 'products', 'category_id')) {
            $pdo->exec("ALTER TABLE products ADD COLUMN category_id INT NULL AFTER sku");
            $pdo->exec("ALTER TABLE products ADD CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL");
        }
        if (!columnExists($pdo, 'products', 'unit_id')) {
            $pdo->exec("ALTER TABLE products ADD COLUMN unit_id INT NULL AFTER category_id");
            $pdo->exec("ALTER TABLE products ADD CONSTRAINT fk_product_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL");
        }
        
        $pdo->exec("ALTER TABLE products ADD COLUMN image VARCHAR(255) AFTER unit_id");
        
        if (!columnExists($pdo, 'products', 'cost_price')) {
            $pdo->exec("ALTER TABLE products ADD COLUMN cost_price DECIMAL(10,2) DEFAULT 0.00 AFTER image");
            $pdo->exec("ALTER TABLE products ADD COLUMN selling_price DECIMAL(10,2) DEFAULT 0.00 AFTER cost_price");
            $pdo->exec("ALTER TABLE products ADD COLUMN stock_quantity INT DEFAULT 0 AFTER selling_price");
            $pdo->exec("ALTER TABLE products ADD COLUMN min_stock_level INT DEFAULT 5 AFTER stock_quantity");
            $pdo->exec("ALTER TABLE products ADD COLUMN description TEXT AFTER min_stock_level");
        }
        echo "Updated 'products' table for enhanced features and multi-tenancy.<br>";
    }

    // 4. Setup Customers Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    if (!columnExists($pdo, 'customers', 'company_id')) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN company_id INT NOT NULL AFTER id");
        $pdo->exec("ALTER TABLE customers ADD COLUMN email VARCHAR(100) AFTER name");
        $pdo->exec("ALTER TABLE customers ADD COLUMN phone VARCHAR(20) AFTER email");
        $pdo->exec("ALTER TABLE customers ADD COLUMN address TEXT AFTER phone");
        $pdo->exec("ALTER TABLE customers ADD CONSTRAINT fk_customer_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE");
        echo "Migrated 'customers' table for multi-tenancy.<br>";
    }
    if (!columnExists($pdo, 'customers', 'loyalty_points')) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN loyalty_points INT DEFAULT 0 AFTER address");
        echo "Added 'loyalty_points' to customers.<br>";
    }
    if (!columnExists($pdo, 'customers', 'total_purchases')) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN total_purchases DECIMAL(12,2) DEFAULT 0.00 AFTER loyalty_points");
        echo "Added 'total_purchases' to customers.<br>";
    }
    if (!columnExists($pdo, 'customers', 'notes')) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN notes TEXT AFTER total_purchases");
        echo "Added 'notes' to customers.<br>";
    }
    echo "Table 'customers' OK.<br>";

    // 4b. Customer Purchases Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS customer_purchases (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        customer_id INT NOT NULL,
        receipt_no VARCHAR(50),
        items INT DEFAULT 1,
        total DECIMAL(12,2) DEFAULT 0.00,
        payment_method VARCHAR(30) DEFAULT 'Cash',
        points_earned INT DEFAULT 0,
        notes TEXT,
        purchase_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_purchase_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
        CONSTRAINT fk_purchase_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'customer_purchases' OK.<br>";

    // ── Suppliers ──────────────────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS suppliers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        name VARCHAR(150) NOT NULL,
        category VARCHAR(100),
        phone VARCHAR(30),
        email VARCHAR(120),
        address TEXT,
        notes TEXT,
        status ENUM('active','inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_supplier_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'suppliers' OK.<br>";

    // ── Purchase Orders ────────────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS purchase_orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        supplier_id INT NOT NULL,
        po_number VARCHAR(30) NOT NULL,
        order_date DATE NOT NULL,
        expected_date DATE,
        items INT DEFAULT 0,
        total DECIMAL(12,2) DEFAULT 0.00,
        status ENUM('pending','received','cancelled') DEFAULT 'pending',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_po_company  FOREIGN KEY (company_id)  REFERENCES companies(id)  ON DELETE CASCADE,
        CONSTRAINT fk_po_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id)  ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'purchase_orders' OK.<br>";

    // ── Supplier Payments ──────────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS supplier_payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        supplier_id INT NOT NULL,
        purchase_order_id INT,
        amount DECIMAL(12,2) DEFAULT 0.00,
        payment_date DATE NOT NULL,
        method ENUM('Cash','Bank Transfer','Check','Mobile Money','Other') DEFAULT 'Cash',
        reference VARCHAR(80),
        notes TEXT,
        status ENUM('paid','pending') DEFAULT 'paid',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_spay_company   FOREIGN KEY (company_id)        REFERENCES companies(id)       ON DELETE CASCADE,
        CONSTRAINT fk_spay_supplier  FOREIGN KEY (supplier_id)       REFERENCES suppliers(id)       ON DELETE CASCADE,
        CONSTRAINT fk_spay_po        FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'supplier_payments' OK.<br>";

    // ── Sales (POS) ─────────────────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS sales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        user_id INT NULL,
        customer_id INT NULL,
        receipt_no VARCHAR(50) NOT NULL UNIQUE,
        subtotal DECIMAL(12,2) DEFAULT 0.00,
        tax_amount DECIMAL(12,2) DEFAULT 0.00,
        discount_amount DECIMAL(12,2) DEFAULT 0.00,
        total_amount DECIMAL(12,2) DEFAULT 0.00,
        amount_received DECIMAL(12,2) DEFAULT 0.00,
        change_amount DECIMAL(12,2) DEFAULT 0.00,
        payment_method ENUM('Cash', 'Card', 'Mobile Money', 'Other') DEFAULT 'Cash',
        status ENUM('completed', 'held', 'cancelled') DEFAULT 'completed',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_sale_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
        CONSTRAINT fk_sale_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        CONSTRAINT fk_sale_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'sales' OK.<br>";

    // ── Sale Items ──────────────────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS sale_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        sale_id INT NOT NULL,
        product_id INT NOT NULL,
        unit_price DECIMAL(12,2) DEFAULT 0.00,
        qty INT DEFAULT 1,
        total DECIMAL(12,2) DEFAULT 0.00,
        CONSTRAINT fk_si_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
        CONSTRAINT fk_si_sale FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
        CONSTRAINT fk_si_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'sale_items' OK.<br>";

    // ── Sales Returns ──────────────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS sales_returns (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        sale_id INT NULL,
        customer_id INT NULL,
        receipt_no VARCHAR(50),
        return_number VARCHAR(50) NOT NULL UNIQUE,
        total_amount DECIMAL(12,2) DEFAULT 0.00,
        refund_amount DECIMAL(12,2) DEFAULT 0.00,
        refund_method ENUM('Cash', 'Card', 'Mobile Money', 'Store Credit', 'Other') DEFAULT 'Cash',
        reason TEXT,
        status ENUM('pending', 'refunded', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_ret_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
        CONSTRAINT fk_ret_sale FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE SET NULL,
        CONSTRAINT fk_ret_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS sales_return_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        return_id INT NOT NULL,
        product_id INT NOT NULL,
        qty INT DEFAULT 1,
        unit_price DECIMAL(12,2) DEFAULT 0.00,
        condition_status ENUM('restock', 'damaged', 'used') DEFAULT 'restock',
        CONSTRAINT fk_ri_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
        CONSTRAINT fk_ri_return FOREIGN KEY (return_id) REFERENCES sales_returns(id) ON DELETE CASCADE,
        CONSTRAINT fk_ri_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'sales_returns' & 'sales_return_items' OK.<br>";

    // ── Void Logs ──────────────────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS void_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        sale_id INT NOT NULL,
        receipt_no VARCHAR(50),
        reason TEXT,
        admin_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_void_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
        CONSTRAINT fk_void_sale FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'void_logs' OK.<br>";

    // ── Expense Categories ──────────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS expense_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_expcat_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'expense_categories' OK.<br>";

    // ── Expenses ────────────────────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS expenses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        category_id INT NULL,
        description VARCHAR(255) NOT NULL,
        amount DECIMAL(12,2) DEFAULT 0.00,
        expense_date DATE NOT NULL,
        payment_method ENUM('Cash', 'Bank Transfer', 'Card', 'Mobile Money', 'Other') DEFAULT 'Cash',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_exp_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
        CONSTRAINT fk_exp_category FOREIGN KEY (category_id) REFERENCES expense_categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'expenses' OK.<br>";

    // ── Backups ─────────────────────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS backups (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        filename VARCHAR(255) NOT NULL,
        filesize VARCHAR(50) NOT NULL,
        type ENUM('Auto', 'Manual') DEFAULT 'Manual',
        status ENUM('Complete', 'Failed', 'In Progress') DEFAULT 'Complete',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_backup_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'backups' OK.<br>";

    // 5. Initialize Super Admin
    $superUsername = 'Roach';
    $superPassword = 'flying@DUTCHMAN97';
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'super_admin' LIMIT 1");
    $stmt->execute();
    $existingSuper = $stmt->fetch();
    
    if (!$existingSuper) {
        $name = 'Vendora Super Admin';
        $hashedPassword = password_hash($superPassword, PASSWORD_DEFAULT);
        
        $insert = $pdo->prepare("INSERT INTO users (name, username, password, role, company_id) VALUES (?, ?, ?, 'super_admin', NULL)");
        $insert->execute([$name, $superUsername, $hashedPassword]);
        echo "Default Super Admin created: <b>$superUsername</b><br>";
    } else {
        // Update credentials if already exists to match user request
        $hashedPassword = password_hash($superPassword, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
        $update->execute([$superUsername, $hashedPassword, $existingSuper['id']]);
        echo "Super Admin credentials updated to: <b>$superUsername</b><br>";
    }

    // 6. Initialize a Test Company
    $pdo->exec("UPDATE companies SET name = 'Vendora', email = 'info@vendora.com' WHERE name = 'SlayBees Apparel' OR name = 'SlayBees'");
    
    $stmt = $pdo->query("SELECT id FROM companies WHERE name = 'Vendora' LIMIT 1");
    $testCompany = $stmt->fetch();
    if (!$testCompany) {
        $pdo->exec("INSERT INTO companies (name, email) VALUES ('Vendora', 'info@vendora.com')");
        $companyId = $pdo->lastInsertId();
        echo "Test Company 'Vendora' created.<br>";

        // Create an Admin for this company
        $adminUsername = 'admin';
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$adminUsername]);
        if (!$stmt->fetch()) {
            $password = password_hash('password123', PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (name, username, password, role, company_id) VALUES (?, ?, ?, ?, ?)");
            $insert->execute(['Vendora Admin', $adminUsername, $password, 'admin', $companyId]);
            echo "Admin for Vendora created: <b>$adminUsername</b><br>";
        }
    } else {
        $companyId = $testCompany['id']; // Get company ID if it already exists
        // Create an Admin for this company if it doesn't exist
        $adminUsername = 'admin';
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$adminUsername]);
        if (!$stmt->fetch()) {
            $password = password_hash('password123', PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (name, username, password, role, company_id) VALUES (?, ?, ?, ?, ?)");
            $insert->execute(['Vendora Admin', $adminUsername, $password, 'admin', $companyId]);
            echo "Admin for Vendora created: <b>$adminUsername</b><br>";
        }
    }

    echo "<br><b>Vendora Multi-Tenancy Setup/Migration Complete!</b><br>";
    echo "<a href='index.php?page=login'>Go to Login Page</a>";

} catch (PDOException $e) {
    die("Error setting up multi-tenancy database: " . $e->getMessage());
}
?>
