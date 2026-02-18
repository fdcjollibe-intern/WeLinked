-- ============================================
-- Migration: Optimize notification queries for comment deletion
-- Date: 2026-02-19
-- Author: System Optimization
-- Description: Add composite index for efficient comment notification deletion
-- ============================================

-- Add composite index for type+target_type+target_id+actor_id queries
-- This optimizes deletion of specific comment notifications when comments are deleted
-- Query pattern: WHERE type='comment' AND target_type='post' AND target_id=X AND actor_id=Y
CREATE INDEX idx_notifications_type_target_actor ON notifications(type, target_type, target_id, actor_id);

-- This index improves performance for:
-- 1. Deleting the "User commented on your post" notification when comment is deleted
-- 2. Finding notifications by specific type and target combination
-- 3. Matching notifications by actor for specific actions
