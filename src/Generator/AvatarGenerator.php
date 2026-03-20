<?php

namespace Resofire\Avatars\Generator;

use Flarum\Foundation\Paths;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Illuminate\Support\Str;
use Resofire\Avatars\Generator\Style\StyleInterface;
use Resofire\Avatars\Generator\Style\RetroPixel;
use Resofire\Avatars\Generator\Style\Cyberpunk;
use Resofire\Avatars\Generator\Style\Android;
use Resofire\Avatars\Generator\Style\Fantasy;
use Resofire\Avatars\Generator\Style\Orc;
use Resofire\Avatars\Generator\Style\Anime;
use Resofire\Avatars\Generator\Style\Undead;
use Resofire\Avatars\Generator\Style\SpaceExplorer;
use Resofire\Avatars\Generator\Style\FantasyCreature;
use Resofire\Avatars\Generator\Style\Pirate;
use Resofire\Avatars\Generator\Style\Glitch;
use Resofire\Avatars\Generator\Style\Emoji;

class AvatarGenerator
{
    protected SettingsRepositoryInterface $settings;
    protected Paths $paths;

    /** @var StyleInterface[] */
    protected array $styles;

    public function __construct(SettingsRepositoryInterface $settings, Paths $paths)
    {
        $this->settings = $settings;
        $this->paths = $paths;

        $this->styles = [
            'retro-pixel'       => new RetroPixel(),
            'cyberpunk'         => new Cyberpunk(),
            'android'           => new Android(),
            'fantasy'           => new Fantasy(),
            'orc'               => new Orc(),
            'anime'             => new Anime(),
            'undead'            => new Undead(),
            'space-explorer'    => new SpaceExplorer(),
            'fantasy-creature'  => new FantasyCreature(),
            'pirate'            => new Pirate(),
            'glitch'            => new Glitch(),
            'emoji'             => new Emoji(),
        ];
    }

    public function styles(): array
    {
        return $this->styles;
    }

    public function resolveStyle(?string $key): StyleInterface
    {
        // Handle random selection
        if ($key === 'random' || empty($key)) {
            $keys = array_keys($this->styles);
            return $this->styles[$keys[array_rand($keys)]];
        }

        if (isset($this->styles[$key])) {
            return $this->styles[$key];
        }

        $default = $this->settings->get('resofire-avatars.default_style', 'retro-pixel');

        if ($default === 'random') {
            $keys = array_keys($this->styles);
            return $this->styles[$keys[array_rand($keys)]];
        }

        return $this->styles[$default] ?? $this->styles['retro-pixel'];
    }

    public function generateForUser(User $user, ?string $styleKey = null): void
    {
        $effectiveKey = $styleKey ?? $user->rf_avatar_style;
        $style = $this->resolveStyle($effectiveKey);
        $avatarDir = $this->paths->public . '/assets/avatars';

        if (!is_dir($avatarDir)) {
            mkdir($avatarDir, 0755, true);
        }

        $image = $style->generate($user->username);

        $filename = 'rf_' . Str::random(24) . '.png';
        $filepath = $avatarDir . '/' . $filename;

        imagepng($image, $filepath, 6);
        imagedestroy($image);

        $oldAvatar = $user->getRawOriginal('avatar_url');
        if ($oldAvatar && strpos($oldAvatar, 'rf_') === 0) {
            $oldPath = $avatarDir . '/' . $oldAvatar;
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }

        // Store the resolved style key (not 'random') so the user's picker shows
        // their actual current style, not just 'random'.
        $storedKey = $style->key();

        User::where('id', $user->id)->update([
            'avatar_url'      => $filename,
            'rf_avatar_style' => $storedKey,
        ]);

        $user->avatar_url      = $filename;
        $user->rf_avatar_style = $storedKey;
    }

    public function generatePreview(string $username, string $styleKey): string
    {
        $style = $this->resolveStyle($styleKey);
        $image = $style->generate($username);

        ob_start();
        imagepng($image, null, 6);
        $png = ob_get_clean();
        imagedestroy($image);

        return $png;
    }
}
