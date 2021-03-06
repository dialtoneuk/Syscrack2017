<?php
	declare(strict_types=1);

	namespace Framework\Application;

	/**
	 * Lewis Lancaster 2016
	 *
	 * Class Loader
	 *
	 * @package Framework
	 */

	use Exception;

	use ReflectionMethod;

	/**
	 * Class Loader
	 * @package Framework\Application
	 */
	class Loader
	{

		/**
		 * @var array
		 */

		protected $classes = [];


		/**
		 * Loads the payload
		 */

		public function loadPaypload()
		{

			$array = $this->readPayload();

			if (empty($array))
			{

				throw new \Error('No payload');
			}

			foreach ($array as $class => $method)
			{

				if (isset($this->classes[$class]))
				{

					if ($this->isMethodStatic($class, $method))
						forward_static_call([$this->classes[$class], $method]);

					else
						call_user_func([$this->classes[$class], $method]);

				}
				else
				{

					if ($this->isMethodStatic($class, $method))
					{

						$this->classes[$class] = $method;

						forward_static_call([$class, $method]);
					}
					else
					{

						$this->createClass($class);

						call_user_func([$this->classes[$class], $method]);
					}
				}

			}
		}

		/**
		 * Creates a class
		 *
		 * @param $class
		 *
		 * @return mixed
		 */

		private function createClass($class)
		{

			$class_instance = new $class;

			if (isset($this->classes[$class]))
			{

				return $class_instance;
			}

			$this->classes[$class] = $class;

			return $class_instance;
		}

		/**
		 * Returns true if the instance is a static method
		 *
		 * @param $class
		 *
		 * @param $method
		 *
		 * @return bool
		 */

		private function isMethodStatic($class, $method)
		{

			try
			{

				$class = new ReflectionMethod($this->returnStaticHead($class, $method));

				if ($class->isStatic())
				{

					return true;
				}

				return false;
			} catch (Exception $error)
			{

				return false;
			}
		}

		/**
		 * Reads the payload
		 *
		 * @return mixed
		 */

		private function readPayload()
		{

			if (file_exists($this->getFileLocation()) == false)
				throw new \Error("Invalid file location: " . $this->getFileLocation() );

			return json_decode(file_get_contents($this->getFileLocation()), true);
		}

		/**
		 * Gets the payload location
		 *
		 * @return string
		 */

		private function getFileLocation()
		{

			if( defined("SYSCRACK_ROOT") == false )
			{

				if( defined("PHPUNIT_ROOT") )
					$root = PHPUNIT_ROOT;
				else
					$root = getcwd();
			}
			else
				$root = SYSCRACK_ROOT;

			return $root . '/data/config/autoloader.json';
		}

		/**
		 * Returns the head of a function
		 *
		 * @param $class
		 *
		 * @param $method
		 *
		 * @return string
		 */

		private function returnStaticHead($class, $method)
		{

			return sprintf('%s::%s', $class, $method);
		}
	}