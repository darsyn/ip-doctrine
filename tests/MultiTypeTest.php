<?php

namespace Darsyn\IP\Tests\Doctrine;

use Darsyn\IP\Doctrine\MultiType;
use Darsyn\IP\Version\Multi as IP;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class MultiTypeTest extends TestCase
{
    /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
    private $platform;

    /** @var \Darsyn\IP\Doctrine\MultiType $type */
    private $type;

    /**
     * @beforeClass
     * @return void
     */
    #[PHPUnit\BeforeClass]
    public static function setUpBeforeClassWithoutReturnDeclaration()
    {
        if (class_exists(Type::class)) {
            Type::addType('ip_multi', MultiType::class);
        }
    }

    /**
     * @before
     * @return void
     */
    #[PHPUnit\Before]
    protected function setUpWithoutReturnDeclaration()
    {
        if (!class_exists('Doctrine\DBAL\Types\Type')) {
            $this->markTestSkipped('Skipping test that requires "doctrine/dbal".');
        }

        $this->platform = new TestPlatform;
        $type = Type::getType('ip_multi');
        $this->assertInstanceOf(MultiType::class, $type);
        $this->type = $type;
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testIpConvertsToDatabaseValue()
    {
        $ip = IP::factory('12.34.56.78');

        $expected = $ip->getBinary();
        $actual = $this->type->convertToDatabaseValue($ip, $this->platform);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testInvalidIpConversionForDatabaseValue()
    {
        $this->expectException(\Doctrine\DBAL\Types\ConversionException::class);
        $this->type->convertToDatabaseValue('abcdefg', $this->platform);
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testNullConversionForDatabaseValue()
    {
        $this->assertNull($this->type->convertToDatabaseValue(null, $this->platform));
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testIpConvertsToPHPValue()
    {
        $ip = IP::factory('12.34.56.78');
        /** @var IP $dbIp */
        $dbIp = $this->type->convertToPHPValue($ip->getBinary(), $this->platform);
        $this->assertInstanceOf(IP::class, $dbIp);
        $this->assertEquals('12.34.56.78', $dbIp->getDotAddress());
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testIpObjectConvertsToPHPValue()
    {
        $ip = IP::factory('12.34.56.78');
        /** @var IP $dbIp */
        $dbIp = $this->type->convertToPHPValue($ip, $this->platform);
        $this->assertInstanceOf(IP::class, $dbIp);
        $this->assertSame($ip, $dbIp);
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testStreamConvertsToPHPValue()
    {
        $ip = IP::factory('12.34.56.78');
        $stream = fopen('php://memory','r+');
        // assertIsResource() isn't available for PHP 5.6 and 7.0 (PHPUnit < 7.0).
        $this->assertTrue(is_resource($stream));
        fwrite($stream, $ip->getBinary());
        rewind($stream);
        /** @var IP $dbIp */
        $dbIp = $this->type->convertToPHPValue($stream, $this->platform);
        $this->assertInstanceOf(IP::class, $dbIp);
        $this->assertEquals('12.34.56.78', $dbIp->getDotAddress());
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testInvalidIpConversionForPHPValue()
    {
        $this->expectException(\Doctrine\DBAL\Types\ConversionException::class);
        $this->type->convertToPHPValue('abcdefg', $this->platform);
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testNullConversionForPHPValue()
    {
        $this->assertNull($this->type->convertToPHPValue(null, $this->platform));
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testGetBinaryTypeDeclarationSQL()
    {
        $this->assertEquals('DUMMYBINARY()', $this->type->getSQLDeclaration(['length' => 16], $this->platform));
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testBindingTypeIsALargeObject()
    {
        $this->assertEquals(ParameterType::LARGE_OBJECT, $this->type->getBindingType());
    }
}
