<?php
declare(strict_types=1);

namespace Tests;

use Bigcommerce\ORM\Annotations\Resource;
use Bigcommerce\ORM\Metadata;

class MetadataTest extends BaseTestCase
{
    /** @coversDefaultClass \Bigcommerce\ORM\Metadata */
    protected $metadata;

    protected function setUp(): void
    {
        parent::setUp();
        $this->metadata = new Metadata();
    }

    /**
     * @covers \Bigcommerce\ORM\Metadata::setResource
     * @covers \Bigcommerce\ORM\Metadata::setRelationFields
     * @covers \Bigcommerce\ORM\Metadata::setUploadFields
     * @covers \Bigcommerce\ORM\Metadata::setIncludeFields
     * @covers \Bigcommerce\ORM\Metadata::setValidationProperties
     * @covers \Bigcommerce\ORM\Metadata::setCustomisedFields
     * @covers \Bigcommerce\ORM\Metadata::setReadonlyFields
     * @covers \Bigcommerce\ORM\Metadata::setAutoLoadFields
     * @covers \Bigcommerce\ORM\Metadata::setRequiredFields
     * @covers \Bigcommerce\ORM\Metadata::getResource
     * @covers \Bigcommerce\ORM\Metadata::getRelationFields
     * @covers \Bigcommerce\ORM\Metadata::getUploadFields
     * @covers \Bigcommerce\ORM\Metadata::getIncludeFields
     * @covers \Bigcommerce\ORM\Metadata::getValidationProperties
     * @covers \Bigcommerce\ORM\Metadata::getCustomisedFields
     * @covers \Bigcommerce\ORM\Metadata::getReadonlyFields
     * @covers \Bigcommerce\ORM\Metadata::getAutoLoadFields
     * @covers \Bigcommerce\ORM\Metadata::getRequiredFields
     */
    public function testSettersAndGetters()
    {
        $bigObject = new Resource([]);
        $this->metadata
            ->setResource($bigObject)
            ->setRelationFields([])
            ->setUploadFields([])
            ->setIncludeFields([])
            ->setValidationProperties([])
            ->setCustomisedFields([])
            ->setReadonlyFields([])
            ->setAutoLoadFields([])
            ->setRequiredFields([])
            ->setInResultFields([])
            ->setParamFields([]);
        $this->assertEquals($bigObject, $this->metadata->getResource());
        $this->assertEquals([], $this->metadata->getRelationFields());
        $this->assertEquals([], $this->metadata->getUploadFields());
        $this->assertEquals([], $this->metadata->getIncludeFields());
        $this->assertEquals([], $this->metadata->getValidationProperties());
        $this->assertEquals([], $this->metadata->getCustomisedFields());
        $this->assertEquals([], $this->metadata->getReadonlyFields());
        $this->assertEquals([], $this->metadata->getAutoLoadFields());
        $this->assertEquals([], $this->metadata->getRequiredFields());
        $this->assertEquals([], $this->metadata->getInResultFields());
        $this->assertEquals([], $this->metadata->getParamFields());
    }
}
