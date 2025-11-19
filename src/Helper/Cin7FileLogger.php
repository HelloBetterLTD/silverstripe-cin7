<?php

namespace SilverStripers\Cin7\Helper;

use SilverStripe\Core\Injector\Injectable;

class Cin7FileLogger
{
    use Injectable;

    private $logFile;

    public function __construct($logFile = null)
    {
        $defaultPath = BASE_PATH . '/cin7_api_calls.log';

        $this->logFile = $logFile ?: $defaultPath;

        if (!file_exists($this->logFile)) {
            file_put_contents($this->logFile, json_encode([]));
        }
    }

    public function logCall(string $method = null, string $endpoint = null)
    {
        $endpoint = explode('?', $endpoint)[0];
        $logs = $this->readLogs();

        $today = date('Y-m-d');
        $key = strtoupper($method) . ' ' . $endpoint;

        if (!isset($logs[$today])) {
            $logs[$today] = [];
        }

        if (!isset($logs[$today][$key])) {
            $logs[$today][$key] = [
                'count' => 0,
                'last_called' => null,
            ];
        }

        $logs[$today][$key]['count'] += 1;
        $logs[$today][$key]['last_called'] = date('Y-m-d H:i:s');
        $this->writeLogs($logs);
    }

    private function readLogs(): array
    {
        $content = file_get_contents($this->logFile);
        return $content ? json_decode($content, true) : [];
    }

    private function writeLogs(array $logs)
    {
        file_put_contents($this->logFile, json_encode($logs, JSON_PRETTY_PRINT));
    }

    public function getLogs(string $date = null): array
    {
        $logs = $this->readLogs();
        $date = $date ?: date('Y-m-d');
        return $logs[$date] ?? [];
    }

    public function resetToday()
    {
        $logs = $this->readLogs();
        $today = date('Y-m-d');
        unset($logs[$today]);
        $this->writeLogs($logs);
    }

}
