<?php
declare(strict_types=1);

namespace Bigcommerce\ORM\Annotations;

use Bigcommerce\ORM\Mapper;
use Bigcommerce\ORM\Validation\ValidationInterface;
use Bigcommerce\ORM\Validation\ValidatorInterface;
use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
final class Date extends Annotation implements ValidationInterface
{
    public $format = 'Y-m-d';
    public $validate = false;

    /**
     * @param \Bigcommerce\ORM\Mapper $mapper
     * @return \Bigcommerce\ORM\Validation\ValidatorInterface
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function getValidator(Mapper $mapper): ValidatorInterface
    {
        return new \Bigcommerce\ORM\Validation\Validators\DateValidator($mapper);
    }
}
