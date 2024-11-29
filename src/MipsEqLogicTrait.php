<?php

trait MipsEqLogicTrait {
	private static function getCommandsFileContent(string $filePath) {
		if (!file_exists($filePath)) {
			throw new RuntimeException("Fichier de configuration non trouvé:{$filePath}");
		}
		$content = file_get_contents($filePath);
		if (!is_json($content)) {
			throw new RuntimeException("Fichier de configuration incorrecte:{$filePath}");
		}
		$content = translate::exec($content, realpath($filePath));
		return json_decode($content, true);
	}

	public function createCommandsFromConfigFile(string $filePath, string $commandsKey) {
		$commands = self::getCommandsFileContent($filePath);
		$this->createCommandsFromConfig($commands[$commandsKey]);
	}

	public function createCommandsFromConfig(array $commands) {
		$link_cmds = array();
		foreach ($commands as $cmdDef) {
			/** @var cmd */
			$cmd = $this->getCmd(null, $cmdDef["logicalId"]);
			if (!is_object($cmd)) {
				log::add(__CLASS__, 'debug', 'create:' . $cmdDef["logicalId"] . '/' . $cmdDef["name"]);
				$cmd = new cmd();
				$cmd->setLogicalId($cmdDef["logicalId"]);
				$cmd->setEqLogic_id($this->getId());
				$cmd->setName(__($cmdDef["name"], __FILE__));
				if (isset($cmdDef["isHistorized"])) {
					$cmd->setIsHistorized($cmdDef["isHistorized"]);
				}
				if (isset($cmdDef["isVisible"])) {
					$cmd->setIsVisible($cmdDef["isVisible"]);
				}
				if (isset($cmdDef['template'])) {
					foreach ($cmdDef['template'] as $key => $value) {
						$cmd->setTemplate($key, $value);
					}
				}
			}
			$cmd->setType($cmdDef["type"]);
			$cmd->setSubType($cmdDef["subtype"]);
			if (isset($cmdDef["generic_type"])) {
				$cmd->setGeneric_type($cmdDef["generic_type"]);
			}
			if (isset($cmdDef["order"])) {
				$cmd->setOrder($cmdDef["order"]);
			}
			if (isset($cmdDef['display'])) {
				foreach ($cmdDef['display'] as $key => $value) {
					if ($key == 'title_placeholder' || $key == 'message_placeholder') {
						$value = __($value, __FILE__);
					}
					$cmd->setDisplay($key, $value);
				}
			}
			if (isset($cmdDef["unite"])) {
				$cmd->setUnite($cmdDef["unite"]);
			}

			if (isset($cmdDef['configuration'])) {
				foreach ($cmdDef['configuration'] as $key => $value) {
					$cmd->setConfiguration($key, $value);
				}
			}

			if (isset($cmdDef['value'])) {
				$link_cmds[$cmdDef["logicalId"]] = $cmdDef['value'];
			}

			$cmd->save();

			if (isset($cmdDef['initialValue'])) {
				$cmdValue = $cmd->execCmd();
				if ($cmdValue == '') {
					$this->checkAndUpdateCmd($cmdDef["logicalId"], $cmdDef['initialValue']);
				}
			}
		}

		foreach ($link_cmds as $cmd_logicalId => $link_logicalId) {
			/** @var cmd */
			$cmd = $this->getCmd(null, $cmd_logicalId);
			/** @var cmd */
			$linkCmd = $this->getCmd(null, $link_logicalId);

			if (is_object($cmd) && is_object($linkCmd)) {
				$cmd->setValue($linkCmd->getId());
				$cmd->save();
			}
		}
	}

	private static function executeAsync(string $_method, $_option = null, $_date = 'now') {
		if (!method_exists(__CLASS__, $_method)) {
			throw new InvalidArgumentException("Method provided for executeAsync does not exist: {$_method}");
		}

		$cron = new cron();
		$cron->setClass(__CLASS__);
		$cron->setFunction($_method);
		if (isset($_option)) {
			$cron->setOption($_option);
		}
		$cron->setOnce(1);
		$scheduleTime = strtotime($_date);
		$cron->setSchedule(cron::convertDateToCron($scheduleTime));
		$cron->save();
		if ($scheduleTime <= strtotime('now')) {
			$cron->run();
			log::add(__CLASS__, 'debug', "Task '{$_method}' executed now");
		} else {
			log::add(__CLASS__, 'debug', "Task '{$_method}' scheduled at {$_date}");
		}
	}

	public function getCmdInfoValue($logicalId, $default = '') {
		$cmd = $this->getCmd(null, $logicalId);
		if (!is_object($cmd)) return $default;
		return $cmd->execCmd();
	}

	protected static function getSocketPort() {
		return 0;
	}

	public static function sendToDaemon($params) {
		$deamon_info = self::deamon_info();
		if ($deamon_info['state'] != 'ok') {
			throw new RuntimeException("Le démon n'est pas démarré");
		}
		$port = self::getSocketPort();
		if ($port < 1 || $port > 65535) {
			throw new InvalidArgumentException("Please implement static function getSocketPort and return a valid port number");
		}

		log::add(__CLASS__, 'debug', 'params to send to daemon:' . json_encode($params));
		$params['apikey'] = jeedom::getApiKey(__CLASS__);
		$payLoad = json_encode($params);
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_connect($socket, '127.0.0.1', $port);
		socket_write($socket, $payLoad, strlen($payLoad));
		socket_close($socket);
	}

