<?php
namespace TJM\TBin\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TJM\Component\Console\Command\ContainerAwareCommand as Base;

class SshCommand extends Base{
	protected function configure(){
		$this
			->setName('ssh')
			->setDescription('SSH into a host.')
			->addArgument('where', InputArgument::REQUIRED, 'Host string to SSH into.')
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
		$this->getContainer()->get('shell')->run(null ,$input->getArgument('where'), $opts);
	}
}
