-- schema.sql
-- Table: ms_flacofy_users
CREATE TABLE IF NOT EXISTS ms_flacofy_users
(
    id          INT auto_increment PRIMARY KEY,
    full_name   VARCHAR(255) NULL,
    phone       VARCHAR(20) NULL,
    email       VARCHAR(255) NULL,
    password    VARCHAR(255) NULL,
    is_verified TINYINT(1) DEFAULT 0,
    created_at  DATETIME NULL,
    updated_at  DATETIME NULL,
    deleted_at  DATETIME NULL
    );

-- Table: ms_flacofy_otps
CREATE TABLE IF NOT EXISTS ms_flacofy_otps
(
    id           INT auto_increment PRIMARY KEY,
    phone        VARCHAR(20) NULL,
    email        VARCHAR(255) NULL,
    otp_code     VARCHAR(10) NOT NULL,
    expires_at   DATETIME NOT NULL,
    is_verified  TINYINT(1) DEFAULT 0,
    last_sent_at DATETIME NULL,
    attempts     INT NULL,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP
    );

-- Table: ms_flacofy_rate_limits
CREATE TABLE IF NOT EXISTS ms_flacofy_rate_limits
(
    id              INT auto_increment PRIMARY KEY,
    identifier      VARCHAR(255) NOT NULL,-- phone, email, or IP
    action_type     VARCHAR(50) NULL,-- e.g., "otp_request"
    attempt_count   INT NULL,
    last_attempt_at DATETIME NULL,
    blocked_until   DATETIME NULL
    );

-- Table: ms_flacofy_orders
CREATE TABLE IF NOT EXISTS ms_flacofy_orders
(
    id           INT auto_increment PRIMARY KEY,
    user_id      INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    amount       DECIMAL(10, 2) NULL,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    status       VARCHAR(50) NULL,
    deleted_at   DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES ms_flacofy_users(id) ON DELETE CASCADE
    );
