/*
Navicat MySQL Data Transfer

Source Server         : VIRTUALBOX
Source Server Version : 50538
Source Host           : 192.168.0.23:3306
Source Database       : nginad

Target Server Type    : MYSQL
Target Server Version : 50538
File Encoding         : 65001

Date: 2014-12-12 10:22:07
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for InsertionOrder
-- ----------------------------
DROP TABLE IF EXISTS `InsertionOrder`;
CREATE TABLE `InsertionOrder` (
  `InsertionOrderID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `Name` char(100) NOT NULL,
  `StartDate` datetime NOT NULL,
  `EndDate` datetime NOT NULL,
  `Customer` char(100) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `ImpressionsCounter` int(11) NOT NULL,
  `MaxImpressions` int(11) NOT NULL,
  `CurrentSpend` float NOT NULL,
  `MaxSpend` float NOT NULL,
  `Active` tinyint(1) NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`InsertionOrderID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of InsertionOrder
-- ----------------------------
INSERT INTO `InsertionOrder` VALUES ('4', '21', 'Desktop Media Campaign for XBrand', '2014-09-03 00:00:00', '2019-05-19 00:00:00', 'XBrand', '10101100', '0', '1000000', '0', '100', '1', '2014-09-03 22:57:27', '2015-04-26 21:56:20');
INSERT INTO `InsertionOrder` VALUES ('5', '21', 'Mobile Media Campaign for XBrand', '2014-09-03 00:00:00', '2019-05-01 00:00:00', 'XBrand', '10101100', '0', '1000000', '0', '100', '1', '2014-09-03 22:57:27', '2015-04-26 21:56:20');

-- ----------------------------
-- Table structure for InsertionOrderLineItem
-- ----------------------------
DROP TABLE IF EXISTS `InsertionOrderLineItem`;
CREATE TABLE `InsertionOrderLineItem` (
  `InsertionOrderLineItemID` int(11) NOT NULL AUTO_INCREMENT,
  `InsertionOrderID` int(11) NOT NULL,
  `InsertionOrderTypeID` int(11) NOT NULL,
  `ImpressionType` char(10) NOT NULL DEFAULT 'banner',
  `UserID` int(11) NOT NULL,
  `Name` char(100) NOT NULL,
  `StartDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `EndDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsMobile` smallint(1) NOT NULL,
  `IABSize` char(255) NOT NULL,
  `Height` int(11) NOT NULL,
  `Width` int(11) NOT NULL,
  `Weight` int(11) NOT NULL DEFAULT '5',
  `BidAmount` float NOT NULL,
  `AdTag` text NOT NULL,
  `DeliveryType` enum('if','js') NOT NULL DEFAULT 'if',
  `LandingPageTLD` char(100) NOT NULL,
  `ImpressionsCounter` int(11) NOT NULL,
  `BidsCounter` int(11) NOT NULL,
  `CurrentSpend` float NOT NULL,
  `Active` tinyint(1) NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`InsertionOrderLineItemID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of InsertionOrderLineItem
-- ----------------------------
INSERT INTO `InsertionOrderLineItem` VALUES ('1', '4', '1', 'banner', '21', 'Top Leaderboard Creative', '2014-09-03 00:00:00', '2019-11-04 00:00:00', '0', '728x90', '90', '728', '5', '0.25', '<!-- Desktop 728x90 START -->\r\n<a href=\"http://www.iab.net/guidelines/508676/508767/displayguidelines\"><img border=\"0\" src=\"http://www.iab.net/media/image/728x90.gif\" /></a>\r\n<!-- Desktop 728x90 END -->', 'js', 'iab.net', '0', '0', '0', '1', '2015-05-03 22:57:27', '2015-05-03 22:57:27');
INSERT INTO `InsertionOrderLineItem` VALUES ('2', '4', '1', 'banner', '21', 'Medium Rectange Creative', '2014-09-03 00:00:00', '2019-11-04 00:00:00', '0', '300x250', '250', '300', '5', '0.25', '<!-- Desktop 300x250 START -->\r\n<a href=\"http://www.iab.net/guidelines/508676/508767/displayguidelines\"><img border=\"0\" src=\"http://www.iab.net/media/image/300x250.gif\" /></a>\r\n<!-- Desktop 300x250 END -->', 'js', 'iab.net', '0', '0', '0', '1', '2015-05-03 23:57:27', '2015-05-03 23:57:27');
INSERT INTO `InsertionOrderLineItem` VALUES ('3', '4', '1', 'banner', '21', 'Wide Skyscraper Creative', '2014-09-03 00:00:00', '2019-11-04 00:00:00', '0', '160x600', '600', '160', '5', '0.25', '<!-- Desktop 160x600 START -->\r\n<a href=\"http://www.iab.net/guidelines/508676/508767/displayguidelines\"><img border=\"0\" src=\"http://www.iab.net/media/image/160x600.gif\" /></a>\r\n<!-- Desktop 160x600 END -->', 'js', 'iab.net', '0', '0', '0', '1', '2015-04-26 20:02:23', '2015-04-26 20:02:23');
-- MOBILE CAMPAIGNS
INSERT INTO `InsertionOrderLineItem` VALUES ('4', '5', '1', 'banner', '21', 'Mobile Top Leaderboard Creative', '2014-09-03 00:00:00', '2019-11-04 00:00:00', '1', '728x90', '90', '728', '5', '0.25', '<!-- Mobile 728x90 START -->\r\n<a href=\"http://www.iab.net/guidelines/508676/508767/displayguidelines\"><img border=\"0\" src=\"http://www.iab.net/media/image/728x90.gif\" /></a>\r\n<!-- Mobile 728x90 END -->', 'js', 'iab.net', '0', '0', '0', '1', '2015-05-03 22:57:27', '2015-05-03 22:57:27');
INSERT INTO `InsertionOrderLineItem` VALUES ('5', '5', '1', 'banner', '21', 'Mobile Medium Rectange Creative', '2014-09-03 00:00:00', '2019-11-04 00:00:00', '1', '300x250', '250', '300', '5', '0.25', '<!-- Mobile 300x250 START -->\r\n<a href=\"http://www.iab.net/guidelines/508676/508767/displayguidelines\"><img border=\"0\" src=\"http://www.iab.net/media/image/300x250.gif\" /></a>\r\n<!-- Mobile 300x250 END -->', 'js', 'iab.net', '0', '0', '0', '1', '2015-05-03 23:57:27', '2015-05-03 23:57:27');
INSERT INTO `InsertionOrderLineItem` VALUES ('6', '5', '1', 'banner', '21', 'Mobile Wide Skyscraper Creative', '2014-09-03 00:00:00', '2019-11-04 00:00:00', '1', '160x600', '600', '160', '5', '0.25', '<!-- Mobile 160x600 START -->\r\n<a href=\"http://www.iab.net/guidelines/508676/508767/displayguidelines\"><img border=\"0\" src=\"http://www.iab.net/media/image/160x600.gif\" /></a>\r\n<!-- Mobile 160x600 END -->', 'js', 'iab.net', '0', '0', '0', '1', '2015-04-26 20:02:23', '2015-04-26 20:02:23');

-- ----------------------------
-- Table structure for InsertionOrderLineItemDomainExclusion
-- ----------------------------
DROP TABLE IF EXISTS `InsertionOrderLineItemDomainExclusion`;
CREATE TABLE `InsertionOrderLineItemDomainExclusion` (
  `InsertionOrderLineItemDomainExclusionID` int(11) NOT NULL AUTO_INCREMENT,
  `InsertionOrderLineItemID` int(11) NOT NULL,
  `ExclusionType` enum('url','referrer') NOT NULL,
  `DomainName` char(255) NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`InsertionOrderLineItemDomainExclusionID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of InsertionOrderLineItemDomainExclusion
-- ----------------------------

-- ----------------------------
-- Table structure for InsertionOrderLineItemDomainExclusionPreview
-- ----------------------------
DROP TABLE IF EXISTS `InsertionOrderLineItemDomainExclusionPreview`;
CREATE TABLE `InsertionOrderLineItemDomainExclusionPreview` (
  `InsertionOrderLineItemDomainExclusionPreviewID` int(11) NOT NULL AUTO_INCREMENT,
  `InsertionOrderLineItemPreviewID` int(11) NOT NULL,
  `ExclusionType` enum('url','referrer') NOT NULL,
  `DomainName` char(255) NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`InsertionOrderLineItemDomainExclusionPreviewID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of InsertionOrderLineItemDomainExclusionPreview
-- ----------------------------

-- ----------------------------
-- Table structure for InsertionOrderLineItemDomainExclusiveInclusion
-- ----------------------------
DROP TABLE IF EXISTS `InsertionOrderLineItemDomainExclusiveInclusion`;
CREATE TABLE `InsertionOrderLineItemDomainExclusiveInclusion` (
  `InsertionOrderLineItemDomainExclusiveInclusionID` int(11) NOT NULL AUTO_INCREMENT,
  `InsertionOrderLineItemID` int(11) NOT NULL,
  `InclusionType` enum('url','referrer') NOT NULL,
  `DomainName` char(255) NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`InsertionOrderLineItemDomainExclusiveInclusionID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of InsertionOrderLineItemDomainExclusiveInclusion
-- ----------------------------

-- ----------------------------
-- Table structure for InsertionOrderLineItemDomainExclusiveInclusionPreview
-- ----------------------------
DROP TABLE IF EXISTS `InsertionOrderLineItemDomainExclusiveInclusionPreview`;
CREATE TABLE `InsertionOrderLineItemDomainExclusiveInclusionPreview` (
  `InsertionOrderLineItemDomainExclusiveInclusionPreviewID` int(11) NOT NULL AUTO_INCREMENT,
  `InsertionOrderLineItemPreviewID` int(11) NOT NULL,
  `InclusionType` enum('url','referrer') NOT NULL,
  `DomainName` char(255) NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`InsertionOrderLineItemDomainExclusiveInclusionPreviewID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of InsertionOrderLineItemDomainExclusiveInclusionPreview
-- ----------------------------

-- ----------------------------
-- Table structure for InsertionOrderLineItemPreview
-- ----------------------------
DROP TABLE IF EXISTS `InsertionOrderLineItemPreview`;
CREATE TABLE `InsertionOrderLineItemPreview` (
  `InsertionOrderLineItemPreviewID` int(11) NOT NULL AUTO_INCREMENT,
  `InsertionOrderPreviewID` int(11) NOT NULL,
  `InsertionOrderLineItemID` int(11) NOT NULL,
  `InsertionOrderTypeID` int(11) NOT NULL,
  `ImpressionType` char(10) NOT NULL DEFAULT 'banner',
  `UserID` int(11) NOT NULL,
  `Name` char(100) NOT NULL,
  `StartDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `EndDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsMobile` smallint(1) NOT NULL,
  `IABSize` char(255) NOT NULL,
  `Height` int(11) NOT NULL,
  `Width` int(11) NOT NULL,
  `Weight` int(11) NOT NULL DEFAULT '5',
  `BidAmount` float NOT NULL,
  `AdTag` text NOT NULL,
  `DeliveryType` enum('if','js') NOT NULL DEFAULT 'if',
  `LandingPageTLD` char(100) NOT NULL,
  `ImpressionsCounter` int(11) NOT NULL,
  `BidsCounter` int(11) NOT NULL,
  `CurrentSpend` float NOT NULL,
  `Active` tinyint(1) NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ChangeWentLive` tinyint(1) NOT NULL DEFAULT '0',
  `WentLiveDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`InsertionOrderLineItemPreviewID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of InsertionOrderLineItemPreview
-- ----------------------------
INSERT INTO `InsertionOrderLineItemPreview` VALUES ('1', '1', '0', '1', 'banner', '21', 'Top Leaderboard Creative', '2014-09-03 00:00:00', '2015-11-04 00:00:00', '0', '728x90', '90', '728', '5', '0.25', '<script language=\"Javascript\">\r\nvar zflag_nid=\"2674\"; var zflag_cid=\"1\"; var zflag_sid=\"1\"; var zflag_width=\"728\"; var zflag_height=\"90\"; var zflag_sz=\"14\";\r\n</script>\r\n<script language=\"Javascript\" src=\"http://xp2.zedo.com/jsc/xp2/fo.js\"></script>', 'js', 'nginad.com', '0', '0', '0', '0', '2014-09-03 22:44:59', '2014-09-03 22:57:27', '1', '2014-09-03 22:57:27');

-- ----------------------------
-- Table structure for InsertionOrderLineItemRestrictions
-- ----------------------------
DROP TABLE IF EXISTS `InsertionOrderLineItemRestrictions`;
CREATE TABLE `InsertionOrderLineItemRestrictions` (
  `InsertionOrderLineItemRestrictionsID` int(11) NOT NULL AUTO_INCREMENT,
  `InsertionOrderLineItemID` int(11) NOT NULL,
  `GeoCountry` char(255) DEFAULT NULL,
  `GeoState` char(255) DEFAULT NULL,
  `GeoCity` char(255) DEFAULT NULL,
  `AdTagType` enum('JavaScript','Iframe') DEFAULT NULL,
  `AdPositionMinLeft` int(11) DEFAULT NULL,
  `AdPositionMaxLeft` int(11) DEFAULT NULL,
  `AdPositionMinTop` int(11) DEFAULT NULL,
  `AdPositionMaxTop` int(11) DEFAULT NULL,
  `FoldPos` int(11) DEFAULT NULL,
  `Freq` int(11) DEFAULT NULL,
  `Timezone` char(100) DEFAULT NULL,
  `InIframe` tinyint(1) DEFAULT NULL,
  `InMultipleNestedIframes` tinyint(1) DEFAULT NULL,
  `MinScreenResolutionWidth` int(11) DEFAULT NULL,
  `MaxScreenResolutionWidth` int(11) DEFAULT NULL,
  `MinScreenResolutionHeight` int(11) DEFAULT NULL,
  `MaxScreenResolutionHeight` int(11) DEFAULT NULL,
  `HttpLanguage` char(10) DEFAULT NULL,
  `BrowserUserAgentGrep` char(255) DEFAULT NULL,
  `CookieGrep` char(255) DEFAULT NULL,
  `PmpEnable` tinyint(1) DEFAULT NULL,
  `Secure` tinyint(1) DEFAULT NULL,
  `Optout` tinyint(1) DEFAULT NULL,
  `Vertical` char(100) DEFAULT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`InsertionOrderLineItemRestrictionsID`),
  UNIQUE KEY `RTBBannerID` (`InsertionOrderLineItemID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of InsertionOrderLineItemRestrictions
-- ----------------------------

-- ----------------------------
-- Table structure for InsertionOrderItemRestrictionsVideoRestrictions
-- ----------------------------
DROP TABLE IF EXISTS `InsertionOrderLineItemVideoRestrictions`;
CREATE TABLE `InsertionOrderLineItemVideoRestrictions` (
  `InsertionOrderLineItemVideoRestrictionsID` int(11) NOT NULL AUTO_INCREMENT,
  `InsertionOrderLineItemID` int(11) DEFAULT NULL,
  `GeoCountry` char(255) DEFAULT NULL,
  `GeoState` char(255) DEFAULT NULL,
  `GeoCity` char(255) DEFAULT NULL,
  `MimesCommaSeparated` char(100) DEFAULT NULL,
  `MinDuration` int(10) unsigned DEFAULT NULL,
  `MaxDuration` int(10) unsigned DEFAULT NULL,  
  `MinHeight` int(10) unsigned DEFAULT NULL,
  `MinWidth` int(10) unsigned DEFAULT NULL,
  `ApisSupportedCommaSeparated` char(100) DEFAULT NULL,
  `ProtocolsCommaSeparated` char(100) DEFAULT NULL,
  `DeliveryCommaSeparated` char(100) DEFAULT NULL,
  `PlaybackCommaSeparated` char(100) DEFAULT NULL,
  `StartDelay` char(5) DEFAULT NULL,
  `Linearity` int(10) DEFAULT NULL,
  `FoldPos` int(10) DEFAULT NULL,
  `PmpEnable` tinyint(1) DEFAULT NULL,
  `Secure` tinyint(1) DEFAULT NULL,
  `Optout` tinyint(1) DEFAULT NULL,
  `Vertical` char(100) DEFAULT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`InsertionOrderLineItemVideoRestrictionsID`),
  UNIQUE KEY `RTBVideoID` (`InsertionOrderLineItemID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of InsertionOrderVideoRestrictions
-- ----------------------------

-- ----------------------------
-- Table structure for InsertionOrderLineItemRestrictionsPreview
-- ----------------------------
DROP TABLE IF EXISTS `InsertionOrderLineItemRestrictionsPreview`;
CREATE TABLE `InsertionOrderLineItemRestrictionsPreview` (
  `InsertionOrderLineItemRestrictionsPreviewID` int(11) NOT NULL AUTO_INCREMENT,
  `InsertionOrderLineItemPreviewID` int(11) NOT NULL,
  `GeoCountry` char(255) DEFAULT NULL,
  `GeoState` char(255) DEFAULT NULL,
  `GeoCity` char(255) DEFAULT NULL,
  `AdTagType` enum('JavaScript','Iframe') DEFAULT NULL,
  `AdPositionMinLeft` int(11) DEFAULT NULL,
  `AdPositionMaxLeft` int(11) DEFAULT NULL,
  `AdPositionMinTop` int(11) DEFAULT NULL,
  `AdPositionMaxTop` int(11) DEFAULT NULL,
  `FoldPos` int(11) DEFAULT NULL,
  `Freq` int(11) DEFAULT NULL,
  `Timezone` char(100) DEFAULT NULL,
  `InIframe` tinyint(1) DEFAULT NULL,
  `InMultipleNestedIframes` tinyint(1) DEFAULT NULL,
  `MinScreenResolutionWidth` int(11) DEFAULT NULL,
  `MaxScreenResolutionWidth` int(11) DEFAULT NULL,
  `MinScreenResolutionHeight` int(11) DEFAULT NULL,
  `MaxScreenResolutionHeight` int(11) DEFAULT NULL,
  `HttpLanguage` char(10) DEFAULT NULL,
  `BrowserUserAgentGrep` char(255) DEFAULT NULL,
  `CookieGrep` char(255) DEFAULT NULL,
  `PmpEnable` tinyint(1) DEFAULT NULL,
  `Secure` tinyint(1) DEFAULT NULL,
  `Optout` tinyint(1) DEFAULT NULL,
  `Vertical` char(100) DEFAULT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`InsertionOrderLineItemRestrictionsPreviewID`),
  UNIQUE KEY `RTBBannerPreviewID` (`InsertionOrderLineItemPreviewID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of InsertionOrderLineItemRestrictionsPreview
-- ----------------------------

-- ----------------------------
-- Table structure for InsertionOrderLineItemVideoRestrictionsPreview
-- ----------------------------
DROP TABLE IF EXISTS `InsertionOrderLineItemVideoRestrictionsPreview`;
CREATE TABLE `InsertionOrderLineItemVideoRestrictionsPreview` (
  `InsertionOrderLineItemVideoRestrictionsPreviewID` int(11) NOT NULL AUTO_INCREMENT,
  `InsertionOrderLineItemPreviewID` int(11) NOT NULL,
  `GeoCountry` char(255) DEFAULT NULL,
  `GeoState` char(255) DEFAULT NULL,
  `GeoCity` char(255) DEFAULT NULL,
  `MimesCommaSeparated` char(100) DEFAULT NULL,
  `MinDuration` int(10) unsigned DEFAULT NULL,
  `MaxDuration` int(10) unsigned DEFAULT NULL,  
  `MinHeight` int(10) unsigned DEFAULT NULL,
  `MinWidth` int(10) unsigned DEFAULT NULL,
  `ApisSupportedCommaSeparated` char(100) DEFAULT NULL,
  `ProtocolsCommaSeparated` char(100) DEFAULT NULL,
  `DeliveryCommaSeparated` char(100) DEFAULT NULL,
  `PlaybackCommaSeparated` char(100) DEFAULT NULL,
  `StartDelay` char(5) DEFAULT NULL,
  `Linearity` int(10) DEFAULT NULL,
  `FoldPos` int(10) DEFAULT NULL,
  `PmpEnable` tinyint(1) DEFAULT NULL,
  `Secure` tinyint(1) DEFAULT NULL,
  `Optout` tinyint(1) DEFAULT NULL,
  `Vertical` char(100) DEFAULT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`InsertionOrderLineItemVideoRestrictionsPreviewID`),
  UNIQUE KEY `RTBVideoPreviewID` (`InsertionOrderLineItemPreviewID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of InsertionOrderVideoRestrictionsPreview
-- ----------------------------

-- ----------------------------
-- Table structure for InsertionOrderPreview
-- ----------------------------
DROP TABLE IF EXISTS `InsertionOrderPreview`;
CREATE TABLE `InsertionOrderPreview` (
  `InsertionOrderPreviewID` int(11) NOT NULL AUTO_INCREMENT,
  `InsertionOrderID` int(11) DEFAULT NULL,
  `UserID` int(11) NOT NULL,
  `Name` char(100) NOT NULL,
  `StartDate` datetime NOT NULL,
  `EndDate` datetime NOT NULL,
  `Customer` char(100) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `ImpressionsCounter` int(11) NOT NULL,
  `MaxImpressions` int(11) NOT NULL,
  `CurrentSpend` float NOT NULL,
  `MaxSpend` float NOT NULL,
  `Active` tinyint(1) NOT NULL,
  `Deleted` tinyint(1) NOT NULL DEFAULT '0',
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ChangeWentLive` tinyint(1) NOT NULL DEFAULT '0',
  `WentLiveDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`InsertionOrderPreviewID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of InsertionOrderPreview
-- ----------------------------
INSERT INTO `InsertionOrderPreview` VALUES ('1', null, '21', 'My Media Campaign for XBrand', '2014-09-03 00:00:00', '2017-05-19 00:00:00', 'XBrand', '10101100', '0', '1000000', '0', '100', '0', '0', '2014-09-03 22:42:03', '2014-09-03 22:42:03', '1', '2014-09-03 22:57:27');

-- ----------------------------
-- Table structure for InsertionOrderType
-- ----------------------------
DROP TABLE IF EXISTS `InsertionOrderType`;
CREATE TABLE `InsertionOrderType` (
  `InsertionOrderTypeID` int(11) unsigned NOT NULL,
  `Description` varchar(255) NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`InsertionOrderTypeID`),
  UNIQUE KEY `InsertionOrderType_UNIQUE` (`InsertionOrderTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of InsertionOrderType
-- ----------------------------
INSERT INTO `InsertionOrderType` VALUES ('1', 'Any Remnant', '2014-09-03 22:44:24', '2014-09-03 22:44:24');
INSERT INTO `InsertionOrderType` VALUES ('2', 'In House Remnant', '2014-09-03 22:44:24', '2014-09-03 22:44:24');
INSERT INTO `InsertionOrderType` VALUES ('3', 'RTB Remnant', '2014-09-03 22:44:24', '2014-09-03 22:44:24');
INSERT INTO `InsertionOrderType` VALUES ('4', 'Contract', '2014-09-03 22:44:24', '2014-09-03 22:44:24');

-- ----------------------------
-- Table structure for InsertionOrderMarkup
-- ----------------------------
DROP TABLE IF EXISTS `InsertionOrderMarkup`;
CREATE TABLE `InsertionOrderMarkup` (
  `InsertionOrderID` int(11) NOT NULL,
  `MarkupRate` float NOT NULL,
  PRIMARY KEY (`InsertionOrderID`),
  UNIQUE KEY `InsertionOrderID` (`InsertionOrderID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of AdCampainMarkup
-- ----------------------------
INSERT INTO `InsertionOrderMarkup` VALUES ('4', '0.4');

-- ----------------------------
-- Table structure for AdTemplates
-- ----------------------------
DROP TABLE IF EXISTS `AdTemplates`;
CREATE TABLE `AdTemplates` (
  `AdTemplateID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TemplateName` varchar(50) NOT NULL,
  `IsMobileFlag` smallint(6) NOT NULL DEFAULT '0',
  `Width` int(11) NOT NULL,
  `Height` int(11) NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`AdTemplateID`),
  UNIQUE KEY `TemplateName_UNIQUE` (`TemplateName`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of AdTemplates
-- ----------------------------
INSERT INTO `AdTemplates` VALUES ('1', 'IAB Full Banner', '0', '468', '60', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('2', 'IAB Skyscraper', '0', '120', '600', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('3', 'IAB Leaderboard', '0', '728', '90', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('4', 'IAB Button 1', '0', '120', '90', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('5', 'IAB Button 2', '0', '120', '60', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('6', 'IAB Half Banner', '0', '234', '60', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('7', 'IAB Micro Bar', '0', '88', '31', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('8', 'IAB Square Button', '0', '125', '125', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('9', 'IAB VerticleBanner', '0', '120', '240', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('10', 'IAB Rectangle', '0', '180', '150', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('11', 'IAB Medium Rectangle', '0', '300', '250', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('12', 'IAB Large Rectangle', '0', '336', '280', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('13', 'IAB Vertical Rectangle', '0', '240', '400', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('14', 'IAB Square Pop-up', '0', '250', '250', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('15', 'IAB Wide Skyscraper', '0', '160', '600', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('16', 'IAB Pop-Under', '0', '720', '300', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('17', '3:1 Rectangle', '0', '300', '100', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('18', 'Mobile Phone Banner', '1', '320', '50', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('19', 'Mobile Phone Thin Banner', '1', '300', '50', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('20', 'Mobile Phone Medium Rectangle', '1', '300', '250', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('21', 'Mobile Phone Full Screen', '1', '320', '480', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('22', 'Mobile Phone Thin Full Screen', '1', '300', '480', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('23', 'Mobile Tablet Leaderboard', '2', '728', '90', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('24', 'Mobile Tablet Medium Rectangle', '2', '300', '250', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('25', 'Mobile Tablet Banner', '2', '300', '50', '2014-01-28 23:20:00', '2014-01-28 23:31:33');
INSERT INTO `AdTemplates` VALUES ('26', 'Mobile Tablet Full Screen', '2', '728', '1024', '2014-01-28 23:20:00', '2014-01-28 23:31:33');

-- ----------------------------
-- Table structure for AdType
-- ----------------------------
DROP TABLE IF EXISTS `AdType`;
CREATE TABLE `AdType` (
  `AdTypeID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `AdTypeName` varchar(50) NOT NULL,
  `AdTypeDescription` text,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`AdTypeID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of AdType
-- ----------------------------
INSERT INTO `AdType` VALUES ('1', 'Text', 'Text', '2014-01-28 23:20:00', '2014-01-28 23:20:40');
INSERT INTO `AdType` VALUES ('2', 'Static', 'Static Graphics', '2014-01-28 23:20:00', '2014-01-28 23:20:40');
INSERT INTO `AdType` VALUES ('3', 'Dynamic', 'Dynamic Graphics', '2014-01-28 23:20:00', '2014-01-28 23:20:40');
INSERT INTO `AdType` VALUES ('4', 'Flash', 'Flash Based', '2014-01-28 23:20:00', '2014-01-28 23:20:40');

-- ----------------------------
-- Table structure for auth_Users
-- ----------------------------
DROP TABLE IF EXISTS `auth_Users`;
CREATE TABLE `auth_Users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_login` varchar(50) NOT NULL,
  `user_email` varchar(255) NOT NULL DEFAULT '',
  `user_password` varchar(255) NOT NULL,
  `user_password_salt` char(10) DEFAULT NULL,
  `user_2factor_secret` binary(16) DEFAULT NULL,
  `user_fullname` varchar(255) DEFAULT NULL,
  `user_description` text,
  `user_enabled` smallint(6) NOT NULL DEFAULT '0',
  `user_verified` tinyint(4) NOT NULL DEFAULT '0',
  `user_agreement_accepted` tinyint(4) NOT NULL DEFAULT '0',
  `PublisherInfoID` int(11) DEFAULT NULL,
  `DemandCustomerInfoID` int(11) DEFAULT NULL,
  `create_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_agreement_acceptance_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_permission_cache` blob,
  `user_role` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_login_UNIQUE` (`user_login`),
  UNIQUE KEY `user_email_UNIQUE` (`user_email`) USING BTREE,
  KEY `user_name` (`user_fullname`),
  KEY `user_status` (`user_enabled`),
  KEY `auth_Users_role_fk1` (`user_role`),
  CONSTRAINT `auth_Users_role_fk1` FOREIGN KEY (`user_role`) REFERENCES `rbac_role` (`role_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of auth_Users
-- ----------------------------
INSERT INTO `auth_Users` VALUES ('1', 'admin', 'admin@localhost', '86a65acd94b33daa87c1c6a2d1408593', null, null, null, null, '1', '1', '1', null, null, '2013-11-06 09:24:00', '2013-11-06 09:25:07', '2013-11-06 09:25:07', null, '1');
INSERT INTO `auth_Users` VALUES ('20', 'blowmedia', 'sergey.page@blowmedianow.com', '86a65acd94b33daa87c1c6a2d1408593', null, null, null, null, '1', '1', '1', '3', null, '2014-09-03 21:25:33', '2014-09-03 21:25:33', '2013-11-06 09:25:07', null, '3');
INSERT INTO `auth_Users` VALUES ('21', 'suckmedia', 'larry.brin@suckmedianow.com', '86a65acd94b33daa87c1c6a2d1408593', null, null, null, null, '1', '1', '1', null, '18', '2014-09-03 21:32:24', '2014-09-03 21:32:24', '2013-11-06 09:25:07', null, '2');

-- ----------------------------
-- Table structure for BuySideDailyImpressionsByTLD
-- ----------------------------
DROP TABLE IF EXISTS `BuySideDailyImpressionsByTLD`;
CREATE TABLE `BuySideDailyImpressionsByTLD` (
  `DailyImpressionsByTLDID` int(11) NOT NULL AUTO_INCREMENT,
  `InsertionOrderLineItemID` int(11) NOT NULL,
  `MDY` char(10) NOT NULL,
  `PublisherTLD` char(100) NOT NULL,
  `Impressions` int(11) NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`DailyImpressionsByTLDID`),
  UNIQUE KEY `RTBBannerID_IDX` (`InsertionOrderLineItemID`,`MDY`,`PublisherTLD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of BuySideDailyImpressionsByTLD
-- ----------------------------

-- ----------------------------
-- Table structure for BuySideHourlyBidsCounter
-- ----------------------------
DROP TABLE IF EXISTS `BuySideHourlyBidsCounter`;
CREATE TABLE `BuySideHourlyBidsCounter` (
  `BuySideHourlyBidsCounterID` int(11) NOT NULL AUTO_INCREMENT,
  `BuySidePartnerID` char(100) NOT NULL,
  `InsertionOrderLineItemID` int(11) NOT NULL,
  `MDYH` char(15) NOT NULL,
  `BidsCounter` int(11) NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`BuySideHourlyBidsCounterID`),
  UNIQUE KEY `BuySideHourlyBid_IDX` (`BuySidePartnerID`,`InsertionOrderLineItemID`,`MDYH`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of BuySideHourlyBidsCounter
-- ----------------------------

-- ----------------------------
-- Table structure for BuySideHourlyImpressionsByTLD
-- ----------------------------
DROP TABLE IF EXISTS `BuySideHourlyImpressionsByTLD`;
CREATE TABLE `BuySideHourlyImpressionsByTLD` (
  `BuySideHourlyImpressionsByTLDID` int(11) NOT NULL AUTO_INCREMENT,
  `InsertionOrderLineItemID` int(11) NOT NULL,
  `MDYH` char(15) NOT NULL,
  `PublisherTLD` char(100) NOT NULL,
  `Impressions` int(11) NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`BuySideHourlyImpressionsByTLDID`),
  UNIQUE KEY `AnyBannerID_IDX` (`InsertionOrderLineItemID`,`MDYH`,`PublisherTLD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of BuySideHourlyImpressionsByTLD
-- ----------------------------

-- ----------------------------
-- Table structure for BuySideHourlyImpressionsCounterCurrentSpend
-- ----------------------------
DROP TABLE IF EXISTS `BuySideHourlyImpressionsCounterCurrentSpend`;
CREATE TABLE `BuySideHourlyImpressionsCounterCurrentSpend` (
  `BuySideHourlyImpressionsCounterCurrentSpendID` int(11) NOT NULL AUTO_INCREMENT,
  `BuySidePartnerID` char(100) NOT NULL,
  `InsertionOrderLineItemID` int(11) NOT NULL,
  `MDYH` char(15) NOT NULL,
  `ImpressionsCounter` int(11) NOT NULL,
  `CurrentSpendGross` float NOT NULL,
  `CurrentSpendNet` float NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`BuySideHourlyImpressionsCounterCurrentSpendID`),
  UNIQUE KEY `BuySideHourlyIC_IDX` (`BuySidePartnerID`,`InsertionOrderLineItemID`,`MDYH`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of BuySideHourlyImpressionsCounterCurrentSpend
-- ----------------------------

-- ----------------------------
-- Table structure for ContractPublisherZoneHourlyImpressions
-- ----------------------------
DROP TABLE IF EXISTS `ContractPublisherZoneHourlyImpressions`;
CREATE TABLE `ContractPublisherZoneHourlyImpressions` (
  `ContractPublisherZoneHourlyImpressionsID` int(11) NOT NULL AUTO_INCREMENT,
  `InsertionOrderLineItemID` int(11) NOT NULL,
  `PublisherAdZoneID` int(11) NOT NULL,
  `MDYH` char(15) NOT NULL,
  `Impressions` bigint(20) NOT NULL,
  `SpendTotalGross` float NOT NULL,
  `SpendTotalNet` float NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ContractPublisherZoneHourlyImpressionsID`),
  UNIQUE KEY `ContractPublisherZoneHourlyImpression_IDX` (`ContractPublisherZoneHourlyImpressionsID`,`InsertionOrderLineItemID`,`PublisherAdZoneID`,`MDYH`) USING BTREE,
  UNIQUE KEY `ContractPublisherZoneHourlyImpressions_IDX` (`InsertionOrderLineItemID`,`PublisherAdZoneID`,`MDYH`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of ContractPublisherZoneHourlyImpressions
-- ----------------------------

-- ----------------------------
-- Table structure for DemandCustomerInfo
-- ----------------------------
DROP TABLE IF EXISTS `DemandCustomerInfo`;
CREATE TABLE `DemandCustomerInfo` (
  `DemandCustomerInfoID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Website` varchar(255) NOT NULL,
  `Company` varchar(255) NOT NULL,
  `PartnerType` int(11) NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`DemandCustomerInfoID`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of DemandCustomerInfo
-- ----------------------------
INSERT INTO `DemandCustomerInfo` VALUES ('18', 'Larry Brin', 'larry.brin@suckmedianow.com', 'suckmedianow.com', 'Suck Media Now', '1', '2014-09-03 21:32:24', '2014-09-03 21:32:24');

-- ----------------------------
-- Table structure for IndustryCategories
-- ----------------------------
DROP TABLE IF EXISTS `IndustryCategories`;
CREATE TABLE `IndustryCategories` (
  `IndustryID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IndustryName` varchar(50) NOT NULL,
  `IndustryDescription` text,
  `IndustryStatus` smallint(6) NOT NULL DEFAULT '1',
  `ParentIndustryID` int(10) unsigned DEFAULT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`IndustryID`),
  KEY `FK_ParentIndustry_idx` (`ParentIndustryID`),
  KEY `IndustryName` (`IndustryName`),
  CONSTRAINT `FK_ParentIndustry` FOREIGN KEY (`ParentIndustryID`) REFERENCES `IndustryCategories` (`IndustryID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of IndustryCategories
-- ----------------------------
INSERT INTO `IndustryCategories` VALUES ('1', 'Not Applicable', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('2', 'Automotive', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('3', 'Business and Finance', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('8', 'Education', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('9', 'Employment and Career', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('10', 'Entertainment and Leisure', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('12', 'Gaming', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('14', 'Health and Fitness', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('16', 'Home and Garden', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('18', 'Men\'s Interest', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('21', 'Music', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('23', 'News', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('24', 'Parenting and Family', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('27', 'Real Estate', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('28', 'Reference', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('29', 'Food and Dining', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('31', 'Shopping', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('32', 'Social Networking', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('33', 'Sports', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('34', 'Technology', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('36', 'Travel', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');
INSERT INTO `IndustryCategories` VALUES ('38', 'Women\'s Interest', null, '1', null, '2013-01-29 17:10:00', '2014-01-29 17:14:25');

-- ----------------------------
-- Table structure for LinkedBannerToAdZone
-- ----------------------------
DROP TABLE IF EXISTS `LinkedBannerToAdZone`;
CREATE TABLE `LinkedBannerToAdZone` (
  `LinkedBannerToAdZoneID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `InsertionOrderLineItemID` int(11) unsigned NOT NULL,
  `PublisherAdZoneID` int(11) unsigned NOT NULL,
  `Weight` int(11) NOT NULL DEFAULT '5',
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`LinkedBannerToAdZoneID`),
  UNIQUE KEY `LinkedBannerToAdZone_UNIQUE` (`LinkedBannerToAdZoneID`),
  KEY `FK_Publisher_Zone_ID` (`InsertionOrderLineItemID`),
  KEY `FK_InsertionOrder_Banner_ID` (`PublisherAdZoneID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of LinkedBannerToAdZone
-- ----------------------------

-- ----------------------------
-- Table structure for LinkedBannerToAdZonePreview
-- ----------------------------
DROP TABLE IF EXISTS `LinkedBannerToAdZonePreview`;
CREATE TABLE `LinkedBannerToAdZonePreview` (
  `LinkedBannerToAdZonePreviewID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `InsertionOrderLineItemPreviewID` int(11) unsigned NOT NULL,
  `PublisherAdZoneID` int(11) unsigned NOT NULL,
  `Weight` int(11) NOT NULL DEFAULT '5',
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`LinkedBannerToAdZonePreviewID`),
  UNIQUE KEY `LinkedBannerToAdZone_UNIQUE` (`LinkedBannerToAdZonePreviewID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of LinkedBannerToAdZonePreview
-- ----------------------------

-- ----------------------------
-- Table structure for Maintenance
-- ----------------------------
DROP TABLE IF EXISTS `Maintenance`;
CREATE TABLE `Maintenance` (
  `TagName` char(100) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`TagName`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of Maintenance
-- ----------------------------
INSERT INTO `Maintenance` VALUES ('daily', '2014-09-06 16:05:03');
INSERT INTO `Maintenance` VALUES ('ten_minute', '2014-09-07 10:20:04');

-- ----------------------------
-- Table structure for PublisherAdIndustry
-- ----------------------------
DROP TABLE IF EXISTS `PublisherAdIndustry`;
CREATE TABLE `PublisherAdIndustry` (
  `PublisherAdIndustryID` bigint(20) unsigned NOT NULL,
  `IndustryID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`PublisherAdIndustryID`,`IndustryID`),
  KEY `FK_Types_idx` (`IndustryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of PublisherAdIndustry
-- ----------------------------

-- ----------------------------
-- Table structure for PublisherAdZone
-- ----------------------------
DROP TABLE IF EXISTS `PublisherAdZone`;
CREATE TABLE `PublisherAdZone` (
  `PublisherAdZoneID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `PublisherWebsiteID` int(10) unsigned NOT NULL,
  `PublisherAdZoneTypeID` int(11) NOT NULL,
  `ImpressionType` char(10) NOT NULL DEFAULT 'banner',
  `AdOwnerID` int(10) unsigned NOT NULL,
  `AdName` varchar(100) NOT NULL,
  `Description` char(255) DEFAULT NULL,
  `PassbackAdTag` text NOT NULL,
  `AdStatus` smallint(6) NOT NULL DEFAULT '0',
  `AutoApprove` smallint(6) NOT NULL DEFAULT '1',
  `AdTemplateID` int(10) unsigned DEFAULT NULL,
  `IsMobileFlag` smallint(6) DEFAULT NULL,
  `Width` int(11) NOT NULL,
  `Height` int(11) NOT NULL,
  `FloorPrice` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `TotalRequests` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Aggregated Statistics Field',
  `TotalImpressionsFilled` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Aggregated Statistics Field',
  `TotalAmount` decimal(20,2) unsigned NOT NULL DEFAULT '0.00' COMMENT 'Aggregated Statistics Field',
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PublisherAdZoneID`),
  UNIQUE KEY `UQ_WebAdName` (`PublisherWebsiteID`,`AdName`),
  KEY `FK_OwnerUser_idx` (`AdOwnerID`),
  KEY `FK_WebAdTemplates_idx` (`AdTemplateID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of PublisherAdZone
-- ----------------------------
INSERT INTO `PublisherAdZone` VALUES ('6', '4', '1', 'banner', '3', 'Leaderboard Top Banner', 'This leaderboard ad tag will be shown at the top of all the pages on the website.', '<script language=\"Javascript\">\r\nvar zflag_nid=\"2674\"; var zflag_cid=\"1\"; var zflag_sid=\"1\"; var zflag_width=\"728\"; var zflag_height=\"90\"; var zflag_sz=\"14\";\r\n</script>\r\n<script language=\"Javascript\" src=\"http://xp2.zedo.com/jsc/xp2/fo.js\"></script>', '1', '0', '3', '0', '728', '90', '0.10', '0', '0', '0.00', '2014-09-03 22:40:17', '2014-09-03 22:40:56');

-- ----------------------------
-- Table structure for PublisherAdZoneVideo
-- ----------------------------
DROP TABLE IF EXISTS `PublisherAdZoneVideo`;
CREATE TABLE `PublisherAdZoneVideo` (
  `PublisherAdZoneVideoID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `PublisherAdZoneID` int(11) NOT NULL,
  `MimesCommaSeparated` char(100) DEFAULT NULL,
  `MinDuration` int(10) unsigned DEFAULT NULL,
  `MaxDuration` int(10) unsigned DEFAULT NULL,
  `ApisSupportedCommaSeparated` char(100) DEFAULT NULL,
  `ProtocolsCommaSeparated` char(100) DEFAULT NULL,
  `DeliveryCommaSeparated` char(100) DEFAULT NULL,
  `PlaybackCommaSeparated` char(100) DEFAULT NULL,
  `StartDelay` char(5) DEFAULT NULL,
  `Linearity` int(10) DEFAULT NULL,
  `FoldPos` int(10) DEFAULT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PublisherAdZoneVideoID`),
  UNIQUE KEY `UQ_PublisherAdZone` (`PublisherAdZoneID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for PublisherAdZoneType
-- ----------------------------
DROP TABLE IF EXISTS `PublisherAdZoneType`;
CREATE TABLE `PublisherAdZoneType` (
  `PublisherAdZoneTypeID` int(11) unsigned NOT NULL,
  `Description` varchar(255) NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PublisherAdZoneTypeID`),
  UNIQUE KEY `PublisherAdZoneType_UNIQUE` (`PublisherAdZoneTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of PublisherAdZoneType
-- ----------------------------
INSERT INTO `PublisherAdZoneType` VALUES ('1', 'Any Remnant', '2014-08-17 12:16:21', '2014-08-17 12:16:21');
INSERT INTO `PublisherAdZoneType` VALUES ('2', 'In House Remnant', '2014-08-17 12:16:21', '2014-08-17 12:16:21');
INSERT INTO `PublisherAdZoneType` VALUES ('3', 'RTB Remnant', '2014-08-17 12:16:21', '2014-08-17 12:16:21');
INSERT INTO `PublisherAdZoneType` VALUES ('4', 'Contract', '2014-08-17 12:16:21', '2014-08-17 12:16:21');

-- ----------------------------
-- Table structure for PublisherInfo
-- ----------------------------
DROP TABLE IF EXISTS `PublisherInfo`;
CREATE TABLE `PublisherInfo` (
  `PublisherInfoID` int(11) NOT NULL AUTO_INCREMENT,
  `ParentID` int NOT NULL DEFAULT 0,
  `Name` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Domain` varchar(255) NOT NULL,
  `IABCategory` char(8) NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PublisherInfoID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of PublisherInfo
-- ----------------------------
INSERT INTO `PublisherInfo` VALUES ('3', '0', 'Blow Media Now', 'sergey.page@blowmedianow.com', 'blowmedianow.com', 'IAB19', '2014-09-03 21:25:33', '2014-09-03 21:25:33');

-- ----------------------------
-- Table structure for PublisherMarkup
-- ----------------------------
DROP TABLE IF EXISTS `PublisherMarkup`;
CREATE TABLE `PublisherMarkup` (
  `PublisherInfoID` int(11) NOT NULL,
  `MarkupRate` float NOT NULL,
  PRIMARY KEY (`PublisherInfoID`),
  UNIQUE KEY `PublisherInfoID` (`PublisherInfoID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of PublisherMarkup
-- ----------------------------

-- ----------------------------
-- Table structure for PublisherWebsite
-- ----------------------------
DROP TABLE IF EXISTS `PublisherWebsite`;
CREATE TABLE `PublisherWebsite` (
  `PublisherWebsiteID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `WebDomain` varchar(255) NOT NULL,
  `DomainOwnerID` int(10) unsigned NOT NULL,
  `AutoApprove` smallint(6) NOT NULL DEFAULT '1',
  `ApprovalFlag` smallint(6) NOT NULL DEFAULT '0',
  `IABCategory` char(8) DEFAULT NULL,
  `IABSubCategory` char(8) DEFAULT NULL,
  `Description` text,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PublisherWebsiteID`),
  UNIQUE KEY `WebDomain_UNIQUE` (`WebDomain`,`DomainOwnerID`),
  KEY `FK_Owner_User_ID` (`DomainOwnerID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of PublisherWebsite
-- ----------------------------
INSERT INTO `PublisherWebsite` VALUES ('4', 'blowmedianow.com', '3', '1', '1', 'IAB19', null, 'Blow Media Website', '2014-09-03 22:38:06', '2014-09-03 22:38:06');

-- ----------------------------
-- Table structure for PublisherWebsiteMarkup
-- ----------------------------
DROP TABLE IF EXISTS `PublisherWebsiteMarkup`;
CREATE TABLE `PublisherWebsiteMarkup` (
  `PublisherWebsiteID` int(11) NOT NULL,
  `MarkupRate` float NOT NULL,
  PRIMARY KEY (`PublisherWebsiteID`),
  UNIQUE KEY `PublisherWebsiteID` (`PublisherWebsiteID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for PublisherImpressionsNetworkLoss
-- ----------------------------
DROP TABLE IF EXISTS `PublisherImpressionsNetworkLoss`;
CREATE TABLE `PublisherImpressionsNetworkLoss` (
  `PublisherInfoID` int(11) NOT NULL,
  `CorrectionRate` float NOT NULL,
  PRIMARY KEY (`PublisherInfoID`),
  UNIQUE KEY `PublisherInfoID` (`PublisherInfoID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- ----------------------------
-- Table structure for PublisherWebsiteImpressionsNetworkLoss
-- ----------------------------
DROP TABLE IF EXISTS `PublisherWebsiteImpressionsNetworkLoss`;
CREATE TABLE `PublisherWebsiteImpressionsNetworkLoss` (
  `PublisherWebsiteID` int(11) NOT NULL,
  `CorrectionRate` float NOT NULL,
  PRIMARY KEY (`PublisherWebsiteID`),
  UNIQUE KEY `PublisherWebsiteID` (`PublisherWebsiteID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for rbac_permission
-- ----------------------------
DROP TABLE IF EXISTS `rbac_permission`;
CREATE TABLE `rbac_permission` (
  `perm_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `perm_name` varchar(32) DEFAULT NULL,
  `create_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`perm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of rbac_permission
-- ----------------------------

-- ----------------------------
-- Table structure for rbac_role
-- ----------------------------
DROP TABLE IF EXISTS `rbac_role`;
CREATE TABLE `rbac_role` (
  `role_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_role_id` int(11) unsigned DEFAULT NULL,
  `role_name` varchar(32) DEFAULT NULL,
  `create_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`),
  KEY `parent_role_id` (`parent_role_id`),
  CONSTRAINT `rbac_role_ibfk_1` FOREIGN KEY (`parent_role_id`) REFERENCES `rbac_role` (`role_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of rbac_role
-- ----------------------------
INSERT INTO `rbac_role` VALUES ('1', null, 'super_admin', '2013-11-30 14:48:08', '2013-11-30 14:48:08');
INSERT INTO `rbac_role` VALUES ('2', '1', 'domain_admin', '2013-11-30 14:48:08', '2013-11-30 14:48:08');
INSERT INTO `rbac_role` VALUES ('3', '2', 'member', '2013-11-30 14:48:08', '2013-11-30 14:48:08');
INSERT INTO `rbac_role` VALUES ('4', '3', 'guest', '2013-11-30 14:48:08', '2013-11-30 14:48:08');
INSERT INTO `rbac_role` VALUES ('5', '4', 'anonymous', '2013-11-30 14:48:08', '2013-11-30 14:48:08');

-- ----------------------------
-- Table structure for rbac_role_permission
-- ----------------------------
DROP TABLE IF EXISTS `rbac_role_permission`;
CREATE TABLE `rbac_role_permission` (
  `role_id` int(11) unsigned NOT NULL,
  `perm_id` int(11) unsigned NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`,`perm_id`),
  KEY `perm_id` (`perm_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `rbac_role_permission_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `rbac_role` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `rbac_role_permission_ibfk_2` FOREIGN KEY (`perm_id`) REFERENCES `rbac_permission` (`perm_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of rbac_role_permission
-- ----------------------------

-- ----------------------------
-- Table structure for ReportSubscription
-- ----------------------------
DROP TABLE IF EXISTS `ReportSubscription`;
CREATE TABLE `ReportSubscription` (
  `ReportSubscriptionID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `Status` tinyint(1) DEFAULT '0',
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ReportSubscriptionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ReportSubscription
-- ----------------------------

-- ----------------------------
-- Table structure for SellSidePartnerHourlyBids
-- ----------------------------
DROP TABLE IF EXISTS `SellSidePartnerHourlyBids`;
CREATE TABLE `SellSidePartnerHourlyBids` (
  `SellSidePartnerHourlyBidsID` int(11) NOT NULL AUTO_INCREMENT,
  `SellSidePartnerID` int(11) NOT NULL,
  `PublisherAdZoneID` int(11) NOT NULL,
  `MDYH` char(15) NOT NULL,
  `BidsWonCounter` bigint(20) NOT NULL,
  `BidsLostCounter` bigint(20) NOT NULL,
  `BidsErrorCounter` bigint(20) NOT NULL,
  `SpendTotalGross` float NOT NULL,
  `SpendTotalNet` float NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`SellSidePartnerHourlyBidsID`),
  UNIQUE KEY `SellSidePartnerToZoneBid_IDX` (`SellSidePartnerHourlyBidsID`,`SellSidePartnerID`,`PublisherAdZoneID`,`MDYH`) USING BTREE,
  UNIQUE KEY `SellSidePartnerHourlyBids_IDX` (`SellSidePartnerID`,`PublisherAdZoneID`,`MDYH`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of SellSidePartnerHourlyBids
-- ----------------------------

-- ----------------------------
-- Table structure for UserMarkupDemand
-- ----------------------------
DROP TABLE IF EXISTS `UserMarkupDemand`;
CREATE TABLE `UserMarkupDemand` (
  `UserID` int(11) NOT NULL,
  `MarkupRate` float NOT NULL,
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `UserID` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for PublisherHourlyBids
-- ----------------------------
DROP TABLE IF EXISTS `PublisherHourlyBids`;
CREATE TABLE `PublisherHourlyBids` (
  `PublisherHourlyBidsID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PublisherAdZoneID` int(10) unsigned NOT NULL,
  `MDYH` char(15) NOT NULL,
  `AuctionCounter` bigint(20) NOT NULL,
  `BidsWonCounter` bigint(20) NOT NULL,
  `BidsLostCounter` bigint(20) NOT NULL,
  `BidsErrorCounter` bigint(20) NOT NULL,
  `SpendTotalGross` float NOT NULL,
  `SpendTotalNet` float NOT NULL,
  `DateCreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PublisherHourlyBidsID`),
  UNIQUE KEY `PublisherHourlyBids_IDX` (`PublisherAdZoneID`,`MDYH`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of UserMarkupDemand
-- ----------------------------

-- ----------------------------
-- View structure for auth_userslogin
-- ----------------------------
DROP VIEW IF EXISTS `auth_userslogin`;
CREATE VIEW `auth_userslogin` AS select `auth_Users`.`user_id` AS `user_id`,`auth_Users`.`user_login` AS `user_login`,`auth_Users`.`user_email` AS `user_email`,`auth_Users`.`user_password` AS `user_password`,`auth_Users`.`user_password_salt` AS `user_password_salt`,`auth_Users`.`user_2factor_secret` AS `user_2factor_secret`,`auth_Users`.`user_fullname` AS `user_fullname`,`auth_Users`.`user_description` AS `user_description`,`auth_Users`.`user_enabled` AS `user_enabled`,`auth_Users`.`user_verified` AS `user_verified`,`auth_Users`.`user_agreement_accepted` AS `user_agreement_accepted`,`auth_Users`.`DemandCustomerInfoID` AS `DemandCustomerInfoID`,`auth_Users`.`PublisherInfoID` AS `PublisherInfoID`,`auth_Users`.`create_date` AS `create_date`,`auth_Users`.`update_date` AS `update_date`,`auth_Users`.`user_permission_cache` AS `user_permission_cache`,`auth_Users`.`user_role` AS `user_role`,`rbac_role`.`role_name` AS `user_role_name` from (`auth_Users` join `rbac_role` on((`auth_Users`.`user_role` = `rbac_role`.`role_id`))) where ((`auth_Users`.`user_enabled` > 0) and (`auth_Users`.`user_verified` > 0) and (`auth_Users`.`user_agreement_accepted` > 0)) ;

-- ----------------------------
-- View structure for auth_usersview
-- ----------------------------
DROP VIEW IF EXISTS `auth_usersview`;
CREATE VIEW `auth_usersview` AS select `auth_Users`.`user_id` AS `user_id`,`auth_Users`.`user_login` AS `user_login`,`auth_Users`.`user_email` AS `user_email`,`auth_Users`.`user_password` AS `user_password`,`auth_Users`.`user_password_salt` AS `user_password_salt`,`auth_Users`.`user_2factor_secret` AS `user_2factor_secret`,`auth_Users`.`user_fullname` AS `user_fullname`,`auth_Users`.`user_description` AS `user_description`,`auth_Users`.`user_enabled` AS `user_enabled`,`auth_Users`.`user_verified` AS `user_verified`,`auth_Users`.`DemandCustomerInfoID` AS `DemandCustomerInfoID`,`auth_Users`.`PublisherInfoID` AS `PublisherInfoID`,`auth_Users`.`create_date` AS `create_date`,`auth_Users`.`update_date` AS `update_date`,`auth_Users`.`user_permission_cache` AS `user_permission_cache`,`auth_Users`.`user_role` AS `user_role`,`rbac_role`.`role_name` AS `user_role_name` from (`auth_Users` join `rbac_role` on((`auth_Users`.`user_role` = `rbac_role`.`role_id`))) ;

-- ----------------------------
-- View structure for BidTotalsRollup
-- ----------------------------
DROP VIEW IF EXISTS `BidTotalsRollup`;
CREATE VIEW `BidTotalsRollup` AS select `BuySideHourlyBidsCounter`.`InsertionOrderLineItemID` AS `InsertionOrderLineItemID`,sum(`BuySideHourlyBidsCounter`.`BidsCounter`) AS `TotalBids` from `BuySideHourlyBidsCounter` group by `BuySideHourlyBidsCounter`.`InsertionOrderLineItemID` ;

-- ----------------------------
-- View structure for buySideHourlyBidsAvarage
-- ----------------------------
DROP VIEW IF EXISTS `buySideHourlyBidsAvarage`;
CREATE VIEW `buySideHourlyBidsAvarage` AS select avg(`BuySideHourlyBidsCounter`.`BidsCounter`) AS `avg_bids`,sum(`BuySideHourlyBidsCounter`.`BidsCounter`) AS `total_bids`,`InsertionOrderLineItem`.`Name` AS `banner_name`,`InsertionOrder`.`Name` AS `Name`,`auth_Users`.`user_login` AS `user_login` from (((`BuySideHourlyBidsCounter` join `InsertionOrderLineItem` on((`BuySideHourlyBidsCounter`.`InsertionOrderLineItemID` = `InsertionOrderLineItem`.`InsertionOrderLineItemID`))) join `InsertionOrder` on((`InsertionOrderLineItem`.`InsertionOrderID` = `InsertionOrder`.`InsertionOrderID`))) join `auth_Users` on((`auth_Users`.`user_id` = `InsertionOrderLineItem`.`UserID`))) group by `BuySideHourlyBidsCounter`.`InsertionOrderLineItemID` order by `BuySideHourlyBidsCounter`.`InsertionOrderLineItemID` ;

-- ----------------------------
-- View structure for buySideHourlyBidsAvarageAdmin
-- ----------------------------
DROP VIEW IF EXISTS `buySideHourlyBidsAvarageAdmin`;
CREATE VIEW `buySideHourlyBidsAvarageAdmin` AS select `BuySideHourlyBidsCounter`.`BuySidePartnerID` AS `BuySidePartnerID`,avg(`BuySideHourlyBidsCounter`.`BidsCounter`) AS `avg_bids`,sum(`BuySideHourlyBidsCounter`.`BidsCounter`) AS `total_bids`,`InsertionOrderLineItem`.`Name` AS `banner_name`,`InsertionOrder`.`Name` AS `Name`,`auth_Users`.`user_login` AS `user_login` from (((`BuySideHourlyBidsCounter` join `InsertionOrderLineItem` on((`BuySideHourlyBidsCounter`.`InsertionOrderLineItemID` = `InsertionOrderLineItem`.`InsertionOrderLineItemID`))) join `InsertionOrder` on((`InsertionOrderLineItem`.`InsertionOrderID` = `InsertionOrder`.`InsertionOrderID`))) join `auth_Users` on((`auth_Users`.`user_id` = `InsertionOrderLineItem`.`UserID`))) group by `BuySideHourlyBidsCounter`.`InsertionOrderLineItemID`,`BuySideHourlyBidsCounter`.`BuySidePartnerID` order by `BuySideHourlyBidsCounter`.`InsertionOrderLineItemID` ;

-- ----------------------------
-- View structure for buySideHourlyBidsPerTime
-- ----------------------------
DROP VIEW IF EXISTS `buySideHourlyBidsPerTime`;
CREATE VIEW `buySideHourlyBidsPerTime` AS select `BuySideHourlyBidsCounter`.`BuySidePartnerID` AS `BuySidePartnerID`,`BuySideHourlyBidsCounter`.`MDYH` AS `MDYH`,`BuySideHourlyBidsCounter`.`BidsCounter` AS `BidsCounter`,`BuySideHourlyBidsCounter`.`DateCreated` AS `DateCreated`,`BuySideHourlyBidsCounter`.`DateUpdated` AS `DateUpdated`,`InsertionOrder`.`Name` AS `Name` from ((`BuySideHourlyBidsCounter` join `InsertionOrderLineItem` on((`BuySideHourlyBidsCounter`.`InsertionOrderLineItemID` = `InsertionOrderLineItem`.`InsertionOrderLineItemID`))) join `InsertionOrder` on((`InsertionOrderLineItem`.`InsertionOrderID` = `InsertionOrder`.`InsertionOrderID`))) order by `BuySideHourlyBidsCounter`.`InsertionOrderLineItemID` ;

-- ----------------------------
-- View structure for ImpressionAndSpendTotalsRollup
-- ----------------------------
DROP VIEW IF EXISTS `ImpressionAndSpendTotalsRollup`;
CREATE VIEW `ImpressionAndSpendTotalsRollup` AS select `BuySideHourlyImpressionsCounterCurrentSpend`.`InsertionOrderLineItemID` AS `InsertionOrderLineItemID`,sum(`BuySideHourlyImpressionsCounterCurrentSpend`.`CurrentSpendGross`) AS `TotalSpendGross`,sum(`BuySideHourlyImpressionsCounterCurrentSpend`.`CurrentSpendNet`) AS `TotalSpendNet`,sum(`BuySideHourlyImpressionsCounterCurrentSpend`.`ImpressionsCounter`) AS `TotalImpressions` from `BuySideHourlyImpressionsCounterCurrentSpend` group by `BuySideHourlyImpressionsCounterCurrentSpend`.`InsertionOrderLineItemID` ;

-- ----------------------------
-- View structure for impressionsCurrentSpendPerTime
-- ----------------------------
DROP VIEW IF EXISTS `impressionsCurrentSpendPerTime`;
CREATE VIEW `impressionsCurrentSpendPerTime` AS select `BuySideHourlyImpressionsCounterCurrentSpend`.`BuySidePartnerID` AS `BuySidePartnerID`,`BuySideHourlyImpressionsCounterCurrentSpend`.`MDYH` AS `MDYH`,`BuySideHourlyImpressionsCounterCurrentSpend`.`ImpressionsCounter` AS `ImpressionsCounter`,`BuySideHourlyImpressionsCounterCurrentSpend`.`CurrentSpendGross` AS `CurrentSpendGross`,`BuySideHourlyImpressionsCounterCurrentSpend`.`CurrentSpendNet` AS `CurrentSpendNet`,round((`BuySideHourlyImpressionsCounterCurrentSpend`.`CurrentSpendNet` / `BuySideHourlyImpressionsCounterCurrentSpend`.`ImpressionsCounter`),7) AS `AverageBidCurrentSpendNet`,round((`BuySideHourlyImpressionsCounterCurrentSpend`.`CurrentSpendGross` / `BuySideHourlyImpressionsCounterCurrentSpend`.`ImpressionsCounter`),7) AS `AverageBidCurrentSpendGross`,`BuySideHourlyImpressionsCounterCurrentSpend`.`DateCreated` AS `DateCreated`,`BuySideHourlyImpressionsCounterCurrentSpend`.`DateUpdated` AS `DateUpdated`,`InsertionOrder`.`Name` AS `Name` from ((`BuySideHourlyImpressionsCounterCurrentSpend` join `InsertionOrderLineItem` on((`BuySideHourlyImpressionsCounterCurrentSpend`.`InsertionOrderLineItemID` = `InsertionOrderLineItem`.`InsertionOrderLineItemID`))) join `InsertionOrder` on((`InsertionOrderLineItem`.`InsertionOrderID` = `InsertionOrder`.`InsertionOrderID`))) order by `BuySideHourlyImpressionsCounterCurrentSpend`.`BuySidePartnerID` ;

-- ----------------------------
-- View structure for sellSidePartnerHourlyBidsPerTime
-- ----------------------------
DROP VIEW IF EXISTS `sellSidePartnerHourlyBidsPerTime`;
CREATE VIEW `sellSidePartnerHourlyBidsPerTime` AS select `SellSidePartnerHourlyBids`.`SellSidePartnerID` AS `SellSidePartnerID`,`SellSidePartnerHourlyBids`.`MDYH` AS `MDYH`,`SellSidePartnerHourlyBids`.`BidsWonCounter` AS `BidsWonCounter`,`SellSidePartnerHourlyBids`.`BidsLostCounter` AS `BidsLostCounter`,`SellSidePartnerHourlyBids`.`BidsErrorCounter` AS `BidsErrorCounter`,`SellSidePartnerHourlyBids`.`SpendTotalNet` AS `SpendTotalNet`,round((`SellSidePartnerHourlyBids`.`SpendTotalNet` / `SellSidePartnerHourlyBids`.`BidsWonCounter`),7) AS `AverageBidNet`,round((`SellSidePartnerHourlyBids`.`SpendTotalGross` / `SellSidePartnerHourlyBids`.`BidsWonCounter`),7) AS `AverageBidGross`,`SellSidePartnerHourlyBids`.`DateCreated` AS `DateCreated`,`SellSidePartnerHourlyBids`.`DateUpdated` AS `DateUpdated`,`PublisherAdZone`.`AdName` AS `AdName` from (`SellSidePartnerHourlyBids` join `PublisherAdZone` on((`SellSidePartnerHourlyBids`.`PublisherAdZoneID` = `PublisherAdZone`.`PublisherAdZoneID`))) order by `SellSidePartnerHourlyBids`.`SellSidePartnerID` ;

-- ----------------------------
-- View structure for userImpressionsSpend
-- ----------------------------
DROP VIEW IF EXISTS `userImpressionsSpend`;
CREATE VIEW `userImpressionsSpend` AS select round(sum(`BuySideHourlyImpressionsCounterCurrentSpend`.`CurrentSpendGross`),7) AS `TotalSpendGross`,round(sum(`BuySideHourlyImpressionsCounterCurrentSpend`.`CurrentSpendNet`),7) AS `TotalSpendNet`,`InsertionOrder`.`Name` AS `Name`,`auth_Users`.`user_login` AS `user_login` from (((`BuySideHourlyImpressionsCounterCurrentSpend` join `InsertionOrderLineItem` on((`BuySideHourlyImpressionsCounterCurrentSpend`.`InsertionOrderLineItemID` = `InsertionOrderLineItem`.`InsertionOrderLineItemID`))) join `InsertionOrder` on((`InsertionOrderLineItem`.`InsertionOrderID` = `InsertionOrder`.`InsertionOrderID`))) join `auth_Users` on((`auth_Users`.`user_id` = `InsertionOrderLineItem`.`UserID`))) group by `BuySideHourlyImpressionsCounterCurrentSpend`.`InsertionOrderLineItemID` order by `auth_Users`.`user_login` ;

-- ----------------------------
-- View structure for userImpressionsSpendAdmin
-- ----------------------------
DROP VIEW IF EXISTS `userImpressionsSpendAdmin`;
CREATE VIEW `userImpressionsSpendAdmin` AS select `BuySideHourlyImpressionsCounterCurrentSpend`.`BuySidePartnerID` AS `BuySidePartnerID`,round(sum(`BuySideHourlyImpressionsCounterCurrentSpend`.`CurrentSpendGross`),7) AS `TotalSpendGross`,round(sum(`BuySideHourlyImpressionsCounterCurrentSpend`.`CurrentSpendNet`),7) AS `TotalSpendNet`,`InsertionOrder`.`Name` AS `Name`,`auth_Users`.`user_login` AS `user_login` from (((`BuySideHourlyImpressionsCounterCurrentSpend` join `InsertionOrderLineItem` on((`BuySideHourlyImpressionsCounterCurrentSpend`.`InsertionOrderLineItemID` = `InsertionOrderLineItem`.`InsertionOrderLineItemID`))) join `InsertionOrder` on((`InsertionOrderLineItem`.`InsertionOrderID` = `InsertionOrder`.`InsertionOrderID`))) join `auth_Users` on((`auth_Users`.`user_id` = `InsertionOrderLineItem`.`UserID`))) group by `BuySideHourlyImpressionsCounterCurrentSpend`.`BuySidePartnerID`,`BuySideHourlyImpressionsCounterCurrentSpend`.`InsertionOrderLineItemID` order by `auth_Users`.`user_login` ;

-- ----------------------------
-- View structure for PublisherImpressionsAndSpendHourly
-- ----------------------------
DROP VIEW IF EXISTS `PublisherImpressionsAndSpendHourly`;
CREATE VIEW `PublisherImpressionsAndSpendHourly` AS select `phb`.`MDYH`, `phb`.`PublisherAdZoneID`, `pi`.`Name` as PublisherName, pad.`AdOwnerID` as PublisherInfoID, `pad`.`AdName`, `phb`.`AuctionCounter` as `Requests`, `phb`.`BidsWonCounter` as `Impressions`, round(((`phb`.`SpendTotalNet` / `phb`.`BidsWonCounter`) * 1000),7) AS `eCPM`, round(((`phb`.`SpendTotalGross` / `phb`.`BidsWonCounter`) * 1000),7) AS `GrossECPM`, concat(round((`phb`.`BidsWonCounter` / `phb`.`AuctionCounter`) * 100, 2), '%') as `FillRate`, round(`phb`.`SpendTotalNet`, 7) as `Revenue`, round(`phb`.`SpendTotalGross`, 7) as `GrossRevenue`, `phb`.`DateCreated` from `PublisherHourlyBids` phb inner join `PublisherAdZone` pad on phb.`PublisherAdZoneID` = pad.`PublisherAdZoneID` inner join `PublisherInfo` pi on pad.`AdOwnerID` = pi.`PublisherInfoID` ;

-- ----------------------------
-- View structure for PublisherImpressionsAndSpendHourlyTotals
-- ----------------------------
DROP VIEW IF EXISTS `PublisherImpressionsAndSpendHourlyTotals`;
CREATE VIEW `PublisherImpressionsAndSpendHourlyTotals` AS select PublisherAdZoneID, PublisherName, PublisherInfoID, SUM(Requests) as TotalRequests, SUM(Impressions) as TotalImpressions, SUM(Revenue) as TotalRevenue from PublisherImpressionsAndSpendHourly group by PublisherAdZoneID order by PublisherAdZoneID ;

-- ----------------------------
-- View structure for DemandImpressionsAndSpendHourlyPre
-- ----------------------------
DROP VIEW IF EXISTS `DemandImpressionsAndSpendHourlyPre`;
CREATE VIEW `DemandImpressionsAndSpendHourlyPre` AS select `bshiccs`.`MDYH`, `bshiccs`.`InsertionOrderLineItemID`, `dci`.`Name` as DemandCustomerName, `dci`.`DemandCustomerInfoID` as DemandCustomerInfoID, `acb`.`Name` as BannerName, sum(`bshiccs`.`ImpressionsCounter`) as `Impressions`, round(sum(`bshiccs`.`CurrentSpendNet`),7) as `Cost`, round(sum(`bshiccs`.`CurrentSpendGross`),7) as `GrossCost`, round(((sum(`bshiccs`.`CurrentSpendNet`) / sum(`bshiccs`.`ImpressionsCounter`)) * 1000),7) AS `CPM`, round(((sum(`bshiccs`.`CurrentSpendGross`) / sum(`bshiccs`.`ImpressionsCounter`)) * 1000),7) AS `GrossCPM`, `bshiccs`.`DateCreated` from `BuySideHourlyImpressionsCounterCurrentSpend` bshiccs inner join `InsertionOrderLineItem` acb on bshiccs.`InsertionOrderLineItemID` = acb.`InsertionOrderLineItemID` inner join `auth_Users` au on au.`user_id` = acb.`UserID` inner join `DemandCustomerInfo` dci on au.`DemandCustomerInfoID` = dci.`DemandCustomerInfoID` group by `bshiccs`.`InsertionOrderLineItemID`, `bshiccs`.`MDYH` ;

-- ----------------------------
-- View structure for DemandImpressionsAndSpendHourly
-- ----------------------------
DROP VIEW IF EXISTS `DemandImpressionsAndSpendHourly`;
CREATE VIEW `DemandImpressionsAndSpendHourly` AS select diashp.MDYH, diashp.InsertionOrderLineItemID, diashp.DemandCustomerName, diashp.DemandCustomerInfoID, diashp.BannerName, group_concat(distinct `bshibt`.`PublisherTLD` separator ', ') as PublisherTLDs, diashp.Impressions, diashp.Cost, diashp.GrossCost, diashp.CPM, diashp.GrossCPM, diashp.DateCreated from DemandImpressionsAndSpendHourlyPre diashp left outer join `BuySideHourlyImpressionsByTLD` bshibt on diashp.`InsertionOrderLineItemID` = `bshibt`.`InsertionOrderLineItemID` and diashp.`MDYH` = `bshibt`.`MDYH` group by `diashp`.`InsertionOrderLineItemID`, `diashp`.`MDYH` ;

-- ----------------------------
-- Function structure for MD5_SPLIT_SALT
-- ----------------------------
DROP FUNCTION IF EXISTS `MD5_SPLIT_SALT`;
DELIMITER ;;
CREATE FUNCTION `MD5_SPLIT_SALT`(`password` VARCHAR(255), `salt` char(10)) RETURNS varchar(32) CHARSET latin1
    DETERMINISTIC
BEGIN

/* This function puts the salt in the middle of the plain text password before it feeds it to the MD5 hash.
 * The result is a more difficult to crack password database, as the cracker will also need to know
 * the plain text password string length to know where to put the salt to result in the MD5 hash.
 * Example: password --> passSALTword --> MD5(passSALTword)
 * Different passwords with different lengths will have their salt placed in a different place before it is
 * sent to the MD5 hash algorithm.
 */

DECLARE length INT;
DECLARE firsthalf INT;
DECLARE secondhalf INT;
DECLARE result VARCHAR(32);

SET length = LENGTH(`password`);
SET firsthalf = FLOOR(length/2);
SET secondhalf = length - firsthalf; /* YOU MUST subtract and not recalculate to avoid rounding errors! */
SET result =
			MD5(
				CONCAT(
					SUBSTRING(`password`, 1, firsthalf) ,
					CASE WHEN (`salt` IS NULL) THEN '' ELSE `salt` END, /* If salt is NULL, provide empty string for no salt. */
					SUBSTRING(`password`,secondhalf)
				)
			);

/* One liner:
RETURN MD5(CONCAT(SUBSTRING(`password`, 1, FLOOR(LENGTH(`password`)/2)),CASE WHEN (`salt` IS NULL) THEN '' ELSE `salt` END,SUBSTRING(`password`,(LENGTH(`password`) - FLOOR(LENGTH(`password`)/2)))));
*/

RETURN result;
END
;;
DELIMITER ;
