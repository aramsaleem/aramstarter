<?php

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Schedule;

// Removes audit entries older than ActivityLog::RETENTION_DAYS. Needs the scheduler: php artisan schedule:work
Schedule::command('model:prune', ['--model' => [ActivityLog::class]])->daily();
