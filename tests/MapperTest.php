<?php
declare(strict_types=1);

namespace Tests;

use Bigcommerce\ORM\Annotations\Resource;
use Bigcommerce\ORM\Entities\CustomerAddress;
use Bigcommerce\ORM\Entities\Customer;
use Bigcommerce\ORM\Entities\Product;
use Bigcommerce\ORM\Entities\ProductModifier;
use Bigcommerce\ORM\Entities\ProductModifierValue;
use Bigcommerce\ORM\Entities\ProductReview;
use Bigcommerce\ORM\Exceptions\EntityException;
use Bigcommerce\ORM\Exceptions\MapperException;
use Bigcommerce\ORM\Mapper;
use Bigcommerce\ORM\Metadata;

class MapperTest extends BaseTestCase
{
    /** @var \Bigcommerce\ORM\Mapper */
    protected $mapper;

    /** @var \Bigcommerce\ORM\Entities\Customer */
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new Mapper();
        $this->customer = new Customer();
    }

    /**
     * @covers \Bigcommerce\ORM\Mapper::__construct
     * @covers \Bigcommerce\ORM\Mapper::getObjectType
     * @throws \Bigcommerce\ORM\Exceptions\MapperException
     */
    public function testGetObjectType()
    {
        $this->mapper = new Mapper();
        $objectType = $this->mapper->getObjectType($this->customer);
        $this->assertEquals('Customer', $objectType);
    }

    /**
     * @covers \Bigcommerce\ORM\Mapper::getClassAnnotation
     * @throws \Bigcommerce\ORM\Exceptions\MapperException
     */
    public function testGetClassAnnotation()
    {
        $classAnnotation = $this->mapper->getClassAnnotation($this->customer);
        $this->assertInstanceOf(Resource::class, $classAnnotation);
        $this->assertEquals('Customer', $classAnnotation->name);
        $this->assertEquals('/customers', $classAnnotation->path);
    }

    public function testResourcePath()
    {
        $path = $this->mapper->getResourcePath($this->customer);
        $this->assertEquals('/customers', $path);
    }

    public function testResourcePath2()
    {
        $value = new ProductModifierValue();
        $value->setProductId(111);
        $this->expectException(MapperException::class);
        $path = $this->mapper->getResourcePath($value);
    }

    public function testResourcePath3()
    {
        $review = new ProductReview();
        $review->setProductId(111);
        $path = $this->mapper->getResourcePath($review);
        $this->assertEquals('/catalog/products/111/reviews', $path);
    }

    public function testPatch()
    {
        $product = new Product();
        $data = [
            'name' => 'Product Name',
            'type' => 'physic',
            'primary_image' => [
                "id" => 372,
                "product_id" => 111,
                "is_thumbnail" => true,
                "sort_order" => 1,
                "description" => "",
                "image_file" => "lamp.jpg",
            ],
            'images' => [
                [
                    "id" => 372,
                    "product_id" => 111,
                    "is_thumbnail" => false,
                    "sort_order" => 1,
                    "description" => "",
                    "image_file" => "lamp.jpg",
                ],
            ]
        ];

        /** @var Product $product */
        $product = $this->mapper->patch($product, $data);
        $this->assertTrue($product->isPatched());

        $metadata = $product->getMetadata();
        $resource = $metadata->getResource();
        $relationFields = $metadata->getRelationFields();
        $includeFields = $metadata->getIncludeFields();
        $autoLoadFields = $metadata->getAutoLoadFields();
        $requiredFields = $metadata->getRequiredFields();
        $readonlyFields = $metadata->getReadonlyFields();

        $this->assertInstanceOf(Metadata::class, $metadata);
        $this->assertInstanceOf(Resource::class, $resource);
        $this->assertEquals(9, count($relationFields));
        $this->assertEquals(7, count($includeFields));
        $this->assertEquals(2, count($autoLoadFields));
        $this->assertEquals(1, count($requiredFields));
        $this->assertEquals(3, count($readonlyFields));

        $primaryImage = $product->getPrimaryImage();
        $this->assertEquals('lamp.jpg', $primaryImage->getImageFile());

        $modifier = new ProductModifier();
        $modifier = $this->mapper->patch($modifier, ['name' => 'Modifier Name']);
        $this->assertTrue($modifier->isPatched());
    }

    public function testCheckRequiredFields()
    {
        $product = new Product();
        $check = $this->mapper->checkRequiredFields($product);
        $this->assertEquals(['name' => 'name'], $check);

        $address = new CustomerAddress();
        $check = $this->mapper->checkRequiredFields($address);
        $this->assertEquals(true, $check);
    }

    public function testGetNoneReadonlyData()
    {
        $modifier = new ProductModifier();
        $modifier
            ->setProductId(111)
            ->setSortOrder(2)
            ->setName('name')
            ->setDisplayName('display name')
            ->setType('type')
            ->setConfig(['sku' => 111]);
        $expected = [
            'name' => 'name',
            'display_name' => 'display name',
            'type' => 'type',
            'required' => false,
            'sort_order' => 2,
            'config' => ['sku' => 111]
        ];

        $data = $this->mapper->getWritableFieldValues($modifier, []);
        $this->assertEquals($expected, $data);
    }

    public function testCheckNoneReadOnlyData()
    {
        $expected = [
            'name' => 'name',
            'display_name' => 'display name',
            'type' => 'type',
            'required' => false,
            'sort_order' => 2,
            'config' => ['sku' => 111]
        ];

        $check = $this->mapper->checkPropertyValues($expected);
        $this->assertTrue($check);
    }

    public function testCheckRequiredValidations()
    {
        $customer = new Customer();
        $customer->setEmail('kenngo');
        $check = $this->mapper->checkRequiredValidations($customer);
        $expected = [
            'email' => 'email: Bigcommerce\ORM\Annotations\Email'
        ];

        $this->assertEquals($expected, $check);
    }

    public function testToArray()
    {
        $modifier = new ProductModifier();
        $modifier
            ->setName('Name')
            ->setType('file')
            ->setDisplayName('Display Name');
        $expected = [
            'product_id' => null,
            'name' => 'Name',
            'display_name' => 'Display Name',
            'type' => 'file',
            'required' => false,
            'sort_order' => null,
            'config' => null,
            'id' => null
        ];
        $array = $this->mapper->toArray($modifier);
        $this->assertEquals($expected, $array);

        $expected = [
            'productId' => null,
            'name' => 'Name',
            'displayName' => 'Display Name',
            'type' => 'file',
            'required' => false,
            'sortOrder' => null,
            'config' => null,
            'id' => null
        ];

        $array = $this->mapper->toArray($modifier, Mapper::KEY_BY_PROPERTY_NAME);
        $this->assertEquals($expected, $array);
    }

    public function testGetPropertyValueByName()
    {
        $modifier = new ProductModifier();
        $modifier
            ->setName('Name')
            ->setType('file')
            ->setDisplayName('Display Name');
        $value = $this->mapper->getPropertyValueByName($modifier, 'name');
        $this->assertEquals('Name', $value);
    }

    public function testGetPropertyValueByFieldName()
    {
        $modifier = new ProductModifier();
        $modifier
            ->setName('Name')
            ->setType('file')
            ->setDisplayName('Display Name');
        $value = $this->mapper->getPropertyValueByFieldName($modifier, 'display_name');
        $this->assertEquals('Display Name', $value);
    }

    public function testObject()
    {
        $object = $this->mapper->object(Customer::class);
        $this->assertInstanceOf(Customer::class, $object);
    }

    public function testCheckEntity()
    {
        $this->expectException(EntityException::class);
        $this->mapper->checkEntity(null);
    }

    public function testCheckClass()
    {
        $this->expectException(EntityException::class);
        $this->mapper->checkClass('');
    }

    public function testCheckId()
    {
        $this->expectException(EntityException::class);
        $this->mapper->checkId(0);
    }
}