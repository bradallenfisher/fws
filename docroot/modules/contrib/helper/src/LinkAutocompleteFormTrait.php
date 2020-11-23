<?php

namespace Drupal\helper;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides helpers for adding a content autocomplete link element to a form.
 *
 * @todo Add support for more than one entity type when https://www.drupal.org/node/2423093 lands.
 */
trait LinkAutocompleteFormTrait {

  /**
   * Generates a autocomplete link element for a form.
   *
   * @parma string $entity_type
   *   The entity type.
   * @param string $default_value
   *   The default value for the field.
   *
   * @return array
   *   The autocomplete link form element.
   */
  public static function getLinkAutocompleteElement($entity_type, $default_value = NULL) {
    $type = \Drupal::entityTypeManager()->getDefinition($entity_type);
    return [
      '#type' => 'entity_autocomplete',
      '#target_type' => $entity_type,
      '#default_value' => isset($default_value) ? static::getUriAsDisplayableString($default_value) : '',
      '#description' => t('Start typing the @label of a @type to select it. You can also enter an internal path such as %add-node or an external URL such as %url. Enter %front to link to the front page.', [
        '@label' => $type->getKey('label'),
        '@type' => $type->getSingularLabel(),
        '%add-node' => '/node/add',
        '%url' => 'http://example.com',
        '%front' => '<front>',
      ]),
      '#element_validate' => [
        [static::class, 'validateLinkAutocompleteElement'],
      ],
      // Disable autocompletion when the first character is '/', '#' or '?'.
      '#attributes' => ['data-autocomplete-first-character-blacklist' => '/#?'],
      // Avoid the default value processing since we are doing it ourselves in
      // static::getUriAsDisplayableString().
      '#process_default_value' => FALSE,
    ];
  }

  /**
   * Validation callback for URL elements.
   */
  public static function validateLinkAutocompleteElement(&$element, FormStateInterface $form_state) {
    if (!empty($element['#value'])) {
      $uri = static::getUserEnteredStringAsUri($element['#target_type'], $element['#value']);
      $form_state->setValueForElement($element, $uri);

      // @see \Drupal\link\Plugin\Field\FieldWidget\LinkWidget::validateUriElement()
      // If getUserEnteredStringAsUri() mapped the entered value to a
      // 'internal:' URI , ensure the raw value begins with '/', '?' or '#'.
      // @todo '<front>' is valid input for BC reasons, may be removed by https://www.drupal.org/node/2421941
      $valid_starting_internal_chars = ['/', '?', '#'];
      if (parse_url($uri, PHP_URL_SCHEME) === 'internal' && !in_array($element['#value'][0], $valid_starting_internal_chars, TRUE) && substr($element['#value'], 0, 7) !== '<front>') {
        $form_state->setError($element, t('Manually entered paths should start with one of the following characters: / ? #'));
        return;
      }

      // Validate non-external URLs.
      if (!UrlHelper::isExternal($uri)) {
        $url = Url::fromUri($uri);
        if (!\Drupal::service('path.validator')->getUrlIfValid($url->toString())) {
          $form_state->setError($element, t('The URL @url is invalid.', ['@url' => $element['#value']]));
        }
      }
    }
  }

  /**
   * Gets the URI without the 'internal:' or 'entity:' scheme.
   *
   * The following two forms of URIs are transformed:
   * - 'entity:' URIs: to entity autocomplete ("label (entity id)") strings;
   * - 'internal:' URIs: the scheme is stripped.
   *
   * This method is the inverse of ::getUserEnteredStringAsUri().
   *
   * @param string $uri
   *   The URI to get the displayable string for.
   *
   * @return string
   *   The displayable string.
   *
   * @see static::getUserEnteredStringAsUri()
   * @see \Drupal\link\Plugin\Field\FieldWidget\LinkWidget::getUriAsDisplayableString()
   */
  protected static function getUriAsDisplayableString($uri) {
    $scheme = parse_url($uri, PHP_URL_SCHEME);

    // By default, the displayable string is the URI.
    $displayable_string = $uri;

    // A different displayable string may be chosen in case of the 'internal:'
    // or 'entity:' built-in schemes.
    if ($scheme === 'internal') {
      $uri_reference = explode(':', $uri, 2)[1];

      // @todo '<front>' is valid input for BC reasons, may be removed by https://www.drupal.org/node/2421941
      $path = parse_url($uri, PHP_URL_PATH);
      if ($path === '/') {
        $uri_reference = '<front>' . substr($uri_reference, 1);
      }

      $displayable_string = $uri_reference;
    }
    elseif ($scheme === 'entity') {
      [$entity_type, $entity_id] = explode('/', substr($uri, 7), 2);
      // Show the 'entity:' URI as the entity autocomplete would.
      if ($entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity_id)) {
        $displayable_string = EntityAutocomplete::getEntityLabels([$entity]);
      }
    }
    elseif ($scheme === 'route') {
      $displayable_string = ltrim($displayable_string, 'route:');
    }

    return $displayable_string;
  }

  /**
   * Gets the user-entered string as a URI.
   *
   * The following two forms of input are mapped to URIs:
   * - entity autocomplete ("label (entity id)") strings: to 'entity:' URIs;
   * - strings without a detectable scheme: to 'internal:' URIs.
   *
   * This method is the inverse of ::getUriAsDisplayableString().
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $string
   *   The user-entered string.
   *
   * @return string
   *   The URI, if a non-empty $uri was passed.
   *
   * @see static::getUriAsDisplayableString()
   * @see \Drupal\link\Plugin\Field\FieldWidget\LinkWidget::getUserEnteredStringAsUri()
   */
  protected static function getUserEnteredStringAsUri($entity_type, $string) {
    // By default, assume the entered string is an URI.
    $uri = trim($string);

    // Detect entity autocomplete string, map to 'entity:' URI.
    $entity_id = EntityAutocomplete::extractEntityIdFromAutocompleteInput($string);
    if ($entity_id !== NULL) {
      $uri = 'entity:' . $entity_type . '/' . $entity_id;
    }
    // Detect a schemeless string, map to 'internal:' URI.
    elseif (!empty($string) && parse_url($string, PHP_URL_SCHEME) === NULL) {
      // @todo '<front>' is valid input for BC reasons, may be removed by https://www.drupal.org/node/2421941
      // - '<front>' -> '/'
      // - '<front>#foo' -> '/#foo'
      if (strpos($string, '<front>') === 0) {
        $string = '/' . substr($string, strlen('<front>'));
      }
      $uri = 'internal:' . $string;
    }

    return $uri;
  }
}