	public static function getConfigForCommunity() {
		/** @var plugin */
		$plugin = plugin::byId(__CLASS__);

		$return = "<br>*Remplacez ce texte par une capture d'écran de la page santé Jeedom*<br><br>";
		if ($plugin->getHasDependency()) {
			if (file_exists(log::getPathToLog($plugin->getId() . '_update'))) {
				$return .= "Log des dépendances:<br>```<br><br>collez ici le contenu du log {$plugin->getId()}_update<br><br>```<br>";
			} elseif (file_exists(log::getPathToLog($plugin->getId() . '_packages'))) {
				$return .= "Log des dépendances:<br>```<br><br>collez ici le contenu du log {$plugin->getId()}_packages<br><br>```<br>";
			}
		}
		if ($plugin->getHasOwnDeamon()) {
			$return .= "Log du démon:<br>```<br><br>collez ici le contenu du log {$plugin->getId()}_daemon<br><br>```<br>";
		}
		$return .= "Log du plugin:<br>```<br><br>collez ici le contenu du log {$plugin->getId()}<br><br>```<br>";

		return $return;
	}

	/**
	 * Return the package detail from the requirement line if it is a valid requirement line
	 *
	 * @param string $requirementLine
	 * @param string[] $packageDetail if the requirement line is valid, it will contain the following:
	 * - [0] the full requirement line
	 * - [1] the package name
	 * - [2] the operator
	 * - [3] the version
	 * @return bool true if the requirement line is valid, false otherwise
	 */
	private static function getRequiredPackageDetail(string $requirementLine, &$packageDetail) {
		// regex explanation to match https://pip.pypa.io/en/stable/reference/requirement-specifiers/:
		// valid name: https://packaging.python.org/en/latest/specifications/name-normalization/
		// optional spaces
		// optional brackets with a set of “extras” that serve to install optional dependencies
		// optionally constraints to apply on the version of the package which will consist of
		// - an operator: one of ==, >=, ~=
		// - a version: a series of digits and dots
		// optional spaces
		// end of line or a semicolon or a comma with environment markers
		return preg_match('/^(?<name>[A-Z0-9]|[A-Z0-9][A-Z0-9._-]*[A-Z0-9])\s*(?:\[.*\])?\s*(?:(?<operator>[>=~]=)\s*(?<version>[\d+\.?]+))?\s*(?:$|;.*|,.*)/i', $requirementLine, $packageDetail) === 1;
	}

	/**
	 * Return the installed package detail from the installed packages list if it exists
	 *
	 * @param string $packageName
	 * @param array $installedPackages the list of installed packages
	 * @param string[] $packageDetail if the package is installed, it will contain the following:
	 * - [0] 'package==version'
	 * - [1] the version
	 * @return bool true if the package is installed, false otherwise
	 */
	private static function getInstalledPackageDetail(string $packageName, array $installedPackages, &$packageDetail) {
		$packages = "||" . join("||", $installedPackages);
		return preg_match('/\|\|\K' . $packageName . '==([\d+\.?]+)/i', $packages, $packageDetail) === 1;
	}

	private static function pythonRequirementsInstalled(string $pythonPath, string $requirementsPath) {
		if (!file_exists($pythonPath) || !file_exists($requirementsPath)) {
			return false;
		}
		exec("{$pythonPath} -m pip freeze", $packages_installed);
		exec("cat {$requirementsPath}", $requirements);

		foreach ($requirements as $requirement_line) {
			if (self::getRequiredPackageDetail($requirement_line, $package_details)) {
				if (self::getInstalledPackageDetail($package_details['name'], $packages_installed, $install)) {
					if ($package_details['operator'] == '==' && $package_details['version'] != $install[1]) {
						log::add(__CLASS__, 'debug', "Package {$package_details['name']} version is {$install[1]} but version {$package_details['version']} is required");
						return false;
					} elseif (version_compare($package_details['version'], $install[1], '>')) {
						log::add(__CLASS__, 'debug', "Package {$package_details['name']} version is {$install[1]} but version at least {$package_details['version']} is required");
						return false;
					}
				} else {
					log::add(__CLASS__, 'debug', "Package {$package_details['name']} seems not installed");
					return false;
				}
			}
		}
		return true;
	}

	protected static function logDebug(string $message, string $logicalId = '') {
		log::add(__CLASS__, 'debug', $message, $logicalId);
	}

	protected static function logInfo(string $message, string $logicalId = '') {
		log::add(__CLASS__, 'info', $message, $logicalId);
	}

	protected static function logWarning(string $message, string $logicalId = '') {
		log::add(__CLASS__, 'warning', $message, $logicalId);
	}

	protected static function logError(string $message, string $logicalId = '') {
		log::add(__CLASS__, 'error', $message, $logicalId);
	}
}
