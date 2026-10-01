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
            (new Suite('ui_paying_with_stripe_web_elements_during_checkout'))
            ->withPaths('features')
            ->withContexts(
                'sylius.behat.context.hook.doctrine_orm',
                'tests.flux_se.sylius_stripe_plugin.behat.context.hook.stripe_client_with_expectations',
            )
            ->withContexts(
                'sylius.behat.context.transform.address',
                'sylius.behat.context.transform.customer',
                'sylius.behat.context.transform.lexical',
                'sylius.behat.context.transform.locale',
                'sylius.behat.context.transform.order',
                'sylius.behat.context.transform.payment',
                'sylius.behat.context.transform.product',
                'sylius.behat.context.transform.shared_storage',
                'sylius.behat.context.transform.shipping_method',
                'sylius.behat.context.transform.tax_category',
                'sylius.behat.context.transform.tax_rate',
                'sylius.behat.context.transform.zone',
            )
            ->withContexts(
                'sylius.behat.context.setup.channel',
                'sylius.behat.context.setup.currency',
                'sylius.behat.context.setup.geographical',
                'sylius.behat.context.setup.locale',
                'sylius.behat.context.setup.order',
                'sylius.behat.context.setup.payment',
                'sylius.behat.context.setup.product',
                'sylius.behat.context.setup.shipping',
                'sylius.behat.context.setup.shop_security',
                'sylius.behat.context.setup.taxation',
                'sylius.behat.context.setup.user',
            )
            ->withContexts(
                'tests.flux_se.sylius_stripe_plugin.behat.context.setup.stripe',
            )
            ->withContexts(
                'sylius.behat.context.ui.shop.cart',
                'sylius.behat.context.ui.shop.checkout',
                'sylius.behat.context.ui.shop.checkout.addressing',
                'sylius.behat.context.ui.shop.checkout.complete',
                'sylius.behat.context.ui.shop.checkout.order_details',
                'sylius.behat.context.ui.shop.checkout.payment',
                'sylius.behat.context.ui.shop.checkout.shipping',
                'sylius.behat.context.ui.shop.checkout.thank_you',
            )
            ->withContexts(
                'tests.flux_se.sylius_stripe_plugin.behat.context.ui.shop.stripe_web_elements',
            )
            ->withFilter(new TagFilter('@paying_with_stripe_web_elements_during_checkout&&@ui')),
        ),
    )
;
