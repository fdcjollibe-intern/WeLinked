# ************************************************************
# Sequel Ace SQL dump
# Version 20096
#
# https://sequel-ace.com/
# https://github.com/Sequel-Ace/Sequel-Ace
#
# Host: 127.0.0.1 (MySQL 8.0.45)
# Database: welinked_db
# Generation Time: 2026-02-23 06:11:55 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE='NO_AUTO_VALUE_ON_ZERO', SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table activities
# ------------------------------------------------------------

DROP TABLE IF EXISTS `activities`;

CREATE TABLE `activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT 'User who will see this activity',
  `actor_id` bigint unsigned NOT NULL COMMENT 'User who performed the action',
  `activity_type` enum('follow','reaction','comment','post') COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_type` enum('user','post','comment') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_id` bigint unsigned DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_activities_actor` (`actor_id`),
  KEY `idx_activities_user` (`user_id`,`created_at` DESC),
  KEY `idx_activities_is_read` (`user_id`,`is_read`),
  KEY `idx_activities_type` (`activity_type`),
  CONSTRAINT `fk_activities_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_activities_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table birthday_messages
# ------------------------------------------------------------

DROP TABLE IF EXISTS `birthday_messages`;

CREATE TABLE `birthday_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` bigint unsigned NOT NULL,
  `recipient_id` bigint unsigned NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_birthday_messages_recipient` (`recipient_id`,`deleted_at`),
  KEY `idx_birthday_messages_sender` (`sender_id`,`deleted_at`),
  KEY `idx_birthday_messages_created` (`created_at` DESC),
  CONSTRAINT `fk_birthday_messages_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_birthday_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `birthday_messages` WRITE;
/*!40000 ALTER TABLE `birthday_messages` DISABLE KEYS */;

INSERT INTO `birthday_messages` (`id`, `sender_id`, `recipient_id`, `message`, `is_read`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	(10,1,2,'wassup yo bithc',1,'2026-02-19 09:31:49','2026-02-19 09:32:07',NULL),
	(11,2,1,'waduuppps',1,'2026-02-20 04:00:02','2026-02-20 04:01:24',NULL),
	(12,1,2,'ddfgdhdfgfd',1,'2026-02-19 08:08:31','2026-02-23 01:47:25',NULL),
	(13,53,1,'hello happy borthday',1,'2026-02-20 08:20:46','2026-02-20 08:21:41',NULL);

/*!40000 ALTER TABLE `birthday_messages` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table comment_attachments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `comment_attachments`;

CREATE TABLE `comment_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint unsigned NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` enum('image','video') COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint unsigned NOT NULL COMMENT 'Size in bytes (max 250MB = 262144000 bytes)',
  `upload_status` enum('uploading','completed','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'uploading',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_comment_attachment` (`comment_id`),
  KEY `idx_comment_attachments_comment` (`comment_id`),
  KEY `idx_comment_attachments_status` (`upload_status`),
  CONSTRAINT `fk_comment_attachments_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table comments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `comments`;

CREATE TABLE `comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `content_text` text COLLATE utf8mb4_unicode_ci,
  `content_image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_comments_post_id` (`post_id`),
  KEY `idx_comments_user_id` (`user_id`),
  KEY `idx_comments_created_at` (`created_at` DESC),
  KEY `idx_comments_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content_text`, `content_image_path`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	(43,135,1,'asd',NULL,'2026-02-20 07:56:19','2026-02-20 07:56:19',NULL),
	(44,328,1,'adwesf',NULL,'2026-02-20 07:57:10','2026-02-20 07:57:15','2026-02-20 07:57:15'),
	(45,329,1,'','https://res.cloudinary.com/dn6rffrwk/image/upload/v1771574651/posts/user_1_c3a839cb4ad92c5eed8c8bd0.jpg','2026-02-20 08:04:12','2026-02-20 08:04:12',NULL),
	(46,329,1,'asdasd',NULL,'2026-02-20 08:04:16','2026-02-20 08:04:16',NULL),
	(47,331,1,'hello',NULL,'2026-02-19 08:16:03','2026-02-19 08:16:03',NULL),
	(48,331,53,'hi',NULL,'2026-02-19 08:16:21','2026-02-19 08:16:21',NULL),
	(49,331,1,'','/uploads/attachments/comments/att_6996c6f43697a7.57146598.jpg','2026-02-19 08:16:52','2026-02-19 08:16:52',NULL),
	(50,332,53,'sdsadasdas',NULL,'2026-02-20 09:41:07','2026-02-20 09:41:14',NULL);

/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table friendships
# ------------------------------------------------------------

DROP TABLE IF EXISTS `friendships`;

CREATE TABLE `friendships` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `follower_id` bigint unsigned NOT NULL COMMENT 'User who follows',
  `following_id` bigint unsigned NOT NULL COMMENT 'User being followed',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_follower_following` (`follower_id`,`following_id`),
  KEY `idx_friendships_follower` (`follower_id`),
  KEY `idx_friendships_following` (`following_id`),
  KEY `idx_friendships_follower_created` (`follower_id`,`created_at` DESC),
  CONSTRAINT `fk_friendships_follower` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_friendships_following` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `friendships` WRITE;
/*!40000 ALTER TABLE `friendships` DISABLE KEYS */;

INSERT INTO `friendships` (`id`, `follower_id`, `following_id`, `created_at`)
VALUES
	(22,2,1,'2026-02-18 02:19:55'),
	(24,2,3,'2026-02-18 07:46:55'),
	(25,2,4,'2026-02-18 07:46:57'),
	(27,2,24,'2026-02-19 09:33:17'),
	(28,2,26,'2026-02-19 09:33:17'),
	(29,2,28,'2026-02-19 09:33:18'),
	(31,2,25,'2026-02-19 09:33:19'),
	(32,1,24,'2026-02-20 03:42:09'),
	(33,1,26,'2026-02-20 03:42:10'),
	(34,1,2,'2026-02-20 07:55:38'),
	(36,1,53,'2026-02-19 08:14:23'),
	(37,53,1,'2026-02-20 08:20:28');

/*!40000 ALTER TABLE `friendships` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table likes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `likes`;

CREATE TABLE `likes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `target_type` enum('post','comment') COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_id` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_target` (`user_id`,`target_type`,`target_id`),
  KEY `idx_likes_user_id` (`user_id`),
  KEY `idx_likes_target` (`target_type`,`target_id`),
  CONSTRAINT `fk_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table mentions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mentions`;

CREATE TABLE `mentions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint unsigned NOT NULL,
  `mentioned_user_id` bigint unsigned NOT NULL COMMENT 'User who was mentioned',
  `mentioned_by_user_id` bigint unsigned NOT NULL COMMENT 'User who created the mention',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_post_mentioned_user` (`post_id`,`mentioned_user_id`),
  KEY `fk_mentions_mentioned_by_user` (`mentioned_by_user_id`),
  KEY `idx_mentions_mentioned_user` (`mentioned_user_id`),
  KEY `idx_mentions_post` (`post_id`),
  CONSTRAINT `fk_mentions_mentioned_by_user` FOREIGN KEY (`mentioned_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mentions_mentioned_user` FOREIGN KEY (`mentioned_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mentions_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `mentions` WRITE;
/*!40000 ALTER TABLE `mentions` DISABLE KEYS */;

INSERT INTO `mentions` (`id`, `post_id`, `mentioned_user_id`, `mentioned_by_user_id`, `created_at`)
VALUES
	(18,332,53,1,'2026-02-20 08:24:22');

/*!40000 ALTER TABLE `mentions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table notifications
# ------------------------------------------------------------

DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT 'User receiving the notification',
  `actor_id` bigint unsigned DEFAULT NULL COMMENT 'User who triggered the notification',
  `type` enum('mention','reaction','comment','follow') COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_type` enum('post','comment','user') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_id` bigint unsigned DEFAULT NULL COMMENT 'ID of the target (post_id, comment_id, user_id)',
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user_read` (`user_id`,`is_read`,`created_at` DESC),
  KEY `idx_notifications_type` (`type`),
  KEY `idx_notifications_target` (`target_type`,`target_id`),
  KEY `idx_notifications_actor_target` (`actor_id`,`type`,`target_type`,`target_id`),
  CONSTRAINT `fk_notifications_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;

INSERT INTO `notifications` (`id`, `user_id`, `actor_id`, `type`, `target_type`, `target_id`, `message`, `is_read`, `created_at`)
VALUES
	(58,29,2,'reaction','post',122,'shanegambs reacted 😡 to your post',0,'2026-02-20 03:37:13'),
	(59,26,1,'reaction','post',135,'jdabsofficial reacted 😂 to your post',0,'2026-02-20 07:56:15'),
	(60,26,1,'comment','post',135,'jdabsofficial commented on your post',0,'2026-02-20 07:56:19'),
	(61,1,2,'reaction','post',328,'shanegambs reacted 🥰 to your post',1,'2026-02-20 07:56:54'),
	(62,25,1,'reaction','post',110,'jdabsofficial reacted 😲 to your post',0,'2026-02-20 08:05:29'),
	(63,53,1,'comment','post',331,'jdabsofficial commented on your post',1,'2026-02-19 08:16:03'),
	(64,1,53,'reaction','comment',47,'sirdong reacted 😢 to your comment',0,'2026-02-19 08:16:24'),
	(65,53,1,'comment','post',331,'jdabsofficial commented on your post',0,'2026-02-19 08:16:52'),
	(66,53,1,'mention','post',332,'jdabsofficial mentioned you in a post',1,'2026-02-20 08:24:22'),
	(67,1,53,'reaction','comment',45,'sirdong reacted 😲 to your comment',0,'2026-02-20 09:40:42'),
	(68,1,53,'comment','post',332,'sirdong commented on your post',0,'2026-02-20 09:41:07'),
	(69,27,2,'reaction','post',112,'shanegambs reacted 😲 to your post',0,'2026-02-23 01:47:46');

/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table post_attachments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `post_attachments`;

CREATE TABLE `post_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint unsigned NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` enum('image','video') COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint unsigned NOT NULL COMMENT 'Size in bytes (max 250MB = 262144000 bytes)',
  `display_order` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'Order in gallery (0, 1, 2...)',
  `upload_status` enum('uploading','completed','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'uploading',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_post_attachments_post` (`post_id`),
  KEY `idx_post_attachments_order` (`post_id`,`display_order`),
  KEY `idx_post_attachments_status` (`upload_status`),
  CONSTRAINT `fk_post_attachments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `post_attachments` WRITE;
/*!40000 ALTER TABLE `post_attachments` DISABLE KEYS */;

