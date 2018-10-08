<?php
namespace TJM\TBin\Command;
use Exception;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use TJM\Component\Console\Command\ContainerAwareCommand as Base;

class ShellCommand extends Base{
	static public $defaultName = 'shell';
	protected function configure(){
		$this
			->setDescription('Run `tbin` as a simple interactive shell, allowing easy running of multiple commands.')
		;
	}
	protected function execute(InputInterface $input, OutputInterface $output){
		$app = $this->getApplication();
		//--configure app for shell use
		$app->setAutoExit(false);
		$app->setCatchExceptions(true);
		//--provide instruction
		$output->writeln("Welcome to tbin interactive shell mode.  Choose from the following commands:\n");
		$app->find('list')->run(new ArrayInput([
			'command'=> 'list'
			,'--raw'=> true
		]), $output);
		$output->writeln("\nType `exit` or press `ctl-c` to exit the program.");

		//--provide prompt, run commands until exit is entered
		$questionHelper = $this->getHelper('question');
		$question = new Question('tbin > ', false);
		//---start with all possible commands in autocomplete list
		$autocompleteList = array_keys($app->all());
		array_push($autocompleteList, 'exit');
		array_unshift($autocompleteList, '');
		while(true){
			$question->setAutocompleterValues($autocompleteList);
			$command = $questionHelper->ask($input, $output, $question);
			if($command === 'exit'){
				break;
			}elseif($command){
				//---add last command to autocomplete list
				$autocompleteList[] = $command;
				//---build input object from command
				$argInput = explode(' ', $command);
				array_unshift($argInput, 'tbin');
				$argInput = new ArgvInput($argInput);
				//---run command
				$app->run($argInput, $output);
			}
		}
	}
}
