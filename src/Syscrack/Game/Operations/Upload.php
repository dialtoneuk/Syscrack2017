<?php
	declare(strict_types=1);

	namespace Framework\Syscrack\Game\Operations;

	/**
	 * Lewis Lancaster 2017
	 *
	 * Class Upload
	 *
	 * @package Framework\Syscrack\Game\Operations
	 */

	use Framework\Application\Settings;
	use Framework\Application\Utilities\PostHelper;
	use Framework\Syscrack\Game\Bases\BaseOperation;


	/**
	 * Class Upload
	 * @package Framework\Syscrack\Game\Operations
	 */
	class Upload extends BaseOperation
	{

		/**
		 * @return array
		 */

		public function configuration()
		{

			return [
				'allowsoftware' => false,
				'allowlocal' => false,
				'requiresoftware' => false,
				'requireloggedin' => true,
				'allowpost' => false,
				'allowcustomdata' => true,
			];
		}

		/**
		 * @param $timecompleted
		 * @param $computerid
		 * @param $userid
		 * @param $process
		 * @param array $data
		 *
		 * @return bool
		 */

		public function onCreation($timecompleted, $computerid, $userid, $process, array $data)
		{


			if ($this->checkData($data, ['ipaddress']) == false)
				return false;

			if ($this->checkCustomData($data, ['softwareid']) == false)
				return false;

			if (self::$software->softwareExists($data['custom']['softwareid']) == false)
				return false;

			$software = self::$software->getSoftware($data['custom']['softwareid']);

			if ($this->hasSpace($this->computerAtAddress($data['ipaddress']), $software->size) == false)
				return false;

			if (self::$computer->hasSoftware($computerid, $software->softwareid) == false)
				return false;

			if (self::$computer->isInstalled($computerid, $software->softwareid) == true)
				return false;

			return true;
		}

		/**
		 * @param $timecompleted
		 * @param $timestarted
		 * @param $computerid
		 * @param $userid
		 * @param $process
		 * @param array $data
		 *
		 * @return bool|null|string
		 */

		public function onCompletion($timecompleted, $timestarted, $computerid, $userid, $process, array $data)
		{

			if ($this->checkData($data, ['ipaddress']) == false)
				return false;

			if (self::$internet->ipExists($data['ipaddress']) == false)
				return false;

			if ($this->checkCustomData($data, ['softwareid']) == false)
				return false;

			if (self::$software->softwareExists($data['custom']['softwareid']) == false)
				return false;

			$software = self::$software->getSoftware($data['custom']['softwareid']);

			if (self::$software->hasData($software->softwareid) == true && self::$software->keepData($software->softwareid))
			{

				$softwaredata = self::$software->getSoftwareData($software->softwareid);

				if (self::$software->checkSoftwareData($software->softwareid, ['allowanondownloads']) == true)
					return false;

				if (self::$software->checkSoftwareData($software->softwareid, ['editable']) == true)
					return false;

				$new_softwareid = self::$software->copySoftware($software->softwareid, $this->computerAtAddress($data['ipaddress']), $userid, false, $softwaredata);
			}
			else
				$new_softwareid = self::$software->copySoftware($software->softwareid, $this->computerAtAddress($data['ipaddress']), $userid);


			self::$computer->addSoftware($this->computerAtAddress($data['ipaddress']), $new_softwareid, $software->type);

			if (self::$computer->hasSoftware($this->computerAtAddress($data['ipaddress']), $new_softwareid) == false)
				return false;

			$this->logUpload($software, $this->computerAtAddress($data['ipaddress']), self::$computer->getComputer($computerid)->ipaddress);
			$this->logLocal($software, $data['ipaddress']);

			if( parent::onCompletion(
					$timecompleted,
					$timestarted,
					$computerid,
					$userid,
					$process,
					$data) == false )
				return false;
			else if (isset($data['redirect']) == false)
				return true;
			else
				return ($data['redirect']);
		}

		/**
		 * @param $computerid
		 * @param $ipaddress
		 * @param null $softwareid
		 *
		 * @return int
		 */

		public function getCompletionSpeed($computerid, $ipaddress, $softwareid = null)
		{

			return $this->calculateProcessingTime($computerid, Settings::setting('hardware_type_upload'), 20, $softwareid);
		}

		/**
		 * @param $ipaddress
		 * @param $userid
		 *
		 * @return array|null
		 */

		public function getCustomData($ipaddress, $userid)
		{

			if (PostHelper::hasPostData() == false)
			{

				return null;
			}

			if (PostHelper::checkForRequirements(['softwareid']) == false)
			{

				return null;
			}

			return [
				'softwareid' => PostHelper::getPostData('softwareid')
			];
		}

		/**
		 * @param $software
		 * @param $computerid
		 * @param $ipaddress
		 */

		private function logUpload($software, $computerid, $ipaddress)
		{

			$this->logToComputer('Uploaded file ' . $software->softwarename . ' (' . $software->level . ') on root', $computerid, $ipaddress);
		}

		/**
		 * Logs to the local log
		 *
		 * @param $software
		 * @param $ipaddress
		 */

		private function logLocal($software, $ipaddress)
		{

			$this->logToComputer('Uploaded file ' . $software->softwarename . ' (' . $software->level . ') on <' . $ipaddress . '>', self::$computer->getComputer(self::$computer->computerid())->computerid, 'localhost');
		}
	}