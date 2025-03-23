<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ServeWithCacheClear extends Command
{
    protected $signature = 'serve:fresh';
    protected $description = 'Clear all caches and start the development server';

    public function handle()
    {
        $this->call('config:clear');
        $this->call('cache:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        
        $this->info('All caches cleared! Starting server...');
        
        $this->call('serve');
        
        return 0;
    }
}