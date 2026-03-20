<?php

namespace Resofire\Avatars\Generator\Style;

class Orc extends AbstractStyle
{
    public function key(): string { return 'orc'; }
    public function name(): string { return 'Orc'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        $bgColors  = [[42,74,26],[58,26,90],[74,42,16],[58,74,26],[26,26,42],[90,26,10]];
        $eyeColors = [[255,204,0],[255,80,0],[204,220,0],[255,140,0],[220,220,0],[255,60,60]];
        $hairColors= [[26,26,26],[80,40,0],[60,0,60],[0,40,80],[120,80,0],[180,30,30]];
        $spikeCount= [3,4,5];

        [$br,$bg2,$bb] = $this->pick($username, 0, $bgColors);
        [$er,$eg,$eb]  = $this->pick($username, 1, $eyeColors);
        [$hr,$hg,$hb]  = $this->pick($username, 2, $hairColors);
        $spikes         = $this->pick($username, 3, $spikeCount);
        $hasScar        = $this->hash($username, 4, 0, 1);
        $hasWarpaint    = $this->hash($username, 5, 0, 1);

        $bg    = $this->color($img, $br, $bg2, $bb);
        $eye   = $this->color($img, $er, $eg, $eb);
        $eyeI  = $this->color($img, (int)($er*0.6), (int)($eg*0.3), 0);
        $hair  = $this->color($img, $hr, $hg, $hb);
        $dark  = $this->color($img, (int)($br*0.6), (int)($bg2*0.6), (int)($bb*0.6));
        $white = $this->color($img, 255, 255, 255);
        $black = $this->color($img, 8, 8, 8);
        $tusk  = $this->color($img, 232, 220, 192);
        $nostril = $this->color($img, (int)($br*0.5), (int)($bg2*0.5), (int)($bb*0.5));

        $this->rect($img, 0, 0, 200, 200, $bg);

        // Hair spikes from top arc
        $spacing = (int)(140 / ($spikes + 1));
        for ($i = 0; $i < $spikes; $i++) {
            $cx = 30 + ($i + 1) * $spacing;
            $h  = $this->hash($username, 20+$i, 28, 46);
            $this->polygon($img, [$cx-12, 50, $cx, $cx > 100 ? 50-$h+$h : 50-$h, $cx+12, 50], $hair);
        }

        // Brow ridge
        $this->ellipse($img, 100, 72, 140, 30, $dark);

        // Eyes - wide and fierce
        $this->ellipse($img, 62, 94, 52, 40, $eye);
        $this->ellipse($img, 138, 94, 52, 40, $eye);
        $this->ellipse($img, 62, 94, 34, 28, $eyeI);
        $this->ellipse($img, 138, 94, 34, 28, $eyeI);
        $this->ellipse($img, 62, 95, 16, 20, $black);
        $this->ellipse($img, 138, 95, 16, 20, $black);
        $this->ellipse($img, 56, 88, 8, 8, $white);
        $this->ellipse($img, 132, 88, 8, 8, $white);

        // Warpaint
        if ($hasWarpaint) {
            $wp = $this->color($img, min(255,$br+80), (int)($bg2*0.4), (int)($bb*0.4));
            $this->rect($img, 34, 90, 52, 100, $wp);
            $this->rect($img, 148, 90, 166, 100, $wp);
        }

        // Wide flat nose
        $this->ellipse($img, 100, 120, 50, 32, $dark);
        $this->ellipse($img, 84, 124, 18, 18, $nostril);
        $this->ellipse($img, 116, 124, 18, 18, $nostril);

        // Mouth
        $this->ellipse($img, 100, 152, 80, 28, $dark);

        // Tusks
        $this->polygon($img, [68,146, 56,178, 80,146], $tusk);
        $this->polygon($img, [132,146, 144,178, 120,146], $tusk);

        // Scar
        if ($hasScar) {
            $scar = $this->color($img, (int)($br*0.5), (int)($bg2*0.5), (int)($bb*0.5));
            $this->rect($img, 58, 70, 66, 130, $scar);
        }

        return $img;
    }
}
