<?php

declare(strict_types=1);

namespace Tests\FluxSE\SyliusStripePlugin\Unit\Form\Type;

use FluxSE\SyliusStripePlugin\Form\Type\StripeCheckoutGatewayConfigurationType;
use FluxSE\SyliusStripePlugin\Form\Type\StripeGatewayConfigurationType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AllowMockObjectsWithoutExpectations]
final class StripeGatewayConfigurationTypeTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidator();
    }

    public function test_buildForm_registers_enable_express_checkout_field(): void
    {
        $registered = $this->collectRegisteredFields(new StripeGatewayConfigurationType());

        self::assertArrayHasKey('enable_express_checkout', $registered, 'enable_express_checkout field is not registered on the form.');
        self::assertSame(CheckboxType::class, $registered['enable_express_checkout']['type']);
        self::assertFalse($registered['enable_express_checkout']['options']['required']);
        self::assertSame(
            'flux_se_sylius_stripe_plugin.form.gateway_configuration.stripe.enable_express_checkout',
            $registered['enable_express_checkout']['options']['label'],
        );
    }

    public function test_stripe_checkout_form_registers_enable_adaptive_pricing_field(): void
    {
        $registered = $this->collectRegisteredFields(new StripeCheckoutGatewayConfigurationType());

        self::assertArrayHasKey('enable_adaptive_pricing', $registered, 'enable_adaptive_pricing field is not registered for stripe_checkout.');
        self::assertSame(CheckboxType::class, $registered['enable_adaptive_pricing']['type']);
        self::assertFalse($registered['enable_adaptive_pricing']['options']['required']);
        self::assertSame(
            'flux_se_sylius_stripe_plugin.form.gateway_configuration.stripe.enable_adaptive_pricing',
            $registered['enable_adaptive_pricing']['options']['label'],
        );
    }

    public function test_stripe_checkout_form_inherits_base_form_via_get_parent(): void
    {
        self::assertSame(
            StripeGatewayConfigurationType::class,
            (new StripeCheckoutGatewayConfigurationType())->getParent(),
        );
    }

    public function test_base_form_omits_enable_adaptive_pricing_field(): void
    {
        $registered = $this->collectRegisteredFields(new StripeGatewayConfigurationType());

        self::assertArrayNotHasKey('enable_adaptive_pricing', $registered, 'enable_adaptive_pricing field must not be registered on the base form (stripe_web_elements).');
    }

    /**
     * @return array<string, array{type: string, options: array<string, mixed>}>
     */
    private function collectRegisteredFields(\Symfony\Component\Form\FormTypeInterface $type): array
    {
        $registered = [];
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder
            ->method('add')
            ->willReturnCallback(function (string $name, string $type, array $options) use (&$registered, $builder): FormBuilderInterface {
                $registered[$name] = ['type' => $type, 'options' => $options];

                return $builder;
            });

        $type->buildForm($builder, []);

        return $registered;
    }

    #[DataProvider('acceptedSecretKeyProvider')]
    public function test_secret_key_field_accepts_restricted_keys(string $key): void
    {
        $violations = $this->validator->validate($key, $this->secretKeyConstraints());

        self::assertCount(0, $violations, sprintf('Expected "%s" to be accepted.', $key));
    }

    /** @return iterable<string, array{string}> */
    public static function acceptedSecretKeyProvider(): iterable
    {
        yield 'restricted key in test mode' => ['rk_test_abc123'];
        yield 'restricted key in live mode' => ['rk_live_abc123'];
    }

    #[DataProvider('rejectedSecretKeyProvider')]
    public function test_secret_key_field_rejects_invalid_keys(string $key): void
    {
        $violations = $this->validator->validate($key, $this->secretKeyConstraints());

        self::assertGreaterThan(0, $violations->count(), sprintf('Expected "%s" to be rejected.', $key));
    }

    /** @return iterable<string, array{string}> */
    public static function rejectedSecretKeyProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'standard secret key in test mode' => ['sk_test_abc123'];
        yield 'standard secret key in live mode' => ['sk_live_abc123'];
        yield 'publishable key pasted by mistake' => ['pk_test_abc123'];
        yield 'webhook signing secret pasted by mistake' => ['whsec_abc123'];
        yield 'random text' => ['random'];
        yield 'secret key without environment segment' => ['sk_abc123'];
        yield 'restricted key without environment segment' => ['rk_abc123'];
    }

    #[DataProvider('acceptedPublishableKeyProvider')]
    public function test_publishable_key_field_accepts_publishable_keys(string $key): void
    {
        $violations = $this->validator->validate($key, $this->publishableKeyConstraints());

        self::assertCount(0, $violations, sprintf('Expected "%s" to be accepted.', $key));
    }

    /** @return iterable<string, array{string}> */
    public static function acceptedPublishableKeyProvider(): iterable
    {
        yield 'publishable key in test mode' => ['pk_test_abc123'];
        yield 'publishable key in live mode' => ['pk_live_abc123'];
    }

    #[DataProvider('rejectedPublishableKeyProvider')]
    public function test_publishable_key_field_rejects_invalid_keys(string $key): void
    {
        $violations = $this->validator->validate($key, $this->publishableKeyConstraints());

        self::assertGreaterThan(0, $violations->count(), sprintf('Expected "%s" to be rejected.', $key));
    }

    /** @return iterable<string, array{string}> */
    public static function rejectedPublishableKeyProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'secret key pasted by mistake' => ['sk_test_abc123'];
        yield 'restricted key pasted by mistake' => ['rk_test_abc123'];
        yield 'webhook signing secret pasted by mistake' => ['whsec_abc123'];
        yield 'random text' => ['random'];
        yield 'publishable key without environment segment' => ['pk_abc123'];
    }

    /** @return list<NotBlank|Regex> */
    private function secretKeyConstraints(): array
    {
        return [
            new NotBlank(),
            new Regex(pattern: StripeGatewayConfigurationType::SECRET_KEY_PATTERN),
        ];
    }

    /** @return list<NotBlank|Regex> */
    private function publishableKeyConstraints(): array
    {
        return [
            new NotBlank(),
            new Regex(pattern: StripeGatewayConfigurationType::PUBLISHABLE_KEY_PATTERN),
        ];
    }
}
