<?php

namespace Resofire\Avatars\Generator\Style;

class ScifiAndroid extends AbstractStyle
{
    public function key(): string { return 'scifi-android'; }
    public function name(): string { return 'Sci-Fi Android'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        $bodyColors  = [[42,58,74],[30,50,70],[50,42,70],[42,70,58],[60,50,40]];
        $accentColors= [[68,136,255],[0,255,136],[255,180,0],[200,100,255],[0,200,255]];
        $statusColors= [[0,255,136],[255,80,80],[0,200,255],[255,200,0],[180,100,255]];

        [$br,$bg2,$bb]  = $this->pick($username, 0, $bodyColors);
        [$ar,$ag,$ab]   = $this->pick($username, 1, $accentColors);
        [$str,$stg,$stb]= $this->pick($username, 2, $statusColors);
        $scanLines       = $this->hash($username, 3, 2, 4);
        $hasAntenna      = $this->hash($username, 4, 0, 1);

        $bg      = $this->color($img, 13, 21, 32);
        $body    = $this->color($img, $br, $bg2, $bb);
        $bodyD   = $this->color($img, (int)($br*0.75), (int)($bg2*0.75), (int)($bb*0.75));
        $bodyL   = $this->color($img, min(255,(int)($br*1.2)), min(255,(int)($bg2*1.2)), min(255,(int)($bb*1.2)));
        $accent  = $this->color($img, $ar, $ag, $ab);
        $accentD = $this->color($img, (int)($ar*0.4), (int)($ag*0.4), (int)($ab*0.4));
        $status  = $this->color($img, $str, $stg, $stb);
        $black   = $this->color($img, 0, 0, 0);
        $panel   = $this->color($img, 37, 53, 68);

        // Background
        $this->rect($img, 0, 0, 200, 200, $bg);

        // Chest panel
        $this->rect($img, 30, 164, 170, 200, $bodyD);
        $this->ellipse($img, 68, 182, 18, 18, $black);
        $this->ellipse($img, 68, 182, 10, 10, $status);
        $this->rect($img, 84, 178, 140, 184, $panel);
        $this->rect($img, 84, 178, 116, 184, $accent);

        // Neck
        $this->rect($img, 84, 152, 116, 168, $body);
        $this->rect($img, 88, 156, 112, 160, $accent);

        // Head - rounded rect
        $this->roundRect($img, 52, 42, 96, 114, 12, $body);
        $this->roundRect($img, 54, 44, 92, 110, 10, $bodyD);

        // Forehead panel
        $this->rect($img, 60, 48, 140, 68, $panel);
        $this->rect($img, 64, 52, 84, 62, $bodyL);
        $this->rect($img, 116, 52, 136, 62, $bodyL);
        $this->ellipse($img, 100, 58, 10, 10, $status);

        // Antenna
        if ($hasAntenna) {
            $this->rect($img, 97, 30, 103, 44, $accent);
            $this->ellipse($img, 100, 28, 12, 12, $accent);
            $this->ellipse($img, 100, 28, 6, 6, $status);
        }

        // Eye panels
        $this->rect($img, 58, 72, 94, 94, $black);
        $this->rect($img, 106, 72, 142, 94, $black);
        $this->rect($img, 60, 74, 92, 92, $accentD);
        $this->rect($img, 108, 74, 140, 92, $accentD);

        // Scan lines in eyes
        for ($i = 0; $i < $scanLines; $i++) {
            $y = 78 + ($i * (int)(14 / $scanLines));
            $this->rect($img, 60, $y, 92, $y + 2, $accent);
            $this->rect($img, 108, $y, 140, $y + 2, $accent);
        }

        // Pupils
        $this->ellipse($img, 76, 83, 14, 14, $accent);
        $this->ellipse($img, 124, 83, 14, 14, $accent);

        // Nose panel
        $this->rect($img, 90, 100, 110, 114, $panel);
        $this->rect($img, 92, 102, 108, 112, $bodyL);

        // Mouth grille
        $this->rect($img, 62, 122, 138, 138, $black);
        $this->rect($img, 64, 124, 136, 126, $accent);
        $this->rect($img, 64, 130, 136, 132, $accent);
        $this->rect($img, 64, 136, 136, 138, $accent);

        // Ear panels
        $this->rect($img, 42, 74, 54, 102, $panel);
        $this->rect($img, 146, 74, 158, 102, $panel);
        $this->rect($img, 44, 80, 52, 92, $accent);
        $this->rect($img, 148, 80, 156, 92, $accent);

        return $img;
    }
}
