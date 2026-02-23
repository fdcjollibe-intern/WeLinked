-- Fix is_reel values for existing posts
-- Run this migration to correct any posts that have incorrect is_reel values
-- Date: 2026-02-23

-- Step 1: Mark posts with exactly 1 video attachment as reels (is_reel = TRUE)
UPDATE posts p
INNER JOIN (
    SELECT post_id, COUNT(*) as total_count, SUM(CASE WHEN file_type = 'video' THEN 1 ELSE 0 END) as video_count
    FROM post_attachments
    WHERE upload_status = 'completed'
    GROUP BY post_id
    HAVING total_count = 1 AND video_count = 1
) AS reel_posts ON p.id = reel_posts.post_id
SET p.is_reel = TRUE
WHERE p.deleted_at IS NULL;

-- Step 2: Mark all other posts as NOT reels (is_reel = FALSE)
-- This includes posts with:
-- - No attachments
-- - Multiple attachments
-- - Only image attachments
-- - Mixed media (images + videos)
UPDATE posts p
SET p.is_reel = FALSE
WHERE p.deleted_at IS NULL
  AND (p.is_reel IS NULL OR p.is_reel != TRUE)
  AND p.id NOT IN (
    SELECT post_id
    FROM post_attachments
    WHERE upload_status = 'completed'
    GROUP BY post_id
    HAVING COUNT(*) = 1 AND SUM(CASE WHEN file_type = 'video' THEN 1 ELSE 0 END) = 1
  );

-- Verification queries (run these to check the results):
-- SELECT is_reel, COUNT(*) FROM posts WHERE deleted_at IS NULL GROUP BY is_reel;
-- SELECT p.id, p.is_reel, COUNT(pa.id) as attachment_count, SUM(CASE WHEN pa.file_type = 'video' THEN 1 ELSE 0 END) as video_count 
-- FROM posts p 
-- LEFT JOIN post_attachments pa ON p.id = pa.post_id AND pa.upload_status = 'completed'
-- WHERE p.deleted_at IS NULL 
-- GROUP BY p.id, p.is_reel
-- ORDER BY p.created_at DESC
-- LIMIT 20;
