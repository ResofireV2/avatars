<?php

namespace Resofire\Avatars\Api;

use Flarum\Foundation\Paths;
use Flarum\Http\RequestUtil;
use Flarum\User\User;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FlushAvatarsController implements RequestHandlerInterface
{
    protected Paths $paths;

    public function __construct(Paths $paths)
    {
        $this->paths = $paths;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        RequestUtil::getActor($request)->assertAdmin();

        $avatarDir = $this->paths->public . '/assets/avatars';

        // Collect filenames saved by this extension (rf_ prefix).
        $ourFiles = User::whereNotNull('avatar_url')
            ->where('avatar_url', 'like', 'rf_%')
            ->pluck('avatar_url')
            ->toArray();

        // Clear DB records.
        $affected = User::whereNotNull('avatar_url')
            ->where('avatar_url', 'like', 'rf_%')
            ->update(['avatar_url' => null, 'rf_avatar_style' => null]);

        // Delete files.
        $filesDeleted = 0;
        foreach ($ourFiles as $filename) {
            $filepath = $avatarDir . '/' . basename($filename);
            if (is_file($filepath)) {
                unlink($filepath);
                $filesDeleted++;
            }
        }

        // Also sweep orphaned rf_ files not in DB.
        if (is_dir($avatarDir)) {
            foreach (scandir($avatarDir) as $filename) {
                if (strpos($filename, 'rf_') !== 0) continue;
                $filepath = $avatarDir . '/' . $filename;
                if (is_file($filepath)) {
                    unlink($filepath);
                    $filesDeleted++;
                }
            }
        }

        return new JsonResponse([
            'flushed'      => $affected,
            'filesDeleted' => $filesDeleted,
        ]);
    }
}
