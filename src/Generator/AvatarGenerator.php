<?php

namespace Resofire\Avatars\Generator;

use Flarum\Foundation\Paths;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Illuminate\Support\Str;
use Resofire\Avatars\Generator\Style\StyleInterface;
use Resofire\Avatars\Generator\Style\PixelGamer;
use Resofire\Avatars\Generator\Style\Cyberpunk;
use Resofire\Avatars\Generator\Style\FantasyWarrior;
use Resofire\Avatars\Generator\Style\ScifiAndroid;
use Resofire\Avatars\Generator\Style\OrcWarrior;
use Resofire\Avatars\Generator\Style\AnimeChibi;

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
            'pixel-gamer'     => new PixelGamer(),
            'cyberpunk'       => new Cyberpunk(),
            'fantasy-warrior' => new FantasyWarrior(),
            'scifi-android'   => new ScifiAndroid(),
            'orc-warrior'     => new OrcWarrior(),
            'anime-chibi'     => new AnimeChibi(),
        ];
    }

    /**
     * Return all registered styles.
     *
     * @return StyleInterface[]
     */
    public function styles(): array
    {
        return $this->styles;
    }

    /**
     * Get a style by key, falling back to admin default, then pixel-gamer.
     */
    public function resolveStyle(?string $key): StyleInterface
    {
        if ($key && isset($this->styles[$key])) {
            return $this->styles[$key];
        }

        $default = $this->settings->get('resofire-avatars.default_style', 'pixel-gamer');
        return $this->styles[$default] ?? $this->styles['pixel-gamer'];
    }

    /**
     * Generate a PNG for the given username and style key,
     * save it to assets/avatars, update the user record,
     * and delete the old file if there was one.
     */
    public function generateForUser(User $user, ?string $styleKey = null): void
    {
        $style = $this->resolveStyle($styleKey ?? $user->rf_avatar_style);
        $avatarDir = $this->paths->public . '/assets/avatars';

        if (!is_dir($avatarDir)) {
            mkdir($avatarDir, 0755, true);
        }

        // Generate the image
        $image = $style->generate($user->username);

        // Save to PNG
        $filename = 'rf_' . Str::random(24) . '.png';
        $filepath = $avatarDir . '/' . $filename;

        imagepng($image, $filepath, 6); // compression 6 = good balance
        imagedestroy($image);

        // Delete old file if it was ours
        $oldAvatar = $user->getRawOriginal('avatar_url');
        if ($oldAvatar && strpos($oldAvatar, 'rf_') === 0) {
            $oldPath = $avatarDir . '/' . $oldAvatar;
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }

        // Update DB
        User::where('id', $user->id)->update([
            'avatar_url'      => $filename,
            'rf_avatar_style' => $styleKey ?? $user->rf_avatar_style,
        ]);

        // Sync in-memory model
        $user->avatar_url      = $filename;
        $user->rf_avatar_style = $styleKey ?? $user->rf_avatar_style;
    }

    /**
     * Generate a PNG data string (not saved to disk) for preview purposes.
     * Returns raw PNG binary.
     */
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
