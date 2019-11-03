<?php
namespace TJM\TBin\Service;
use Exception;

class Shell{
	protected $hosts = [];
	public function addHost($alias, $value){
		$this->hosts[$alias] = $value;
		return $this;
	}
	public function getHost($alias){
		return $this->hosts[$alias];
	}
	public function hasHost($alias){
		return isset($this->hosts[$alias]);
	}
	public function run($runCommands = null, $host = 'localhost', $opts = Array()){
		if($this->hasHost($host)){
			$host = $this->getHost($host);
		}
		$capture = isset($opts['capture']) ? $opts['capture'] : null;
		if(is_array($runCommands)){
			$runCommands = $this->convertCommandsArrayToString($runCommands);
		}
		if(isset($opts['path']) && $opts['path']){
			$runCommands = "cd " . escapeshellarg($opts['path']) . " && {$runCommands}";
		}
		$shellOptions = isset($opts['shellOpts']) ? $opts['shellOpts'] : [];
		if($host === 'localhost'){
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
			$command = "ssh {$host}";
		}
		if($runCommands){
			$command .= ' ' . implode(' ', $shellOptions) . ' ' . escapeshellarg($runCommands);
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
	protected function convertCommandsArrayToString($commands){
		return implode(' && ', $commands);
	}
}
