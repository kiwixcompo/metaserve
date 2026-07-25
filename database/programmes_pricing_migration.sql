-- Delete old programmes
DELETE FROM `programmes`;
ALTER TABLE `programmes` AUTO_INCREMENT = 1;

-- Insert the new programmes
INSERT INTO `programmes` (`name`, `description`, `cost`, `duration_weeks`, `is_active`) VALUES
('Mandatory Computer Induction Course', 'Introduction to computers, Microsoft Word, Excel, PowerPoint, Internet, email', 20000.00, 4, 1),
('Professional Upskilling Programme', 'Advanced skills in Data Analytics, Web Development, Cloud Computing, etc.', 40000.00, 12, 1);
