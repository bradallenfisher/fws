#!/bin/bash
# To sync a single site to your local just type the domain after the script name as arg1 and your user name as arg2
sudo chmod -R 777 /var/www/html/fws/docroot/sites/lg/files
drush sql-sync @prod.lg @local.lg -y
drush -y rsync @prod.lg:%files @local.lg:%files --mode=zOvr --exclude-paths=css:js:php:private:styles:tmp:xmlsitemap
sudo chmod -R 755 /var/www/html/fws/docroot/sites/lg/files