<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symkit\RedirectBundle\Contract\RedirectEntityInterface;
use Symkit\RedirectBundle\Repository\RedirectRepository;
use Symkit\RoutingBundle\Entity\Route;

#[ORM\Entity(repositoryClass: RedirectRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_REDIRECT_FROM_TO', columns: ['url_from', 'url_to'])]
#[UniqueEntity(
    fields: ['urlFrom', 'urlTo'],
    message: 'redirect.unique_pair',
    ignoreNull: false,
)]
#[UniqueEntity(
    fields: ['urlFrom'],
    message: 'redirect.unique_source',
)]
class Redirect implements RedirectEntityInterface
{
    #[Assert\Callback(groups: ['create', 'edit'])]
    public function validateUrlsDifferent(ExecutionContextInterface $context): void
    {
        if (null !== $this->urlTo && $this->urlFrom === $this->urlTo) {
            $context->buildViolation('redirect.destination_different')
                ->setTranslationDomain('SymkitRedirectBundle')
                ->atPath('urlTo')
                ->addViolation()
            ;
        }
    }

    #[Assert\Callback(groups: ['create', 'edit'])]
    public function validateDestination(ExecutionContextInterface $context): void
    {
        if (empty($this->urlTo) && null === $this->route) {
            $context->buildViolation('redirect.destination_required')
                ->setTranslationDomain('SymkitRedirectBundle')
                ->atPath('urlTo')
                ->addViolation()
            ;
        }
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore property.unusedType (Doctrine assigns id on persist) */
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(groups: ['create', 'edit'])]
    #[Assert\Regex(pattern: '#^/#', message: 'redirect.url_slash', groups: ['create', 'edit'])]
    private ?string $urlFrom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Regex(pattern: '#^/#', message: 'redirect.url_slash', groups: ['create', 'edit'])]
    private ?string $urlTo = null;

    #[ORM\ManyToOne(targetEntity: Route::class, inversedBy: 'redirects')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Route $route = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrlFrom(): ?string
    {
        return $this->urlFrom;
    }

    public function setUrlFrom(string $urlFrom): static
    {
        $this->urlFrom = $urlFrom;

        return $this;
    }

    public function getUrlTo(): ?string
    {
        return $this->urlTo;
    }

    public function setUrlTo(?string $urlTo): static
    {
        $this->urlTo = $urlTo;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getRoute(): ?Route
    {
        return $this->route;
    }

    public function getRouteName(): ?string
    {
        return $this->route?->getName();
    }

    public function setRoute(?Route $route): static
    {
        $this->route = $route;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->urlFrom;
    }
}
