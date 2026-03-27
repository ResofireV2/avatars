<?php

namespace Resofire\Avatars;

use Flarum\Api\Context;
use Flarum\Api\Resource\UserResource;
use Flarum\Api\Schema;
use Flarum\Extend;
use Flarum\User\Event\Registered;
use Flarum\User\User;
use Resofire\Avatars\Api\FlushAvatarsController;
use Resofire\Avatars\Api\PreviewController;
use Resofire\Avatars\Api\SaveStyleController;
use Resofire\Avatars\User\Avatar\ResofireAvatarDriver;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/less/forum.less'),

    new Extend\Locales(__DIR__ . '/locale'),

    // Register our avatar driver with Flarum's driver system.
    // Admins can select it via Settings > Basics > Avatar Driver.
    (new Extend\User())
        ->avatarDriver('resofire', ResofireAvatarDriver::class),

    // Expose rf_avatar_style on the user resource so the forum JS can read
    // the currently selected style and pre-select it in the picker.
    // Callback signatures verified against Flarum\Api\Resource\UserResource::fields().
    (new Extend\ApiResource(UserResource::class))
        ->fields(fn () => [
            Schema\Str::make('rfAvatarStyle')
                ->get(fn (User $user) => $user->rf_avatar_style)
                ->visible(fn (User $user, Context $context) => $context->getActor()->id === $user->id),
        ]),

    // Generate avatar immediately on registration.
    (new Extend\Event())
        ->listen(Registered::class, Listener\GenerateAvatarOnRegister::class),

    // API routes.
    (new Extend\Routes('api'))
        ->post('/resofire-avatars/style',  'resofire-avatars.style',   SaveStyleController::class)
        ->get('/resofire-avatars/preview', 'resofire-avatars.preview', PreviewController::class)
        ->post('/resofire-avatars/flush',  'resofire-avatars.flush',   FlushAvatarsController::class),
];
