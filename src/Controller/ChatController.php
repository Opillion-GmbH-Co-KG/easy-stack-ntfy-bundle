<?php

declare(strict_types=1);

namespace Opillion\EasyStack\NtfyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class ChatController extends AbstractController
{
    public function __construct(
        private readonly string $ntfyHost,
        private readonly ?string $ntfyToken,
        private readonly ?string $ntfyChatTopic,
        private readonly ?string $ntfyZipProtection
    ) {}

    public function index(): Response
    {
        return $this->render(
            '@EasyStackNtfyBundle/crud/chat.bot.html.twig',
            [
                'title' => 'NTFY Chat',
                'ntfyHost' => $this->ntfyHost ?? '',
                'ntfyToken' => $this->ntfyToken ?? '',
                'ntfyChatTopic' => $this->ntfyChatTopic ?? '',
                'ntfyZipProtection' => $this->ntfyZipProtection ?? '',
            ]
        );
    }
}
