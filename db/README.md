# WeLinked Database Schema Documentation

## Overview
This is the complete database schema for WeLinked - a social media platform built with PHP CakePHP and MySQL in Docker.

---

## Quick Start

### 1. Import the Database
```bash
# From your Docker container
docker exec -i your_mysql_container mysql -u root -p < welinked_complete_schema.sql

# Or from MySQL command line
mysql -u root -p < welinked_complete_schema.sql
```

### 2. Verify Tables
```sql
USE welinked_db;
SHOW TABLES;
```

You should see 10 tables:
- users
- posts
- comments
- likes
- friendships
- reactions
- post_attachments
- comment_attachments
- activities

---

## Database Structure

### Core Tables (Your Original Schema)

#### **users**
Stores user account information.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Unique user ID |
| full_name | VARCHAR(150) | User's full name |
| username | VARCHAR(50) | Unique username |
| email | VARCHAR(100) | Unique email |
| password_hash | VARCHAR(255) | Hashed password |
| profile_photo_path | VARCHAR(255) | Path to profile photo |
| created_at | DATETIME | Account creation date |
| updated_at | DATETIME | Last update date |

**Example:**
```sql
INSERT INTO users (full_name, username, email, password_hash)
VALUES ('John Doe', 'johndoe', 'john@example.com', 'hashed_password_here');
```

---

#### **posts**
Main posts/threads created by users.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Unique post ID |
| user_id | BIGINT | Who created the post |
| content_text | TEXT | Post text (optional) |
| content_image_path | VARCHAR(255) | Legacy single image (optional) |
| created_at | DATETIME | When posted |
| updated_at | DATETIME | Last update |
| deleted_at | DATETIME | Soft delete timestamp |

**Note:** Use `post_attachments` table for multiple images/videos instead of `content_image_path`.

---

#### **comments**
Comments on posts.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Unique comment ID |
| post_id | BIGINT | Which post this belongs to |
| user_id | BIGINT | Who commented |
| content_text | TEXT | Comment text (optional) |
| content_image_path | VARCHAR(255) | Legacy single image (optional) |
| created_at | DATETIME | When commented |
| updated_at | DATETIME | Last update |
| deleted_at | DATETIME | Soft delete timestamp |

**Note:** Use `comment_attachments` table for images/videos instead of `content_image_path`.

---

#### **likes**
Legacy simple likes table (kept for backward compatibility).

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Unique like ID |
| user_id | BIGINT | Who liked |
| target_type | ENUM | 'post' or 'comment' |
| target_id | BIGINT | ID of post/comment |
| created_at | DATETIME | When liked |

**Note:** Consider using the new `reactions` table for emoji reactions.

---

### New Feature Tables

#### **friendships**
Manages who follows who.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Unique friendship ID |
| follower_id | BIGINT | User who follows |
| following_id | BIGINT | User being followed |
| created_at | DATETIME | When followed |

**How It Works:**
- User A follows User B → Insert: `(follower_id=A, following_id=B)`
- User A unfollows User B → Delete that row
- Unique key prevents duplicate follows

**Example:**
```sql
-- User 1 follows User 2
INSERT INTO friendships (follower_id, following_id)
VALUES (1, 2);

-- Check if User 1 follows User 2
SELECT * FROM friendships
WHERE follower_id = 1 AND following_id = 2;

-- Unfollow
DELETE FROM friendships
WHERE follower_id = 1 AND following_id = 2;
```

---

#### **reactions**
Emoji reactions on posts and comments.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Unique reaction ID |
| user_id | BIGINT | Who reacted |
| target_type | ENUM | 'post' or 'comment' |
| target_id | BIGINT | ID of post/comment |
| reaction_type | ENUM | like, haha, love, wow, sad, angry |
| created_at | DATETIME | When reacted |
| updated_at | DATETIME | If changed reaction |

**Reaction Types:**
- `like` → ❤️ Like
- `haha` → 😂 Haha
- `love` → 🥰 Love it
- `wow` → 😯 Wow
- `sad` → 😢 Sad
- `angry` → 😡 Angry

