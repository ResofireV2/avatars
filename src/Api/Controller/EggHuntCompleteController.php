<?php

namespace Resofire\CosmosTheme\Api\Controller;

use Resofire\CosmosTheme\Notification\EggHuntCompleteBlueprint;
use Flarum\Http\RequestUtil;
use Flarum\Notification\NotificationSyncer;
use Flarum\User\User;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * POST /api/resofire/cosmos/egg-complete
 *
 * Called by the frontend when a logged-in user finds all 8 hidden eggs.
 * Per-season rate limiting via user preference cosmosEggHunt{YEAR}.
 */
class EggHuntCompleteController implements RequestHandlerInterface
{
    public function __construct(
        protected NotificationSyncer $notifications
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);

        if ($actor->isGuest()) {
            return new JsonResponse(['error' => 'Authentication required'], 403);
        }

        $year    = (string) date('Y');
        $prefKey = 'cosmosEggHunt' . $year;

        if ($actor->getPreference($prefKey)) {
            return new JsonResponse([], 204);
        }

        $actor->setPreference($prefKey, '1');
        $actor->save();

        $blueprint  = new EggHuntCompleteBlueprint($actor);
        $admins     = User::whereHas('groups', function ($q) {
            $q->where('id', \Flarum\Group\Group::ADMINISTRATOR_ID);
        })->get()->all();
        $recipients = array_filter($admins, function (User $admin) use ($actor) {
            return $admin->id !== $actor->id;
        });

        if (!empty($recipients)) {
            $this->notifications->sync($blueprint, array_values($recipients));
        }

        return new JsonResponse(['status' => 'notified'], 200);
    }
}
