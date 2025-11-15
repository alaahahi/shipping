BEGIN TRANSACTION;

CREATE TABLE IF NOT EXISTS car (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT,
    user_id INTEGER,
    results INTEGER DEFAULT 0,
    no INTEGER NOT NULL,
    deleted_at TEXT,
    client_id INTEGER,
    note TEXT,
    car_type TEXT,
    vin TEXT UNIQUE,
    car_number INTEGER,
    dinar REAL DEFAULT 0,
    dolar_price REAL DEFAULT 0,
    dolar_custom REAL DEFAULT 0,
    checkout REAL DEFAULT 0,
    shipping_dolar REAL DEFAULT 0,
    coc_dolar REAL DEFAULT 0,
    note1 TEXT,
    total REAL DEFAULT 0,
    paid REAL DEFAULT 0,
    profit REAL DEFAULT 0,
    date TEXT,
    car_color TEXT,
    year INTEGER,
    expenses REAL,
    dinar_s REAL DEFAULT 0,
    dolar_price_s REAL DEFAULT 0,
    dolar_custom_s REAL DEFAULT 0,
    checkout_s REAL DEFAULT 0,
    shipping_dolar_s REAL DEFAULT 0,
    coc_dolar_s REAL DEFAULT 0,
    total_s REAL DEFAULT 0,
    discount INTEGER DEFAULT 0,
    expenses_s REAL DEFAULT 0,
    is_exit INTEGER DEFAULT 0,
    contract_id INTEGER DEFAULT 0,
    owner_id INTEGER,
    year_date INTEGER,
    car_have_expenses INTEGER DEFAULT 0,
    land_shipping INTEGER DEFAULT 0,
    land_shipping_dinar INTEGER DEFAULT 0,
    land_shipping_dinar_s INTEGER DEFAULT 0,
    land_shipping_s INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS car_contract (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name_seller TEXT NOT NULL,
    phone_seller TEXT,
    address_seller TEXT,
    seller_id_number TEXT,
    name_buyer TEXT NOT NULL,
    phone_buyer TEXT,
    address_buyer TEXT,
    buyer_id_number TEXT,
    tex_seller INTEGER,
    tex_seller_dinar INTEGER,
    tex_buyer INTEGER,
    tex_buyer_dinar INTEGER,
    vin TEXT NOT NULL,
    car_name TEXT NOT NULL,
    modal TEXT,
    color TEXT,
    size INTEGER,
    no TEXT,
    note TEXT,
    system_note TEXT,
    car_price INTEGER,
    car_paid INTEGER,
    tex_seller_paid INTEGER,
    tex_seller_dinar_paid INTEGER,
    tex_buyer_paid INTEGER,
    tex_buyer_dinar_paid INTEGER,
    no_s TEXT,
    car_name_s TEXT,
    modal_s TEXT,
    size_s INTEGER,
    color_s TEXT,
    vin_s TEXT,
    created TEXT,
    status INTEGER DEFAULT 0,
    verification_token TEXT UNIQUE,
    user_id INTEGER,
    owner_id INTEGER,
    year_date INTEGER,
    created_at TEXT,
    updated_at TEXT,
    deleted_at TEXT
);

CREATE TABLE IF NOT EXISTS car_expenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    car_id INTEGER,
    note TEXT,
    created_at TEXT,
    updated_at TEXT,
    amount_dinar INTEGER,
    amount_dollar INTEGER,
    user_id INTEGER,
    reason_id INTEGER,
    created TEXT,
    owner_id INTEGER,
    deleted_at TEXT
);

CREATE TABLE IF NOT EXISTS car_images (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    car_id INTEGER NOT NULL,
    name TEXT,
    created_at TEXT,
    updated_at TEXT,
    year INTEGER DEFAULT 2025
);

CREATE TABLE IF NOT EXISTS car_model (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT,
    status INTEGER DEFAULT 1,
    deleted_at TEXT
);

CREATE TABLE IF NOT EXISTS contract (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    car_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    created_at TEXT,
    updated_at TEXT,
    price REAL DEFAULT 0,
    paid REAL DEFAULT 0,
    note TEXT,
    created TEXT NOT NULL,
    price_dinar REAL DEFAULT 0,
    paid_dinar REAL DEFAULT 0,
    owner_id INTEGER,
    UNIQUE(car_id)
);

