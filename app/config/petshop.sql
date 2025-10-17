
-- ======================================
-- 1. USERS
-- ======================================
CREATE TABLE users (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(255) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    first_name      VARCHAR(100),
    last_name       VARCHAR(100),
    phone           VARCHAR(20),
    role            ENUM('customer', 'admin', 'staff') DEFAULT 'customer',
    is_active       BOOLEAN DEFAULT TRUE,
    profile_avatar  VARCHAR(255),
    marketing_consent BOOLEAN DEFAULT FALSE,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login_at   DATETIME
);

-- ======================================
-- 2. ADDRESSES --Dia chi giao hang cua nguoi dung 
-- ======================================
CREATE TABLE addresses (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT,
    label           VARCHAR(100),
    full_name       VARCHAR(255),
    phone           VARCHAR(20),
    country         VARCHAR(100),
    province        VARCHAR(100),
    district        VARCHAR(100),
    ward            VARCHAR(100),
    street_address  VARCHAR(255),
    postal_code     VARCHAR(20),
    is_default      BOOLEAN DEFAULT FALSE,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- ======================================
-- 3. CATEGORIES
-- ======================================
CREATE TABLE categories (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    parent_id       BIGINT NULL,
    name            VARCHAR(255) NOT NULL,
    slug            VARCHAR(255) UNIQUE,
    description     TEXT,
    sort_order      INT DEFAULT 0,
    is_active       BOOLEAN DEFAULT TRUE,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id)
);

