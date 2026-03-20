<?php

namespace Resofire\Avatars\Generator\Style;

class Cyberpunk extends AbstractStyle
{
    public function key(): string { return 'cyberpunk'; }
    public function name(): string { return 'Cyberpunk'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        $bgColors     = [[204,0,136],[0,51,0],[0,17,68],[51,0,85],[0,34,51],[51,34,0]];
        $accentColors = [[255,0,204],[0,255,68],[0,136,255],[170,0,255],[0,204,204],[255,238,0]];
        $eyeTypes     = [0,1,2,3]; // visor, slit, implant, scanline
        $mouthTypes   = [0,1,2];   // waveform, bar, circuit

        [$br,$bg2,$bb] = $this->pick($username, 0, $bgColors);
        [$ar,$ag,$ab]  = $this->pick($username, 1, $accentColors);
        $eyeType        = $this->pick($username, 2, $eyeTypes);
        $mouthType      = $this->pick($username, 3, $mouthTypes);
        $hasScar        = $this->hash($username, 4, 0, 1);
        $hasMohawk      = $this->hash($username, 5, 0, 1);

        $bg     = $this->color($img, $br, $bg2, $bb);
        $acc    = $this->color($img, $ar, $ag, $ab);
        $accD   = $this->color($img, (int)($ar*0.4), (int)($ag*0.4), (int)($ab*0.4));
        $panel  = $this->color($img, (int)($br*0.5), (int)($bg2*0.5), (int)($bb*0.5));
        $black  = $this->color($img, 5, 5, 15);

        $this->rect($img, 0, 0, 200, 200, $bg);

        if ($hasMohawk) {
            $this->rect($img, 88, 0, 112, 44, $acc);
        }

        // Eye socket panels
        $this->rect($img, 30, 80, 90, 108, $panel);
        $this->rect($img, 110, 80, 170, 108, $panel);

        switch ($eyeType) {
            case 0: // visor bar
                $this->rect($img, 32, 82, 88, 106, $black);
                $this->rect($img, 32, 86, 88, 90, $acc);
                $this->rect($img, 32, 96, 88, 100, $acc);
                $this->ellipse($img, 60, 94, 28, 20, $accD);
                $this->ellipse($img, 60, 94, 16, 12, $acc);
                $this->rect($img, 112, 82, 168, 106, $black);
                $this->rect($img, 112, 86, 168, 90, $acc);
                $this->rect($img, 112, 96, 168, 100, $acc);
                $this->ellipse($img, 140, 94, 28, 20, $accD);
                $this->ellipse($img, 140, 94, 16, 12, $acc);
                break;
            case 1: // slit
                $this->rect($img, 32, 82, 88, 106, $black);
                $this->rect($img, 32, 90, 88, 98, $acc);
                $this->ellipse($img, 60, 94, 24, 8, $acc);
                $this->rect($img, 112, 82, 168, 106, $black);
                $this->rect($img, 112, 90, 168, 98, $acc);
                $this->ellipse($img, 140, 94, 24, 8, $acc);
                break;
            case 2: // implant
                $this->rect($img, 32, 82, 88, 106, $black);
                $this->rect($img, 32, 84, 88, 88, $acc);
                $this->rect($img, 32, 94, 88, 98, $acc);
                $this->rect($img, 32, 104, 88, 108, $acc);
                $this->ellipse($img, 60, 94, 20, 20, $accD);
                $this->ellipse($img, 60, 94, 12, 12, $acc);
                $this->rect($img, 112, 82, 168, 106, $black);
                $this->ellipse($img, 140, 94, 28, 24, $accD);
                $this->ellipse($img, 140, 94, 14, 14, $acc);
                $this->ellipse($img, 140, 94, 6, 6, $black);
                break;
            case 3: // scanline
                $this->rect($img, 32, 82, 88, 106, $black);
                for ($i = 0; $i < 4; $i++) {
                    $this->rect($img, 32, 84+$i*6, 88, 86+$i*6, $acc);
                }
                $this->ellipse($img, 60, 94, 22, 18, $acc);
                $this->rect($img, 112, 82, 168, 106, $black);
                for ($i = 0; $i < 4; $i++) {
                    $this->rect($img, 112, 84+$i*6, 168, 86+$i*6, $acc);
                }
                $this->ellipse($img, 140, 94, 22, 18, $acc);
                break;
        }

        // Mouth panel
        $this->rect($img, 30, 128, 170, 148, $panel);
        $this->rect($img, 32, 130, 168, 146, $black);

        switch ($mouthType) {
            case 0: // waveform
                $pts = [32,138, 44,130, 56,146, 68,130, 80,146, 92,130, 104,146, 116,130, 128,146, 140,130, 152,146, 164,138, 168,138];
                imagesetthickness($img, 3);
                for ($i = 0; $i < count($pts)-2; $i+=2) {
                    imageline($img, $pts[$i], $pts[$i+1], $pts[$i+2], $pts[$i+3], $acc);
                }
                imagesetthickness($img, 1);
                break;
            case 1: // bar segments
                $this->rect($img, 34, 134, 70, 144, $acc);
                $this->rect($img, 74, 134, 100, 144, $acc);
                $this->rect($img, 104, 134, 166, 144, $accD);
                break;
            case 2: // circuit
                foreach ([34,56,78,100,122,144] as $cx) {
                    $this->rect($img, $cx, 134, $cx+16, 144, $acc);
                }
                break;
        }

        if ($hasScar) {
            $scar = $this->color($img, min(255,$ar+60), (int)($ag*0.3), (int)($ab*0.3));
            $this->rect($img, 56, 70, 62, 120, $scar);
        }

        return $img;
    }
}
