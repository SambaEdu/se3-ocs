#!/bin/bash

###

apt-get remove --purge se3-ocs ocsinventory-server -y
mysqladmin -f drop ocsweb 
rm -f /var/www/se3/includes/dbconfig.inc.php
mysql -e "drop USER ocs@localhost" -b mysql
mysql -e "drop USER ocs" -b mysql













