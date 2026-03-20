<?php

namespace Resofire\Avatars\Generator\Style;

class Glitch extends AbstractStyle
{
    public function key(): string { return 'glitch'; }
    public function name(): string { return 'Glitch'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        // Pastel vaporwave backgrounds
        $bgColors = [
            [221, 102, 170], // hot pink
            [68,  204, 238], // cyan
            [153, 102, 204], // lavender
            [68,  221, 170], // mint
            [255, 170, 204], // peach
            [238, 221,   0], // yellow
        ];

        // Accent / RGB split color pairs
        $accentColors = [
            [255,   0, 136], [0, 255, 204],
            [  0, 102, 170], [0, 238, 255],
            [102,   0, 170], [255,  0, 255],
            [  0, 136,  85], [0, 255, 170],
            [255,  68, 136], [0, 204, 255],
            [170, 136,   0], [255, 238,   0],
        ];

        $eyeTypes   = [0, 1, 2, 3]; // RGB-split, X, star, normal-glitched
        $mouthTypes = [0, 1, 2];    // offset bar, waveform, broken smile

        [$br, $bg2, $bb]  = $this->pick($username, 0, $bgColors);
        [$ar, $ag, $ab]   = $this->pick($username, 1, $accentColors);
        $eyeType           = $this->pick($username, 2, $eyeTypes);
        $mouthType         = $this->pick($username, 3, $mouthTypes);
        $scanlineCount     = $this->hash($username, 4, 3, 7);
        $glitchShift       = $this->hash($username, 5, 4, 12);

        $bg     = $this->color($img, $br, $bg2, $bb);
        $acc    = $this->color($img, $ar, $ag, $ab);
        $dark   = $this->color($img, (int)($br * 0.55), (int)($bg2 * 0.55), (int)($bb * 0.55));
        $white  = $this->color($img, 255, 255, 255);
        $black  = $this->color($img, 8, 8, 18);
        $red    = $this->color($img, 255, 0, 68);
        $grn    = $this->color($img, 0, 255, 136);
        $blu    = $this->color($img, 0, 136, 255);

        $this->rect($img, 0, 0, 200, 200, $bg);

        // Horizontal scanlines across entire canvas
        $gap = (int)(200 / ($scanlineCount + 1));
        for ($i = 1; $i <= $scanlineCount; $i++) {
            $y = $i * $gap;
            $this->rect($img, 0, $y, 200, $y + 1, $dark);
        }

        // Glitch horizontal slice offsets — random-looking but deterministic
        $sliceY = $this->hash($username, 6, 60, 100);
        $this->rect($img, $glitchShift, $sliceY, 200, $sliceY + 8, $bg);
        // redraw bg strip offset to simulate data corruption
        $sliceY2 = $this->hash($username, 7, 110, 150);
        $this->rect($img, 0, $sliceY2, 200 - $glitchShift, $sliceY2 + 5, $bg);

        // Eyes
        switch ($eyeType) {
            case 0: // RGB split — three offset ellipses per eye
                $this->ellipse($img, 66 - 4,  94, 52, 52, $red);
                $this->ellipse($img, 66,       94, 52, 52, $grn);
                $this->ellipse($img, 66 + 4,  94, 52, 52, $blu);
                $this->ellipse($img, 66,       94, 36, 36, $white);
                $this->ellipse($img, 66,       95, 20, 22, $acc);
                $this->ellipse($img, 66,       96, 10, 10, $black);

                $this->ellipse($img, 134 - 4, 94, 52, 52, $red);
                $this->ellipse($img, 134,      94, 52, 52, $grn);
                $this->ellipse($img, 134 + 4, 94, 52, 52, $blu);
                $this->ellipse($img, 134,      94, 36, 36, $white);
                $this->ellipse($img, 134,      95, 20, 22, $acc);
                $this->ellipse($img, 134,      96, 10, 10, $black);
                break;

            case 1: // X eyes with RGB tint
                $this->ellipse($img, 66,  94, 52, 52, $white);
                $this->ellipse($img, 134, 94, 52, 52, $white);
                imagesetthickness($img, 8);
                imageline($img, 42, 70, 90, 118, $acc);
                imageline($img, 90, 70, 42, 118, $acc);
                imageline($img, 110, 70, 158, 118, $acc);
                imageline($img, 158, 70, 110, 118, $acc);
                imagesetthickness($img, 1);
                // RGB fringe
                $this->ellipse($img, 66 - $glitchShift/2,  94, 54, 54, $red);
                $this->ellipse($img, 134 + $glitchShift/2, 94, 54, 54, $blu);
                break;

            case 2: // star pupils
                $this->ellipse($img, 66,  94, 52, 52, $white);
                $this->ellipse($img, 134, 94, 52, 52, $white);
                $this->ellipse($img, 66,  95, 36, 36, $acc);
                $this->ellipse($img, 134, 95, 36, 36, $acc);
                // stars via polygon
                $this->polygon($img, [66,74, 70,88, 84,88, 73,97, 77,111, 66,102, 55,111, 59,97, 48,88, 62,88], $white);
                $this->polygon($img, [134,74, 138,88, 152,88, 141,97, 145,111, 134,102, 123,111, 127,97, 116,88, 130,88], $white);
                // RGB offset copies
                $this->ellipse($img, 66 - 3,  94, 54, 54, $red);
                $this->ellipse($img, 134 + 3, 94, 54, 54, $blu);
                break;

            case 3: // normal eyes with glitch corruption blocks
                $this->ellipse($img, 66,  94, 52, 52, $white);
                $this->ellipse($img, 134, 94, 52, 52, $white);
                $this->ellipse($img, 66,  95, 36, 36, $acc);
                $this->ellipse($img, 134, 95, 36, 36, $acc);
                $this->ellipse($img, 66,  96, 18, 18, $black);
                $this->ellipse($img, 134, 96, 18, 18, $black);
                $this->ellipse($img, 58,  88, 10, 10, $white);
                $this->ellipse($img, 126, 88, 10, 10, $white);
                // glitch corruption blocks over eyes
                $bx = $this->hash($username, 8, 36, 56);
                $this->rect($img, $bx, 84, $bx + $glitchShift * 2, 90, $acc);
                $this->rect($img, 200 - $bx - $glitchShift * 2, 98, 200 - $bx, 104, $dark);
                break;
        }

        // Eye shine on cases 3
        if ($eyeType === 3) {
            $this->ellipse($img, 58,  88, 10, 10, $white);
            $this->ellipse($img, 126, 88, 10, 10, $white);
        }

        // Mouth
        switch ($mouthType) {
            case 0: // offset RGB bars
                $this->rect($img, 44 - $glitchShift, 138, 156 - $glitchShift, 146, $red);
                $this->rect($img, 44,                 142, 156,                 150, $grn);
                $this->rect($img, 44 + $glitchShift, 146, 156 + $glitchShift, 154, $blu);
                $this->rect($img, 44,                 140, 156,                 152, $white);
                break;

            case 1: // waveform
                $pts = [40,148, 52,138, 64,158, 76,138, 88,158, 100,138, 112,158, 124,138, 136,158, 148,138, 160,148];
                imagesetthickness($img, 4);
                for ($i = 0; $i < count($pts) - 2; $i += 2) {
                    imageline($img, $pts[$i], $pts[$i+1], $pts[$i+2], $pts[$i+3], $acc);
                }
                // RGB offset
                imagesetthickness($img, 2);
                for ($i = 0; $i < count($pts) - 2; $i += 2) {
                    imageline($img, $pts[$i] - 3, $pts[$i+1], $pts[$i+2] - 3, $pts[$i+3], $red);
                    imageline($img, $pts[$i] + 3, $pts[$i+1], $pts[$i+2] + 3, $pts[$i+3], $blu);
                }
                imagesetthickness($img, 1);
                break;

            case 2: // broken smile with glitch gap
                imagesetthickness($img, 5);
                imagearc($img, 100, 152, 90, 44, 15, 85, $acc);
                imagearc($img, 100, 152, 90, 44, 95, 165, $acc);
                // RGB fringe
                imagesetthickness($img, 2);
                imagearc($img, 100 - 3, 152, 92, 44, 10, 170, $red);
                imagearc($img, 100 + 3, 152, 92, 44, 10, 170, $blu);
                imagesetthickness($img, 1);
                break;
        }

        return $img;
    }
}
