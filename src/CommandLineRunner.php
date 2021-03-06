<?php

namespace Scan;

use Scan\Phases\IndexingPhase;
use Scan\Phases\AnalyzingPhase;

class CommandLineRunner
{

	function usage() {
		echo "
Usage: php -d memory_limit=500M Scan.php [-a] [-i] [-n #] [-o output_file_name] [-p #/#] config_file

where: -p #/#                 = Define the number of partitions and the current partition.
                                Use for multiple hosts. Example: -p 1/4

       -n #                   = number of child process to run.
                                Use for multiple processes on a single host.

       -a                     = run the \"analyze\" operation

       -i                     = run the \"index\" operation.
                                Defaults to yes if using in memory index.

       -s                     = prefer sqlite index

       -m                     = prefer in memory index (only available when -n=1 and -p=1/1)

       -o output_file_name    = Output results in junit format to the specified filename

       -v                     = Increase verbosity level.  Can be used once or twice.

       -h  or --help          = Ignore all other options and show this page.

";
	}

	function run(array $argv) {

		set_time_limit(0);
		date_default_timezone_set("UTC");

		try {
			$config=new Config($argv);
		}
		catch(InvalidConfigException $exception) {
			$this->usage();
			exit(1);
		}

		if($config->shouldIndex()) {
			$config->outputExtraVerbose("Indexing\n");
			$indexer=new IndexingPhase();
			$indexer->run($config);
			$config->outputExtraVerbose("\nDone\n\n");
			exit(0);
		}

		if($config->shouldAnalyze()) {
			$analyzer = new AnalyzingPhase();
			$config->outputExtraVerbose("Analyzing\n");

			if(!$config->hasFileList()) {
				$exitCode = $analyzer->run($config);
			} else {
				$list = $config->getFileList();
				$exitCode = $analyzer->phase2($config, $list);
			}
			$config->outputExtraVerbose("\nDone\n\n");
			exit($exitCode);
		}

	}
}