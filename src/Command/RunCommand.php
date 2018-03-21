<?php
namespace TJM\TBin\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TJM\Component\Console\Command\ContainerAwareCommand as Base;

class RunCommand extends Base{
	protected function configure(){
		$this
			->setName('run')
			->setDescription('Run command on remote server.')
			->addArgument('where', InputArgument::REQUIRED, 'SSH style host string of host to run command on.')
			->addArgument('run', InputArgument::REQUIRED, 'Command(s) to run.')
			->addOption('cd', 'd', InputOption::VALUE_REQUIRED, 'Directory to change to.')
			->addOption('forward-agent', 'f', InputOption::VALUE_NONE, 'Forward local credentials for connecting to other servers from remote.')
		;
	}
	protected function execute(InputInterface $input, OutputInterface $output){
		$opts = [];
		if($input->getOption('cd')){
			$opts['cd'] = $input->getOption('cd');
		}
		if($input->getOption('forward-agent')){
			$opts['forwardAgent'] = true;
		}
		$this->getContainer()->get('shell')->run($input->getArgument('run') ,$input->getArgument('where'), $opts);
	}
}
