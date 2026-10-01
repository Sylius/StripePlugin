<?php

declare(strict_types=1);

use Behat\Config\Config;

return (new Config())
    ->import([
        'suites/ui/admin.php',
        'suites/ui/stripe_checkout/shop.php',
        'suites/ui/stripe_checkout/admin.php',
        'suites/ui/stripe_web_elements/shop.php',
        'suites/ui/stripe_web_elements/admin.php',
        'suites/api/admin.php',
        'suites/api/stripe_checkout/shop.php',
        'suites/api/stripe_web_elements/shop.php',
    ])
;
