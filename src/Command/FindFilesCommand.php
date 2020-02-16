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
		$trailingCharacter = ($run ? ';' : '+');
		$contents = $input->getOption('contents');
		if($contents){
			$grepOpts = '-l';
			$opts['command'] .= " -type f -exec grep {$grepOpts} " . escapeshellarg(array_shift($contents)) . " {} \\{$trailingCharacter}";
			foreach($contents as $content){
				$opts['command'] .= " | xargs grep {$grepOpts} " . escapeshellarg($content);
			}
		}
		$opts['host'] = $input->getArgument('host');
		if($run){
			$opts['command'] .= " | xargs {$run}";
			$opts['interactive'] = true;
			$output->writeln('Running: ' . $opts['command']);
			$this->shell->run($opts);
		}else{
			$output->writeln('Running: ' . $opts['command']);
			$output->writeln($this->shell->run($opts));
		}
	}
}
