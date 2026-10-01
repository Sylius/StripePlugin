<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Filter\TagFilter;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))
        ->withSuite(
            (new Suite('ui_managing_stripe_web_elements_orders'))
            ->withPaths('features')
            ->withContexts(
                'sylius.behat.context.hook.doctrine_orm',
                'tests.flux_se.sylius_stripe_plugin.behat.context.hook.stripe_client_with_expectations',
            )
            ->withContexts(
                'sylius.behat.context.transform.address',
                'sylius.behat.context.transform.customer',
                'sylius.behat.context.transform.locale',
                'sylius.behat.context.transform.order',
                'sylius.behat.context.transform.payment',
                'sylius.behat.context.transform.product',
                'sylius.behat.context.transform.shared_storage',
                'sylius.behat.context.transform.shipping_method',
            )
            ->withContexts(
                'sylius.behat.context.setup.channel',
                'sylius.behat.context.setup.currency',
                'sylius.behat.context.setup.locale',
                'sylius.behat.context.setup.order',
                'sylius.behat.context.setup.payment',
                'sylius.behat.context.setup.product',
                'sylius.behat.context.setup.admin_security',
                'sylius.behat.context.setup.shipping',
                'sylius.behat.context.setup.user',
                'sylius.behat.context.setup.zone',
                'tests.flux_se.sylius_stripe_plugin.behat.context.setup.managing_orders.stripe_web_elements',
            )
            ->withContexts(
                'sylius.behat.context.ui.admin.managing_orders',
                'sylius.behat.context.ui.admin.notification',
                'tests.flux_se.sylius_stripe_plugin.behat.context.setup.stripe',
            )
            ->withFilter(new TagFilter('@managing_stripe_web_elements_orders&&@ui')),
        ),
    )
;
