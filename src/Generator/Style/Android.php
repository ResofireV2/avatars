<?php

namespace Resofire\Avatars\Generator\Style;

class Android extends AbstractStyle
{
    public function key(): string { return 'android'; }
    public function name(): string { return 'Android'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        $bgColors     = [[42,58,74],[37,37,42],[26,58,58],[58,42,16],[58,58,68],[34,34,16]];
        $accentColors = [[68,136,255],[255,51,51],[0,204,170],[204,119,0],[170,170,255],[255,102,0]];
        $statusColors = [[0,255,136],[255,80,80],[0,200,255],[255,200,0],[180,100,255],[255,150,0]];
        $eyeTypes     = [0,1,2,3]; // scan, radar, segment, happy

        [$br,$bg2,$bb] = $this->pick($username, 0, $bgColors);
        [$ar,$ag,$ab]  = $this->pick($username, 1, $accentColors);
        [$sr,$sg,$sb]  = $this->pick($username, 2, $statusColors);
        $eyeType        = $this->pick($username, 3, $eyeTypes);
        $hasAntenna     = $this->hash($username, 4, 0, 1);

        $bg     = $this->color($img, $br, $bg2, $bb);
        $acc    = $this->color($img, $ar, $ag, $ab);
        $accD   = $this->color($img, (int)($ar*0.35), (int)($ag*0.35), (int)($ab*0.35));
        $status = $this->color($img, $sr, $sg, $sb);
        $panel  = $this->color($img, max(0,$br-20), max(0,$bg2-20), max(0,$bb-20));
        $black  = $this->color($img, 8, 14, 22);

        $this->rect($img, 0, 0, 200, 200, $bg);

        if ($hasAntenna) {
            $this->rect($img, 92, 0, 108, 40, $panel);
            $this->ellipse($img, 100, 6, 22, 22, $status);
            $this->ellipse($img, 100, 6, 12, 12, $this->color($img, min(255,$sr+60), min(255,$sg+60), min(255,$sb+60)));
        }

        // Status light
        $this->ellipse($img, 100, 50, 18, 18, $status);

        // Forehead bar
        $this->rect($img, 30, 58, 170, 74, $panel);

        // Eye panels
        $this->rect($img, 30, 80, 88, 110, $black);
        $this->rect($img, 112, 80, 170, 110, $black);

        switch ($eyeType) {
            case 0: // scan lines
                for ($i = 0; $i < 3; $i++) {
                    $this->rect($img, 32, 82+$i*10, 86, 88+$i*10, $acc);
                }
                $this->ellipse($img, 59, 95, 28, 20, $accD);
                $this->ellipse($img, 59, 95, 16, 12, $acc);
                for ($i = 0; $i < 3; $i++) {
                    $this->rect($img, 114, 82+$i*10, 168, 88+$i*10, $acc);
                }
                $this->ellipse($img, 141, 95, 28, 20, $accD);
                $this->ellipse($img, 141, 95, 16, 12, $acc);
                break;
            case 1: // radar single eye each
                $this->ellipse($img, 59, 95, 44, 22, $accD);
                $this->ellipse($img, 59, 95, 24, 24, $acc);
                $this->ellipse($img, 59, 95, 12, 12, $accD);
                $this->ellipse($img, 141, 95, 44, 22, $accD);
                $this->ellipse($img, 141, 95, 24, 24, $acc);
                $this->ellipse($img, 141, 95, 12, 12, $accD);
                break;
            case 2: // segmented triple
                foreach ([34,54,70] as $ex) {
                    $this->rect($img, $ex, 82, $ex+16, 108, $panel);
                    $this->rect($img, $ex+2, 84, $ex+14, 106, $acc);
                }
                foreach ([116,136,152] as $ex) {
                    $this->rect($img, $ex, 82, $ex+16, 108, $panel);
                    $this->rect($img, $ex+2, 84, $ex+14, 106, $acc);
                }
                break;
            case 3: // happy scan
                $this->rect($img, 32, 84, 86, 94, $acc);
                $this->ellipse($img, 59, 95, 26, 14, $acc);
                $this->rect($img, 114, 84, 168, 94, $acc);
                $this->ellipse($img, 141, 95, 26, 14, $acc);
                break;
        }

        // Mouth grille
        $this->rect($img, 30, 126, 170, 150, $black);
        foreach ([34,54,74,94,114,134,154] as $mx) {
            $this->rect($img, $mx, 128, $mx+16, 148, $panel);
        }
        $this->rect($img, 32, 130, 168, 134, $acc);
        $this->rect($img, 32, 142, 168, 146, $acc);

        return $img;
    }
}
