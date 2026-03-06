<?php

namespace App\Services\Instasites;

use Illuminate\Support\Facades\File;

class FaviconGeneratorService
{
    /**
     * Generate a favicon based on domain name and save to the public folder
     */
    public function generate(string $publicPath, string $domain, ?string $backgroundColor = null, array $context = []): string
    {
        $siteName = trim((string)($context['siteName'] ?? ''));
        $themeName = trim((string)($context['theme'] ?? 'default'));
        $normalizedDomain = $this->normalizeDomain($domain);
        $seedInput = strtolower($normalizedDomain . '|' . $siteName . '|' . $themeName);
        $seed = abs((int) crc32($seedInput));

        $firstLetter = $this->resolveMonogram($siteName, $normalizedDomain);

        $primaryColor = $this->sanitizeHexColor((string)($context['primaryColor'] ?? ''));
        $accentColor = $this->sanitizeHexColor((string)($context['accentColor'] ?? ''));

        if (!$primaryColor) {
            $primaryColor = $this->getColorForDomain($normalizedDomain, 68, 46);
        }
        if (!$accentColor) {
            $accentColor = $this->shiftColorHue($primaryColor, 42);
        }

        $baseColor = $this->sanitizeHexColor((string)$backgroundColor)
            ?: $this->mixHexColors($primaryColor, $accentColor, 0.6);

        $size = 192;
        $image = imagecreatetruecolor($size, $size);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $this->drawGradientBackground($image, $size, $baseColor, $accentColor);
        $this->drawPatternOverlay($image, $size, $seed, $primaryColor, $accentColor);
        $this->drawMonogram($image, $size, $firstLetter);
        
        File::ensureDirectoryExists("{$publicPath}/assets");

        $faviconPath = "{$publicPath}/assets/favicon.png";
        imagepng($image, $faviconPath);
        imagedestroy($image);

        return 'assets/favicon.png';
    }

    private function normalizeDomain(string $domain): string
    {
        $value = strtolower(trim($domain));
        return (string)preg_replace('/^www\./', '', $value);
    }

    private function resolveMonogram(string $siteName, string $domain): string
    {
        $source = trim($siteName) !== '' ? $siteName : $domain;
        if (preg_match('/[A-Za-z0-9]/', $source, $match)) {
            return strtoupper($match[0]);
        }

        return 'S';
    }

