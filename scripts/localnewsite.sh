#!/bin/bash

## RUN ./newsite.sh foundationrepairprosbatonrouge wp_

site=$1
echo "##### Adding server config /etc/hosts on term and enable the conf."
cat /var/www/html/fws/drupal/scripts/local.vhost.txt > /etc/apache2/sites-available/$site.conf
sed -i "s/website/$site/g" /etc/apache2/sites-available/$site.conf
a2ensite $site

cd /var/www/html/fws/drupal/docroot/sites
mkdir -p $site
cat /var/www/html/fws/drupal/scripts/settings.txt > /var/www/html/fws/drupal/docroot/sites/$site/settings.php
cat /var/www/html/fws/drupal/scripts/settings.local.txt > /var/www/html/fws/drupal/docroot/sites/$site/settings.local.php
sed -i "s/databasename/fws_$site/g" /var/www/html/fws/drupal/docroot/sites/$site/settings.local.php

mkdir -p $site/files/
cp lg/files/* $site/files/ -rf
chown -R www-data:www-data $site/files/
chown -R vagrant:vagrant $site/settings.php
chown -R vagrant:vagrant $site/settings.local.php
chmod 644 $site/settings.php
chown vagrant:vagrant $site
chmod -R 775 $site/files

# Create the DB first since we have global read, a .my.cnf file and no grant option.
##########################################################
mysql -e "create database if not exists fws_$site;"
mysqldump fws_lg > /var/www/$site.sql
mysql fws_$site < /var/www/$site.sql

cat /dev/null > /var/www/html/fws/drupal/docroot/sites/sites.php
echo "<?php" >> /var/www/html/fws/drupal/docroot/sites/sites.php

#Loop through FBDRUPAL sites Directory
cd /var/www/html/fws/drupal/docroot/sites/
  # grab each directory by name
  for site in *
    do
      #if it is a directory not a file - and not default - and not all.
      if [[ -d $site && $site != "default" && $site != "all" ]]
      then
        echo -e "\n"                                             >> /var/www/html/fws/drupal/docroot/sites/sites.php
        echo "\$sites['www.${site}.co'] = '${site}';"   >> /var/www/html/fws/drupal/docroot/sites/sites.php
        echo "\$sites['${site}.co'] = '${site}';"   >> /var/www/html/fws/drupal/docroot/sites/sites.php
        echo "\$sites['www.${site}.org'] = '${site}';"   >> /var/www/html/fws/drupal/docroot/sites/sites.php
        echo "\$sites['${site}.org'] = '${site}';"   >> /var/www/html/fws/drupal/docroot/sites/sites.php
        echo "\$sites['www.${site}.fisherwebsolutions.com'] = '${site}';"   >> /var/www/html/fws/drupal/docroot/sites/sites.php
        echo "\$sites['${site}.fisherwebsolutions.com'] = '${site}';"   >> /var/www/html/fws/drupal/docroot/sites/sites.php
        echo "\$sites['${site}.fws.test'] = '${site}';"   >> /var/www/html/fws/drupal/docroot/sites/sites.php
        echo "\$sites['www.${site}.fws.test'] = '${site}';"   >> /var/www/html/fws/drupal/docroot/sites/sites.php
        echo "\$sites['${site}.com'] = '${site}';"   >> /var/www/html/fws/drupal/docroot/sites/sites.php
        echo "\$sites['www.${site}.com'] = '${site}';"   >> /var/www/html/fws/drupal/docroot/sites/sites.php
      fi
  done