**Rules:**
- One reaction per user per post/comment (enforced by unique key)
- Changing reaction updates the same row
- Removing reaction deletes the row

**Example:**
```sql
-- Add a reaction
INSERT INTO reactions (user_id, target_type, target_id, reaction_type)
VALUES (1, 'post', 5, 'love')
ON DUPLICATE KEY UPDATE reaction_type = 'love', updated_at = NOW();

-- Change reaction
UPDATE reactions
SET reaction_type = 'haha', updated_at = NOW()
WHERE user_id = 1 AND target_type = 'post' AND target_id = 5;

-- Remove reaction
DELETE FROM reactions
WHERE user_id = 1 AND target_type = 'post' AND target_id = 5;

-- Get reaction counts
SELECT reaction_type, COUNT(*) as count
FROM reactions
WHERE target_type = 'post' AND target_id = 5
GROUP BY reaction_type;
```

---

#### **post_attachments**
Multiple images/videos per post.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Unique attachment ID |
| post_id | BIGINT | Which post this belongs to |
| file_path | VARCHAR(255) | Path to file |
| file_type | ENUM | 'image' or 'video' |
| file_size | BIGINT | Size in bytes (max 250MB) |
| display_order | TINYINT | Gallery order (0, 1, 2...) |
| upload_status | ENUM | uploading, completed, failed |
| created_at | DATETIME | When uploaded |

**How It Works:**
- One post can have many attachments
- `display_order` controls the gallery sequence (left to right)
- `upload_status` tracks progress for UI
- Max file size: 250MB = 262,144,000 bytes

**Example:**
```sql
-- Add first image to post
INSERT INTO post_attachments (post_id, file_path, file_type, file_size, display_order, upload_status)
VALUES (1, '/uploads/posts/image1.jpg', 'image', 2048000, 0, 'completed');

-- Add second image
INSERT INTO post_attachments (post_id, file_path, file_type, file_size, display_order, upload_status)
VALUES (1, '/uploads/posts/image2.jpg', 'image', 1536000, 1, 'completed');

-- Get attachments in order
SELECT * FROM post_attachments
WHERE post_id = 1
ORDER BY display_order ASC;
```

---

#### **comment_attachments**
Single image or video per comment.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Unique attachment ID |
| comment_id | BIGINT | Which comment this belongs to |
| file_path | VARCHAR(255) | Path to file |
| file_type | ENUM | 'image' or 'video' |
| file_size | BIGINT | Size in bytes (max 250MB) |
| upload_status | ENUM | uploading, completed, failed |
| created_at | DATETIME | When uploaded |

**How It Works:**
- One comment = one attachment max (enforced by unique key)
- Similar to post attachments but limited to 1 per comment

**Example:**
```sql
-- Add attachment to comment
INSERT INTO comment_attachments (comment_id, file_path, file_type, file_size, upload_status)
VALUES (10, '/uploads/comments/video1.mp4', 'video', 15360000, 'completed');
```

---

#### **activities**
Activity feed/notifications for users.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Unique activity ID |
| user_id | BIGINT | Who will see this notification |
| actor_id | BIGINT | Who did the action |
| activity_type | ENUM | follow, reaction, comment, post |
| target_type | ENUM | user, post, comment |
| target_id | BIGINT | ID of target |
| is_read | BOOLEAN | Has user seen it? |
| created_at | DATETIME | When it happened |

**Activity Types:**
- `follow` → "John followed you"
- `reaction` → "Jane loved your post"
- `comment` → "Mike commented on your post"
- `post` → "Sarah posted something" (if you follow them)

