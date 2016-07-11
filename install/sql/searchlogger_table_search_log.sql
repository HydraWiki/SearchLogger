CREATE TABLE /*_*/search_log (
  `sid` int(12) NOT NULL AUTO_INCREMENT,
  `search_term` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `search_method` enum('fulltext','go','partial','ajax') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'partial',
  `timestamp` int(14) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sid`),
  KEY `search_term` (`search_term`,`search_method`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;