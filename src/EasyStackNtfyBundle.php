<?php

declare(strict_types=1);

namespace Opillion\EasyStack\NtfyBundle;

use Opillion\EasyStack\NtfyBundle\DependencyInjection\EasyStackNtfyBundleExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EasyStackNtfyBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new EasyStackNtfyBundleExtension();
        }

        return $this->extension ?: null;
    }
}
