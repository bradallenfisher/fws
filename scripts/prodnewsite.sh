#!/bin/bash

## RUN ./newsite.sh foundationrepairprosbatonrouge wp_

site=$1
prefix="fws_"

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
mysql -e "create database if not exists $prefix$site;"
mysqldump fws_lg > /var/www/$site.sql
mysql fws_$site < /var/www/$site.sql

cat /dev/null > /var/www/html/fws/docroot/sites/sites.php
echo "<?php" >> /var/www/html/fws/docroot/sites/sites.php

#Loop through FBDRUPAL sites Directory
cd /var/www/html/fws/docroot/sites/
  # grab each directory by name
  for site in *
    do
      #if it is a directory not a file - and not default - and not all.
      if [[ -d $site && $site != "default" && $site != "all" ]]
      then
        echo -e "\n"                                             >> /var/www/html/fws/docroot/sites/sites.php
        echo "\$sites['${site}.fisherwebsolutions.com'] = '${site}';"   >> /var/www/html/fws/docroot/sites/sites.php
        echo "\$sites['${site}.fws.test'] = '${site}';"   >> /var/www/html/fws/docroot/sites/sites.php
        echo "\$sites['${site}.com'] = '${site}';"   >> /var/www/html/fws/docroot/sites/sites.php
      fi
  done

echo "##### Adding server config /etc/hosts on term and enable the conf."
cat /var/www/html/fws/scripts/prod.vhost.txt > /etc/apache2/sites-available/$site.conf
sed -i "s/website/$site/g" /etc/apache2/sites-available/$site.conf
a2ensite $site
