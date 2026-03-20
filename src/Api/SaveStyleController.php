<?php

namespace Resofire\Avatars\Api;

use Flarum\Http\RequestUtil;
use Flarum\User\User;
use Illuminate\Contracts\Filesystem\Factory;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Resofire\Avatars\Generator\AvatarGenerator;

class SaveStyleController implements RequestHandlerInterface
{
    protected AvatarGenerator $generator;
    protected $uploadDir;

    public function __construct(AvatarGenerator $generator, Factory $filesystemFactory)
    {
        $this->generator = $generator;
        $this->uploadDir = $filesystemFactory->disk('flarum-avatars');
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertRegistered();

        $body     = $request->getParsedBody();
        $styleKey = $body['style'] ?? null;
        $userId   = $body['userId'] ?? $actor->id;

        // Users can only change their own style unless they are admin.
        if ((string) $userId !== (string) $actor->id) {
            $actor->assertAdmin();
        }

        // Validate style key exists.
        $validStyles = array_keys($this->generator->styles());
        if (!in_array($styleKey, $validStyles, true)) {
            return new JsonResponse(['error' => 'Invalid style'], 422);
        }

        $user = User::findOrFail($userId);
        $this->generator->generateForUser($user, $styleKey);

        return new JsonResponse([
            'avatarUrl' => $user->avatar_url
                ? $this->uploadDir->url($user->avatar_url)
                : null,
            'style' => $styleKey,
        ]);
    }
}
