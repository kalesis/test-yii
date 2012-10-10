/*
	Cruge Data Model
	----------------

	lista de tablas de Cruge.

	aqui van dos grupos:

		1. aquellas propias de Cruge.

		2. aquellas del paquete de autenticacion oficial del Yii, pero con una modificacion minima.

	tablas:
		seg_system, seg_user, seg_session, seg_field, seg_fieldvalue
			@author: Christian Salazar H. <christiansalazarh@gmail.com> @bluyell

		seg_authitem, seg_authitemchild, seg_authassignment
			paquete original de Yii, pero con modificaciones en seg_authassignment
			para relacionarla con seg_user (foregin key on delete cascade), ademas
			de cambiarle el tipo de clave del iduser de VARCHAR(64) a INT
*/
CREATE TABLE `seg_system` (
  `idsystem` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NULL ,
  `largename` VARCHAR(45) NULL ,
  `sessionmaxdurationmins` INT(11) NULL DEFAULT 30 ,
  `sessionmaxsameipconnections` INT(11) NULL DEFAULT 10 ,
  `sessionreusesessions` INT(11) NULL DEFAULT 1 COMMENT '1yes 0no' ,
  `sessionmaxsessionsperday` INT(11) NULL DEFAULT -1 ,
  `sessionmaxsessionsperuser` INT(11) NULL DEFAULT -1 ,
  `systemnonewsessions` INT(11) NULL DEFAULT 0 COMMENT '1yes 0no' ,
  `systemdown` INT(11) NULL DEFAULT 0 ,
  `registerusingcaptcha` INT(11) NULL DEFAULT 0 ,
  `registerusingterms` INT(11) NULL DEFAULT 0 ,
  `terms` BLOB NULL ,
  `registerusingactivation` INT(11) NULL DEFAULT 1 ,
  `defaultroleforregistration` VARCHAR(64) NULL ,
  `registerusingtermslabel` VARCHAR(100) NULL ,
  `registrationonlogin` INT(11) NULL DEFAULT 1 ,
  PRIMARY KEY (`idsystem`) )
ENGINE = InnoDB;


CREATE TABLE `seg_session` (
  `idsession` INT NOT NULL AUTO_INCREMENT ,
  `iduser` INT NOT NULL ,
  `created` BIGINT(30) NULL ,
  `expire` BIGINT(30) NULL ,
  `status` INT(11) NULL DEFAULT 0 ,
  `ipaddress` VARCHAR(45) NULL ,
  `usagecount` INT(11) NULL DEFAULT 0 ,
  `lastusage` BIGINT(30) NULL ,
  `logoutdate` BIGINT(30) NULL ,
  `ipaddressout` VARCHAR(45) NULL ,
  PRIMARY KEY (`idsession`) ,
  INDEX `crugesession_iduser` (`iduser` ASC) )
ENGINE = InnoDB;

CREATE  TABLE `seg_user` (
  `iduser` INT NOT NULL AUTO_INCREMENT ,
  `regdate` BIGINT(30) NULL ,
  `actdate` BIGINT(30) NULL ,
  `logondate` BIGINT(30) NULL ,
  `username` VARCHAR(45) NULL ,
  `email` VARCHAR(45) NULL ,
  `password` VARCHAR(45) NULL COMMENT 'md5 encrypted' ,
  `authkey` VARCHAR(100) NULL COMMENT 'llave de autentificacion' ,
  `state` INT(11) NULL DEFAULT 0 ,
  `totalsessioncounter` INT(11) NULL DEFAULT 0 ,
  `currentsessioncounter` INT(11) NULL DEFAULT 0 ,
  PRIMARY KEY (`iduser`) )
ENGINE = InnoDB;

delete from `seg_user`;
ALTER TABLE `seg_user` AUTO_INCREMENT = 1;
insert into `seg_user`(username, email, password, state) values
 ('admin', 'admin@tucorreo.com','admin',1),('invitado', 'invitado','nopassword',1)
