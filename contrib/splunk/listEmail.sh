#!/bin/bash
cd /opt/splunk/bin/scripts/
./listFromSplunk.php -c listEmail.conf "$1" "$2" "$3" "$4" "$5" "$6" "$7" "$8"
