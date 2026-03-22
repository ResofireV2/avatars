<?php

namespace Resofire\Avatars\Generator\Style;

class Orc extends AbstractStyle
{
    public function key(): string { return 'orc'; }
    public function name(): string { return 'Orc'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        $skinColors = [
            [42,  74,  26],  // swamp green
            [58,  90,  26],  // bright green
            [26,  74,  42],  // teal green
            [74,  90,  16],  // yellow green
            [90,  42,  10],  // brown orc
            [58,  26,  90],  // twilight purple
            [42,  26,  74],  // dark purple
            [74,  42,  16],  // bronze
        ];
        $eyeColors = [
            [255, 204,   0],  // gold
            [255,  80,   0],  // orange
            [204, 220,   0],  // yellow-green
            [255, 140,   0],  // amber
            [220, 220,   0],  // yellow
            [255,  60,  60],  // red
        ];

        [$br, $bg2, $bb] = $this->pick($username, 0, $skinColors);
        [$er, $eg, $eb]  = $this->pick($username, 1, $eyeColors);

        $tuskType    = $this->hash($username, 2, 0, 6); // 0-6
        $hairType    = $this->hash($username, 3, 0, 6); // 0-6
        $warpaintType= $this->hash($username, 4, 0, 6); // 0-6
        $eyeType     = $this->hash($username, 5, 0, 4); // 0-4
        $noseRing    = $this->hash($username, 6, 0, 4); // 0=none,1=left,2=right,3=both,4=septum
        $hasScar     = $this->hash($username, 7, 0, 1);

        $bg     = $this->color($img, $br, $bg2, $bb);
        $bgD    = $this->color($img, (int)($br*0.6), (int)($bg2*0.6), (int)($bb*0.6));
        $bgL    = $this->color($img, min(255,$br+20), min(255,$bg2+20), min(255,$bb+20));
        $eye    = $this->color($img, $er, $eg, $eb);
        $eyeD   = $this->color($img, (int)($er*0.6), (int)($eg*0.5), 0);
        $black  = $this->color($img, 8, 8, 8);
        $white  = $this->color($img, 255, 255, 255);
        $tuskC  = $this->color($img, 232, 220, 192);
        $tuskD  = $this->color($img, 180, 168, 140);
        $gold   = $this->color($img, 255, 204, 0);
        $goldD  = $this->color($img, 180, 140, 0);
        $red    = $this->color($img, 180, 0, 0);
        $darkRed= $this->color($img, 100, 0, 0);
        $ironC  = $this->color($img, 80, 80, 80);
        $ironL  = $this->color($img, 110, 110, 110);

        $this->rect($img, 0, 0, 200, 200, $bg);

        // ── HAIR / TOP ─────────────────────────────────────────────
        switch ($hairType) {
            case 0: // spikes
                $hairC = $this->color($img, 26, 26, 26);
                $positions = [40, 60, 80, 100, 120, 140, 160];
                $count = $this->hash($username, 20, 3, 5);
                $step  = (int)(120 / ($count + 1));
                for ($i = 0; $i < $count; $i++) {
                    $cx = 40 + ($i + 1) * $step;
                    $h  = $this->hash($username, 21+$i, 26, 44);
                    $this->polygon($img, [$cx-10, 52, $cx, 52-$h, $cx+10, 52], $hairC);
                }
                break;
            case 1: // mohawk strip
                $hairC = $this->color($img, $this->hash($username,30,80,180), 0, 0);
                $this->rect($img, 88, 0, 112, 48, $hairC);
                $this->ellipse($img, 100, 10, 28, 20, $this->color($img, min(255,
                    $this->hash($username,30,80,180)+40), 0, 0));
                $this->ellipse($img, 100, 36, 22, 16, $hairC);
                break;
            case 2: // dreadlocks at sides
                $hairC = $this->color($img, 60, 30, 0);
                $beadC = $this->color($img, 200, 140, 0);
                $this->ellipse($img, 16, 86,  14, 44, $hairC);
                $this->ellipse($img, 184, 86, 14, 44, $hairC);
                $this->ellipse($img, 14, 98,  10, 28, $bgD);
                $this->ellipse($img, 186, 98, 10, 28, $bgD);
                $this->ellipse($img, 16, 88,  10, 8,  $beadC);
                $this->ellipse($img, 184, 88, 10, 8,  $beadC);
                break;
            case 3: // bald with skull tattoo
                $tattooC = $this->color($img, max(0,$br-30), max(0,$bg2-30), max(0,$bb-30));
                $this->ellipse($img, 100, 30, 34, 28, $tattooC);
                $this->ellipse($img, 88,  28, 11, 11, $bgD);
                $this->ellipse($img, 112, 28, 11, 11, $bgD);
                $this->ellipse($img, 100, 38, 9,  7,  $bgD);
                $this->polygon($img, [94,44, 100,50, 106,44], $bgD);
                break;
            case 4: // wild mane
                $hairC = $this->color($img, 50, 25, 0);
                $this->ellipse($img, 14,  84, 22, 50, $hairC);
                $this->ellipse($img, 186, 84, 22, 50, $hairC);
                $this->ellipse($img, 36,  30, 24, 36, $hairC);
                $this->ellipse($img, 64,  24, 24, 32, $hairC);
                $this->ellipse($img, 100, 22, 24, 30, $hairC);
                $this->ellipse($img, 136, 24, 24, 32, $hairC);
                $this->ellipse($img, 164, 30, 24, 36, $hairC);
                break;
            case 5: // topknot
                $hairC = $this->color($img, 26, 26, 26);
                $wrapC = $this->color($img, 180, 60, 0);
                $this->ellipse($img, 100, 22, 28, 26, $hairC);
                $this->ellipse($img, 100, 20, 20, 18, $this->color($img,50,50,50));
                $this->rect($img, 84, 32, 116, 38, $wrapC);
                break;
            case 6: // battle helmet
                $this->rect($img, 14, 0,  186, 50, $ironC);
                $this->rect($img, 16, 2,  184, 48, $ironL);
                // horns
                $this->polygon($img, [14, 0,  8,  0,  14, 28], $ironC);
                $this->polygon($img, [186, 0, 192, 0, 186, 28], $ironC);
                // rivets
                $this->ellipse($img, 30,  12, 8, 8, $ironC);
                $this->ellipse($img, 170, 12, 8, 8, $ironC);
                $this->ellipse($img, 100, 8,  8, 8, $ironC);
                // nose guard
                $this->rect($img, 94, 44, 106, 70, $ironC);
                break;
        }

        // ── BROW RIDGE ─────────────────────────────────────────────
        $this->ellipse($img, 100, 72, 148, 28, $bgD);

        // ── EYES ────────────────────────────────────────────────────
        switch ($eyeType) {
            case 0: // wide fierce (original)
                $this->ellipse($img, 62,  92, 52, 38, $eye);
                $this->ellipse($img, 138, 92, 52, 38, $eye);
                $this->ellipse($img, 62,  92, 32, 24, $eyeD);
                $this->ellipse($img, 138, 92, 32, 24, $eyeD);
                $this->ellipse($img, 62,  93, 14, 18, $black);
                $this->ellipse($img, 138, 93, 14, 18, $black);
                $this->ellipse($img, 56,  87, 8,  8,  $white);
                $this->ellipse($img, 132, 87, 8,  8,  $white);
                break;
            case 1: // small beady
                $this->ellipse($img, 62,  92, 24, 18, $eye);
                $this->ellipse($img, 138, 92, 24, 18, $eye);
                $this->ellipse($img, 62,  92, 14, 11, $eyeD);
                $this->ellipse($img, 138, 92, 14, 11, $eyeD);
                $this->ellipse($img, 62,  93, 6,  7,  $black);
                $this->ellipse($img, 138, 93, 6,  7,  $black);
                $this->ellipse($img, 57,  88, 5,  5,  $white);
                $this->ellipse($img, 133, 88, 5,  5,  $white);
                break;
            case 2: // one squinting with scar
                // left normal
                $this->ellipse($img, 62,  92, 52, 38, $eye);
                $this->ellipse($img, 62,  92, 32, 24, $eyeD);
                $this->ellipse($img, 62,  93, 14, 18, $black);
                $this->ellipse($img, 56,  87, 8,  8,  $white);
                // right squinting
                $this->ellipse($img, 138, 94, 52, 20, $eye);
                $this->ellipse($img, 138, 94, 32, 12, $eyeD);
                $this->ellipse($img, 138, 94, 14,  6, $black);
                // scar through right eye
                imagesetthickness($img, 3);
                imageline($img, 128, 78, 140, 110, $bgD);
                imagesetthickness($img, 1);
                break;
            case 3: // berserker glow
                $glowR = $this->color($img, 200, 0, 0);
                $glowM = $this->color($img, 255, 60, 0);
                $glowL = $this->color($img, 255, 160, 0);
                foreach ([62, 138] as $ex) {
                    $this->ellipse($img, $ex, 92, 58, 44, $darkRed);
                    $this->ellipse($img, $ex, 92, 48, 36, $glowR);
                    $this->ellipse($img, $ex, 92, 36, 26, $glowM);
                    $this->ellipse($img, $ex, 92, 22, 16, $glowL);
                    $this->ellipse($img, $ex, 92, 8,  8,  $white);
                }
                break;
            case 4: // mismatched
                $eye2C = $this->color($img, 200, 0, 0);
                $eye2D = $this->color($img, 100, 0, 0);
                $this->ellipse($img, 62,  92, 52, 38, $eye);
                $this->ellipse($img, 62,  92, 32, 24, $eyeD);
                $this->ellipse($img, 62,  93, 14, 18, $black);
                $this->ellipse($img, 56,  87, 8,  8,  $white);
                $this->ellipse($img, 138, 92, 52, 38, $eye2C);
                $this->ellipse($img, 138, 92, 32, 24, $eye2D);
                $this->ellipse($img, 138, 93, 14, 18, $black);
                $this->ellipse($img, 132, 87, 8,  8,  $white);
                break;
        }

        // ── WARPAINT ────────────────────────────────────────────────
        $wpR = $this->hash($username, 40, 0, 2); // 0=red,1=black,2=white
        $wpColors = [
            [$this->color($img,180,20,20),  $this->color($img,220,40,40)],
            [$this->color($img,10,10,10),   $this->color($img,40,40,40)],
            [$this->color($img,220,220,220),$this->color($img,255,255,255)],
        ];
        [$wpC, $wpL] = $wpColors[$wpR];

        switch ($warpaintType) {
            case 0: break; // none
            case 1: // cheek stripes
                $this->rect($img, 14, 86, 36, 94, $wpC);
                $this->rect($img, 16, 96, 34, 103, $wpC);
                $this->rect($img, 164, 86, 186, 94, $wpC);
                $this->rect($img, 166, 96, 184, 103, $wpC);
                break;
            case 2: // full tribal
                $this->rect($img, 94, 30, 106, 68, $wpC);
                $this->rect($img, 14, 84,  48, 94, $wpC);
                $this->rect($img, 152, 84, 186, 94, $wpC);
                $this->rect($img, 14, 96,  42, 104, $wpC);
                $this->rect($img, 158, 96, 186, 104, $wpC);
                $this->ellipse($img, 88,  50, 7, 7, $wpC);
                $this->ellipse($img, 100, 46, 7, 7, $wpC);
                $this->ellipse($img, 112, 50, 7, 7, $wpC);
                break;
            case 3: // eye stripe band
                $this->rect($img, 14, 82, 186, 100, $wpC);
                break;
            case 4: // forehead symbol
                $this->ellipse($img, 100, 48, 22, 18, $wpC);
                $this->rect($img, 94,  36, 106, 58, $wpC);
                $this->rect($img, 84,  44, 116, 54, $wpC);
                $this->ellipse($img, 100, 48, 9, 9, $wpL);
                break;
            case 5: // skull paint
                $skullW = $this->color($img, 220, 220, 200);
                $this->ellipse($img, 100, 62, 56, 44, $skullW);
                $this->ellipse($img, 80,  62, 22, 20, $bgD);
                $this->ellipse($img, 120, 62, 22, 20, $bgD);
                $this->ellipse($img, 92,  74, 9,  7,  $bgD);
                $this->ellipse($img, 108, 74, 9,  7,  $bgD);
                $this->polygon($img, [84,84, 116,84, 116,90, 84,90], $bgD);
                foreach ([88,96,104,112] as $tx) {
                    $this->rect($img, $tx, 84, $tx+6, 90, $skullW);
                }
                break;
            case 6: // blood splatter
                $bloodC = $this->color($img, 140, 0, 0);
                $splatPositions = [[24,68],[38,58],[18,80],[54,62],[168,66],[180,74],[158,58],[174,82]];
                foreach ($splatPositions as $i => [$sx, $sy]) {
                    $r = $this->hash($username, 50+$i, 4, 10);
                    $this->ellipse($img, $sx, $sy, $r, (int)($r*0.8), $bloodC);
                }
                break;
        }

        // ── SCAR ───────────────────────────────────────────────────
        if ($hasScar && $eyeType !== 2) { // avoid double-scar with squint eye
            $scarC = $this->color($img, max(0,$br-30), max(0,$bg2-30), max(0,$bb-30));
            imagesetthickness($img, 3);
            imageline($img, 72, 64, 82, 118, $scarC);
            imagesetthickness($img, 1);
        }

        // ── NOSE ───────────────────────────────────────────────────
        $this->ellipse($img, 100, 122, 52, 34, $bgD);
        $this->ellipse($img, 82,  126, 18, 18, $this->color($img,(int)($br*0.5),(int)($bg2*0.5),(int)($bb*0.5)));
        $this->ellipse($img, 118, 126, 18, 18, $this->color($img,(int)($br*0.5),(int)($bg2*0.5),(int)($bb*0.5)));

        // ── NOSE RING ───────────────────────────────────────────────
        $ringMetal = $this->hash($username, 60, 0, 2); // 0=gold, 1=silver, 2=bone
        $ringColors = [$gold, $this->color($img,200,200,200), $this->color($img,220,210,180)];
        $ringDColors= [$goldD, $this->color($img,140,140,140), $this->color($img,170,158,130)];
        $ringC  = $ringColors[$ringMetal];
        $ringDC = $ringDColors[$ringMetal];

        switch ($noseRing) {
            case 0: break; // none
            case 1: // left nostril ring
                imagesetthickness($img, 4);
                imagearc($img, 82, 130, 16, 16, 0, 270, $ringC);
                imagesetthickness($img, 1);
                $this->ellipse($img, 82, 122, 6, 6, $ringDC);
                break;
            case 2: // right nostril ring
                imagesetthickness($img, 4);
                imagearc($img, 118, 130, 16, 16, 270, 180, $ringC);
                imagesetthickness($img, 1);
                $this->ellipse($img, 118, 122, 6, 6, $ringDC);
                break;
            case 3: // both nostrils
                imagesetthickness($img, 4);
                imagearc($img, 82,  130, 16, 16, 0,   270, $ringC);
                imagearc($img, 118, 130, 16, 16, 270, 180, $ringC);
                imagesetthickness($img, 1);
                $this->ellipse($img, 82,  122, 6, 6, $ringDC);
                $this->ellipse($img, 118, 122, 6, 6, $ringDC);
                break;
            case 4: // septum — large ring through the centre
                imagesetthickness($img, 5);
                imagearc($img, 100, 128, 28, 22, 0, 180, $ringC);
                imagesetthickness($img, 1);
                $this->ellipse($img, 86,  128, 8, 8, $ringDC);
                $this->ellipse($img, 114, 128, 8, 8, $ringDC);
                break;
        }

        // ── MOUTH ──────────────────────────────────────────────────
        $this->ellipse($img, 100, 154, 82, 28, $bgD);

        // ── TUSKS ──────────────────────────────────────────────────
        switch ($tuskType) {
            case 0: // twin upward (original)
                $this->polygon($img, [70, 148,  58, 182,  84, 148], $tuskC);
                $this->polygon($img, [130, 148, 142, 182, 116, 148], $tuskC);
                $this->polygon($img, [72, 148,  62, 176,  80, 148], $tuskD);
                $this->polygon($img, [132, 148, 138, 176, 122, 148], $tuskD);
                break;
            case 1: // single massive centre tusk
                $this->polygon($img, [88, 148, 100, 190, 112, 148], $tuskC);
                $this->polygon($img, [91, 148, 100, 184, 109, 148], $tuskD);
                break;
            case 2: // four small tusks
                foreach ([[64,76], [80,70], [120,70], [136,76]] as [$tx, $th]) {
                    $this->polygon($img, [$tx-7, 148, $tx, 148+$th, $tx+7, 148], $tuskC);
                    $this->polygon($img, [$tx-4, 148, $tx, 148+$th-8, $tx+4, 148], $tuskD);
                }
                break;
            case 3: // broken — left full, right stubbed
                $this->polygon($img, [70, 148, 58, 182, 84, 148], $tuskC);
                $this->polygon($img, [72, 148, 62, 176, 80, 148], $tuskD);
                $this->polygon($img, [130, 148, 126, 164, 140, 148], $tuskC);
                imagesetthickness($img, 2);
                imageline($img, 126, 164, 140, 158, $tuskD);
                imageline($img, 128, 166, 142, 160, $this->color($img,150,140,115));
                imagesetthickness($img, 1);
                break;
            case 4: // wide splayed outward
                imagesetthickness($img, 8);
                imageline($img, 74, 148, 48, 178, $tuskC);
                imageline($img, 126, 148, 152, 178, $tuskC);
                imagesetthickness($img, 4);
                imageline($img, 74, 148, 50, 174, $tuskD);
                imageline($img, 126, 148, 150, 174, $tuskD);
                imagesetthickness($img, 1);
                break;
            case 5: // ringed tusks with gold bands
                $this->polygon($img, [70, 148, 58, 182, 84, 148], $tuskC);
                $this->polygon($img, [130, 148, 142, 182, 116, 148], $tuskC);
                // gold rings
                $this->rect($img, 58, 158, 84, 164, $gold);
                $this->rect($img, 116, 158, 142, 164, $gold);
                $this->rect($img, 60, 168, 82, 173, $goldD);
                $this->rect($img, 118, 168, 140, 173, $goldD);
                break;
            case 6: // no tusks — just grim line
                imagesetthickness($img, 3);
                imagearc($img, 100, 160, 70, 28, 200, 340, $bgD);
                imagesetthickness($img, 1);
                break;
        }

        return $img;
    }
}