CREATE TABLE IF NOT EXISTS contract_img (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    car_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    created_at TEXT,
    updated_at TEXT,
    year INTEGER
);

CREATE TABLE IF NOT EXISTS driving (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id INTEGER,
    name TEXT,
    created_at TEXT,
    updated_at TEXT,
    car_number TEXT,
    year TEXT,
    owner_id INTEGER NOT NULL,
    year_date INTEGER NOT NULL,
    car_type TEXT,
    user_id INTEGER,
    created TEXT,
    deleted_at TEXT,
    note TEXT,
    color TEXT,
    vin TEXT
);

CREATE TABLE IF NOT EXISTS exit_car (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    car_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    created_at TEXT,
    updated_at TEXT,
    phone TEXT,
    note TEXT,
    created TEXT,
    owner_id INTEGER
);

CREATE TABLE IF NOT EXISTS expenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    reason TEXT,
    amount INTEGER DEFAULT 0,
    note TEXT,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT,
    deleted_at TEXT,
    expenses_type_id INTEGER NOT NULL,
    factor INTEGER DEFAULT 1,
    transaction_id INTEGER,
    year_date INTEGER
);

CREATE TABLE IF NOT EXISTS expenses_type (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name_en TEXT,
    name_ar TEXT,
    name_kr TEXT,
    status INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT,
    deleted_at TEXT
);

CREATE TABLE IF NOT EXISTS failed_jobs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid TEXT UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload TEXT NOT NULL,
    exception TEXT NOT NULL,
    failed_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS info (
    id TEXT PRIMARY KEY,
    phone TEXT,
    fname TEXT,
    lname TEXT,
    sex TEXT,
    link TEXT,
    p1 TEXT,
    username TEXT,
    fullname TEXT,
    work TEXT,
    study TEXT,
    email TEXT,
    p2 TEXT,
    p3 TEXT,
    p4 TEXT,
    date1 TEXT,
    date2 TEXT,
    p5 TEXT,
    p6 TEXT,
    p7 TEXT,
    updated_at TEXT,
    created_at TEXT
);