INSERT INTO `post_attachments` (`id`, `post_id`, `file_path`, `file_type`, `file_size`, `display_order`, `upload_status`, `created_at`)
VALUES
	(91,329,'https://res.cloudinary.com/dn6rffrwk/image/upload/v1771574625/posts/user_1_0fc2836be52581512c73d343.jpg','image',31722,0,'completed','2026-02-20 08:03:54'),
	(92,329,'https://res.cloudinary.com/dn6rffrwk/image/upload/v1771574627/posts/user_1_4df2756dcdc176e53729c341.jpg','image',13809,1,'completed','2026-02-20 08:03:54'),
	(93,329,'https://res.cloudinary.com/dn6rffrwk/image/upload/v1771574629/posts/user_1_8356f3f11cd0b4cac4b67a85.jpg','image',31387,2,'completed','2026-02-20 08:03:54'),
	(94,329,'https://res.cloudinary.com/dn6rffrwk/image/upload/v1771574631/posts/user_1_37ff9ed4c04e22563a2da617.jpg','image',14917,3,'completed','2026-02-20 08:03:54'),
	(95,330,'/uploads/attachments/posts/att_6996c5e3ad51e2.38846119.jpg','image',13809,0,'completed','2026-02-19 08:12:23'),
	(96,330,'/uploads/attachments/posts/att_6996c5e4d05885.73943538.jpg','image',14917,1,'completed','2026-02-19 08:12:23'),
	(97,330,'/uploads/attachments/posts/att_6996c5e611e984.21692536.jpg','image',31722,2,'completed','2026-02-19 08:12:23'),
	(98,331,'/uploads/attachments/posts/att_6996c6989c8372.80192319.mp4','video',7016888,0,'completed','2026-02-19 08:15:23'),
	(99,334,'https://res.cloudinary.com/dn6rffrwk/image/upload/v1771813634/posts/user_53_bcea2c0f8c840477ebafdaad.png','image',7261,0,'completed','2026-02-23 02:27:16'),
	(100,335,'https://res.cloudinary.com/dn6rffrwk/video/upload/v1771813657/posts/user_53_c387209fc1ea5817afedb806.mp4','video',2784710,0,'completed','2026-02-23 02:27:49'),
	(101,336,'https://res.cloudinary.com/dn6rffrwk/video/upload/v1771813694/posts/user_53_4dffee77eb16a3648728dc8b.mp4','video',7196967,0,'completed','2026-02-23 02:28:46');

