<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('invoices:generate-recurring')
    ->dailyAt('00:05')
    ->withoutOverlapping();