-- Add gallery_folder_id to posts table
ALTER TABLE posts ADD COLUMN gallery_folder_id UUID REFERENCES media_folders(id) ON DELETE SET NULL;
