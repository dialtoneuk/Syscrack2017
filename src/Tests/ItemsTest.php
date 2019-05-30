<?php
	/**
	 * Created by PhpStorm.
	 * User: newsy
	 * Date: 15/05/2019
	 * Time: 01:37
	 */

	namespace Framework\Tests;

	use Framework\Syscrack\Game\Items;

	class ItemsTest extends BaseTestCase
	{

		/**
		 * @var Items
		 */

		protected static $items;
		protected static $item = 'colours';

		public static function setUpBeforeClass(): void
		{

			if( isset( self::$items ) == false )
				self::$items = new Items();

			parent::setUpBeforeClass(); // TODO: Change the autogenerated stub
		}

		public function testItem()
		{

			$this->assertNotEmpty( self::$items->item(self::$item) );

		}

		public function testHas()
		{

			$this->assertTrue( self::$items->has(self::$item) );
		}

		public function testTraded()
		{

			$this->assertTrue( self::$items->traded(self::$item,0,0,0) );
		}

		public function testUsed()
		{

			$this->assertTrue( self::$items->used(self::$item,0,0,0) );
		}

		public function testEquipped()
		{

			$this->assertTrue( self::$items->equipped(self::$item,0,0,0) );
		}

		public function testSettings()
		{

			$this->assertNotEmpty( self::$items->settings(self::$item) );
		}
	}