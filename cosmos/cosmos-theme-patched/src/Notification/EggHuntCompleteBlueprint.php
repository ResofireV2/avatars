<?php

namespace Resofire\CosmosTheme\Notification;

use Flarum\Notification\Blueprint\BlueprintInterface;
use Flarum\User\User;

/**
 * Notification blueprint fired when a user finds all 8 hidden Easter eggs.
 * Sent to every admin on the forum.
 */
class EggHuntCompleteBlueprint implements BlueprintInterface
{
    public function __construct(
        protected User $user
    ) {}

    public function getFromUser(): ?User
    {
        return $this->user;
    }

    public function getSubject(): ?User
    {
        return $this->user;
    }

    public function getData(): array
    {
        return [
            'displayName' => $this->user->display_name,
            'username'    => $this->user->username,
        ];
    }

    public static function getType(): string
    {
        return 'cosmosEggHuntComplete';
    }

    public static function getSubjectModel(): string
    {
        return User::class;
    }
}
