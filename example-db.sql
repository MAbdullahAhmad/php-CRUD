CREATE DATABASE IF NOT EXISTS php_crud;
USE php_crud;

CREATE TABLE IF NOT EXISTS products(id iNT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(254) NOT NULL, price FLOAT NOT NULL DEFAULT 0.0);
