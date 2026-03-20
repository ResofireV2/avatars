<?php

namespace Resofire\Avatars\Generator\Style;

class Anime extends AbstractStyle
{
    public function key(): string { return 'anime'; }
    public function name(): string { return 'Anime'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        $bgColors   = [[68,136,204],[238,51,136],[68,204,170],[170,136,204],[238,204,0],[238,102,68]];
        $hairColors = [[51,102,170],[204,17,102],[51,170,136],[136,102,170],[204,170,0],[204,68,34]];
        $eyeColors  = [[34,102,255],[238,51,136],[34,170,102],[136,68,204],[204,136,0],[238,68,68]];
        $hairTypes  = [0,1,2,3]; // ahoge, twin-block, fringe, spiky

        [$br,$bg2,$bb] = $this->pick($username, 0, $bgColors);
        [$hr,$hg,$hb]  = $this->pick($username, 1, $hairColors);
        [$er,$eg,$eb]  = $this->pick($username, 2, $eyeColors);
        $hairType       = $this->pick($username, 3, $hairTypes);
        $expression     = $this->pick($username, 4, [0,1,2,3]); // happy, wink, star, heart

        $bg    = $this->color($img, $br, $bg2, $bb);
        $hair  = $this->color($img, $hr, $hg, $hb);
        $hairL = $this->color($img, min(255,(int)($hr*1.3)), min(255,(int)($hg*1.3)), min(255,(int)($hb*1.3)));
        $eye   = $this->color($img, $er, $eg, $eb);
        $eyeD  = $this->color($img, (int)($er*0.25), (int)($eg*0.25), (int)($eb*0.25));
        $darkBg= $this->color($img, (int)($br*0.7), (int)($bg2*0.7), (int)($bb*0.7));
        $white = $this->color($img, 255, 255, 255);
        $black = $this->color($img, 8, 8, 20);
        $blush = $this->colorA($img, 255, 120, 120, 80);
        $mouth = $this->color($img, 200, 80, 80);

        $this->rect($img, 0, 0, 200, 200, $bg);

        // Hair top block
        $this->rect($img, 0, 0, 200, 50, $darkBg);

        switch ($hairType) {
            case 0: // ahoge
                $this->rect($img, 88, 0, 112, 56, $hair);
                $this->ellipse($img, 100, 8, 28, 20, $hair);
                break;
            case 1: // twin block
                $this->rect($img, 0, 0, 74, 50, $hair);
                $this->rect($img, 126, 0, 200, 50, $hair);
                break;
            case 2: // full fringe
                $this->rect($img, 0, 0, 200, 50, $hair);
                $this->ellipse($img, 100, 50, 160, 20, $hair);
                break;
            case 3: // spiky
                foreach ([30,55,80,100,120,145,170] as $sx) {
                    $h = $this->hash($username, 30+$sx, 16, 36);
                    $this->polygon($img, [$sx-12,50, $sx,50-$h, $sx+12,50], $hair);
                }
                break;
        }

        // Hair highlight
        $this->ellipse($img, 70, 30, 50, 20, $hairL);

        // Giant anime eyes
        $this->ellipse($img, 66, 106, 56, 64, $white);
        $this->ellipse($img, 134, 106, 56, 64, $white);

        switch ($expression) {
            case 0: // happy
                $this->ellipse($img, 66, 107, 40, 48, $eye);
                $this->ellipse($img, 134, 107, 40, 48, $eye);
                $this->ellipse($img, 66, 108, 22, 28, $eyeD);
                $this->ellipse($img, 134, 108, 22, 28, $eyeD);
                $this->ellipse($img, 56, 97, 14, 14, $white);
                $this->ellipse($img, 124, 97, 14, 14, $white);
                $this->ellipse($img, 72, 112, 8, 8, $white);
                $this->ellipse($img, 140, 112, 8, 8, $white);
                break;
            case 1: // wink right
                $this->ellipse($img, 66, 107, 40, 48, $eye);
                $this->ellipse($img, 66, 108, 22, 28, $eyeD);
                $this->ellipse($img, 56, 97, 14, 14, $white);
                $this->ellipse($img, 72, 112, 8, 8, $white);
                // wink closed eye
                $this->rect($img, 114, 102, 154, 114, $bg);
                $this->rect($img, 114, 105, 154, 111, $eyeD);
                break;
            case 2: // star pupils
                $this->ellipse($img, 66, 107, 40, 48, $eye);
                $this->ellipse($img, 134, 107, 40, 48, $eye);
                $star1 = [66,94, 69,104, 78,104, 71,110, 74,120, 66,114, 58,120, 61,110, 54,104, 63,104];
                $star2 = [134,94, 137,104, 146,104, 139,110, 142,120, 134,114, 126,120, 129,110, 122,104, 131,104];
                $this->polygon($img, $star1, $white);
                $this->polygon($img, $star2, $white);
                break;
            case 3: // heart pupils
                $this->ellipse($img, 66, 107, 40, 48, $eye);
                $this->ellipse($img, 134, 107, 40, 48, $eye);
                // hearts as circles + triangle approx
                $this->ellipse($img, 60, 103, 16, 14, $white);
                $this->ellipse($img, 72, 103, 16, 14, $white);
                $this->polygon($img, [54,108, 78,108, 66,122], $white);
                $this->ellipse($img, 128, 103, 16, 14, $white);
                $this->ellipse($img, 140, 103, 16, 14, $white);
                $this->polygon($img, [122,108, 146,108, 134,122], $white);
                break;
        }

        // Blush
        $this->ellipse($img, 36, 126, 34, 18, $blush);
        $this->ellipse($img, 164, 126, 34, 18, $blush);

        // Nose
        $this->ellipse($img, 100, 130, 10, 8, $this->color($img, (int)($br*0.8), (int)($bg2*0.75), (int)($bb*0.75)));

        // Mouth
        imagesetthickness($img, 3);
        imagearc($img, 100, 148, 50, 26, 15, 165, $mouth);
        imagesetthickness($img, 1);

        return $img;
    }
}
