<?php
namespace TJM\TBin\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TJM\ShellRunner\Location\Location;
use TJM\ShellRunner\ShellRunner;

class RunCommand extends Command{
	static public $defaultName = 'run';
	protected $shell;
	public function __construct(ShellRunner $shell){
		$this->shell = $shell;
		parent::__construct();
	}
	protected function configure(){
		$this
			->setDescription('Run command on remote server.')
			->addArgument('run', InputArgument::REQUIRED, 'Command(s) to run.')
			->addOption('forward-agent', 'f', InputOption::VALUE_NONE, 'Forward local credentials for connecting to other servers from remote.')
			->addOption('host', 'h', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'SSH style host string of host(s) to run command on.', ['localhost'])
			->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Directory to change to.')
		;
	}
	protected function execute(InputInterface $input, OutputInterface $output){
		$opts = [
			'command'=> $input->getArgument('run')
			,'interactive'=> true
		];
		if($input->getOption('forward-agent')){
			$opts['forwardAgent'] = true;
		}
		foreach($input->getOption('host') as $host){
			$location = new Location([
				'host'=> $host,
				'protocol'=> $host === 'localhost' ? 'file' : 'ssh',
			]);
			if($input->getOption('path')){
				$location->setPath($input->getOption('path'));
			}
			$this->shell->run($opts, $location);
		}
		return 0;
	}
}
