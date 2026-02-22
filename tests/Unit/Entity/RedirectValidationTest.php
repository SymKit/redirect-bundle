<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Stringable;
use Symfony\Component\Validator\Validation;
use Symkit\RedirectBundle\Entity\Redirect;

final class RedirectValidationTest extends TestCase
{
    private function getValidator(): \Symfony\Component\Validator\Validator\ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testValidateUrlsDifferentAddsViolationWhenUrlFromEqualsUrlTo(): void
    {
        $redirect = new Redirect();
        $redirect->setUrlFrom('/same');
        $redirect->setUrlTo('/same');

        $violations = $this->getValidator()->validate($redirect, groups: ['create']);

        self::assertGreaterThan(0, $violations->count());
        $messages = array_map(static fn ($v) => $v->getMessage(), iterator_to_array($violations));
        self::assertNotEmpty(array_filter($messages, static fn (string|Stringable $m) => str_contains((string) $m, 'destination_different') || 'redirect.destination_different' === (string) $m));
    }

    public function testValidateUrlsDifferentNoViolationWhenDifferent(): void
    {
        $redirect = new Redirect();
        $redirect->setUrlFrom('/from');
        $redirect->setUrlTo('/to');

        $violations = $this->getValidator()->validate($redirect, groups: ['create']);

        $callbackViolations = array_filter(iterator_to_array($violations), static fn ($v) => 'urlTo' === $v->getPropertyPath() && str_contains((string) $v->getMessage(), 'destination_different'));
        self::assertCount(0, $callbackViolations);
    }

    public function testValidateDestinationAddsViolationWhenNoUrlToAndNoRoute(): void
    {
        $redirect = new Redirect();
        $redirect->setUrlFrom('/from');
        $redirect->setUrlTo(null);
        $redirect->setRoute(null);

        $violations = $this->getValidator()->validate($redirect, groups: ['create']);

        $destinationViolations = array_filter(iterator_to_array($violations), static fn ($v) => 'urlTo' === $v->getPropertyPath());
        self::assertNotEmpty($destinationViolations);
    }

    public function testValidateDestinationNoViolationWhenUrlToSet(): void
    {
        $redirect = new Redirect();
        $redirect->setUrlFrom('/from');
        $redirect->setUrlTo('/to');

        $violations = $this->getValidator()->validate($redirect, groups: ['create']);

        $destinationViolations = array_filter(iterator_to_array($violations), static fn ($v) => 'urlTo' === $v->getPropertyPath() && str_contains((string) $v->getMessage(), 'destination_required'));
        self::assertCount(0, $destinationViolations);
    }
}
