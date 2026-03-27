<?php

namespace Resofire\Avatars\Api;

use Flarum\Http\RequestUtil;
use Flarum\User\User;
use Illuminate\Contracts\Filesystem\Factory;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FlushAvatarsController implements RequestHandlerInterface
{
    protected Factory $filesystemFactory;

    public function __construct(Factory $filesystemFactory)
    {
        $this->filesystemFactory = $filesystemFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        RequestUtil::getActor($request)->assertAdmin();

        $disk = $this->filesystemFactory->disk('flarum-avatars');

        // Collect filenames saved by this extension (rf_ prefix).
        $ourFiles = User::whereNotNull('avatar_url')
            ->where('avatar_url', 'like', 'rf_%')
            ->pluck('avatar_url')
            ->toArray();

        // Clear avatar_url and rf_avatar_style for users with rf_ avatars.
        $affected = User::whereNotNull('avatar_url')
            ->where('avatar_url', 'like', 'rf_%')
            ->update(['avatar_url' => null, 'rf_avatar_style' => null]);

        // Also clear rf_avatar_style for any remaining users.
        User::whereNotNull('rf_avatar_style')->update(['rf_avatar_style' => null]);

        // Delete the files via the disk abstraction.
        $filesDeleted = 0;
        foreach ($ourFiles as $filename) {
            $filename = basename($filename);
            if ($disk->exists($filename)) {
                $disk->delete($filename);
                $filesDeleted++;
            }
        }

        // Sweep orphaned rf_ files not referenced in the DB.
        foreach ($disk->files() as $filename) {
            if (strpos(basename($filename), 'rf_') !== 0) continue;
            $disk->delete($filename);
            $filesDeleted++;
        }

        return new JsonResponse([
            'flushed'      => $affected,
            'filesDeleted' => $filesDeleted,
        ]);
    }
}
