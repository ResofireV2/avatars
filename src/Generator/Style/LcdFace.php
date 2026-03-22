<?php

namespace Resofire\Avatars\Generator\Style;

class LcdFace extends AbstractStyle
{
    public function key(): string { return 'lcd-face'; }
    public function name(): string { return 'LCD Face'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        // Dark screen background colors
        $bgColors = [
            [8,  18,  8],   // terminal green
            [8,   8, 24],   // phosphor blue
            [20,  8, 28],   // purple screen
            [24,  6,  6],   // red screen
            [20, 14,  0],   // amber screen
            [0,  18, 18],   // teal screen
            [16, 16, 16],   // white screen / grayscale
            [4,  12, 20],   // midnight blue
        ];

        // Pixel / text color per screen type
        $pixelColors = [
            [0,  255,  68],  // green
            [68, 136, 255],  // blue
            [170, 68, 255],  // purple
            [255,  68,  68], // red
            [255, 170,   0], // amber
            [0,  220, 200],  // cyan
            [220, 220, 220], // light gray
            [68, 170, 255],  // sky blue
        ];

        $screenIdx = $this->hash($username, 0, 0, 7);
        [$br, $bg2, $bb]  = $bgColors[$screenIdx];
        [$pr, $pg2, $pb]  = $pixelColors[$screenIdx];

        $bgC  = $this->color($img, $br,  $bg2,  $bb);
        $px   = $this->color($img, $pr,  $pg2,  $pb);
        $pxD  = $this->color($img, (int)($pr * 0.5), (int)($pg2 * 0.5), (int)($pb * 0.5));
        $scan = $this->color($img, (int)($br + 6), (int)($bg2 + 6), (int)($bb + 6));

        // Fill bg
        $this->rect($img, 0, 0, 200, 200, $bgC);

        // Scanlines across whole canvas
        $scanCount = $this->hash($username, 1, 3, 7);
        $scanGap   = (int)(200 / $scanCount);
        for ($i = 0; $i < $scanCount; $i++) {
            $this->rect($img, 0, $i * $scanGap, 200, $i * $scanGap + 1, $scan);
        }

        // Screen bezel / inner frame
        $this->rect($img, 14, 14, 186, 186, $this->color($img,
            min(255, $br + 12), min(255, $bg2 + 12), min(255, $bb + 12)));
        $this->rect($img, 18, 18, 182, 182, $bgC);

        // Corner dots
        $cornerC = $this->color($img, min(255, $pr / 2), min(255, $pg2 / 2), min(255, $pb / 2));
        $this->ellipse($img,  24,  24, 8, 8, $cornerC);
        $this->ellipse($img, 176,  24, 8, 8, $cornerC);
        $this->ellipse($img,  24, 176, 8, 8, $cornerC);
        $this->ellipse($img, 176, 176, 8, 8, $cornerC);

        // Expression type
        $expression = $this->hash($username, 2, 0, 7);
        $p = 10; // pixel block size

        switch ($expression) {
            case 0: // ^_^ happy
                // left eye ^
                $this->rect($img, 50, 80, 50+$p, 80+$p, $px);
                $this->rect($img, 60, 70, 60+$p, 70+$p, $px);
                $this->rect($img, 70, 80, 70+$p, 80+$p, $px);
                // right eye ^
                $this->rect($img, 120, 80, 120+$p, 80+$p, $px);
                $this->rect($img, 130, 70, 130+$p, 70+$p, $px);
                $this->rect($img, 140, 80, 140+$p, 80+$p, $px);
                // smile arc
                $this->rect($img, 50,  130, 60,  130+$p, $px);
                $this->rect($img, 60,  140, 70,  140+$p, $px);
                $this->rect($img, 70,  150, 90,  150+$p, $px);
                $this->rect($img, 90,  150, 120, 150+$p, $px);
                $this->rect($img, 120, 150, 140, 150+$p, $px);
                $this->rect($img, 140, 140, 150, 140+$p, $px);
                $this->rect($img, 150, 130, 160, 130+$p, $px);
                break;

            case 1: // -- neutral / deadpan
                $this->rect($img, 48, 84, 88, 84+$p, $px);
                $this->rect($img, 112, 84, 152, 84+$p, $px);
                $this->rect($img, 60, 140, 140, 140+$p, $px);
                break;

            case 2: // O_O surprised
                // left O
                $this->rect($img, 44, 68, 44+$p, 68+$p, $px); $this->rect($img, 54, 68, 54+$p, 68+$p, $px); $this->rect($img, 64, 68, 64+$p, 68+$p, $px);
                $this->rect($img, 44, 78, 44+$p, 78+$p, $px); $this->rect($img, 64, 78, 64+$p, 78+$p, $px);
                $this->rect($img, 44, 88, 44+$p, 88+$p, $px); $this->rect($img, 54, 88, 54+$p, 88+$p, $px); $this->rect($img, 64, 88, 64+$p, 88+$p, $px);
                // right O
                $this->rect($img, 118, 68, 118+$p, 68+$p, $px); $this->rect($img, 128, 68, 128+$p, 68+$p, $px); $this->rect($img, 138, 68, 138+$p, 68+$p, $px);
                $this->rect($img, 118, 78, 118+$p, 78+$p, $px); $this->rect($img, 138, 78, 138+$p, 78+$p, $px);
                $this->rect($img, 118, 88, 118+$p, 88+$p, $px); $this->rect($img, 128, 88, 128+$p, 88+$p, $px); $this->rect($img, 138, 88, 138+$p, 88+$p, $px);
                // small o mouth
                $this->rect($img, 88, 130, 98, 130+$p, $px); $this->rect($img, 108, 130, 118, 130+$p, $px);
                $this->rect($img, 84, 140, 94, 140+$p, $px); $this->rect($img, 112, 140, 122, 140+$p, $px);
                $this->rect($img, 88, 150, 98, 150+$p, $px); $this->rect($img, 108, 150, 118, 150+$p, $px);
                break;

            case 3: // >_< squint
                // > left
                $this->rect($img, 44, 70, 44+$p, 70+$p, $px);
                $this->rect($img, 54, 80, 54+$p, 80+$p, $px);
                $this->rect($img, 44, 90, 44+$p, 90+$p, $px);
                // < right
                $this->rect($img, 148, 70, 148+$p, 70+$p, $px);
                $this->rect($img, 138, 80, 138+$p, 80+$p, $px);
                $this->rect($img, 148, 90, 148+$p, 90+$p, $px);
                // grin
                $this->rect($img, 50,  130, 60,  130+$p, $px);
                $this->rect($img, 60,  140, 150, 140+$p, $px);
                $this->rect($img, 140, 130, 150, 130+$p, $px);
                break;

            case 4: // UwU
                // U left eye
                $this->rect($img, 46, 72, 46+$p, 72+$p, $px);
                $this->rect($img, 56, 82, 56+$p, 82+$p, $px);
                $this->rect($img, 66, 72, 66+$p, 72+$p, $px);
                // U right eye
                $this->rect($img, 124, 72, 124+$p, 72+$p, $px);
                $this->rect($img, 134, 82, 134+$p, 82+$p, $px);
                $this->rect($img, 144, 72, 144+$p, 72+$p, $px);
                // w mouth
                $this->rect($img, 56,  128, 66,  128+$p, $px);
                $this->rect($img, 66,  138, 76,  138+$p, $px);
                $this->rect($img, 76,  128, 86,  128+$p, $px);
                $this->rect($img, 86,  138, 96,  138+$p, $px);
                $this->rect($img, 96,  128, 106, 128+$p, $px);
                $this->rect($img, 106, 138, 116, 138+$p, $px);
                $this->rect($img, 116, 128, 126, 128+$p, $px);
                $this->rect($img, 126, 138, 136, 138+$p, $px);
                $this->rect($img, 136, 128, 146, 128+$p, $px);
                break;

            case 5: // X X
                // X left
                $this->rect($img, 44, 68, 44+$p, 68+$p, $px); $this->rect($img, 74, 68, 74+$p, 68+$p, $px);
                $this->rect($img, 54, 78, 54+$p, 78+$p, $px); $this->rect($img, 64, 78, 64+$p, 78+$p, $px);
                $this->rect($img, 44, 88, 44+$p, 88+$p, $px); $this->rect($img, 74, 88, 74+$p, 88+$p, $px);
                // X right
                $this->rect($img, 118, 68, 118+$p, 68+$p, $px); $this->rect($img, 148, 68, 148+$p, 68+$p, $px);
                $this->rect($img, 128, 78, 128+$p, 78+$p, $px); $this->rect($img, 138, 78, 138+$p, 78+$p, $px);
                $this->rect($img, 118, 88, 118+$p, 88+$p, $px); $this->rect($img, 148, 88, 148+$p, 88+$p, $px);
                // teeth bar
                $this->rect($img, 46, 128, 154, 128+$p*2, $px);
                $this->rect($img, 48, 128, 58,  128+$p+4, $bgC);
                $this->rect($img, 60, 128, 70,  128+$p+4, $bgC);
                $this->rect($img, 72, 128, 82,  128+$p+4, $bgC);
                $this->rect($img, 84, 128, 94,  128+$p+4, $bgC);
                $this->rect($img, 96, 128, 106, 128+$p+4, $bgC);
                $this->rect($img, 108,128, 118, 128+$p+4, $bgC);
                $this->rect($img, 120,128, 130, 128+$p+4, $bgC);
                $this->rect($img, 132,128, 142, 128+$p+4, $bgC);
                $this->rect($img, 144,128, 154, 128+$p+4, $bgC);
                break;

            case 6: // :D big grin
                // : eyes — two stacked pixels each
                $this->rect($img, 56, 68, 56+$p, 68+$p, $px);
                $this->rect($img, 56, 82, 56+$p, 82+$p, $px);
                $this->rect($img, 134, 68, 134+$p, 68+$p, $px);
                $this->rect($img, 134, 82, 134+$p, 82+$p, $px);
                // D = big open smile arc
                $this->rect($img, 48,  118, 152, 118+$p, $px);
                $this->rect($img, 40,  128, 50,  128+$p, $px);
                $this->rect($img, 150, 128, 160, 128+$p, $px);
                $this->rect($img, 38,  138, 48,  138+$p, $px);
                $this->rect($img, 152, 138, 162, 138+$p, $px);
                $this->rect($img, 40,  148, 50,  148+$p, $px);
                $this->rect($img, 150, 148, 160, 148+$p, $px);
                $this->rect($img, 48,  158, 152, 158+$p, $px);
                break;

            case 7: // T_T crying
                // T eyes — horizontal bars
                $this->rect($img, 44, 72, 76, 72+$p, $px);
                $this->rect($img, 54, 82, 64, 82+$p+4, $px);
                $this->rect($img, 124, 72, 156, 72+$p, $px);
                $this->rect($img, 134, 82, 144, 82+$p+4, $px);
                // tear drops
                $this->rect($img, 58,  94,  68,  94+$p,  $pxD);
                $this->rect($img, 56,  104, 66,  104+$p, $pxD);
                $this->rect($img, 138, 94,  148, 94+$p,  $pxD);
                $this->rect($img, 136, 104, 146, 104+$p, $pxD);
                // sad mouth
                $this->rect($img, 52,  148, 62,  148+$p, $px);
                $this->rect($img, 62,  140, 72,  140+$p, $px);
                $this->rect($img, 72,  136, 128, 136+$p, $px);
                $this->rect($img, 128, 140, 138, 140+$p, $px);
                $this->rect($img, 138, 148, 148, 148+$p, $px);
                break;
        }

        // Status indicator dot bottom right
        $this->ellipse($img, 170, 170, 14, 14, $pxD);
        $this->ellipse($img, 170, 170,  8,  8, $px);

        return $img;
    }
}
