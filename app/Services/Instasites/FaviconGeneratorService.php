<?php

namespace App\Services\Instasites;

use Illuminate\Support\Facades\File;

class FaviconGeneratorService
{
    /**
     * Generate a favicon based on domain name and save to the public folder
     */
    public function generate(string $publicPath, string $domain, ?string $backgroundColor = null): string
    {
        $firstLetter = strtoupper(substr(trim($domain, 'www.'), 0, 1));
        
        // Generate a consistent color based on domain if not provided
        if (!$backgroundColor) {
            $backgroundColor = $this->getColorForDomain($domain);
        }
        
        // Create favicon using GD Library
        $size = 192; // 192x192 for modern favicon
        $image = imagecreatetruecolor($size, $size);
        
        // Convert hex color to RGB
        [$r, $g, $b] = $this->hexToRgb($backgroundColor);
        $bgColor = imagecolorallocate($image, $r, $g, $b);
        $textColor = imagecolorallocate($image, 255, 255, 255); // White text
        
        // Fill background
        imagefilledrectangle($image, 0, 0, $size, $size, $bgColor);
        
        // Add rounded corners effect (optional, draw circle)
        // For now, we'll keep it simple with a filled rectangle
        
        // Add text (first letter)
        $fontPath = __DIR__ . '/../../resources/fonts/arial.ttf';
        if (!file_exists($fontPath)) {
            $fontPath = base_path('resources/fonts/arial.ttf');
        }
        
        // Fallback if TTF not available - use built-in font
        if (!file_exists($fontPath)) {
            // Use imagefilledflood or large built-in font
            $fontSize = 5; // Built-in font size
            $x = ($size - imagefontwidth($fontSize) * strlen($firstLetter)) / 2;
            $y = ($size - imagefontheight($fontSize)) / 2;
            imagestring($image, $fontSize, $x, $y, $firstLetter, $textColor);
        } else {
            // Use TrueType font for better appearance
            $fontSize = 100;
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $firstLetter);
            $x = ($size - ($bbox[2] - $bbox[0])) / 2;
            $y = ($size - ($bbox[1] - $bbox[7])) / 2;
            imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontPath, $firstLetter);
        }
        
        // Ensure directory exists
        File::ensureDirectoryExists("{$publicPath}/assets");
        
        // Save favicon in multiple formats
        $faviconPath = "{$publicPath}/assets/favicon.png";
        imagepng($image, $faviconPath);
        imagedestroy($image);
        
        return 'assets/favicon.png';
    }
    
    /**
     * Generate a consistent color based on domain name
     */
    private function getColorForDomain(string $domain): string
    {
        // Generate a consistent hash-based color
        $hash = crc32($domain);
        $hue = abs($hash % 360);
        $saturation = 70 + (abs($hash) % 20); // 70-90%
        $lightness = 45 + (abs($hash / 2) % 20); // 45-65%
        
        // HSL to Hex conversion
        return $this->hslToHex($hue, $saturation, $lightness);
    }
    
    /**
     * Convert HSL to Hex color
     */
    private function hslToHex(float $h, float $s, float $l): string
    {
        $h = $h / 360;
        $s = $s / 100;
        $l = $l / 100;
        
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h * 6, 2) - 1));
        $m = $l - $c / 2;
        
        if ($h < 1/6) { $r = $c; $g = $x; $b = 0; }
        elseif ($h < 2/6) { $r = $x; $g = $c; $b = 0; }
        elseif ($h < 3/6) { $r = 0; $g = $c; $b = $x; }
        elseif ($h < 4/6) { $r = 0; $g = $x; $b = $c; }
        elseif ($h < 5/6) { $r = $x; $g = 0; $b = $c; }
        else { $r = $c; $g = 0; $b = $x; }
        
        $r = (int)round(($r + $m) * 255);
        $g = (int)round(($g + $m) * 255);
        $b = (int)round(($b + $m) * 255);
        
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
    
    /**
     * Convert hex to RGB
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        $hex = (strlen($hex) === 3) ? implode('', array_map(fn($x) => $x.$x, str_split($hex))) : $hex;
        list($r, $g, $b) = array_map('hexdec', str_split($hex, 2));
        return [$r, $g, $b];
    }
}
