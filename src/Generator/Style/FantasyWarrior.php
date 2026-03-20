<?php

namespace Resofire\Avatars\Generator\Style;

class FantasyWarrior extends AbstractStyle
{
    public function key(): string { return 'fantasy-warrior'; }
    public function name(): string { return 'Fantasy Warrior'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        $skinTones  = [[240,208,160],[255,220,180],[200,160,110],[180,130,80],[255,235,200]];
        $hairColors = [[139,26,26],[20,20,20],[200,160,0],[30,80,160],[180,100,180],[80,160,80]];
        $eyeColors  = [[68,204,170],[100,180,255],[200,180,50],[180,80,200],[50,200,100]];
        $gemColors  = [[68,170,255],[255,80,80],[80,220,80],[255,200,0],[200,100,255]];
        $paintColors= [[139,26,26],[26,80,139],[139,100,26],[80,139,26],[100,26,139]];

        [$sr,$sg,$sb]  = $this->pick($username, 0, $skinTones);
        [$hr,$hg,$hb]  = $this->pick($username, 1, $hairColors);
        [$er,$eg,$eb]  = $this->pick($username, 2, $eyeColors);
        [$gr,$gg,$gb]  = $this->pick($username, 3, $gemColors);
        [$pr,$pg,$pb]  = $this->pick($username, 4, $paintColors);
        $paintStyle     = $this->hash($username, 5, 0, 2); // 0=lines, 1=dots, 2=stripe

        $bg      = $this->color($img, 26, 14, 10);
        $face    = $this->color($img, $sr, $sg, $sb);
        $faceD   = $this->color($img, (int)($sr*0.82), (int)($sg*0.78), (int)($sb*0.72));
        $hair    = $this->color($img, $hr, $hg, $hb);
        $eyeC    = $this->color($img, $er, $eg, $eb);
        $gem     = $this->color($img, $gr, $gg, $gb);
        $gold    = $this->color($img, 200, 160, 32);
        $paint   = $this->color($img, $pr, $pg, $pb);
        $white   = $this->color($img, 255, 255, 255);
        $black   = $this->color($img, 0, 0, 0);
        $mouth   = $this->color($img, 192, 96, 80);
        $armor   = $this->color($img, 90, 74, 42);

        // Background
        $this->rect($img, 0, 0, 200, 200, $bg);

        // Armor bottom
        $this->rect($img, 0, 168, 200, 200, $armor);
        $this->rect($img, 0, 168, 200, 171, $gold);

        // Neck
        $this->rect($img, 86, 152, 114, 172, $face);

        // Hair sides
        $this->rect($img, 58, 70, 74, 148, $hair);
        $this->rect($img, 126, 70, 142, 148, $hair);

        // Face
        $this->ellipse($img, 100, 106, 104, 120, $face);

        // Pointed ears (triangles)
        $this->polygon($img, [58,90, 40,66, 58,118], $face);
        $this->polygon($img, [142,90, 160,66, 142,118], $face);

        // Hair top
        $this->ellipse($img, 100, 66, 112, 48, $hair);

        // Circlet
        $this->rect($img, 60, 72, 140, 78, $gold);
        $this->polygon($img, [100,58, 108,74, 92,74], $gold);
        $this->ellipse($img, 100, 56, 14, 14, $gem);

        // Eyes
        $this->ellipse($img, 78, 100, 32, 24, $white);
        $this->ellipse($img, 122, 100, 32, 24, $white);
        $this->ellipse($img, 78, 100, 20, 20, $eyeC);
        $this->ellipse($img, 122, 100, 20, 20, $eyeC);
        $this->ellipse($img, 78, 100, 10, 10, $black);
        $this->ellipse($img, 122, 100, 10, 10, $black);
        $this->rect($img, 72, 94, 76, 98, $white);
        $this->rect($img, 116, 94, 120, 98, $white);

        // Angled brows - two rect segments each
        $this->rect($img, 62, 87, 76, 91, $hair);
        $this->rect($img, 76, 90, 90, 94, $hair);
        $this->rect($img, 110, 90, 124, 94, $hair);
        $this->rect($img, 124, 87, 138, 91, $hair);

        // Nose
        $this->rect($img, 96, 112, 104, 120, $faceD);

        // Mouth
        $this->rect($img, 80, 130, 120, 136, $mouth);
        $this->rect($img, 78, 132, 82, 136, $mouth);
        $this->rect($img, 118, 132, 122, 136, $mouth);

        // War paint
        switch ($paintStyle) {
            case 0: // vertical lines
                $this->rect($img, 62, 102, 66, 120, $paint);
                $this->rect($img, 134, 102, 138, 120, $paint);
                break;
            case 1: // dots
                $this->ellipse($img, 64, 106, 8, 8, $paint);
                $this->ellipse($img, 64, 118, 8, 8, $paint);
                $this->ellipse($img, 136, 106, 8, 8, $paint);
                $this->ellipse($img, 136, 118, 8, 8, $paint);
                break;
            case 2: // diagonal stripe
                $this->polygon($img, [60,100, 66,100, 76,122, 70,122], $paint);
                $this->polygon($img, [134,100, 140,100, 130,122, 124,122], $paint);
                break;
        }

        return $img;
    }
}
