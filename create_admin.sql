-- Creates (or repairs) the Sneakers Room admin account.
-- Run once in phpMyAdmin: select sneakers_db, open the SQL tab, paste, Go.
-- Safe to run again: if the email already exists, it fixes the password and role.
--
-- Login:  sneakersroom.admin@gmail.com
-- Password: sneakersroom   (change it after your first login)

USE sneakers_db;

INSERT INTO users (name, email, mobile, address, password_hash, role)
VALUES (
  'Sneakers Room Admin',
  'sneakersroom.admin@gmail.com',
  '09077768906',
  'poblacion',
  '$2y$10$/GS03KeyV.We3TvbcIhbYuZfdOhbbA92V0mT1uvnfKGWvQCnOeN0a',
  'admin'
)
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  password_hash = VALUES(password_hash),
  role = 'admin';