;
ALTER TABLE `seg_user` AUTO_INCREMENT = 10;
delete from `seg_system`;
INSERT INTO `seg_system` (`idsystem`,`name`,`largename`,`sessionmaxdurationmins`,`sessionmaxsameipconnections`,`sessionreusesessions`,`sessionmaxsessionsperday`,`sessionmaxsessionsperuser`,`systemnonewsessions`,`systemdown`,`registerusingcaptcha`,`registerusingterms`,`terms`,`registerusingactivation`,`defaultroleforregistration`,`registerusingtermslabel`,`registrationonlogin`) VALUES
 (1,'default',NULL,30,10,1,-1,-1,0,0,0,0,'',0,'','',1);



CREATE  TABLE `seg_field` (
  `idfield` INT NOT NULL AUTO_INCREMENT ,
  `fieldname` VARCHAR(20) NOT NULL ,
  `longname` VARCHAR(50) NULL ,
  `position` INT(11) NULL DEFAULT 0 ,
  `required` INT(11) NULL DEFAULT 0 ,
  `fieldtype` INT(11) NULL DEFAULT 0 ,
  `fieldsize` INT(11) NULL DEFAULT 20 ,
  `maxlength` INT(11) NULL DEFAULT 45 ,
  `showinreports` INT(11) NULL DEFAULT 0 ,
  `useregexp` VARCHAR(512) NULL ,
  `useregexpmsg` VARCHAR(512) NULL ,
  `predetvalue` MEDIUMBLOB NULL ,
  PRIMARY KEY (`idfield`) )
ENGINE = InnoDB;

CREATE  TABLE `seg_fieldvalue` (
  `idfieldvalue` INT NOT NULL AUTO_INCREMENT ,
  `iduser` INT NOT NULL ,
  `idfield` INT NOT NULL ,
  `value` BLOB NULL ,
  PRIMARY KEY (`idfieldvalue`) ,
  INDEX `fk_seg_fieldvalue_seg_user1` (`iduser` ASC) ,
  INDEX `fk_seg_fieldvalue_seg_field1` (`idfield` ASC) ,
  CONSTRAINT `fk_seg_fieldvalue_seg_user1`
    FOREIGN KEY (`iduser` )
    REFERENCES `seg_user` (`iduser` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_seg_fieldvalue_seg_field1`
    FOREIGN KEY (`idfield` )
    REFERENCES `seg_field` (`idfield` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE `seg_authitem` (
  `name` VARCHAR(64) NOT NULL ,
  `type` INT(11) NOT NULL ,
  `description` TEXT NULL DEFAULT NULL ,
  `bizrule` TEXT NULL DEFAULT NULL ,
  `data` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`name`) )
ENGINE = InnoDB;

CREATE TABLE `seg_authassignment` (
  `userid` INT NOT NULL ,
  `bizrule` TEXT NULL DEFAULT NULL ,
  `data` TEXT NULL DEFAULT NULL ,
  `itemname` VARCHAR(64) NOT NULL ,
  PRIMARY KEY (`userid`, `itemname`) ,
  INDEX `fk_seg_authassignment_seg_authitem1` (`itemname` ASC) ,
  INDEX `fk_seg_authassignment_user` (`userid` ASC) ,
  CONSTRAINT `fk_seg_authassignment_seg_authitem1`
    FOREIGN KEY (`itemname` )
    REFERENCES `seg_authitem` (`name` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_seg_authassignment_user`
    FOREIGN KEY (`userid` )
    REFERENCES `seg_user` (`iduser` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


CREATE TABLE `seg_authitemchild` (
  `parent` VARCHAR(64) NOT NULL ,
  `child` VARCHAR(64) NOT NULL ,
  PRIMARY KEY (`parent`, `child`) ,
  INDEX `child` (`child` ASC) ,
  CONSTRAINT `crugeauthitemchild_ibfk_1`
    FOREIGN KEY (`parent` )
    REFERENCES `seg_authitem` (`name` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `crugeauthitemchild_ibfk_2`
    FOREIGN KEY (`child` )
    REFERENCES `seg_authitem` (`name` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;