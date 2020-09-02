#!/bin/bash

site=$1

# To sync a single site to your local just type the domain after the script name as arg1 and your user name as arg2
sudo chmod -R 777 /var/www/html/fws/drupal/docroot/sites/$site/files
drush sql-sync @prod.$site @local.$site -y
drush -y rsync @prod.$site:%files @local.$site:%files --mode=zOvr --exclude-paths=css:js:php:private:styles:tmp:xmlsitemap
sudo chmod -R 755 /var/www/html/fws/drupal/docroot/sites/$site/files

