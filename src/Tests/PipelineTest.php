<?php
	/**
	 * Created by PhpStorm.
	 * User: newsy
	 * Date: 18/06/2019
	 * Time: 03:52
	 */

	namespace Framework\Tests;

	use Framework\Application\UtilitiesV2\Pipeline;

	class PipelineTest extends BaseTestCase
	{

		/**
		 * @var Pipeline
		 */

		protected static $pipeline;

		/**
		 *
		 */

		public static function setUpBeforeClass(): void
		{

			if( isset( self::$pipeline ) == false )
				self::$pipeline = new Pipeline();

			parent::setUpBeforeClass(); // TODO: Change the autogenerated stub
		}

		/**
		 *
		 */

		public function testRead()
		{

			$result = self::$pipeline->read();

			self::assertNotEmpty( $result );
		}

		public function testProcess()
		{

			$result = self::$pipeline->process( true );

			self::assertNotEmpty( $result );
		}
	}
