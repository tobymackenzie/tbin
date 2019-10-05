<?php
namespace TJM\TBin\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TJM\Component\Console\Command\ContainerAwareCommand as Base;

class SelfUpdateCommand extends Command{
	static public $defaultName = 'self:update';
	protected $projectPath;
	public function __construct($projectPath){
		$this->projectPath = $projectPath;
		parent::__construct();
	}
	protected function configure(){
		$this
			->setDescription('Update tbin (`git pull` and `composer update`).')
		;
	}
	protected function execute(InputInterface $input, OutputInterface $output){
		//-! may want some config for options for these
		chdir($this->projectPath);
		if(shell_exec('which git')){
			passthru("git pull");
		}
		foreach([
			__DIR__ . '/composer.phar'
			,__DIR__ . '/bin/composer.phar'
			,trim(shell_exec('which composer'))
		] as $composer){
			if(is_executable($composer)){
				passthru("{$composer} update");
			}
		}
	}
}
