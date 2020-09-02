cd /var/www/html/fws/drupal/docroot/sites/
  # grab each directory by name
  for site in *
    do
      #if it is a directory not a file - and not default - and not all.
      if [[ -d $site && $site != "default" && $site != "all" ]]
      then
        echo "about to do $site"
        drush @local.$site updb -y
      fi
    done