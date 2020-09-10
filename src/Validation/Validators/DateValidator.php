<?php
declare(strict_types=1);

namespace Bigcommerce\ORM\Validation\Validators;

use Bigcommerce\ORM\Entity;
use Bigcommerce\ORM\Validation\AbstractValidator;
use Bigcommerce\ORM\Validation\ValidationInterface;
use Bigcommerce\ORM\Validation\ValidatorInterface;

/**
 * Class DateValidator
 * @package Bigcommerce\ORM\Validation\Validators
 */
class DateValidator extends AbstractValidator implements ValidatorInterface
{

    /**
     * @param \Bigcommerce\ORM\Entity $entity entity
     * @param \ReflectionProperty $property property
     * @param \Bigcommerce\ORM\Validation\ValidationInterface $annotation relation
     * @return bool
     */
    public function validate(Entity &$entity, \ReflectionProperty $property, ValidationInterface $annotation)
    {
        $date = $this->mapper->getPropertyValue($entity, $property);
        if ($date === null) {
            return true;
        }
        /** @var \Bigcommerce\ORM\Annotations\Date $annotation */
        $date = date_parse_from_format($annotation->format, $date);

        return checkdate($date['month'], $date['day'], $date['year']);
    }
}
