<?php

namespace Secotrust\Bundle\RouteStatisticsBundle\Command;

use Secotrust\Bundle\RouteStatisticsBundle\Log\RegexReader;
use Secotrust\Bundle\RouteStatisticsBundle\Log\SymfonyReader;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper;

class StatisticsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('router:statistics');
        $this->setDescription('Displays route match statistics');
        $this->addArgument('filename', InputArgument::REQUIRED, 'Path to the log file');
        $this->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Format of the log file, one of apache, nginx, symfony or a custom regular expression', 'symfony');
        $this->addOption('prefix', null, InputOption::VALUE_OPTIONAL, 'URL log prefix, for example "/app_dev.php"', null);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');
        $format = $input->getOption('format');
        $prefix = $input->getOption('prefix');

        if (is_readable($filename) && $handle = fopen($filename, 'r')) {
            switch ($format) {
                case 'apache':
                case 'nginx':
                    $reader = new RegexReader($this->getContainer()->get('router'), $prefix, '#(?<=")(\w+) ([^"]+)(?= )#');
                    break;
                case 'symfony':
                    $reader = new SymfonyReader();
                    break;
                default:
                    $reader = new RegexReader($this->getContainer()->get('router'), $prefix, $format);
                    break;
            }

            while (!feof($handle)) {
                $line = fgets($handle, 4096);
                $reader->readLine($line);
            }

            fclose($handle);

            if ($stats = $reader->getStatistics()) {
                arsort($stats);

                $format = sprintf('<info>%% %uu</info>: %%s <comment>(#%%u)</comment>', strlen(reset($stats)));
                $routes = $this->getAllRoutes();

                foreach ($stats as $route => $count) {
                    $position = 1 + $routes[$route];
                    $output->writeln(sprintf($format, $count, $route, $position));
                }
            } else {
                $output->writeln('<error>Could not find any matching records, maybe you should specify a prefix or the format is wrong?</error>');
            }
        } else {
            throw new FileNotFoundException($filename);
        }
    }

    /**
     * @return array
     * @throws \RuntimeException
     */
    private function getAllRoutes()
    {
        $routeCollection = $this->getContainer()->get('router')->getRouteCollection();
        $dumper = new PhpMatcherDumper($routeCollection);
        $matcherCode = $dumper->dump(array());

        if (preg_match_all('#^\s*// (\S+)$#m', $matcherCode, $matches)) {
            $routes = array_flip($matches[1]);

            if (count($matches[1]) !== count($routes)) {
                throw new \RuntimeException('Duplicate route comments found');
            }

            return $routes;
        }

        throw new \RuntimeException('Could not determine the route order');
    }
}
