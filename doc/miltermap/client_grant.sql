-- Run this grant for server postfix.example.com which have to query
-- with password "password".

GRANT USAGE ON `miltermap` . * TO 'blackman'@'postfix.example.com' IDENTIFIED BY PASSWORD '*2470C0C06DEE42FD1618BB99005ADCA2EC9D1E19' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;

GRANT SELECT, LOCK TABLES ON `miltermap` . * TO 'blackman'@'postfix.example.com';

GRANT USAGE ON `milteripmap` . * TO 'blackman'@'postfix.example.com' IDENTIFIED BY PASSWORD '*2470C0C06DEE42FD1618BB99005ADCA2EC9D1E19' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;

GRANT SELECT, LOCK TABLES ON `milteripmap` . * TO 'blackman'@'postfix.example.com';
