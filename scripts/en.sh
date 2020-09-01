#!/bin/bash
cd /var/www/html/fws/docroot/sites/
  # grab each directory by name
  for site in *
    do
      #if it is a directory not a file - and not default - and not all.
      if [[ -d $site && $site != "default" && $site != "all" ]]
      then
        drush @prod.$site en honeypot -y
      fi
    done