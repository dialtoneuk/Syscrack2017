<?php
	declare(strict_types=1);
	/**
	 * Created by PhpStorm.
	 * User: lewis
	 * Date: 22/07/2018
	 * Time: 01:01
	 */

	namespace Framework\Application\UtilitiesV2\Scripts;


	use Framework\Application\UtilitiesV2\Debug;
	use Framework\Application\UtilitiesV2\Migrator;

	/**
	 * Class AutoMigrate
	 * @package Framework\Application\UtilitiesV2\Scripts
	 */
	class AutoMigrate extends Base
	{

		/**
		 * @var Migrator
		 */

		protected $migrator;


		/**
		 * @param $arguments
		 *
		 * @return bool
		 * @throws \Error
		 */

		public function execute($arguments)
		{

			Debug::echo("Instancing Migrator", 4);

			$this->migrator = new Migrator();

			Debug::echo("Calling migrator", 4);

			try
			{

				$this->migrator->process();
			} catch (\Error $error)
			{

				return (false);
			}

			return (true);
		}

		/**
		 * @return array|null
		 */

		public function requiredArguments()
		{

			return (null);
		}

		/**
		 * @return array
		 */

		public function help()
		{
			return ([
				"arguments" => $this->requiredArguments(),
				"help" => "Migrates database (creates tables) and some json files. Should be used when installing Colourspace and then never touched again."
			]);
		}
	}