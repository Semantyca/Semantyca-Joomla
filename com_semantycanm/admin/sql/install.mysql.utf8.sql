DROP TABLE IF EXISTS `#__subscriber_events`;
DROP TABLE IF EXISTS `#__nm_subscribers`;
DROP TABLE IF EXISTS `#__nm_events`;
DROP TABLE IF EXISTS `#__nm_stats`;
DROP TABLE IF EXISTS `#__nm_newsletter_mailing_list`;
DROP TABLE IF EXISTS `#__nm_newsletters`;
DROP TABLE IF EXISTS `#__nm_mailing_list`;



CREATE TABLE `#__nm_mailing_list`
(
    id       INT AUTO_INCREMENT,
    reg_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    name     VARCHAR(255),
    PRIMARY KEY (id)
) ENGINE = InnoDB;

CREATE TABLE `#__nm_subscribers`
(
    id           INT AUTO_INCREMENT,
    reg_date     DATETIME DEFAULT CURRENT_TIMESTAMP,
    email        VARCHAR(255),
    name         VARCHAR(255),
    mail_list_id INT,
    PRIMARY KEY (id),
    FOREIGN KEY (mail_list_id) REFERENCES `#__nm_mailing_list` (id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE `#__subscriber_events`
(
    id            INT AUTO_INCREMENT,
    reg_date      DATETIME DEFAULT CURRENT_TIMESTAMP,
    subscriber_id INT,
    event_type    INT,
    expected      BOOLEAN,
    trigger_token VARCHAR(255),
    event_date    DATETIME,

    PRIMARY KEY (id),
    FOREIGN KEY (subscriber_id) REFERENCES `#__nm_subscribers` (id) ON DELETE CASCADE
) ENGINE = InnoDB;


CREATE TABLE `#__nm_newsletters`
(
    id              INT AUTO_INCREMENT,
    reg_date        DATETIME DEFAULT CURRENT_TIMESTAMP,
    subject         VARCHAR(255),
    message_content MEDIUMTEXT,
    hash            CHAR(64) AS (SHA2(CONCAT(subject, message_content), 256)) STORED,
    PRIMARY KEY (id)
) ENGINE = InnoDB;


CREATE TABLE `#__nm_newsletter_mailing_list`
(
    reg_date        DATETIME DEFAULT CURRENT_TIMESTAMP,
    newsletter_id   INT,
    mailing_list_id INT,
    PRIMARY KEY (newsletter_id, mailing_list_id),
    FOREIGN KEY (newsletter_id) REFERENCES `#__nm_newsletters` (id),
    FOREIGN KEY (mailing_list_id) REFERENCES `#__nm_mailing_list` (id)
) ENGINE = InnoDB;

CREATE TABLE `#__nm_stats`
(
    id            INT AUTO_INCREMENT,
    reg_date      DATETIME DEFAULT CURRENT_TIMESTAMP,
    newsletter_id INT,
    recipients    JSON,
    opens         INT,
    clicks        INT,
    unsubs        INT,
    status        INT,
    sent_time     DATETIME,
    PRIMARY KEY (id),
    FOREIGN KEY (newsletter_id) REFERENCES `#__nm_newsletters` (id)
) ENGINE = InnoDB;


CREATE TABLE `#__nm_events`
(
    id           INT AUTO_INCREMENT,
    event_time   DATETIME DEFAULT CURRENT_TIMESTAMP,
    event_type   VARCHAR(255),
    event_source VARCHAR(255),
    stats_id     INT,
    PRIMARY KEY (id),
    FOREIGN KEY (stats_id) REFERENCES `#__nm_stats` (id)
) ENGINE = InnoDB;



INSERT INTO `#__nm_mailing_list` (`name`)
VALUES ('Accounting Team'),
       ('Marketing Group'),
       ('Sales Department'),
       ('HR Management'),
       ('Software Development');


INSERT INTO `#__nm_subscribers` (`name`, `email`, `mail_list_id`)
VALUES ('John Doe', 'johndoe@semantyca.com', 1),
       ('Jane Smith', 'janesmith@semantyca.com', 1),
       ('Bob Johnson', 'bobjohnson@semantyca.com', 1),
       ('Jim Davis', 'jimdavis@semantyca.com', 1),
       ('Jill Wilson', 'jillwilson@semantyca.com', 1),
       ('Tom Brown', 'tombrown@semantyca.com', 2),
       ('Emily Taylor', 'emilytaylor@semantyca.com', 2),
       ('Mike Anderson', 'mikeanderson@semantyca.com', 2),
       ('Sarah Martin', 'sarahmartin@semantyca.com', 2),
       ('Joe Miller', 'joemiller@semantyca.com', 2),
       ('Emma White', 'emmawhite@semantyca.com', 3),
       ('David Moore', 'davidmoore@semantyca.com', 3),
       ('Olivia Jackson', 'oliviajackson@semantyca.com', 3),
       ('Liam Harris', 'liamharris@semantyca.com', 3),
       ('Sophia Thompson', 'sophithompson@semantyca.com', 3),
       ('Mason Turner', 'masonturner@semantyca.com', 4),
       ('Ava Harris', 'avaharris@semantyca.com', 4),
       ('Noah Martinez', 'noahmartinez@semantyca.com', 4),
       ('Ava Martin', 'avamartin@semantyca.com', 4),
       ('Ethan White', 'ethanwhite@semantyca.com', 4);

