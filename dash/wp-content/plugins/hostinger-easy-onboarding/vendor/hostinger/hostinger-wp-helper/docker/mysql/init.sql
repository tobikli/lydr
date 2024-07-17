CREATE DATABASE IF NOT EXISTS `wordpress_test`;

CREATE USER 'wordpress_test'@'localhost' IDENTIFIED BY 'local';
GRANT ALL PRIVILEGES ON *.* TO 'wordpress_test'@'%';
