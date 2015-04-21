DROP TABLE IF EXISTS `civicrm_pricesetmap`;
DROP TABLE IF EXISTS `civicrm_pricesetmap_detail`;

-- /*******************************************************
-- *
-- * pricesetmap
-- *
-- * A map of price set fields that when purchased will be translated into
-- * custom data and relationships
-- *
-- *******************************************************/
CREATE TABLE `civicrm_pricesetmap` (
     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'PriceSet Map ID',
     `is_active` tinyint    COMMENT 'Is this Map active?',
     `page_id` int unsigned COMMENT 'Contribution Page ID',

    PRIMARY KEY ( `id` )
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- /*******************************************************
-- *
-- * pricesetmap_detail
-- *
-- * What to do witch which Price set fields
-- *
-- *******************************************************/

CREATE TABLE `civicrm_pricesetmap_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(10) unsigned NOT NULL COMMENT 'Contribution Page ID',
  `type` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `field_id` int(10) unsigned NOT NULL COMMENT 'The id of the PriceSet field to act upon',
  `field_value` int(10) unsigned DEFAULT NULL,
  `relationship_type` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Relationship Type to create based on purchase of field',
  `related_contact_id` int(10) unsigned DEFAULT NULL COMMENT 'Whom to create the relationship with',
  `relationship_start` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `relationship_end` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `relationship_date_match_membership` tinyint(4) DEFAULT '0',
  `custom_data_id` int(10) unsigned DEFAULT NULL COMMENT 'The field to store the data in',
  `custom_data_format` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


