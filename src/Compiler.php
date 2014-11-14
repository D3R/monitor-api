<?php

namespace D3R\Monitor;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * PHAR Compiler - Mainly nicked from composer!
 *
 * This class compiles the codebase into a self-contained phar file that is able to
 * react to both CLI and HTTP requests.
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 * @copyright 2014 D3R Ltd
 * @license   http://d3r.com/license D3R Software Licence
 * @package D3R
 */
class Compiler
{
    private $version;
    private $versionDate;
    private $start;

    /**
     * Compiles d3r-tools into a single phar file
     *
     * @throws \RuntimeException
     * @param  string            $pharFile The full path to the file to create
     */
    public function compile($pharFile = 'd3r-monitor-api.phar', $force = false, ConsoleOutputInterface $output = null)
    {
        if (is_null($output)) {
            $output = new ConsoleOutput();
        }

        $this->start = microtime(true);
        $output->writeLn('Generating phar file at ' . $pharFile);
        $output->writeLn('Starting compilation at ' . date('H:i:s'));
        // @TODO Remove var_dump
        if (true == ini_get('phar.readonly')) {
            $this->pharWarning($output);
            return;
        }

        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $process = new Process('git log --pretty="%H" -n1 HEAD', __DIR__);
        if ($process->run() != 0) {
            throw new \RuntimeException(
                'Can\'t run git log. You must ensure to run compile from d3r-tools git
                repository clone and that git binary is available.'
            );
        }
        $this->version = trim($process->getOutput());

        $process = new Process('git log -n1 --pretty=%ci HEAD', __DIR__);
        if ($process->run() != 0) {
            throw new \RuntimeException(
                'Can\'t run git log. You must ensure to run compile from d3r-tools git
                repository clone and that git binary is available.'
            );
        }
        $date = new \DateTime(trim($process->getOutput()));
        $date->setTimezone(new \DateTimeZone('UTC'));
        $this->versionDate = $date->format('Y-m-d H:i:s');

        $process = new Process('git describe --tags HEAD');
        if ($process->run() == 0) {
            $this->version = trim($process->getOutput());
        }

        $phar = new \Phar($pharFile, 0, 'd3r-monitor-api.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->notName('Compiler.php')
            ->in(__DIR__)
        ;

        $this->addFilesFromFinder($phar, $finder, 'Base source files', $output);

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->exclude('Tests')
            ->exclude('tests')
            ->exclude('symfony')
            // ->in(__DIR__.'/../../vendor/symfony/')
            // ->in(__DIR__.'/../../vendor/monolog/')
            // ->in(__DIR__.'/../../vendor/psr/')
            ->in(__DIR__.'/../vendor/entomb/')
            ->in(__DIR__.'/../vendor/slim/')
        ;

        $this->addFilesFromFinder($phar, $finder, 'Other library files', $output);

        $output->writeLn('Adding autoload framework');
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->in(__DIR__ . '/../vendor/composer')
            ;
        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../vendor/autoload.php'), $output);
        $this->addFilesFromFinder($phar, $finder, 'Composer', $output);

        $output->writeLn('Adding web bootstrap');
        $this->addWeb($phar, $output);

        // Stubs
        $output->writeLn('Adding phar stub');
        $phar->setStub($this->getStub($output));

        $phar->stopBuffering();

        // disabled for interoperability with systems without gzip ext
        // $phar->compressFiles(\Phar::GZ);

        // $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../LICENSE'), false);
        $end  = microtime(true);
        $secs = round($end - $this->start);
        $output->writeLn('Compilation finished at ' . date('H:i:s'));
        $output->writeLn('Compile time was ' . $secs . ' seconds');
        $output->writeLn('Compiled phar file is available at ' . $pharFile);
        unset($phar);
    }

    private function addFilesFromFinder(\Phar $phar, Finder $finder, $label, ConsoleOutputInterface $output)
    {
        $count = iterator_count($finder);
        $output->writeLn($label . ' : ' . $count . ' files found');
        // $bar = new ProgressBar($output, $count);
        // $bar->start();
        foreach ($finder as $file) {
            $this->addFile($phar, $file, $output);
            // $bar->advance();
        }
        // $bar->finish();
        $output->writeLn('');
    }

    private function addFile($phar, $file, ConsoleOutputInterface $output, $strip = true)
    {
        $path = strtr(str_replace(dirname(__DIR__).DIRECTORY_SEPARATOR, '', $file->getRealPath()), '\\', '/');
        $output->writeLn("Adding path $path");

        $content = file_get_contents($file);
        if ($strip) {
            $content = $this->stripWhitespace($content);
        } elseif ('LICENSE' === basename($file)) {
            $content = "\n".$content."\n";
        }

        if ($path === 'src/tools/Constants.php') {
            // $output->writeLn('Setting version string to ' . $this->version);
            $content = str_replace('@dev_version@', $this->version, $content);
            // $output->writeLn('Setting release date to ' . $this->versionDate);
            $content = str_replace('@release_date@', $this->versionDate, $content);
        }

        $phar->addFromString($path, $content);
    }

    private function addWeb($phar, ConsoleOutputInterface $output)
    {
        // $output->writeLn("Adding web/index.php");

        $content = file_get_contents(__DIR__.'/../web/index.php');
        $phar->addFromString('web/index.php', $content);
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     *
     * @param  string $source A PHP string
     * @return string The PHP string with the whitespace removed
     */
    private function stripWhitespace($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }

    private function getStub(ConsoleOutputInterface $output)
    {
        // $output->writeLn("Adding stub");
        $stub = <<<'EOF'
<?php
/**
 * D3R Monitor API Compiled archive
 *
 * @copyright 2014 D3R Ltd
 * @license   http://d3r.com/license D3R Software Licence
 */

Phar::mapPhar('d3r-monitor-api.phar');
require 'phar://d3r-monitor-api.phar/web/index.php';

__HALT_COMPILER();
EOF;
        return $stub;
    }

    /**
     * Output a warning message about phar.readonly
     *
     * @param ConsoleOutputInterface
     * @return void
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function pharWarning(ConsoleOutputInterface $output)
    {
        $output->writeLn('Aargh!!');
        $output->writeLn('It looks like your php configuration doesn\'t support writing to phar files');
        $output->writeLn('By default its disabled. You need to set the following directive in php.ini');
        $output->writeLn('');
        $output->writeLn('phar.readonly = 0');
        $output->writeLn('');
        $output->writeLn('Exactly where you put that depends on your machine but php.ini seems to be located at');
        $output->writeLn('');
        $output->writeLn(php_ini_loaded_file());
        $output->writeLn('');
    }
}
