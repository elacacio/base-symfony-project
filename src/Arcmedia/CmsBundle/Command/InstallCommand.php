<?php

namespace Arcmedia\CmsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class InstallCommand
 * @package Arcmedia\CmsBundle\Command
 */
class InstallCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('arcmedia:install')
            ->setDescription('Install all dependencies to work in this project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Update composer version
        $this->banner($output, 'php composer.phar self-update');
        $selfUpdate = new Process('php composer.phar self-update', null, null, null, 900);
        $selfUpdate->run(
            function ($type, $buffer) use ($output) {
                $output->write($buffer);
            }
        );
        // Instala composer dependencies
        $this->banner($output, 'php composer.phar install');
        $composerInstall = new Process('php composer.phar install --ansi', null, null, null, 900);
        $composerInstall->run(
            function ($type, $buffer) use ($output) {
                $output->write($buffer);
            }
        );
        // Generate FOSJsRoutingBundle routes
        $this->banner($output, 'app/console fos:js-routing:dump --target web/bundles/js/fos_js_routes.js');
        $routingJs = $this->getApplication()->find('fos:js-routing:dump');
        $routingJsArgs = new ArrayInput(
            [
                'command' => 'fos:js-routing:dump',
                '--target' => 'web/bundles/js/fos_js_routes.js',
            ]
        );
        $routingJs->run($routingJsArgs, $output);
        // Dump assetic to generate grouped files
        $this->banner($output, 'app/console assetic:dump');
        $asseticDump = $this->getApplication()->find('assetic:dump');
        $asseticDumpArgs = new ArrayInput(['command' => 'assetic:dump']);
        $asseticDump->run($asseticDumpArgs, $output);
    }

    /**
     * Returns header message to cosonle.
     *
     * @param $output
     * @param $message
     */
    private function banner($output, $message)
    {
        $line = str_repeat('=', strlen($message));
        $output->writeln('');
        $output->writeln("<comment>$message</comment>");
        $output->writeln("<comment>$line</comment>");
    }
}
