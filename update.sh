#!/bin/bash
echo This is a basic update for RBL Manager
echo A new version of interface, plugin and cron tries to install
echo in default paths.
echo Make sure that RBL.tgz is in DOCUMENT ROOT of web server
echo and this file is in the parent path.
echo
echo USE WITH CARE: THIS IS A BETA TOOL
echo


echo -en "\n\nFirst, install or update DB, then press <ENTER> :-> "
read KEY

echo -en "\nSecond, install or update web server conf, then press <ENTER> :-> "
read KEY

echo -en "\nEnter the local path for contrib files (hint: /usr/local/RBL) :-> "
read LPATH


echo
echo Some bak of conf is very useful, I proceed
echo Saving config.php as config.php.bak
cp -pv RBL/config.php RBL/config.php.bak
echo Saving imap.conf as imap.conf.bak
cp -pv RBL/imap.conf RBL/imap.conf.bak
echo Saving yourbl crontab file
cp -pv $LPATH/contrib/yourbl.cron $LPATH/contrib/yourbl.cron.bak
echo Saving amavis plugin
cp -pv $LPATH/contrib/amavis/exportAmavisLdap.php $LPATH/contrib/amavis/exportAmavisLdap.php.bak
echo Saving yourbl conf list
find $LPATH/contrib/rbldns -name "*.conf" -exec sh -c 'cp -pv "$1" "${1%.conf}.conf.bak"' _ {} \;
echo Saving Splunk plugin
cp -pv $LPATH/contrib/splunk/listFromSplunk.php  $LPATH/contrib/splunk/listFromSplunk.php.bak
cp -pv $LPATH/contrib/splunk/listEmail.sh $LPATH/contrib/splunk/listEmail.sh.bak
cp -pv $LPATH/contrib/splunk/listEmail.conf $LPATH/contrib/splunk/listEmail.conf.bak
cp -pv $LPATH/contrib/splunk/alert.eml $LPATH/contrib/splunk/alert.eml.bak


echo
echo Press a key to upgrade.
read KEY
echo
echo "Untar the package"...
tar xvzf RBL.tgz

echo move the contrib parts...
[ -d $LPATH ] || mkdir -p $LPATH
cp -prv RBL/contrib $LPATH
rm -rfv $LPATH/doc
mv -v RBL/doc $LPATH
rm -rfv RBL/contrib


echo -en "Make again symlink (type YES for yes)? :-> "
read SYM
if [ "$SYM" == 'YES' ];
	then
		echo Making symlinks:
		echo Install the expire daily task...
		ln -s /usr/local/RBL/contrib/expire.php /etc/cron.daily/expireRBL.php

		echo 'Install other scheduled task (amavis,DNSBL export...)'
		ln -s /usr/local/RBL/contrib/yourbl.cron /etc/cron.d/yourbl
		echo '* Please REMEMBER to adjust the paths written into /etc/cron.d/yourbl *'

		echo
echo Remember also to update any RSYNC task and RBLDNS or BIND configuration file.
echo Update Spamassassin rules if needed.
echo

		echo Install Splunk plugin
		[ -d /opt/splunk/bin/scripts ] echo 'NO, it seems Splunk is not installed!'
		ln -s $LPATH/contrib/splunk/listFromSplunk.php /opt/splunk/bin/scripts/
		ln -s $LPATH/contrib/splunk/otherfunction.php /opt/splunk/bin/scripts/
		ln -s $LPATH/contrib/splunk/listEmail.sh /opt/splunk/bin/scripts/
		ln -s $LPATH/contrib/splunk/listEmail.conf /opt/splunk/bin/scripts/
fi

echo
echo Remember also to update any RSYNC task and RBLDNS or BIND configuration file.
echo Update Spamassassin rules if needed.
echo
echo Remember to check configuration file in :
echo RBL/config.php
echo $LPATH/contrib/amavis/exportAmavisLdap.php
echo $LPATH/contrib/splunk/*.conf

echo DONE!
