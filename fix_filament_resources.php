<?php

// Script to fix Filament resource property types

$resourcePath = __DIR__ . '/app/Filament/Resources/';
$pagesPaths = [];

// Find all Resource files
$resourceFiles = glob($resourcePath . '*Resource.php');

// Find all Pages directories and their files
$pagesDirs = glob($resourcePath . '*/Pages/', GLOB_ONLYDIR);
foreach ($pagesDirs as $pagesDir) {
    $pageFiles = glob($pagesDir . '*.php');
    $pagesPaths = array_merge($pagesPaths, $pageFiles);
}

// Find all RelationManager files
$relationManagerFiles = [];
$relationManagerDirs = glob($resourcePath . '*/RelationManagers/', GLOB_ONLYDIR);
foreach ($relationManagerDirs as $rmDir) {
    $rmFiles = glob($rmDir . '*.php');
    $relationManagerFiles = array_merge($relationManagerFiles, $rmFiles);
}

// Also check for nested RelationManager files
$nestedRmDirs = glob($resourcePath . '*/Pages/RelationManagers/', GLOB_ONLYDIR);
foreach ($nestedRmDirs as $rmDir) {
    $rmFiles = glob($rmDir . '*.php');
    $relationManagerFiles = array_merge($relationManagerFiles, $rmFiles);
}

echo "Found " . count($resourceFiles) . " resource files, " . count($pagesPaths) . " page files, and " . count($relationManagerFiles) . " relation manager files to fix.\n";

// Function to fix a file
function fixFile($filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Fix Resource classes - properties should be nullable
    if (strpos($filePath, 'Resource.php') !== false && strpos($filePath, '/Pages/') === false) {
        // Fix $model property
        $content = preg_replace(
            '/\/\*\*\s*\n\s*\*\s*@var\s+string\s*\n\s*\*\/\s*\n\s*protected\s+static\s+\$model\s*=/',
            'protected static ?string $model =',
            $content
        );
        
        // Fix $navigationIcon property
        $content = preg_replace(
            '/\/\*\*\s*\n\s*\*\s*@var\s+string\s*\n\s*\*\/\s*\n\s*protected\s+static\s+\$navigationIcon\s*=/',
            'protected static ?string $navigationIcon =',
            $content
        );
        
        // Fix $navigationGroup property
        $content = preg_replace(
            '/\/\*\*\s*\n\s*\*\s*@var\s+string\s*\n\s*\*\/\s*\n\s*protected\s+static\s+\$navigationGroup\s*=/',
            'protected static ?string $navigationGroup =',
            $content
        );
        
        // Fix other common properties that might be nullable
        $content = preg_replace(
            '/\/\*\*\s*\n\s*\*\s*@var\s+string\s*\n\s*\*\/\s*\n\s*protected\s+static\s+\$navigationLabel\s*=/',
            'protected static ?string $navigationLabel =',
            $content
        );
    }
    
    // Fix Page classes - $resource should be string (not nullable)
    if (strpos($filePath, '/Pages/') !== false) {
        $content = preg_replace(
            '/\/\*\*\s*\n\s*\*\s*@var\s+string\s*\n\s*\*\/\s*\n\s*protected\s+static\s+\$resource\s*=/',
            'protected static string $resource =',
            $content
        );
    }
    
    // Fix RelationManager classes - $relationship should be string (not nullable)
    if (strpos($filePath, 'RelationManager.php') !== false) {
        $content = preg_replace(
            '/\/\*\*\s*\n\s*\*\s*@var\s+string\s*\n\s*\*\/\s*\n\s*protected\s+static\s+\$relationship\s*=/',
            'protected static string $relationship =',
            $content
        );
    }
    
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        echo "Fixed: " . basename($filePath) . "\n";
        return true;
    }
    
    return false;
}

$fixedCount = 0;

// Fix all resource files
foreach ($resourceFiles as $file) {
    if (fixFile($file)) {
        $fixedCount++;
    }
}

// Fix all page files
foreach ($pagesPaths as $file) {
    if (fixFile($file)) {
        $fixedCount++;
    }
}

// Fix all relation manager files
foreach ($relationManagerFiles as $file) {
    if (fixFile($file)) {
        $fixedCount++;
    }
}

echo "\nFixed $fixedCount files total.\n";
echo "Done!\n";
