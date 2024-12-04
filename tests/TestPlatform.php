<?php

namespace Darsyn\IP\Tests\Doctrine;


use Doctrine\DBAL\Platforms\SQLitePlatform;

class TestPlatform extends SQLitePlatform
{
    public function getBinaryTypeDeclarationSQL(array $column): string
    {
        return 'DUMMYBINARY()';
    }
}
