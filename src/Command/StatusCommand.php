<?php
namespace TJM\TBin\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TJM\Component\Console\Command\ContainerAwareCommand as Base;
use TJM\ShellRunner\ShellRunner;

class StatusCommand extends Command{
	static public $defaultName = 'status';
	protected $shell;
	public function __construct(ShellRunner $shell){
		$this->shell = $shell;
		parent::__construct();
	}
	protected function configure(){
		$this
			->setDescription('Get status of a machine or site.')
			->addArgument('host', InputArgument::OPTIONAL, 'SSH style host string of host to run command on.', 'localhost')
		;
	}
	protected function execute(InputInterface $input, OutputInterface $output){
		//-!! logic should go into a service, but what service?
		$host = $input->getArgument('host');
		$translatedHost = $host;
		if($this->shell->hasHost($host)){
			$translatedHost = $this->shell->getHost($host);
		}
		$isLocalhost = ($host === 'localhost');
		$sshKnown = false;
		if(!$isLocalhost){
			try{
				$sshKnown = (bool) $this->shell->run('ssh-keygen -F ' . $translatedHost);
			}catch(\Exception $e){}
		}

		if(!$isLocalhost){
			$output->writeln($this->shell->run('ping -c 1 ' . $translatedHost));
		}
		if($isLocalhost || $sshKnown){
			$output->writeln($this->shell->run([
				'command'=> 'w -i'
				,'host'=> $host
			]));
		}
	}
}
