<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\Contract;

use DateTimeImmutable;

interface RedirectEntityInterface
{
    public function getId(): ?int;

    public function getUrlFrom(): ?string;

    public function getUrlTo(): ?string;

    /**
     * @return object|null The target route entity (e.g. Symkit\RoutingBundle\Entity\Route)
     */
    public function getRoute(): ?object;

    /**
     * Name of the linked route for URL generation, or null if no route or not applicable.
     */
    public function getRouteName(): ?string;

    public function getCreatedAt(): ?DateTimeImmutable;

    public function __toString(): string;
}