/*!40000 ALTER TABLE `post_attachments` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table posts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `posts`;

CREATE TABLE `posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `content_text` text COLLATE utf8mb4_unicode_ci,
  `content_image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `is_reel` tinyint(1) DEFAULT NULL COMMENT 'Whether this post should be displayed as a reel (true for posts with single video)',
  PRIMARY KEY (`id`),
  KEY `idx_posts_user_id` (`user_id`),
  KEY `idx_posts_created_at` (`created_at` DESC),
  KEY `idx_posts_deleted_at` (`deleted_at`),
  KEY `idx_posts_user_created` (`user_id`,`created_at` DESC),
  KEY `idx_posts_deleted_created` (`deleted_at`,`created_at` DESC),
  CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=337 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;

INSERT INTO `posts` (`id`, `user_id`, `content_text`, `content_image_path`, `location`, `created_at`, `updated_at`, `deleted_at`, `is_reel`)
VALUES
	(109,24,'Spent the whole morning optimizing a query that was taking 1.8s. Added a composite index and now it runs in 40ms. Performance tuning is addictive when you see those numbers drop 🚀',NULL,NULL,'2026-02-19 09:32:28','2026-02-19 10:03:16',NULL,NULL),
	(110,25,'UI isn’t about making things pretty. It’s about removing friction. If a user has to think about where to click, we already failed.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(111,26,'I used to underestimate database constraints. Now I realize foreign keys are the only thing standing between structure and chaos.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(112,27,'Redesigned a dashboard today. Same data, better hierarchy, cleaner spacing. The difference in clarity is insane ✨',NULL,NULL,'2026-02-19 09:32:28','2026-02-19 10:03:18',NULL,NULL),
	(113,28,'Dockerized the entire stack and suddenly onboarding new devs became 10x easier. Infrastructure decisions compound over time.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(114,29,'Debugged a bug for 3 hours only to realize it was a missing null check. Humbling experience 😂',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(115,30,'WebSockets are powerful, but scaling them requires serious thought about memory and connection management.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(116,31,'Micro-interactions are subtle, but they create emotional feedback. A 200ms animation can change how a product feels 💡',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(117,24,'Refactoring legacy code feels like walking into someone else’s brain from 2017. Sometimes you just close the file and breathe.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(118,25,'Dark mode isn’t just aesthetic. It reduces eye strain during long dev sessions. Small details matter 🌙',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(119,26,'Normalized the schema properly today and suddenly half of my weird bugs disappeared. Data integrity saves lives.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(120,27,'Design systems aren’t about control. They’re about consistency. Consistency builds trust with users.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(121,28,'Set up CI/CD with automated tests and now deployments feel boring. That’s how it should be.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(122,29,'Frontend performance is invisible when it works and painfully obvious when it doesn’t.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(123,30,'Polling works… until traffic grows. Then you start questioning your architectural decisions.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(124,31,'Animation without intention is noise. Movement should guide attention, not distract from it.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(125,24,'Security tip: never trust user input. Validate. Sanitize. Escape. Repeat. Attackers only need one mistake 🔐',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(126,25,'Minimal UI is harder than complex UI. You have fewer elements, so every decision carries more weight.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(127,26,'Reading MySQL EXPLAIN output is like learning a new language. Once you get it, query optimization becomes a game.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(128,27,'Good UX reduces support tickets. Great UX makes users feel smart.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(129,28,'Kubernetes gives you power, but it also gives you responsibility. Complexity should be justified.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(130,29,'Edge cases are where production bugs are born. Always test the weird scenarios.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(131,30,'Scaling horizontally sounds simple until you deal with distributed state.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(132,31,'Typography can completely change how a product is perceived. Fonts communicate tone.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(133,24,'Caching is one of those things you don’t appreciate until your server starts sweating under load.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(134,25,'Spacing is underrated in UI. More whitespace often equals more clarity.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(135,26,'Composite indexes aren’t magic. They need to match your query pattern exactly.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(136,27,'Users don’t care how complex your backend is. They care if it feels fast.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(137,28,'Automated backups should be tested, not just configured.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(138,29,'Spent today improving accessibility. Keyboard navigation alone revealed so many flaws.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(139,30,'SSE is surprisingly efficient for lightweight real-time updates. Not everything needs full WebSockets.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(140,31,'Details create polish. Polish creates trust.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(141,24,'Argon2id hashing might feel slow during login, but that’s the point. Security should cost something 🔐',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(142,25,'Color contrast testing should be mandatory before shipping any UI.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(143,26,'Schema-first development changed how I think about backend architecture.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(144,27,'UX is empathy translated into interface decisions.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(145,28,'Logs are messages from your past self. Write them clearly.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(146,29,'Shipping fast is good. Shipping thoughtfully is better.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(147,30,'Real-time notifications feel simple to users but hide serious backend engineering.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(148,31,'Motion design is storytelling through movement.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(149,24,'Spent hours debugging only to discover a timezone mismatch. Dates are dangerous.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(150,25,'Design trends come and go, but clarity is timeless.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(151,26,'Denormalization is a performance tool, not a shortcut.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(152,27,'Feedback loops in product design should be tight and constant.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(153,28,'Infrastructure decisions made early can either empower or haunt you later.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(154,29,'Sometimes the best solution is deleting code.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(155,30,'Connection pooling becomes critical once concurrency spikes.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(156,31,'A smooth loading state can make waiting feel shorter ⏳',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(157,24,'Observability isn’t optional in modern systems. Metrics, logs, traces — all three matter.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(158,26,'Clean architecture isn’t about layers. It’s about boundaries and responsibility.',NULL,NULL,'2026-02-19 09:32:28',NULL,NULL,NULL),
	(162,30,'Spent today rewriting a service layer and the codebase feels 10x cleaner. Architecture decisions compound over time.',NULL,NULL,'2026-02-04 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(163,31,'Today I learned that small schema tweaks can eliminate entire bug classes.',NULL,NULL,'2026-02-05 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(164,27,'Real-time features are simple in theory but brutal in scaling scenarios.',NULL,NULL,'2026-02-15 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(165,31,'UI refactors are satisfying when you delete more code than you add.',NULL,NULL,'2026-01-22 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(166,30,'UI refactors are satisfying when you delete more code than you add.',NULL,NULL,'2026-01-30 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(167,28,'Optimizing queries feels like solving puzzles with measurable rewards.',NULL,NULL,'2026-02-18 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(168,27,'Sometimes deleting a feature improves the product more than adding one.',NULL,NULL,'2026-01-22 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(169,26,'Monitoring should be proactive, not reactive.',NULL,NULL,'2026-02-16 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(170,29,'I underestimated how much proper indexing affects performance. The database went from stressed to relaxed instantly.',NULL,NULL,'2026-02-13 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(171,28,'Real-time features are simple in theory but brutal in scaling scenarios.',NULL,NULL,'2026-02-03 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(172,24,'Sometimes deleting a feature improves the product more than adding one.',NULL,NULL,'2026-02-16 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(173,29,'Spent today rewriting a service layer and the codebase feels 10x cleaner. Architecture decisions compound over time.',NULL,NULL,'2026-02-10 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(174,29,'Designing systems for scale requires thinking about failure first.',NULL,NULL,'2026-01-23 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(175,28,'Spent today rewriting a service layer and the codebase feels 10x cleaner. Architecture decisions compound over time.',NULL,NULL,'2026-02-04 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(176,30,'Real-time features are simple in theory but brutal in scaling scenarios.',NULL,NULL,'2026-01-28 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(177,28,'I underestimated how much proper indexing affects performance. The database went from stressed to relaxed instantly.',NULL,NULL,'2026-01-30 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(178,25,'Designing systems for scale requires thinking about failure first.',NULL,NULL,'2026-02-02 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(179,29,'Monitoring should be proactive, not reactive.',NULL,NULL,'2026-01-27 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(180,31,'Spent today rewriting a service layer and the codebase feels 10x cleaner. Architecture decisions compound over time.',NULL,NULL,'2026-02-10 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(181,26,'Real-time features are simple in theory but brutal in scaling scenarios.',NULL,NULL,'2026-02-17 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(182,25,'Optimizing queries feels like solving puzzles with measurable rewards.',NULL,NULL,'2026-01-27 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(183,28,'Designing systems for scale requires thinking about failure first.',NULL,NULL,'2026-02-19 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(184,27,'Spent today rewriting a service layer and the codebase feels 10x cleaner. Architecture decisions compound over time.',NULL,NULL,'2026-02-04 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(185,31,'Today I learned that small schema tweaks can eliminate entire bug classes.',NULL,NULL,'2026-02-04 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(186,30,'Real-time features are simple in theory but brutal in scaling scenarios.',NULL,NULL,'2026-02-19 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(187,31,'Monitoring should be proactive, not reactive.',NULL,NULL,'2026-02-10 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(188,31,'Optimizing queries feels like solving puzzles with measurable rewards.',NULL,NULL,'2026-02-19 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(189,30,'Today I learned that small schema tweaks can eliminate entire bug classes.',NULL,NULL,'2026-01-23 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(190,30,'I underestimated how much proper indexing affects performance. The database went from stressed to relaxed instantly.',NULL,NULL,'2026-01-27 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(191,27,'Optimizing queries feels like solving puzzles with measurable rewards.',NULL,NULL,'2026-02-01 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(192,26,'Sometimes deleting a feature improves the product more than adding one.',NULL,NULL,'2026-02-02 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(193,30,'Optimizing queries feels like solving puzzles with measurable rewards.',NULL,NULL,'2026-01-23 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(194,30,'Spent today rewriting a service layer and the codebase feels 10x cleaner. Architecture decisions compound over time.',NULL,NULL,'2026-02-08 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(195,26,'I underestimated how much proper indexing affects performance. The database went from stressed to relaxed instantly.',NULL,NULL,'2026-01-23 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(196,27,'I underestimated how much proper indexing affects performance. The database went from stressed to relaxed instantly.',NULL,NULL,'2026-01-25 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(197,25,'Refactoring isn’t glamorous, but it’s responsible engineering.',NULL,NULL,'2026-01-23 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(198,29,'Monitoring should be proactive, not reactive.',NULL,NULL,'2026-02-20 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(199,30,'Monitoring should be proactive, not reactive.',NULL,NULL,'2026-01-25 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(200,28,'Refactoring isn’t glamorous, but it’s responsible engineering.',NULL,NULL,'2026-02-18 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(201,31,'UI refactors are satisfying when you delete more code than you add.',NULL,NULL,'2026-02-06 03:02:19','2026-02-20 03:05:03',NULL,NULL),
	(225,24,'Scaling backend systems changes how you think about every query.',NULL,NULL,'2026-02-12 03:06:01',NULL,NULL,NULL),
	(228,47,'Testing queue workers in production.',NULL,NULL,'2025-11-03 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(229,26,'Testing queue workers in production.',NULL,NULL,'2026-01-28 03:12:51','2026-02-20 03:17:10',NULL,NULL),
	(230,50,'Rewriting legacy code properly this time.',NULL,NULL,'2025-11-17 03:12:51','2026-02-20 03:13:47',NULL,1),
	(231,25,'Refactoring backend modules today. Cleaner structure now.',NULL,NULL,'2025-12-27 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(232,41,'Late night debugging session but worth it.',NULL,NULL,'2025-11-16 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(233,45,'Small UI tweaks make big differences.',NULL,NULL,'2025-11-16 03:12:51','2026-02-20 03:13:54',NULL,NULL),
	(234,26,'Deploying new update tonight.',NULL,NULL,'2025-11-11 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(235,24,'Improving query performance with better indexing.',NULL,NULL,'2025-10-26 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(236,30,'Optimizing API response time.',NULL,NULL,'2025-12-01 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(237,29,'Working on background job processing.',NULL,NULL,'2026-01-02 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(238,51,'Small UI tweaks make big differences.',NULL,NULL,'2025-12-09 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(239,41,'Small UI tweaks make big differences.',NULL,NULL,'2025-11-26 03:12:51','2026-02-20 03:13:56',NULL,NULL),
	(240,41,'Small UI tweaks make big differences.',NULL,NULL,'2025-12-26 03:12:51','2026-02-20 03:13:57',NULL,NULL),
	(241,40,'Deploying new update tonight.',NULL,NULL,'2025-11-14 03:12:51','2026-02-20 03:13:59',NULL,NULL),
	(242,40,'Deploying new update tonight.',NULL,NULL,'2025-11-15 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(243,24,'Refactoring backend modules today. Cleaner structure now.',NULL,NULL,'2026-02-03 03:12:51','2026-02-20 03:14:01',NULL,NULL),
	(244,28,'Rewriting legacy code properly this time.',NULL,NULL,'2025-12-14 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(245,50,'Deploying new update tonight.',NULL,NULL,'2025-11-20 03:12:51','2026-02-20 03:14:02',NULL,NULL),
	(246,35,'Experimenting with websocket scaling.',NULL,NULL,'2026-02-06 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(247,49,'Optimizing API response time.',NULL,NULL,'2025-11-02 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(248,48,'Testing queue workers in production.',NULL,NULL,'2025-11-19 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(249,49,'Rewriting legacy code properly this time.',NULL,NULL,'2026-01-25 03:12:51','2026-02-20 03:14:06',NULL,NULL),
	(250,29,'Late night debugging session but worth it.',NULL,NULL,'2025-12-03 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(251,45,'Refactoring backend modules today. Cleaner structure now.',NULL,NULL,'2026-01-20 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(252,38,'Optimizing API response time.',NULL,NULL,'2025-10-24 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(253,46,'Late night debugging session but worth it.',NULL,NULL,'2026-02-12 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(254,48,'Improving query performance with better indexing.',NULL,NULL,'2026-01-28 03:12:51','2026-02-20 03:14:09',NULL,NULL),
	(255,30,'Experimenting with websocket scaling.',NULL,NULL,'2025-12-03 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(256,40,'Experimenting with websocket scaling.',NULL,NULL,'2025-11-23 03:12:51','2026-02-20 03:14:10',NULL,NULL),
	(257,43,'Testing queue workers in production.',NULL,NULL,'2025-12-18 03:12:51','2026-02-20 03:14:12',NULL,1),
	(258,38,'Small UI tweaks make big differences.',NULL,NULL,'2026-02-20 03:12:51','2026-02-20 03:14:13',NULL,NULL),
	(259,44,'Working on background job processing.',NULL,NULL,'2026-01-04 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(260,44,'Refactoring backend modules today. Cleaner structure now.',NULL,NULL,'2025-10-30 03:12:51','2026-02-20 03:14:15',NULL,NULL),
	(261,33,'Refactoring backend modules today. Cleaner structure now.',NULL,NULL,'2025-12-14 03:12:51','2026-02-20 03:14:16',NULL,NULL),
	(262,40,'Refactoring backend modules today. Cleaner structure now.',NULL,NULL,'2025-12-03 03:12:51','2026-02-20 03:14:18',NULL,1),
	(263,40,'Optimizing API response time.',NULL,NULL,'2025-11-08 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(264,36,'Working on background job processing.',NULL,NULL,'2025-11-05 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(265,25,'Working on background job processing.',NULL,NULL,'2026-01-08 03:12:51','2026-02-20 03:12:51',NULL,1),
	(266,26,'Late night debugging session but worth it.',NULL,NULL,'2026-01-23 03:12:51','2026-02-20 03:14:19',NULL,NULL),
	(267,26,'Optimizing API response time.',NULL,NULL,'2025-10-25 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(268,30,'Improving query performance with better indexing.',NULL,NULL,'2025-12-03 03:12:51','2026-02-20 03:14:22',NULL,NULL),
	(269,37,'Working on background job processing.',NULL,NULL,'2025-11-04 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(270,29,'Refactoring backend modules today. Cleaner structure now.',NULL,NULL,'2026-01-13 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(271,25,'Optimizing API response time.',NULL,NULL,'2026-02-19 03:12:51','2026-02-20 03:14:25',NULL,NULL),
	(272,38,'Small UI tweaks make big differences.',NULL,NULL,'2026-01-03 03:12:51','2026-02-20 03:14:28',NULL,NULL),
	(273,41,'Experimenting with websocket scaling.',NULL,NULL,'2026-02-04 03:12:51','2026-02-20 03:14:31',NULL,NULL),
	(274,33,'Refactoring backend modules today. Cleaner structure now.',NULL,NULL,'2025-11-21 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(275,41,'Working on background job processing.',NULL,NULL,'2025-12-10 03:12:51','2026-02-20 03:14:33',NULL,NULL),
	(276,34,'Experimenting with websocket scaling.',NULL,NULL,'2025-10-28 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(277,45,'Testing queue workers in production.',NULL,NULL,'2025-12-31 03:12:51','2026-02-20 03:14:47',NULL,NULL),
	(278,24,'Experimenting with websocket scaling.',NULL,NULL,'2025-11-29 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(279,34,'Late night debugging session but worth it.',NULL,NULL,'2025-10-29 03:12:51','2026-02-20 03:12:51',NULL,1),
	(280,31,'Late night debugging session but worth it.',NULL,NULL,'2025-12-02 03:12:51','2026-02-20 03:14:49',NULL,NULL),
	(281,36,'Experimenting with websocket scaling.',NULL,NULL,'2025-11-05 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(282,37,'Small UI tweaks make big differences.',NULL,NULL,'2026-01-13 03:12:51','2026-02-20 03:17:10',NULL,NULL),
	(283,33,'Refactoring backend modules today. Cleaner structure now.',NULL,NULL,'2026-02-10 03:12:51','2026-02-20 03:17:10',NULL,NULL),
	(284,47,'Working on background job processing.',NULL,NULL,'2026-01-26 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(285,42,'Working on background job processing.',NULL,NULL,'2025-11-13 03:12:51','2026-02-20 03:17:10',NULL,1),
	(286,40,'Rewriting legacy code properly this time.',NULL,NULL,'2026-01-29 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(287,33,'Rewriting legacy code properly this time.',NULL,NULL,'2026-01-04 03:12:51','2026-02-20 03:12:51',NULL,1),
	(288,29,'Deploying new update tonight.',NULL,NULL,'2026-01-01 03:12:51','2026-02-20 03:17:10',NULL,NULL),
	(289,39,'Optimizing API response time.',NULL,NULL,'2025-10-25 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(290,48,'Rewriting legacy code properly this time.',NULL,NULL,'2025-11-22 03:12:51','2026-02-20 03:17:10',NULL,NULL),
	(291,38,'Deploying new update tonight.',NULL,NULL,'2025-11-02 03:12:51','2026-02-20 03:17:10',NULL,NULL),
	(292,30,'Small UI tweaks make big differences.',NULL,NULL,'2026-01-30 03:12:51','2026-02-20 03:12:51',NULL,1),
	(293,45,'Optimizing API response time.',NULL,NULL,'2026-01-18 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(294,43,'Deploying new update tonight.',NULL,NULL,'2025-10-25 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(295,38,'Optimizing API response time.',NULL,NULL,'2026-01-15 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(296,42,'Small UI tweaks make big differences.',NULL,NULL,'2025-11-16 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(297,25,'Experimenting with websocket scaling.',NULL,NULL,'2025-11-20 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(298,27,'Improving query performance with better indexing.',NULL,NULL,'2026-01-10 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(299,47,'Late night debugging session but worth it.',NULL,NULL,'2025-11-20 03:12:51','2026-02-20 03:12:51',NULL,1),
	(300,25,'Rewriting legacy code properly this time.',NULL,NULL,'2026-01-27 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(301,51,'Experimenting with websocket scaling.',NULL,NULL,'2025-12-03 03:12:51','2026-02-20 03:12:51',NULL,1),
	(302,42,'Rewriting legacy code properly this time.',NULL,NULL,'2026-02-17 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(303,36,'Deploying new update tonight.',NULL,NULL,'2026-01-15 03:12:51','2026-02-20 03:17:10',NULL,NULL),
	(304,29,'Small UI tweaks make big differences.',NULL,NULL,'2025-10-29 03:12:51','2026-02-20 03:17:10',NULL,1),
	(305,45,'Working on background job processing.',NULL,NULL,'2026-01-04 03:12:51','2026-02-20 03:17:10',NULL,NULL),
	(306,49,'Testing queue workers in production.',NULL,NULL,'2025-11-03 03:12:51','2026-02-20 03:12:51',NULL,1),
	(307,48,'Rewriting legacy code properly this time.',NULL,NULL,'2026-02-13 03:12:51','2026-02-20 03:17:10',NULL,NULL),
	(308,35,'Working on background job processing.',NULL,NULL,'2025-11-26 03:12:51','2026-02-20 03:17:10',NULL,NULL),
	(309,38,'Small UI tweaks make big differences.',NULL,NULL,'2026-01-07 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(310,40,'Working on background job processing.',NULL,NULL,'2025-12-24 03:12:51','2026-02-20 03:17:10',NULL,NULL),
	(311,33,'Deploying new update tonight.',NULL,NULL,'2025-12-01 03:12:51','2026-02-20 03:12:51',NULL,1),
	(312,29,'Testing queue workers in production.',NULL,NULL,'2026-02-01 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(313,30,'Working on background job processing.',NULL,NULL,'2026-01-01 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(314,40,'Small UI tweaks make big differences.',NULL,NULL,'2026-02-10 03:12:51','2026-02-20 03:17:10',NULL,NULL),
	(315,43,'Late night debugging session but worth it.',NULL,NULL,'2026-01-30 03:12:51','2026-02-20 03:17:10',NULL,NULL),
	(316,31,'Testing queue workers in production.',NULL,NULL,'2025-11-30 03:12:51','2026-02-20 03:12:51',NULL,1),
	(317,34,'Optimizing API response time.',NULL,NULL,'2025-11-18 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(318,39,'Improving query performance with better indexing.',NULL,NULL,'2026-01-28 03:12:51','2026-02-20 03:17:10',NULL,NULL),
	(319,44,'Optimizing API response time.',NULL,NULL,'2025-12-18 03:12:51','2026-02-20 03:17:10',NULL,NULL),
	(320,28,'Deploying new update tonight.',NULL,NULL,'2025-10-28 03:12:51','2026-02-20 03:17:10',NULL,1),
	(321,29,'Testing queue workers in production.',NULL,NULL,'2025-11-27 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(322,41,'Late night debugging session but worth it.',NULL,NULL,'2026-02-06 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(323,25,'Deploying new update tonight.',NULL,NULL,'2025-11-08 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(324,45,'Working on background job processing.',NULL,NULL,'2025-12-22 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(325,37,'Small UI tweaks make big differences.',NULL,NULL,'2025-12-18 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(326,25,'Improving query performance with better indexing.',NULL,NULL,'2025-11-30 03:12:51','2026-02-20 03:12:51',NULL,NULL),
	(327,33,'Refactoring backend modules today. Cleaner structure now.',NULL,NULL,'2025-11-29 03:12:51','2026-02-20 03:17:10',NULL,NULL),
	(328,1,'ggwp',NULL,NULL,'2026-02-20 07:56:38','2026-02-20 07:56:38',NULL,NULL),
	(329,1,'','https://res.cloudinary.com/dn6rffrwk/image/upload/v1771574625/posts/user_1_0fc2836be52581512c73d343.jpg',NULL,'2026-02-20 08:03:54','2026-02-20 08:03:54',NULL,NULL),
	(330,1,'','/uploads/attachments/posts/att_6996c5e3ad51e2.38846119.jpg',NULL,'2026-02-19 08:12:23','2026-02-19 08:12:23',NULL,NULL),
	(331,53,'test post ','/uploads/attachments/posts/att_6996c6989c8372.80192319.mp4',NULL,'2026-02-19 08:15:22','2026-02-19 08:15:23',NULL,1),
	(332,1,'hello @sirdong ',NULL,NULL,'2026-02-20 08:24:21','2026-02-20 08:24:21',NULL,NULL),
	(333,53,'<script>alert(1);</script>',NULL,NULL,'2026-02-20 08:31:40','2026-02-20 08:31:40',NULL,NULL),
	(334,53,'','https://res.cloudinary.com/dn6rffrwk/image/upload/v1771813634/posts/user_53_bcea2c0f8c840477ebafdaad.png',NULL,'2026-02-23 02:27:16','2026-02-23 02:27:16',NULL,0),
	(335,53,'#fyp #viral','https://res.cloudinary.com/dn6rffrwk/video/upload/v1771813657/posts/user_53_c387209fc1ea5817afedb806.mp4',NULL,'2026-02-23 02:27:49','2026-02-23 02:27:49',NULL,1),
	(336,53,'hello','https://res.cloudinary.com/dn6rffrwk/video/upload/v1771813694/posts/user_53_4dffee77eb16a3648728dc8b.mp4',NULL,'2026-02-23 02:28:46','2026-02-23 02:28:46',NULL,1);

/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table reactions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `reactions`;

CREATE TABLE `reactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `target_type` enum('post','comment') COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_id` bigint unsigned NOT NULL,
  `reaction_type` enum('like','haha','love','wow','sad','angry') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_target_reaction` (`user_id`,`target_type`,`target_id`),
  KEY `idx_reactions_target` (`target_type`,`target_id`),
  KEY `idx_reactions_user` (`user_id`),
  KEY `idx_reactions_type` (`target_type`,`target_id`,`reaction_type`),
  CONSTRAINT `fk_reactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=850 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `reactions` WRITE;
/*!40000 ALTER TABLE `reactions` DISABLE KEYS */;

