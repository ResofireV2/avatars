<?php

namespace Resofire\Avatars\Generator\Style;

class Pirate extends AbstractStyle
{
    public function key(): string { return 'pirate'; }
    public function name(): string { return 'Pirate'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        $bgColors      = [[10,42,90],[17,17,17],[26,90,74],[170,136,0],[74,74,90],[122,74,26]];
        $bandanaColors = [[204,34,34],[17,17,17],[10,42,90],[136,0,136],[34,102,34],[204,136,0]];
        $eyeColors     = [[34,102,255],[255,255,255],[68,204,136],[255,204,0],[136,136,204],[255,136,34]];
        $fangCount     = [0,1,2]; // teeth shown

        [$br,$bg2,$bb]  = $this->pick($username, 0, $bgColors);
        [$bdr,$bdg,$bdb]= $this->pick($username, 1, $bandanaColors);
        [$er,$eg,$eb]   = $this->pick($username, 2, $eyeColors);
        $fangs           = $this->pick($username, 3, $fangCount);
        $eyepatchSide    = $this->hash($username, 4, 0, 1); // 0=left, 1=right
        $hasScar         = $this->hash($username, 5, 0, 1);
        $topType         = $this->pick($username, 6, [0,1,2]); // skull, coins, plain bandana

        $bg      = $this->color($img, $br, $bg2, $bb);
        $bandana = $this->color($img, $bdr, $bdg, $bdb);
        $bandanaD= $this->color($img, (int)($bdr*0.7), (int)($bdg*0.7), (int)($bdb*0.7));
        $eye     = $this->color($img, $er, $eg, $eb);
        $eyeD    = $this->color($img, (int)($er*0.3), (int)($eg*0.3), (int)($eb*0.3));
        $patch   = $this->color($img, 8, 8, 8);
        $patchRim= $this->color($img, 60, 60, 60);
        $white   = $this->color($img, 255, 255, 255);
        $black   = $this->color($img, 8, 8, 8);
        $skull   = $this->color($img, 220, 215, 195);
        $gold    = $this->color($img, 204, 170, 0);

        $this->rect($img, 0, 0, 200, 200, $bg);

        // Bandana top
        $this->rect($img, 0, 0, 200, 50, $bandana);
        $this->rect($img, 0, 48, 200, 56, $bandanaD);

        // Top decoration
        switch ($topType) {
            case 0: // skull on bandana
                $this->ellipse($img, 100, 24, 44, 44, $skull);
                $this->ellipse($img, 88, 22, 10, 12, $black);
                $this->ellipse($img, 112, 22, 10, 12, $black);
                $this->rect($img, 88, 32, 94, 38, $black);
                $this->rect($img, 97, 32, 103, 38, $black);
                $this->rect($img, 106, 32, 112, 38, $black);
                break;
            case 1: // gold coins
                foreach ([50,80,100,120,150] as $cx) {
                    $this->ellipse($img, $cx, 24, 22, 22, $gold);
                    $this->ellipse($img, $cx, 24, 14, 14, $this->color($img, 255, 204, 0));
                }
                break;
            case 2: // plain knot detail
                $this->ellipse($img, 170, 28, 30, 30, $bandanaD);
                $this->rect($img, 166, 38, 200, 48, $bandana);
                break;
        }

        // Eyes — one normal, one eyepatch
        $openEyeX  = $eyepatchSide === 0 ? 134 : 66;
        $patchEyeX = $eyepatchSide === 0 ? 66  : 134;

        // Open eye
        $this->ellipse($img, $openEyeX, 100, 52, 52, $white);
        $this->ellipse($img, $openEyeX, 101, 36, 36, $eye);
        $this->ellipse($img, $openEyeX, 102, 18, 18, $eyeD);
        $this->ellipse($img, $openEyeX-8, 93, 12, 12, $white);

        // Eyepatch
        $this->ellipse($img, $patchEyeX, 100, 52, 48, $patch);
        $this->ellipse($img, $patchEyeX, 100, 44, 40, $patchRim);
        $this->ellipse($img, $patchEyeX, 100, 36, 32, $patch);
        // Strap
        $this->rect($img, $patchEyeX-26, 98, $patchEyeX+26, 104, $patchRim);

        // Scar
        if ($hasScar) {
            $scar = $this->color($img, max(0,$br-20), max(0,$bg2-20), max(0,$bb-20));
            $this->rect($img, $patchEyeX-4, 72, $patchEyeX+2, 130, $scar);
        }

        // Smirk mouth
        $this->ellipse($img, 100, 152, 80, 28, $this->color($img, (int)($br*0.5), (int)($bg2*0.5), (int)($bb*0.5)));

        if ($fangs > 0) {
            $fangPositions = [[82,148],[100,148],[118,148]];
            for ($i = 0; $i < $fangs && $i < count($fangPositions); $i++) {
                [$fx,$fy] = $fangPositions[$i];
                $this->polygon($img, [$fx-6,$fy, $fx,($fy+18), $fx+6,$fy], $skull);
            }
        }

        return $img;
    }
}
