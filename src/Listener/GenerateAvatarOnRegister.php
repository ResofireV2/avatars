<?php

namespace Resofire\Avatars\Listener;

use Flarum\User\Event\Registered;
use Resofire\Avatars\Generator\AvatarGenerator;

class GenerateAvatarOnRegister
{
    protected AvatarGenerator $generator;

    public function __construct(AvatarGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function handle(Registered $event): void
    {
        $user = $event->user;

        // Skip if user already has an avatar (e.g. from SSO).
        if (!empty($user->getRawOriginal('avatar_url'))) {
            return;
        }

        try {
            $this->generator->generateForUser($user);
        } catch (\Throwable $e) {
            // Don't block registration. Lazy fallback handles it on first load.
        }
    }
}
