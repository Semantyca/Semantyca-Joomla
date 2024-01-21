DROP TABLE IF EXISTS `#__semantyca_nm_stats`;
DROP TABLE IF EXISTS `#__semantyca_nm_mailing_list_rel_usergroups`;
DROP TABLE IF EXISTS `#__semantyca_nm_subscriber_events`;
DROP TABLE IF EXISTS `#__semantyca_nm_newsletters`;
DROP TABLE IF EXISTS `#__semantyca_nm_mailing_list`;
DROP TABLE IF EXISTS `#__semantyca_nm_template_sections`; #deprecated
DROP TABLE IF EXISTS `#__semantyca_nm_templates`;

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



CREATE TABLE IF NOT EXISTS `#__semantyca_nm_subscriber_events`
(
    id               INT AUTO_INCREMENT,
    reg_date         DATETIME DEFAULT CURRENT_TIMESTAMP,
    newsletter_id INT,
    subscriber_email VARCHAR(255),
    event_type       INT,
    expected         BOOLEAN,
    trigger_token    VARCHAR(255),
    event_date       DATETIME,

    PRIMARY KEY (id)
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
    recipients    JSON,
    errors        JSON,
    opens  INT DEFAULT 0,
    clicks INT DEFAULT 0,
    unsubs INT DEFAULT 0,
    status INT DEFAULT 0,
    sent_time     DATETIME,
    PRIMARY KEY (id),
    FOREIGN KEY (newsletter_id) REFERENCES `#__semantyca_nm_newsletters` (id)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `#__semantyca_nm_templates`
(
    id                 INT AUTO_INCREMENT,
    reg_date           DATETIME DEFAULT CURRENT_TIMESTAMP,
    type    VARCHAR(20),
    name               VARCHAR(255),
    content MEDIUMTEXT,
    wrapper MEDIUMTEXT,
    max_articles       INT,
    max_articles_short INT,
    PRIMARY KEY (id)
) ENGINE = InnoDB;
