<?php

namespace App\Services;

use Monolog\Formatter\LogglyFormatter;

class LogglyFormatterExtend extends LogglyFormatter
{
    public function format(array $record)
    {
        if(isset($record['message']) && isset($record['level_name']) && $record['level'] == 400){
            unset($record['message']);
        }
        if (isset($record["datetime"]) && ($record["datetime"] instanceof \DateTime)) {
            $record["timestamp"] = $record["datetime"]->format("Y-m-d\TH:i:s.uO");
            // TODO 2.0 unset the 'datetime' parameter, retained for BC
        }

        return parent::format($record);
    }
}
