<?php

namespace Resofire\Avatars\Generator\Style;

class AnimeChibi extends AbstractStyle
{
    public function key(): string { return 'anime-chibi'; }
    public function name(): string { return 'Anime Chibi'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        $skinTones   = [[253,232,208],[255,220,185],[240,200,160],[255,240,220],[220,180,140]];
        $hairColors  = [[204,34,102],[26,26,26],[255,200,0],[30,100,200],[150,0,200],[200,80,0],[0,160,80]];
        $eyeColors   = [[51,102,255],[0,180,120],[180,60,200],[255,120,0],[200,0,80],[0,160,220]];
        $collarColors= [[51,68,170],[170,40,40],[40,140,70],[120,60,180],[180,120,0]];
        $bgColors    = [[26,16,48],[10,20,40],[30,10,30],[10,30,20],[40,20,10]];

        [$sr,$sg,$sb]  = $this->pick($username, 0, $skinTones);
        [$hr,$hg,$hb]  = $this->pick($username, 1, $hairColors);
        [$er,$eg,$eb]  = $this->pick($username, 2, $eyeColors);
        [$cr,$cg,$cb]  = $this->pick($username, 3, $collarColors);
        [$bgr,$bgg,$bgb] = $this->pick($username, 4, $bgColors);
        $hasAhoge       = $this->hash($username, 5, 0, 1);

        $bg      = $this->color($img, $bgr, $bgg, $bgb);
        $face    = $this->color($img, $sr, $sg, $sb);
        $faceD   = $this->color($img, (int)($sr*0.88), (int)($sg*0.82), (int)($sb*0.78));
        $hair    = $this->color($img, $hr, $hg, $hb);
        $hairL   = $this->color($img, min(255,(int)($hr*1.3)), min(255,(int)($hg*1.3)), min(255,(int)($hb*1.3)));
        $eyeC    = $this->color($img, $er, $eg, $eb);
        $eyeD    = $this->color($img, (int)($er*0.3), (int)($eg*0.3), (int)($eb*0.3));
        $collar  = $this->color($img, $cr, $cg, $cb);
        $white   = $this->color($img, 255, 255, 255);
        $black   = $this->color($img, 0, 0, 12);
        $blush   = $this->colorA($img, 255, 153, 153, 80);
        $nose    = $this->color($img, (int)($sr*0.86), (int)($sg*0.78), (int)($sb*0.74));
        $mouth   = $this->color($img, 204, 102, 102);
        $star1   = $this->color($img, 255, 255, 255);

        // Background
        $this->rect($img, 0, 0, 200, 200, $bg);

        // Stars
        foreach ([[20,30],[170,24],[190,150],[14,140],[160,170]] as $i => [$sx,$sy]) {
            $size = $this->hash($username, 10 + $i, 1, 3);
            $this->rect($img, $sx, $sy, $sx + $size, $sy + $size, $star1);
        }

        // Sailor collar
        $this->rect($img, 20, 168, 180, 200, $collar);
        $this->polygon($img, [100,156, 88,190, 112,190], $white);

        // Neck
        $this->rect($img, 86, 152, 114, 170, $face);

        // Hair sides (behind face)
        $this->rect($img, 34, 72, 54, 148, $hair);
        $this->rect($img, 146, 72, 166, 148, $hair);

        // Big round chibi head
        $this->ellipse($img, 100, 106, 128, 128, $face);

        // Small ears
        $this->ellipse($img, 36, 106, 18, 22, $face);
        $this->ellipse($img, 164, 106, 18, 22, $face);

        // Hair top
        $this->ellipse($img, 100, 72, 136, 60, $hair);

        // Hair highlight
        $this->ellipse($img, 84, 68, 36, 18, $hairL);

        // Ahoge
        if ($hasAhoge) {
            $this->rect($img, 97, 44, 103, 66, $hair);
            $this->ellipse($img, 100, 44, 18, 14, $hair);
        }

        // HUGE anime eyes
        $this->ellipse($img, 72, 106, 46, 54, $white);
        $this->ellipse($img, 128, 106, 46, 54, $white);

        // Iris
        $this->ellipse($img, 72, 108, 34, 42, $eyeC);
        $this->ellipse($img, 128, 108, 34, 42, $eyeC);

        // Pupil
        $this->ellipse($img, 72, 110, 18, 22, $eyeD);
        $this->ellipse($img, 128, 110, 18, 22, $eyeD);

        // Eye shine - large
        $this->ellipse($img, 64, 100, 14, 14, $white);
        $this->ellipse($img, 120, 100, 14, 14, $white);

        // Eye shine - small
        $this->ellipse($img, 78, 112, 6, 6, $white);
        $this->ellipse($img, 134, 112, 6, 6, $white);

        // Blush marks
        $this->ellipse($img, 52, 120, 28, 16, $blush);
        $this->ellipse($img, 148, 120, 28, 16, $blush);

        // Tiny nose
        $this->ellipse($img, 100, 128, 8, 6, $nose);

        // Smile - stepped rectangle arc
        $this->rect($img, 80, 140, 88, 144, $mouth);
        $this->rect($img, 88, 143, 96, 147, $mouth);
        $this->rect($img, 96, 145, 104, 149, $mouth);
        $this->rect($img, 104, 143, 112, 147, $mouth);
        $this->rect($img, 112, 140, 120, 144, $mouth);

        return $img;
    }
}
