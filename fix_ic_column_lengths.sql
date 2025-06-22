-- SQL script to fix IC and phone number field lengths
-- This will extend all IC fields from varchar(20) to varchar(50) to prevent truncation
-- and also fix the contact_number field in events table

-- Fix IC fields in cocurricular_groups table
ALTER TABLE cocurricular_groups 
MODIFY COLUMN advisor_ic VARCHAR(50),
MODIFY COLUMN president_ic VARCHAR(50),
MODIFY COLUMN vice_president_ic VARCHAR(50),
MODIFY COLUMN secretary_ic VARCHAR(50),
MODIFY COLUMN treasurer_ic VARCHAR(50),
MODIFY COLUMN vice_secretary_ic VARCHAR(50),
MODIFY COLUMN vice_treasurer_ic VARCHAR(50),
MODIFY COLUMN exco_y6_ic VARCHAR(50),
MODIFY COLUMN exco_y5_ic VARCHAR(50),
MODIFY COLUMN exco_y4_ic VARCHAR(50);

-- Fix contact_number field in events table
ALTER TABLE events 
MODIFY COLUMN contact_number VARCHAR(50);
