<?php

namespace Resofire\Avatars\Generator\Style;

class PixelGamer extends AbstractStyle
{
    public function key(): string { return 'pixel-gamer'; }
    public function name(): string { return 'Pixel Gamer'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        // Palette derived from username
        $skinTones = [[244,164,96],[255,195,140],[198,134,66],[240,180,120],[160,100,50]];
        $hairColors = [[61,43,31],[255,200,0],[180,30,30],[30,100,200],[20,20,20],[150,0,200]];
        $shirtColors = [[34,68,204],[180,30,30],[20,140,60],[120,60,180],[200,120,0]];
        $headsetColors = [[79,195,247],[255,100,100],[100,255,150],[255,200,0],[180,100,255]];
        $eyeColors = [[34,68,204],[30,160,80],[180,60,0],[120,60,180],[200,20,20]];

        [$sr,$sg,$sb] = $this->pick($username, 0, $skinTones);
        [$hr,$hg,$hb] = $this->pick($username, 1, $hairColors);
        [$sr2,$sg2,$sb2] = $this->pick($username, 2, $shirtColors);
        [$hsr,$hsg,$hsb] = $this->pick($username, 3, $headsetColors);
        [$er,$eg,$eb] = $this->pick($username, 4, $eyeColors);

        $bg      = $this->color($img, 26, 26, 46);
        $skin    = $this->color($img, $sr, $sg, $sb);
        $skinD   = $this->color($img, (int)($sr*0.85), (int)($sg*0.85), (int)($sb*0.85));
        $hair    = $this->color($img, $hr, $hg, $hb);
        $shirt   = $this->color($img, $sr2, $sg2, $sb2);
        $shirtD  = $this->color($img, (int)($sr2*0.75), (int)($sg2*0.75), (int)($sb2*0.75));
        $headset = $this->color($img, $hsr, $hsg, $hsb);
        $eye     = $this->color($img, $er, $eg, $eb);
        $white   = $this->color($img, 255, 255, 255);
        $black   = $this->color($img, 0, 0, 0);
        $mouth   = $this->color($img, 192, 96, 74);
        $nose    = $this->color($img, (int)($sr*0.82), (int)($sg*0.75), (int)($sb*0.7));

        // Background
        $this->rect($img, 0, 0, 200, 200, $bg);

        // Shirt / body
        $this->rect($img, 55, 148, 145, 200, $shirt);
        $this->rect($img, 86, 148, 114, 176, $shirtD);

        // Neck
        $this->rect($img, 86, 136, 114, 152, $skin);

        // Ears
        $this->rect($img, 54, 80, 66, 100, $skinD);
        $this->rect($img, 134, 80, 146, 100, $skinD);

        // Head
        $this->rect($img, 64, 56, 136, 140, $skin);

        // Hair top + sides
        $this->rect($img, 60, 50, 140, 68, $hair);
        $this->rect($img, 56, 56, 68, 76, $hair);
        $this->rect($img, 132, 56, 144, 76, $hair);

        // Eyes (white area)
        $this->rect($img, 70, 76, 90, 92, $white);
        $this->rect($img, 110, 76, 130, 92, $white);

        // Iris
        $this->rect($img, 73, 79, 88, 90, $eye);
        $this->rect($img, 113, 79, 128, 90, $eye);

        // Pupil
        $this->rect($img, 77, 81, 85, 88, $black);
        $this->rect($img, 117, 81, 125, 88, $black);

        // Eye shine
        $this->rect($img, 77, 79, 81, 83, $white);
        $this->rect($img, 117, 79, 121, 83, $white);

        // Eyebrows
        $this->rect($img, 68, 70, 92, 75, $hair);
        $this->rect($img, 108, 70, 132, 75, $hair);

        // Nose
        $this->rect($img, 96, 100, 104, 110, $nose);

        // Mouth - smile made of pixel blocks
        $this->rect($img, 74, 118, 82, 124, $mouth);
        $this->rect($img, 82, 122, 90, 128, $mouth);
        $this->rect($img, 90, 126, 110, 130, $mouth);
        $this->rect($img, 110, 122, 118, 128, $mouth);
        $this->rect($img, 118, 118, 126, 124, $mouth);

        // Headset cups
        $this->rect($img, 46, 68, 60, 94, $headset);
        $this->rect($img, 140, 68, 154, 94, $headset);

        // Headset band (stepped rectangles approximating arc)
        $this->rect($img, 58, 62, 72, 68, $headset);
        $this->rect($img, 70, 56, 84, 62, $headset);
        $this->rect($img, 82, 52, 118, 58, $headset);
        $this->rect($img, 116, 56, 130, 62, $headset);
        $this->rect($img, 128, 62, 142, 68, $headset);

        return $img;
    }
}
