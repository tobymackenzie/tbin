<?php
namespace TJM\TBin\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TJM\Component\Console\Command\ContainerAwareCommand as Base;

class StatusCommand extends Base{
	static public $defaultName = 'status';
	protected function configure(){
		$this
			->setDescription('Get status of a machine or site.')
			->addArgument('where', InputArgument::OPTIONAL, 'SSH style host string of host to run command on.', 'localhost')
		;
	}
	protected function execute(InputInterface $input, OutputInterface $output){
		//-!! logic should go into a service, but what service?
		$where = $input->getArgument('where');
		$shellService = $this->getContainer()->get('shell');
		$isLocalhost = ($where === 'localhost');
		$sshKnown = false;
		if(!$isLocalhost){
			$whereHost = $where;
			if($shellService->hasWhereAlias($where)){
				$whereHost = $shellService->getWhereAlias($where);
			}
			try{
				$sshKnown = (bool) $shellService->run('ssh-keygen -F ' . $whereHost, 'localhost', [
					'capture'=> true
				]);
			}catch(\Exception $e){}
		}

		if(!$isLocalhost){
			$shellService->run('ping -c 1 ' . $whereHost);
		}
		if($isLocalhost || $sshKnown){
			$shellService->run('w -i', $where);

		}
	}
}
