<?php

namespace Resofire\Avatars\Generator\Style;

class Fantasy extends AbstractStyle
{
    public function key(): string { return 'fantasy'; }
    public function name(): string { return 'Fantasy'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        $bgColors  = [[26,74,42],[10,26,90],[90,10,10],[122,90,0],[58,26,90],[10,58,42]];
        $eyeColors = [[0,204,68],[34,85,238],[204,34,0],[170,136,0],[136,68,204],[0,170,102]];
        $gemColors = [[68,170,255],[255,80,80],[80,220,80],[255,200,0],[200,100,255],[0,220,180]];
        $topTypes  = [0,1,2,3,4,5]; // leaves, gem, horns, crown, ripple, none+star

        [$br,$bg2,$bb] = $this->pick($username, 0, $bgColors);
        [$er,$eg,$eb]  = $this->pick($username, 1, $eyeColors);
        [$gr,$gg,$gb]  = $this->pick($username, 2, $gemColors);
        $topType        = $this->pick($username, 3, $topTypes);
        $eyePupil       = $this->pick($username, 4, [0,1,2]); // round, slit, star

        $bg   = $this->color($img, $br, $bg2, $bb);
        $eye  = $this->color($img, $er, $eg, $eb);
        $eyeD = $this->color($img, (int)($er*0.3), (int)($eg*0.3), (int)($eb*0.3));
        $gem  = $this->color($img, $gr, $gg, $gb);
        $white= $this->color($img, 255, 255, 255);
        $black= $this->color($img, 5, 5, 15);
        $topC = $this->color($img, min(255,$br+40), min(255,$bg2+40), min(255,$bb+40));

        $this->rect($img, 0, 0, 200, 200, $bg);

        // Top decoration
        switch ($topType) {
            case 0: // leaves
                foreach ([30,50,70,100,130,150,170] as $lx) {
                    $h = $this->hash($username, 10+$lx, 12, 28);
                    $this->ellipse($img, $lx, 4, 18, $h*2, $topC);
                }
                break;
            case 1: // gem circlet
                $this->rect($img, 30, 20, 170, 26, $topC);
                $this->ellipse($img, 100, 14, 26, 26, $gem);
                $this->ellipse($img, 100, 14, 16, 16, $this->color($img, min(255,$gr+60), min(255,$gg+60), min(255,$gb+60)));
                break;
            case 2: // horns
                $this->polygon($img, [70,30, 54,0, 86,24], $topC);
                $this->polygon($img, [130,30, 146,0, 114,24], $topC);
                break;
            case 3: // crown points
                foreach ([50,75,100,125,150] as $cx) {
                    $this->polygon($img, [$cx-10,30, $cx,8, $cx+10,30], $gem);
                }
                $this->rect($img, 40, 28, 160, 38, $topC);
                break;
            case 4: // water ripples
                for ($i = 0; $i < 3; $i++) {
                    $this->ellipse($img, 100, 10, 160-$i*30, 8+$i*4, $topC);
                }
                break;
            case 5: // star
                $this->polygon($img, [100,6, 106,22, 122,22, 110,32, 114,48, 100,38, 86,48, 90,32, 78,22, 94,22], $gem);
                break;
        }

        // Eyes
        $this->ellipse($img, 66, 100, 52, 60, $white);
        $this->ellipse($img, 134, 100, 52, 60, $white);
        $this->ellipse($img, 66, 101, 36, 44, $eye);
        $this->ellipse($img, 134, 101, 36, 44, $eye);

        switch ($eyePupil) {
            case 0: // round
                $this->ellipse($img, 66, 102, 18, 22, $eyeD);
                $this->ellipse($img, 134, 102, 18, 22, $eyeD);
                break;
            case 1: // slit
                $this->ellipse($img, 66, 102, 10, 28, $eyeD);
                $this->ellipse($img, 134, 102, 10, 28, $eyeD);
                break;
            case 2: // star pupils
                $this->polygon($img, [66,88, 69,98, 78,98, 71,104, 74,114, 66,108, 58,114, 61,104, 54,98, 63,98], $eyeD);
                $this->polygon($img, [134,88, 137,98, 146,98, 139,104, 142,114, 134,108, 126,114, 129,104, 122,98, 131,98], $eyeD);
                break;
        }

        // Eye shine
        $this->ellipse($img, 58, 93, 10, 10, $white);
        $this->ellipse($img, 126, 93, 10, 10, $white);

        // Blush
        $blush = $this->color($img, min(255,$br+60), (int)($bg2*0.6), (int)($bb*0.6));
        $this->ellipse($img, 38, 116, 28, 14, $blush);
        $this->ellipse($img, 162, 116, 28, 14, $blush);

        // Mouth
        imagesetthickness($img, 4);
        imagearc($img, 100, 138, 68, 40, 10, 170, $eye);
        imagesetthickness($img, 1);

        return $img;
    }
}
