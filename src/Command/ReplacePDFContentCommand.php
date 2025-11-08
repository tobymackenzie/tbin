<?php
namespace TJM\TBin\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TJM\ShellRunner\ShellRunner;

class ReplacePDFContentCommand extends Command{
	static public $defaultName = 'replace-pdf-content';
	protected $shell;
	public function __construct(ShellRunner $shell){
		$this->shell = $shell;
		parent::__construct();
	}
	protected function configure(){
		$this
			->setDescription('Find and replace text content in a PDF.  Note: This requires `PDFtk` command to be installed and on your path.')
			->addArgument('source-file', InputArgument::REQUIRED, 'File to find and replace text in.')
			->addArgument('find-string', InputArgument::REQUIRED, 'String to look for in file.')
			->addArgument('replacement-string', InputArgument::OPTIONAL, 'String to replace find-string with.')
			->addArgument('destination-file', InputArgument::OPTIONAL, 'Filename to write output to.  Defaults to overwriting source.')
		;
	}
	protected function execute(InputInterface $input, OutputInterface $output){
		$sourceFile = $input->getArgument('source-file');
		$destFile = $input->getArgument('destination-file') ?: $sourceFile;
		$this->shell->run([
			'command'=> [
				'LANG=C'
				,"pdftk {$sourceFile} output __tmp12345.pdf uncompress"
				,"sed -e 's/{$input->getArgument('find-string')}/{$input->getArgument('replacement-string')}/g' <__tmp12345.pdf >__tmp22345.pdf"
				,"pdftk __tmp22345.pdf output {$destFile} compress"
				,'rm __tmp12345.pdf __tmp22345.pdf'
			]
		]);
		return 0;
	}
}
