<?php

namespace Beryllium\CacheBundle\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

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
        $debug = $input->getOption('debug');
        $client = $this->getContainer()->get('beryllium_cache.client');

        if (is_object($client) && method_exists($client, 'getStats')) {
            $text = $this->formatStats($client->getStats(), $debug);
        } else {
            $text = 'Active cache client does not have a stats function.';
        }

        $output->writeln($text);
    }

    /**
     * Format the raw array for the command line report
     *
     * @param array $stats An array of memcache::extendedstats
     * @param bool $debug (default: false) True causes a raw dump of the stats array to be provided
     * @return string ConsoleComponent-formatted output, suitable for ->writeln() usage
     */
    public function formatStats($stats, $debug = false)
    {
        if (!is_array($stats)) {
            return "No statistics returned.\n";
        }

        if (count($stats) == 0) {
            return "No statistics returned.\n";
        }

        $out = "Servers found: " . count($stats) . "\n\n";
        foreach ($stats as $host => $item) {
            if (!is_array($item) || count($item) == 0) {
                $out .= "  <error>" . $host . "</error>\n";

                continue;
            }

            $out .= "<info>Host:\t" . $host . "</info>\n";

            $out .= "\tUsage: " . $this->formatUsage($item['bytes'], $item['limit_maxbytes']) . "\n";
            $out .= "\tUptime: " . $this->formatUptime($item['uptime']) . "\n";
            $out .= "\tOpen Connections: " . $item['curr_connections'] . "\n";
            $out .= "\tHits: " . $item['get_hits'] . "\n";
            $out .= "\tMisses: " . $item['get_misses'] . "\n";
            if ($item['get_hits'] + $item['get_misses'] > 0) {
                $out .= "\tHelpfulness: " . round(
                    $item['get_hits'] / ($item['get_hits'] + $item['get_misses']) * 100,
                    2
                ) . "%\n";
            }

            if ($debug) {
                $out .= "\n";
                foreach ($item as $key => $value) {
                    $out .= "\t" . $key . ': ' . $value . "\n";
                }
            }
        }

        return $out;
    }

    /**
     * Format the usage stats
     *
     * @param integer $bytes Cache usage (in bytes)
     * @param integer $maxbytes Cache maximum size (in bytes)
     * @return string A short string with friendly formatting
     */
    public function formatUsage($bytes, $maxbytes)
    {
        if (!is_numeric($maxbytes) || $maxbytes < 1) {
            return '(undefined)';
        }

        $out = round($bytes / $maxbytes, 3) . "% (";
        $out .= round($bytes / 1024 / 1024, 2) . 'MB of ';
        $out .= round($maxbytes / 1024 / 1024, 2) . 'MB)';

        return $out;
    }

    /**
     * Formats the uptime to be friendlier
     *
     * @param integer $uptime Cache server uptime (in seconds)
     * @return string A short string with friendly formatting
     */
    public function formatUptime($uptime)
    {
        $days = floor($uptime / 24 / 60 / 60);
        $days_remainder = $uptime - ($days * 24 * 60 * 60);
        $hours = floor($days_remainder / 60 / 60);
        $hours_remainder = $days_remainder - ($hours * 60 * 60);
        $minutes = floor($hours_remainder / 60);
        $minutes_remainder = $hours_remainder - ($minutes * 60);
        $seconds = $minutes_remainder;
        $out = $uptime . ' seconds (';

        if ($days > 0) {
            $out .= $days . ' days, ';
        }

        if ($hours > 0) {
            $out .= $hours . ' hours, ';
        }


        if ($minutes > 0) {
            $out .= $minutes . ' minutes, ';
        }


        if ($seconds > 0) {
            $out .= $seconds . ' seconds';
        }

        return $out . ')';
    }
}
