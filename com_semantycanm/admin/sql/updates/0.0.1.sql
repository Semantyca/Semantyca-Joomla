CREATE TABLE IF NOT EXISTS `#__semantyca_nm_templates`
(
    id                 INT AUTO_INCREMENT,
    reg_date           DATETIME DEFAULT CURRENT_TIMESTAMP,
    type               VARCHAR(20),
    name               VARCHAR(255),
    content            MEDIUMTEXT,
    wrapper            MEDIUMTEXT,
    banner             VARCHAR(255),
    max_articles       INT,
    max_articles_short INT,
    PRIMARY KEY (id)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `#__semantyca_nm_mailing_list`
(
    id       INT AUTO_INCREMENT,
    reg_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    name     VARCHAR(255),
    PRIMARY KEY (id)
) ENGINE = InnoDB;


CREATE TABLE IF NOT EXISTS `#__semantyca_nm_mailing_list_rel_usergroups`
(
    mailing_list_id INT,
    user_group_id   INT,
    reg_date        DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (mailing_list_id, user_group_id),
    FOREIGN KEY (mailing_list_id) REFERENCES `#__semantyca_nm_mailing_list` (id) ON DELETE CASCADE
) ENGINE = InnoDB;


CREATE TABLE IF NOT EXISTS `#__semantyca_nm_newsletters`
(
    id              INT AUTO_INCREMENT,
    reg_date        DATETIME DEFAULT CURRENT_TIMESTAMP,
    subject         VARCHAR(255),
    message_content MEDIUMTEXT,
    hash            CHAR(64) AS (SHA2(CONCAT(subject, message_content), 256)) STORED,
    PRIMARY KEY (id)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `#__semantyca_nm_stats`
(
    id            INT AUTO_INCREMENT,
    reg_date      DATETIME DEFAULT CURRENT_TIMESTAMP,
    newsletter_id INT,
    status        INT      DEFAULT 0,
    PRIMARY KEY (id),
    FOREIGN KEY (newsletter_id) REFERENCES `#__semantyca_nm_newsletters` (id) ON DELETE CASCADE
) ENGINE = InnoDB;


CREATE TABLE IF NOT EXISTS `#__semantyca_nm_subscriber_events`
(
    id               INT AUTO_INCREMENT,
    reg_date         DATETIME DEFAULT CURRENT_TIMESTAMP,
    stats_id         INT,
    subscriber_email VARCHAR(255),
    event_type       INT      DEFAULT 99,
    fulfilled        INT      DEFAULT 0,
    trigger_token    VARCHAR(255),
    event_date       DATETIME,
    errors JSON DEFAULT (JSON_ARRAY()),
    PRIMARY KEY (id),
    FOREIGN KEY (stats_id) REFERENCES `#__semantyca_nm_stats` (id) ON DELETE CASCADE
) ENGINE = InnoDB;
