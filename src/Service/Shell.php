<?php
namespace TJM\TBin\Service;
use Exception;

class Shell{
	protected $whereAliases = [];
	public function addWhereAlias($alias, $value){
		$this->whereAliases[$alias] = $value;
		return $this;
	}
	public function getWhereAlias($alias){
		return $this->whereAliases[$alias];
	}
	public function hasWhereAlias($alias){
		return isset($this->whereAliases[$alias]);
	}
	public function run($runCommands = null, $where = 'localhost', $opts = Array()){
		if($this->hasWhereAlias($where)){
			$where = $this->getWhereAlias($where);
		}
		$capture = isset($opts['capture']) ? $opts['capture'] : null;
		if(is_array($runCommands)){
			$runCommands = $this->convertCommandsArrayToString($runCommands);
		}
		if(isset($opts['cd']) && $opts['cd']){
			$runCommands = "cd {$opts['cd']} && {$runCommands}";
		}
		$shellOptions = isset($opts['shellOpts']) ? $opts['shellOpts'] : [];
		if($where === 'localhost'){
			if($runCommands && !in_array('-c', $shellOptions)){
				$shellOptions[] = '-c';
			}
			$command = '$SHELL';
		}else{
			if($runCommands && !in_array('-t', $shellOptions)){
				$shellOptions[] = '-t';
			}
			if(isset($opts['forwardAgent']) && $opts['forwardAgent'] && !in_array('-o ForwardAgent="yes"', $shellOptions)){
				$shellOptions[] = '-o ForwardAgent="yes"';
			}
			$command = "ssh {$where}";
		}
		if($runCommands){
			$command .= ' ' . implode(' ', $shellOptions) . ' "' . $this->escapeCommandDoubleQuotes($runCommands) . '"';
		}
		if($capture){
			exec($command, $result, $exitCode);
		}else{
			passthru($command, $exitCode);
		}
		if($exitCode){
			throw new Exception("Error {$exitCode} running command `{$command}`", $exitCode);
		}
		return isset($result) && $result ? implode("\n", $result) : $exitCode;
	}
	protected function escapeCommandDoubleQuotes($command){
		return str_replace('"', '\\"', $command);
	}
	protected function convertCommandsArrayToString($commands){
		return implode(' && ', $commands);
	}
}
