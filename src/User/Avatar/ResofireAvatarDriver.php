<?php

namespace Resofire\Avatars\User\Avatar;

use Flarum\User\Avatar\DriverInterface;
use Flarum\User\User;
use Illuminate\Contracts\Filesystem\Factory;
use Resofire\Avatars\Generator\AvatarGenerator;

class ResofireAvatarDriver implements DriverInterface
{
    public function __construct(
        protected AvatarGenerator $generator,
        protected Factory $filesystemFactory,
    ) {}

    /**
     * Return an avatar URL for the given user.
     *
     * If the user already has an uploaded avatar (avatar_url set but not an
     * rf_ file) this driver is not in use for that user — return null and let
     * Flarum's own URL resolution on the User model handle it.
     *
     * If the user has no avatar at all, generate one, persist the filename,
     * and return its public URL. This covers both new users whose registration
     * listener somehow missed and existing users migrating from Flarum 1.x.
     */
    public function avatarUrl(User $user): ?string
    {
        // generateForUser writes the filename to avatar_url on the model and
        // in the database. After it returns, avatar_url is set, so the User
        // model's own attribute accessor will resolve the URL correctly —
        // but since we're already inside that accessor's fallback path, we
        // resolve it ourselves directly from the disk to avoid recursion.
        try {
            $this->generator->generateForUser($user);

            $filename = $user->getRawOriginal('avatar_url');

            if ($filename) {
                return $this->filesystemFactory->disk('flarum-avatars')->url($filename);
            }
        } catch (\Throwable) {
            // Fail silently — the user will appear without an avatar rather
            // than breaking the page. Errors surface in the application log.
        }

        return null;
    }
}
