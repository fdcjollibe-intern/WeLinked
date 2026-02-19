# ************************************************************
# Sequel Ace SQL dump
# Version 20096
#
# https://sequel-ace.com/
# https://github.com/Sequel-Ace/Sequel-Ace
#
# Host: 127.0.0.1 (MySQL 8.0.45)
# Database: welinked_db
# Generation Time: 2026-02-19 10:12:11 +0000
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `birthday_messages` WRITE;
/*!40000 ALTER TABLE `birthday_messages` DISABLE KEYS */;

INSERT INTO `birthday_messages` (`id`, `sender_id`, `recipient_id`, `message`, `is_read`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	(10,1,2,'wassup yo bithc',1,'2026-02-19 09:31:49','2026-02-19 09:32:07',NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content_text`, `content_image_path`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	(42,161,2,'asdasd',NULL,'2026-02-19 10:09:16','2026-02-19 10:09:16',NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `friendships` WRITE;
/*!40000 ALTER TABLE `friendships` DISABLE KEYS */;

INSERT INTO `friendships` (`id`, `follower_id`, `following_id`, `created_at`)
VALUES
	(20,1,3,'2026-02-17 09:34:52'),
	(22,2,1,'2026-02-18 02:19:55'),
	(23,1,2,'2026-02-18 07:43:13'),
	(24,2,3,'2026-02-18 07:46:55'),
	(25,2,4,'2026-02-18 07:46:57'),
	(27,2,24,'2026-02-19 09:33:17'),
	(28,2,26,'2026-02-19 09:33:17'),
	(29,2,28,'2026-02-19 09:33:18'),
	(30,2,27,'2026-02-19 09:33:19'),
	(31,2,25,'2026-02-19 09:33:19');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `post_attachments` WRITE;
/*!40000 ALTER TABLE `post_attachments` DISABLE KEYS */;

INSERT INTO `post_attachments` (`id`, `post_id`, `file_path`, `file_type`, `file_size`, `display_order`, `upload_status`, `created_at`)
VALUES
	(83,159,'https://res.cloudinary.com/dn6rffrwk/image/upload/v1771494112/posts/user_2_665d2aa75ab9f9ccd5b8508f.jpg','image',13809,0,'completed','2026-02-19 09:41:57'),
	(84,159,'https://res.cloudinary.com/dn6rffrwk/image/upload/v1771494114/posts/user_2_41a0ac90f851c77ee8ee3c6f.jpg','image',14917,1,'completed','2026-02-19 09:41:57'),
	(85,159,'https://res.cloudinary.com/dn6rffrwk/image/upload/v1771494116/posts/user_2_379154f70c354c2cb6f41786.jpg','image',31722,2,'completed','2026-02-19 09:41:57'),
	(86,160,'https://res.cloudinary.com/dn6rffrwk/image/upload/v1771494302/posts/user_2_175d844572202490374918c6.jpg','image',12132,0,'completed','2026-02-19 09:45:09'),
	(87,160,'https://res.cloudinary.com/dn6rffrwk/image/upload/v1771494304/posts/user_2_1e098afe3aef8d6876f3d71a.jpg','image',22330,1,'completed','2026-02-19 09:45:10'),
	(88,160,'https://res.cloudinary.com/dn6rffrwk/image/upload/v1771494306/posts/user_2_47f35b72c59c4a0a8a9708e3.jpg','image',15422,2,'completed','2026-02-19 09:45:10'),
	(89,160,'https://res.cloudinary.com/dn6rffrwk/image/upload/v1771494308/posts/user_2_0a587066a616ee6a7b8f71c1.jpg','image',19461,3,'completed','2026-02-19 09:45:10'),
	(90,161,'https://res.cloudinary.com/dn6rffrwk/video/upload/v1771495639/posts/user_2_bf1bb7628d705d902b7c3d12.mp4','video',2784710,0,'completed','2026-02-19 10:07:31');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
	(159,2,'','https://res.cloudinary.com/dn6rffrwk/image/upload/v1771494112/posts/user_2_665d2aa75ab9f9ccd5b8508f.jpg',NULL,'2026-02-19 09:41:57','2026-02-19 09:41:57',NULL,NULL),
	(160,2,'','https://res.cloudinary.com/dn6rffrwk/image/upload/v1771494302/posts/user_2_175d844572202490374918c6.jpg',NULL,'2026-02-19 09:45:09','2026-02-19 09:45:09',NULL,NULL),
	(161,2,'creative ideas boi ','https://res.cloudinary.com/dn6rffrwk/video/upload/v1771495639/posts/user_2_bf1bb7628d705d902b7c3d12.mp4',NULL,'2026-02-19 10:07:31','2026-02-19 10:07:31',NULL,1);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `reactions` WRITE;
/*!40000 ALTER TABLE `reactions` DISABLE KEYS */;

INSERT INTO `reactions` (`id`, `user_id`, `target_type`, `target_id`, `reaction_type`, `created_at`, `updated_at`)
VALUES
	(128,2,'post',160,'love','2026-02-19 09:49:57','2026-02-19 09:49:57'),
	(129,26,'post',109,'haha','2026-02-20 06:32:28',NULL),
	(130,28,'post',109,'angry','2026-02-21 06:32:28',NULL),
	(131,29,'post',110,'angry','2026-02-20 05:32:28',NULL),
	(132,31,'post',110,'angry','2026-02-20 00:32:28',NULL),
	(136,2,'post',161,'like','2026-02-19 10:09:13','2026-02-19 10:09:13');

/*!40000 ALTER TABLE `reactions` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `password_hash`, `profile_photo_path`, `gender`, `birthdate`, `is_birthday_public`, `bio`, `website`, `created_at`, `updated_at`)
VALUES
	(1,'Jollibe Dablo','jdabsofficial','jrons.theblue@gmail.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://res.cloudinary.com/dn6rffrwk/image/upload/v1771319664/profilephotos/user_1_d467634d3c6259ab.jpg','Male',NULL,0,'Dream big, move with purpose, and stay grounded. ✨ ','https://github.com/fdcjollibe-intern/','2026-02-12 06:02:04','2026-02-17 09:14:25'),
	(2,'Shane Gamboa','shanegambs','rons.theblue@gmail.com','$argon2id$v=19$m=65536,t=4,p=1$VVdFdlQxQXZjdmtIdVR0Sw$GLcMKqB4S/upz7OUQjE770c6h1F52JgJyj3rHCf9u3U','https://res.cloudinary.com/dn6rffrwk/image/upload/v1771396171/profilephotos/user_2_324ceb7dd024aeaf.png','Male','2026-02-19',1,'Level-headed strategist with a joystick in hand.','','2026-02-13 01:20:52','2026-02-19 05:34:51'),
	(3,'JDabs The Great','jshawttyyy_','jdabs.inquiries@gmail.com','$argon2id$v=19$m=65536,t=4,p=1$L0FkdFpaSVFMZGJkV09vSg$QFOEJumxK+d6t2/CE5cQirofNdLTa93qXkwyeiI9alc',NULL,'Prefer not to say',NULL,0,NULL,NULL,'2026-02-13 07:35:05','2026-02-13 07:35:05'),
	(4,'Cedrick Buster','cedricktaposnapo','cedrick@gmail.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw',NULL,'Prefer not to say',NULL,0,NULL,NULL,'2026-02-18 02:05:55','2026-02-18 06:40:15'),
	(24,'Marcus Reed','marcuscodes','marcus@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/men/32.jpg','Male','1998-06-12',1,'Backend dev. Coffee addict. Scaling things that break.','https://marcus.dev','2026-02-19 09:28:00',NULL),
	(25,'Lena Park','lenabyte','lena@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/women/44.jpg','Female','2000-02-21',0,'Vue + UI nerd. Minimalist.',NULL,'2026-02-19 09:28:00',NULL),
	(26,'Adrian Cruz','adriandev','adrian@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/men/75.jpg','Male','1997-11-04',1,'CakePHP + MySQL guy.',NULL,'2026-02-19 09:28:00',NULL),
	(27,'Sofia Tan','sofiatan','sofia@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/women/68.jpg','Female','1999-08-15',0,'Frontend engineer. UX over everything.','https://sofiatan.dev','2026-02-19 09:28:00',NULL),
	(28,'Ethan Morales','ethanstack','ethan@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/men/12.jpg','Male','1995-03-09',0,'DevOps + Docker wizard.',NULL,'2026-02-19 09:28:00',NULL),
	(29,'Chloe Rivera','chloetech','chloe@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/women/23.jpg','Female','2001-12-01',1,'Fullstack builder. Coffee > sleep.',NULL,'2026-02-19 09:28:00',NULL),
	(30,'Noah Lim','noahbuilds','noah@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/men/41.jpg','Male','1996-07-19',0,'Scaling realtime systems.',NULL,'2026-02-19 09:28:00',NULL),
	(31,'Isabella Gomez','isabellacode','isabella@example.com','$argon2id$v=19$m=65536,t=4,p=1$SDJDekN4TFppazVPOHJNLg$a+FhkFRY/4HortquKTnWRoWNMzSyicezsp7b0MM1DBw','https://randomuser.me/api/portraits/women/52.jpg','Female','1998-09-30',1,'UI animations & micro-interactions.','https://isabellag.dev','2026-02-19 09:28:00',NULL);

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
