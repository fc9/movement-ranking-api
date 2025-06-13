-- SQLite version of the database schema for local testing

-- Create tables
CREATE TABLE IF NOT EXISTS `user` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `name` TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS `movement` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `name` TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS `personal_record` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `user_id` INTEGER NOT NULL,
    `movement_id` INTEGER NOT NULL,
    `value` REAL NOT NULL,
    `date` TEXT NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`movement_id`) REFERENCES `movement`(`id`) ON DELETE CASCADE
);

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS `idx_user_name` ON `user`(`name`);
CREATE INDEX IF NOT EXISTS `idx_movement_name` ON `movement`(`name`);
CREATE INDEX IF NOT EXISTS `idx_personal_record_user_movement` ON `personal_record`(`user_id`, `movement_id`);
CREATE INDEX IF NOT EXISTS `idx_personal_record_movement_value` ON `personal_record`(`movement_id`, `value` DESC);
CREATE INDEX IF NOT EXISTS `idx_personal_record_date` ON `personal_record`(`date`);

-- Insert sample data
INSERT INTO `user` (id, name) VALUES
(1, 'Joao'),
(2, 'Jose'),
(3, 'Paulo');

INSERT INTO movement (id, name) VALUES
(1, 'Deadlift'),
(2, 'Back Squat'),
(3, 'Bench Press');

INSERT INTO personal_record (id, user_id, movement_id, value, date) VALUES
(1, 1, 1, 100.0, '2021-01-01 00:00:00.0'),
(2, 1, 1, 180.0, '2021-01-02 00:00:00.0'),
(3, 1, 1, 150.0, '2021-01-03 00:00:00.0'),
(4, 1, 1, 110.0, '2021-01-04 00:00:00.0'),
(5, 2, 1, 110.0, '2021-01-04 00:00:00.0'),
(6, 2, 1, 140.0, '2021-01-05 00:00:00.0'),
(7, 2, 1, 190.0, '2021-01-06 00:00:00.0'),
(8, 3, 1, 170.0, '2021-01-01 00:00:00.0'),
(9, 3, 1, 120.0, '2021-01-02 00:00:00.0'),
(10, 3, 1, 130.0, '2021-01-03 00:00:00.0'),
(11, 1, 2, 130.0, '2021-01-03 00:00:00.0'),
(12, 2, 2, 130.0, '2021-01-03 00:00:00.0'),
(13, 3, 2, 125.0, '2021-01-03 00:00:00.0'),
(14, 1, 2, 110.0, '2021-01-05 00:00:00.0'),
(15, 1, 2, 100.0, '2021-01-01 00:00:00.0'),
(16, 2, 2, 120.0, '2021-01-01 00:00:00.0'),
(17, 3, 2, 120.0, '2021-01-01 00:00:00.0');

