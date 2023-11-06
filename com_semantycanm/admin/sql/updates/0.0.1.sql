DROP TABLE IF EXISTS `#__nm_mailing_list`;

CREATE TABLE `#__nm_mailing_list` (
                                       `id` SERIAL NOT NULL,
                                       `name` VARCHAR(100) NOT NULL,
                                       PRIMARY KEY (`id`)
) ENGINE = InnoDB;

INSERT INTO `#__nm_mailing_list` (`name`) VALUES
                                               ('Accounting'),
                                               ('Nuno 3');