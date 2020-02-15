<?php
namespace TJM\TBin\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
			->addArgument('host', InputArgument::REQUIRED, 'SSH style host string of host to run command on.')
			->addArgument('run', InputArgument::REQUIRED, 'Command(s) to run.')
			->addOption('forward-agent', 'f', InputOption::VALUE_NONE, 'Forward local credentials for connecting to other servers from remote.')
			->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Directory to change to.')
		;
	}
	protected function execute(InputInterface $input, OutputInterface $output){
		$opts = [
			'command'=> $input->getArgument('run')
			,'host'=> $input->getArgument('host')
			,'interactive'=> true
		];
		if($input->getOption('path')){
			$opts['path'] = $input->getOption('path');
		}
		if($input->getOption('forward-agent')){
			$opts['forwardAgent'] = true;
		}
		$this->shell->run($opts);
	}
}
