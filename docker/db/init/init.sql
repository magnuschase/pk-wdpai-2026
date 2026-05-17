-- =============================================================
--  Terra & Craft – full database schema
--  PostgreSQL
-- =============================================================

-- -------------------------------------------------------------
--  CORE TABLES
-- -------------------------------------------------------------

CREATE TABLE users (
    id          SERIAL PRIMARY KEY,
    username    VARCHAR(50)  UNIQUE NOT NULL,
    email       VARCHAR(255) UNIQUE NOT NULL,
    password    TEXT         NOT NULL,
    is_active   BOOLEAN      DEFAULT TRUE,
    is_admin    BOOLEAN      DEFAULT FALSE,
    created_at  TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 1:1 with users
CREATE TABLE user_profiles (
    user_id     INTEGER PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    avatar_url  VARCHAR(500),
    bio         TEXT,
    phone       VARCHAR(30)
);

CREATE TABLE categories (
    id    SERIAL PRIMARY KEY,
    name  VARCHAR(100) NOT NULL,
    slug  VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE gallery_items (
    id           SERIAL PRIMARY KEY,
    category_id  INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    name         VARCHAR(200) NOT NULL,
    material     VARCHAR(100),
    price        NUMERIC(10,2) NOT NULL,
    image_url    VARCHAR(500),
    description  TEXT,
    created_at   TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE object_types (
    id    SERIAL PRIMARY KEY,
    name  VARCHAR(50) NOT NULL,
    icon  VARCHAR(100)
);

CREATE TABLE glazes (
    id         SERIAL PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    color_hex  VARCHAR(7)
);

CREATE TABLE orders (
    id                SERIAL PRIMARY KEY,
    user_id           INTEGER REFERENCES users(id)        ON DELETE CASCADE,
    object_type_id    INTEGER REFERENCES object_types(id) ON DELETE SET NULL,
    glaze_id          INTEGER REFERENCES glazes(id)       ON DELETE SET NULL,
    size_type         VARCHAR(20)   CHECK (size_type IN ('small','medium','large','custom')),
    custom_dimensions VARCHAR(200),
    special_requests  TEXT,
    budget_min        NUMERIC(10,2),
    budget_max        NUMERIC(10,2),
    status            VARCHAR(30) DEFAULT 'pending'
                          CHECK (status IN ('pending','approved','in_progress','completed','rejected')),
    price_adjustment  NUMERIC(10,2),
    created_at        TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 1:many → orders (inspiration images uploaded in wizard step 5)
CREATE TABLE order_images (
    id          SERIAL PRIMARY KEY,
    order_id    INTEGER REFERENCES orders(id) ON DELETE CASCADE,
    image_url   VARCHAR(500) NOT NULL,
    uploaded_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 1:many → orders + users (admin internal notes and messages to customer)
CREATE TABLE order_notes (
    id         SERIAL PRIMARY KEY,
    order_id   INTEGER REFERENCES orders(id)  ON DELETE CASCADE,
    author_id  INTEGER REFERENCES users(id)   ON DELETE SET NULL,
    note_type  VARCHAR(20) CHECK (note_type IN ('internal','customer')),
    content    TEXT NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- many:many users ↔ gallery_items (favourites / wishlist)
CREATE TABLE user_favorites (
    user_id    INTEGER REFERENCES users(id)         ON DELETE CASCADE,
    item_id    INTEGER REFERENCES gallery_items(id) ON DELETE CASCADE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, item_id)
);

-- -------------------------------------------------------------
--  VIEWS (minimum 2 required, each using JOINs across multiple tables)
-- -------------------------------------------------------------

-- View 1: order details joined with customer data, object type and glaze
CREATE VIEW v_order_details AS
SELECT
    o.id,
    o.status,
    o.size_type,
    o.custom_dimensions,
    o.special_requests,
    o.budget_min,
    o.budget_max,
    o.price_adjustment,
    o.created_at,
    o.updated_at,
    u.username         AS customer_name,
    u.email            AS customer_email,
    ot.name            AS object_type,
    ot.icon            AS object_icon,
    g.name             AS glaze_name,
    g.color_hex        AS glaze_color
FROM orders o
JOIN  users        u  ON o.user_id        = u.id
LEFT JOIN object_types ot ON o.object_type_id = ot.id
LEFT JOIN glazes        g  ON o.glaze_id       = g.id;

-- View 2: user statistics with order counts and last activity date
CREATE VIEW v_user_stats AS
SELECT
    u.id,
    u.username,
    u.email,
    u.is_active,
    u.is_admin,
    u.created_at,
    up.avatar_url,
    COUNT(o.id)              AS total_orders,
    COALESCE(SUM(o.price_adjustment), 0) AS total_spent,
    MAX(o.updated_at)        AS last_order_date
FROM users u
LEFT JOIN user_profiles up ON u.id = up.user_id
LEFT JOIN orders        o  ON u.id = o.user_id
GROUP BY u.id, u.username, u.email, u.is_active, u.is_admin, u.created_at, up.avatar_url;

-- -------------------------------------------------------------
--  FUNCTION (minimum 1 required)
-- -------------------------------------------------------------

-- Returns the total number of orders for a given user
CREATE OR REPLACE FUNCTION get_user_order_count(p_user_id INTEGER)
RETURNS INTEGER AS $$
DECLARE
    v_count INTEGER;
BEGIN
    SELECT COUNT(*)
    INTO   v_count
    FROM   orders
    WHERE  user_id = p_user_id;
    RETURN v_count;
END;
$$ LANGUAGE plpgsql;

-- -------------------------------------------------------------
--  TRIGGER (minimum 1 required)
-- -------------------------------------------------------------

-- Automatically refreshes updated_at on every UPDATE to the orders table
CREATE OR REPLACE FUNCTION fn_set_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_orders_updated_at
    BEFORE UPDATE ON orders
    FOR EACH ROW
    EXECUTE FUNCTION fn_set_updated_at();

-- -------------------------------------------------------------
--  SAMPLE DATA
-- -------------------------------------------------------------

-- Users (all passwords are bcrypt hash of "password123")
INSERT INTO users (username, email, password, is_active, is_admin) VALUES
('admin',       'admin@terracraft.com',  '$2y$10$mP5XvHZ83r5KaiND98hs4usqnqWbd1Ar3e8.okYQ9Efno0W12Ab2C', TRUE,  TRUE),
('alice_moore', 'alice@example.com',     '$2y$10$mP5XvHZ83r5KaiND98hs4usqnqWbd1Ar3e8.okYQ9Efno0W12Ab2C', TRUE,  FALSE),
('ben_carter',  'ben@example.com',       '$2y$10$mP5XvHZ83r5KaiND98hs4usqnqWbd1Ar3e8.okYQ9Efno0W12Ab2C', TRUE,  FALSE),
('clara_di',    'clara@example.com',     '$2y$10$mP5XvHZ83r5KaiND98hs4usqnqWbd1Ar3e8.okYQ9Efno0W12Ab2C', FALSE, FALSE),
('david_kim',   'david@example.com',     '$2y$10$mP5XvHZ83r5KaiND98hs4usqnqWbd1Ar3e8.okYQ9Efno0W12Ab2C', TRUE,  FALSE),
('eva_green',   'eva@example.com',       '$2y$10$mP5XvHZ83r5KaiND98hs4usqnqWbd1Ar3e8.okYQ9Efno0W12Ab2C', TRUE,  FALSE);

-- User profiles (1:1 relation)
INSERT INTO user_profiles (user_id, avatar_url, bio, phone) VALUES
(1, NULL,  'Studio administrator',                     '+48 100 000 000'),
(2, NULL,  'Pottery enthusiast from Warsaw',           '+48 111 222 333'),
(3, NULL,  'Minimalist design lover',                  '+48 444 555 666'),
(4, NULL,  NULL,                                        NULL),
(5, NULL,  'Collector of handcrafted ceramics',        '+48 777 888 999'),
(6, NULL,  'Interior designer, loves terracotta tones','+48 999 111 222');

-- Gallery categories
INSERT INTO categories (name, slug) VALUES
('Vases',     'vases'),
('Bowls',     'bowls'),
('Tableware', 'tableware'),
('Sculptures','sculptures'),
('Planters',  'planters');

-- Gallery items
INSERT INTO gallery_items (category_id, name, material, price, image_url, description) VALUES
(1, 'Earthen Vase No.4',    'Stoneware',  120.00, '/public/images/gallery/vase1.jpg',      'A rustic stoneware vase with organic curves.'),
(2, 'Morning Coffee Set',   'Porcelain',  140.00, '/public/images/gallery/coffee_set.jpg', 'Set of 4 minimalist porcelain mugs.'),
(3, 'Speckled Dinner Plate','Stoneware',   45.00, '/public/images/gallery/plate1.jpg',     'Handmade dinner plate with speckled glaze.'),
(5, 'Aged Planter',         'Terracotta',  95.00, '/public/images/gallery/planter1.jpg',   'Terracotta planter with a weathered finish.'),
(2, 'Midnight Bowl',        'Raku',        85.00, '/public/images/gallery/bowl1.jpg',      'Dark raku bowl, each piece uniquely fired.'),
(4, 'Stone Sculpture',      'Stoneware',  210.00, '/public/images/gallery/sculpture1.jpg', 'Abstract stoneware sculpture.'),
(1, 'Teal Bud Vase',        'Porcelain',   65.00, '/public/images/gallery/vase2.jpg',      'Small bud vase in deep teal glaze.'),
(3, 'Farmhouse Mug',        'Stoneware',   35.00, '/public/images/gallery/mug1.jpg',       'Classic farmhouse-style stoneware mug.');

-- Custom order object types
INSERT INTO object_types (name, icon) VALUES
('Mug',     'mug'),
('Plate',   'plate'),
('Bowl',    'bowl'),
('Vase',    'vase'),
('Planter', 'planter'),
('Other',   'other');

-- Glazes / finishes
INSERT INTO glazes (name, color_hex) VALUES
('Matte White',   '#F5F0EB'),
('Speckled Sand', '#C8B89A'),
('Deep Teal',     '#2A7C7C'),
('Terracotta',    '#C4622D');

-- Sample orders
INSERT INTO orders (user_id, object_type_id, glaze_id, size_type, custom_dimensions, special_requests, budget_min, budget_max, status, price_adjustment) VALUES
(2, 4, 3, 'custom', 'Height ~12 inches, Width ~6 inches',
 'Looking for something organic but elegant. Grandmother''s initials E.M. engraved near the base. Deep teal glaze with some variation, not too solid.',
 150.00, 200.00, 'pending', NULL),
(3, 1, 1, 'medium', NULL,
 'Plain matte white, simple and clean.',
 40.00, 60.00, 'approved', 5.00),
(5, 3, 4, 'large', NULL,
 'Terracotta bowl for fruit display. Rough exterior preferred.',
 70.00, 100.00, 'completed', 0.00),
(6, 2, 2, 'small', NULL,
 'Speckled sand plate, diameter about 20cm.',
 30.00, 50.00, 'in_progress', NULL);

-- Order inspiration images are uploaded by users via the wizard form

-- Order notes and messages
INSERT INTO order_notes (order_id, author_id, note_type, content) VALUES
(1, 1, 'internal', 'Another user requested a Deep Teal Vase last month. Check pricing.'),
(1, 1, 'customer', 'Thank you for your order! We will review your request and get back to you shortly.'),
(2, 1, 'customer', 'Your order has been approved. We will start production next week.'),
(3, 1, 'internal', 'Completed and shipped. Customer confirmed receipt.');

-- Favourites (many:many)
INSERT INTO user_favorites (user_id, item_id) VALUES
(2, 1), (2, 3), (2, 7),
(3, 2), (3, 5),
(5, 4), (5, 6), (5, 8),
(6, 1), (6, 3);
