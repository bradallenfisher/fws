<?php

namespace Drupal\helper\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validates duplicate entity field values.
 */
class EntityFieldUniqueValuesValidator extends FieldListUniqueValuesValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    /** @var \Drupal\Core\Entity\FieldableEntityInterface $value */
    parent::validate($value->get($constraint->field_name), $constraint);
  }

}
