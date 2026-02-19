-- Add is_reel column to posts table
-- This column marks whether a post should be displayed as a reel
-- A post is considered a reel when it has exactly 1 video attachment

ALTER TABLE posts 
ADD COLUMN is_reel BOOLEAN NULL DEFAULT NULL
COMMENT 'Whether this post should be displayed as a reel (true for posts with single video)';

-- Add index for efficient reel filtering
CREATE INDEX idx_posts_is_reel ON posts(is_reel) WHERE is_reel = TRUE;

-- Optionally, backfill existing posts with 1 video as reels
-- This can be run manually if you want to mark existing posts
-- UPDATE posts p
-- SET is_reel = TRUE
-- WHERE EXISTS (
--     SELECT 1
--     FROM post_attachments pa
--     WHERE pa.post_id = p.id
--       AND pa.file_type = 'video'
--       AND pa.upload_status = 'completed'
--     GROUP BY pa.post_id
--     HAVING COUNT(*) = 1
-- )
-- AND NOT EXISTS (
--     SELECT 1
--     FROM post_attachments pa2
--     WHERE pa2.post_id = p.id
--       AND pa2.file_type != 'video'
-- );
