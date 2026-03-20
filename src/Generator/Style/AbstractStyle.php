<?php

namespace Resofire\Avatars\Generator\Style;

abstract class AbstractStyle implements StyleInterface
{
    protected const SIZE = 200;

    /**
     * Produce an array of deterministic integers from the username.
     * Each call with the same username + index returns the same value.
     */
    protected function hash(string $username, int $index, int $min, int $max): int
    {
        $hash = abs(crc32($username . ':' . $index));
        return $min + ($hash % ($max - $min + 1));
    }

    /**
     * Pick one item from an array deterministically.
     */
    protected function pick(string $username, int $index, array $options)
    {
        $i = $this->hash($username, $index, 0, count($options) - 1);
        return $options[$i];
    }

    /**
     * Create a blank 200x200 canvas.
     */
    protected function canvas(): \GdImage
    {
        $img = imagecreatetruecolor(self::SIZE, self::SIZE);
        imagesavealpha($img, true);
        return $img;
    }

    /**
     * Allocate a color on an image.
     */
    protected function color(\GdImage $img, int $r, int $g, int $b): int
    {
        return imagecolorallocate($img, $r, $g, $b);
    }

    /**
     * Allocate a color with alpha (0=opaque, 127=transparent).
     */
    protected function colorA(\GdImage $img, int $r, int $g, int $b, int $a): int
    {
        return imagecolorallocatealpha($img, $r, $g, $b, $a);
    }

    /**
     * Draw a filled ellipse centered at cx,cy.
     */
    protected function ellipse(\GdImage $img, int $cx, int $cy, int $w, int $h, int $color): void
    {
        imagefilledellipse($img, $cx, $cy, $w, $h, $color);
    }

    /**
     * Draw a filled rectangle.
     */
    protected function rect(\GdImage $img, int $x1, int $y1, int $x2, int $y2, int $color): void
    {
        imagefilledrectangle($img, $x1, $y1, $x2, $y2, $color);
    }

    /**
     * Draw a filled polygon from a flat array of [x1,y1,x2,y2,...].
     */
    protected function polygon(\GdImage $img, array $points, int $color): void
    {
        imagefilledpolygon($img, $points, $color);
    }

    /**
     * Draw a rounded rectangle.
     */
    protected function roundRect(\GdImage $img, int $x, int $y, int $w, int $h, int $r, int $color): void
    {
        // Fill center and two bars
        $this->rect($img, $x + $r, $y, $x + $w - $r, $y + $h, $color);
        $this->rect($img, $x, $y + $r, $x + $w, $y + $h - $r, $color);
        // Four corner arcs
        $this->ellipse($img, $x + $r,     $y + $r,     $r * 2, $r * 2, $color);
        $this->ellipse($img, $x + $w - $r, $y + $r,     $r * 2, $r * 2, $color);
        $this->ellipse($img, $x + $r,     $y + $h - $r, $r * 2, $r * 2, $color);
        $this->ellipse($img, $x + $w - $r, $y + $h - $r, $r * 2, $r * 2, $color);
    }

    /**
     * Scale a value from 100px base canvas to 200px.
     */
    protected function s(int $v): int
    {
        return $v * 2;
    }
}
