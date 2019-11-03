<?php
namespace TJM\TBin\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TJM\TBin\Service\Shell;

class SshCommand extends Command{
	static public $defaultName = 'ssh';
	protected $shell;
	public function __construct(Shell $shell){
		$this->shell = $shell;
		parent::__construct();
	}
	protected function configure(){
		$this
			->setDescription('SSH into a host.')
			->addArgument('host', InputArgument::REQUIRED, 'Host string to SSH into.')
			->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Directory to change to.')
			->addOption('forward-agent', 'f', InputOption::VALUE_NONE, 'Forward local credentials for connecting to other servers from remote.')
		;
	}
	protected function execute(InputInterface $input, OutputInterface $output){
		$opts = [];
		if($input->getOption('forward-agent')){
			$opts['forwardAgent'] = true;
		}
		if($input->getOption('path')){
			$opts['path'] = $input->getOption('path');
		}
		$this->shell->run(null ,$input->getArgument('host'), $opts);
	}
}
