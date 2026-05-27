<?php

require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/../app/models/TextHelper.php';
require_once __DIR__ . '/../app/models/Usuario.php';

$declaredBefore = get_declared_classes();

foreach (glob(__DIR__ . '/Unit/*Test.php') as $testFile) {
    require_once $testFile;
}

$declaredAfter = get_declared_classes();
$testClasses = [];
foreach (array_diff($declaredAfter, $declaredBefore) as $className) {
    if (substr($className, -4) === 'Test') {
        $testClasses[] = $className;
    }
}

$passed = 0;
$failed = 0;

foreach ($testClasses as $testClass) {
    if (!class_exists($testClass)) {
        echo "Test class {$testClass} not found.\n";
        $failed++;
        continue;
    }

    $testInstance = new $testClass();
    $reflection = new ReflectionClass($testClass);

    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if (strpos($method->name, 'test') !== 0) {
            continue;
        }

        $testName = $testClass . '::' . $method->name;

        try {
            $method->invoke($testInstance);
            echo "[PASS] {$testName}\n";
            $passed++;
        } catch (Throwable $e) {
            echo "[FAIL] {$testName} - " . $e->getMessage() . "\n";
            $failed++;
        }
    }
}

echo PHP_EOL;
echo "Tests passed: {$passed}\n";
echo "Tests failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
