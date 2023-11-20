DROP TABLE IF EXISTS `#__nm_newsletter_mailing_list`;
DROP TABLE IF EXISTS `#__nm_subscribers`;
DROP TABLE IF EXISTS `#__nm_newsletters`;
DROP TABLE IF EXISTS `#__nm_mailing_list`;


CREATE TABLE `#__nm_mailing_list`
(
    id   INT AUTO_INCREMENT,
    name VARCHAR(255),
    PRIMARY KEY (id)
) ENGINE = InnoDB;

CREATE TABLE `#__nm_subscribers`
(
    id           INT AUTO_INCREMENT,
    email        VARCHAR(255),
    name         VARCHAR(255),
    mail_list_id INT,
    PRIMARY KEY (id),
    FOREIGN KEY (mail_list_id) REFERENCES `#__nm_mailing_list` (id)
) ENGINE = InnoDB;

CREATE TABLE `#__nm_newsletters`
(
    id              INT AUTO_INCREMENT,
    subject         VARCHAR(255),
    send_date       DATE,
    message_content MEDIUMTEXT,
    PRIMARY KEY (id)
) ENGINE = InnoDB;

CREATE TABLE `#__nm_newsletter_mailing_list`
(
    newsletter_id INT,
    mailing_list_id INT,
    PRIMARY KEY (newsletter_id, mailing_list_id),
    FOREIGN KEY (newsletter_id) REFERENCES `#__nm_newsletters` (id),
    FOREIGN KEY (mailing_list_id) REFERENCES `#__nm_mailing_list` (id)
) ENGINE = InnoDB;

INSERT INTO `#__nm_mailing_list` (`name`)
VALUES ('Accounting Team'),
       ('Marketing Group'),
       ('Sales Department'),
       ('HR Management'),
       ('Software Development');


INSERT INTO `#__nm_subscribers` (`name`, `email`, `mail_list_id`)
VALUES ('John Doe', 'johndoe@example.com', 1),
       ('Jane Smith', 'janesmith@example.com', 1),
       ('Bob Johnson', 'bobjohnson@example.com', 1),
       ('Jim Davis', 'jimdavis@example.com', 1),
       ('Jill Wilson', 'jillwilson@example.com', 1),
       ('Tom Brown', 'tombrown@example.com', 2),
       ('Emily Taylor', 'emilytaylor@example.com', 2),
       ('Mike Anderson', 'mikeanderson@example.com', 2),
       ('Sarah Martin', 'sarahmartin@example.com', 2),
       ('Joe Miller', 'joemiller@example.com', 2),
       ('Emma White', 'emmawhite@example.com', 3),
       ('David Moore', 'davidmoore@example.com', 3),
       ('Olivia Jackson', 'oliviajackson@example.com', 3),
       ('Liam Harris', 'liamharris@example.com', 3),
       ('Sophia Thompson', 'sophithompson@example.com', 3),
       ('Mason Turner', 'masonturner@example.com', 4),
       ('Ava Harris', 'avaharris@example.com', 4),
       ('Noah Martinez', 'noahmartinez@example.com', 4),
       ('Ava Martin', 'avamartin@example.com', 4),
       ('Ethan White', 'ethanwhite@example.com', 4);