**Example:**
```sql
-- User 2 followed User 1 → Create notification for User 1
INSERT INTO activities (user_id, actor_id, activity_type, target_type, target_id)
VALUES (1, 2, 'follow', 'user', 1);

-- User 3 reacted to User 1's post (post ID 5)
INSERT INTO activities (user_id, actor_id, activity_type, target_type, target_id)
VALUES (1, 3, 'reaction', 'post', 5);

-- Get unread notifications
SELECT a.*, u.username, u.full_name
FROM activities a
INNER JOIN users u ON a.actor_id = u.id
WHERE a.user_id = 1 AND a.is_read = FALSE
ORDER BY a.created_at DESC;

-- Mark as read
UPDATE activities
SET is_read = TRUE
WHERE user_id = 1 AND id IN (1, 2, 3);
```

---

## Common Queries for Your CakePHP App

### Feed Queries

**1. "For You" Feed (Mixed Posts)**
Shows posts from friends + random users, randomized.

```sql
SELECT p.*, u.username, u.full_name, u.profile_photo_path
FROM posts p
INNER JOIN users u ON p.user_id = u.id
LEFT JOIN friendships f ON p.user_id = f.following_id AND f.follower_id = 1
WHERE p.deleted_at IS NULL
ORDER BY RAND()
LIMIT 20 OFFSET 0;
```

**2. "Friends" Feed**
Shows only posts from users you follow.

```sql
SELECT p.*, u.username, u.full_name, u.profile_photo_path
FROM posts p
INNER JOIN users u ON p.user_id = u.id
INNER JOIN friendships f ON p.user_id = f.following_id
WHERE f.follower_id = 1 AND p.deleted_at IS NULL
ORDER BY p.created_at DESC
LIMIT 20 OFFSET 0;
```

**3. Load More Posts (Pagination)**
For infinite scroll, increment OFFSET by 20 each time.

```sql
-- First load: OFFSET 0
-- Second load: OFFSET 20
-- Third load: OFFSET 40
-- And so on...
```

---

### Post Details Queries

**4. Get Post with All Attachments**
```sql
SELECT p.*, pa.id as attachment_id, pa.file_path, pa.file_type, pa.display_order
FROM posts p
LEFT JOIN post_attachments pa ON p.id = pa.post_id
WHERE p.id = 5 AND p.deleted_at IS NULL
ORDER BY pa.display_order ASC;
```

**5. Get Reaction Counts for a Post**
```sql
SELECT reaction_type, COUNT(*) as count
FROM reactions
WHERE target_type = 'post' AND target_id = 5
GROUP BY reaction_type;
```

**6. Check User's Reaction on a Post**
```sql
SELECT reaction_type
FROM reactions
WHERE user_id = 1 AND target_type = 'post' AND target_id = 5;
```

**7. Get Who Reacted to a Post**
```sql
SELECT u.username, u.full_name, u.profile_photo_path, r.reaction_type
FROM reactions r
INNER JOIN users u ON r.user_id = u.id
WHERE r.target_type = 'post' AND r.target_id = 5
ORDER BY r.created_at DESC;
```

---

### Comment Queries

**8. Get Comments for a Post**
```sql
SELECT c.*, u.username, u.full_name, u.profile_photo_path,
       ca.file_path, ca.file_type
FROM comments c
INNER JOIN users u ON c.user_id = u.id
LEFT JOIN comment_attachments ca ON c.id = ca.comment_id
WHERE c.post_id = 5 AND c.deleted_at IS NULL
ORDER BY c.created_at ASC;
```

---

### Friendship Queries

**9. Get Suggested Users to Follow**
Shows users you're not following yet.

```sql
SELECT u.id, u.username, u.full_name, u.profile_photo_path
FROM users u
WHERE u.id NOT IN (
    SELECT following_id FROM friendships WHERE follower_id = 1
)
AND u.id != 1
LIMIT 10;
```

**10. Check if Following a User**
```sql
SELECT id FROM friendships
WHERE follower_id = 1 AND following_id = 2;
```

**11. Get My Following List**
```sql
SELECT u.id, u.username, u.full_name, u.profile_photo_path
FROM users u
INNER JOIN friendships f ON u.id = f.following_id
WHERE f.follower_id = 1
ORDER BY f.created_at DESC;
```

