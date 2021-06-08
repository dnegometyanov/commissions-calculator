<?php

declare(strict_types=1);

namespace Commissions;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require 'vendor/autoload.php';

define('APPROOT', realpath(__DIR__ . '/../'));

// init service container
$containerBuilder = new ContainerBuilder();

$loader = new YamlFileLoader($containerBuilder, new FileLocator(APPROOT . '/src/config/'));

getenv('APP_ENV') === 'test'
    ? $loader->load('services_test.yaml')
    : $loader->load('services.yaml');

try {
    $consoleCommand =  $containerBuilder->get('calculate.commissions.console.command');
    $consoleCommand->run();
} catch (\Exception $e) {
    echo sprintf('Error while calculating commissions: %s', $e->getMessage());
}
