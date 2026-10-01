<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container): void {
    $env = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'dev';
    if (!str_starts_with((string) $env, 'test')) {
        return;
    }

    $repoRoot = \dirname(__DIR__, 3);

    // Sylius ships the Behat service definitions as PHP from 2.3 on and as XML before that.
    $behatConfig = $repoRoot . '/vendor/sylius/sylius/src/Sylius/Behat/Resources/config/services';
    $container->import($behatConfig . (file_exists($behatConfig . '.php') ? '.php' : '.xml'));
    $container->import($repoRoot . '/tests/Behat/Resources/services.php');
};
