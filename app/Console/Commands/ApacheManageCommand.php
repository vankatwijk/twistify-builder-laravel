<?php

namespace App\Console\Commands;

use App\Services\Instasites\ApacheVirtualHostService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ApacheManageCommand extends Command
{
    protected $signature = 'apache:manage 
                           {action : The action to perform (list|enable|disable|reload|test-config)}
                           {hostname? : The hostname to manage (required for enable/disable)}';

    protected $description = 'Manage Apache virtual hosts for Instasites';

    public function __construct(
        private ApacheVirtualHostService $apacheService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $action = $this->argument('action');
        $hostname = $this->argument('hostname');

        if (!$this->apacheService->isEnabled()) {
            $this->warn('Apache integration is disabled in configuration');
            return 1;
        }

        return match ($action) {
            'list' => $this->listVirtualHosts(),
            'enable' => $this->enableSite($hostname),
            'disable' => $this->disableSite($hostname),
            'reload' => $this->reloadApache(),
            'test-config' => $this->testApacheConfig(),
            default => $this->invalidAction($action),
        };
    }

    private function listVirtualHosts(): int
    {
        $this->info('Managed Apache Virtual Hosts:');
        $this->line('');

        $hosts = $this->apacheService->getManagedHosts();
        
        if (empty($hosts)) {
            $this->warn('No managed virtual hosts found');
            return 0;
        }

        $sitesAvailable = config('instasites.apache.sites_available');
        $sitesEnabled = config('instasites.apache.sites_enabled');

        foreach ($hosts as $host) {
            $configFile = $sitesAvailable . '/' . $host . '.conf';
            $enabledFile = $sitesEnabled . '/' . $host . '.conf';
            
            $status = File::exists($enabledFile) ? '<info>ENABLED</info>' : '<comment>DISABLED</comment>';
            $this->line("  {$host} - {$status}");
            
            if ($this->option('verbose')) {
                $this->line("    Config: {$configFile}");
                if (File::exists($enabledFile)) {
                    $this->line("    Enabled: {$enabledFile}");
                }
            }
        }

        return 0;
    }

    private function enableSite(?string $hostname): int
    {
        if (!$hostname) {
            $this->error('Hostname is required for enable action');
            return 1;
        }

        $this->info("Enabling Apache site: {$hostname}");
        
        $sitesRoot = config('instasites.sites_root');
        $documentRoot = $sitesRoot . '/' . $hostname . '/public';
        
        if (!is_dir($documentRoot)) {
            $this->warn("Document root does not exist: {$documentRoot}");
            $this->warn("You may need to build the site first");
        }

        $success = $this->apacheService->createVirtualHost($hostname, $documentRoot);
        
        if ($success) {
            $this->info("Successfully enabled site: {$hostname}");
            return 0;
        } else {
            $this->error("Failed to enable site: {$hostname}");
            return 1;
        }
    }

    private function disableSite(?string $hostname): int
    {
        if (!$hostname) {
            $this->error('Hostname is required for disable action');
            return 1;
        }

        $this->info("Disabling Apache site: {$hostname}");
        
        $success = $this->apacheService->removeVirtualHost($hostname);
        
        if ($success) {
            $this->info("Successfully disabled site: {$hostname}");
            return 0;
        } else {
            $this->error("Failed to disable site: {$hostname}");
            return 1;
        }
    }

    private function reloadApache(): int
    {
        $this->info('Reloading Apache configuration...');
        
        $reloadCommand = config('instasites.apache.reload_command');
        $output = [];
        $returnCode = 0;
        
        exec($reloadCommand . ' 2>&1', $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->info('Apache configuration reloaded successfully');
            return 0;
        } else {
            $this->error('Failed to reload Apache configuration');
            $this->line('Output:');
            foreach ($output as $line) {
                $this->line("  {$line}");
            }
            return 1;
        }
    }

    private function testApacheConfig(): int
    {
        $this->info('Testing Apache configuration...');
        
        $output = [];
        $returnCode = 0;
        
        exec('sudo apache2ctl configtest 2>&1', $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->info('Apache configuration test passed');
            foreach ($output as $line) {
                $this->line("  {$line}");
            }
            return 0;
        } else {
            $this->error('Apache configuration test failed');
            foreach ($output as $line) {
                $this->line("  {$line}");
            }
            return 1;
        }
    }

    private function invalidAction(string $action): int
    {
        $this->error("Invalid action: {$action}");
        $this->line('Available actions: list, enable, disable, reload, test-config');
        return 1;
    }
}
