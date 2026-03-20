<?php

namespace Resofire\Avatars\Generator\Style;

interface StyleInterface
{
    /**
     * Return the style's unique key used in settings and DB.
     */
    public function key(): string;

    /**
     * Return the style's display name.
     */
    public function name(): string;

    /**
     * Generate a GD image resource for the given username.
     * The image is always 200x200 pixels.
     *
     * @return \GdImage
     */
    public function generate(string $username): \GdImage;
}
