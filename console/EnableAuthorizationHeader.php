<?php

namespace mikp\sanctum\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class EnableAuthorizationHeader extends Command
{
    // adds the following to the root .htaccess file
    // ##
    // ## Authorization header
    // ##
    // RewriteCond %{HTTP:Authorization} ^(.*)
    // RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
    protected $apache_test_config_string = "## Authorization header";

    protected $apache_write_string = "    ##" . PHP_EOL .
        "    ## Authorization header" . PHP_EOL .
        "    ##" . PHP_EOL .
        "    # AUTO-GENERATED DO NOT MODIFY" . PHP_EOL .
        "    RewriteCond %{HTTP:Authorization} ^(.*)" . PHP_EOL .
        "    RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]" . PHP_EOL;

    /**
     * @var string The console command name.
     */
    protected $name = 'sanctum:authorization';

    /**
     * @var string The console command description.
     */
    protected $description = 'Adds Authorization header access to .htaccess';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        // get options
        $add = $this->option('add');
        $remove = $this->option('remove');
        $yes = $this->option('yes');

        // check warning
        if (!$yes) {
            if (!$this->confirm('This will modify the root .htaccess file, Do you wish to continue? [yes|no]')) {
                $this->info('Aborting command');
                return;
            }
        }

        // get the file name
        $fname = implode('/', [getcwd(), '.htaccess']);

        // check if exists
        if (!file_exists($fname)) {
            $this->error('.htaccess file missing!');
            return;
        }

        // is config already written
        $config_present = false;
        if (exec('grep "' . $this->apache_test_config_string . '" ' . $fname)) {
            $config_present = true;
        }

        // do add command
        if ($add) {
            if ($config_present) {
                $this->info('File was already modified');
                return;
            }

            // take a backup
            $this->backupFile($fname);

            // get all the lines in the file
            if ($lines = file($fname)) {
                // open the file for writing
                $fhandle = fopen($fname, "w") or die("couldn't open $fname");

                // read each line until the end of the if module
                $key = '</IfModule>';
                foreach ($lines as $line) {
                    if (strstr($line, $key)) { // look for $key in each line
                        // do the modification
                        fwrite($fhandle, $this->apache_write_string . "\n"); // insert data before line with key
                    }
                    fwrite($fhandle, $line); //place $line back in file
                }
            }

            $this->info($fname);
            $this->info(file_get_contents($fname));
            $this->info('Added Authorization header changes to .htacess');
            return;
        }

        // do remove command
        if ($remove) {
            if (!$config_present) {
                $this->info('Config was already removed');
                return;
            }

            // do the modification
            $this->restoreFile($fname);
            $this->info('Removed Authorization header changes from .htacess');
            return;
        }

        $this->error('Need to specify an action via options --add or --remove');
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['add', null, InputOption::VALUE_NONE, 'Add to .htaccess', null],
            ['remove', null, InputOption::VALUE_NONE, 'Remove from .htaccess', null],
            ['yes', 'y', InputOption::VALUE_NONE, 'Accept continue', null],
        ];
    }

    /**
     * copy a backup of a given file
     */
    protected function backupFile($fname)
    {
        $backup = $fname . ".bak";
        copy($fname, $backup) or exit("failed to copy backup of $fname");
    }

    /**
     * restore file backup to file
     */
    protected function restoreFile($fname)
    {
        $backup = $fname . ".bak";
        copy($backup, $fname) or exit("failed to restore backup of $fname");
    }
}
