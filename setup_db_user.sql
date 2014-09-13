-- Only needed if you're accessing your database via command line rather than cPanel
-- Change the database name, username and password to whateer you want

drop database if exists sequence;
create database sequence;
CREATE USER 'username'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON sequence TO 'username';

use sequence;-- change the database name to the one you specified before
