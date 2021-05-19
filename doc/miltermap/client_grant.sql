-- Run this grant for server postfix.example.com which have to query
-- with password "password".

GRANT USAGE ON `miltermap` . * TO 'blackman'@'postfix.example.com'  IDENTIFIED WITH mysql_native_password BY 'password' ;

GRANT SELECT, LOCK TABLES ON `miltermap` . * TO 'blackman'@'postfix.example.com';

GRANT USAGE ON `milteripmap` . * TO 'blackman'@'postfix.example.com'  IDENTIFIED WITH mysql_native_password BY 'password' ;

GRANT SELECT, LOCK TABLES ON `milteripmap` . * TO 'blackman'@'postfix.example.com';
