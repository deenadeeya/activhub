-- Add rejection_remarks column to cocu_activities table
ALTER TABLE `cocu_activities` ADD COLUMN `rejection_remarks` TEXT DEFAULT NULL AFTER `approved_at`;
