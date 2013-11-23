<?php

namespace Beryllium\CacheBundle\Command;

use Beryllium\CacheBundle\Cache;
use Beryllium\CacheBundle\Client\StatsInterface;
use Beryllium\CacheBundle\Statistics;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a command-line interface for viewing cache client stats
 *
 * @author Kevin Boyd <beryllium@beryllium.ca>
 * @license See LICENSE.md
 */
class CacheClientStatsCommand extends ContainerAwareCommand
{
    /**
     * Configure the CLI task
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('cacheclient:stats')
             ->setDescription('Display Cache Statistics')
             ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Show debugging information');
    }

    /**
     * Execute the CLI task
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Cache $cache */
        $cache = $this->getContainer()->get('be_cache');
        $client = $cache->getClient();

        if (!($client instanceof StatsInterface)) {
            $output->writeln('<info>Statistics for this caching client is not available</info>');
            return;
        }

        $statistics = $client->getStats();
        if (count($statistics) === 0) {
            $output->writeln('<info>No instances detected</info>');
            return;
        }

        foreach ($statistics as $instance => $stats) {
            $output->writeln('<info>Instance: ' . $instance . '</info>');
            $this->formatStatistics($stats)->render($output);
            $output->writeln('');
        }
    }

    /**
     * Format single instance of stats
     *
     * @param Statistics $stats
     * @return TableHelper
     */
    private function formatStatistics(Statistics $stats)
    {
        /** @var TableHelper $formatter */
        $formatter = $this->getHelperSet()->get('table');
        $result = array();
        foreach ($stats->getFormattedArray() as $key => $value) {
            $result[] = array($key . ':', $value);
        }

        $formatter
            ->setLayout(TableHelper::LAYOUT_BORDERLESS)
            ->setHorizontalBorderChar('-')
            ->setRows($result);

        return $formatter;
    }
}
