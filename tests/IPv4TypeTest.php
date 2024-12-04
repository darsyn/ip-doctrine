<?php

namespace Darsyn\IP\Tests\Doctrine;

use Darsyn\IP\Doctrine\IPv4Type;
use Darsyn\IP\Version\IPv4 as IP;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class IPv4TypeTest extends TestCase
{
    private AbstractPlatform $platform;
    private IPv4Type $type;

    #[PHPUnit\BeforeClass]
    public static function setUpBeforeClassWithoutReturnDeclaration(): void
    {
        if (class_exists(Type::class)) {
            Type::addType('ipv4', IPv4Type::class);
        }
    }

    #[PHPUnit\Before]
    protected function setUpWithoutReturnDeclaration(): void
    {
        if (!class_exists(Type::class)) {
            $this->markTestSkipped('Skipping test that requires "doctrine/dbal".');
        }

        $this->platform = new TestPlatform;
        $type = Type::getType('ipv4');
        $this->assertInstanceOf(IPv4Type::class, $type);
        $this->type = $type;
    }

    #[PHPUnit\Test]
    public function testIpConvertsToDatabaseValue(): void
    {
        $ip = IP::factory('12.34.56.78');

        $expected = $ip->getBinary();
        $actual = $this->type->convertToDatabaseValue($ip, $this->platform);

        $this->assertEquals($expected, $actual);
    }

    #[PHPUnit\Test]
    public function testInvalidIpConversionForDatabaseValue(): void
    {
        $this->expectException(\Doctrine\DBAL\Types\ConversionException::class);
        $this->type->convertToDatabaseValue('abcdefg', $this->platform);
    }

    #[PHPUnit\Test]
    public function testNullConversionForDatabaseValue(): void
    {
        $this->assertNull($this->type->convertToDatabaseValue(null, $this->platform));
    }

    #[PHPUnit\Test]
    public function testIpConvertsToPHPValue(): void
    {
        $ip = IP::factory('12.34.56.78');
        /** @var IP $dbIp */
        $dbIp = $this->type->convertToPHPValue($ip->getBinary(), $this->platform);
        $this->assertInstanceOf(IP::class, $dbIp);
        $this->assertEquals('12.34.56.78', $dbIp->getDotAddress());
    }

    #[PHPUnit\Test]
    public function testIpObjectConvertsToPHPValue(): void
    {
        $ip = IP::factory('12.34.56.78');
        /** @var IP $dbIp */
        $dbIp = $this->type->convertToPHPValue($ip, $this->platform);
        $this->assertInstanceOf(IP::class, $dbIp);
        $this->assertSame($ip, $dbIp);
    }

    #[PHPUnit\Test]
    public function testStreamConvertsToPHPValue(): void
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

    #[PHPUnit\Test]
    public function testInvalidIpConversionForPHPValue(): void
    {
        $this->expectException(\Doctrine\DBAL\Types\ConversionException::class);
        $this->type->convertToPHPValue('abcdefg', $this->platform);
    }

    #[PHPUnit\Test]
    public function testNullConversionForPHPValue(): void
    {
        $this->assertNull($this->type->convertToPHPValue(null, $this->platform));
    }

    #[PHPUnit\Test]
    public function testGetBinaryTypeDeclarationSQL(): void
    {
        $this->assertEquals('DUMMYBINARY()', $this->type->getSQLDeclaration(['length' => 4], $this->platform));
    }

    #[PHPUnit\Test]
    public function testBindingTypeIsALargeObject(): void
    {
        $this->assertEquals(ParameterType::LARGE_OBJECT, $this->type->getBindingType());
    }
}
