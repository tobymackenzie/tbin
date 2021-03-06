<?php
namespace TJM\TBin\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TJM\ShellRunner\ShellRunner;

class FindFilesCommand extends Command{
	static public $defaultName = 'find-files';
	protected $shell;
	public function __construct(ShellRunner $shell){
		$this->shell = $shell;
		parent::__construct();
	}
	protected function configure(){
		$this
			->setDescription('Find files via the `find` command.  Optionally use `grep` command to find content.  Optionally do stuff with those files using run option.')
			->addArgument('host', InputArgument::REQUIRED, 'SSH style host string of host to run command on.')
			->addArgument('name', InputArgument::OPTIONAL, 'Look for files with name.')
			->addOption('contents', 'c', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Search file contents for string.  Multiple for separate strings (AND).')
			->addOption('exclude-paths', 'e', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Path(s) to exclude from search.')
			->addOption('find-options', 'o', InputOption::VALUE_REQUIRED, 'Options for the find command.')
			->addOption('forward-agent', 'f', InputOption::VALUE_NONE, 'Forward local credentials for connecting to other servers from remote.')
			->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Directory to search at.')
			->addOption('run', 'r', InputOption::VALUE_REQUIRED, 'Command to run on found files.')
		;
	}
	protected function execute(InputInterface $input, OutputInterface $output){
		//-!! logic should go into a service, but what service?  Shell? Files?
		$opts = [];
		if($input->getOption('forward-agent')){
			$opts['forwardAgent'] = true;
		}
		if($input->getOption('path')){
			$opts['path'] = $input->getOption('path');
		}
		$opts['command'] = "find .";
		if($input->getOption('find-options')){
			$opts['command'] .= " {$input->getOption('find-options')}";
		}
		$excludePaths = $input->getOption('exclude-paths');
		if($excludePaths){
			foreach($excludePaths as $path){
				$opts['command'] .= " -not -path " . escapeshellarg($path);
			}
		}
		$name = $input->getArgument('name');
		if($name){
			$opts['command'] .= " -name " . escapeshellarg($name);
		}
		$run = $input->getOption('run');
		$contents = $input->getOption('contents');
		if($contents){
			$opts['command'] .= ' -type f';
		}
		if($contents || $run){
			$opts['command'] .= ' -print0';
		}
		if($contents){
			$maxI = count($contents) - 1;
			foreach($contents as $i=> $content){
				$opts['command'] .= " | xargs -0 grep -l --null " . escapeshellarg($content);
			}
		}
		$opts['command'] .= ' | sort';
		if($contents || $run){
			$opts['command'] .= ' -z';
			if(!$run){
				$opts['command'] .= ' | tr ' . escapeshellarg('\0') . ' ' . escapeshellarg('\n');
			}
		}
		$opts['host'] = $input->getArgument('host');
		if($run){
			$opts['command'] .= " | xargs -0 {$run}";
			$opts['interactive'] = true;
		}
		if($output->isVerbose()){
			$output->writeln('Running: ' . $opts['command']);
		}
		$result = $this->shell->run($opts);
		if(!$run){
			$output->writeln($result);
		}
	}
}
