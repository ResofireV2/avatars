<?php

namespace Resofire\Avatars;

use Flarum\Api\Serializer\BasicUserSerializer;
use Flarum\Api\Serializer\CurrentUserSerializer;
use Flarum\Extend;
use Flarum\User\Event\Registered;
use Resofire\Avatars\Api\FlushAvatarsController;
use Resofire\Avatars\Api\PreviewController;
use Resofire\Avatars\Api\SaveStyleController;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/less/forum.less'),

    new Extend\Locales(__DIR__ . '/locale'),

    // Expose rf_avatar_style on the current user so the forum JS can read it.
    (new Extend\ApiSerializer(CurrentUserSerializer::class))
        ->attribute('rfAvatarStyle', function ($serializer, $user) {
            return $user->rf_avatar_style;
        }),

    // Lazy avatar generation for users with no avatar.
    (new Extend\ApiSerializer(BasicUserSerializer::class))
        ->attributes(Api\AddRfAvatar::class),

    // Generate avatar on registration.
    (new Extend\Event())
        ->listen(Registered::class, Listener\GenerateAvatarOnRegister::class),

    // API routes.
    (new Extend\Routes('api'))
        ->post('/resofire-avatars/style',   'resofire-avatars.style',   SaveStyleController::class)
        ->get('/resofire-avatars/preview',  'resofire-avatars.preview', PreviewController::class)
        ->post('/resofire-avatars/flush',   'resofire-avatars.flush',   FlushAvatarsController::class),
];
