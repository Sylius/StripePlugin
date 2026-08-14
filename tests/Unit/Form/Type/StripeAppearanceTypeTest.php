<?php

declare(strict_types=1);

namespace Tests\FluxSE\SyliusStripePlugin\Unit\Form\Type;

use FluxSE\SyliusStripePlugin\Form\Type\StripeAppearanceType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class StripeAppearanceTypeTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidator();
    }

    #[DataProvider('acceptedColorProvider')]
    public function test_color_field_accepts_valid_hex(string $color): void
    {
        $violations = $this->validator->validate($color, new Regex(pattern: StripeAppearanceType::COLOR_PATTERN));

        self::assertCount(0, $violations, sprintf('Expected "%s" to be accepted.', $color));
    }

    /** @return iterable<string, array{string}> */
    public static function acceptedColorProvider(): iterable
    {
        yield 'short hex' => ['#f00'];
        yield 'long hex lowercase' => ['#ff0000'];
        yield 'long hex uppercase' => ['#FF0000'];
        yield 'mixed case' => ['#fF00aB'];
        yield '4 digit hex' => ['#f0f0'];
    }

    #[DataProvider('rejectedColorProvider')]
    public function test_color_field_rejects_invalid_hex(string $color): void
    {
        $violations = $this->validator->validate($color, new Regex(pattern: StripeAppearanceType::COLOR_PATTERN));

        self::assertGreaterThan(0, $violations->count(), sprintf('Expected "%s" to be rejected.', $color));
    }

    /** @return iterable<string, array{string}> */
    public static function rejectedColorProvider(): iterable
    {
        yield 'missing hash' => ['ff0000'];
        yield 'rgb notation' => ['rgb(255,0,0)'];
        yield 'named color' => ['red'];
        yield 'too short' => ['#ff'];
        yield 'too long' => ['#ff000000'];
        yield 'invalid chars' => ['#gg0000'];
    }

    #[DataProvider('acceptedBorderRadiusProvider')]
    public function test_border_radius_field_accepts_valid_values(string $value): void
    {
        $violations = $this->validator->validate($value, new Regex(pattern: StripeAppearanceType::BORDER_RADIUS_PATTERN));

        self::assertCount(0, $violations, sprintf('Expected "%s" to be accepted.', $value));
    }

    /** @return iterable<string, array{string}> */
    public static function acceptedBorderRadiusProvider(): iterable
    {
        yield 'pixels' => ['4px'];
        yield 'rem' => ['0rem'];
        yield 'decimal rem' => ['0rem'];
        yield 'em' => ['2em'];
        yield 'percent' => ['50%'];
        yield 'multi digit px' => ['16px'];
    }

    #[DataProvider('rejectedBorderRadiusProvider')]
    public function test_border_radius_field_rejects_invalid_values(string $value): void
    {
        $violations = $this->validator->validate($value, new Regex(pattern: StripeAppearanceType::BORDER_RADIUS_PATTERN));

        self::assertGreaterThan(0, $violations->count(), sprintf('Expected "%s" to be rejected.', $value));
    }

    /** @return iterable<string, array{string}> */
    public static function rejectedBorderRadiusProvider(): iterable
    {
        yield 'no unit' => ['4'];
        yield 'negative value' => ['-4px'];
        yield 'decimal with px' => ['1.5px'];
        yield 'unknown unit' => ['4vh'];
        yield 'bare text' => ['medium'];
    }
}
