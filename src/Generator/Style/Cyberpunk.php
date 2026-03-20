<?php

namespace Resofire\Avatars\Generator\Style;

class Cyberpunk extends AbstractStyle
{
    public function key(): string { return 'cyberpunk'; }
    public function name(): string { return 'Cyberpunk'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        $skinTones   = [[30,60,30],[20,50,40],[25,55,35],[40,70,40],[15,45,25]];
        $mohawkColors= [[0,255,136],[0,200,255],[255,50,50],[200,0,255],[255,200,0]];
        $eyeColors   = [[0,204,68],[0,170,255],[255,100,0],[180,0,255],[0,255,200]];
        $implantColors=[[0,170,255],[0,255,136],[255,50,150],[200,100,255],[255,180,0]];

        [$sr,$sg,$sb]   = $this->pick($username, 0, $skinTones);
        [$mr,$mg,$mb]   = $this->pick($username, 1, $mohawkColors);
        [$er,$eg,$eb]   = $this->pick($username, 2, $eyeColors);
        [$ir,$ig,$ib]   = $this->pick($username, 3, $implantColors);
        $scarSide        = $this->hash($username, 4, 0, 1); // 0=left, 1=right
        $hasEarring      = $this->hash($username, 5, 0, 1);

        $bg       = $this->color($img, 10, 10, 26);
        $face     = $this->color($img, $sr, $sg, $sb);
        $faceD    = $this->color($img, (int)($sr*0.75), (int)($sg*0.75), (int)($sb*0.75));
        $mohawk   = $this->color($img, $mr, $mg, $mb);
        $eyeC     = $this->color($img, $er, $eg, $eb);
        $implant  = $this->color($img, $ir, $ig, $ib);
        $implantD = $this->color($img, (int)($ir*0.5), (int)($ig*0.5), (int)($ib*0.5));
        $black    = $this->color($img, 0, 0, 0);
        $white    = $this->color($img, 255, 255, 255);
        $circuit  = $this->color($img, $mr, $mg, $mb);
        $collar   = $this->color($img, 17, 17, 17);
        $scarC    = $this->color($img, 90, 42, 42);
        $smirkC   = $this->color($img, $mr, $mg, $mb);

        // Background
        $this->rect($img, 0, 0, 200, 200, $bg);

        // Subtle circuit lines at bottom
        $this->rect($img, 0, 160, 70, 162, $circuit);
        imagesetpixel($img, 70, 161, $circuit);
        $this->rect($img, 130, 160, 200, 162, $circuit);

        // Collar / body
        $this->rect($img, 40, 168, 160, 200, $collar);
        $this->rect($img, 40, 168, 160, 170, $circuit);

        // Neck
        $this->rect($img, 86, 152, 114, 172, $faceD);

        // Face ellipse approximated
        $this->ellipse($img, 100, 106, 112, 128, $face);

        // Ears
        $this->ellipse($img, 44, 106, 20, 32, $face);
        $this->ellipse($img, 156, 106, 20, 32, $face);

        // Earring
        if ($hasEarring) {
            $this->ellipse($img, 36, 116, 8, 8, $implant);
        }

        // Mohawk
        $this->rect($img, 92, 34, 108, 74, $mohawk);
        $this->ellipse($img, 100, 36, 32, 16, $mohawk);

        // Left eye - natural
        $this->ellipse($img, 76, 100, 34, 26, $black);
        $this->ellipse($img, 76, 100, 22, 20, $eyeC);
        $this->ellipse($img, 76, 100, 10, 10, $black);
        $this->rect($img, 70, 94, 74, 98, $white);

        // Right eye - cyber implant (rectangles)
        $this->rect($img, 108, 92, 142, 110, $implantD);
        $this->rect($img, 110, 94, 140, 108, $black);
        $this->rect($img, 110, 97, 140, 99, $implant);
        $this->rect($img, 110, 102, 140, 104, $implant);
        $this->rect($img, 110, 107, 140, 109, $implant);
        $this->ellipse($img, 124, 101, 14, 14, $implant);
        $this->ellipse($img, 124, 101, 8, 8, $implantD);
        // implant wire
        $this->rect($img, 140, 94, 152, 96, $implant);
        $this->ellipse($img, 154, 94, 6, 6, $implant);

        // Brows
        $this->rect($img, 60, 86, 90, 90, $faceD);
        $this->rect($img, 110, 86, 140, 90, $faceD);

        // Nose
        $this->rect($img, 94, 112, 106, 120, $faceD);

        // Smirk
        $this->rect($img, 74, 130, 118, 134, $smirkC);
        $this->rect($img, 74, 130, 94, 134, $faceD); // cover left half darker

        // Scar
        if ($scarSide === 0) {
            $this->rect($img, 72, 98, 75, 118, $scarC);
        } else {
            $this->rect($img, 125, 98, 128, 118, $scarC);
        }

        return $img;
    }
}
