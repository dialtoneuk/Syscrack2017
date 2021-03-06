<?php
	declare(strict_types=1);

	namespace Framework\Syscrack\Game;

	/**
	 * Lewis Lancaster 2017
	 *
	 * Class Internet
	 *
	 * @package Framework\Syscrack\Game
	 */

	use Framework\Application\UtilitiesV2\Container;
	use Framework\Application\Utilities\Hashes;
	use Framework\Database\Tables\Computer;

	/**
	 * Class Internet
	 * @package Framework\Syscrack\Game
	 */
	class Internet
	{

		/**
		 * @var Computer
		 */

		protected static $computer;

		/**
		 * Internet constructor.
		 */

		public function __construct()
		{

			if( isset( self::$computer ) == false )
				self::$computer = new Computer();
		}

		/**
		 * Returns true if the address exists
		 *
		 * @param $ipaddress
		 *
		 * @return bool
		 */

		public function ipExists($ipaddress)
		{

			if (self::$computer->getComputerByIPAddress($ipaddress) == null)
			{

				return false;
			}

			return true;
		}

		/**
		 * Gets the computers by their IP address
		 *
		 * @param $ipaddress
		 *
		 * @return mixed|null
		 */

		public function computer($ipaddress)
		{

			return self::$computer->getComputerByIPAddress($ipaddress);
		}

		/**
		 * Gets the computers password
		 *
		 * @param $ipaddress
		 *
		 * @return mixed
		 */

		public function getComputerPassword($ipaddress)
		{

			return self::$computer->getComputerByIPAddress($ipaddress)->password;
		}

		/**
		 * Gets the computers address
		 *
		 * @param $computerid
		 *
		 * @return mixed
		 */

		public function getComputerAddress($computerid)
		{

			return self::$computer->getComputer($computerid)->ipaddress;
		}

		/**
		 * Changes the computers address
		 *
		 * @param $computerid
		 *
		 * @return string
		 */

		public function changeAddress($computerid)
		{

			$address = $this->getIP();

			if ($this->ipExists($address))
			{

				throw new \Error();
			}

			$array = [
				'ipaddress' => $address
			];

			self::$computer->updateComputer($computerid, $array);

			return $address;
		}

		/**
		 * Returns true if the user has a current connection
		 *
		 * @return bool
		 */

		public function hasCurrentConnection()
		{

			if (isset($_SESSION['connected_ipaddress']) == false)
			{

				return false;
			}

			if (empty($_SESSION['connected_ipaddress']) || $_SESSION['connected_ipaddress'] == null)
			{

				return false;
			}

			return true;
		}

		/**
		 * Gets the current connected address of the computer
		 *
		 * @return null
		 */

		public function getCurrentConnectedAddress()
		{

			if (Container::exist('session') == false)
			{

				return null;
			}

			$session = Container::get('session');

			if ($session->isLoggedIn() == false)
			{

				return null;
			}

			if (isset($_SESSION['connected_ipaddress']) == false)
			{

				return null;
			}

			return $_SESSION['connected_ipaddress'];
		}

		/**
		 * Sets the current connected address of the user
		 *
		 * @param $ipaddress
		 */

		public function setCurrentConnectedAddress($ipaddress)
		{

			$_SESSION['connected_ipaddress'] = $ipaddress;
		}

		/**
		 * Changes the computers password
		 *
		 * @param $computerid
		 *
		 * @return string
		 */

		public function changePassword($computerid)
		{

			$password = $this->getPassword();

			$array = [
				'password' => $password
			];

			self::$computer->updateComputer($computerid, $array);

			return $password;
		}

		/**
		 * Returns a new random IP address
		 *
		 * @return string
		 */

		public function getIP()
		{

			return rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
		}

		/**
		 * Returns a new random computer password
		 *
		 * @return string
		 */

		private function getPassword()
		{

			return Hashes::randomBytes(rand(6, 18));
		}
	}