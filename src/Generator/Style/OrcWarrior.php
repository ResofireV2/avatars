<?php

namespace Resofire\Avatars\Generator\Style;

class OrcWarrior extends AbstractStyle
{
    public function key(): string { return 'orc-warrior'; }
    public function name(): string { return 'Orc Warrior'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        $skinTones   = [[74,122,58],[50,100,40],[90,140,70],[40,90,30],[100,150,80]];
        $hairColors  = [[26,26,26],[80,40,0],[60,0,60],[0,40,80],[120,80,0]];
        $eyeColors   = [[255,204,0],[255,140,0],[200,255,0],[255,80,0],[220,220,0]];
        $armorColors = [[58,58,58],[80,60,20],[40,40,80],[80,40,40],[40,70,40]];

        [$sr,$sg,$sb]  = $this->pick($username, 0, $skinTones);
        [$hr,$hg,$hb]  = $this->pick($username, 1, $hairColors);
        [$er,$eg,$eb]  = $this->pick($username, 2, $eyeColors);
        [$ar2,$ag2,$ab2] = $this->pick($username, 3, $armorColors);
        $hasScar        = $this->hash($username, 4, 0, 1);
        $spikeCount     = $this->hash($username, 5, 3, 5);

        $bg      = $this->color($img, 13, 26, 13);
        $face    = $this->color($img, $sr, $sg, $sb);
        $faceD   = $this->color($img, (int)($sr*0.75), (int)($sg*0.75), (int)($sb*0.75));
        $hair    = $this->color($img, $hr, $hg, $hb);
        $eyeC    = $this->color($img, $er, $eg, $eb);
        $eyeI    = $this->color($img, 204, 68, 0);
        $armor   = $this->color($img, $ar2, $ag2, $ab2);
        $armorL  = $this->color($img, min(255,$ar2+30), min(255,$ag2+30), min(255,$ab2+30));
        $tusk    = $this->color($img, 232, 220, 192);
        $black   = $this->color($img, 0, 0, 0);
        $white   = $this->color($img, 255, 255, 255);
        $scar    = $this->color($img, 42, 80, 30);
        $brow    = $this->color($img, (int)($sr*0.6), (int)($sg*0.6), (int)($sb*0.6));
        $nostril = $this->color($img, (int)($sr*0.55), (int)($sg*0.55), (int)($sb*0.55));

        // Background
        $this->rect($img, 0, 0, 200, 200, $bg);

        // Armor
        $this->rect($img, 20, 168, 180, 200, $armor);
        $this->rect($img, 20, 168, 180, 172, $armorL);
        // shoulder hints
        $this->polygon($img, [20,168, 50,148, 60,168], $armor);
        $this->polygon($img, [180,168, 150,148, 140,168], $armor);

        // Neck
        $this->rect($img, 84, 152, 116, 172, $face);

        // Face
        $this->ellipse($img, 100, 106, 120, 128, $face);

        // Large pointed ears
        $this->polygon($img, [40,90, 20,60, 40,120], $face);
        $this->polygon($img, [160,90, 180,60, 160,120], $face);

        // Hair spikes
        $spacing = (int)(80 / ($spikeCount + 1));
        for ($i = 0; $i < $spikeCount; $i++) {
            $cx = 60 + ($i + 1) * $spacing;
            $this->polygon($img, [$cx - 8, 72, $cx, 44, $cx + 8, 72], $hair);
        }

        // Brow ridge
        $this->ellipse($img, 100, 84, 104, 28, $faceD);

        // Heavy brows
        $this->rect($img, 60, 88, 90, 96, $brow);
        $this->rect($img, 110, 88, 140, 96, $brow);

        // Eyes - yellow/red
        $this->ellipse($img, 76, 104, 40, 30, $eyeC);
        $this->ellipse($img, 124, 104, 40, 30, $eyeC);
        $this->ellipse($img, 76, 104, 26, 24, $eyeI);
        $this->ellipse($img, 124, 104, 26, 24, $eyeI);
        $this->ellipse($img, 76, 104, 12, 14, $black);
        $this->ellipse($img, 124, 104, 12, 14, $black);
        $this->rect($img, 70, 98, 74, 102, $white);
        $this->rect($img, 118, 98, 122, 102, $white);

        // Wide flat nose
        $this->ellipse($img, 100, 122, 32, 20, $faceD);
        $this->ellipse($img, 90, 124, 12, 12, $nostril);
        $this->ellipse($img, 110, 124, 12, 12, $nostril);

        // Mouth
        $this->rect($img, 66, 138, 134, 148, $faceD);

        // Tusks
        $this->polygon($img, [76,140, 70,164, 84,140], $tusk);
        $this->polygon($img, [124,140, 130,164, 116,140], $tusk);

        // Scar
        if ($hasScar) {
            $this->rect($img, 72, 88, 76, 126, $scar);
        }

        return $img;
    }
}
