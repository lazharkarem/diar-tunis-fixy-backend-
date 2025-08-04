<?php

/**
 * Script to fix remaining syntax errors in Filament files
 */

function fixRemainingSyntaxErrors($filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Fix malformed match expressions
    $patterns = [
        // Fix: function (string $state): string { return match ($state); } {
        '/function \(string \$state\): string \{ return match \(\$state\); \} \{/' => 'function (string $state): string {',
        
        // Fix: function (string $state): string { return str_replace('App\Models\\'; }, '', $state)}
        '/function \(string \$state\): string \{ return str_replace\(\'App\\\\Models\\\\\\\\\'; \}, \'\', \$state\)\}/' => 'function (string $state): string { return str_replace(\'App\\\\Models\\\\\', \'\', $state); }',
        
        // Fix malformed str_replace patterns
        '/str_replace\(\'App\\\\Models\\\\\\\\\'; \}, \'\', \$state\)/' => 'str_replace(\'App\\\\Models\\\\\', \'\', $state)',
        
        // Fix match expressions to switch statements
        '/return match \(\$state\) \{([^}]+)\};/' => function($matches) {
            $cases = $matches[1];
            $switchCases = [];
            
            // Parse the cases
            if (preg_match_all('/\'([^\']+)\' => \'([^\']+)\'/', $cases, $caseMatches, PREG_SET_ORDER)) {
                foreach ($caseMatches as $case) {
                    $switchCases[] = "            case '{$case[1]}': return '{$case[2]}';";
                }
            }
            
            $switchStatement = "switch (\$state) {\n" . 
                             implode("\n", $switchCases) . "\n" .
                             "            default: return 'gray';\n" .
                             "        }";
                             
            return $switchStatement;
        }
    ];
    
    foreach ($patterns as $pattern => $replacement) {
        if (is_callable($replacement)) {
            $content = preg_replace_callback($pattern, $replacement, $content);
        } else {
            $content = preg_replace($pattern, $replacement, $content);
        }
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

echo "Scanning for Filament PHP files with remaining syntax errors...\n";
$files = findFilamentFiles($filamentDir);
echo "Found " . count($files) . " PHP files\n\n";

$fixedCount = 0;
foreach ($files as $file) {
    if (fixRemainingSyntaxErrors($file)) {
        $fixedCount++;
    }
}

echo "\nCompleted! Fixed $fixedCount files.\n";
