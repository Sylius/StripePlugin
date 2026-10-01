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
            (new Suite('api_managing_payment_methods', ['javascript' => false]))
            ->withPaths('features')
            ->withContexts(
                'sylius.behat.context.hook.doctrine_orm',
            )
            ->withContexts(
                'sylius.behat.context.setup.admin_api_security',
                'sylius.behat.context.setup.channel',
                'sylius.behat.context.setup.currency',
                'sylius.behat.context.setup.locale',
                'sylius.behat.context.setup.order',
                'sylius.behat.context.setup.payment',
                'sylius.behat.context.setup.product',
                'sylius.behat.context.setup.shipping',
                'sylius.behat.context.setup.user',
                'sylius.behat.context.setup.zone',
            )
            ->withContexts(
                'sylius.behat.context.transform.address',
                'sylius.behat.context.transform.channel',
                'sylius.behat.context.transform.customer',
                'sylius.behat.context.transform.locale',
                'sylius.behat.context.transform.payment',
                'sylius.behat.context.transform.product',
                'sylius.behat.context.transform.shipping_method',
                'sylius.behat.context.transform.shared_storage',
            )
            ->withContexts(
                'sylius.behat.context.api.admin.save',
                'sylius.behat.context.api.admin.response',
            )
            ->withContexts(
                'sylius.behat.context.api.admin.managing_payment_methods',
                'sylius.behat.context.api.admin.translation',
                'tests.flux_se.sylius_stripe_plugin.behat.context.api.admin.managing_payment_methods',
            )
            ->withFilter(new TagFilter('@managing_payment_methods&&@api')),
        )
        ->withSuite(
            (new Suite('api_managing_orders', ['javascript' => false]))
            ->withPaths('features')
            ->withContexts(
                'sylius.behat.context.hook.doctrine_orm',
                'sylius.behat.context.hook.mailer',
                'sylius.behat.context.hook.calendar',
            )
            ->withContexts(
                'sylius.behat.context.setup.admin_api_security',
                'sylius.behat.context.setup.admin_user',
                'sylius.behat.context.setup.calendar',
                'sylius.behat.context.setup.cart',
                'sylius.behat.context.setup.channel',
                'sylius.behat.context.setup.currency',
                'sylius.behat.context.setup.customer',
                'sylius.behat.context.setup.geographical',
                'sylius.behat.context.setup.locale',
                'sylius.behat.context.setup.order',
                'sylius.behat.context.setup.payment',
                'sylius.behat.context.setup.product',
                'sylius.behat.context.setup.product_taxon',
                'sylius.behat.context.setup.promotion',
                'sylius.behat.context.setup.shipping',
                'sylius.behat.context.setup.shop_api_security',
                'sylius.behat.context.setup.taxation',
                'sylius.behat.context.setup.taxonomy',
                'sylius.behat.context.setup.zone',
            )
            ->withContexts(
                'sylius.behat.context.transform.address',
                'sylius.behat.context.transform.cart',
                'sylius.behat.context.transform.channel',
                'sylius.behat.context.transform.country',
                'sylius.behat.context.transform.currency',
                'sylius.behat.context.transform.customer',
                'sylius.behat.context.transform.lexical',
                'sylius.behat.context.transform.locale',
                'sylius.behat.context.transform.order',
                'sylius.behat.context.transform.payment',
                'sylius.behat.context.transform.product',
                'sylius.behat.context.transform.product_variant',
                'sylius.behat.context.transform.promotion',
                'sylius.behat.context.transform.shipping_method',
                'sylius.behat.context.transform.tax_category',
                'sylius.behat.context.transform.taxon',
                'sylius.behat.context.transform.zone',
                'tests.flux_se.sylius_stripe_plugin.behat.context.setup.managing_orders.stripe_web_elements',
            )
            ->withContexts(
                'sylius.behat.context.transform.shared_storage',
            )
            ->withContexts(
                'sylius.behat.context.api.admin.managing_orders',
                'sylius.behat.context.api.email',
            )
            ->withContexts(
                'sylius.behat.context.api.shop.checkout',
                'tests.flux_se.sylius_stripe_plugin.behat.context.setup.stripe',
            )
            ->withFilter(new TagFilter('@managing_orders&&@api')),
        ),
    )
;
