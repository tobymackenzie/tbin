<?php
namespace TJM\TBin\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TJM\ShellRunner\Location\Location;
use TJM\ShellRunner\ShellRunner;

class SshCommand extends Command{
	static public $defaultName = 'ssh';
	protected $shell;
	public function __construct(ShellRunner $shell){
		$this->shell = $shell;
		parent::__construct();
	}
	protected function configure(){
		$this
			->setDescription('SSH into a host.')
			->addArgument('hosts', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Host(s) string to SSH into.')
			->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Directory to change to.')
			->addOption('forward-agent', 'f', InputOption::VALUE_NONE, 'Forward local credentials for connecting to other servers from remote.')
		;
	}
	protected function execute(InputInterface $input, OutputInterface $output){
		$opts = [
			'interactive'=> true
		];
		if($input->getOption('forward-agent')){
			$opts['forwardAgent'] = true;
		}
		foreach($input->getArgument('hosts') as $host){
			$location = new Location([
				'host'=> $host,
				'protocol'=> $host === 'localhost' ? 'file' : 'ssh',
			]);
			if($input->getOption('path')){
				$location->setPath($input->getOption('path'));
			}
			$this->shell->run($opts, $location);
		}
	}
}
