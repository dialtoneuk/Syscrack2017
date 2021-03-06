<?php
	declare(strict_types=1);

	namespace Framework\Syscrack\Game;

	/**
	 * Lewis Lancaster 2017
	 *
	 * Class BaseComputer
	 *
	 * @package Framework\Syscrack\Game
	 */

	use Framework\Application;
	use Framework\Application\UtilitiesV2\Container;
	use Framework\Application\Settings;
	use Framework\Application\Utilities\Factory;
	use Framework\Application\Utilities\FileSystem;
	use Framework\Database\Tables\Computer as Database;
	use Framework\Syscrack\Game\Bases\BaseComputer;
	use Framework\Syscrack\Game\Interfaces\Computer as Structure;

	/**
	 * Class Computer
	 * @package Framework\Syscrack\Game
	 */
	class Computer
	{

		/**
		 * @var Factory;
		 */

		protected static $factory;

		/**
		 * @var Database
		 */

		protected static $database;

		/**
		 * @var Metadata
		 */

		protected static $metadata;

		/**
		 * BaseComputer constructor.
		 */

		public function __construct()
		{

			if (isset(self::$database) == false)
				self::$database = new Database();

			if( isset( self::$metadata ) == false )
				self::$metadata = new Metadata();

			if (empty(self::$factory))
				$this->loadComputers();
		}

		/**
		 * Loads the computers into the array
		 */

		public function loadComputers()
		{

			self::$factory = new Factory( Application::globals()->COMPUTER_NAMESPACE );

			foreach (FileSystem::getFilesInDirectory( Application::globals()->COMPUTER_FILEPATH ) as $file)
			{

				$name = FileSystem::getFileName($file);

				if (self::$factory->hasClass($name))
				{

					continue;
				}

				self::$factory->createClass($name);
			}
		}

		/**
		 * Gets a computer class
		 *
		 * @param $name
		 *
		 * @return \Framework\Syscrack\Game\Interfaces\Computer
		 */

		public function getComputerClass($name)
		{

			if (self::$factory->hasClass($name) == false)
			{

				throw new \Error();
			}

			return self::$factory->findClass($name);
		}

		/**
		 * @return array|Structure|Interfaces\Software|\stdClass
		 */

		public function getComputerClasses()
		{

			return (self::$factory->getAllClasses());
		}

		/**
		 * Returns true if we have this computer class
		 *
		 * @param $name
		 *
		 * @return bool
		 */

		public function hasComputerClass($name)
		{

			if (self::$factory->hasClass($name) == false)
			{

				return false;
			}

			return true;
		}

		/**
		 * Gets the computers configuration
		 *
		 * @param $name
		 *
		 * @return mixed
		 */

		public function getComputerConfiguration($name)
		{

			$configuration = self::$factory->findClass($name)->configuration();

			if (empty($configuration))
			{

				throw new \Error();
			}

			return $configuration;
		}

		/**
		 * Calls the computer start up method
		 *
		 * @param $name
		 *
		 * @return mixed
		 */

		public function onComputerStartup($name, $computerid, array $software = [], array $hardware = [], array $custom = [] )
		{

			/**
			 * @var $class BaseComputer
			 */
			$class = self::$factory->findClass($name);

			if (empty($class))
			{

				throw new \Error();
			}

			return $class->onStartup( $computerid, $software, $hardware, $custom );
		}

		/**
		 * Calls the computer start up method
		 *
		 * @param $name
		 *
		 * @return mixed
		 */

		public function onComputerReset($name, $computerid )
		{

			/**
			 * @var $class BaseComputer
			 */
			$class = self::$factory->findClass($name);

			if (empty($class))
				throw new \Error();

			if( self::$metadata->exists( $computerid ) == false )
				$metadata = null;
			else
				$metadata = self::$metadata->get( $computerid );

			return $class->onReset( $computerid, $metadata  );
		}

		/**
		 * Finds a computer by its type
		 *
		 * @param $type
		 *
		 * @return Computer|null
		 */

		public function findComputerByType($type)
		{

			$classes = self::$factory->getAllClasses();

			foreach ($classes as $class)
			{

				if ($class instanceof Structure == false)
				{

					throw new \Error();
				}

				/**
				 * @var Structure $class
				 */

				if ($class->configuration()['type'] == $type)
				{

					return $class;
				}
			}

			return null;
		}

		/**
		 * @param $computerid
		 */

		public function delete( $computerid )
		{

			self::$database->delete( $computerid );
		}

		/**
		 * Returns true if we have this computer type
		 *
		 * @param $type
		 *
		 * @return bool
		 */

		public function hasComputerType($type)
		{

			if ($this->findComputerByType($type) == null)
			{

				return false;
			}

			return true;
		}

		/**
		 * @return \Illuminate\Support\Collection|mixed
		 */

		public function getAllComputers()
		{

			return self::$database->getAllComputers();
		}

		/**
		 * Gets the computer count
		 *
		 * @return int
		 */

		public function getComputerCount()
		{

			return self::$database->getComputerCount();
		}

		/**
		 * Returns true if the user has computers
		 *
		 * @param $userid
		 *
		 * @return bool
		 */

		public function userHasComputers($userid)
		{

			if (self::$database->getComputersByUser($userid) == null)
			{

				return false;
			}

			return true;
		}

		/**
		 * Changes a computers IP address
		 *
		 * @param $computerid
		 *
		 * @param $address
		 */

		public function changeIPAddress($computerid, $address)
		{

			$array = [
				'ipaddress' => $address
			];

			self::$database->updateComputer($computerid, $array);
		}

		/**
		 * Formats a computer to the default software
		 *
		 * @param $computerid
		 */

		public function format($computerid)
		{

			$array = [
				'software' => json_encode([])
			];

			self::$database->updateComputer($computerid, $array);
		}

		/**
		 * Resets the hardware of a computer
		 *
		 * @param $computerid
		 */

		public function resetHardware($computerid)
		{

			$array = [
				'hardware' => json_encode([])
			];

			self::$database->updateComputer($computerid, $array);
		}

		/**
		 * Sets the hardware of a computer
		 *
		 * @param $computerid
		 *
		 * @param array $hardware
		 */

		public function setHardware($computerid, array $hardware)
		{

			$array = [
				'hardware' => json_encode($hardware)
			];

			self::$database->updateComputer($computerid, $array);
		}

		/**
		 * Gets the computer at the id
		 *
		 * @param $computerid
		 *
		 * @return mixed||\stdClass
		 */

		public function getComputer($computerid)
		{

			return self::$database->getComputer($computerid);
		}

		/**
		 * Gets the current list of software in the system
		 *
		 * @param $computerid
		 *
		 * @return array
		 */

		public function getComputerSoftware($computerid)
		{

			return json_decode($this->getComputer($computerid)->software, true);
		}

		/**
		 * Returns true if the computer exists
		 *
		 * @param $computerid
		 *
		 * @return bool
		 */

		public function computerExists($computerid)
		{

			if (self::$database->getComputer($computerid) == null)
				return false;

			return true;
		}

		/**
		 * Returns true if the computer has this software
		 *
		 * @param $computerid
		 *
		 * @param $softwareid
		 *
		 * @return bool
		 */

		public function hasSoftware($computerid, $softwareid)
		{

			$software = $this->getComputerSoftware($computerid);

			foreach ($software as $softwares)
			{

				if ($softwares['softwareid'] == $softwareid)
				{

					return true;
				}
			}

			return false;
		}

		/**
		 * Creates a new computer
		 *
		 * @param $userid
		 *
		 * @param $type
		 *
		 * @param $ipaddress
		 *
		 * @param array $software
		 *
		 * @param array $hardware
		 *
		 * @return int
		 */

		public function createComputer($userid, $type, $ipaddress, $software = [], $hardware = [])
		{

			$array = [
				'userid' => $userid,
				'type' => $type,
				'ipaddress' => $ipaddress,
				'software' => json_encode($software),
				'hardware' => json_encode($hardware)
			];

			return self::$database->insertComputer($array);
		}

		/**
		 * @param $computerid
		 * @param $softwareid
		 * @param $type
		 * @param null $userid
		 */

		public function addSoftware($computerid, $softwareid, $type, $userid = null)
		{

			if ($userid == null)
				$userid = @Container::get('session')->userid();

			$software = $this->getComputerSoftware($computerid);

			$software[] = [
				'softwareid' => $softwareid,
				'type' => $type,
				'installed' => false,
				'timeinstalled' => time(),
				'userid' => $userid
			];

			self::$database->updateComputer($computerid, ['software' => json_encode($software)]);
		}

		/**
		 * removes a software from the computers list
		 *
		 * @param $computerid
		 *
		 * @param $softwareid
		 */

		public function removeSoftware($computerid, $softwareid)
		{

			$software = $this->getComputerSoftware($computerid);

			if (empty($software))
			{

				throw new \Error();
			}

			foreach ($software as $key => $softwares)
			{

				if ($softwares['softwareid'] == $softwareid)
				{

					unset($softwares[$key]);
				}
			}

			self::$database->updateComputer($computerid, ['software' => json_encode($software)]);
		}

		/**
		 * Installs a software on the computer side software list
		 *
		 * @param $computerid
		 *
		 * @param $softwareid
		 */

		public function installSoftware($computerid, $softwareid)
		{

			$software = $this->getComputerSoftware($computerid);

			if (empty($software))
			{

				throw new \Error();
			}

			foreach ($software as $key => $softwares)
			{

				if ($softwares['softwareid'] == $softwareid)
				{

					$software[$key]['installed'] = true;
				}
			}

			self::$database->updateComputer($computerid, ['software' => json_encode($software)]);
		}

		/**
		 * Uninstalls a software
		 *
		 * @param $computerid
		 *
		 * @param $softwareid
		 */

		public function uninstallSoftware($computerid, $softwareid)
		{

			$software = $this->getComputerSoftware($computerid);

			if (empty($software))
			{

				throw new \Error();
			}

			foreach ($software as $key => $softwares)
			{

				if ($softwares['softwareid'] == $softwareid)
				{

					$software[$key]['installed'] = false;
				}
			}

			self::$database->updateComputer($computerid, ['software' => json_encode($software)]);
		}


		/**
		 * Gets the computers hardware
		 *
		 * @param $computerid
		 *
		 * @return array
		 */

		public function getComputerHardware($computerid)
		{

			return json_decode(self::$database->getComputer($computerid)->hardware, true);
		}

		/**
		 * Returns the main ( first ) computer
		 *
		 * @param $userid
		 *
		 * @return mixed|\stdClass
		 */

		public function getUserMainComputer($userid)
		{

			return self::$database->getComputersByUser($userid)[0];
		}

		/**
		 * Gets all the users computers
		 *
		 * @param $userid
		 *
		 * @return \Illuminate\Support\Collection|null
		 */

		public function getUserComputers($userid)
		{

			return self::$database->getComputersByUser($userid);
		}

		/**
		 * Gets the computers type
		 *
		 * @param $computerid
		 *
		 * @return mixed
		 */

		public function getComputerType($computerid)
		{

			return self::$database->getComputer($computerid)->type;
		}

		/**
		 * Gets all the installed software on a computer
		 *
		 * @param $computerid
		 *
		 * @return array
		 */

		public function getInstalledSoftware($computerid)
		{

			$software = $this->getComputerSoftware($computerid);

			$result = [];

			foreach ($software as $key => $value)
			{

				if ($value['installed'] == true)
				{

					$result[] = $value['softwareid'];
				}
			}

			return $result;
		}

		/**
		 * Gets the install cracker on the machine
		 *
		 * @param $computerid
		 *
		 * @return null
		 */

		public function getCracker($computerid)
		{

			$software = $this->getComputerSoftware($computerid);

			foreach ($software as $softwares)
			{

				if ($softwares['type'] == Settings::setting('software_cracker_type'))
				{

					if ($softwares['installed'] == false)
					{

						continue;
					}

					return $softwares['softwareid'];
				}
			}

			return null;
		}

		/**
		 * Gets the firewall
		 *
		 * @param $computerid
		 *
		 * @return null
		 */

		public function getFirewall($computerid)
		{

			$software = $this->getComputerSoftware($computerid);

			foreach ($software as $softwares)
			{

				if ($softwares['type'] == Settings::setting('software_hasher_type'))
				{

					if ($softwares['installed'] == false)
					{

						continue;
					}

					return $softwares['softwareid'];
				}
			}

			return null;
		}

		/**
		 * Gets the hasher
		 *
		 * @param $computerid
		 *
		 * @return null
		 */

		public function getHasher($computerid)
		{

			$software = $this->getComputerSoftware($computerid);

			foreach ($software as $softwares)
			{

				if ($softwares['type'] == Settings::setting('software_hasher_type'))
				{

					if ($softwares['installed'] == false)
					{

						continue;
					}

					return $softwares['softwareid'];
				}
			}

			return null;
		}

		/**
		 * Returns the collector
		 *
		 * @param $computerid
		 *
		 * @return null
		 */

		public function getCollector($computerid)
		{

			$software = $this->getComputerSoftware($computerid);

			foreach ($software as $softwares)
			{

				if ($softwares['type'] == Settings::setting('software_collector_type'))
				{

					if ($softwares['installed'] == false)
					{

						continue;
					}

					return $softwares['softwareid'];
				}
			}

			return null;
		}

		/**
		 * Gets the current connected user commputer
		 *
		 * @param $computerid
		 */

		public function setCurrentUserComputer($computerid)
		{

			$_SESSION['current_computer'] = $computerid;
		}

		/**
		 * Gets the current connected user computer
		 *
		 * @return mixed
		 */

		public function computerid()
		{

			return @$_SESSION['current_computer'];
		}

		/**
		 * Returns true if we have a current connected computer
		 *
		 * @return bool
		 */

		public function hasCurrentComputer()
		{

			if (isset($_SESSION['current_computer']) == false)
			{

				return false;
			}

			if (empty($_SESSION['current_computer']))
			{

				return false;
			}

			return true;
		}

		/**
		 * Checks if the user has this type of software installed
		 *
		 * @param $computerid
		 *
		 * @param $type
		 *
		 * @param bool $checkinstall
		 *
		 * @return bool
		 */

		public function hasType($computerid, $type, $checkinstall = true)
		{

			$software = $this->getComputerSoftware($computerid);

			foreach ($software as $softwares)
			{

				if ($softwares['type'] == $type)
				{

					if ($checkinstall && $softwares['installed'] == false)
					{

						continue;
					}

					return true;
				}
			}

			return false;
		}

		/**
		 * Gets a software by its name
		 *
		 * @param $computerid
		 *
		 * @param $softwarename
		 *
		 * @param bool $checkinstalled
		 *
		 * @return mixed|null
		 */

		public function getSoftwareByName($computerid, $softwarename, $checkinstalled = true)
		{

			$software = $this->getComputerSoftware($computerid);

			foreach ($software as $softwares)
			{

				if ($softwares['softwarename'] == $softwarename)
				{

					if ($checkinstalled)
					{

						if ($softwares['installed'] == true)
						{

							return $softwares;
						}
					}
					else
					{

						return $softwares;
					}
				}
			}

			return null;
		}

		/**
		 * Returns true if this software is installed
		 *
		 * @param $computerid
		 *
		 * @param $softwareid
		 *
		 * @return bool
		 */

		public function isInstalled($computerid, $softwareid)
		{

			$software = $this->getComputerSoftware($computerid);

			if (empty($software))
			{

				return false;
			}

			foreach ($software as $softwares)
			{

				if ($softwares['softwareid'] == $softwareid)
				{

					return $softwares['installed'];
				}
			}

			return false;
		}

		/**
		 * @param $computerid
		 * @param $type
		 * @param $userid
		 *
		 * @return bool
		 */

		public function installedByUser($computerid, $type, $userid)
		{
			$softwares = $this->getComputerSoftware($computerid);

			if (empty($softwares))
				return false;

			foreach ($softwares as $software)
				if ($software['type'] == $type)
					if (@$software['userid'] == $userid)
						if ($software['installed'])
							return true;

			return false;
		}

		/**
		 * Returns true if the computer is a bank
		 *
		 * @param $computerid
		 *
		 * @return bool
		 */

		public function isBank($computerid)
		{

			if ($this->getComputerType($computerid) !== Settings::setting('computers_type_bank'))
			{

				return false;
			}

			return true;
		}

		/**
		 * Returns true if the computer is a bitcoin server
		 *
		 * @param $computerid
		 *
		 * @return bool
		 */

		public function isBitcoin($computerid)
		{

			if ($this->getComputerType($computerid) !== Settings::setting('computers_type_bitcoin'))
			{

				return false;
			}

			return true;
		}

		/**
		 * Returns true if the computer is a market server
		 *
		 * @param $computerid
		 *
		 * @return bool
		 */

		public function isMarket($computerid)
		{

			if ($this->getComputerType($computerid) !== Settings::setting('computers_type_market'))
			{

				return false;
			}

			return true;
		}

		/**
		 * Returns true if the computer is an NPCs
		 *
		 * @param $computerid
		 *
		 * @return bool
		 */

		public function isNPC($computerid)
		{

			if ($this->getComputerType($computerid) !== Settings::setting('computers_type_npc'))
			{

				return false;
			}

			return true;
		}

		/**
		 * Returns true if the computer is a VPC
		 *
		 * @param $computerid
		 *
		 * @return bool
		 */

		public function isVPC($computerid)
		{

			if ($this->getComputerType($computerid) !== Settings::setting('computers_type_vpc'))
			{

				return false;
			}

			return true;
		}
	}