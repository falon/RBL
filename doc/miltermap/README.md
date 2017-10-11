# Miltermap for Postfix Plugin
This Plugin implement a mysql table of per client access to milters. See at http://www.postfix.org/MILTER_README.html#per-client

The query to select milter is:

SELECT conf FROM config WHERE name IN(
    SELECT name FROM milt WHERE id=(
        SELECT idmilt FROM net WHERE
        	((inet_aton('%s')&netmask)=network)
        		AND active='1' AND exp>NOW()
    )
)

where %s is the SMTP client IP address.
