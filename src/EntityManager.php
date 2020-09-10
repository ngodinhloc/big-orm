<?php
declare(strict_types=1);

namespace Bigcommerce\ORM;

use Bigcommerce\ORM\Client\ClientInterface;
use Bigcommerce\ORM\Events\EntityManagerEvent;
use Bigcommerce\ORM\Exceptions\EntityException;
use Bigcommerce\ORM\Relation\RelationInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class EntityManager
 * @package Bigcommerce\ORM
 */
class EntityManager
{
    /** @var \Bigcommerce\ORM\Client\ClientInterface */
    protected $client;

    /** @var \Bigcommerce\ORM\Mapper */
    protected $mapper;

    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * EntityManager constructor.
     *
     * @param \Bigcommerce\ORM\Client\ClientInterface|null $client
     * @param \Bigcommerce\ORM\Mapper|null $mapper mapper
     * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface|null $eventDispatcher
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct(
        ClientInterface $client = null,
        Mapper $mapper = null,
        EventDispatcherInterface $eventDispatcher = null)
    {
        $this->client = $client;
        $this->mapper = $mapper ?: new Mapper();
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Count the total number of object
     *
     * @param string|null $className
     * @param int|null $parentId
     * @return int|false
     * @throws \Bigcommerce\ORM\Exceptions\MapperException
     * @throws \Bigcommerce\ORM\Client\Exceptions\ClientException
     * @throws \Bigcommerce\ORM\Client\Exceptions\ResultException
     * @throws \Bigcommerce\ORM\Exceptions\EntityException
     */
    public function count(string $className = null, int $parentId = null)
    {
        $this->mapper->checkClass($className);

        $object = $this->mapper->object($className);
        $bigObject = $this->mapper->getClassAnnotation($object);
        $path = $this->mapper->getPath($bigObject, null, $parentId);

        return $this->client->count($path);
    }

    /**
     * Find all object of a class name
     *
     * @param string|null $className class
     * @param int|null $parentId
     * @param array|null $order order
     * @param bool $auto lazy loading
     * @return array|bool
     * @throws \Bigcommerce\ORM\Exceptions\MapperException
     * @throws \Bigcommerce\ORM\Client\Exceptions\ClientException
     * @throws \Bigcommerce\ORM\Client\Exceptions\ResultException
     * @throws \Bigcommerce\ORM\Exceptions\EntityException
     */
    public function findAll(string $className = null, int $parentId = null, array $order = null, bool $auto = false)
    {
        $this->mapper->checkClass($className);

        $object = $this->mapper->object($className);
        $bigObject = $this->mapper->getClassAnnotation($object);
        $path = $this->mapper->getPath($bigObject, null, $parentId);
        $autoIncludes = $this->mapper->getAutoIncludes($object);

        $queryBuilder = new QueryBuilder();
        if (!empty($order)) {
            $queryBuilder->order($order);
        }
        $queryString = $queryBuilder->include($autoIncludes)->getQueryString();
        $result = $this->client->findAll($path . "?" . $queryString);

        return $this->arrayToCollection($result, $className, $parentId, $auto);
    }

    /**
     * Query objects by conditions
     *
     * @param string|null $className
     * @param int|null $parentId
     * @param \Bigcommerce\ORM\QueryBuilder $queryBuilder
     * @param bool $auto
     * @return array|false
     * @throws \Bigcommerce\ORM\Exceptions\MapperException
     * @throws \Bigcommerce\ORM\Client\Exceptions\ClientException
     * @throws \Bigcommerce\ORM\Client\Exceptions\ResultException
     * @throws \Bigcommerce\ORM\Exceptions\EntityException
     */
    public function findBy(string $className = null, int $parentId = null, QueryBuilder $queryBuilder = null, $auto = false)
    {
        $this->mapper->checkClass($className);

        $object = $this->mapper->object($className);
        $bigObject = $this->mapper->getClassAnnotation($object);
        $path = $this->mapper->getPath($bigObject, null, $parentId);
        $autoIncludes = $this->mapper->getAutoIncludes($object);
        $queryString = $queryBuilder->include($autoIncludes)->getQueryString();
        $result = $this->client->findBy($path . "?" . $queryString);

        return $this->arrayToCollection($result, $className, $parentId, $auto);
    }

    /**
     * @param string|null $className
     * @param int|null $id
     * @param int|null $parentId
     * @param bool $auto
     * @return \Bigcommerce\ORM\Entity|false
     * @throws \Bigcommerce\ORM\Client\Exceptions\ClientException
     * @throws \Bigcommerce\ORM\Client\Exceptions\ResultException
     * @throws \Bigcommerce\ORM\Exceptions\EntityException
     * @throws Exceptions\MapperException
     */
    public function find(string $className = null, int $id = null, int $parentId = null, bool $auto = false)
    {
        $this->mapper->checkClass($className);
        $this->mapper->checkId($id);

        $object = $this->mapper->object($className);
        $bigObject = $this->mapper->getClassAnnotation($object);
        $path = $this->mapper->getPath($bigObject, null, $parentId);
        $autoIncludes = $this->mapper->getAutoIncludes($object);

        $queryBuilder = new QueryBuilder();
        $query = $queryBuilder->whereIn('id', [$id])->include($autoIncludes)->getQueryString();
        $result = $this->client->find($path . "?" . $query);

        if (empty($result)) {
            return false;
        }

        $entity = $this->mapper->patch($object, $result);
        if ($auto == false) {
            return $entity;
        }
        // No auto loading
        if (empty($entity->getMetadata()->getAutoLoads())) {
            return $entity;
        }

        // auto loading
        return $this->autoLoad($entity, $result, $parentId);
    }

    /**
     * Save entity
     * If id is provided: update entity
     * No id provided: create entity
     *
     * @param \Bigcommerce\ORM\Entity $entity entity
     * @return bool
     * @throws \Bigcommerce\ORM\Exceptions\EntityException
     * @throws \Bigcommerce\ORM\Exceptions\MapperException
     * @throws \Bigcommerce\ORM\Client\Exceptions\ResultException
     * @throws \Bigcommerce\ORM\Client\Exceptions\ClientException
     */
    public function save(Entity $entity)
    {
        $this->mapper->checkEntity($entity);

        if ($entity->isPatched() !== true) {
            $entity = $this->mapper->patch($entity, []);
        }

        $checkRequiredProperties = $this->mapper->checkRequiredFields($entity);
        if ($checkRequiredProperties !== true) {
            throw new EntityException(EntityException::MSG_REQUIRED_PROPERTIES . implode(", ", $checkRequiredProperties));
        }

        $checkRequiredValidations = $this->mapper->checkRequiredValidations($entity);
        if ($checkRequiredValidations !== true) {
            throw new EntityException(EntityException::MSG_REQUIRED_VALIDATIONS . implode(", ", $checkRequiredValidations));
        }

        $bigObject = $this->mapper->getClassAnnotation($entity);
        $path = $this->mapper->getPath($bigObject, $entity);
        $data = $this->mapper->getNoneReadonlyData($entity);

        // update entity
        if (!empty($id = $entity->getId())) {
            return $this->updateEntity($entity, $data, $path);
        }

        // create entity
        return $this->createEntity($entity, $data, $path);
    }

    /**
     * Update entity : allow to update entity with array of data
     *
     * @param \Bigcommerce\ORM\Entity $entity entity
     * @param array $data [fieldName => value]
     * @return bool
     * @throws \Bigcommerce\ORM\Exceptions\MapperException
     * @throws \Bigcommerce\ORM\Client\Exceptions\ResultException
     * @throws \Bigcommerce\ORM\Client\Exceptions\ClientException
     * @throws \Bigcommerce\ORM\Exceptions\EntityException
     */
    public function update(Entity $entity = null, array $data = [])
    {
        $this->mapper->checkEntity($entity);
        $this->mapper->checkId($entity->getId());

        if (empty($data)) {
            return true;
        }

        if ($entity->isPatched() !== true) {
            $entity = $this->mapper->patch($entity, []);
        }

        $checkRequiredValidations = $this->mapper->checkRequiredValidations($entity);
        if ($checkRequiredValidations !== true) {
            throw new EntityException(EntityException::MSG_REQUIRED_VALIDATIONS . implode(", ", $checkRequiredValidations));
        }

        if (!$this->mapper->checkNoneReadonlyData($data)) {
            return true;
        }

        $bigObject = $this->mapper->getClassAnnotation($entity);
        $data = $this->mapper->getNoneReadonlyData($entity, $data);
        $path = $this->mapper->getPath($bigObject, $entity);

        return $this->updateEntity($entity, $data, $path);
    }

    /**
     * Create an entity from data
     * @param string|null $class
     * @param array|null $data
     * @return \Bigcommerce\ORM\Entity
     * @throws \Bigcommerce\ORM\Exceptions\EntityException
     * @throws \Bigcommerce\ORM\Exceptions\MapperException
     */
    public function new(string $class = null, array $data = null)
    {
        $this->mapper->checkClass($class);
        $object = $this->mapper->object($class);

        return $this->mapper->patch($object, $data);
    }

    /**
     * Patch entity with data array
     *
     * @param \Bigcommerce\ORM\Entity $entity entity
     * @param array|null $array data
     * @return \Bigcommerce\ORM\Entity
     * @throws \Bigcommerce\ORM\Exceptions\MapperException
     */
    public function patch(Entity $entity = null, array $array = [])
    {
        return $this->mapper->patch($entity, $array);
    }

    /**
     * @param \Bigcommerce\ORM\Entity $entity
     * @param int $key
     * @return array
     * @throws \Bigcommerce\ORM\Exceptions\MapperException
     * @see \Bigcommerce\ORM\Mapper::KEY_BY_FIELD_NAME
     * @see \Bigcommerce\ORM\Mapper::KEY_BY_PROPERTY_NAME
     */
    public function toArray(Entity $entity, int $key = Mapper::KEY_BY_FIELD_NAME)
    {
        return $this->mapper->toArray($entity, $key);
    }

    /**
     * @param array $array
     * @param string $className
     * @param int $parentId
     * @param bool $auto
     * @return array
     * @throws \Bigcommerce\ORM\Exceptions\MapperException
     */
    private function arrayToCollection(array $array = [], string $className = null, int $parentId = null, bool $auto = false)
    {
        $collections = [];
        if (!empty($array)) {
            foreach ($array as $item) {
                $object = $this->mapper->object($className);
                $relationEntity = $this->mapper->patch($object, $item);
                if ($auto == false) {
                    $collections[] = $relationEntity;
                } else {
                    if (empty($relationEntity->getMetadata()->getAutoLoads())) {
                        $collections[] = $relationEntity;
                    } else {
                        $collections[] = $this->autoLoad($relationEntity, $item, $parentId);
                    }
                }
            }
        }

        return $collections;
    }

    /**
     * Load object in relations
     *
     * @param \Bigcommerce\ORM\Entity $entity entity
     * @param array $data
     * @param int $parentId
     * @return \Bigcommerce\ORM\Entity
     */
    private function autoLoad(Entity $entity = null, array $data = null, int $parentId = null)
    {
        if (empty($entity) || empty($entity->getMetadata()->getAutoLoads())) {
            return $entity;
        }
        foreach ($entity->getMetadata()->getAutoLoads() as $load) {
            $property = $load['property'];
            $annotation = $load['annotation'];
            if ($annotation instanceof RelationInterface) {
                $handler = $annotation->getHandler($this);
                $handler->handle($entity, $property, $annotation, $data, $parentId);
            }
        }

        return $entity;
    }

    /**
     * @param \Bigcommerce\ORM\Entity $entity
     * @param array $data
     * @param string $path
     * @return bool
     * @throws \Bigcommerce\ORM\Client\Exceptions\ClientException
     * @throws \Bigcommerce\ORM\Client\Exceptions\ResultException
     * @throws \Bigcommerce\ORM\Exceptions\EntityException
     * @throws \Bigcommerce\ORM\Exceptions\MapperException
     */
    private function createEntity(Entity $entity, array $data, string $path)
    {
        if (!$this->mapper->checkNoneReadonlyData($data)) {
            throw new EntityException(EntityException::MSG_EMPTY_NONE_READONLY_DATA);
        }

        $files = [];
        if (!empty($uploadFiles = $entity->getMetadata()->getUploadFiles())) {
            foreach ($uploadFiles as $field => $property) {
                $location = $this->mapper->getPropertyValue($entity, $property);
                if (!empty($location)) {
                    if (!file_exists($location)) {
                        throw new EntityException(EntityException::MSG_INVALID_UPLOAD_FILE . $location);
                    }
                    $files[$field] = $location;
                }
            }
        }

        $result = $this->client->create($path, $data, $files);
        if (!empty($result)) {
            $this->mapper->patch($entity, $result, true);
            $this->mapper->setPropertyValueByName($entity, 'isNew', true);
            if ($this->hasEventDispatcher()) {
                $this->eventDispatcher->dispatch(
                    new EntityManagerEvent(EntityManagerEvent::ENTITY_CREATED, $entity),
                    EntityManagerEvent::ENTITY_CREATED
                );
            }

            return true;
        }

        return false;
    }

    /**
     * @param \Bigcommerce\ORM\Entity $entity
     * @param array $data
     * @param string $path
     * @return bool
     * @throws \Bigcommerce\ORM\Client\Exceptions\ClientException
     * @throws \Bigcommerce\ORM\Client\Exceptions\ResultException
     * @throws \Bigcommerce\ORM\Exceptions\MapperException
     */
    private function updateEntity(Entity $entity, array $data, string $path)
    {
        if (!$this->mapper->checkNoneReadonlyData($data)) {
            return true;
        }

        $path = $path . "/{$entity->getId()}";
        $result = $this->client->update($path, $data);
        if (!empty($result)) {
            $this->mapper->patch($entity, $result, true);
            $this->mapper->setPropertyValueByName($entity, 'isNew', false);
            if ($this->hasEventDispatcher()) {
                $this->eventDispatcher->dispatch(
                    new EntityManagerEvent(EntityManagerEvent::ENTITY_UPDATED, $entity),
                    EntityManagerEvent::ENTITY_UPDATED
                );
            }

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function hasEventDispatcher()
    {
        return ($this->eventDispatcher instanceof EventDispatcherInterface);
    }

    /**
     * @param string $class class
     * @return \Bigcommerce\ORM\Repository
     * @throws \Exception
     */
    public function getRepository(string $class = null)
    {
        $repository = new Repository($this);
        $repository->setClassName($class);

        return $repository;
    }

    /**
     * @return \Bigcommerce\ORM\Mapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @param \Bigcommerce\ORM\Mapper $mapper mapper
     * @return \Bigcommerce\ORM\EntityManager
     */
    public function setMapper(Mapper $mapper = null)
    {
        $this->mapper = $mapper;

        return $this;
    }

    /**
     * @return \Bigcommerce\ORM\Client\ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param \Bigcommerce\ORM\Client\ClientInterface $client
     * @return \Bigcommerce\ORM\EntityManager
     */
    public function setClient(ClientInterface $client): EntityManager
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @return \Bigcommerce\ORM\EntityManager
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): EntityManager
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }
}
