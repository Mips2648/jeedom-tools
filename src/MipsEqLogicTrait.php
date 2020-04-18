<?php

trait MipsEqLogicTrait {
	private static function getCommandsConfig($file) {
		$return = array();
		$path = dirname(__FILE__) . "/../config/{$file}";
		$content = file_get_contents($path);
		if (is_json($content)) {
			$return += json_decode($content, true);
		} else {
			log::add(__CLASS__, 'error', __('Fichier de configuration non trouvé:', __FILE__).$path);
		}

		return $return;
	}

	public function createCmdFromDef($commandsDef) {
		$link_cmds = array();
		foreach ($commandsDef as $cmdDef){
			$cmd = $this->getCmd(null, $cmdDef["logicalId"]);
			if (!is_object($cmd)) {
				log::add(__CLASS__, 'debug', 'create:'.$cmdDef["logicalId"].'/'.$cmdDef["name"]);
				$cmd = new cmd();
				$cmd->setLogicalId($cmdDef["logicalId"]);
				$cmd->setEqLogic_id($this->getId());
				$cmd->setName(__($cmdDef["name"], __FILE__));
				if(isset($cmdDef["isHistorized"])) {
					$cmd->setIsHistorized($cmdDef["isHistorized"]);
				}
				if(isset($cmdDef["isVisible"])) {
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
			if(isset($cmdDef["generic_type"])) {
				$cmd->setGeneric_type($cmdDef["generic_type"]);
			}
			if (isset($cmdDef['display'])) {
				foreach ($cmdDef['display'] as $key => $value) {
					if ($key=='title_placeholder' || $key=='message_placeholder') {
						$value = __($value, __FILE__);
					}
					$cmd->setDisplay($key, $value);
				}
			}
			if(isset($cmdDef["unite"])) {
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
				if ($cmdValue=='') {
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
}