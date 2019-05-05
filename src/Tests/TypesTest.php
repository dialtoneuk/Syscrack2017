<?php
/**
 * Created by PhpStorm.
 * User: newsy
 * Date: 05/05/2019
 * Time: 21:38
 */

namespace Framework\Tests;

use Framework\Application\UtilitiesV2\Debug;
use Framework\Syscrack\Game\Types;

class TypesTest extends BaseTestCase
{

    /**
     * @var Types
     */

    protected static $types;

    public static function setUpBeforeClass(): void
    {

        self::$types = new Types();
        parent::setUpBeforeClass(); // TODO: Change the autogenerated stub
    }

    public function testGet()
    {

        $this->assertNotEmpty( self::$types->get() );
        Debug::echo( self::$types->get() );
    }
}