-- ======================================
-- 4. BRANDS
-- ======================================
CREATE TABLE brands (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(255) NOT NULL UNIQUE,
    slug            VARCHAR(255) UNIQUE,
    logo_url        VARCHAR(255),
    description     TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ======================================
-- 5. PRODUCTS
-- ======================================
CREATE TABLE products (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    sku             VARCHAR(100) UNIQUE,
    name            VARCHAR(255) NOT NULL,
    slug            VARCHAR(255) UNIQUE,
    brand_id        BIGINT,
    category_id     BIGINT,
    short_description TEXT,
    long_description  TEXT,
    base_price      DECIMAL(12,2) NOT NULL,
    price           DECIMAL(12,2) NOT NULL,
    currency        VARCHAR(10) DEFAULT 'VND',
    weight          DECIMAL(10,2),
    length          DECIMAL(10,2),
    width           DECIMAL(10,2),
    height          DECIMAL(10,2),
    stock_quantity  INT DEFAULT 0,
    stock_status    ENUM('in_stock', 'out_of_stock', 'preorder') DEFAULT 'in_stock',
    status          ENUM('active', 'disabled') DEFAULT 'active',
    featured        BOOLEAN DEFAULT FALSE,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES brands(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- ======================================
-- 6. PRODUCT IMAGES
-- ======================================
CREATE TABLE product_images (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id      BIGINT NOT NULL,
    url             VARCHAR(255) NOT NULL,
    alt_text        VARCHAR(255),
    is_primary      BOOLEAN DEFAULT FALSE,
    sort_order      INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- ======================================
-- 7. PRODUCT VARIANTS
-- ======================================
CREATE TABLE product_variants (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id      BIGINT NOT NULL,
    sku             VARCHAR(100),
    attributes      JSON,
    price           DECIMAL(12,2),
    stock_quantity  INT DEFAULT 0,
    weight          DECIMAL(10,2),
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- ======================================
-- 8. PROMOTIONS / DISCOUNTS
-- ======================================
CREATE TABLE promotions (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(50),
    title           VARCHAR(255),
    description     TEXT,
    discount_type   ENUM('percent','fixed'),
    discount_value  DECIMAL(12,2),
    applies_to      ENUM('product','category','order') DEFAULT 'order',
    start_at        DATETIME,
    end_at          DATETIME,
    usage_limit     INT,
    per_user_limit  INT,
    min_order_value DECIMAL(12,2),
    is_active       BOOLEAN DEFAULT TRUE
);

-- ======================================
-- 9. CARTS & CART ITEMS
-- ======================================
CREATE TABLE carts (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT NULL,
    session_id      VARCHAR(255),
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE cart_items (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    cart_id         BIGINT NOT NULL,
    product_id      BIGINT,
    variant_id      BIGINT NULL,
    quantity        INT DEFAULT 1,
    price_snapshot  DECIMAL(12,2),
    added_at        DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES carts(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(id)
);

-- ======================================
-- 10. ORDERS
-- ======================================
CREATE TABLE orders (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_number    VARCHAR(50) UNIQUE,
    user_id         BIGINT NULL,
    status          ENUM('pending','confirmed','packing','shipped','delivered','cancelled','returned') DEFAULT 'pending',
    payment_status  ENUM('unpaid','paid','refunded') DEFAULT 'unpaid',
    shipping_address_id BIGINT,
    billing_address_id  BIGINT,
    subtotal        DECIMAL(12,2),
    shipping_fee    DECIMAL(12,2),
    tax_amount      DECIMAL(12,2),
    discount_amount DECIMAL(12,2),
    total_amount    DECIMAL(12,2),
    payment_method  ENUM('cod','bank','credit_card','momo','vnpay') DEFAULT 'cod',
    placed_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    notes           TEXT,
    shipping_provider VARCHAR(100),
    tracking_number VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (shipping_address_id) REFERENCES addresses(id),
    FOREIGN KEY (billing_address_id) REFERENCES addresses(id)
);

-- ======================================
-- 11. ORDER ITEMS
-- ======================================
CREATE TABLE order_items (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_id        BIGINT NOT NULL,
    product_id      BIGINT,
    product_name_snapshot VARCHAR(255),
    sku_snapshot    VARCHAR(100),
    quantity        INT,
    unit_price      DECIMAL(12,2),
    total_price     DECIMAL(12,2),
    tax_amount      DECIMAL(12,2),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- ======================================
-- 12. PAYMENTS
-- ======================================
CREATE TABLE payments (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_id        BIGINT NOT NULL,
    payment_method  VARCHAR(50),
    amount          DECIMAL(12,2),
    status          ENUM('pending','success','failed','refunded') DEFAULT 'pending',
    transaction_id  VARCHAR(100),
    paid_at         DATETIME,
    raw_response    JSON,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- ======================================
-- 13. REVIEWS
-- ======================================
CREATE TABLE reviews (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id      BIGINT NOT NULL,
    user_id         BIGINT NULL,
    rating          INT CHECK (rating BETWEEN 1 AND 5),
    title           VARCHAR(255),
    content         TEXT,
    images          JSON,
    is_approved     BOOLEAN DEFAULT FALSE,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- ======================================
-- 14. BLOG POSTS
-- ======================================
CREATE TABLE blog_posts (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(255),
    slug            VARCHAR(255) UNIQUE,
    content         LONGTEXT,
    excerpt         TEXT,
    author_id       BIGINT,
    status          ENUM('draft','published') DEFAULT 'draft',
    featured_image  VARCHAR(255),
    published_at    DATETIME,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id)
);

-- ======================================
-- 15. WISHLIST
-- ======================================
CREATE TABLE wishlists (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT NOT NULL,
    name            VARCHAR(100) DEFAULT 'My Wishlist',
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE wishlist_items (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    wishlist_id     BIGINT NOT NULL,
    product_id      BIGINT NOT NULL,
    added_at        DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wishlist_id) REFERENCES wishlists(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- ======================================
-- 16. ADMIN / AUDIT LOGS
-- ======================================
CREATE TABLE admin_logs (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    actor_user_id   BIGINT,
    action          VARCHAR(100),
    resource_type   VARCHAR(100),
    resource_id     BIGINT,
    meta            JSON,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (actor_user_id) REFERENCES users(id)
);

-- ======================================
-- 17. SITE SETTINGS
-- ======================================
CREATE TABLE site_settings (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    setting_key     VARCHAR(100) UNIQUE,
    setting_value   JSON,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
