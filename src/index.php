<?php

declare(strict_types=1);

namespace Commissions;

use Commissions\CalculatorContext\Api\CalculateCommissionsConsoleCommand;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require 'vendor/autoload.php';

define('APPROOT', realpath(__DIR__ . '/../'));

// init service container
$containerBuilder = new ContainerBuilder();

$loader = new YamlFileLoader($containerBuilder, new FileLocator(APPROOT . '/src/config/'));

$loader->load('services.yaml');

try {
    $consoleCommand =  $containerBuilder->get('calculate.commissions.console.command');
    $consoleCommand->run();
} catch (\Exception $e) {
    echo sprintf('Error while calculating commissions: %s', $e->getMessage());
}
