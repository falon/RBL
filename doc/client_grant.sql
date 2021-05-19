-- Run this grant for server postfix.example.com which have to query
-- with password "password".

GRANT USAGE ON `rbl` . * TO 'blackman'@'postfix.example.com' IDENTIFIED WITH mysql_native_password BY 'password' ;
ALTER USER 'blackman'@'postfix.example.com' WITH MAX_QUERIES_PER_HOUR 0  MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
GRANT SELECT, USAGE, LOCK TABLES ON `rbl` . * TO 'blackman'@'postfix.example.com';
