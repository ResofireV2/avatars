<?php

namespace Resofire\Avatars\Api;

use Flarum\Api\Serializer\BasicUserSerializer;
use Flarum\User\User;
use Illuminate\Contracts\Filesystem\Factory;
use Resofire\Avatars\Generator\AvatarGenerator;

class AddRfAvatar
{
    protected AvatarGenerator $generator;
    protected $uploadDir;

    public function __construct(AvatarGenerator $generator, Factory $filesystemFactory)
    {
        $this->generator = $generator;
        $this->uploadDir = $filesystemFactory->disk('flarum-avatars');
    }

    public function __invoke(BasicUserSerializer $serializer, User $user, array $attributes): array
    {
        // Already has an avatar — nothing to do.
        if (!empty($attributes['avatarUrl'])) {
            return $attributes;
        }

        // Lazy generation for existing users with no avatar.
        try {
            $this->generator->generateForUser($user);
            // Build the full public URL from the stored filename.
            if ($user->avatar_url) {
                $attributes['avatarUrl'] = $this->uploadDir->url($user->avatar_url);
            }
        } catch (\Throwable $e) {
            // Fail silently — user just won't have an avatar this load.
        }

        return $attributes;
    }
}
