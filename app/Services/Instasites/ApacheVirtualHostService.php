<?php

namespace App\Services\Instasites;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ApacheVirtualHostService
{
    private string $sitesAvailable;
    private string $sitesEnabled;
    private string $reloadCommand;
    private bool $enabled;

    public function __construct()
    {
        $config = config('instasites.apache');
        $this->sitesAvailable = $config['sites_available'];
        $this->sitesEnabled = $config['sites_enabled'];
        $this->reloadCommand = $config['reload_command'];
        $this->enabled = $config['enabled'];
    }

    /**
     * Create Apache virtual host configuration for a hostname
     */
    public function createVirtualHost(string $hostname, string $documentRoot): bool
    {
        if (!$this->enabled) {
            Log::info("Apache integration disabled, skipping virtual host creation for {$hostname}");
            return true;
        }

        try {
            $configContent = $this->generateVirtualHostConfig($hostname, $documentRoot);
            $configFile = $this->getConfigFilePath($hostname);

            // Ensure directories exist
            if (!is_dir($this->sitesAvailable)) {
                Log::warning("Apache sites-available directory does not exist: {$this->sitesAvailable}");
                return false;
            }

            // Write the configuration file
            File::put($configFile, $configContent);
            Log::info("Created Apache virtual host config: {$configFile}");

            // Enable the site
            return $this->enableSite($hostname);

        } catch (\Exception $e) {
            Log::error("Failed to create virtual host for {$hostname}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove Apache virtual host configuration for a hostname
     */
    public function removeVirtualHost(string $hostname): bool
    {
        if (!$this->enabled) {
            Log::info("Apache integration disabled, skipping virtual host removal for {$hostname}");
            return true;
        }

        try {
            // Disable the site first
            $this->disableSite($hostname);

            // Remove the configuration file
            $configFile = $this->getConfigFilePath($hostname);
            if (File::exists($configFile)) {
                File::delete($configFile);
                Log::info("Removed Apache virtual host config: {$configFile}");
            }

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to remove virtual host for {$hostname}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enable an Apache site
     */
    private function enableSite(string $hostname): bool
    {
        try {
            $configFile = $this->getConfigFilePath($hostname);
            $enabledFile = $this->getEnabledFilePath($hostname);

            // Create symbolic link if it doesn't exist
            if (!File::exists($enabledFile)) {
                if (!is_dir($this->sitesEnabled)) {
                    Log::warning("Apache sites-enabled directory does not exist: {$this->sitesEnabled}");
                    return false;
                }

                symlink($configFile, $enabledFile);
                Log::info("Enabled Apache site: {$hostname}");
            }

            // Reload Apache configuration
            return $this->reloadApache();

        } catch (\Exception $e) {
            Log::error("Failed to enable site {$hostname}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Disable an Apache site
     */
    private function disableSite(string $hostname): bool
    {
        try {
            $enabledFile = $this->getEnabledFilePath($hostname);

            if (File::exists($enabledFile)) {
                File::delete($enabledFile);
                Log::info("Disabled Apache site: {$hostname}");
                
                // Reload Apache configuration
                $this->reloadApache();
            }

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to disable site {$hostname}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reload Apache configuration
     */
    private function reloadApache(): bool
    {
        try {
            $output = [];
            $returnCode = 0;
            
            exec($this->reloadCommand . ' 2>&1', $output, $returnCode);
            
            if ($returnCode === 0) {
                Log::info("Apache configuration reloaded successfully");
                return true;
            } else {
                Log::error("Failed to reload Apache configuration. Output: " . implode("\n", $output));
                return false;
            }

        } catch (\Exception $e) {
            Log::error("Failed to reload Apache: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate Apache virtual host configuration content
     */
    private function generateVirtualHostConfig(string $hostname, string $documentRoot): string
    {
        $sanitizedHostname = $this->sanitizeHostname($hostname);
        
        return <<<APACHE
<VirtualHost *:80>
    ServerName {$hostname}
    DocumentRoot {$documentRoot}
    
    # Enable directory browsing and .htaccess
    <Directory {$documentRoot}>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Default file handling
        DirectoryIndex index.html index.htm
        
        # Handle missing files gracefully
        FallbackResource /index.html
    </Directory>
    
    # Logging
    ErrorLog \${APACHE_LOG_DIR}/{$sanitizedHostname}_error.log
    CustomLog \${APACHE_LOG_DIR}/{$sanitizedHostname}_access.log combined
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    
    # Cache static assets
    <LocationMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$">
        ExpiresActive On
        ExpiresDefault "access plus 1 month"
        Header append Cache-Control "public"
    </LocationMatch>
</VirtualHost>
APACHE;
    }

    /**
     * Get the path to the Apache configuration file
     */
    private function getConfigFilePath(string $hostname): string
    {
        $sanitizedHostname = $this->sanitizeHostname($hostname);
        return $this->sitesAvailable . '/' . $sanitizedHostname . '.conf';
    }

    /**
     * Get the path to the enabled site symlink
     */
    private function getEnabledFilePath(string $hostname): string
    {
        $sanitizedHostname = $this->sanitizeHostname($hostname);
        return $this->sitesEnabled . '/' . $sanitizedHostname . '.conf';
    }

    /**
     * Sanitize hostname for use in file names
     */
    private function sanitizeHostname(string $hostname): string
    {
        // Remove protocol if present
        $hostname = preg_replace('/^https?:\/\//', '', $hostname);
        
        // Replace dots and other special characters with underscores
        return preg_replace('/[^a-zA-Z0-9\-_]/', '_', $hostname);
    }

    /**
     * Check if Apache integration is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get list of managed virtual hosts
     */
    public function getManagedHosts(): array
    {
        if (!$this->enabled || !is_dir($this->sitesAvailable)) {
            return [];
        }

        $hosts = [];
        $files = File::glob($this->sitesAvailable . '/*.conf');
        
        foreach ($files as $file) {
            $basename = basename($file, '.conf');
            // Skip default Apache configs
            if (!in_array($basename, ['000-default', 'default-ssl'])) {
                $hosts[] = $basename;
            }
        }

        return $hosts;
    }
}
