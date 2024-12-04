<?php

namespace Darsyn\IP\Doctrine;

use Darsyn\IP\IpInterface;
use Darsyn\IP\Version\Multi as IP;

/**
 * {@inheritDoc}
 */
class MultiType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    protected function getIpClass(): string
    {
        return IP::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function createIpObject(string $ip): IpInterface
    {
        return IP::factory($ip);
    }
}
