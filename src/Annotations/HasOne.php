<?php

declare(strict_types=1);

namespace Bigcommerce\ORM\Annotations;

use Bigcommerce\ORM\EntityManager;
use Bigcommerce\ORM\Relation\Handlers\HasOneHandler;
use Bigcommerce\ORM\Relation\HasRelationInterface;
use Bigcommerce\ORM\Relation\OneRelationInterface;
use Bigcommerce\ORM\Relation\RelationHandlerInterface;
use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class HasOne extends Annotation implements HasRelationInterface, OneRelationInterface
{
    public $name;
    public $targetClass;
    public $field;
    public $targetField;
    public $from = 'api';   // api, include, result
    public $auto = false;

    /**
     * @param \Bigcommerce\ORM\EntityManager $entityManager entity manager
     * @return \Bigcommerce\ORM\Relation\RelationHandlerInterface
     */
    public function getHandler(EntityManager $entityManager): RelationHandlerInterface
    {
        return new HasOneHandler($entityManager);
    }
}
