<?php

namespace Resofire\CosmosTheme\Api\Controller;

use Resofire\CosmosTheme\Notification\GiftHuntCompleteBlueprint;
use Flarum\Http\RequestUtil;
use Flarum\Notification\NotificationSyncer;
use Flarum\User\User;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * POST /api/resofire/cosmos/gift-complete
 *
 * Called by the frontend when a logged-in user finds all 8 hidden gifts.
 *
 * Per-season rate limiting via user preference:
 *   Key:   cosmosGiftHunt{YEAR}   e.g. cosmosGiftHunt2025
 *   Value: '1'
 *
 * If the preference is already set for the current year, this endpoint
 * returns HTTP 204 silently — the celebration still fires on the frontend,
 * but no notification is sent to admins again.
 *
 * If not yet set: notifies every admin, then marks the preference.
 *
 * Guests cannot call this endpoint (returns 403 via isGuest() check).
 */
class GiftHuntCompleteController implements RequestHandlerInterface
{
    public function __construct(
        protected NotificationSyncer $notifications
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);

        // Guests cannot complete the hunt
        if ($actor->isGuest()) {
            return new JsonResponse(['error' => 'Authentication required'], 403);
        }

        $year    = (string) date('Y');
        $prefKey = 'cosmosGiftHunt' . $year;

        // Already completed this season — silent 204, no repeat notification
        if ($actor->getPreference($prefKey)) {
            return new JsonResponse([], 204);
        }

        // Mark as completed for this season
        $actor->setPreference($prefKey, '1');
        $actor->save();

        // Notify all admins
        $blueprint = new GiftHuntCompleteBlueprint($actor);
        $admins    = User::whereHas('groups', function ($q) {
            $q->where('id', \Flarum\Group\Group::ADMINISTRATOR_ID);
        })->get()->all();

        // Don't notify the completing user if they happen to be an admin
        $recipients = array_filter($admins, function (User $admin) use ($actor) {
            return $admin->id !== $actor->id;
        });

        if (!empty($recipients)) {
            $this->notifications->sync($blueprint, array_values($recipients));
        }

        return new JsonResponse(['status' => 'notified'], 200);
    }
}
