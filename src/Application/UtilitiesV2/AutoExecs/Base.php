<?php
	declare(strict_types=1);
	/**
	 * Created by PhpStorm.
	 * User: lewis
	 * Date: 06/08/2018
	 * Time: 01:50
	 */

	namespace Framework\Application\UtilitiesV2\AutoExecs;


	use Framework\Application\Session;
	use Framework\Application\UtilitiesV2\Container;
	use Framework\Application\UtilitiesV2\Interfaces\AutoExec;
	use Framework\Database\Manager;

	/**
	 * Class Base
	 * @package Framework\Application\UtilitiesV2\AutoExecs
	 */
	abstract class Base implements AutoExec
	{

		/**
		 * @var Session
		 */

		protected static $session;

		/**
		 * @var Manager
		 */

		protected static $database;

		/**
		 * Base constructor.
		 * @throws \Error
		 */

		public function __construct()
		{

			if ( isset( self::$session ) == false )
				self::$session = new Session();

			if ( isset( self::$database ) == false )
				self::$database = new Manager( true );

			if( isset( self::$session ) == false )
				self::$session  = Container::get("session");
		}

		/**
		 * @param array $data
		 *
		 * @return bool
		 */

		public function execute(array $data)
		{

			return( true );
		}
	}