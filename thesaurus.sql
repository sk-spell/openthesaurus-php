-- phpMyAdmin SQL Dump
-- version 2.6.3-pl1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Erstellungszeit: 23. Oktober 2005 um 17:55
-- Server Version: 4.1.13
-- PHP-Version: 4.4.0
-- 
-- Datenbank: `thesaurus`
-- 

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `active_sessions`
-- 

CREATE TABLE active_sessions (
  sid varchar(32) NOT NULL default '',
  name varchar(32) NOT NULL default '',
  val text,
  `changed` varchar(14) NOT NULL default '',
  PRIMARY KEY  (name,sid),
  KEY `changed` (`changed`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `admin_checks`
-- 

CREATE TABLE admin_checks (
  keyname varchar(255) NOT NULL default '',
  `value` varchar(255) default NULL
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `antonyms`
-- 

CREATE TABLE antonyms (
  id int(11) NOT NULL auto_increment,
  word_meaning_id1 int(11) NOT NULL default '0',
  word_meaning_id2 int(11) NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY word_meaning_id1_2 (word_meaning_id1),
  UNIQUE KEY word_meaning_id2 (word_meaning_id2),
  KEY word_meaning_id1 (word_meaning_id1,word_meaning_id2)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `auth_user`
-- 

CREATE TABLE auth_user (
  user_id varchar(100) NOT NULL default '',
  username varchar(100) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  visiblename varchar(255) default NULL,
  perms varchar(255) default NULL,
  subs_date datetime NOT NULL default '0000-00-00 00:00:00',
  blocked tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (user_id),
  UNIQUE KEY k_username (username),
  UNIQUE KEY visiblename (visiblename)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `db_sequence`
-- 

CREATE TABLE db_sequence (
  seq_name varchar(127) NOT NULL default '',
  nextid int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (seq_name)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `meanings`
-- 

CREATE TABLE meanings (
  id int(11) NOT NULL auto_increment,
  distinction varchar(255) default NULL,
  subject_id int(11) default NULL,
  hidden tinyint(4) default '0',
  check_count int(11) NOT NULL default '0',
  super_id int(11) default NULL,
  PRIMARY KEY  (id),
  KEY hidden (hidden),
  KEY check_count (check_count),
  KEY super_id (super_id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `search_log`
-- 

CREATE TABLE search_log (
  id int(11) NOT NULL auto_increment,
  term varchar(255) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  matches int(11) NOT NULL default '0',
  submatch tinyint(4) NOT NULL default '0',
  ip varchar(15) NOT NULL default '0',
  searchtime float default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `subjects`
-- 

CREATE TABLE subjects (
  id int(11) NOT NULL auto_increment,
  `subject` varchar(50) NOT NULL default '',
  explanation varchar(255) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `user_actions_log`
-- 

CREATE TABLE user_actions_log (
  id int(11) NOT NULL auto_increment,
  user_id varchar(255) default '0',
  ip_address varchar(15) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  word varchar(255) NOT NULL default '',
  synset varchar(255) default NULL,
  synset_id int(11) default NULL,
  `type` char(1) NOT NULL default '',
  `comment` varchar(255) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `uses`
-- 

CREATE TABLE uses (
  id int(11) NOT NULL auto_increment,
  name varchar(255) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY name (name)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `word_forms`
-- 

CREATE TABLE word_forms (
  id int(11) NOT NULL default '0',
  word varchar(100) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY word (word)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `word_mapping`
-- 

CREATE TABLE word_mapping (
  derived_id int(11) NOT NULL default '0',
  base_id int(11) NOT NULL default '0',
  KEY derived_id (derived_id),
  KEY base_id (base_id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `word_meanings`
-- 

CREATE TABLE word_meanings (
  id int(11) NOT NULL auto_increment,
  word_id int(11) NOT NULL default '0',
  meaning_id int(11) NOT NULL default '0',
  use_id int(11) default NULL,
  PRIMARY KEY  (id),
  KEY word_id (word_id,meaning_id),
  KEY meaning_id (meaning_id),
  KEY use_id (use_id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `words`
-- 

CREATE TABLE words (
  id int(11) NOT NULL auto_increment,
  word varchar(255) NOT NULL default '',
  lookup varchar(255) default NULL,
  PRIMARY KEY  (id),
  KEY word (word),
  KEY lookup (lookup)
) TYPE=MyISAM;

# manually added:

INSERT INTO `admin_checks` ( `keyname` , `value` ) 
VALUES (
 'check_date', NULL 
);

# added 2006-04-13:
ALTER TABLE `uses` ADD `shortname` VARCHAR( 255 ) NOT NULL ;

# manually added:
INSERT INTO `uses` VALUES (1, '(keine Besonderheit)', '');
INSERT INTO `uses` VALUES (2, 'umgangssprachlich', 'ugs.');
INSERT INTO `uses` VALUES (3, 'derb', 'derb');
INSERT INTO `uses` VALUES (4, 'vulgär', 'vulg.');
INSERT INTO `uses` VALUES (5, 'fachsprachlich', 'fachspr.');

# added 2006-02-04:
ALTER TABLE `user_actions_log` ADD INDEX ( `synset_id` );

# (German) example data for the 'subjects' table:
#INSERT INTO subjects VALUES (1, 'Physik', NULL);
#INSERT INTO subjects VALUES (2, 'Medizin', NULL);
#INSERT INTO subjects VALUES (3, 'Botanik', NULL);
#INSERT INTO subjects VALUES (4, 'Zoologie', NULL);
#INSERT INTO subjects VALUES (5, 'Anatomie', NULL);
#INSERT INTO subjects VALUES (6, 'Computer', NULL);
#INSERT INTO subjects VALUES (7, 'Biologie', NULL);
#INSERT INTO subjects VALUES (8, 'Musik', NULL);
#INSERT INTO subjects VALUES (9, 'Sport', NULL);
#INSERT INTO subjects VALUES (10, 'Technik', NULL);
#INSERT INTO subjects VALUES (11, 'Chemie', NULL);
#INSERT INTO subjects VALUES (12, 'Jura', NULL);
#INSERT INTO subjects VALUES (13, 'Astronomie', NULL);
#INSERT INTO subjects VALUES (14, 'Elektrizität', NULL);
#INSERT INTO subjects VALUES (15, 'Religion', NULL);
#INSERT INTO subjects VALUES (16, 'figurativ', NULL);
#INSERT INTO subjects VALUES (17, 'umgangssprachlich', NULL);
#INSERT INTO subjects VALUES (18, 'Mathematik', NULL);
#INSERT INTO subjects VALUES (19, 'Militär', NULL);
#INSERT INTO subjects VALUES (20, 'Ökonomie', NULL);
#INSERT INTO subjects VALUES (21, 'Automobil', NULL);
#INSERT INTO subjects VALUES (22, 'Gastronomie', NULL);
#INSERT INTO subjects VALUES (23, 'Schiffahrt', NULL);
#INSERT INTO subjects VALUES (24, 'Biochemie', NULL);
#INSERT INTO subjects VALUES (25, 'Geschichte', NULL);
#INSERT INTO subjects VALUES (26, 'Politik', NULL);
#INSERT INTO subjects VALUES (27, 'Geologie', NULL);

# added 2006-04-15:
CREATE TABLE `wiktionary` (
  `headword` varchar(255) NOT NULL default '',
  `meanings` text,
  `synonyms` text
) ENGINE=MyISAM;
ALTER TABLE `wiktionary` ADD INDEX ( `headword` );

# added 2006-09-03:
ALTER TABLE `search_log` ADD `webservice` TINYINT NOT NULL DEFAULT '0';
# added 2006-09-23:
ALTER TABLE `search_log` ADD `searchform` TINYINT NULL;
INSERT INTO `admin_checks` ( `keyname` , `value` ) VALUES ( 'login_date', NULL );
# added 2006-12-12:
ALTER TABLE `auth_user` ADD `last_login` DATETIME NULL ;
