DROP TABLE IF EXISTS payment_drug_or_biological;
DROP TABLE IF EXISTS payment_medical_supply;
DROP TABLE IF EXISTS payment;
DROP TABLE IF EXISTS drug_or_biological;
DROP TABLE IF EXISTS hospital;
DROP TABLE IF EXISTS recipient_type;
DROP TABLE IF EXISTS manufacturer_or_GPO;
DROP TABLE IF EXISTS medical_supply;
DROP TABLE IF EXISTS third_party;
DROP TABLE IF EXISTS third_party_recipient_policy;
DROP TABLE IF EXISTS location;
DROP TABLE IF EXISTS physician_license;
DROP TABLE IF EXISTS physician;
DROP TABLE IF EXISTS physician_type;
DROP TABLE IF EXISTS payment_form;
DROP TABLE IF EXISTS payment_nature;
DROP TABLE IF EXISTS payment_product;
DROP TABLE IF EXISTS country;
DROP TABLE IF EXISTS state;

CREATE TABLE country (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code CHAR(3),
    full VARCHAR (50) NOT NULL,
    CONSTRAINT country_full_uk UNIQUE (full)
) ENGINE=InnoDB;

CREATE TABLE state (
    id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code CHAR(2) NOT NULL,
    full VARCHAR(30),
    CONSTRAINT state_code_uk UNIQUE (code)
) ENGINE=InnoDB;

CREATE TABLE hospital (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    external_id VARCHAR(100),
    name VARCHAR(100) NOT NULL,
    CONSTRAINT hospital_name_uk UNIQUE (name)
) ENGINE=InnoDB;

CREATE TABLE location (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    address_1 VARCHAR(100),
    address_2 VARCHAR(100),
    city VARCHAR(100),
    province VARCHAR(100),
    state_id TINYINT UNSIGNED,
    postal VARCHAR(20),
    zip CHAR(10),
    country_id SMALLINT UNSIGNED,
    CONSTRAINT location_state_fk FOREIGN KEY (state_id) REFERENCES state (id),
    CONSTRAINT location_country_fk FOREIGN KEY (country_id) REFERENCES country (id)
) ENGINE=InnoDB;

CREATE TABLE drug_or_biological (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    NDC VARCHAR(100),
    name VARCHAR(100)
) ENGINE=InnoDB;

CREATE TABLE manufacturer_or_GPO (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    external_id VARCHAR(100),
    name VARCHAR(100) NOT NULL,
    location_id INTEGER UNSIGNED,
    CONSTRAINT manufacturer_or_GPO_location_fk FOREIGN KEY (location_id) REFERENCES location (id),
    CONSTRAINT manufacturer_or_GPO_name_uk UNIQUE (name)
) ENGINE=InnoDB;

CREATE TABLE medical_supply (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    CONSTRAINT medical_supply_name_uk UNIQUE (name)
) ENGINE=InnoDB;

CREATE TABLE third_party_recipient_policy (
    id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(30) NOT NULL,
    CONSTRAINT third_party_policy_description_uk UNIQUE (description)
) ENGINE=InnoDB;

CREATE TABLE third_party (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    name VARCHAR(100),
    recipient_policy_id TINYINT UNSIGNED NOT NULL,
    CONSTRAINT third_party_policy_fk FOREIGN KEY (recipient_policy_id) REFERENCES third_party_recipient_policy (id)
) ENGINE=InnoDB;

CREATE TABLE physician_type (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(100) NOT NULL,
    CONSTRAINT physician_type_description_uk UNIQUE (description)
) ENGINE=InnoDB;

CREATE TABLE recipient_type (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(100) NOT NULL,
    CONSTRAINT recipient_type_description_uk UNIQUE (description)
) ENGINE=InnoDB;

CREATE TABLE physician (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    profile_id INTEGER UNSIGNED NOT NULL,
    first_name VARCHAR(20) NOT NULL,
    middle_name VARCHAR(20),
    last_name VARCHAR(20) NOT NULL,
    name_suffix VARCHAR(5),
    primary_type_id SMALLINT UNSIGNED NOT NULL,
    specialty VARCHAR(200),
    is_owner TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT physician_primary_type_fk FOREIGN KEY (primary_type_id) REFERENCES physician_type (id),
    CONSTRAINT physician_profile_uk UNIQUE (profile_id)
) ENGINE=InnoDB;

