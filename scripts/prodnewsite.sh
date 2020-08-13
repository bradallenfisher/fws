#!/bin/bash

## RUN ./newsite.sh foundationrepairprosbatonrouge wp_

site=$1
echo "##### Adding server config /etc/hosts on term and enable the conf."
cat /var/www/html/fws/scripts/prod.vhost.txt > /etc/apache2/sites-available/$site.conf
sed -i "s/website/$site/g" /etc/apache2/sites-available/$site.conf
a2ensite $site

cd /var/www/html/fws/docroot/sites
mkdir -p $site
cat /var/www/html/fws/scripts/prod.settings.txt > /var/www/html/fws/docroot/sites/$site/settings.php

sed -i "s/databasename/fws_$site/g" /var/www/html/fws/docroot/sites/$site/settings.php
sed -i "s/rootuser/fws_admin/g" /var/www/html/fws/docroot/sites/$site/settings.php
sed -i "s/rootpass/BafLjfMySql_2019/g" /var/www/html/fws/docroot/sites/$site/settings.php

mkdir -p $site/files/
cp lg/files/* $site/files/ -rf
chown -R www-data:www-data $site/files/
chown fws:fws $site
chown fws:fws $site/settings.php
chmod 644 $site/settings.php
chmod -R 755 $site/files

# Create the DB first since we have global read, a .my.cnf file and no grant option.
##########################################################
mysql -e "create database if not exists fws_$site;"
mysqldump fws_lg > /var/www/$site.sql
mysql fws_$site < /var/www/$site.sql