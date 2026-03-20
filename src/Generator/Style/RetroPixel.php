<?php

namespace Resofire\Avatars\Generator\Style;

class RetroPixel extends AbstractStyle
{
    public function key(): string { return 'retro-pixel'; }
    public function name(): string { return 'Retro Pixel'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        $bgColors   = [[224,112,48],[204,34,34],[34,119,102],[200,160,96],[107,124,42],[170,68,34]];
        $darkColors = [[192,80,32],[170,17,17],[17,102,85],[168,128,64],[85,102,26],[136,51,17]];
        $eyeColors  = [[34,68,204],[34,160,80],[180,60,0],[120,60,180],[200,20,20],[255,200,0]];
        $mouthTypes = [0,1,2,3,4]; // smile, frown, teeth, flat, smirk

        [$br,$bg2,$bb]  = $this->pick($username, 0, $bgColors);
        [$dr,$dg,$db]   = $this->pick($username, 1, $darkColors);
        [$er,$eg,$eb]   = $this->pick($username, 2, $eyeColors);
        $mouth           = $this->pick($username, 3, $mouthTypes);
        $hasHeadset      = $this->hash($username, 4, 0, 1);

        $bg    = $this->color($img, $br, $bg2, $bb);
        $dark  = $this->color($img, $dr, $dg, $db);
        $eye   = $this->color($img, $er, $eg, $eb);
        $white = $this->color($img, 255, 255, 255);
        $black = $this->color($img, 20, 20, 20);

        $this->rect($img, 0, 0, 200, 200, $bg);

        if ($hasHeadset) {
            $hs = $this->color($img, 79, 195, 247);
            $this->rect($img, 14, 68, 30, 108, $hs);
            $this->rect($img, 170, 68, 186, 108, $hs);
            $this->rect($img, 22, 58, 56, 68, $hs);
            $this->rect($img, 144, 58, 178, 68, $hs);
            $this->rect($img, 56, 50, 144, 60, $hs);
        }

        // Eye sockets
        $this->rect($img, 52, 72, 82, 92, $dark);
        $this->rect($img, 118, 72, 148, 92, $dark);
        // Eye whites
        $this->rect($img, 54, 74, 80, 90, $white);
        $this->rect($img, 120, 74, 146, 90, $white);
        // Iris
        $this->rect($img, 58, 76, 76, 88, $eye);
        $this->rect($img, 124, 76, 142, 88, $eye);
        // Pupil
        $this->rect($img, 62, 78, 72, 86, $black);
        $this->rect($img, 128, 78, 138, 86, $black);
        // Shine
        $this->rect($img, 62, 78, 66, 82, $white);
        $this->rect($img, 128, 78, 132, 82, $white);

        // Mouth
        switch ($mouth) {
            case 0: // smile
                $this->rect($img, 70, 126, 78, 132, $dark);
                $this->rect($img, 78, 130, 86, 136, $dark);
                $this->rect($img, 86, 132, 114, 136, $dark);
                $this->rect($img, 114, 130, 122, 136, $dark);
                $this->rect($img, 122, 126, 130, 132, $dark);
                break;
            case 1: // frown
                $this->rect($img, 70, 132, 78, 136, $dark);
                $this->rect($img, 78, 130, 86, 134, $dark);
                $this->rect($img, 86, 128, 114, 132, $dark);
                $this->rect($img, 114, 130, 122, 134, $dark);
                $this->rect($img, 122, 132, 130, 136, $dark);
                break;
            case 2: // teeth
                $this->rect($img, 68, 126, 132, 138, $dark);
                foreach ([70,80,90,100,110,120] as $tx) {
                    $this->rect($img, $tx, 126, $tx+8, 134, $white);
                }
                break;
            case 3: // flat
                $this->rect($img, 72, 130, 128, 136, $dark);
                break;
            case 4: // smirk
                $this->rect($img, 72, 130, 100, 134, $dark);
                $this->rect($img, 100, 126, 128, 130, $dark);
                break;
        }

        return $img;
    }
}
