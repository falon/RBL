# RBL Manager
A complete RBL Management System

## Abstract
A complete tool to create and manage a time expiring lists of IP, networks, usernames and email address.
All the active members can be exported into RBLDNS zone format. The lists are stored into MySQL server, which can be accessed by Postfix for any policy enforcements. A Splunk plugin allow an authomated listing mechanism to block SPAM source.
Every member in list can be active (really listed), or inactive (time expired, or deactivated by administrative task).

## Requisite

- PHP > 7 (not tested with PHP5, but it could works).
- MySQL Server > 5.
- php-gmp and Haran/PHP.IPv4
- RBLDNS, if you export file zone through RBLDNS Export Plugin.
- Splunk, or shared output result Splunk folder for the Splunk List Plugin
- php-ldap for the Amavis Export Plugin
- php-imap, php-xml and Splunk SDK for the SPAM Learn Observer

## Basic Installation
Unfortunately I don't have time to provide a very stupid user installation. Sorry, you must follow the instructions.
Install via composer.
Move the doc and contrib folders to /usr/local/RBL or other location. Or don't move at all, if you like.

### Database MySQL
Check at the doc/db.sql and doc/grant.sql. Default values work with default config. Arrange them in your environment.
You can separate the MySQL host from the web host.

mysql -u root < doc/db.sql
mysql -u root < doc/grant.sql

### config.php
Copy config.php-default to config.php

Customize the DB part ($dbhost, $userdb, $db, $pwd)

Customize the tables ($tables) you want. These are the lists. List types are classified by "field". There are:

- ip (list of IP exportable to rbldns format through RBLDNS Export Plugin)
- network (list of CIDR networks exportable to rbldns format through RBLDNS Export Plugin)
- username (list of username, maybe useful to Postfix policy over sasl_username, if you like)
- email (list of email addresses, useful to Postfix policy over sender email addresses, if you like)
- domain (list of domains, exportable to rbldns format such as URIBL or SURBL)

Every list name and key must be unique. The field "active" if set to FALSE make the list inexistent.
The "limit" field configures the maximum number of active members in every list.
The "bl" field identifies list as blocklist (TRUE) or whitelist (FALSE), but it is quite useless. It just helps you to make a sane employ of the list.
The "depend" field defines constraint through lists. For instance, a spam listed item can't be subscribed to a whitelist.

The admins can list and relist items by hand through the web GUI. The superadmins (TRUE) can  list and relist up to years intervals. The list and relist facility is allowed only if you enable "require_auth", for safety reason. List and relist actions are logged with the authenticated admin credential.

## Plugin

The plugins are the real useful core of the lists. See at the wiki.
