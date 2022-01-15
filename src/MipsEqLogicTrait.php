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
		return json_decode($content, true);
	}

	public function createCommandsFromConfigFile(string $filePath, string $commandsKey) {
		$commands = self::getCommandsFileContent($filePath);
		$this->createCommandsFromConfig($commands[$commandsKey]);
	}

	public function createCommandsFromConfig(array $commands) {
		$link_cmds = array();
		foreach ($commands as $cmdDef) {
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
			$cmd = $this->getCmd(null, $cmd_logicalId);
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
		$cron->setSchedule(cron::convertDateToCron(strtotime($_date)));
		$cron->save();
		$cron->run();
		log::add(__CLASS__, 'debug', "ExecuteAsync '{$_method}'");
	}
}
