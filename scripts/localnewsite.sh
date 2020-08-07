#!/bin/bash

## RUN ./newsite.sh foundationrepairprosbatonrouge wp_

site=$1

cd /var/www/html/fws/docroot/sites
mkdir -p $site
cat /var/www/html/fws/scripts/settings.txt > /var/www/html/fws/docroot/sites/$site/settings.php
cat /var/www/html/fws/scripts/settings.local.txt > /var/www/html/fws/docroot/sites/$site/settings.local.php
sed -i "s/databasename/fws_$site/g" /var/www/html/fws/docroot/sites/$site/settings.local.php

mkdir -p $site/files/
chown -R www-data:www-data $site/files/
chown -R vagrant:vagrant $site/settings.php
chmod 644 $site/settings.php
chown vagrant:vagrant $site
chmod -R 775 $site/files

# Create the DB first since we have global read, a .my.cnf file and no grant option.
##########################################################
mysql -e "create database if not exists fws_$site;"
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

echo "##### Add server config /etc/hosts on term and enable the conf."
