<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debugging ID Verification Button Issues\n";
echo "=======================================\n\n";

// Check if the route exists
try {
    $routes = app('router')->getRoutes();
    $idVerificationRoute = null;
    
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'id-verification')) {
            $idVerificationRoute = $route;
            break;
        }
    }
    
    if ($idVerificationRoute) {
        echo "✓ ID Verification route found: " . $idVerificationRoute->uri() . "\n";
    } else {
        echo "✗ ID Verification route not found\n";
    }
} catch (Exception $e) {
    echo "✗ Route check error: " . $e->getMessage() . "\n";
}

// Check if the view exists
try {
    $viewPath = resource_path('views/id-verification.blade.php');
    if (file_exists($viewPath)) {
        echo "✓ ID Verification view found\n";
        
        // Check for key elements in the view
        $viewContent = file_get_contents($viewPath);
        
        $elements = [
            'idvGateSelfie' => 'Selfie with ID button',
            'idvGateUpload' => 'Upload ID files button',
            'idvModeGate' => 'Mode gate container',
            'idvMainCameraBlock' => 'Main camera block',
            'idvUploadOnlyBlock' => 'Upload only block',
            'idvGuide' => 'Guide element',
            'idvVideo' => 'Video element',
            'idvCanvas' => 'Canvas element'
        ];
        
        echo "\nChecking HTML Elements:\n";
        foreach ($elements as $id => $description) {
            if (str_contains($viewContent, $id)) {
                echo "  ✓ $id: $description\n";
            } else {
                echo "  ✗ $id: $description - NOT FOUND\n";
            }
        }
        
        // Check for JavaScript functions
        $jsFunctions = [
            'setProofMode' => 'Set proof mode function',
            'applyLayoutForMode' => 'Apply layout for mode function',
            'startCamera' => 'Start camera function',
            'stopCamera' => 'Stop camera function',
            'hidePermission' => 'Hide permission function',
            'showPermission' => 'Show permission function'
        ];
        
        echo "\nChecking JavaScript Functions:\n";
        foreach ($jsFunctions as $function => $description) {
            if (str_contains($viewContent, $function)) {
                echo "  ✓ $function: $description\n";
            } else {
                echo "  ✗ $function: $description - NOT FOUND\n";
            }
        }
        
    } else {
        echo "✗ ID Verification view not found\n";
    }
} catch (Exception $e) {
    echo "✗ View check error: " . $e->getMessage() . "\n";
}

// Check for JavaScript errors by examining the view structure
echo "\nCommon Issues to Check:\n";
echo "1. JavaScript syntax errors in the view\n";
echo "2. Missing DOM elements when event listeners are attached\n";
echo "3. CSS classes preventing element visibility\n";
echo "4. Browser console errors (check with F12)\n";
echo "5. Camera permission issues\n";
echo "6. HTTPS requirement for camera access\n";

echo "\nDebugging Steps:\n";
echo "1. Open browser developer tools (F12)\n";
echo "2. Check Console tab for JavaScript errors\n";
echo "3. Click buttons and watch for error messages\n";
echo "4. Verify elements exist in Elements tab\n";
echo "5. Check Network tab for failed requests\n";

echo "\n=======================================\n";
echo "🔍 Debugging Complete - Check Browser Console for Specific Errors\n";