**12. Get My Followers**
```sql
SELECT u.id, u.username, u.full_name, u.profile_photo_path
FROM users u
INNER JOIN friendships f ON u.id = f.follower_id
WHERE f.following_id = 1
ORDER BY f.created_at DESC;
```

---

### Activity/Notification Queries

**13. Get Recent Activities**
```sql
SELECT a.*, u.username, u.full_name, u.profile_photo_path
FROM activities a
INNER JOIN users u ON a.actor_id = u.id
WHERE a.user_id = 1
ORDER BY a.created_at DESC
LIMIT 20;
```

**14. Get Unread Activity Count**
```sql
SELECT COUNT(*) as unread_count
FROM activities
WHERE user_id = 1 AND is_read = FALSE;
```

---

## Relationship Diagram

```
users (the main table)
├── posts (user creates many posts)
│   ├── post_attachments (post has many images/videos)
│   ├── reactions (post has many reactions)
│   └── comments (post has many comments)
│       ├── comment_attachments (comment has one attachment)
│       └── reactions (comment has many reactions)
│
├── friendships (user follows many users)
│   ├── follower_id → users.id
│   └── following_id → users.id
│
└── activities (user receives many notifications)
    ├── user_id → who sees the notification
    └── actor_id → who did the action
```

---

## Important Notes

### File Size Limits
- Max attachment size: **250MB** = 262,144,000 bytes
- Validate this in your PHP code before inserting

### Soft Deletes
- `posts` and `comments` use `deleted_at` for soft deletes
- Always check `WHERE deleted_at IS NULL` when querying

### Unique Constraints
- Users cannot follow the same person twice
- Users cannot react twice to the same post/comment
- Comments can only have one attachment

### Upload Progress
- Use `upload_status` to track: uploading → completed → failed
- Update status after each chunk/completion

### Pagination
- Load 20 posts at a time
- Start fetching more when user scrolls to 15th post
- Use OFFSET for pagination: 0, 20, 40, 60...

---

## CakePHP Model Associations (Quick Reference)

```php
// User Model
$this->hasMany('Posts');
$this->hasMany('Comments');
$this->hasMany('Reactions');
$this->hasMany('FollowingFriendships', [
    'className' => 'Friendships',
    'foreignKey' => 'follower_id'
]);
$this->hasMany('FollowerFriendships', [
    'className' => 'Friendships',
    'foreignKey' => 'following_id'
]);

// Post Model
$this->belongsTo('Users');
$this->hasMany('PostAttachments');
$this->hasMany('Comments');
$this->hasMany('Reactions', [
    'conditions' => ['target_type' => 'post'],
    'foreignKey' => 'target_id'
]);

// Comment Model
$this->belongsTo('Posts');
$this->belongsTo('Users');
$this->hasOne('CommentAttachments');
$this->hasMany('Reactions', [
    'conditions' => ['target_type' => 'comment'],
    'foreignKey' => 'target_id'
]);
```

---

## Troubleshooting

### Issue: Foreign key constraint fails
**Solution:** Make sure parent records exist before inserting child records.
Example: Create user before creating post.

### Issue: Duplicate entry error
**Solution:** Check unique constraints. User trying to follow twice or react twice.

### Issue: Slow queries
**Solution:** Indexes are already added. If still slow:
1. Check EXPLAIN query
2. Add composite indexes if needed
3. Consider caching frequently accessed data

### Issue: Can't see posts in feed
**Solution:** 
1. Check if posts have `deleted_at = NULL`
2. Verify friendship records exist for "Friends" feed
3. Check user_id foreign keys are correct

---

## Need Help?

1. Check this README first
2. Review the commented SQL file
3. Test queries in MySQL Workbench before coding
4. Use `EXPLAIN` to debug slow queries

---

## Version History

- **v1.0** (2026-02-12) - Initial complete schema
  - Core tables: users, posts, comments, likes
  - New features: friendships, reactions, attachments, activities

---

**Good luck with your WeLinked project!** 🚀