CREATE TABLE physician_license (
    physician_id INTEGER UNSIGNED NOT NULL,
    state_id TINYINT UNSIGNED NOT NULL,
    CONSTRAINT physician_license_physician_fk FOREIGN KEY (physician_id) REFERENCES physician (id),
    CONSTRAINT physician_license_state_fk FOREIGN KEY (state_id) REFERENCES state (id),
    CONSTRAINT physician_license_physician_state_uk UNIQUE (physician_id, state_id)
) ENGINE=InnoDB;

CREATE TABLE payment_form (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(200) NOT NULL,
    CONSTRAINT payment_form_description_uk UNIQUE (description)
) ENGINE=InnoDB;

CREATE TABLE payment_nature (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(200) NOT NULL,
    CONSTRAINT payment_nature_description_uk UNIQUE (description)
) ENGINE=InnoDB;

CREATE TABLE payment_product (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(200) NOT NULL,
    CONSTRAINT payment_product_description_uk UNIQUE (description)
) ENGINE=InnoDB;

CREATE TABLE payment (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    date_paid DATETIME,
    date_published DATETIME,
    transmitted_date_paid VARCHAR(50) NOT NULL,
    transmitted_date_published VARCHAR(50),
    general_transaction_id VARCHAR(100),
    physician_id INTEGER UNSIGNED,
    form_id SMALLINT UNSIGNED NOT NULL,
    nature_id SMALLINT UNSIGNED NOT NULL,
    product_id SMALLINT UNSIGNED NOT NULL,
    submitting_manufacturer_or_GPO_id INTEGER UNSIGNED,
    teaching_hospital_id INTEGER UNSIGNED,
    third_party_id INTEGER UNSIGNED,
    total_amount_USD DECIMAL(10, 2),
    num_total_payments SMALLINT UNSIGNED NOT NULL,
    program_year CHAR(4),
    covered_recipient_type_id SMALLINT UNSIGNED NOT NULL,
    location_id INTEGER UNSIGNED NOT NULL,
    travel_location_id INTEGER UNSIGNED NOT NULL,
    is_charity TINYINT(1) NOT NULL DEFAULT 0,
    is_delayed_in_publication_of_general_payment TINYINT(1) NOT NULL DEFAULT 0,
    is_disputed_for_publication TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT payment_physician_fk FOREIGN KEY (physician_id) REFERENCES physician (id),
    CONSTRAINT payment_form_fk FOREIGN KEY (form_id) REFERENCES payment_form (id),
    CONSTRAINT payment_nature_fk FOREIGN KEY (nature_id) REFERENCES payment_nature (id),
    CONSTRAINT payment_product_fk FOREIGN KEY (product_id) REFERENCES payment_product (id),
    CONSTRAINT payment_submitting_manufacturer_or_GPO_fk FOREIGN KEY (submitting_manufacturer_or_GPO_id) REFERENCES manufacturer_or_GPO (id),
    CONSTRAINT payment_teaching_hospital_fk FOREIGN KEY (teaching_hospital_id) REFERENCES hospital (id),
    CONSTRAINT payment_third_party_fk FOREIGN KEY (third_party_id) REFERENCES third_party (id),
    CONSTRAINT payment_covered_recipient_type_fk FOREIGN KEY (covered_recipient_type_id) REFERENCES recipient_type (id),
    CONSTRAINT payment_location_fk FOREIGN KEY (location_id) REFERENCES location (id),
    CONSTRAINT payment_travel_location_fk FOREIGN KEY (travel_location_id) REFERENCES location (id)
) ENGINE=InnoDB;

CREATE TABLE payment_drug_or_biological (
    payment_id BIGINT UNSIGNED NOT NULL,
    drug_or_biological_id INTEGER UNSIGNED NOT NULL,
    CONSTRAINT payment_drug_or_biological_payment_fk FOREIGN KEY (payment_id) REFERENCES payment (id),
    CONSTRAINT payment_drug_or_biological_drug_or_biological_fk FOREIGN KEY (drug_or_biological_id) REFERENCES drug_or_biological (id)
) ENGINE=InnoDB;

CREATE TABLE payment_medical_supply (
    payment_id BIGINT UNSIGNED NOT NULL,
    medical_supply_id INTEGER UNSIGNED NOT NULL,
    CONSTRAINT payment_medical_supply_payment_fk FOREIGN KEY (payment_id) REFERENCES payment (id),
    CONSTRAINT payment_medical_supply_medical_supply_fk FOREIGN KEY (medical_supply_id) REFERENCES medical_supply (id)
) ENGINE=InnoDB;
