# Adstxt
As part of a broader effort to eliminate the ability to profit from counterfeit
inventory in the open digital advertising ecosystem, Ads.txt provides a 
mechanism to enable content owners to declare who is authorized to sell their 
inventory See https://iabtechlab.com/ads-txt/

# Misc

## Nginx
If you have Nginx rules like the one below, the module will not work, because Nginx will not pass the request to Drupal

```
location = /ads.txt {
    allow all;
    log_not_found off;
}
```

You need to change it, there may be an other solution, but this works:

```
location = /ads.txt {
    allow all;
    log_not_found off;
    try_files $uri /index.php?$query_string;
}

location = /app-ads.txt {
    allow all;
    log_not_found off;
    try_files $uri /index.php?$query_string;
}
```
