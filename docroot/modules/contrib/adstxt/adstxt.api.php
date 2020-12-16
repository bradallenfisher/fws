<?php

/**
 * @file
 * Hooks provided by the adstxt module.
 */

/**
 * Add additional lines to the site's ads.txt file.
 *
 * @return array
 *   An array of strings to add to the ads.txt.
 */
function hook_adstxt() {
  return [
    'greenadexchange.com, 12345, DIRECT, AEC242',
    'blueadexchange.com, 4536, DIRECT',
    'silverssp.com, 9675, RESELLER',
  ];
}

/**
 * Add additional lines to the site's app-ads.txt file.
 *
 * @return array
 *   An array of strings to add to the ads.txt.
 */
function hook_app_adstxt() {
  return array(
    'onetwothree.com, 12345, DIRECT, AEC242',
    'fourfivesix.com, 4536, DIRECT',
    '97whatever.com, 9675, RESELLER',
  );
}
