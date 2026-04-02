CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','politician','user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE parties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE politicians (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    party_id INT,
    position VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (party_id) REFERENCES parties(id) ON DELETE SET NULL
);

CREATE TABLE declarations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    politician_id INT,
    year YEAR,
    status ENUM('draft','submitted') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (politician_id) REFERENCES politicians(id) ON DELETE CASCADE
);

CREATE TABLE assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    declaration_id INT,
    type VARCHAR(50), -- π.χ. deposit, car, house
    description TEXT,
    value DECIMAL(12,2),
    FOREIGN KEY (declaration_id) REFERENCES declarations(id) ON DELETE CASCADE
);