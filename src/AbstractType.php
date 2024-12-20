<?php

namespace Darsyn\IP\Doctrine;

use Darsyn\IP\Exception\IpException;
use Darsyn\IP\IpInterface;
use Darsyn\IP\Util\MbString;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;

/**
 * Field type mapping for the Doctrine Database Abstraction Layer (DBAL).
 *
 * IP fields will be stored as a string in the database and converted back to
 * the IP value object when querying.
 */
abstract class AbstractType extends Type
{
    const NAME = 'ip';
    /** @var int */
    const IP_LENGTH = 16;

    /**
     * @psalm-return class-string
     */
    abstract protected function getIpClass(): string;

    /**
     * @throws \Darsyn\IP\Exception\InvalidIpAddressException
     * @throws \Darsyn\IP\Exception\WrongVersionException
     */
    abstract protected function createIpObject(string $ip): IpInterface;

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getBinaryTypeDeclarationSQL(['length' => static::IP_LENGTH]);
    }

    /**
     * {@inheritdoc}
     * @throws \Doctrine\DBAL\Types\ConversionException
     * @return \Darsyn\IP\IpInterface|null
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        /** @var string|resource|\Darsyn\IP\IpInterface|null $value */
        // PostgreSQL will return the binary data as a resource instead of a string (like MySQL).
        if (\is_resource($value)) {
            if (\get_resource_type($value) !== 'stream' || false === $value = \stream_get_contents($value)) {
                throw new ConversionException(sprintf(
                    'Could not convert database value to Doctrine Type "%s" (could not convert non-stream resource to a string).',
                    self::NAME
                ));
            }
        }
        /** @var string|\Darsyn\IP\IpInterface|null $value */
        if (empty($value)) {
            return null;
        }
        if (\is_object($value)) {
            if (!\is_a($value, $this->getIpClass(), false)) {
                throw ValueNotConvertible::new($value, $this->getIpClass());
            }
            return $value;
        }
        try {
            return $this->createIpObject($value);
        } catch (IpException $e) {
            throw ValueNotConvertible::new($value, $this->getIpClass(), null, $e);
        }
    }

    /**
     * {@inheritdoc}
     * @throws \Doctrine\DBAL\Types\ConversionException
     * @return string|null
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (empty($value)) {
            return null;
        }
        if (\is_string($value)) {
            try {
                $value = $this->createIpObject($value);
            } catch (IpException $e) {
                throw new ConversionException(sprintf(
                    'Could not convert PHP value "%s" to valid IP address ready for database insertion.',
                    (string) $value
                ), 0, $e);
            }
        }

        if (!\is_object($value)) {
            throw new ConversionException(sprintf(
                'Could not convert PHP value of type "%s" to valid IP address ready for database insertion.',
                gettype($value)
            ));
        }

        if (!\is_a($value, IpInterface::class, false)) {
            throw new ConversionException(sprintf(
                'Could not convert PHP object "%s" to a valid IP instance ready for database insertion.',
                \get_class($value)
            ));
        }

        if (static::IP_LENGTH !== $valueLength = MbString::getLength($value->getBinary())) {
            throw new ConversionException(sprintf(
                'Cannot fit IPv%d address (%d bytes) into database column (%d bytes). Reconfigure Doctrine types to use a different IP class.',
                $value->getVersion(),
                $valueLength,
                static::IP_LENGTH
            ));
        }

        return $value->getBinary();
    }

    /**
     * {@inheritdoc}
     * @return ParameterType
     */
    public function getBindingType(): ParameterType
    {
        return ParameterType::LARGE_OBJECT;
    }
}
