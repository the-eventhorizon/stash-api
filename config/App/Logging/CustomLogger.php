<?php

namespace App\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Illuminate\Support\Facades\Mail;

class CustomLogger extends StreamHandler
{
    public function __construct($level = Level::Info, $bubble = true)
    {
        parent::__construct(storage_path('logs/custom.log'), $level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        parent::write($record);

        $message = $record->message;
        $context = $record->context;

        Mail::raw("Log message: $message\nContext: " . print_r($context, true), function ($mail) {
            $mail->to('stefanguettler@outlook.com')
                ->subject('Custom log message');
        });
    }
}
