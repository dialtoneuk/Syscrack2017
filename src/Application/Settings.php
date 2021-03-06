<?php
	declare(strict_types=1);

	namespace Framework\Application;

	use Framework\Application\Utilities\FileSystem;
	use Framework\Application\UtilitiesV2\Debug;

	/**
	 * Lewis Lancaster 2016
	 *
	 * Class Settings
	 *
	 * @package Framework
	 */



	class Settings
	{

		/**
		 * The settings for this web application
		 *
		 * @var array
		 */

		private static $settings = [];

		/**
		 * Gets the settings for this application
		 *
		 * @return array
		 */

		public static function settings()
		{

			return self::$settings;
		}


		/**
		 * Writes the settings to file
		 */

		public static function writeSettings()
		{

			FileSystem::writeJson( self::fileLocation(), self::$settings );
		}

		/**
		 * Removes a setting
		 *
		 * @param $setting_name
		 *
		 * @param bool $save
		 */

		public static function removeSetting($setting_name, $save = true)
		{

			unset(self::$settings[$setting_name]);

			if ($save)
				self::writeSettings();

		}

		/**
		 * Adds a setting
		 *
		 * @param $name
		 *
		 * @param $value
		 *
		 * @param bool $save
		 */

		public static function addSetting($name, $value, $save = true)
		{

			self::$settings[$name] = $value;

			if ($save)
				self::writeSettings();

		}

		/**
		 * Updates a setting
		 *
		 * @param $setting_name
		 *
		 * @param $setting_value
		 *
		 * @param bool $save
		 */

		public static function updateSetting($setting_name, $setting_value, $save = true)
		{

			self::$settings[$setting_name] = $setting_value;

			if ($save)
				Settings::writeSettings();

		}

		/**
		 * Returns true if we have that setting
		 *
		 * @param $setting_name
		 *
		 * @return bool
		 */

		public static function hasSetting($setting_name)
		{

			if (isset(self::$settings[$setting_name]) == false)
				return false;


			return true;
		}

		/**
		 * Checks is the settings are valid.
		 *
		 * @return bool
		 */

		public static function checkSettings()
		{

			try
			{

				self::settings();
			} catch (\Error $error)
			{

				return false;
			}

			return true;
		}

		/**
		 * @return array
		 *
		 */

		public static function setup()
		{

			self::loadSettings();

			if (self::$settings == null)
				throw new \Error();

			return self::$settings;
		}

		/**
		 * @param $setting
		 *
		 * @return mixed
		 */

		public static function setting($setting)
		{

			$settings = self::settings();

			if (isset($settings[$setting]) == false)
				throw new \Error('Setting does not exist: ' . $setting);

			$setting = $settings[$setting];

			if (is_array($setting) == false)
				if (self::hasParsableData($setting))
					return self::parseSetting($setting);


			return $setting;
		}

		/**
		 * Checks if we can parse anything in this setting
		 *
		 * @param $setting
		 *
		 * @return bool
		 */

		public static function hasParsableData($setting)
		{

			if( is_string( $setting ) == false )
				$setting = (string)$setting;

			if ( @preg_match('/\<(.*?)\>/', $setting) == false)
				return false;

			return true;
		}

		/**
		 * Gets the variables from the setting
		 *
		 * @param $setting
		 *
		 * @return array
		 */

		private static function getRegexMatch($setting)
		{

			preg_match("/\<php(.*?)\>/", $setting, $array);

			if (empty($array))
				throw new \Error();

			return $array[1];
		}

		/**
		 * This function parses the setting and replaces any of the magic brackets with their respective variable values
		 *
		 * @param $setting
		 *
		 * @return array|bool
		 */

		public static function parseSetting($setting)
		{

			$match = self::getRegexMatch($setting);
			$parsed = null;

			if (Settings::setting('settings_php_enabled'))
			{

				try
				{

					eval("\$parsed = {$match};");
				} catch (\Error $error)
				{

					throw new \Error($error->getMessage());
				}
			}
			else
			{

				$parsed = $match;
			}

			return self::replaceMatches($setting, [
				[
					$match,
					$parsed
				]
			]);
		}

		/**
		 * Replace1s the matches with the variables value
		 *
		 * @param $setting
		 *
		 * @param $array
		 *
		 * @return mixed
		 */

		private static function replaceMatches($setting, $array)
		{

			foreach ($array as $value)
				$setting = str_replace("<php" . $value[0] . ">", $value[1], $setting);


			return $setting;
		}

		/**
		 * Loads the settings
		 */

		public static function loadSettings()
		{

			$settings = self::readSettings();

			if (empty($settings))
				throw new \Error();

			self::$settings = $settings;
		}

		/**
		 * Returns true if we can find our settings file ( aka it exists )
		 *
		 * @return bool
		 */

		public static function canFindSettings()
		{

			if ( Filesystem::exists( self::fileLocation('settings.json') ) == false)
				return false;


			return true;
		}

		/**
		 * Reads the specific settings
		 *
		 * @return mixed
		 */

		private static function readSettings()
		{

			if (FileSystem::exists(self::fileLocation() ) == false )
				self::makeUserSettings();

			return( FileSystem::readJson( self::fileLocation() ) );
		}

		/**
		 * Makes unique user settings
		 */

		private function makeUserSettings()
		{

			if( FileSystem::exists( self::fileLocation( "settings.json") ) == false )
				throw new \Error("file does not exist: " . self::fileLocation( "settings.json" ) );

			if( Debug::isCMD() )
				Debug::message("created user_settings.json using default settings value " );

			$data = FileSystem::read( self::fileLocation( "settings.json") );
			FileSystem::write( self::fileLocation(), $data );
		}

		/**
		 * The file location of the settings
		 *
		 * @return string
		 */

		public static function fileLocation( $file="user_settings.json")
		{

			return FileSystem::separate("data", "config", "settings", $file );
		}
	}