INSERT INTO `reactions` (`id`, `user_id`, `target_type`, `target_id`, `reaction_type`, `created_at`, `updated_at`)
VALUES
	(128,2,'post',160,'love','2026-02-19 09:49:57','2026-02-19 09:49:57'),
	(129,26,'post',109,'haha','2026-02-20 06:32:28',NULL),
	(130,28,'post',109,'angry','2026-02-21 06:32:28',NULL),
	(131,29,'post',110,'angry','2026-02-20 05:32:28',NULL),
	(132,31,'post',110,'angry','2026-02-20 00:32:28',NULL),
	(136,2,'post',161,'like','2026-02-19 10:09:13','2026-02-19 10:09:13'),
	(137,31,'post',109,'love','2026-02-21 02:32:28',NULL),
	(138,27,'post',109,'angry','2026-02-23 07:32:28',NULL),
	(139,30,'post',110,'like','2026-02-20 10:32:28',NULL),
	(140,28,'post',110,'like','2026-02-22 03:32:28',NULL),
	(141,27,'post',110,'like','2026-02-21 05:32:28',NULL),
	(142,26,'post',110,'sad','2026-02-20 04:32:28',NULL),
	(143,24,'post',110,'sad','2026-02-22 20:32:28',NULL),
	(144,31,'post',111,'love','2026-02-20 22:32:28',NULL),
	(145,29,'post',111,'love','2026-02-23 01:32:28',NULL),
	(146,28,'post',111,'angry','2026-02-20 11:32:28',NULL),
	(147,31,'post',112,'like','2026-02-21 17:32:28',NULL),
	(148,29,'post',112,'sad','2026-02-21 00:32:28',NULL),
	(149,28,'post',112,'sad','2026-02-19 12:32:28',NULL),
	(150,25,'post',112,'love','2026-02-21 14:32:28',NULL),
	(151,24,'post',112,'sad','2026-02-21 23:32:28',NULL),
	(152,26,'post',113,'love','2026-02-19 21:32:28',NULL),
	(153,25,'post',113,'angry','2026-02-21 23:32:28',NULL),
	(154,31,'post',114,'angry','2026-02-22 11:32:28',NULL),
	(155,27,'post',114,'like','2026-02-23 08:32:28',NULL),
	(156,31,'post',115,'wow','2026-02-21 20:32:28',NULL),
	(157,29,'post',115,'wow','2026-02-22 22:32:28',NULL),
	(158,26,'post',115,'angry','2026-02-21 20:32:28',NULL),
	(159,25,'post',115,'love','2026-02-23 07:32:28',NULL),
	(160,28,'post',116,'sad','2026-02-20 05:32:28',NULL),
	(161,24,'post',116,'sad','2026-02-20 13:32:28',NULL),
	(162,31,'post',117,'like','2026-02-19 15:32:28',NULL),
	(163,25,'post',117,'love','2026-02-21 15:32:28',NULL),
	(164,30,'post',118,'wow','2026-02-20 21:32:28',NULL),
	(165,29,'post',118,'wow','2026-02-22 04:32:28',NULL),
	(166,29,'post',119,'love','2026-02-22 01:32:28',NULL),
	(167,28,'post',119,'love','2026-02-20 10:32:28',NULL),
	(168,27,'post',119,'love','2026-02-22 23:32:28',NULL),
	(169,25,'post',119,'sad','2026-02-19 21:32:28',NULL),
	(170,24,'post',119,'sad','2026-02-22 13:32:28',NULL),
	(171,30,'post',120,'angry','2026-02-21 07:32:28',NULL),
	(172,29,'post',120,'angry','2026-02-20 20:32:28',NULL),
	(173,25,'post',120,'angry','2026-02-20 05:32:28',NULL),
	(174,30,'post',121,'wow','2026-02-23 02:32:28',NULL),
	(175,29,'post',121,'like','2026-02-22 22:32:28',NULL),
	(176,27,'post',121,'like','2026-02-20 08:32:28',NULL),
	(177,26,'post',121,'wow','2026-02-22 14:32:28',NULL),
	(178,25,'post',121,'wow','2026-02-21 17:32:28',NULL),
	(179,24,'post',121,'haha','2026-02-20 13:32:28',NULL),
	(180,24,'post',122,'wow','2026-02-22 18:32:28',NULL),
	(181,28,'post',123,'wow','2026-02-21 23:32:28',NULL),
	(182,26,'post',123,'haha','2026-02-21 19:32:28',NULL),
	(183,30,'post',124,'sad','2026-02-20 07:32:28',NULL),
	(184,29,'post',124,'love','2026-02-19 17:32:28',NULL),
	(185,28,'post',124,'love','2026-02-20 15:32:28',NULL),
	(186,26,'post',124,'haha','2026-02-21 15:32:28',NULL),
	(187,31,'post',125,'wow','2026-02-20 20:32:28',NULL),
	(188,30,'post',125,'sad','2026-02-20 11:32:28',NULL),
	(189,29,'post',125,'wow','2026-02-21 22:32:28',NULL),
	(190,28,'post',125,'love','2026-02-22 09:32:28',NULL),
	(191,27,'post',125,'haha','2026-02-21 00:32:28',NULL),
	(192,25,'post',125,'love','2026-02-20 03:32:28',NULL),
	(193,31,'post',126,'angry','2026-02-20 15:32:28',NULL),
	(194,29,'post',126,'like','2026-02-19 13:32:28',NULL),
	(195,28,'post',126,'like','2026-02-21 13:32:28',NULL),
	(196,27,'post',126,'angry','2026-02-22 01:32:28',NULL),
	(197,26,'post',126,'angry','2026-02-21 19:32:28',NULL),
	(198,24,'post',126,'love','2026-02-23 06:32:28',NULL),
	(199,30,'post',127,'like','2026-02-22 06:32:28',NULL),
	(200,29,'post',127,'haha','2026-02-19 17:32:28',NULL),
	(201,27,'post',127,'love','2026-02-19 23:32:28',NULL),
	(202,29,'post',128,'wow','2026-02-21 19:32:28',NULL),
	(203,28,'post',128,'wow','2026-02-23 08:32:28',NULL),
	(204,26,'post',128,'love','2026-02-20 17:32:28',NULL),
	(205,31,'post',129,'sad','2026-02-21 19:32:28',NULL),
	(206,29,'post',129,'like','2026-02-22 16:32:28',NULL),
	(207,27,'post',129,'like','2026-02-20 02:32:28',NULL),
	(208,30,'post',130,'love','2026-02-20 11:32:28',NULL),
	(209,28,'post',130,'angry','2026-02-20 08:32:28',NULL),
	(210,24,'post',130,'like','2026-02-20 16:32:28',NULL),
	(211,28,'post',131,'angry','2026-02-23 05:32:28',NULL),
	(212,30,'post',132,'angry','2026-02-21 17:32:28',NULL),
	(213,29,'post',132,'love','2026-02-19 12:32:28',NULL),
	(214,27,'post',132,'like','2026-02-22 02:32:28',NULL),
	(215,25,'post',132,'like','2026-02-22 15:32:28',NULL),
	(216,24,'post',132,'haha','2026-02-20 21:32:28',NULL),
	(217,30,'post',133,'sad','2026-02-22 23:32:28',NULL),
	(218,29,'post',133,'sad','2026-02-20 12:32:28',NULL),
	(219,28,'post',133,'like','2026-02-22 07:32:28',NULL),
	(220,27,'post',133,'love','2026-02-20 18:32:28',NULL),
	(221,26,'post',133,'angry','2026-02-21 20:32:28',NULL),
	(222,25,'post',133,'wow','2026-02-20 22:32:28',NULL),
	(223,31,'post',134,'angry','2026-02-20 01:32:28',NULL),
	(224,26,'post',134,'love','2026-02-22 11:32:28',NULL),
	(225,31,'post',135,'like','2026-02-22 01:32:28',NULL),
	(226,28,'post',135,'love','2026-02-20 01:32:28',NULL),
	(227,25,'post',135,'sad','2026-02-20 22:32:28',NULL),
	(228,30,'post',136,'love','2026-02-22 04:32:28',NULL),
	(229,29,'post',136,'haha','2026-02-23 02:32:28',NULL),
	(230,26,'post',136,'love','2026-02-19 10:32:28',NULL),
	(231,28,'post',138,'wow','2026-02-19 19:32:28',NULL),
	(232,27,'post',138,'angry','2026-02-21 15:32:28',NULL),
	(233,25,'post',138,'haha','2026-02-21 20:32:28',NULL),
	(234,29,'post',139,'love','2026-02-19 21:32:28',NULL),
	(235,27,'post',139,'wow','2026-02-21 12:32:28',NULL),
	(236,29,'post',140,'love','2026-02-21 00:32:28',NULL),
	(237,28,'post',140,'angry','2026-02-19 13:32:28',NULL),
	(238,27,'post',140,'like','2026-02-22 11:32:28',NULL),
	(239,26,'post',140,'wow','2026-02-23 04:32:28',NULL),
	(240,25,'post',140,'angry','2026-02-19 10:32:28',NULL),
	(241,24,'post',140,'angry','2026-02-21 06:32:28',NULL),
	(242,29,'post',141,'like','2026-02-21 04:32:28',NULL),
	(243,28,'post',141,'wow','2026-02-20 16:32:28',NULL),
	(244,30,'post',142,'like','2026-02-22 11:32:28',NULL),
	(245,30,'post',143,'sad','2026-02-22 23:32:28',NULL),
	(246,29,'post',143,'angry','2026-02-22 22:32:28',NULL),
	(247,25,'post',143,'haha','2026-02-20 03:32:28',NULL),
	(248,24,'post',143,'love','2026-02-20 05:32:28',NULL),
	(249,26,'post',144,'wow','2026-02-20 07:32:28',NULL),
	(250,24,'post',144,'haha','2026-02-23 04:32:28',NULL),
	(251,31,'post',145,'wow','2026-02-21 09:32:28',NULL),
	(252,26,'post',145,'wow','2026-02-23 07:32:28',NULL),
	(253,25,'post',145,'wow','2026-02-21 22:32:28',NULL),
	(254,28,'post',146,'love','2026-02-20 03:32:28',NULL),
	(255,24,'post',146,'wow','2026-02-20 04:32:28',NULL),
	(256,31,'post',147,'sad','2026-02-21 12:32:28',NULL),
	(257,29,'post',147,'wow','2026-02-20 17:32:28',NULL),
	(258,28,'post',147,'wow','2026-02-22 08:32:28',NULL),
	(259,26,'post',147,'sad','2026-02-22 11:32:28',NULL),
	(260,25,'post',147,'angry','2026-02-20 18:32:28',NULL),
	(261,26,'post',148,'angry','2026-02-20 18:32:28',NULL),
	(262,25,'post',148,'like','2026-02-21 01:32:28',NULL),
	(263,24,'post',148,'like','2026-02-20 15:32:28',NULL),
	(264,31,'post',149,'love','2026-02-23 02:32:28',NULL),
	(265,31,'post',150,'like','2026-02-23 05:32:28',NULL),
	(266,30,'post',150,'love','2026-02-23 04:32:28',NULL),
	(267,29,'post',150,'sad','2026-02-23 03:32:28',NULL),
	(268,28,'post',150,'like','2026-02-23 05:32:28',NULL),
	(269,24,'post',150,'haha','2026-02-19 23:32:28',NULL),
	(270,30,'post',151,'wow','2026-02-22 23:32:28',NULL),
	(271,25,'post',152,'like','2026-02-21 01:32:28',NULL),
	(272,30,'post',153,'angry','2026-02-21 02:32:28',NULL),
	(273,29,'post',153,'sad','2026-02-23 03:32:28',NULL),
	(274,26,'post',153,'like','2026-02-21 22:32:28',NULL),
	(275,25,'post',153,'wow','2026-02-22 21:32:28',NULL),
	(276,31,'post',154,'wow','2026-02-20 12:32:28',NULL),
	(277,30,'post',154,'like','2026-02-21 00:32:28',NULL),
	(278,28,'post',154,'sad','2026-02-21 18:32:28',NULL),
	(279,26,'post',154,'angry','2026-02-22 11:32:28',NULL),
	(280,25,'post',154,'haha','2026-02-21 17:32:28',NULL),
	(281,31,'post',155,'like','2026-02-22 02:32:28',NULL),
	(282,29,'post',155,'like','2026-02-19 13:32:28',NULL),
	(283,25,'post',155,'wow','2026-02-22 23:32:28',NULL),
	(284,27,'post',156,'haha','2026-02-20 20:32:28',NULL),
	(285,26,'post',156,'haha','2026-02-20 13:32:28',NULL),
	(286,24,'post',156,'love','2026-02-21 15:32:28',NULL),
	(287,30,'post',157,'love','2026-02-21 08:32:28',NULL),
	(288,29,'post',157,'like','2026-02-21 00:32:28',NULL),
	(289,29,'post',158,'like','2026-02-19 10:32:28',NULL),
	(290,30,'post',159,'wow','2026-02-22 17:41:57',NULL),
	(291,28,'post',159,'sad','2026-02-19 13:41:57',NULL),
	(292,30,'post',160,'haha','2026-02-21 04:45:09',NULL),
	(293,29,'post',160,'wow','2026-02-22 16:45:09',NULL),
	(294,28,'post',160,'haha','2026-02-19 21:45:09',NULL),
	(295,26,'post',160,'wow','2026-02-20 14:45:09',NULL),
	(296,24,'post',160,'like','2026-02-19 22:45:09',NULL),
	(297,31,'post',161,'sad','2026-02-20 07:07:31',NULL),
	(298,29,'post',161,'like','2026-02-23 08:07:31',NULL),
	(299,27,'post',161,'wow','2026-02-23 01:07:31',NULL),
	(300,26,'post',161,'wow','2026-02-22 23:07:31',NULL),
	(301,25,'post',161,'love','2026-02-22 20:07:31',NULL),
	(302,24,'post',161,'angry','2026-02-21 00:07:31',NULL),
	(303,29,'post',162,'like','2026-02-07 19:02:19',NULL),
	(304,26,'post',162,'like','2026-02-06 08:02:19',NULL),
	(305,24,'post',162,'angry','2026-02-05 15:02:19',NULL),
	(306,30,'post',163,'like','2026-02-05 17:02:19',NULL),
	(307,27,'post',163,'angry','2026-02-08 16:02:19',NULL),
	(308,24,'post',163,'sad','2026-02-08 23:02:19',NULL),
	(309,29,'post',164,'wow','2026-02-17 22:02:19',NULL),
	(310,25,'post',164,'wow','2026-02-16 18:02:19',NULL),
	(311,24,'post',164,'love','2026-02-17 12:02:19',NULL),
	(312,30,'post',165,'like','2026-01-23 13:02:19',NULL),
	(313,29,'post',165,'sad','2026-01-25 06:02:19',NULL),
	(314,28,'post',165,'sad','2026-01-24 06:02:19',NULL),
	(315,27,'post',165,'sad','2026-01-25 20:02:19',NULL),
	(316,26,'post',165,'love','2026-01-24 23:02:19',NULL),
	(317,25,'post',165,'haha','2026-01-22 11:02:19',NULL),
	(318,24,'post',165,'angry','2026-01-25 16:02:19',NULL),
	(319,29,'post',166,'wow','2026-02-01 07:02:19',NULL),
	(320,28,'post',166,'haha','2026-02-01 11:02:19',NULL),
	(321,27,'post',166,'wow','2026-01-30 14:02:19',NULL),
	(322,31,'post',167,'haha','2026-02-21 17:02:19',NULL),
	(323,27,'post',167,'haha','2026-02-20 22:02:19',NULL),
	(324,24,'post',167,'love','2026-02-21 22:02:19',NULL),
	(325,31,'post',168,'love','2026-01-23 15:02:19',NULL),
	(326,29,'post',168,'love','2026-01-23 23:02:19',NULL),
	(327,28,'post',168,'angry','2026-01-23 20:02:19',NULL),
	(328,26,'post',168,'wow','2026-01-25 03:02:19',NULL),
	(329,25,'post',168,'like','2026-01-25 21:02:19',NULL),
	(330,24,'post',168,'love','2026-01-22 14:02:19',NULL),
	(331,24,'post',169,'love','2026-02-19 00:02:19',NULL),
	(332,31,'post',170,'haha','2026-02-15 22:02:19',NULL),
	(333,28,'post',170,'angry','2026-02-14 13:02:19',NULL),
	(334,24,'post',170,'angry','2026-02-14 22:02:19',NULL),
	(335,30,'post',172,'wow','2026-02-18 23:02:19',NULL),
	(336,27,'post',172,'angry','2026-02-18 16:02:19',NULL),
	(337,26,'post',172,'wow','2026-02-19 21:02:19',NULL),
	(338,25,'post',172,'haha','2026-02-16 20:02:19',NULL),
	(339,31,'post',173,'love','2026-02-11 00:02:19',NULL),
	(340,30,'post',173,'sad','2026-02-10 19:02:19',NULL),
	(341,25,'post',173,'angry','2026-02-11 23:02:19',NULL),
	(342,31,'post',174,'angry','2026-01-26 23:02:19',NULL),
	(343,30,'post',174,'love','2026-01-25 12:02:19',NULL),
	(344,31,'post',175,'haha','2026-02-05 14:02:19',NULL),
	(345,29,'post',175,'like','2026-02-04 19:02:19',NULL),
	(346,26,'post',175,'sad','2026-02-06 00:02:19',NULL),
	(347,24,'post',175,'haha','2026-02-05 04:02:19',NULL),
	(348,31,'post',176,'love','2026-01-30 08:02:19',NULL),
	(349,29,'post',176,'like','2026-01-30 18:02:19',NULL),
	(350,28,'post',176,'wow','2026-01-29 18:02:19',NULL),
	(351,27,'post',176,'angry','2026-01-28 16:02:19',NULL),
	(352,26,'post',176,'angry','2026-01-28 18:02:19',NULL),
	(353,25,'post',176,'wow','2026-01-30 01:02:19',NULL),
	(354,24,'post',176,'like','2026-01-29 00:02:19',NULL),
	(355,29,'post',177,'like','2026-02-02 18:02:19',NULL),
	(356,27,'post',177,'love','2026-01-31 16:02:19',NULL),
	(357,27,'post',178,'sad','2026-02-05 18:02:19',NULL),
	(358,30,'post',179,'haha','2026-01-27 08:02:19',NULL),
	(359,25,'post',179,'angry','2026-01-28 22:02:19',NULL),
	(360,29,'post',180,'haha','2026-02-14 00:02:19',NULL),
	(361,28,'post',180,'like','2026-02-10 19:02:19',NULL),
	(362,27,'post',180,'love','2026-02-12 23:02:19',NULL),
	(363,26,'post',180,'like','2026-02-11 09:02:19',NULL),
	(364,25,'post',180,'like','2026-02-12 11:02:19',NULL),
	(365,29,'post',181,'sad','2026-02-19 12:02:19',NULL),
	(366,27,'post',181,'like','2026-02-18 02:02:19',NULL),
	(367,25,'post',181,'angry','2026-02-18 04:02:19',NULL),
	(368,30,'post',182,'like','2026-01-27 15:02:19',NULL),
	(369,29,'post',182,'sad','2026-01-28 13:02:19',NULL),
	(370,28,'post',182,'sad','2026-01-28 06:02:19',NULL),
	(371,27,'post',182,'love','2026-01-29 11:02:19',NULL),
	(372,24,'post',182,'angry','2026-01-28 12:02:19',NULL),
	(373,31,'post',183,'haha','2026-02-20 11:02:19',NULL),
	(374,25,'post',183,'like','2026-02-22 07:02:19',NULL),
	(375,26,'post',184,'like','2026-02-07 11:02:19',NULL),
	(376,28,'post',185,'wow','2026-02-06 15:02:19',NULL),
	(377,27,'post',185,'like','2026-02-06 01:02:19',NULL),
	(378,26,'post',185,'wow','2026-02-04 07:02:19',NULL),
	(379,25,'post',185,'sad','2026-02-05 12:02:19',NULL),
	(380,29,'post',186,'angry','2026-02-22 00:02:19',NULL),
	(381,27,'post',186,'haha','2026-02-20 19:02:19',NULL),
	(382,30,'post',187,'like','2026-02-11 20:02:19',NULL),
	(383,29,'post',187,'haha','2026-02-11 03:02:19',NULL),
	(384,28,'post',187,'love','2026-02-13 10:02:19',NULL),
	(385,26,'post',187,'wow','2026-02-10 11:02:19',NULL),
	(386,24,'post',187,'love','2026-02-11 23:02:19',NULL),
	(387,30,'post',188,'angry','2026-02-20 09:02:19',NULL),
	(388,24,'post',188,'angry','2026-02-21 01:02:19',NULL),
	(389,29,'post',189,'love','2026-01-24 00:02:19',NULL),
	(390,26,'post',189,'haha','2026-01-23 03:02:19',NULL),
	(391,25,'post',189,'wow','2026-01-26 19:02:19',NULL),
	(392,29,'post',190,'sad','2026-01-27 06:02:19',NULL),
	(393,27,'post',190,'wow','2026-01-30 12:02:19',NULL),
	(394,25,'post',190,'angry','2026-01-29 14:02:19',NULL),
	(395,24,'post',190,'wow','2026-01-30 12:02:19',NULL),
	(396,29,'post',193,'angry','2026-01-25 09:02:19',NULL),
	(397,28,'post',193,'love','2026-01-25 00:02:19',NULL),
	(398,27,'post',193,'angry','2026-01-24 11:02:19',NULL),
	(399,31,'post',194,'like','2026-02-10 13:02:19',NULL),
	(400,24,'post',194,'angry','2026-02-10 06:02:19',NULL),
	(401,27,'post',195,'angry','2026-01-23 07:02:19',NULL),
	(402,25,'post',195,'like','2026-01-24 12:02:19',NULL),
	(403,24,'post',195,'wow','2026-01-23 05:02:19',NULL),
	(404,31,'post',196,'love','2026-01-25 17:02:19',NULL),
	(405,30,'post',196,'haha','2026-01-27 06:02:19',NULL),
	(406,28,'post',196,'sad','2026-01-27 16:02:19',NULL),
	(407,25,'post',196,'haha','2026-01-25 16:02:19',NULL),
	(408,31,'post',197,'haha','2026-01-24 22:02:19',NULL),
	(409,28,'post',197,'haha','2026-01-25 23:02:19',NULL),
	(410,24,'post',197,'angry','2026-01-24 05:02:19',NULL),
	(411,31,'post',198,'haha','2026-02-23 05:02:19',NULL),
	(412,30,'post',198,'angry','2026-02-20 19:02:19',NULL),
	(413,26,'post',198,'haha','2026-02-21 09:02:19',NULL),
	(414,27,'post',199,'wow','2026-01-27 19:02:19',NULL),
	(415,25,'post',199,'sad','2026-01-26 01:02:19',NULL),
	(416,31,'post',200,'sad','2026-02-21 02:02:19',NULL),
	(417,30,'post',200,'angry','2026-02-21 15:02:19',NULL),
	(418,26,'post',200,'wow','2026-02-21 23:02:19',NULL),
	(419,24,'post',200,'love','2026-02-22 00:02:19',NULL),
	(420,28,'post',201,'wow','2026-02-08 18:02:19',NULL),
	(648,29,'post',109,'angry','2026-02-21 04:32:28',NULL),
	(649,30,'post',111,'sad','2026-02-22 14:32:28',NULL),
	(650,30,'post',112,'sad','2026-02-24 04:32:28',NULL),
	(651,24,'post',113,'like','2026-02-22 16:32:28',NULL),
	(652,25,'post',114,'wow','2026-02-24 04:32:28',NULL),
	(653,24,'post',114,'like','2026-02-24 07:32:28',NULL),
	(654,24,'post',115,'love','2026-02-19 13:32:28',NULL),
	(655,30,'post',116,'wow','2026-02-22 18:32:28',NULL),
	(656,27,'post',116,'like','2026-02-19 09:32:28',NULL),
	(657,28,'post',118,'angry','2026-02-20 04:32:28',NULL),
	(658,27,'post',118,'sad','2026-02-20 16:32:28',NULL),
	(659,31,'post',119,'sad','2026-02-19 09:32:28',NULL),
	(660,30,'post',119,'angry','2026-02-19 22:32:28',NULL),
	(661,28,'post',120,'wow','2026-02-21 19:32:28',NULL),
	(662,31,'post',121,'angry','2026-02-22 18:32:28',NULL),
	(663,24,'post',123,'angry','2026-02-21 00:32:28',NULL),
	(664,25,'post',124,'angry','2026-02-23 09:32:28',NULL),
	(665,30,'post',126,'sad','2026-02-22 12:32:28',NULL),
	(666,31,'post',128,'love','2026-02-19 18:32:28',NULL),
	(667,30,'post',128,'sad','2026-02-19 14:32:28',NULL),
	(668,25,'post',128,'like','2026-02-24 00:32:28',NULL),
	(669,24,'post',129,'wow','2026-02-23 14:32:28',NULL),
	(670,31,'post',130,'like','2026-02-23 09:32:28',NULL),
	(671,27,'post',130,'like','2026-02-23 08:32:28',NULL),
	(672,25,'post',130,'haha','2026-02-23 10:32:28',NULL),
	(673,29,'post',131,'angry','2026-02-20 21:32:28',NULL),
	(674,28,'post',132,'wow','2026-02-19 16:32:28',NULL),
	(675,26,'post',132,'sad','2026-02-24 03:32:28',NULL),
	(676,31,'post',133,'love','2026-02-19 21:32:28',NULL),
	(677,30,'post',134,'like','2026-02-23 02:32:28',NULL),
	(678,29,'post',135,'love','2026-02-20 02:32:28',NULL),
	(679,27,'post',135,'like','2026-02-21 14:32:28',NULL),
	(680,24,'post',135,'angry','2026-02-22 18:32:28',NULL),
	(681,31,'post',136,'sad','2026-02-19 15:32:28',NULL),
	(682,28,'post',136,'like','2026-02-23 14:32:28',NULL),
	(683,25,'post',136,'like','2026-02-23 08:32:28',NULL),
	(684,24,'post',136,'wow','2026-02-23 20:32:28',NULL),
	(685,29,'post',137,'haha','2026-02-24 04:32:28',NULL),
	(686,27,'post',137,'love','2026-02-23 23:32:28',NULL),
	(687,30,'post',138,'love','2026-02-19 09:32:28',NULL),
	(688,31,'post',139,'love','2026-02-23 18:32:28',NULL),
	(689,30,'post',140,'love','2026-02-19 20:32:28',NULL),
	(690,27,'post',141,'like','2026-02-23 00:32:28',NULL),
	(691,26,'post',141,'sad','2026-02-19 11:32:28',NULL),
	(692,29,'post',142,'love','2026-02-21 02:32:28',NULL),
	(693,26,'post',142,'wow','2026-02-23 14:32:28',NULL),
	(694,31,'post',143,'like','2026-02-21 20:32:28',NULL),
	(695,28,'post',143,'sad','2026-02-23 14:32:28',NULL),
	(696,30,'post',144,'sad','2026-02-20 07:32:28',NULL),
	(697,29,'post',145,'love','2026-02-21 10:32:28',NULL),
	(698,27,'post',147,'angry','2026-02-23 14:32:28',NULL),
	(699,27,'post',148,'love','2026-02-19 12:32:28',NULL),
	(700,27,'post',150,'like','2026-02-19 15:32:28',NULL),
	(701,26,'post',150,'haha','2026-02-22 20:32:28',NULL),
	(702,31,'post',152,'haha','2026-02-20 15:32:28',NULL),
	(703,31,'post',153,'sad','2026-02-23 19:32:28',NULL),
	(704,27,'post',153,'like','2026-02-21 01:32:28',NULL),
	(705,24,'post',153,'sad','2026-02-20 16:32:28',NULL),
	(706,27,'post',154,'wow','2026-02-20 19:32:28',NULL),
	(707,28,'post',155,'angry','2026-02-20 10:32:28',NULL),
	(708,27,'post',155,'haha','2026-02-23 11:32:28',NULL),
	(709,24,'post',155,'like','2026-02-21 03:32:28',NULL),
	(710,29,'post',156,'love','2026-02-21 19:32:28',NULL),
	(711,28,'post',156,'like','2026-02-20 04:32:28',NULL),
	(712,25,'post',156,'wow','2026-02-22 20:32:28',NULL),
	(713,28,'post',157,'haha','2026-02-22 22:32:28',NULL),
	(714,26,'post',157,'wow','2026-02-21 02:32:28',NULL),
	(715,24,'post',159,'haha','2026-02-21 11:41:57',NULL),
	(716,27,'post',160,'like','2026-02-21 08:45:09',NULL),
	(717,25,'post',160,'sad','2026-02-22 10:45:09',NULL),
	(718,28,'post',163,'love','2026-02-06 05:02:19',NULL),
	(719,26,'post',163,'wow','2026-02-05 23:02:19',NULL),
	(720,25,'post',163,'sad','2026-02-09 16:02:19',NULL),
	(721,31,'post',164,'haha','2026-02-16 23:02:19',NULL),
	(722,26,'post',164,'wow','2026-02-15 16:02:19',NULL),
	(723,30,'post',167,'like','2026-02-23 01:02:19',NULL),
	(724,30,'post',170,'sad','2026-02-17 05:02:19',NULL),
	(725,27,'post',170,'wow','2026-02-17 23:02:19',NULL),
	(726,25,'post',170,'wow','2026-02-16 12:02:19',NULL),
	(727,30,'post',171,'sad','2026-02-07 13:02:19',NULL),
	(728,27,'post',173,'wow','2026-02-13 19:02:19',NULL),
	(729,26,'post',174,'angry','2026-01-26 18:02:19',NULL),
	(730,25,'post',174,'wow','2026-01-23 19:02:19',NULL),
	(731,24,'post',174,'love','2026-01-25 05:02:19',NULL),
	(732,30,'post',175,'sad','2026-02-04 22:02:19',NULL),
	(733,25,'post',175,'haha','2026-02-09 01:02:19',NULL),
	(734,31,'post',177,'angry','2026-01-31 06:02:19',NULL),
	(735,30,'post',177,'wow','2026-01-30 14:02:19',NULL),
	(736,24,'post',177,'like','2026-01-30 03:02:19',NULL),
	(737,28,'post',181,'like','2026-02-18 08:02:19',NULL),
	(738,24,'post',181,'love','2026-02-19 03:02:19',NULL),
	(739,31,'post',182,'sad','2026-01-31 04:02:19',NULL),
	(740,31,'post',184,'love','2026-02-04 08:02:19',NULL),
	(741,30,'post',184,'angry','2026-02-05 05:02:19',NULL),
	(742,29,'post',184,'haha','2026-02-06 08:02:19',NULL),
	(743,30,'post',185,'sad','2026-02-05 22:02:19',NULL),
	(744,26,'post',186,'love','2026-02-19 06:02:19',NULL),
	(745,24,'post',186,'like','2026-02-20 14:02:19',NULL),
	(746,25,'post',187,'like','2026-02-11 10:02:19',NULL),
	(747,28,'post',188,'sad','2026-02-20 09:02:19',NULL),
	(748,27,'post',188,'angry','2026-02-20 17:02:19',NULL),
	(749,28,'post',189,'haha','2026-01-25 15:02:19',NULL),
	(750,24,'post',189,'haha','2026-01-23 10:02:19',NULL),
	(751,28,'post',190,'haha','2026-01-27 17:02:19',NULL),
	(752,26,'post',190,'sad','2026-01-27 11:02:19',NULL),
	(753,29,'post',191,'angry','2026-02-03 11:02:19',NULL),
	(754,24,'post',191,'angry','2026-02-05 04:02:19',NULL),
	(755,25,'post',192,'haha','2026-02-02 12:02:19',NULL),
	(756,29,'post',194,'like','2026-02-08 14:02:19',NULL),
	(757,26,'post',194,'like','2026-02-11 08:02:19',NULL),
	(758,25,'post',194,'love','2026-02-10 23:02:19',NULL),
	(759,31,'post',195,'haha','2026-01-23 09:02:19',NULL),
	(760,29,'post',195,'sad','2026-01-25 11:02:19',NULL),
	(761,29,'post',196,'angry','2026-01-28 20:02:19',NULL),
	(762,28,'post',198,'love','2026-02-23 22:02:19',NULL),
	(763,27,'post',198,'love','2026-02-24 01:02:19',NULL),
	(764,24,'post',198,'haha','2026-02-24 02:02:19',NULL),
	(765,31,'post',199,'like','2026-01-29 01:02:19',NULL),
	(766,28,'post',199,'haha','2026-01-28 16:02:19',NULL),
	(767,29,'post',200,'angry','2026-02-20 09:02:19',NULL),
	(768,30,'post',225,'angry','2026-02-12 09:06:01',NULL),
	(769,26,'post',225,'wow','2026-02-15 15:06:01',NULL),
	(775,24,'post',250,'like','2026-02-13 03:16:45',NULL),
	(776,26,'post',269,'haha','2026-02-10 03:16:45',NULL),
	(777,40,'post',273,'wow','2026-02-02 03:16:45',NULL),
	(778,37,'post',233,'love','2026-02-13 03:16:45',NULL),
	(779,41,'post',275,'like','2026-02-15 03:16:45',NULL),
	(780,44,'post',250,'wow','2026-01-27 03:16:45',NULL),
	(781,46,'post',273,'haha','2026-02-09 03:16:45',NULL),
	(782,43,'post',233,'like','2026-02-18 03:16:45',NULL),
	(783,45,'post',275,'love','2026-02-03 03:16:45',NULL),
	(784,28,'post',250,'like','2026-01-31 03:16:45',NULL),
	(785,33,'post',269,'haha','2026-02-05 03:16:45',NULL),
	(786,36,'post',273,'love','2026-01-30 03:16:45',NULL),
	(787,44,'post',233,'love','2026-01-28 03:16:45',NULL),
	(788,25,'post',275,'like','2026-01-26 03:16:45',NULL),
	(789,29,'post',250,'like','2026-02-10 03:16:45',NULL),
	(790,42,'post',269,'like','2026-02-17 03:16:45',NULL),
	(791,49,'post',273,'like','2026-02-03 03:16:45',NULL),
	(792,25,'post',233,'love','2026-01-26 03:16:45',NULL),
	(793,37,'post',275,'haha','2026-02-08 03:16:45',NULL),
	(794,46,'post',250,'haha','2026-02-07 03:16:45',NULL),
	(795,49,'post',269,'like','2026-02-06 03:16:45',NULL),
	(796,43,'post',273,'like','2026-02-17 03:16:45',NULL),
	(797,51,'post',275,'like','2026-01-22 03:16:45',NULL),
	(798,47,'post',269,'like','2026-01-30 03:16:45',NULL),
	(799,35,'post',273,'wow','2026-02-01 03:16:45',NULL),
	(800,32,'post',233,'love','2026-01-28 03:16:45',NULL),
	(801,32,'post',275,'like','2026-01-31 03:16:45',NULL),
	(802,27,'post',250,'love','2026-02-09 03:16:45',NULL),
	(803,44,'post',275,'like','2026-02-10 03:16:45',NULL),
	(804,35,'post',250,'like','2026-02-18 03:16:45',NULL),
	(805,29,'post',269,'haha','2026-01-26 03:16:45',NULL),
	(806,28,'post',273,'like','2026-02-17 03:16:45',NULL),
	(807,40,'post',233,'love','2026-02-10 03:16:45',NULL),
	(808,48,'post',275,'like','2026-02-20 03:16:45',NULL),
	(809,24,'post',273,'like','2026-02-13 03:16:45',NULL),
	(810,26,'post',233,'haha','2026-02-05 03:16:45',NULL),
	(811,30,'post',275,'love','2026-02-10 03:16:45',NULL),
	(812,50,'post',250,'haha','2026-02-03 03:16:45',NULL),
	(813,51,'post',273,'love','2026-02-02 03:16:45',NULL),
	(814,38,'post',233,'haha','2026-02-05 03:16:45',NULL),
	(815,27,'post',275,'like','2026-01-29 03:16:45',NULL),
	(816,45,'post',250,'like','2026-02-14 03:16:45',NULL),
	(817,39,'post',269,'like','2026-02-11 03:16:45',NULL),
	(818,35,'post',233,'haha','2026-02-08 03:16:45',NULL),
	(819,49,'post',250,'like','2026-01-26 03:16:45',NULL),
	(820,32,'post',269,'wow','2026-02-20 03:16:45',NULL),
	(821,26,'post',273,'like','2026-01-28 03:16:45',NULL),
	(822,41,'post',233,'wow','2026-02-08 03:16:45',NULL),
	(823,51,'post',250,'wow','2026-02-18 03:16:45',NULL),
	(824,35,'post',269,'haha','2026-01-30 03:16:45',NULL),
	(825,31,'post',273,'like','2026-02-14 03:16:45',NULL),
	(826,34,'post',233,'like','2026-02-04 03:16:45',NULL),
	(827,43,'post',275,'wow','2026-02-08 03:16:45',NULL),
	(828,32,'post',250,'like','2026-01-25 03:16:45',NULL),
	(829,45,'post',233,'haha','2026-02-13 03:16:45',NULL),
	(830,26,'post',275,'haha','2026-02-06 03:16:45',NULL),
	(831,26,'post',250,'like','2026-01-26 03:16:45',NULL),
	(832,30,'post',269,'love','2026-02-05 03:16:45',NULL),
	(833,39,'post',273,'like','2026-01-31 03:16:45',NULL),
	(834,39,'post',233,'haha','2026-02-16 03:16:45',NULL),
	(835,36,'post',275,'haha','2026-02-04 03:16:45',NULL),
	(838,2,'post',122,'angry','2026-02-20 03:37:13','2026-02-20 03:37:13'),
	(839,1,'post',135,'haha','2026-02-20 07:56:15','2026-02-20 07:56:15'),
	(840,1,'comment',43,'sad','2026-02-20 07:56:22','2026-02-20 07:56:22'),
	(841,2,'post',328,'love','2026-02-20 07:56:54','2026-02-20 07:56:54'),
	(843,1,'post',328,'angry','2026-02-20 07:57:06','2026-02-20 08:03:52'),
	(844,1,'post',110,'wow','2026-02-20 08:05:29','2026-02-20 08:05:29'),
	(845,53,'post',331,'wow','2026-02-19 08:16:13','2026-02-19 08:16:15'),
	(846,53,'comment',47,'sad','2026-02-19 08:16:24','2026-02-19 08:16:24'),
	(847,53,'comment',45,'wow','2026-02-20 09:40:42','2026-02-20 09:40:42'),
	(848,53,'comment',50,'sad','2026-02-20 09:41:10','2026-02-20 09:41:10'),
	(849,2,'post',112,'wow','2026-02-23 01:47:45','2026-02-23 01:47:45');

