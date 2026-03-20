<?php

namespace Resofire\Avatars\Generator\Style;

class SpaceExplorer extends AbstractStyle
{
    public function key(): string { return 'space-explorer'; }
    public function name(): string { return 'Space Explorer'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        $bgColors  = [[42,10,90],[138,102,0],[5,5,16],[10,42,106],[136,26,0],[0,68,34]];
        $eyeTypes  = [0,1,2,3,4]; // normal-big, four-eyes, star, squint, cyclops
        $accentColors = [[102,68,204],[204,153,0],[68,68,204],[68,170,255],[204,68,0],[0,204,102]];

        [$br,$bg2,$bb] = $this->pick($username, 0, $bgColors);
        [$ar,$ag,$ab]  = $this->pick($username, 1, $accentColors);
        $eyeType        = $this->pick($username, 2, $eyeTypes);
        $hasHelmet      = $this->hash($username, 3, 0, 1);
        $hasAntenna     = $this->hash($username, 4, 0, 1);

        $bg    = $this->color($img, $br, $bg2, $bb);
        $acc   = $this->color($img, $ar, $ag, $ab);
        $accL  = $this->color($img, min(255,(int)($ar*1.4)), min(255,(int)($ag*1.4)), min(255,(int)($ab*1.4)));
        $white = $this->color($img, 255, 255, 255);
        $black = $this->color($img, 5, 5, 18);
        $star  = $this->color($img, 255, 238, 170);

        $this->rect($img, 0, 0, 200, 200, $bg);

        // Stars in background
        $starPositions = [[18,22],[160,18],[190,80],[10,140],[180,160],[80,10],[140,190]];
        foreach ($starPositions as $i => [$sx,$sy]) {
            $sz = $this->hash($username, 50+$i, 2, 6);
            $this->ellipse($img, $sx, $sy, $sz*2, $sz*2, $star);
        }

        if ($hasAntenna) {
            $this->rect($img, 92, 0, 108, 38, $acc);
            $this->ellipse($img, 100, 8, 24, 24, $accL);
            $this->ellipse($img, 100, 8, 12, 12, $white);
        }

        if ($hasHelmet) {
            $this->rect($img, 20, 10, 180, 56, $this->color($img, (int)($br*0.6), (int)($bg2*0.6), (int)($bb*0.6)));
            $this->rect($img, 24, 14, 176, 52, $this->color($img, min(255,$ar/2), min(255,$ag/2), min(255,$ab/2)));
            $this->rect($img, 28, 18, 172, 48, $acc);
        }

        switch ($eyeType) {
            case 0: // big alien eyes
                $this->ellipse($img, 66, 100, 56, 64, $white);
                $this->ellipse($img, 134, 100, 56, 64, $white);
                $this->ellipse($img, 66, 101, 38, 46, $acc);
                $this->ellipse($img, 134, 101, 38, 46, $acc);
                $this->ellipse($img, 66, 102, 18, 22, $black);
                $this->ellipse($img, 134, 102, 18, 22, $black);
                $this->ellipse($img, 58, 93, 12, 12, $white);
                $this->ellipse($img, 126, 93, 12, 12, $white);
                break;
            case 1: // four eyes
                foreach ([46,74,126,154] as $ex) {
                    $this->ellipse($img, $ex, 100, 36, 44, $white);
                    $this->ellipse($img, $ex, 101, 24, 30, $acc);
                    $this->ellipse($img, $ex, 102, 12, 14, $black);
                    $this->ellipse($img, $ex-5, 94, 8, 8, $white);
                }
                break;
            case 2: // star eyes
                $this->polygon($img, [66,78, 70,92, 84,92, 73,101, 77,115, 66,106, 55,115, 59,101, 48,92, 62,92], $acc);
                $this->polygon($img, [134,78, 138,92, 152,92, 141,101, 145,115, 134,106, 123,115, 127,101, 116,92, 130,92], $acc);
                $this->ellipse($img, 66, 100, 14, 14, $white);
                $this->ellipse($img, 134, 100, 14, 14, $white);
                break;
            case 3: // squint
                $this->ellipse($img, 66, 100, 56, 32, $white);
                $this->ellipse($img, 134, 100, 56, 32, $white);
                $this->ellipse($img, 66, 100, 38, 18, $acc);
                $this->ellipse($img, 134, 100, 38, 18, $acc);
                $this->ellipse($img, 66, 100, 16, 10, $black);
                $this->ellipse($img, 134, 100, 16, 10, $black);
                $this->ellipse($img, 58, 95, 8, 8, $white);
                $this->ellipse($img, 126, 95, 8, 8, $white);
                break;
            case 4: // cyclops
                $this->ellipse($img, 100, 100, 80, 88, $white);
                $this->ellipse($img, 100, 101, 56, 64, $acc);
                $this->ellipse($img, 100, 102, 30, 34, $black);
                $this->ellipse($img, 84, 88, 18, 18, $white);
                break;
        }

        // Mouth
        imagesetthickness($img, 4);
        imagearc($img, 100, 152, 70, 32, 15, 165, $acc);
        imagesetthickness($img, 1);

        return $img;
    }
}
