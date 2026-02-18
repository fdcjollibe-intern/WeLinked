-- ============================================
-- Migration: Add actor_id composite index for notifications
-- Date: 2026-02-19
-- Author: System Optimization
-- Description: Add index to optimize reaction notification deletion/updates
-- ============================================

-- Add composite index for actor-based notification queries
-- This optimizes deleteReactionNotification() and updateReactionNotification() queries
CREATE INDEX idx_notifications_actor_target ON notifications(actor_id, type, target_type, target_id);

-- This index improves performance for:
-- 1. Finding existing reaction notifications when changing reaction type
-- 2. Deleting reaction notifications when user removes their reaction
-- Query pattern: WHERE actor_id = X AND type = 'reaction' AND target_type = Y AND target_id = Z
