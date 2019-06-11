<?php
	declare(strict_types=1);

	namespace Framework\Application\UtilitiesV2\Scripts;

	/**
	 * Created by PhpStorm.
	 * User: lewis
	 * Date: 31/08/2018
	 * Time: 18:10
	 */

	use Framework\Application\UtilitiesV2\Container;
	use Framework\Application\UtilitiesV2\Debug;
	use Framework\Application\UtilitiesV2\Format;

	/**
	 * Class SysInfo
	 * @package Framework\Application\UtilitiesV2\Scripts
	 */
	class SysInfo extends Base
	{

		/**
		 * @var array
		 */

		protected $info;

		/**
		 * SysInfo constructor.
		 */

		public function __construct()
		{

			$this->info = $this->default();
			parent::__construct();
		}

		/**
		 * @param $arguments
		 *
		 * @return bool
		 */

		public function execute($arguments)
		{

			//Push some lines onto the stack which are only excessible when we execute
			array_push($this->info, " loaded scripts: " . count(Container::get("application")->getScripts()->scripts() ));

			/** @noinspection PhpUnusedLocalVariableInspection */
			$keys = array_keys($arguments);

			if (isset($arguments["detailed"]) == false)
			{
				//Cute little hack to allow easier use with help
				$keys = array_keys($arguments);

				if (empty($keys) == false)
					if ($keys[0] == "detailed")
						$arguments["detailed"] = true;
			}


			if (isset($arguments["detailed"]) && $arguments["detailed"] == true)
			{

				array_push($this->info, "\nConstants\n");
				array_push($this->info, get_defined_constants());
			}
			//Check for trailing new line if not add one
			if (last($this->info) !== "\n" && array_has($this->info, "\n") == false)
				array_push($this->info, "\n");

			foreach ($this->info as $item)
			{

				if (is_string($item) == false)
					$string = print_r($item);
				else
					$string = $item;

				Debug::echo($string);
			}

			$this->info = $this->default();

			return parent::execute($arguments); // TODO: Change the autogenerated stub
		}

		/**
		 * @return array|null
		 */

		public function requiredArguments()
		{

			return parent::requiredArguments();
		}

		/**
		 * @return array
		 */

		public function help()
		{

			return ([
				"arguments" => $this->requiredArguments(),
				"help" => "Execute sysinfo detailed in order to view more detailed information."
			]);
		}

		/**
		 * @return array
		 */

		private function default()
		{

			return ([
				"\n\nSyscrack Terminal Control Center (STCC)",
				" - Written by Lewis Lancaster (30/04/2019) in PHP 7\n",
				"Instance Info:\n",
				" php version: " . PHP_VERSION,
				" sapi: " . php_sapi_name(),
				" pid: " . getmypid(),
				" verbosity: " . Debug::verbosity(),
				" cwd: " . getcwd(),
				" syscrack root: " . SYSCRACK_ROOT,
				" initialized: " . Format::timestamp(),
				" session: " . @Debug::$session
			]);
		}
	}