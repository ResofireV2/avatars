<?php

namespace Resofire\Avatars\Generator\Style;

class Undead extends AbstractStyle
{
    public function key(): string { return 'undead'; }
    public function name(): string { return 'Undead'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        $bgColors  = [[74,74,74],[42,74,26],[102,0,0],[221,216,192],[58,26,74],[8,8,8]];
        $eyeTypes  = [0,1,2,3]; // hollow, X, glow-red, spiral
        $mouthTypes= [0,1,2];   // stitched, grin-fangs, drool

        [$br,$bg2,$bb] = $this->pick($username, 0, $bgColors);
        $eyeType        = $this->pick($username, 1, $eyeTypes);
        $mouthType      = $this->pick($username, 2, $mouthTypes);
        $hasCracks      = $this->hash($username, 3, 0, 1);

        $bg    = $this->color($img, $br, $bg2, $bb);
        $dark  = $this->color($img, (int)($br*0.4), (int)($bg2*0.4), (int)($bb*0.4));
        $red   = $this->color($img, 180, 0, 0);
        $green = $this->color($img, 136, 204, 0);
        $purp  = $this->color($img, 170, 80, 255);
        $white = $this->color($img, 220, 215, 200);
        $black = $this->color($img, 10, 10, 10);

        $this->rect($img, 0, 0, 200, 200, $bg);

        // Cracks
        if ($hasCracks) {
            $crack = $this->color($img, max(0,$br-40), max(0,$bg2-40), max(0,$bb-40));
            $this->rect($img, 92, 0, 96, 60, $crack);
            $this->rect($img, 94, 20, 106, 24, $crack);
            $this->rect($img, 50, 30, 54, 70, $crack);
        }

        // Eye sockets (deep)
        $this->ellipse($img, 66, 88, 56, 60, $black);
        $this->ellipse($img, 134, 88, 56, 60, $black);

        switch ($eyeType) {
            case 0: // hollow with faint red glow
                $this->ellipse($img, 66, 90, 22, 24, $red);
                $this->ellipse($img, 134, 90, 22, 24, $red);
                $this->ellipse($img, 66, 90, 10, 12, $black);
                $this->ellipse($img, 134, 90, 10, 12, $black);
                break;
            case 1: // X eyes
                imagesetthickness($img, 5);
                imageline($img, 44, 66, 88, 110, $green);
                imageline($img, 88, 66, 44, 110, $green);
                imageline($img, 112, 66, 156, 110, $green);
                imageline($img, 156, 66, 112, 110, $green);
                imagesetthickness($img, 1);
                break;
            case 2: // glowing red
                $this->ellipse($img, 66, 88, 40, 44, $red);
                $this->ellipse($img, 134, 88, 40, 44, $red);
                $this->ellipse($img, 66, 88, 20, 22, $black);
                $this->ellipse($img, 134, 88, 20, 22, $black);
                break;
            case 3: // spiral/swirl
                $this->ellipse($img, 66, 88, 44, 44, $purp);
                $this->ellipse($img, 66, 88, 30, 30, $black);
                $this->ellipse($img, 66, 88, 18, 18, $purp);
                $this->ellipse($img, 66, 88, 8, 8, $black);
                $this->ellipse($img, 134, 88, 44, 44, $purp);
                $this->ellipse($img, 134, 88, 30, 30, $black);
                $this->ellipse($img, 134, 88, 18, 18, $purp);
                $this->ellipse($img, 134, 88, 8, 8, $black);
                break;
        }

        switch ($mouthType) {
            case 0: // stitched
                $this->rect($img, 50, 136, 150, 144, $dark);
                foreach ([56,70,84,98,112,126,138] as $sx) {
                    $this->rect($img, $sx, 130, $sx+4, 150, $dark);
                }
                break;
            case 1: // grin with fangs
                $this->ellipse($img, 100, 150, 90, 30, $dark);
                $this->polygon($img, [64,140, 58,160, 72,140], $white);
                $this->polygon($img, [88,140, 82,158, 96,140], $white);
                $this->polygon($img, [112,140, 106,158, 120,140], $white);
                $this->polygon($img, [136,140, 130,160, 142,140], $white);
                break;
            case 2: // drool
                imagesetthickness($img, 3);
                imagearc($img, 100, 144, 80, 36, 10, 170, $green);
                imagesetthickness($img, 1);
                $this->ellipse($img, 86, 168, 10, 18, $green);
                $this->ellipse($img, 86, 178, 7, 7, $green);
                break;
        }

        return $img;
    }
}
