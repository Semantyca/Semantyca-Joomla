CREATE TABLE IF NOT EXISTS `#__semantyca_nm_templates`
(
    id            INT AUTO_INCREMENT,
    reg_date      DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    type          VARCHAR(20),
    version       INT      DEFAULT 1,
    name          VARCHAR(255) NOT NULL UNIQUE,
    is_available  BOOL     DEFAULT false,
    description   MEDIUMTEXT,
    content       MEDIUMTEXT,
    wrapper       MEDIUMTEXT,
    hash          VARCHAR(255),
    PRIMARY KEY (id),
    CONSTRAINT check_name_not_empty CHECK (name <> '')
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `#__semantyca_nm_templates_autosave`
(
    autosave_id     INT AUTO_INCREMENT PRIMARY KEY,
    template_id     INT NOT NULL,
    description     MEDIUMTEXT,
    content         MEDIUMTEXT,
    wrapper         MEDIUMTEXT,
    auto_saved_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES `#__semantyca_nm_templates` (id) ON DELETE CASCADE
) ENGINE = InnoDB;


CREATE TABLE IF NOT EXISTS `#__semantyca_nm_custom_fields`
(
    id            INT AUTO_INCREMENT,
    reg_date      DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    template_id   INT,
    name          VARCHAR(255) NOT NULL,
    type          INT      DEFAULT -1,
    caption       VARCHAR(255) NOT NULL,
    default_value JSON     DEFAULT (JSON_ARRAY()),
    is_available  BOOL     DEFAULT false,
    PRIMARY KEY (id),
    CONSTRAINT `fk_semantyca_nm_template` FOREIGN KEY (template_id) REFERENCES `#__semantyca_nm_templates` (id) ON DELETE CASCADE,
    UNIQUE KEY `unique_type_name` (template_id, type, name)
) ENGINE = InnoDB;



CREATE TABLE IF NOT EXISTS `#__semantyca_nm_mailing_list`
(
    id            INT AUTO_INCREMENT,
    reg_date      DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    name          VARCHAR(255),
    PRIMARY KEY (id)
) ENGINE = InnoDB;


CREATE TABLE IF NOT EXISTS `#__semantyca_nm_mailing_list_rel_usergroups`
(
    mailing_list_id INT,
    user_group_id   INT,
    reg_date        DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_date   DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (mailing_list_id, user_group_id),
    FOREIGN KEY (mailing_list_id) REFERENCES `#__semantyca_nm_mailing_list` (id) ON DELETE CASCADE
) ENGINE = InnoDB;


CREATE TABLE IF NOT EXISTS `#__semantyca_nm_newsletters`
(
    id                   INT AUTO_INCREMENT,
    reg_date             DATETIME   DEFAULT CURRENT_TIMESTAMP,
    modified_date        DATETIME   DEFAULT CURRENT_TIMESTAMP,
    subject              VARCHAR(255),
    use_wrapper          BOOL       DEFAULT TRUE,
    template_id          INT,
    custom_fields_values JSON       DEFAULT (JSON_ARRAY()),
    articles_ids         JSON       DEFAULT (JSON_ARRAY()),
    is_test              BOOL,
    mailing_list_ids     JSON       DEFAULT (JSON_ARRAY()),
    test_email           VARCHAR(255),
    message_content      MEDIUMTEXT DEFAULT '',
    PRIMARY KEY (id)
) ENGINE = InnoDB;


CREATE TABLE IF NOT EXISTS `#__semantyca_nm_sending_events`
(
    id            INT AUTO_INCREMENT,
    reg_date      DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    newsletter_id INT,
    status        INT      DEFAULT 0,
    PRIMARY KEY (id),
    FOREIGN KEY (newsletter_id) REFERENCES `#__semantyca_nm_newsletters` (id) ON DELETE CASCADE
) ENGINE = InnoDB;


CREATE TABLE IF NOT EXISTS `#__semantyca_nm_subscriber_events`
(
    id               INT AUTO_INCREMENT,
    reg_date         DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_date    DATETIME DEFAULT CURRENT_TIMESTAMP,
    sending_id       INT,
    subscriber_email VARCHAR(255),
    event_type       INT      DEFAULT 99,
    fulfilled        INT,
    trigger_token    VARCHAR(255),
    event_date       DATETIME,
    errors           JSON     DEFAULT (JSON_ARRAY()),
    PRIMARY KEY (id),
    FOREIGN KEY (sending_id) REFERENCES `#__semantyca_nm_sending_events` (id) ON DELETE CASCADE
) ENGINE = InnoDB;
