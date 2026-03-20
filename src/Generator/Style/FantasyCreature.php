<?php

namespace Resofire\Avatars\Generator\Style;

class FantasyCreature extends AbstractStyle
{
    public function key(): string { return 'fantasy-creature'; }
    public function name(): string { return 'Fantasy Creature'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        $types = [
            ['bg'=>[90,58,26],  'eye'=>[204,136,0],  'top'=>'fur'],
            ['bg'=>[200,192,216],'eye'=>[204,0,0],    'top'=>'widow'],
            ['bg'=>[138,0,0],   'eye'=>[255,204,0],  'top'=>'horns'],
            ['bg'=>[0,68,136],  'eye'=>[0,136,255],  'top'=>'ripple'],
            ['bg'=>[26,90,26],  'eye'=>[51,170,51],  'top'=>'leaves'],
            ['bg'=>[10,10,20],  'eye'=>[170,68,255], 'top'=>'shadow'],
        ];

        $type = $this->pick($username, 0, $types);
        [$br,$bg2,$bb] = $type['bg'];
        [$er,$eg,$eb]  = $type['eye'];
        $topType        = $type['top'];
        $fangCount      = $this->hash($username, 3, 2, 4);
        $hasScar        = $this->hash($username, 4, 0, 1);

        $bg    = $this->color($img, $br, $bg2, $bb);
        $bgD   = $this->color($img, (int)($br*0.6), (int)($bg2*0.6), (int)($bb*0.6));
        $eye   = $this->color($img, $er, $eg, $eb);
        $eyeD  = $this->color($img, (int)($er*0.3), (int)($eg*0.3), (int)($eb*0.3));
        $white = $this->color($img, 255, 255, 255);
        $black = $this->color($img, 8, 8, 10);
        $fang  = $this->color($img, 230, 220, 200);

        $this->rect($img, 0, 0, 200, 200, $bg);

        // Top decoration by creature type
        switch ($topType) {
            case 'fur': // werewolf ear humps
                $this->ellipse($img, 30, 20, 50, 70, $bgD);
                $this->ellipse($img, 170, 20, 50, 70, $bgD);
                $this->ellipse($img, 100, 10, 80, 50, $bgD);
                break;
            case 'widow': // vampire widow peak
                $this->polygon($img, [100,10, 80,36, 120,36], $bgD);
                $this->rect($img, 0, 0, 200, 36, $bgD);
                break;
            case 'horns': // demon horns
                $this->polygon($img, [64,38, 44,0, 84,30], $bgD);
                $this->polygon($img, [136,38, 156,0, 116,30], $bgD);
                break;
            case 'ripple': // water elementale
                for ($i = 0; $i < 3; $i++) {
                    $this->ellipse($img, 100, 8, 170-$i*30, 12+$i*6, $this->color($img, min(255,$br+30), min(255,$bg2+30), min(255,$bb+30)));
                }
                break;
            case 'leaves': // forest spirit
                foreach ([24,46,70,100,130,154,176] as $lx) {
                    $h = $this->hash($username, 60+$lx, 20, 40);
                    $this->ellipse($img, $lx, $h/2, 20, $h, $bgD);
                }
                break;
            case 'shadow': // barely visible dark mass
                $this->ellipse($img, 100, 20, 120, 60, $bgD);
                $this->ellipse($img, 50, 30, 60, 40, $bgD);
                $this->ellipse($img, 150, 30, 60, 40, $bgD);
                break;
        }

        // Eyes
        $this->ellipse($img, 66, 94, 52, 56, $white);
        $this->ellipse($img, 134, 94, 52, 56, $white);
        $this->ellipse($img, 66, 95, 36, 40, $eye);
        $this->ellipse($img, 134, 95, 36, 40, $eye);

        // Pupil shape varies by type
        if ($topType === 'horns' || $topType === 'widow') {
            // slit pupils
            $this->ellipse($img, 66, 95, 10, 28, $eyeD);
            $this->ellipse($img, 134, 95, 10, 28, $eyeD);
        } else {
            $this->ellipse($img, 66, 96, 18, 20, $eyeD);
            $this->ellipse($img, 134, 96, 18, 20, $eyeD);
        }

        $this->ellipse($img, 58, 87, 10, 10, $white);
        $this->ellipse($img, 126, 87, 10, 10, $white);

        // Snout/mouth area
        $this->ellipse($img, 100, 138, 80, 50, $bgD);

        // Fangs
        $spacing = 60 / ($fangCount + 1);
        for ($i = 0; $i < $fangCount; $i++) {
            $fx = (int)(70 + ($i + 1) * $spacing);
            $this->polygon($img, [$fx-8,132, $fx,158, $fx+8,132], $fang);
        }

        if ($hasScar) {
            $scar = $this->color($img, max(0,$br-30), max(0,$bg2-30), max(0,$bb-30));
            $this->rect($img, 58, 70, 65, 130, $scar);
        }

        return $img;
    }
}
