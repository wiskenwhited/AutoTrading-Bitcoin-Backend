<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

abstract class AbstractCommand extends Command
{
    protected function executeUnderLock(callable $fn)
    {
        $className = class_basename($this);
        $file = fopen(storage_path() . "/cron/flock/$className.flock", "r+");
        // Do an exclusive lock
        if (flock($file, LOCK_EX | LOCK_NB)) {
            // Execute command logic
            $fn();
            // Release the lock
            flock($file, LOCK_UN);
        } else {
            Log::warning("Cron tried to overlap execution of $className");
        }
        fclose($file);
    }
}