<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\OpenMarketplace\Api\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use BitBag\OpenMarketplace\Api\Context\VendorContextInterface;
use BitBag\OpenMarketplace\Api\SectionResolver\ShopVendorApiSection;
use BitBag\OpenMarketplace\Entity\VendorInterface;
use BitBag\OpenMarketplace\Repository\VendorRepositoryInterface;
use Ramsey\Uuid\UuidInterface;
use Sylius\Bundle\ApiBundle\SectionResolver\ShopApiSection;
use Sylius\Bundle\CoreBundle\SectionResolver\SectionProviderInterface;

final class VendorAccountItemDataProvider implements RestrictedDataProviderInterface, ItemDataProviderInterface
{
    private VendorContextInterface $vendorContext;

    private VendorRepositoryInterface $vendorRepository;

    private SectionProviderInterface $sectionProvider;

    public function __construct(
        VendorContextInterface $vendorContext,
        VendorRepositoryInterface $vendorRepository,
        SectionProviderInterface $sectionProvider
    ) {
        $this->vendorContext = $vendorContext;
        $this->vendorRepository = $vendorRepository;
        $this->sectionProvider = $sectionProvider;
    }

    /**
     * @param UuidInterface $id
     */
    public function getItem(
        string $resourceClass,
        $id,
        string $operationName = null,
        array $context = []
    ) {
        $section = $this->sectionProvider->getSection();

        if ($section instanceof ShopVendorApiSection && $this->isRequestedByRightVendor($id)) {
            return $this->vendorRepository->findOneBy(['uuid' => $id]);
        }

        if ($section instanceof ShopApiSection) {
            return $this->vendorRepository->findOneBy(['uuid' => $id]);
        }

        return null;
    }

    public function supports(
        string $resourceClass,
        string $operationName = null,
        array $context = []
    ): bool {
        return is_a($resourceClass, VendorInterface::class, true);
    }

    public function isRequestedByRightVendor(UuidInterface $uuid): bool
    {
        $vendor = $this->vendorContext->getVendor();

        if (null === $vendor) {
            return false;
        }

        /** @var UuidInterface $userVendorUuid */
        $userVendorUuid = $vendor->getUuid();

        return $uuid->equals($userVendorUuid);
    }
}
