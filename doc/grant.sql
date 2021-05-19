CREATE USER 'blackuser'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password';

GRANT SELECT ,
INSERT ,
UPDATE ,
DELETE ,
USAGE  ,
LOCK TABLES ON `rbl` . * TO 'blackuser'@'localhost';

ALTER USER 'blackuser'@'localhost' WITH MAX_QUERIES_PER_HOUR 0  MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