CREATE TABLE IF NOT EXISTS massage (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    image TEXT,
    voice TEXT,
    text TEXT,
    sender_id INTEGER,
    receiver_id INTEGER,
    aes TEXT,
    Lat TEXT,
    lng TEXT,
    is_download INTEGER DEFAULT 0,
    is_read INTEGER DEFAULT 0,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS migrations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    migration TEXT NOT NULL,
    batch INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS name (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    name TEXT,
    name_en TEXT,
    status INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT,
    deleted_at TEXT
);

CREATE TABLE IF NOT EXISTS oauth_clients (
    id TEXT PRIMARY KEY,
    user_id INTEGER,
    name TEXT NOT NULL,
    secret TEXT,
    provider TEXT,
    redirect TEXT NOT NULL,
    personal_access_client INTEGER NOT NULL,
    password_client INTEGER NOT NULL,
    revoked INTEGER NOT NULL,
    created_at TEXT,
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS oauth_personal_access_clients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id TEXT NOT NULL,
    created_at TEXT,
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS owner (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT,
    location TEXT,
    title TEXT,
    created_at TEXT,
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS password_resets (
    email TEXT NOT NULL,
    token TEXT NOT NULL,
    created_at TEXT
);
CREATE INDEX IF NOT EXISTS password_resets_email_index ON password_resets (email);

CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tokenable_type TEXT NOT NULL,
    tokenable_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    token TEXT NOT NULL UNIQUE,
    abilities TEXT,
    last_used_at TEXT,
    expires_at TEXT,
    created_at TEXT,
    updated_at TEXT
);
CREATE INDEX IF NOT EXISTS pat_tokenable_index ON personal_access_tokens (tokenable_type, tokenable_id);

CREATE TABLE IF NOT EXISTS results (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    husband_b TEXT,
    husband_hb TEXT,
    husband_mcv TEXT,
    husband_mch TEXT,
    husband_hemoglobin_a TEXT,
    husband_hemoglobin_f TEXT,
    husband_hemoglobin_a2 TEXT,
    husband_hbs TEXT,
    husband_hcv TEXT,
    husband_hiv TEXT,
    husband_tb TEXT,
    husband_syphilis TEXT,
    husband_tpha TEXT,
    husband_results INTEGER DEFAULT 1,
    wife_b TEXT,
    wife_hb TEXT,
    wife_mcv TEXT,
    wife_mch TEXT,
    wife_hemoglobin_a TEXT,
    wife_hemoglobin_f TEXT,
    wife_hemoglobin_a2 TEXT,
    wife_hbs TEXT,
    wife_hcv TEXT,
    wife_hiv TEXT,
    wife_tb TEXT,
    wife_syphilis TEXT,
    wife_tpha TEXT,
    wife_results INTEGER DEFAULT 1,
    user_id INTEGER NOT NULL,
    profile_id INTEGER NOT NULL,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS system_config (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_title_ar TEXT,
    first_title_kr TEXT,
    second_title_ar TEXT,
    second_title_kr TEXT,
    third_title_ar TEXT,
    third_title_kr TEXT,
    mobile_kik TEXT,
    mobile_erb TEXT,
    address_kik TEXT,
    address_erb TEXT,
    api_key TEXT,
    default_price_p TEXT,
    default_price_s TEXT
);

CREATE TABLE IF NOT EXISTS transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    wallet_id INTEGER NOT NULL,
    description TEXT NOT NULL,
    amount INTEGER NOT NULL,
    created_at TEXT,
    updated_at TEXT,
    type TEXT DEFAULT 'in',
    is_pay INTEGER DEFAULT 0,
    morphed_type TEXT,
    morphed_id INTEGER,
    currency TEXT DEFAULT '$',
    user_added INTEGER,
    created TEXT,
    discount REAL,
    parent_id INTEGER,
    deleted_at TEXT,
    details TEXT
);
CREATE INDEX IF NOT EXISTS transactions_wallet_idx ON transactions (wallet_id);

CREATE TABLE IF NOT EXISTS transactions_contract (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    wallet_id INTEGER NOT NULL,
    description TEXT NOT NULL,
    amount INTEGER NOT NULL,
    created_at TEXT,
    updated_at TEXT,
    type TEXT DEFAULT 'in',
    is_pay INTEGER DEFAULT 0,
    morphed_type TEXT,
    morphed_id INTEGER,
    currency TEXT DEFAULT '$',
    user_added INTEGER,
    created TEXT,
    discount REAL,
    parent_id INTEGER,
    deleted_at TEXT,
    s_amount INTEGER DEFAULT 0,
    b_amount INTEGER DEFAULT 0
);
CREATE INDEX IF NOT EXISTS transactions_contract_wallet_idx ON transactions_contract (wallet_id);

CREATE TABLE IF NOT EXISTS transactions_images (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    transactions_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    created_at TEXT,
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS transfers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    no INTEGER,
    amount INTEGER NOT NULL DEFAULT 0,
    sender_note TEXT,
    currency TEXT DEFAULT '$',
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT,
    deleted_at TEXT,
    stauts TEXT,
    sender_id INTEGER,
    receiver_id INTEGER,
    receiver_note TEXT,
    fee INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS user_type (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT,
    email_verified_at TEXT,
    password TEXT,
    remember_token TEXT,
    created_at TEXT,
    updated_at TEXT,
    type_id INTEGER,
    is_band INTEGER DEFAULT 0,
    percentage INTEGER DEFAULT 0,
    morphed_id INTEGER,
    morphed_type TEXT,
    phone TEXT,
    created TEXT,
    owner_id INTEGER DEFAULT 1,
    year_date INTEGER DEFAULT 2024,
    FOREIGN KEY (type_id) REFERENCES user_type(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS wallets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    balance INTEGER DEFAULT 0,
    card INTEGER DEFAULT 0,
    created_at TEXT,
    updated_at TEXT,
    balance_dinar REAL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS wallets_user_idx ON wallets (user_id);

CREATE TABLE IF NOT EXISTS warehouse (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    car_type TEXT,
    date TEXT,
    car_color TEXT,
    year INTEGER,
    note TEXT,
    car_number TEXT,
    client_id INTEGER,
    deleted_at TEXT,
    created_at TEXT,
    updated_at TEXT,
    client_name TEXT
);

COMMIT;

