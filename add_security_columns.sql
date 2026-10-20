-- SQL Migration: Add 3 Security Question/Answer column pairs to user table
-- Run this script to add the required columns for the 3-question verification system

ALTER TABLE `user` 
ADD COLUMN IF NOT EXISTS `security_question_1` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `security_answer_1` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `security_question_2` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `security_answer_2` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `security_question_3` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `security_answer_3` VARCHAR(255) NULL;

-- Verify the columns were added
-- DESCRIBE `user`;

