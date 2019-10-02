<?php
namespace TJM\TBin\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TJM\Component\Console\Command\ContainerAwareCommand as Base;

class FindFilesCommand extends Base{
	static public $defaultName = 'find-files';
	protected function configure(){
		$this
			->setDescription('Find files via the `find` command.  Optionally use `grep` command to find content.  Optionally do stuff with those files using run option.')
			->addArgument('where', InputArgument::REQUIRED, 'SSH style host string of host to run command on.')
			->addArgument('name', InputArgument::OPTIONAL, 'Look for files with name.')
			->addOption('contents', 'c', InputOption::VALUE_REQUIRED, 'Search file contents for string.')
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
		$command = "find .";
		if($input->getOption('find-options')){
			$command .= " {$input->getOption('find-options')}";
		}
		$excludePaths = $input->getOption('exclude-paths');
		if($excludePaths){
			foreach($excludePaths as $path){
				$command .= " -not -path " . escapeshellarg($path);
			}
		}
		$name = $input->getArgument('name');
		if($name){
			$command .= " -name " . escapeshellarg($name);
		}
		$run = $input->getOption('run');
		$trailingCharacter = ($run ? ';' : '+');
		if($input->getOption('contents')){
			$grepOpts = ($run ? '-q' : '-l');
			$command .= " -type f -exec grep {$grepOpts} " . escapeshellarg($input->getOption('contents')) . " {} \\{$trailingCharacter}";
		}
		if($run){
			$command .= " -exec {$run} {} \\{$trailingCharacter}";
		}
		$output->writeln('Running: ' . $command);
		$this->getContainer()->get('shell')->run($command ,$input->getArgument('where'), $opts);
	}
}
