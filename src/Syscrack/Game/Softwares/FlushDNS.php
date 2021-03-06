<?php
	declare(strict_types=1);

	namespace Framework\Syscrack\Game\Softwares;

	/**
	 * Lewis Lancaster 2017
	 *
	 * Class FlushDNS
	 *
	 * @package Framework\Syscrack\Game\Softwares
	 */

	use Framework\Syscrack\Game\Bases\BaseSoftware;
	use Framework\Syscrack\Game\Tool;

	/**
	 * Class FlushDNS
	 * @package Framework\Syscrack\Game\Softwares
	 */
	class FlushDNS extends BaseSoftware
	{

		/**
		 * The configuration of this Structure
		 *
		 * @return array
		 */

		public function configuration()
		{

			return [
				'uniquename' => 'flushdns',
				'extension' => '.cmd',
				'type' => 'flushdns',
				'installable' => true,
				'executable' => true,
				'localexecuteonly' => true,
			];
		}

		/**
		 * @param null $userid
		 * @param null $sofwareid
		 * @param null $computerid
		 *
		 * @return Tool
		 */

		public function tool($userid = null, $sofwareid = null, $computerid = null): Tool
		{

			$tool = new Tool("Reset Address", "info");
			$tool->setAction('resetaddress');
			$tool->addInput('accountnumber', 'accounts');
			$tool->isExternal();
			$tool->isComputerType('isp');
			$tool->icon = "globe";

			return ($tool);
		}
	}