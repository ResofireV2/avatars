<?php

namespace Resofire\Avatars\Generator\Style;

class Emoji extends AbstractStyle
{
    public function key(): string { return 'emoji'; }
    public function name(): string { return 'Emoji'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        // ── SKIN TONES ────────────────────────────────────────────────
        $skinTones = [
            [255, 204,   0],  // classic yellow
            [255, 225, 185],  // light skin
            [240, 180, 120],  // medium-light skin
            [190, 120,  70],  // medium skin
            [120,  70,  30],  // dark skin
            [ 60,  30,  10],  // deep skin
            [ 68, 136, 204],  // blue alien
            [ 90, 170,  70],  // green goblin
        ];

        $eyeTypes   = [0, 1, 2, 3, 4, 5, 6, 7];
        $mouthTypes = [0, 1, 2, 3, 4, 5];
        $browTypes  = [0, 1, 2, 3, 4, 5]; // 0=none,1=flat,2=raised,3=furrowed,4=one-raised,5=bushy
        $accessory  = [0, 1, 2, 3, 4, 5, 6]; // 0=none,1=party hat,2=halo,3=devil horns,4=crown,5=grad cap,6=headband ears

        [$sr, $sg, $sb]  = $this->pick($username, 0, $skinTones);
        $eyeType          = $this->pick($username, 1, $eyeTypes);
        $mouthType        = $this->pick($username, 2, $mouthTypes);
        $browType         = $this->pick($username, 3, $browTypes);
        $acc              = $this->pick($username, 4, $accessory);
        $hasBlush         = $this->hash($username, 5, 0, 1);
        $hasTear          = $this->hash($username, 6, 0, 1);
        $hasSweat         = $this->hash($username, 7, 0, 1);

        // ── COLORS ────────────────────────────────────────────────────
        $bg     = $this->color($img, $sr, $sg, $sb);
        $dark   = $this->color($img, (int)($sr*0.65), (int)($sg*0.65), (int)($sb*0.65));
        $darker = $this->color($img, (int)($sr*0.42), (int)($sg*0.42), (int)($sb*0.42));
        $white  = $this->color($img, 255, 255, 255);
        $black  = $this->color($img,  20,  20,  20);
        $blush  = $this->color($img, 255, 100, 120);
        $red    = $this->color($img, 220,  50,  50);
        $blue   = $this->color($img, 100, 150, 255);
        $pink   = $this->color($img, 255, 100, 150);
        $gold   = $this->color($img, 255, 187,   0);
        $goldD  = $this->color($img, 204, 136,   0);

        $this->rect($img, 0, 0, 200, 200, $bg);

        // ── ACCESSORY (drawn first — behind face features) ────────────
        switch ($acc) {
            case 0: break; // none

            case 1: // party hat
                $hatC  = $this->color($img, 220,  30,  60);
                $hatD  = $this->color($img, 170,  10,  40);
                $hatDot= $this->color($img, 255, 238,   0);
                $this->polygon($img, [100, 0, 60, 54, 140, 54], $hatC);
                $this->polygon($img, [100, 0, 60, 54, 140, 54], $hatD); // redraw with outline trick
                // polka dots on hat
                $this->ellipse($img,  82, 24, 12, 12, $hatDot);
                $this->ellipse($img, 112, 18, 10, 10, $white);
                $this->ellipse($img,  92, 40, 10, 10, $white);
                $this->ellipse($img, 118, 36, 12, 12, $hatDot);
                // brim
                $this->rect($img, 56, 50, 144, 62, $hatD);
                $this->rect($img, 58, 52, 142, 60, $hatC);
                // pompom
                $this->ellipse($img, 100, 0, 18, 18, $hatDot);
                break;

            case 2: // halo
                $haloC = $this->color($img, 255, 221,   0);
                $haloL = $this->color($img, 255, 245, 120);
                imagesetthickness($img, 8);
                imagearc($img, 100, 18, 100, 28, 180, 360, $haloC);
                imagearc($img, 100, 18, 100, 28,   0, 180, $haloC);
                imagesetthickness($img, 4);
                imagearc($img, 100, 16, 102, 28, 180, 360, $haloL);
                imagesetthickness($img, 1);
                break;

            case 3: // devil horns
                $hornC = $this->color($img, 180,  20,  10);
                $hornD = $this->color($img, 130,  10,   5);
                $this->polygon($img, [ 62, 0,  46, 46,  80, 46], $hornC);
                $this->polygon($img, [138, 0, 120, 46, 154, 46], $hornC);
                $this->polygon($img, [ 62, 0,  46, 46,  80, 46], $hornD);
                $this->polygon($img, [138, 0, 120, 46, 154, 46], $hornD);
                break;

            case 4: // crown
                $crownC = $this->color($img, 255, 170,   0);
                $crownD = $this->color($img, 200, 120,   0);
                // five points
                $this->polygon($img, [
                    38, 0,   38, 52,
                    56, 38,  78, 56,
                   100, 36, 122, 56,
                   144, 38, 162, 52,
                   162, 0
                ], $crownC);
                // gems
                $gemR = $this->color($img, 220, 30,  60);
                $gemB = $this->color($img,  30, 80, 220);
                $gemG = $this->color($img,  30, 180, 60);
                $this->ellipse($img,  50, 14, 14, 14, $gemR);
                $this->ellipse($img, 100,  8, 16, 16, $gemB);
                $this->ellipse($img, 150, 14, 14, 14, $gemG);
                // band
                $this->rect($img, 38, 46, 162, 60, $crownD);
                break;

            case 5: // graduation cap
                $capC = $this->color($img, 28, 28, 28);
                $capD = $this->color($img, 50, 50, 50);
                // board top
                $this->polygon($img, [100, 0, 48, 22, 100, 44, 152, 22], $capC);
                // cylinder
                $this->rect($img, 74, 22, 126, 40, $capD);
                // tassel
                imageline($img, 152, 22, 158, 48, $gold);
                $this->ellipse($img, 158, 50, 12, 12, $gold);
                break;

            case 6: // headband with animal ears
                $earC = $this->color($img, 255, 180, 200);
                $earD = $this->color($img, 220, 120, 150);
                $bandC= $this->color($img, 200, 100, 140);
                // ears
                $this->ellipse($img,  44, 18, 38, 44, $earD);
                $this->ellipse($img,  44, 18, 26, 32, $earC);
                $this->ellipse($img, 156, 18, 38, 44, $earD);
                $this->ellipse($img, 156, 18, 26, 32, $earC);
                // headband
                $this->rect($img, 18, 36, 182, 52, $bandC);
                break;
        }

        // ── EYES ──────────────────────────────────────────────────────
        switch ($eyeType) {
            case 0: // classic dots
                $this->ellipse($img,  70, 88, 34, 34, $darker);
                $this->ellipse($img, 130, 88, 34, 34, $darker);
                $this->ellipse($img,  70, 88, 20, 20, $black);
                $this->ellipse($img, 130, 88, 20, 20, $black);
                $this->ellipse($img,  63, 82,  8,  8, $white);
                $this->ellipse($img, 123, 82,  8,  8, $white);
                break;

            case 1: // heart eyes
                $this->ellipse($img,  58, 84, 30, 28, $white);
                $this->ellipse($img,  82, 84, 30, 28, $white);
                $this->ellipse($img,  58, 86, 28, 26, $red);
                $this->ellipse($img,  82, 86, 28, 26, $red);
                $this->polygon($img, [46,88, 94,88, 70,114], $red);
                $this->polygon($img, [46,88, 94,88, 70,112], $white);
                $this->ellipse($img, 118, 84, 30, 28, $white);
                $this->ellipse($img, 142, 84, 30, 28, $white);
                $this->ellipse($img, 118, 86, 28, 26, $red);
                $this->ellipse($img, 142, 86, 28, 26, $red);
                $this->polygon($img, [106,88, 154,88, 130,114], $red);
                $this->polygon($img, [106,88, 154,88, 130,112], $white);
                break;

            case 2: // sunglasses
                $this->rect($img,  38, 76,  92, 104, $black);
                $this->rect($img, 108, 76, 162, 104, $black);
                $this->rect($img,  92, 84, 108,  92, $black);
                $this->rect($img,  30, 84,  38,  92, $black);
                $this->rect($img, 162, 84, 170,  92, $black);
                $this->ellipse($img,  55, 84, 28, 18, $dark);
                $this->ellipse($img, 115, 84, 28, 18, $dark);
                break;

            case 3: // X eyes
                $this->ellipse($img,  70, 88, 44, 44, $white);
                $this->ellipse($img, 130, 88, 44, 44, $white);
                imagesetthickness($img, 7);
                imageline($img,  50, 68,  90, 108, $darker);
                imageline($img,  90, 68,  50, 108, $darker);
                imageline($img, 110, 68, 150, 108, $darker);
                imageline($img, 150, 68, 110, 108, $darker);
                imagesetthickness($img, 1);
                break;

            case 4: // star eyes
                $this->ellipse($img,  70, 88, 50, 50, $white);
                $this->ellipse($img, 130, 88, 50, 50, $white);
                $this->polygon($img, [ 70,65,  74,80,  88,80,  77,90,  81,105,  70,95,  59,105,  63,90,  52,80,  66,80], $darker);
                $this->polygon($img, [130,65, 134,80, 148,80, 137,90, 141,105, 130,95, 119,105, 123,90, 112,80, 126,80], $darker);
                break;

            case 5: // wide open surprise
                $this->ellipse($img,  70, 88, 56, 60, $white);
                $this->ellipse($img, 130, 88, 56, 60, $white);
                $this->ellipse($img,  70, 89, 38, 42, $darker);
                $this->ellipse($img, 130, 89, 38, 42, $darker);
                $this->ellipse($img,  70, 90, 20, 22, $black);
                $this->ellipse($img, 130, 90, 20, 22, $black);
                $this->ellipse($img,  60, 80, 12, 12, $white);
                $this->ellipse($img, 120, 80, 12, 12, $white);
                break;

            case 6: // wink — right eye closed
                $this->ellipse($img,  70, 88, 34, 34, $darker);
                $this->ellipse($img,  70, 88, 20, 20, $black);
                $this->ellipse($img,  63, 82,  8,  8, $white);
                imagesetthickness($img, 6);
                imagearc($img, 130, 92, 46, 30, 200, 340, $darker);
                imagesetthickness($img, 1);
                break;

            case 7: // squint happy ^_^
                imagesetthickness($img, 6);
                imagearc($img,  70, 94, 46, 32, 200, 340, $darker);
                imagearc($img, 130, 94, 46, 32, 200, 340, $darker);
                imagesetthickness($img, 1);
                break;
        }

        // ── EYEBROWS ──────────────────────────────────────────────────
        switch ($browType) {
            case 0: break; // none

            case 1: // flat neutral
                $this->rect($img,  44, 60, 94, 68, $darker);
                $this->rect($img, 106, 60, 156, 68, $darker);
                break;

            case 2: // raised / surprised — arched up high
                imagesetthickness($img, 7);
                imagearc($img,  70, 58, 58, 28, 210, 330, $darker);
                imagearc($img, 130, 58, 58, 28, 210, 330, $darker);
                imagesetthickness($img, 1);
                break;

            case 3: // furrowed angry — inner ends drop down
                imagesetthickness($img, 8);
                imageline($img,  44, 60,  96, 70, $darker);
                imageline($img, 104, 70, 156, 60, $darker);
                imagesetthickness($img, 1);
                break;

            case 4: // one raised (left up, right flat)
                imagesetthickness($img, 7);
                imagearc($img, 70, 58, 58, 28, 210, 330, $darker);
                imagesetthickness($img, 1);
                $this->rect($img, 106, 62, 156, 69, $darker);
                break;

            case 5: // thick bushy
                $bushyC = $this->color($img, max(0,(int)($sr*0.3)), max(0,(int)($sg*0.3)), max(0,(int)($sb*0.3)));
                $this->ellipse($img,  69, 63, 56, 16, $bushyC);
                $this->ellipse($img, 131, 63, 56, 16, $bushyC);
                break;
        }

        // ── BLUSH ─────────────────────────────────────────────────────
        if ($hasBlush) {
            $this->ellipse($img,  38, 120, 42, 22, $blush);
            $this->ellipse($img, 162, 120, 42, 22, $blush);
        }

        // ── TEAR ──────────────────────────────────────────────────────
        if ($hasTear && $eyeType !== 7) {
            $this->ellipse($img, 155, 106, 14, 20, $blue);
            $this->ellipse($img, 155, 118, 10, 10, $blue);
        }

        // ── SWEAT DROP ────────────────────────────────────────────────
        if ($hasSweat && !$hasTear) {
            $this->ellipse($img, 170, 50, 12, 18, $blue);
            $this->ellipse($img, 170, 60,  8,  8, $blue);
        }

        // ── MOUTH ─────────────────────────────────────────────────────
        switch ($mouthType) {
            case 0: // big smile arc
                imagesetthickness($img, 7);
                imagearc($img, 100, 148, 110, 70, 15, 165, $darker);
                imagesetthickness($img, 1);
                break;

            case 1: // grin with teeth
                $this->ellipse($img, 100, 156, 110, 50, $darker);
                $this->rect($img,  46, 138, 154, 158, $darker);
                $this->rect($img,  48, 140, 152, 155, $white);
                $this->rect($img,  70, 140,  73, 155, $darker);
                $this->rect($img,  92, 140,  95, 155, $darker);
                $this->rect($img, 114, 140, 117, 155, $darker);
                $this->rect($img, 136, 140, 139, 155, $darker);
                break;

            case 2: // open laugh O
                $this->ellipse($img, 100, 155, 80, 60, $darker);
                $this->ellipse($img, 100, 157, 62, 44, $black);
                break;

            case 3: // sad frown
                imagesetthickness($img, 7);
                imagearc($img, 100, 170, 100, 70, 195, 345, $darker);
                imagesetthickness($img, 1);
                break;

            case 4: // flat neutral
                $this->rect($img, 60, 148, 140, 156, $darker);
                break;

            case 5: // tongue out
                $this->ellipse($img, 100, 152,  90, 44, $darker);
                $this->rect($img,   56, 134, 144, 152, $darker);
                $this->rect($img,   58, 136, 142, 150, $white);
                $this->ellipse($img, 100, 164,  52, 34, $pink);
                $this->ellipse($img, 100, 167,  30, 16, $red);
                break;
        }

        return $img;
    }
}
