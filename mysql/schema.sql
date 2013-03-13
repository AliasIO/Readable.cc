SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

DROP SCHEMA IF EXISTS `readable_cc` ;
CREATE SCHEMA IF NOT EXISTS `readable_cc` DEFAULT CHARACTER SET latin1 ;
USE `readable_cc` ;

-- -----------------------------------------------------
-- Table `readable_cc`.`feeds`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `readable_cc`.`feeds` ;

CREATE  TABLE IF NOT EXISTS `readable_cc`.`feeds` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `url` VARCHAR(255) NOT NULL ,
  `title` VARCHAR(225) NULL ,
  `link` VARCHAR(225) NULL ,
  `created_at` DATETIME NULL DEFAULT NULL ,
  `last_fetched_at` DATETIME NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `url` (`url` ASC) ,
  INDEX `last_fetched_at` (`last_fetched_at` ASC) )
ENGINE = InnoDB
AUTO_INCREMENT = 12
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `readable_cc`.`items`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `readable_cc`.`items` ;

CREATE  TABLE IF NOT EXISTS `readable_cc`.`items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `url` VARCHAR(255) NOT NULL ,
  `title` VARCHAR(255) NULL ,
  `contents` TEXT NULL ,
  `posted_at` DATETIME NOT NULL ,
  `feed_id` INT(11) UNSIGNED NULL DEFAULT NULL ,
  `created_at` DATETIME NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `url` (`url` ASC) ,
  INDEX `posted_at` (`posted_at` ASC) ,
  INDEX `feed_id` (`feed_id` ASC) ,
  CONSTRAINT `items_feed_id`
    FOREIGN KEY (`feed_id` )
    REFERENCES `readable_cc`.`feeds` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `readable_cc`.`words`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `readable_cc`.`words` ;

CREATE  TABLE IF NOT EXISTS `readable_cc`.`words` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `word` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `word` (`word` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `readable_cc`.`items_words`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `readable_cc`.`items_words` ;

CREATE  TABLE IF NOT EXISTS `readable_cc`.`items_words` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `item_id` INT(11) UNSIGNED NOT NULL ,
  `word_id` INT(11) UNSIGNED NOT NULL ,
  `count` INT(11) UNSIGNED NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `item_word` (`item_id` ASC, `word_id` ASC) ,
  INDEX `count` (`count` ASC) ,
  INDEX `items_words_item_id` (`item_id` ASC) ,
  INDEX `items_words_word_id` (`word_id` ASC) ,
  CONSTRAINT `items_words_item_id`
    FOREIGN KEY (`item_id` )
    REFERENCES `readable_cc`.`items` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `items_words_word_id`
    FOREIGN KEY (`word_id` )
    REFERENCES `readable_cc`.`words` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `readable_cc`.`users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `readable_cc`.`users` ;

CREATE  TABLE IF NOT EXISTS `readable_cc`.`users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `email` VARCHAR(255) NULL DEFAULT NULL ,
  `password` VARCHAR(255) NULL DEFAULT NULL ,
  `timezone` INT(11) NULL DEFAULT 0 ,
  `created_at` DATETIME NULL ,
  `updated_at` DATETIME NULL ,
  `last_active_at` DATETIME NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `email` (`email` ASC) ,
  INDEX `last_active_at` (`last_active_at` ASC) )
ENGINE = InnoDB
AUTO_INCREMENT = 12
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `readable_cc`.`users_feeds`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `readable_cc`.`users_feeds` ;

CREATE  TABLE IF NOT EXISTS `readable_cc`.`users_feeds` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `user_id` INT(11) UNSIGNED NOT NULL ,
  `feed_id` INT(11) UNSIGNED NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `user_feed` (`user_id` ASC, `feed_id` ASC) ,
  INDEX `users_feeds_user_id` (`user_id` ASC) ,
  INDEX `users_feeds_feed_id` (`feed_id` ASC) ,
  CONSTRAINT `users_feeds_user_id`
    FOREIGN KEY (`user_id` )
    REFERENCES `readable_cc`.`users` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `users_feeds_feed_id`
    FOREIGN KEY (`feed_id` )
    REFERENCES `readable_cc`.`feeds` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 7
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `readable_cc`.`users_items`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `readable_cc`.`users_items` ;

CREATE  TABLE IF NOT EXISTS `readable_cc`.`users_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `user_id` INT(11) UNSIGNED NOT NULL ,
  `item_id` INT(11) UNSIGNED NOT NULL ,
  `vote` TINYINT(1) NOT NULL DEFAULT 0 ,
  `score` INT(11) NOT NULL DEFAULT 0 ,
  `read` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `user_item` (`user_id` ASC, `item_id` ASC) ,
  INDEX `vote` (`vote` ASC) ,
  INDEX `users_items_user_id` (`user_id` ASC) ,
  INDEX `users_items_item_id` (`item_id` ASC) ,
  INDEX `read` (`read` ASC) ,
  CONSTRAINT `users_items_user_id`
    FOREIGN KEY (`user_id` )
    REFERENCES `readable_cc`.`users` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `users_items_item_id`
    FOREIGN KEY (`item_id` )
    REFERENCES `readable_cc`.`items` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `readable_cc`.`users_words`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `readable_cc`.`users_words` ;

CREATE  TABLE IF NOT EXISTS `readable_cc`.`users_words` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `user_id` INT(11) UNSIGNED NOT NULL ,
  `word_id` INT(11) UNSIGNED NOT NULL ,
  `score` INT(11) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `user_word` (`user_id` ASC, `word_id` ASC) ,
  INDEX `score` (`score` ASC) ,
  INDEX `users_words_user_id` (`user_id` ASC) ,
  INDEX `users_words_word_id` (`word_id` ASC) ,
  CONSTRAINT `users_words_user_id`
    FOREIGN KEY (`user_id` )
    REFERENCES `readable_cc`.`users` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `users_words_word_id`
    FOREIGN KEY (`word_id` )
    REFERENCES `readable_cc`.`words` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