    private function sanitizeHexColor(string $color): ?string
    {
        $value = trim($color);
        if ($value === '') {
            return null;
        }

        if (!str_starts_with($value, '#')) {
            $value = '#' . $value;
        }

        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value) !== 1) {
            return null;
        }

        if (strlen($value) === 4) {
            $r = $value[1];
            $g = $value[2];
            $b = $value[3];
            return "#{$r}{$r}{$g}{$g}{$b}{$b}";
        }

        return strtolower($value);
    }

    private function drawGradientBackground($image, int $size, string $startHex, string $endHex): void
    {
        [$sr, $sg, $sb] = $this->hexToRgb($startHex);
        [$er, $eg, $eb] = $this->hexToRgb($endHex);

        for ($y = 0; $y < $size; $y++) {
            $t = $y / max(1, $size - 1);
            $r = (int)round($sr + (($er - $sr) * $t));
            $g = (int)round($sg + (($eg - $sg) * $t));
            $b = (int)round($sb + (($eb - $sb) * $t));
            $lineColor = imagecolorallocate($image, $r, $g, $b);
            imageline($image, 0, $y, $size, $y, $lineColor);
        }
    }

    private function drawPatternOverlay($image, int $size, int $seed, string $primaryHex, string $accentHex): void
    {
        [$pr, $pg, $pb] = $this->hexToRgb($primaryHex);
        [$ar, $ag, $ab] = $this->hexToRgb($accentHex);
        $mode = $seed % 3;

        if ($mode === 0) {
            $outer = imagecolorallocatealpha($image, $ar, $ag, $ab, 108);
            $inner = imagecolorallocatealpha($image, $pr, $pg, $pb, 118);
            imagefilledellipse($image, (int)($size * 0.78), (int)($size * 0.22), (int)($size * 1.1), (int)($size * 1.1), $outer);
            imagefilledellipse($image, (int)($size * 0.12), (int)($size * 0.9), (int)($size * 0.92), (int)($size * 0.92), $inner);
            return;
        }

        if ($mode === 1) {
            $stripe = imagecolorallocatealpha($image, $ar, $ag, $ab, 108);
            $step = 22 + ($seed % 10);
            for ($x = -$size; $x < $size * 2; $x += $step) {
                imagefilledpolygon($image, [
                    $x, 0,
                    $x + 14, 0,
                    $x - 36, $size,
                    $x - 50, $size,
                ], 4, $stripe);
            }
            return;
        }

        $dot = imagecolorallocatealpha($image, $pr, $pg, $pb, 112);
        $spacing = 26 + ($seed % 8);
        for ($y = 12; $y < $size; $y += $spacing) {
            for ($x = 10; $x < $size; $x += $spacing) {
                imagefilledellipse($image, $x, $y, 8, 8, $dot);
            }
        }
    }

    private function drawMonogram($image, int $size, string $letter): void
    {
        $textColor = imagecolorallocate($image, 255, 255, 255);
        $shadowColor = imagecolorallocatealpha($image, 0, 0, 0, 72);

        $fontPath = $this->resolveFontPath();

        if (!$fontPath) {
            $fontSize = 5;
            $x = (int)(($size - imagefontwidth($fontSize) * strlen($letter)) / 2);
            $y = (int)(($size - imagefontheight($fontSize)) / 2);
            imagestring($image, $fontSize, $x + 1, $y + 1, $letter, $shadowColor);
            imagestring($image, $fontSize, $x, $y, $letter, $textColor);
            return;
        }

        $fontSize = 108;
        $bbox = imagettfbbox($fontSize, 0, $fontPath, $letter);
        $width = (int)abs($bbox[4] - $bbox[0]);
        $height = (int)abs($bbox[5] - $bbox[1]);
        $x = (int)(($size - $width) / 2);
        $y = (int)(($size + $height) / 2);

        imagettftext($image, $fontSize, 0, $x + 2, $y + 2, $shadowColor, $fontPath, $letter);
        imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontPath, $letter);
    }

    private function resolveFontPath(): ?string
    {
        $candidates = [
            base_path('resources/fonts/Inter-Bold.ttf'),
            base_path('resources/fonts/arial.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        ];

        foreach ($candidates as $fontPath) {
            if (is_file($fontPath)) {
                return $fontPath;
            }
        }

        return null;
    }
    
    /**
     * Generate a consistent color based on domain name
     */
    private function getColorForDomain(string $domain, int $saturation = 78, int $lightness = 50): string
    {
        // Generate a consistent hash-based color
        $hash = crc32($domain);
        $hue = abs($hash % 360);
        $saturation = max(35, min(90, $saturation));
        $lightness = max(30, min(70, $lightness));
        
        // HSL to Hex conversion
        return $this->hslToHex($hue, $saturation, $lightness);
    }

    private function shiftColorHue(string $hexColor, float $shift): string
    {
        [$r, $g, $b] = $this->hexToRgb($hexColor);
        [$h, $s, $l] = $this->rgbToHsl($r, $g, $b);
        $h = fmod(($h + $shift + 360.0), 360.0);

        return $this->hslToHex($h, $s, max(36, min(64, $l)));
    }

    private function mixHexColors(string $a, string $b, float $ratio = 0.5): string
    {
        $ratio = max(0, min(1, $ratio));
        [$ar, $ag, $ab] = $this->hexToRgb($a);
        [$br, $bg, $bb] = $this->hexToRgb($b);

        $r = (int)round(($ar * $ratio) + ($br * (1 - $ratio)));
        $g = (int)round(($ag * $ratio) + ($bg * (1 - $ratio)));
        $bOut = (int)round(($ab * $ratio) + ($bb * (1 - $ratio)));

        return sprintf('#%02x%02x%02x', $r, $g, $bOut);
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

    private function rgbToHsl(int $r, int $g, int $b): array
    {
        $r /= 255;
        $g /= 255;
        $b /= 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $delta = $max - $min;

        $h = 0.0;
        if ($delta !== 0.0) {
            if ($max === $r) {
                $h = 60 * fmod((($g - $b) / $delta), 6);
            } elseif ($max === $g) {
                $h = 60 * ((($b - $r) / $delta) + 2);
            } else {
                $h = 60 * ((($r - $g) / $delta) + 4);
            }
        }

        if ($h < 0) {
            $h += 360;
        }

        $l = ($max + $min) / 2;
        $s = $delta == 0 ? 0 : $delta / (1 - abs((2 * $l) - 1));

        return [$h, $s * 100, $l * 100];
    }
}