/*!40000 ALTER TABLE `reactions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table sessions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `id` char(40) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data` blob,
  `expires` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;

INSERT INTO `sessions` (`id`, `created`, `modified`, `data`, `expires`)
VALUES
	(X'3438386137303931393365383133616261333332363230346131363238346232','2026-02-20 07:40:40','2026-02-20 07:40:40',NULL,1771580440);

/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table user_sessions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_sessions`;

CREATE TABLE `user_sessions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `session_id` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `websocket_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_type` enum('desktop','mobile','tablet') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'desktop',
  `device_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser_version` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `os_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `os_version` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT '0',
  `last_activity` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_session` (`session_id`),
  KEY `idx_user_sessions_user_id` (`user_id`),
  KEY `idx_user_sessions_session_id` (`session_id`),
  KEY `idx_user_sessions_websocket_id` (`websocket_id`),
  KEY `idx_user_sessions_last_activity` (`last_activity`),
  KEY `idx_user_sessions_user_activity` (`user_id`,`last_activity` DESC),
  CONSTRAINT `fk_user_sessions_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `user_sessions` WRITE;
/*!40000 ALTER TABLE `user_sessions` DISABLE KEYS */;

INSERT INTO `user_sessions` (`id`, `user_id`, `session_id`, `websocket_id`, `device_type`, `device_name`, `browser_name`, `browser_version`, `os_name`, `os_version`, `ip_address`, `user_agent`, `country`, `city`, `is_current`, `last_activity`, `created_at`)
VALUES
	(9,1,'e90ddd9808d6ed1440e4421018c90768',NULL,'desktop','Apple Macintosh','Firefox','148.0','OS X',NULL,'192.168.65.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0',NULL,NULL,0,'2026-02-20 08:00:02','2026-02-20 08:00:02'),
	(11,2,'6ba3e31fb19d6c41d6335918719a27a8',NULL,'desktop','Apple Macintosh','Chrome','145.0.0.0','OS X',NULL,'192.168.65.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,NULL,0,'2026-02-19 08:10:02','2026-02-19 08:10:02'),
	(12,1,'d8ada297a4455b75ca664e0b25440509',NULL,'desktop','Apple Macintosh','Firefox','148.0','OS X',NULL,'192.168.65.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0',NULL,NULL,0,'2026-02-20 08:19:03','2026-02-20 08:19:03'),
	(13,53,'bb69f3cac7174b62f4efa5619fb89629',NULL,'desktop','Apple Macintosh','Chrome','145.0.0.0','OS X',NULL,'192.168.65.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,NULL,0,'2026-02-20 08:20:10','2026-02-20 08:20:10'),
	(14,53,'e8777b36dba34c03530649ab78105816',NULL,'desktop','Apple Macintosh','Chrome','145.0.0.0','OS X',NULL,'192.168.65.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,NULL,0,'2026-02-20 08:48:07','2026-02-20 08:48:07'),
	(15,1,'429e4ac433ce64a492a46f4150cf3906',NULL,'desktop','Apple Macintosh','Firefox','148.0','OS X',NULL,'192.168.65.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0',NULL,NULL,1,'2026-02-20 09:19:54','2026-02-20 09:19:54'),
	(16,2,'5c8c36c93d0c3f085d514edcbd5a4ef8',NULL,'desktop','Apple Macintosh','Firefox','148.0','OS X',NULL,'192.168.65.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0',NULL,NULL,0,'2026-02-23 01:36:51','2026-02-23 01:36:52'),
	(17,53,'533177330d4262a2f01af2de64c676df',NULL,'desktop','Apple Macintosh','Chrome','145.0.0.0','OS X',NULL,'192.168.65.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',NULL,NULL,1,'2026-02-23 02:27:06','2026-02-23 02:27:06');

/*!40000 ALTER TABLE `user_sessions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('Male','Female','Prefer not to say') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Prefer not to say',
  `birthdate` date DEFAULT NULL,
  `is_birthday_public` tinyint(1) NOT NULL DEFAULT '0',
  `bio` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_username` (`username`),
  KEY `idx_users_gender` (`gender`),
  KEY `idx_users_birthdate` (`birthdate`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `password_hash`, `profile_photo_path`, `gender`, `birthdate`, `is_birthday_public`, `bio`, `website`, `created_at`, `updated_at`)
VALUES
	(1,'Jollibe Dablo','jdabsofficial','jrons.theblue@gmail.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://res.cloudinary.com/dn6rffrwk/image/upload/v1771319664/profilephotos/user_1_d467634d3c6259ab.jpg','Male','2026-02-20',1,'Dream big, move with purpose, and stay grounded. ✨ ','https://github.com/fdcjollibe-intern/','2026-02-12 06:02:04','2026-02-20 04:00:51'),
	(2,'Shane Gamboa','shanegambs','rons.theblue@gmail.com','$argon2id$v=19$m=65536,t=4,p=1$VVdFdlQxQXZjdmtIdVR0Sw$GLcMKqB4S/upz7OUQjE770c6h1F52JgJyj3rHCf9u3U','https://res.cloudinary.com/dn6rffrwk/image/upload/v1771396171/profilephotos/user_2_324ceb7dd024aeaf.png','Female','2026-02-19',1,'Level-headed strategist with a joystick in hand.','','2026-02-13 01:20:52','2026-02-23 01:48:50'),
	(3,'JDabs The Great','jshawttyyy_','jdabs.inquiries@gmail.com','$argon2id$v=19$m=65536,t=4,p=1$L0FkdFpaSVFMZGJkV09vSg$QFOEJumxK+d6t2/CE5cQirofNdLTa93qXkwyeiI9alc',NULL,'Prefer not to say',NULL,0,NULL,NULL,'2026-02-13 07:35:05','2026-02-13 07:35:05'),
	(4,'Cedrick Buster','cedricktaposnapo','cedrick@gmail.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw',NULL,'Prefer not to say',NULL,0,NULL,NULL,'2026-02-18 02:05:55','2026-02-18 06:40:15'),
	(24,'Marcus Reed','marcuscodes','marcus@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/men/32.jpg','Male','1998-06-12',1,'Backend dev. Coffee addict. Scaling things that break.','https://marcus.dev','2026-02-19 09:28:00',NULL),
	(25,'Lena Park','lenabyte','lena@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/women/44.jpg','Female','2000-02-21',0,'Vue + UI nerd. Minimalist.',NULL,'2026-02-19 09:28:00',NULL),
	(26,'Adrian Cruz','adriandev','adrian@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/men/75.jpg','Male','1997-11-04',1,'CakePHP + MySQL guy.',NULL,'2026-02-19 09:28:00',NULL),
	(27,'Sofia Tan','sofiatan','sofia@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/women/68.jpg','Female','1999-08-15',0,'Frontend engineer. UX over everything.','https://sofiatan.dev','2026-02-19 09:28:00',NULL),
	(28,'Ethan Morales','ethanstack','ethan@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/men/12.jpg','Male','1995-03-09',0,'DevOps + Docker wizard.',NULL,'2026-02-19 09:28:00',NULL),
	(29,'Chloe Rivera','chloetech','chloe@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/women/23.jpg','Female','2001-12-01',1,'Fullstack builder. Coffee > sleep.',NULL,'2026-02-19 09:28:00',NULL),
	(30,'Noah Lim','noahbuilds','noah@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/men/41.jpg','Male','1996-07-19',0,'Scaling realtime systems.',NULL,'2026-02-19 09:28:00',NULL),
	(31,'Isabella Gomez','isabellacode','isabella@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/women/52.jpg','Female','1998-09-30',1,'UI animations & micro-interactions.','https://isabellag.dev','2026-02-19 09:28:00',NULL),
	(32,'Daniel Martinez','danmartinez','daniel.martinez@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/men/11.jpg','Male','1996-04-12',0,'Backend engineer focused on scalable APIs and system design.',NULL,'2025-10-23 03:09:32',NULL),
	(33,'Ava Thompson','avathompson','ava.thompson@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/women/22.jpg','Female','1998-09-05',0,'Frontend developer passionate about accessibility and clean UI.','https://avadev.io','2025-11-22 03:09:32',NULL),
	(34,'Michael Chen','michaelchen','michael.chen@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/men/33.jpg','Male','1994-01-19',0,'Cloud infrastructure specialist. DevOps and automation enthusiast.',NULL,'2025-08-04 03:09:32',NULL),
	(35,'Samantha Lee','samanthalee','samantha.lee@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/women/45.jpg','Female','1997-07-21',0,'Product designer building thoughtful digital experiences.',NULL,'2025-12-22 03:09:32',NULL),
	(36,'Ryan Patel','ryanpatel','ryan.patel@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/men/55.jpg','Male','1995-03-14',0,'Fullstack engineer. JavaScript everywhere.',NULL,'2025-09-23 03:09:32',NULL),
	(37,'Olivia Johnson','oliviajohnson','olivia.johnson@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/women/67.jpg','Female','1999-11-30',0,'UI/UX enthusiast exploring micro-interactions.',NULL,'2026-01-06 03:09:32',NULL),
	(38,'Lucas Brown','lucasbrown','lucas.brown@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/men/77.jpg','Male','1993-05-10',0,'Performance-focused backend engineer.',NULL,'2025-08-24 03:09:32',NULL),
	(39,'Emily Davis','emilydavis','emily.davis@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/women/18.jpg','Female','1998-02-28',0,'Building user-first products.',NULL,'2025-12-02 03:09:32',NULL),
	(40,'James Wilson','jameswilson','james.wilson@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/men/21.jpg','Male','1992-08-18',0,'System architect and problem solver.',NULL,'2025-04-26 03:09:32',NULL),
	(41,'Grace Miller','gracemiller','grace.miller@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/women/29.jpg','Female','1997-12-03',0,'Designing intuitive interfaces.',NULL,'2025-11-02 03:09:32',NULL),
	(42,'Ethan Walker','ethanwalker','ethan.walker@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/men/41.jpg','Male','1996-10-10',0,'Scaling distributed systems.',NULL,'2025-12-12 03:09:32',NULL),
	(43,'Chloe Anderson','chloeanderson','chloe.anderson@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/women/35.jpg','Female','1999-06-06',0,'Frontend engineer exploring animations.',NULL,'2026-01-21 03:09:32',NULL),
	(44,'Nathan Scott','nathanscott','nathan.scott@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/men/52.jpg','Male','1995-09-22',0,'Cloud-native developer.',NULL,'2025-10-03 03:09:32',NULL),
	(45,'Lily Turner','lilyturner','lily.turner@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/women/58.jpg','Female','1998-04-04',0,'Passionate about product strategy.',NULL,'2025-12-27 03:09:32',NULL),
	(46,'Benjamin Hall','benjaminhall','benjamin.hall@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/men/63.jpg','Male','1994-03-01',0,'API designer and backend specialist.',NULL,'2025-07-25 03:09:32',NULL),
	(47,'Hannah Young','hannahyoung','hannah.young@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/women/72.jpg','Female','1997-01-15',0,'Design systems advocate.',NULL,'2025-11-17 03:09:32',NULL),
	(48,'Christopher King','christopherking','christopher.king@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/men/85.jpg','Male','1993-12-11',0,'DevOps engineer automating everything.',NULL,'2025-09-03 03:09:32',NULL),
	(49,'Mia Wright','miawright','mia.wright@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/women/81.jpg','Female','1999-05-17',0,'UX researcher turned product designer.',NULL,'2025-12-17 03:09:32',NULL),
	(50,'Andrew Lopez','andrewlopez','andrew.lopez@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/men/90.jpg','Male','1995-02-07',0,'Building scalable microservices.',NULL,'2025-10-18 03:09:32',NULL),
	(51,'Sophia Harris','sophiaharris','sophia.harris@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/women/88.jpg','Female','1998-08-08',0,'Frontend architect and design enthusiast.',NULL,'2026-01-11 03:09:32',NULL),
	(52,'Shane Dumps','shanegambs1','shanegambs1@gmail.com','$argon2id$v=19$m=65536,t=4,p=1$eEN4OVZ0SWZWYXdmTTF6cw$uwsTwRDP5Z+NcyQMVnysrRPSpObnRKaZaABSxtYV4gU','https://res.cloudinary.com/dn6rffrwk/image/upload/v1771569642/temp-profile_x0tfur.jpg','Prefer not to say',NULL,0,NULL,NULL,'2026-02-20 06:32:38','2026-02-20 06:41:24'),
	(53,'MR Dong','sirdong','sirdong@gmail.com','$argon2id$v=19$m=65536,t=4,p=1$MlBtM0g2WVNJTXNIZTNMQw$bqloJrmscEA5e/yimwCtzq8xIE3t/tic/c/ZuIWa1OE','https://res.cloudinary.com/dn6rffrwk/image/upload/v1771814373/profilephotos/user_53_a08fe35c8cbac62e.jpg','Male',NULL,0,NULL,NULL,'2026-02-19 08:13:49','2026-02-23 02:39:33');

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
