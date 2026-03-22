<?php

namespace Resofire\Avatars\Generator\Style;

class Android extends AbstractStyle
{
    public function key(): string { return 'android'; }
    public function name(): string { return 'Android'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        // Background colors — steel, slate, gunmetal tones
        $bgColors = [
            [42, 58, 74],   // slate blue
            [42, 42, 42],   // gunmetal
            [26, 58, 58],   // dark teal
            [58, 42, 16],   // bronze
            [58, 58, 68],   // blue-gray
            [34, 34, 16],   // olive dark
            [48, 36, 56],   // dark mauve
            [20, 42, 28],   // dark green-steel
            [52, 36, 36],   // dark rust
            [28, 28, 48],   // dark indigo
        ];

        // Accent colors — one vivid color per unit
        $accentColors = [
            [68,  136, 255], // blue
            [255,  51,  51], // red
            [0,   204, 170], // teal
            [204, 119,   0], // amber
            [170, 170, 255], // lavender
            [255, 102,   0], // orange
            [0,   220,  80], // green
            [220,   0, 220], // magenta
            [0,   200, 255], // cyan
            [255, 200,   0], // yellow
        ];

        // Status light colors
        $statusColors = [
            [0,   255, 136], // green
            [255,  80,  80], // red
            [0,   200, 255], // cyan
            [255, 200,   0], // yellow
            [180, 100, 255], // purple
            [255, 150,   0], // orange
            [0,   255, 200], // aqua
            [255, 255, 128], // pale yellow
        ];

        [$br, $bg2, $bb]   = $this->pick($username, 0, $bgColors);
        [$ar, $ag, $ab]    = $this->pick($username, 1, $accentColors);
        [$sr, $sg, $sb]    = $this->pick($username, 2, $statusColors);

        // Slot picks
        $topType     = $this->hash($username, 3, 0, 6);
        $foreType    = $this->hash($username, 4, 0, 6);
        $eyeType     = $this->hash($username, 5, 0, 6);
        $mouthType   = $this->hash($username, 6, 0, 6);
        $neckType    = $this->hash($username, 7, 0, 6);

        // Colors
        $bg     = $this->color($img, $br, $bg2, $bb);
        $bgL    = $this->color($img, min(255,$br+18), min(255,$bg2+18), min(255,$bb+18));
        $bgLL   = $this->color($img, min(255,$br+36), min(255,$bg2+36), min(255,$bb+36));
        $bgD    = $this->color($img, max(0,$br-14), max(0,$bg2-14), max(0,$bb-14));
        $panel  = $this->color($img, max(0,$br-20), max(0,$bg2-20), max(0,$bb-20));
        $acc    = $this->color($img, $ar, $ag, $ab);
        $accD   = $this->color($img, (int)($ar*0.35), (int)($ag*0.35), (int)($ab*0.35));
        $accL   = $this->color($img, min(255,(int)($ar*1.3)), min(255,(int)($ag*1.3)), min(255,(int)($ab*1.3)));
        $status = $this->color($img, $sr, $sg, $sb);
        $statusL= $this->color($img, min(255,$sr+60), min(255,$sg+60), min(255,$sb+60));

        // Fill background
        $this->rect($img, 0, 0, 200, 200, $bg);

        // ── SLOT 1: TOP ───────────────────────────────────────────────
        switch ($topType) {
            case 0: // single center antenna
                $this->rect($img, 94, 0, 106, 38, $bgLL);
                $this->ellipse($img, 100, 8, 18, 18, $status);
                $this->ellipse($img, 100, 8, 10, 10, $statusL);
                break;
            case 1: // twin antennae
                $this->rect($img, 72, 0, 80, 30, $bgLL);
                $this->rect($img, 120, 0, 128, 24, $bgLL);
                $this->ellipse($img, 76,  5, 14, 14, $status);
                $this->ellipse($img, 124, 5, 12, 12, $status);
                $this->ellipse($img, 76,  5, 7, 7, $statusL);
                $this->ellipse($img, 124, 5, 6, 6, $statusL);
                break;
            case 2: // dome head
                $this->ellipse($img, 100, 42, 140, 70, $bgL);
                $this->ellipse($img, 100, 44, 110, 50, $acc);
                $this->ellipse($img, 100, 44, 110, 50, $bgD);
                // slight glow shimmer
                $this->ellipse($img, 100, 34, 90, 30, $acc);
                $this->ellipse($img, 100, 34, 88, 28, $bgL);
                break;
            case 3: // exposed circuit board top
                $this->rect($img, 0, 0, 200, 44, $bgD);
                $this->rect($img, 30, 14, 170, 16, $acc);
                $this->rect($img, 30, 14, 32, 30, $acc);
                $this->rect($img, 168, 14, 170, 26, $acc);
                $this->rect($img, 70, 0, 72, 14, $acc);
                $this->rect($img, 58, 6, 72, 8, $acc);
                $this->rect($img, 128, 0, 130, 14, $acc);
                $this->rect($img, 128, 8, 142, 10, $acc);
                $this->ellipse($img, 30,  14, 8, 8, $acc);
                $this->ellipse($img, 58,   6, 6, 6, $accL);
                $this->ellipse($img, 170, 14, 8, 8, $acc);
                $this->ellipse($img, 142,  8, 6, 6, $accL);
                break;
            case 4: // sensor array bar across top
                $this->rect($img, 0, 0, 200, 36, $bgD);
                $this->rect($img, 4, 4, 196, 32, $panel);
                foreach ([20, 36, 52, 76, 100, 124, 148, 164, 180] as $i => $sx) {
                    $r = ($sx === 100) ? 9 : 6;
                    $col = ($sx === 100) ? $accL : $acc;
                    $this->ellipse($img, $sx, 18, $r*2, $r*2, $col);
                    if ($sx === 100) {
                        $this->ellipse($img, $sx, 18, 8, 8, $statusL);
                    }
                }
                break;
            case 5: // cooling vent fins
                $this->rect($img, 0, 0, 200, 40, $bgD);
                foreach (range(20, 180, 16) as $fx) {
                    $this->rect($img, $fx, 2, $fx+10, 36, $bgLL);
                }
                $this->rect($img, 0, 0, 200, 2, $acc);
                $this->rect($img, 0, 38, 200, 40, $acc);
                break;
            case 6: // flush — just status light, no protruding top
                $this->ellipse($img, 100, 18, 18, 18, $status);
                $this->ellipse($img, 100, 18, 10, 10, $statusL);
                break;
        }

        // ── SLOT 2: FOREHEAD BAR ─────────────────────────────────────
        // Always draw a forehead zone so the face has structure
        $foreY = 54;
        $this->rect($img, 20, $foreY, 180, $foreY+20, $panel);

        switch ($foreType) {
            case 0: // plain status bar (original)
                $this->rect($img, 22, $foreY+2, 178, $foreY+18, $bgD);
                $this->rect($img, 24, $foreY+5, 176, $foreY+9, $acc);
                $this->rect($img, 24, $foreY+12, 140, $foreY+15, $acc);
                break;
            case 1: // LED dot matrix
                $this->rect($img, 22, $foreY+2, 178, $foreY+18, $bgD);
                $dotCols = [28, 36, 44, 52, 60, 68, 76, 84, 92, 100, 108, 116, 124, 132, 140, 148, 156, 164, 172];
                foreach ($dotCols as $i => $dx) {
                    $on1 = $this->hash($username, 20+$i, 0, 1);
                    $on2 = $this->hash($username, 40+$i, 0, 1);
                    $this->ellipse($img, $dx, $foreY+8,  5, 5, $on1 ? $acc : $accD);
                    $this->ellipse($img, $dx, $foreY+14, 5, 5, $on2 ? $acc : $accD);
                }
                break;
            case 2: // manufacturer / model plate
                $this->rect($img, 22, $foreY+2, 178, $foreY+18, $bgD);
                $this->rect($img, 24, $foreY+4, 80,  $foreY+10, $acc);
                $this->rect($img, 84, $foreY+4, 110, $foreY+10, $accD);
                $this->rect($img, 114,$foreY+4, 176, $foreY+10, $acc);
                $this->rect($img, 24, $foreY+12, 100, $foreY+15, $accD);
                $this->rect($img, 104,$foreY+12, 176, $foreY+16, $acc);
                break;
            case 3: // damage crack through forehead
                $this->rect($img, 22, $foreY+2, 178, $foreY+18, $bgD);
                $this->rect($img, 24, $foreY+6, 176, $foreY+10, $acc);
                // crack polyline
                imagesetthickness($img, 2);
                imageline($img, 110, $foreY,    108, $foreY+8,  $bgD);
                imageline($img, 108, $foreY+8,  114, $foreY+14, $bgD);
                imageline($img, 114, $foreY+14, 110, $foreY+20, $bgD);
                imageline($img, 108, $foreY+8,  102, $foreY+12, $bgD);
                imagesetthickness($img, 1);
                break;
            case 4: // reinforced plate with corner bolts
                $this->rect($img, 22, $foreY+2, 178, $foreY+18, $bgLL);
                $this->ellipse($img, 30,  $foreY+6,  8, 8, $bgD);
                $this->ellipse($img, 170, $foreY+6,  8, 8, $bgD);
                $this->ellipse($img, 30,  $foreY+16, 8, 8, $bgD);
                $this->ellipse($img, 170, $foreY+16, 8, 8, $bgD);
                $this->ellipse($img, 30,  $foreY+6,  4, 4, $bgLL);
                $this->ellipse($img, 170, $foreY+6,  4, 4, $bgLL);
                $this->ellipse($img, 30,  $foreY+16, 4, 4, $bgLL);
                $this->ellipse($img, 170, $foreY+16, 4, 4, $bgLL);
                $this->rect($img, 38, $foreY+9, 162, $foreY+13, $panel);
                break;
            case 5: // full-width sensor strip (replaces forehead bar visually)
                $this->rect($img, 20, $foreY, 180, $foreY+20, $bgD);
                $this->rect($img, 22, $foreY+2, 178, $foreY+18, $panel);
                for ($i = 0; $i < 3; $i++) {
                    $this->rect($img, 24, $foreY+4+$i*5, 176, $foreY+6+$i*5, $acc);
                }
                $this->ellipse($img, 80,  $foreY+10, 18, 10, $accD);
                $this->ellipse($img, 120, $foreY+10, 18, 10, $accD);
                $this->ellipse($img, 80,  $foreY+10, 10, 6, $acc);
                $this->ellipse($img, 120, $foreY+10, 10, 6, $acc);
                break;
            case 6: // minimal / clean — just a thin line
                $this->rect($img, 22, $foreY+9, 178, $foreY+11, $acc);
                $this->ellipse($img, 100, $foreY+10, 14, 14, $bgL);
                $this->ellipse($img, 100, $foreY+10, 8,  8,  $status);
                break;
        }

        // ── SLOT 3: EYES ─────────────────────────────────────────────
        $eyeY = 82; $eyeH = 34; $lx = 64; $rx = 136;
        // Eye panel background
        $this->rect($img, 20, $eyeY, 180, $eyeY+$eyeH, $panel);
        $this->rect($img, 22, $eyeY+2, 178, $eyeY+$eyeH-2, $bgD);
        // Left and right eye sockets
        $this->rect($img, 24,  $eyeY+4, 100, $eyeY+$eyeH-4, $panel);
        $this->rect($img, 102, $eyeY+4, 178, $eyeY+$eyeH-4, $panel);

        switch ($eyeType) {
            case 0: // scan lines
                foreach ([$lx-38, $rx-38] as $ex) {
                    $heights = [4, 3, 2];
                    foreach ($heights as $i => $th) {
                        $this->rect($img, $ex+2, $eyeY+6+$i*9, $ex+74, $eyeY+6+$i*9+$th, $acc);
                    }
                    $this->ellipse($img, $ex+38, $eyeY+17, 26, 16, $accD);
                    $this->ellipse($img, $ex+38, $eyeY+17, 14, 8,  $acc);
                }
                break;
            case 1: // targeting reticle
                foreach ([$lx, $rx] as $cx) {
                    $this->ellipse($img, $cx, $eyeY+17, 24, 24, $panel);
                    $this->ellipse($img, $cx, $eyeY+17, 20, 20, $acc);
                    $this->ellipse($img, $cx, $eyeY+17, 13, 13, $bgD);
                    $this->ellipse($img, $cx, $eyeY+17, 7,  7,  $acc);
                    imageline($img, $cx, $eyeY+4,  $cx, $eyeY+9,  $acc);
                    imageline($img, $cx, $eyeY+25, $cx, $eyeY+30, $acc);
                    imageline($img, $cx-12, $eyeY+17, $cx-7,  $eyeY+17, $acc);
                    imageline($img, $cx+7,  $eyeY+17, $cx+12, $eyeY+17, $acc);
                    $this->ellipse($img, $cx, $eyeY+17, 4, 4, $accL);
                }
                break;
            case 2: // camera lens rings
                foreach ([$lx, $rx] as $cx) {
                    $this->ellipse($img, $cx, $eyeY+17, 26, 26, $bgD);
                    $this->ellipse($img, $cx, $eyeY+17, 22, 22, $bgLL);
                    $this->ellipse($img, $cx, $eyeY+17, 17, 17, $bgD);
                    $this->ellipse($img, $cx, $eyeY+17, 12, 12, $bgLL);
                    $this->ellipse($img, $cx, $eyeY+17, 8,  8,  $acc);
                    $this->ellipse($img, $cx, $eyeY+17, 4,  4,  $accL);
                    $this->ellipse($img, $cx, $eyeY+17, 2,  2,  $bgD);
                    imageline($img, $cx, $eyeY+4,  $cx, $eyeY+7,  $acc);
                    imageline($img, $cx, $eyeY+27, $cx, $eyeY+30, $acc);
                    imageline($img, $cx-13, $eyeY+17, $cx-10, $eyeY+17, $acc);
                    imageline($img, $cx+10, $eyeY+17, $cx+13, $eyeY+17, $acc);
                }
                break;
            case 3: // segmented bars (original style)
                foreach ([$lx-38, $rx-38] as $ex) {
                    foreach ([2, 18, 32] as $i => $ox) {
                        $opacity = $i === 0 ? 1.0 : ($i === 1 ? 0.6 : 0.3);
                        $this->rect($img, $ex+$ox, $eyeY+4, $ex+$ox+14, $eyeY+$eyeH-4, $panel);
                        $c = ($opacity >= 1.0) ? $acc : (($opacity >= 0.6) ? $accD : $this->color($img, (int)($ar*0.2), (int)($ag*0.2), (int)($ab*0.2)));
                        $this->rect($img, $ex+$ox+2, $eyeY+6, $ex+$ox+12, $eyeY+$eyeH-6, $c);
                    }
                }
                break;
            case 4: // radar sweep
                foreach ([$lx, $rx] as $cx) {
                    $this->ellipse($img, $cx, $eyeY+17, 26, 26, $bgD);
                    $this->ellipse($img, $cx, $eyeY+17, 20, 20, $acc);
                    $this->ellipse($img, $cx, $eyeY+17, 14, 14, $bgD);
                    $this->ellipse($img, $cx, $eyeY+17, 8,  8,  $bgLL);
                    // sweep arm
                    imageline($img, $cx, $eyeY+17, $cx+9, $eyeY+9, $acc);
                    $this->ellipse($img, $cx, $eyeY+17, 4, 4, $acc);
                    // blip
                    $this->ellipse($img, $cx+8, $eyeY+10, 6, 6, $accL);
                }
                break;
            case 5: // compound / insect eye
                foreach ([$lx-36, $rx-36] as $ex) {
                    $positions = [
                        [8,8],[16,8],[24,8],[32,8],[40,8],[48,8],[56,8],[64,8],[70,8],
                        [4,16],[12,16],[20,16],[28,16],[36,16],[44,16],[52,16],[60,16],[68,16],[74,16],
                        [8,24],[16,24],[24,24],[32,24],[40,24],[48,24],[56,24],[64,24],[70,24],
                    ];
                    foreach ($positions as $i => [$ox, $oy]) {
                        $on = $this->hash($username, 80+$i, 0, 3);
                        $c = $on === 0 ? $accD : ($on === 1 ? $acc : ($on === 2 ? $accL : $bgLL));
                        $this->ellipse($img, $ex+$ox, $eyeY+4+$oy, 5, 5, $c);
                    }
                }
                break;
            case 6: // holographic diamond projection
                foreach ([$lx, $rx] as $cx) {
                    $cy = $eyeY+17;
                    $this->polygon($img, [$cx, $cy-13, $cx+13, $cy, $cx, $cy+13, $cx-13, $cy], $bgD);
                    $this->polygon($img, [$cx, $cy-10, $cx+10, $cy, $cx, $cy+10, $cx-10, $cy], $acc);
                    $this->polygon($img, [$cx, $cy-6,  $cx+6,  $cy, $cx, $cy+6,  $cx-6,  $cy], $accD);
                    imageline($img, $cx-13, $cy, $cx+13, $cy, $acc);
                    imageline($img, $cx, $cy-13, $cx, $cy+13, $acc);
                    $this->ellipse($img, $cx, $cy, 4, 4, $accL);
                }
                break;
        }

        // ── SLOT 4: MOUTH ─────────────────────────────────────────────
        $mY = 128; $mH = 26;
        $this->rect($img, 20, $mY, 180, $mY+$mH, $panel);
        $this->rect($img, 22, $mY+2, 178, $mY+$mH-2, $bgD);

        switch ($mouthType) {
            case 0: // speaker grille (original)
                foreach ([26, 44, 62, 80, 98, 116, 134, 152, 170] as $gx) {
                    if ($gx+14 > 176) break;
                    $this->rect($img, $gx, $mY+2, $gx+14, $mY+$mH-2, $bgLL);
                }
                $this->rect($img, 22, $mY+5,  178, $mY+7,  $acc);
                $this->rect($img, 22, $mY+13, 178, $mY+15, $acc);
                $this->rect($img, 22, $mY+21, 178, $mY+23, $acc);
                break;
            case 1: // voicebox waveform display
                $this->rect($img, 22, $mY+2, 178, $mY+$mH-2, $panel);
                $pts = [24,141, 36,131, 48,151, 60,131, 72,151, 84,131, 96,151, 108,131, 120,151, 132,131, 144,151, 156,131, 168,141, 176,141];
                imagesetthickness($img, 3);
                for ($i = 0; $i < count($pts)-2; $i+=2) {
                    imageline($img, $pts[$i], $pts[$i+1], $pts[$i+2], $pts[$i+3], $acc);
                }
                imagesetthickness($img, 1);
                break;
            case 2: // data port connector
                $this->rect($img, 60, $mY+2, 140, $mY+$mH-2, $bgLL);
                $this->rect($img, 62, $mY+4, 138, $mY+$mH-4, $panel);
                for ($i = 0; $i < 9; $i++) {
                    $this->rect($img, 64+$i*9, $mY+7, 64+$i*9+7, $mY+$mH-7, $acc);
                }
                break;
            case 3: // welded / sealed shut with weld dots
                $this->rect($img, 22, $mY+10, 178, $mY+16, $bgLL);
                $this->rect($img, 22, $mY+11, 178, $mY+14, $panel);
                foreach ([28, 44, 60, 76, 92, 108, 124, 140, 156, 172] as $wx) {
                    $this->ellipse($img, $wx, $mY+13, 8, 8, $acc);
                    $this->ellipse($img, $wx, $mY+13, 4, 4, $accL);
                }
                break;
            case 4: // happy arc
                imagesetthickness($img, 5);
                imagearc($img, 100, $mY+38, 120, 54, 210, 330, $acc);
                imagesetthickness($img, 1);
                break;
            case 5: // segmented jaw panels
                foreach ([24, 52, 76, 100, 124, 148, 170] as $jx) {
                    if ($jx+20 > 178) break;
                    $this->rect($img, $jx, $mY+2, $jx+20, $mY+$mH-2, $bgLL);
                    $this->rect($img, $jx+2, $mY+4, $jx+18, $mY+8, $acc);
                }
                break;
            case 6: // mechanical gears
                $this->rect($img, 22, $mY+2, 178, $mY+$mH-2, $bgL);
                foreach ([42, 72, 100, 128, 158] as $gx) {
                    $r = ($gx === 100) ? 12 : 9;
                    $this->ellipse($img, $gx, $mY+14, $r*2, $r*2, $bgLL);
                    $this->ellipse($img, $gx, $mY+14, ($r-3)*2, ($r-3)*2, $acc);
                    $this->ellipse($img, $gx, $mY+14, ($r-6 > 0 ? ($r-6)*2 : 2), ($r-6 > 0 ? ($r-6)*2 : 2), $bgL);
                }
                break;
        }

        // ── SLOT 5: NECK / CHEST ──────────────────────────────────────
        $nY = 162;
        switch ($neckType) {
            case 0: break; // nothing
            case 1: // serial number plate
                $this->rect($img, 28, $nY, 172, $nY+28, $bgLL);
                $this->rect($img, 30, $nY+2, 170, $nY+26, $panel);
                $this->rect($img, 32, $nY+6,  90, $nY+10, $acc);
                $this->rect($img, 94, $nY+6, 168, $nY+10, $accD);
                $this->rect($img, 32, $nY+16, 168, $nY+19, $accD);
                $this->rect($img, 32, $nY+20, 100, $nY+23, $acc);
                break;
            case 2: // power cell battery indicator
                $this->rect($img, 28, $nY, 172, $nY+26, $bgLL);
                $this->rect($img, 30, $nY+2, 170, $nY+24, $panel);
                $charge = $this->hash($username, 90, 1, 4); // 1-4 bars filled
                foreach ([0,1,2,3] as $i) {
                    $bx = 34 + $i * 32;
                    $filled = $i < $charge;
                    $col = $filled ? $acc : $bgLL;
                    $this->rect($img, $bx, $nY+5, $bx+26, $nY+21, $col);
                }
                $this->rect($img, 162, $nY+9, 168, $nY+17, $bgLL);
                break;
            case 3: // cooling vents
                $this->rect($img, 0, $nY, 200, 200, $bgD);
                foreach ([30, 46, 62, 78, 94, 110, 126, 142, 158, 174] as $vx) {
                    $this->rect($img, $vx, $nY+4, $vx+12, $nY+22, $bgLL);
                }
                $this->rect($img, 0, $nY, 200, $nY+3, $acc);
                break;
            case 4: // embossed hex logo
                $cx2 = 100; $cy2 = $nY+14;
                $this->polygon($img, [$cx2, $cy2-14, $cx2+12, $cy2-7, $cx2+12, $cy2+7, $cx2, $cy2+14, $cx2-12, $cy2+7, $cx2-12, $cy2-7], $bgLL);
                $this->polygon($img, [$cx2, $cy2-10, $cx2+9, $cy2-5, $cx2+9, $cy2+5, $cx2, $cy2+10, $cx2-9, $cy2+5, $cx2-9, $cy2-5], $panel);
                $this->ellipse($img, $cx2, $cy2, 10, 10, $acc);
                $this->ellipse($img, $cx2, $cy2, 5,  5,  $accL);
                break;
            case 5: // spine cable bundles
                $this->rect($img, 0, $nY, 200, 200, $bgL);
                imagesetthickness($img, 4);
                $cables = [[$acc, 80], [$accL, 96], [$acc, 112], [$accD, 128]];
                foreach ($cables as [$col, $cx2]) {
                    $offset = $this->hash($username, 91+$cx2, -14, 14);
                    imageline($img, $cx2, $nY, $cx2+$offset, $nY+20, $col);
                    imageline($img, $cx2+$offset, $nY+20, $cx2, 200, $col);
                }
                imagesetthickness($img, 1);
                foreach ([80, 96, 112, 128] as $cx2) {
                    $this->ellipse($img, $cx2, $nY+2, 10, 10, $bgLL);
                }
                break;
            case 6: // hazard warning stripes
                $this->rect($img, 0, $nY, 200, 200, $bgD);
                $stripeW = 28;
                for ($i = 0; $i * $stripeW < 200 + $stripeW; $i++) {
                    if ($i % 2 === 0) {
                        $this->polygon($img, [
                            $i*$stripeW, $nY,
                            ($i+1)*$stripeW, $nY,
                            ($i+1)*$stripeW - 10, 200,
                            $i*$stripeW - 10, 200
                        ], $acc);
                    }
                }
                $this->rect($img, 0, $nY, 200, $nY+3, $bgD);
                break;
        }

        return $img;
    }
}
