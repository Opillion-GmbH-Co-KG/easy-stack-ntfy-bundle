<?php

declare(strict_types=1);

namespace Opillion\EasyStack\NtfyBundle\System\EasyAdmin;

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Opillion\EasyStack\NtfyBundle\System\AppDependencyMap;

final class AdminMenuProvider
{
    public function getMenuItems(string $extensionPoint): iterable
    {
        if ($extensionPoint !== 'root.after_library') {
            return;
        }

        if (!AppDependencyMap::menuRequirementsAvailable()) {
            return;
        }

        yield MenuItem::section('Ntfy', 'fas fa-robot')
            ->setPermission('ROLE_USER');

        yield MenuItem::linkToRoute('Chat', 'fas fa-comments', 'easy_stack_ntfy_chat')
            ->setPermission('ROLE_USER');
    }
}
