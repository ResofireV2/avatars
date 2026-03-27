<?php

namespace Resofire\Avatars\Api;

use Flarum\Http\RequestUtil;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Resofire\Avatars\Generator\AvatarGenerator;

class PreviewController implements RequestHandlerInterface
{
    protected AvatarGenerator $generator;

    public function __construct(AvatarGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        RequestUtil::getActor($request)->assertRegistered();

        $params   = $request->getQueryParams();
        $username = $params['username'] ?? 'user';
        $style    = $params['style'] ?? 'pixel-gamer';

        $webp = $this->generator->generatePreview($username, $style);

        $response = new Response();
        $response->getBody()->write($webp);

        return $response->withHeader('Content-Type', 'image/webp')
                        ->withHeader('Cache-Control', 'public, max-age=300');
    }
}
