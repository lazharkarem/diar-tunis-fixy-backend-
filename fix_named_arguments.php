<?php

/**
 * Script to fix named arguments in Filament files for PHP compatibility
 * Converts named arguments to positional arguments
 */

function fixNamedArguments($filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Common patterns to fix named arguments
    $patterns = [
        // Fix: toggleable(isToggledHiddenByDefault: true)
        '/->toggleable\(isToggledHiddenByDefault:\s*true\)/' => '->toggleable(true)',
        
        // Fix: toggleable(isToggledHiddenByDefault: false)
        '/->toggleable\(isToggledHiddenByDefault:\s*false\)/' => '->toggleable(false)',
        
        // Fix: unique(ignoreRecord: true)
        '/->unique\(ignoreRecord:\s*true\)/' => '->unique(ignoreRecord: true)',
        
        // Fix: afterOrEqual('field_name')
        '/->afterOrEqual\(\'([^\']+)\'\)/' => '->afterOrEqual(\'$1\')',
        
        // Fix: minValue(1)
        '/->minValue\((\d+)\)/' => '->minValue($1)',
        
        // Fix: maxLength with ignoreRecord
        '/->maxLength\((\d+)\)\s*->unique\(ignoreRecord:\s*true\)/' => '->maxLength($1)->unique()',
        
        // Fix specific patterns that might have issues
        '/ignoreRecord:\s*true/' => '',
    ];
    
    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    // Only write if content changed
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        echo "Fixed: $filePath\n";
        return true;
    }
    
    return false;
}

// Function to recursively find PHP files in Filament directory
function findFilamentFiles($dir) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    
    return $files;
}

// Main execution
$filamentDir = __DIR__ . '/app/Filament';

if (!is_dir($filamentDir)) {
    echo "Error: Filament directory not found at $filamentDir\n";
    exit(1);
}

echo "Scanning for Filament PHP files with named arguments...\n";
$files = findFilamentFiles($filamentDir);
echo "Found " . count($files) . " PHP files\n\n";

$fixedCount = 0;
foreach ($files as $file) {
    if (fixNamedArguments($file)) {
        $fixedCount++;
    }
}

echo "\nCompleted! Fixed $fixedCount files.\n";
