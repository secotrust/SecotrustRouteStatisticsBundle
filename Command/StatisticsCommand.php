<?php

/*
 * This file is part of the SecotrustRouteStatisticsBundle package.
 *
 * (c) Henrik Westphal <henrik.westphal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Secotrust\Bundle\RouteStatisticsBundle\Command;

use Secotrust\Bundle\RouteStatisticsBundle\Reader\LogReader;
use Secotrust\Bundle\RouteStatisticsBundle\Reader\RegexReader;
use Secotrust\Bundle\RouteStatisticsBundle\Reader\SymfonyReader;
use Secotrust\Bundle\RouteStatisticsBundle\Reader\ProfilerReader;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper;
use Symfony\Component\Routing\RouterInterface;

class StatisticsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    public function isEnabled()
    {
        if (!$this->getContainer()->has('router')) {
            return false;
        }
        $router = $this->getContainer()->get('router');
        if (!$router instanceof RouterInterface) {
            return false;
        }

        return parent::isEnabled();
    }

    protected function configure()
    {
        $this->setName('router:statistics');
        $this->setDescription('Displays route match statistics');
        $this->addArgument('filename', InputArgument::OPTIONAL, 'Path to the log file');
        $this->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Format of the log file, one of apache, nginx, symfony or a custom regular expression', 'profiler');
        $this->addOption('prefix', null, InputOption::VALUE_OPTIONAL, 'URL log prefix, for example "/app_dev.php"', null);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reader = $this->getReader($input);

        if ($reader instanceof LogReader) {
            /** @var \Secotrust\Bundle\RouteStatisticsBundle\Reader\LogReader $reader */
            $filename = $input->getArgument('filename');
            if (is_readable($filename) && $handle = fopen($filename, 'r')) {
                while (!feof($handle)) {
                    $line = fgets($handle, 4096);
                    $reader->readLine($line);
                }
                fclose($handle);
            } else {
                throw new FileNotFoundException($filename);
            }
        }

        if ($stats = $reader->getStatistics()) {
            arsort($stats);

            $format = sprintf('<info>%% %uu</info>: %%s <comment>(#%%u)</comment>', strlen(reset($stats)));
            $routes = $this->getAllRoutes();

            foreach ($stats as $route => $count) {
                $position = $routes[$route];
                $output->writeln(sprintf($format, $count, $route, $position));
            }
        } else {
            $output->writeln('<error>Could not find any matching records, maybe you should specify a prefix or the format is wrong?</error>');
        }
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return \Secotrust\Bundle\RouteStatisticsBundle\Reader\Reader
     */
    private function getReader(InputInterface $input)
    {
        $format = $input->getOption('format');
        $prefix = $input->getOption('prefix');

        switch ($format) {
            case 'apache':
            case 'nginx':
                return new RegexReader($this->getContainer()->get('router'), $prefix, '#(?<=")(\w+) ([^"]+)(?= )#');
            case 'profiler':
                return new ProfilerReader($this->getContainer()->get('profiler'));
            case 'symfony':
                return new SymfonyReader();
            default:
                return new RegexReader($this->getContainer()->get('router'), $prefix, $format);
        }
    }

    /**
     * @return array
     */
    private function getAllRoutes()
    {
        $routeCollection = $this->getContainer()->get('router')->getRouteCollection();
        $routes = array();

        $i = 0;
        foreach ($routeCollection->all() as $name => $route) {
            $routes[$name] = ++$i;
        }

        return $routes;
    }
}
