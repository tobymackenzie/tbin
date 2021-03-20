<?php
namespace TJM\TBin\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TJM\ShellRunner\ShellRunner;

class LogsCommand extends Command{
	static public $defaultName = 'logs';
	protected $shell;
	public function __construct(ShellRunner $shell){
		$this->shell = $shell;
		parent::__construct();
	}
	protected function configure(){
		$this
			->setDescription('Read log files.')
			->addArgument('name', InputArgument::REQUIRED, 'Look for log files with name.')
			->addOption('contents', 'c', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Find in log contents.')
			->addOption('host', 'h', InputOption::VALUE_REQUIRED, 'SSH style host string of host to read log(s) on.')
			->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Directory to find log in.', '/var/log')
			->addOption('run', 'r', InputOption::VALUE_REQUIRED, 'Command to run on found log.', 'less')
		;
	}
	protected function execute(InputInterface $input, OutputInterface $output){
		//-!! logic should go into a service, but what service?  Shell? Files?
		$opts = [];
		$opts['path'] = $input->getOption('path');
		$opts['command'] = "find . -type f";
		//--name must work for find `-name` option, so we must move any dir to path
		$pathBits = explode('/', $input->getArgument('name'));
		$name = array_pop($pathBits);
		$opts['path'] .= '/' . implode('/', $pathBits);
		$opts['command'] .= " -name " . escapeshellarg($name) . ' -print0';
		$runOpts = explode(' ', trim($input->getOption('run')));
		$run = array_shift($runOpts);
		$contents = $input->getOption('contents');

		if($contents){
			switch($run){
				case 'less':
					$runOpts[] = "--pattern=" . escapeshellarg(implode('|', $contents));
				break;
				case 'vi':
				case 'vim':
					$runOpts[] = "-c " . escapeshellarg('set hlsearch') . ' +/' . escapeshellarg(implode('|', $contents));
				break;
			}
			foreach($contents as $content){
				$opts['command'] .= " | xargs -0 grep -l --null " . escapeshellarg($content);
			}
		}
		$opts['host'] = $input->getOption('host');
		$opts['command'] .= " | xargs -0 {$run} " . implode(' ', $runOpts);
		$opts['interactive'] = true;
		if($output->isVerbose()){
			$output->writeln('Running: ' . $opts['command']);
		}
		$result = $this->shell->run($opts);
		if(!$run){
			$output->writeln($result);
		}
	}
}
