<?php
namespace TJM\TBin\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TJM\Component\Console\Command\ContainerAwareCommand as Base;
use TJM\TBin\Service\Shell;

class StatusCommand extends Command{
	static public $defaultName = 'status';
	protected $shell;
	public function __construct(Shell $shell){
		$this->shell = $shell;
		parent::__construct();
	}
	protected function configure(){
		$this
			->setDescription('Get status of a machine or site.')
			->addArgument('where', InputArgument::OPTIONAL, 'SSH style host string of host to run command on.', 'localhost')
		;
	}
	protected function execute(InputInterface $input, OutputInterface $output){
		//-!! logic should go into a service, but what service?
		$where = $input->getArgument('where');
		$isLocalhost = ($where === 'localhost');
		$sshKnown = false;
		if(!$isLocalhost){
			$whereHost = $where;
			if($this->shell->hasWhereAlias($where)){
				$whereHost = $this->shell->getWhereAlias($where);
			}
			try{
				$sshKnown = (bool) $this->shell->run('ssh-keygen -F ' . $whereHost, 'localhost', [
					'capture'=> true
				]);
			}catch(\Exception $e){}
		}

		if(!$isLocalhost){
			$this->shell->run('ping -c 1 ' . $whereHost);
		}
		if($isLocalhost || $sshKnown){
			$this->shell->run('w -i', $where);
		}
	}
}
