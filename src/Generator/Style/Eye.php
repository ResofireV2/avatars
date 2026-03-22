<?php

namespace Resofire\Avatars\Generator\Style;

class Eye extends AbstractStyle
{
    public function key(): string { return 'eye'; }
    public function name(): string { return 'Eye'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        // ── SLOTS ────────────────────────────────────────────────────
        $irisColorIdx  = $this->hash($username, 0, 0, 9);  // 10 iris colors
        $irisTexture   = $this->hash($username, 1, 0, 6);  // 7 textures
        $pupilShape    = $this->hash($username, 2, 0, 8);  // 9 shapes
        $scleraType    = $this->hash($username, 3, 0, 5);  // 6 sclera conditions
        $pupilFlair    = $this->hash($username, 4, 0, 5);  // 6 flair types
        $catchLight    = $this->hash($username, 5, 0, 4);  // 5 catch light types
        $limbalRing    = $this->hash($username, 6, 0, 2);  // 3 limbal ring widths

        // ── IRIS COLORS (inner/outer/mid) ───────────────────────────
        $irisColors = [
            [[26,58,136],  [30,68,170],  [50,100,204]], // ocean blue
            [[136,52,0],   [200,85,0],   [255,136,0]],  // amber fire
            [[10,58,10],   [26,90,26],   [42,130,42]],  // forest green
            [[42,10,90],   [74,26,150],  [106,42,204]], // deep violet
            [[90,90,106],  [120,120,136],[154,154,170]], // silver grey
            [[100,0,0],    [170,0,0],    [204,34,0]],   // blood red
            [[0,90,100],   [0,140,160],  [0,200,220]],  // teal/cyan
            [[100,60,0],   [160,100,10], [220,160,30]], // gold
            [[180,60,120], [220,80,160], [255,120,200]],// rose pink
            [[10,10,26],   [20,20,50],   [40,40,90]],   // near black / void
        ];
        [$ic1, $ic2, $ic3] = $irisColors[$irisColorIdx];

        $irisOuter = $this->color($img, ...$ic1);
        $irisMid   = $this->color($img, ...$ic2);
        $irisInner = $this->color($img, ...$ic3);

        // ── SCLERA COLORS ────────────────────────────────────────────
        $scleraBases = [
            [240, 236, 226], // clean white
            [240, 220, 218], // bloodshot (pinkish)
            [232, 216, 140], // yellowed
            [10,   4,   8],  // black sclera
            [244, 240, 232], // cracked porcelain
            [14,  20,  40],  // deep blue alien
        ];
        [$slr, $slg, $slb] = $scleraBases[$scleraType];
        $scleraC = $this->color($img, $slr, $slg, $slb);

        // ── BASE COLORS ──────────────────────────────────────────────
        $black  = $this->color($img,   8,   8,   8);
        $white  = $this->color($img, 255, 255, 255);
        $red    = $this->color($img, 204,  17,   0);
        $redD   = $this->color($img, 136,   0,   0);
        $redL   = $this->color($img, 255,  68,  34);
        $orange = $this->color($img, 255, 102,   0);
        $yellow = $this->color($img, 255, 220,   0);
        $blue   = $this->color($img,  68, 187, 255);
        $blueL  = $this->color($img, 136, 221, 255);
        $green  = $this->color($img,  68, 255,   0);
        $greenL = $this->color($img, 136, 255,  68);
        $purple = $this->color($img, 136,   0, 204);
        $purpleL= $this->color($img, 170,  68, 255);
        $gold   = $this->color($img, 255, 204,   0);
        $goldL  = $this->color($img, 255, 238, 136);

        // ── FILL SCLERA ──────────────────────────────────────────────
        $this->rect($img, 0, 0, 200, 200, $scleraC);

        // ── SCLERA CONDITIONS ────────────────────────────────────────
        switch ($scleraType) {
            case 1: // dramatic bloodshot — thick veins
                $veinC = $this->color($img, 204, 17, 0);
                $veinD = $this->color($img, 136,  0, 0);
                $veins = [
                    [0,30,  60,70,  50,90],
                    [0,80,  54,84,  68,100],
                    [0,140, 50,120, 40,108],
                    [200,30, 144,62, 152,82],
                    [200,80, 152,80, 138,94],
                    [200,140,152,118,148,100],
                    [80,200, 90,164, 100,140],
                    [120,200,112,162,102,140],
                ];
                foreach ($veins as $i => [$x1,$y1,$x2,$y2,$x3,$y3]) {
                    imagesetthickness($img, 4);
                    imageline($img, $x1,$y1,$x2,$y2, $veinC);
                    imagesetthickness($img, 3);
                    imageline($img, $x2,$y2,$x3,$y3, $veinD);
                    // branch
                    $bx = $x2 + $this->hash($username, 30+$i, -20, 20);
                    $by = $y2 + $this->hash($username, 38+$i, -16, 16);
                    imagesetthickness($img, 2);
                    imageline($img, $x2,$y2,$bx,$by, $veinC);
                }
                imagesetthickness($img, 1);
                break;
            case 2: // yellowed — add brown age spots
                $spotC = $this->color($img, 180, 148, 60);
                foreach ([[30,40,14,10],[158,36,12,9],[20,140,10,8],[170,150,12,9],[80,170,10,7]] as [$sx,$sy,$sw,$sh]) {
                    $this->ellipse($img, $sx, $sy, $sw, $sh, $spotC);
                }
                break;
            case 3: // black sclera — add subtle dark texture
                $texC = $this->color($img, 20, 8, 16);
                foreach ([[40,30],[160,28],[24,100],[176,104],[60,170],[140,168]] as [$tx,$ty]) {
                    $this->ellipse($img, $tx, $ty, 20, 14, $texC);
                }
                break;
            case 4: // cracked porcelain — web of fine cracks
                $crackC = $this->color($img, 176, 168, 148);
                $crackPts = [
                    [100,100, 34,34],   [34,34,  8,26],    [34,34,  20,14],
                    [100,100, 20,100],  [20,100,  4,90],
                    [100,100, 40,168],  [40,168, 24,186],   [40,168, 18,176],
                    [100,100, 168,168], [168,168,186,180],
                    [100,100, 180,100], [180,100,196,92],
                    [100,100, 168,34],  [168,34, 186,20],   [168,34, 178,14],
                    [100,100, 100,20],  [100,20, 94,4],
                ];
                imagesetthickness($img, 2);
                foreach ($crackPts as [$x1,$y1,$x2,$y2]) {
                    imageline($img, $x1,$y1,$x2,$y2, $crackC);
                }
                imagesetthickness($img, 1);
                break;
            case 5: // deep blue alien — vein-like dark blue lines
                $alienV = $this->color($img, 8, 30, 80);
                imagesetthickness($img, 3);
                foreach ([[0,40,50,60],[200,40,154,62],[0,120,48,118],[200,120,152,118],[80,200,88,164],[120,200,112,162]] as [$x1,$y1,$x2,$y2]) {
                    imageline($img, $x1,$y1,$x2,$y2, $alienV);
                }
                imagesetthickness($img, 1);
                break;
        }

        // ── IRIS BASE (large, fills most of circle) ──────────────────
        $this->ellipse($img, 100, 100, 148, 148, $irisOuter);
        $this->ellipse($img, 100, 100, 124, 124, $irisMid);
        $this->ellipse($img, 100, 100,  96,  96, $irisInner);

        // ── IRIS TEXTURE ─────────────────────────────────────────────
        switch ($irisTexture) {
            case 0: // radiating spokes
                $spokeC = $this->color($img, max(0,$ic1[0]-14), max(0,$ic1[1]-14), max(0,$ic1[2]-14));
                $angles = [0,22,45,67,90,112,135,157,180,202,225,247,270,292,315,337];
                foreach ($angles as $i => $deg) {
                    $rad = $deg * M_PI / 180;
                    $x2 = (int)(100 + cos($rad) * 74);
                    $y2 = (int)(100 + sin($rad) * 74);
                    $w = ($i % 2 === 0) ? 3 : 2;
                    imagesetthickness($img, $w);
                    imageline($img, 100, 100, $x2, $y2, $spokeC);
                }
                imagesetthickness($img, 1);
                break;
            case 1: // concentric rings
                $ringA = $this->color($img, max(0,$ic1[0]-20), max(0,$ic1[1]-20), max(0,$ic1[2]-20));
                $ringB = $this->color($img, min(255,$ic3[0]+20), min(255,$ic3[1]+20), min(255,$ic3[2]+20));
                foreach ([68,56,44,32] as $i => $r) {
                    $c = ($i % 2 === 0) ? $ringA : $ringB;
                    imagesetthickness($img, 2);
                    imagearc($img, 100, 100, $r*2, $r*2, 0, 360, $c);
                }
                imagesetthickness($img, 1);
                break;
            case 2: // starburst
                $sbC = $this->color($img, max(0,$ic1[0]-18), max(0,$ic1[1]-18), max(0,$ic1[2]-18));
                $sbL = $this->color($img, min(255,$ic3[0]+18), min(255,$ic3[1]+18), min(255,$ic3[2]+18));
                for ($a = 0; $a < 360; $a += 30) {
                    $rad = $a * M_PI / 180;
                    $long = ($a % 60 === 0) ? 68 : 52;
                    $x2 = (int)(100 + cos($rad) * $long);
                    $y2 = (int)(100 + sin($rad) * $long);
                    $c = ($a % 60 === 0) ? $sbC : $sbL;
                    imagesetthickness($img, ($a % 60 === 0) ? 4 : 2);
                    imageline($img, 100, 100, $x2, $y2, $c);
                }
                imagesetthickness($img, 1);
                break;
            case 3: // marbled swirl
                $swC = $this->color($img, max(0,$ic1[0]-16), max(0,$ic1[1]-16), max(0,$ic1[2]-16));
                $swL = $this->color($img, min(255,$ic3[0]+16), min(255,$ic3[1]+16), min(255,$ic3[2]+16));
                imagesetthickness($img, 3);
                imagearc($img, 100, 100, 140, 140, 0, 270, $swC);
                imagearc($img, 108, 108, 120, 120, 90, 330, $swL);
                imagearc($img, 92, 100, 100, 100, 200, 60, $swC);
                imagesetthickness($img, 2);
                imagearc($img, 100, 92, 80, 80, 20, 290, $swL);
                imagesetthickness($img, 1);
                break;
            case 4: // cracked glass
                $cgC = $this->color($img, max(0,$ic1[0]-24), max(0,$ic1[1]-24), max(0,$ic1[2]-24));
                $cgL = $this->color($img, min(255,$ic3[0]+14), min(255,$ic3[1]+14), min(255,$ic3[2]+14));
                $cracks = [[100,100,52,44],[52,44,36,52],[100,100,154,42],[154,42,168,54],
                           [100,100,164,110],[164,110,172,130],[100,100,140,156],[100,100,52,164],[52,164,42,178]];
                imagesetthickness($img, 2);
                foreach ($cracks as [$x1,$y1,$x2,$y2]) imageline($img,$x1,$y1,$x2,$y2,$cgC);
                imagesetthickness($img, 1);
                // bright shards
                $this->polygon($img, [100,100, 52,44, 64,58], $cgL);
                $this->polygon($img, [100,100, 154,42, 142,56], $cgL);
                break;
            case 5: // crystalline hexagonal facets
                $fC1 = $this->color($img, min(255,$ic2[0]+16), min(255,$ic2[1]+16), min(255,$ic2[2]+16));
                $fC2 = $this->color($img, max(0,$ic1[0]-16), max(0,$ic1[1]-16), max(0,$ic1[2]-16));
                // draw hexagonal cells
                $hexCenters = [[100,76],[124,90],[124,118],[100,132],[76,118],[76,90],[100,100]];
                foreach ($hexCenters as $i => [$hx,$hy]) {
                    $pts = [];
                    for ($a = 0; $a < 360; $a += 60) {
                        $r = ($i === 6) ? 22 : 26;
                        $pts[] = (int)($hx + cos($a * M_PI/180) * $r);
                        $pts[] = (int)($hy + sin($a * M_PI/180) * $r);
                    }
                    $c = ($i % 2 === 0) ? $fC1 : $fC2;
                    $this->polygon($img, $pts, $c);
                }
                break;
            case 6: // flat plain — just the gradient rings, no extra texture
                break;
        }

        // ── LIMBAL RING ──────────────────────────────────────────────
        $limbalWidths = [3, 6, 10];
        $limbalC = $this->color($img, max(0,$ic1[0]-30), max(0,$ic1[1]-30), max(0,$ic1[2]-30));
        imagesetthickness($img, $limbalWidths[$limbalRing]);
        imagearc($img, 100, 100, 148, 148, 0, 360, $limbalC);
        imagesetthickness($img, 1);

        // ── PUPIL FLAIR (drawn before pupil so pupil sits on top) ────
        switch ($pupilFlair) {
            case 1: // fire aura
                $this->ellipse($img, 100, 100, 72, 72, $this->color($img,255,34,0));
                $this->ellipse($img, 100, 100, 60, 60, $this->color($img,255,68,0));
                $this->ellipse($img, 100, 100, 48, 48, $this->color($img,255,100,0));
                $this->ellipse($img, 100, 100, 38, 38, $this->color($img,255,140,0));
                // flame licks
                foreach ([0,90,180,270] as $fa) {
                    $rad = $fa * M_PI / 180;
                    $fx = (int)(100 + cos($rad) * 34);
                    $fy = (int)(100 + sin($rad) * 34);
                    $this->polygon($img, [
                        (int)(100+cos(($fa-8)*M_PI/180)*30), (int)(100+sin(($fa-8)*M_PI/180)*30),
                        $fx, $fy,
                        (int)(100+cos(($fa+8)*M_PI/180)*30), (int)(100+sin(($fa+8)*M_PI/180)*30)
                    ], $orange);
                }
                break;
            case 2: // electric crackle
                $boltC = $blue;
                $boltL = $blueL;
                imagesetthickness($img, 3);
                $bolts = [[100,100,88,80,92,70,80,54],[100,100,112,80,108,70,120,54],
                          [100,100,78,104,68,100,52,104],[100,100,122,104,132,100,148,104],
                          [100,100,90,118,86,128,78,144]];
                foreach ($bolts as $b) {
                    for ($i = 0; $i < count($b)-2; $i += 2) {
                        imageline($img, $b[$i],$b[$i+1],$b[$i+2],$b[$i+3], $boltC);
                    }
                }
                imagesetthickness($img, 6);
                foreach ($bolts as $b) {
                    imageline($img, $b[0],$b[1],$b[2],$b[3], $boltL);
                }
                imagesetthickness($img, 1);
                // bright core
                $this->ellipse($img, 100, 100, 18, 18, $blueL);
                $this->ellipse($img, 100, 100, 10, 10, $white);
                break;
            case 3: // void consuming
                // tendrils of pure black reaching out
                $voidC = $black;
                $voidGlow = $purpleL;
                $tendrilAngles = [0,40,80,130,180,230,280,320];
                foreach ($tendrilAngles as $ta) {
                    $rad = $ta * M_PI / 180;
                    $tx = (int)(100 + cos($rad) * 62);
                    $ty = (int)(100 + sin($rad) * 62);
                    imagesetthickness($img, 8);
                    imageline($img, 100, 100, $tx, $ty, $voidC);
                    imagesetthickness($img, 2);
                    imageline($img, 100, 100, $tx, $ty, $voidGlow);
                }
                imagesetthickness($img, 1);
                // large consuming pupil base
                $this->ellipse($img, 100, 100, 60, 60, $black);
                break;
            case 4: // divine radiance
                // golden light rays
                $rayC = $gold;
                $rayL = $goldL;
                $rayAngles = [0, 22, 45, 67, 90, 112, 135, 157, 180, 202, 225, 247, 270, 292, 315, 337];
                foreach ($rayAngles as $i => $ra) {
                    $rad = $ra * M_PI / 180;
                    $len = ($i % 2 === 0) ? 68 : 52;
                    $tx = (int)(100 + cos($rad) * $len);
                    $ty = (int)(100 + sin($rad) * $len);
                    imagesetthickness($img, ($i % 2 === 0) ? 3 : 2);
                    imageline($img, 100, 100, $tx, $ty, $rayC);
                }
                imagesetthickness($img, 1);
                // golden glow rings
                $this->ellipse($img, 100, 100, 56, 56, $this->color($img,255,204,0));
                $this->ellipse($img, 100, 100, 42, 42, $this->color($img,255,220,68));
                $this->ellipse($img, 100, 100, 28, 28, $this->color($img,255,238,136));
                break;
            case 5: // toxic melt
                // green acid dissolving outward
                $toxC  = $green;
                $toxL  = $greenL;
                $toxD  = $this->color($img, 0, 136, 0);
                $this->ellipse($img, 100, 100, 60, 60, $this->color($img,0,170,0));
                $this->ellipse($img, 100, 108, 44, 54, $this->color($img,0,200,0));
                $this->ellipse($img, 100, 100, 38, 38, $this->color($img,68,255,0));
                // toxic drip blobs
                foreach ([[100,72,16,12],[86,80,12,10],[114,80,12,10],[78,96,10,8],[122,96,10,8],[100,124,14,22]] as [$tx,$ty,$tw,$th]) {
                    $this->ellipse($img, $tx, $ty, $tw, $th, $toxL);
                }
                break;
        }

        // ── PUPIL ────────────────────────────────────────────────────
        $pupilC = $this->color($img, 6, 4, 8);
        $pupilSize = $this->hash($username, 7, 0, 2); // 0=small, 1=medium, 2=large

        // base pupil sizes
        $pSizes = [24, 34, 46];
        $ps = $pSizes[$pupilSize];

        switch ($pupilShape) {
            case 0: // round
                $this->ellipse($img, 100, 100, $ps, $ps, $pupilC);
                break;
            case 1: // vertical slit
                $this->ellipse($img, 100, 100, (int)($ps*0.35), $ps, $pupilC);
                break;
            case 2: // horizontal slit (goat)
                $this->ellipse($img, 100, 100, $ps, (int)($ps*0.35), $pupilC);
                // rounded ends
                $this->ellipse($img, 100-(int)($ps*0.3), 100, (int)($ps*0.35), (int)($ps*0.35), $pupilC);
                $this->ellipse($img, 100+(int)($ps*0.3), 100, (int)($ps*0.35), (int)($ps*0.35), $pupilC);
                break;
            case 3: // star shaped
                $starPts = [];
                for ($a = 0; $a < 360; $a += 30) {
                    $r = ($a % 60 === 0) ? $ps/2 : $ps/4;
                    $starPts[] = (int)(100 + cos($a * M_PI/180) * $r);
                    $starPts[] = (int)(100 + sin($a * M_PI/180) * $r);
                }
                $this->polygon($img, $starPts, $pupilC);
                break;
            case 4: // heart
                $hps = (int)($ps * 0.6);
                imagefill($img, 0, 0, $scleraC); // not ideal but we just draw heart
                $this->polygon($img, [
                    100, 100-$hps+8,
                    100+$hps, 100-4,
                    100+$hps-2, 100+$hps-8,
                    100, 100+$hps+4,
                    100-$hps+2, 100+$hps-8,
                    100-$hps, 100-4,
                ], $pupilC);
                // round the top lobes
                $this->ellipse($img, 100-(int)($hps*0.5), 100-(int)($hps*0.2), (int)($hps*0.6), (int)($hps*0.6), $pupilC);
                $this->ellipse($img, 100+(int)($hps*0.5), 100-(int)($hps*0.2), (int)($hps*0.6), (int)($hps*0.6), $pupilC);
                // fix: just overdraw a proper heart
                $this->ellipse($img, 100, 100, $ps+4, $ps+4, $irisInner); // clear area
                $this->ellipse($img, 100-(int)($ps*0.28), 100-(int)($ps*0.12), (int)($ps*0.56), (int)($ps*0.56), $pupilC);
                $this->ellipse($img, 100+(int)($ps*0.28), 100-(int)($ps*0.12), (int)($ps*0.56), (int)($ps*0.56), $pupilC);
                $this->polygon($img, [
                    100-(int)($ps*0.5), 100-(int)($ps*0.1),
                    100,                100+(int)($ps*0.6),
                    100+(int)($ps*0.5), 100-(int)($ps*0.1),
                ], $pupilC);
                break;
            case 5: // keyhole
                $this->ellipse($img, 100, 100-(int)($ps*0.15), (int)($ps*0.6), (int)($ps*0.6), $pupilC);
                $this->polygon($img, [
                    100-(int)($ps*0.22), 100-(int)($ps*0.14),
                    100-(int)($ps*0.22), 100+(int)($ps*0.5),
                    100+(int)($ps*0.22), 100+(int)($ps*0.5),
                    100+(int)($ps*0.22), 100-(int)($ps*0.14),
                ], $pupilC);
                break;
            case 6: // hourglass
                $this->polygon($img, [
                    100-(int)($ps*0.5), 100-(int)($ps*0.5),
                    100+(int)($ps*0.5), 100-(int)($ps*0.5),
                    100+(int)($ps*0.15), 100,
                    100+(int)($ps*0.5), 100+(int)($ps*0.5),
                    100-(int)($ps*0.5), 100+(int)($ps*0.5),
                    100-(int)($ps*0.15), 100,
                ], $pupilC);
                break;
            case 7: // fully dilated — huge black circle
                $this->ellipse($img, 100, 100, 130, 130, $pupilC);
                break;
            case 8: // pinpoint — tiny dot
                $this->ellipse($img, 100, 100, 8, 8, $pupilC);
                break;
        }

        // ── CATCH LIGHT ──────────────────────────────────────────────
        switch ($catchLight) {
            case 0: // single natural
                $this->ellipse($img, 76, 74, 22, 22, $white);
                $this->ellipse($img, 72, 70, 12, 12, $white);
                break;
            case 1: // two catch lights
                $this->ellipse($img, 74, 72, 18, 18, $white);
                $this->ellipse($img, 70, 68,  9,  9, $white);
                $this->ellipse($img, 118, 82, 12, 12, $white);
                $this->ellipse($img, 116, 80,  6,  6, $white);
                break;
            case 2: // none / dead eye — no reflections
                break;
            case 3: // scattered sparkles
                foreach ([[72,68,10],[88,62,7],[116,74,8],[68,86,6],[106,68,5]] as [$sx,$sy,$sr]) {
                    $this->ellipse($img, $sx, $sy, $sr, $sr, $white);
                }
                $this->ellipse($img, 70, 66, 5, 5, $white);
                break;
            case 4: // inner glow from pupil — concentric light rings
                $glowC = $this->color($img, min(255,$ic3[0]+60), min(255,$ic3[1]+60), min(255,$ic3[2]+60));
                imagesetthickness($img, 3);
                imagearc($img, 100, 100, 70, 70, 0, 360, $glowC);
                imagearc($img, 100, 100, 52, 52, 0, 360, $this->color($img,min(255,$ic3[0]+80),min(255,$ic3[1]+80),min(255,$ic3[2]+80)));
                imagesetthickness($img, 1);
                $this->ellipse($img, 80, 78, 14, 14, $white);
                break;
        }

        return $img;
    }
}
