-- database/schema.sql (Final v1)
-- SQL Script for creating the initial database structure for the Song Review App.
-- Run this script manually in your target database after it has been created.

-- Select the database first if running this interactively (e.g., in MySQL client):
-- USE your_database_name; -- Replace with the actual database name

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
-- Stores individual feedback submissions from users.
--

-- Optional: Drop the table first if you need to recreate it during development.
-- WARNING: This deletes all existing data in the table!
-- DROP TABLE IF EXISTS feedback;

CREATE TABLE IF NOT EXISTS feedback (
    -- `id`: Primary key, automatically increments for each new row. Ensures uniqueness.
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- `song_id`: A string identifier for the song being reviewed (e.g., 'help_me', 'track_02').
    -- This allows the table to store feedback for multiple songs in the future.
    -- NOT NULL constraint ensures this value is always provided.
    song_id VARCHAR(100) NOT NULL COMMENT 'Identifier for the song being reviewed',

    -- `chosen_version`: The specific version identifier the user preferred (e.g., 'v3', 'v4', 'mix_b').
    -- NOT NULL constraint ensures the core vote is always recorded.
    chosen_version VARCHAR(50) NOT NULL COMMENT 'Version identifier preferred by the user',

    -- `feedback_text`: Optional free-text comments provided by the user.
    -- TEXT type allows for longer comments. NULL indicates the field is optional.
    feedback_text TEXT NULL COMMENT 'Optional textual feedback from the user',

    -- `submitted_at`: Timestamp automatically set to the date and time when the feedback row was inserted.
    -- Useful for tracking when feedback was received and for ordering results.
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp when the feedback was submitted'

)
-- `ENGINE=InnoDB`: Specifies the storage engine. InnoDB is the default and recommended
-- engine for general use, supporting transactions, foreign keys, etc.
ENGINE=InnoDB
-- `DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`: Sets the default character set
-- and collation for the table. utf8mb4 is recommended for full Unicode support,
-- allowing storage of emojis and various international characters correctly.
DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
-- Optional: Add a table comment for clarity.
COMMENT='Stores user feedback and votes on song versions';

-- --------------------------------------------------------

-- Optional: Add indexes after table creation if needed for performance.
-- CREATE INDEX idx_song_id ON feedback (song_id);
-- CREATE INDEX idx_chosen_version ON feedback (chosen_version);
-- CREATE INDEX idx_submitted_at ON feedback (submitted_at);

-- End of schema definition for V1.
