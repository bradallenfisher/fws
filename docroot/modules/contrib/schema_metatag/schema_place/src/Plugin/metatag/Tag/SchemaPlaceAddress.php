<?php

namespace Drupal\schema_place\Plugin\metatag\Tag;

use Drupal\schema_metatag\Plugin\metatag\Tag\SchemaAddressBase;

/**
 * Provides a plugin for the 'schema_place_address' meta tag.
 *
 * - 'id' should be a globally unique id.
 * - 'name' should match the Schema.org element name.
 * - 'group' should match the id of the group that defines the Schema.org type.
 *
 * @MetatagTag(
 *   id = "schema_place_address",
 *   label = @Translation("address"),
 *   description = @Translation("Physical address of the place."),
 *   name = "address",
 *   group = "schema_place",
 *   weight = 1,
 *   type = "string",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class SchemaPlaceAddress extends SchemaAddressBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}
