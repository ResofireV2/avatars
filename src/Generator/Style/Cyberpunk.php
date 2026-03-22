<?php

namespace Resofire\Avatars\Generator\Style;

class Cyberpunk extends AbstractStyle
{
    public function key(): string { return 'cyberpunk'; }
    public function name(): string { return 'Cyberpunk'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        $bgColors = [
            [26,  0,  32], [0,  26,  26], [0,  17,  51], [26,  0,  85],
            [0,  26,   0], [34,  0,   0], [26, 17,   0], [0,  10,  26],
            [17,  0,  26], [0,  26,  17], [26,  8,   0], [0,   0,  26],
            [17, 17,   0], [26,  0,  17],
        ];
        $accentColors = [
            [255,   0, 204], [0,   255,  68], [0,   136, 255], [170,   0, 255],
            [0,   220, 204], [255, 238,   0], [255,  68,   0], [0,   255, 170],
            [255,   0,  68], [136, 255,   0],
        ];

        [$br, $bg2, $bb]   = $this->pick($username, 0, $bgColors);
        [$ar, $ag, $ab]    = $this->pick($username, 1, $accentColors);
        [$a2r, $a2g, $a2b] = $this->pick($username, 2, $accentColors);
        $topType            = $this->hash($username, 3, 0, 6);
        $eyeType            = $this->hash($username, 4, 0, 6);
        $cheekType          = $this->hash($username, 5, 0, 6);
        $mouthType          = $this->hash($username, 6, 0, 6);
        $collarType         = $this->hash($username, 7, 0, 6);

        $bg    = $this->color($img, $br, $bg2, $bb);
        $bgL   = $this->color($img, min(255,$br+18), min(255,$bg2+18), min(255,$bb+18));
        $bgLL  = $this->color($img, min(255,$br+36), min(255,$bg2+36), min(255,$bb+36));
        $acc   = $this->color($img, $ar, $ag, $ab);
        $accD  = $this->color($img, (int)($ar*0.4), (int)($ag*0.4), (int)($ab*0.4));
        $acc2  = $this->color($img, $a2r, $a2g, $a2b);

        $this->rect($img, 0, 0, 200, 200, $bg);

        // ── TOP ──────────────────────────────────────────────────────
        switch ($topType) {
            case 0: // mohawk
                $this->rect($img, 88, 0, 112, 44, $acc);
                $this->ellipse($img, 100, 8, 30, 18, $acc);
                break;
            case 1: // twin antennae
                $this->rect($img, 72, 0, 78, 36, $bgLL);
                $this->rect($img, 96, 0, 102, 28, $bgLL);
                $this->ellipse($img, 75, 5, 14, 14, $acc);
                $this->ellipse($img, 99, 5, 10, 10, $acc2);
                break;
            case 2: // neural jack cluster
                foreach ([52, 68, 84, 100, 116, 132, 148] as $i => $jx) {
                    $h = $this->hash($username, 40+$i, 8, 26);
                    $this->rect($img, $jx-3, 0, $jx+3, $h, $bgLL);
                    $this->ellipse($img, $jx, $h, 10, 10, ($i % 2 === 0) ? $acc : $acc2);
                }
                break;
            case 3: // hood / cowl
                $this->rect($img, 0, 0, 200, 70, $bgL);
                $this->ellipse($img, 100, 62, 186, 44, $bgLL);
                break;
            case 4: // circuit tattoo
                $this->rect($img, 44, 28, 156, 31, $acc);
                $this->rect($img, 44, 18, 47, 31, $acc);
                $this->rect($img, 44, 18, 64, 21, $acc);
                $this->rect($img, 153, 18, 156, 31, $acc);
                $this->rect($img, 136, 18, 156, 21, $acc);
                $this->rect($img, 98, 0, 102, 28, $acc);
                $this->rect($img, 84, 12, 102, 15, $acc);
                $this->ellipse($img, 44,  18, 8, 8, $acc);
                $this->ellipse($img, 64,  18, 6, 6, $acc2);
                $this->ellipse($img, 156, 18, 8, 8, $acc);
                $this->ellipse($img, 136, 18, 6, 6, $acc2);
                $this->ellipse($img, 84,  12, 6, 6, $acc2);
                break;
            case 5: // spiky implants
                foreach ([52, 72, 90, 100, 110, 128, 148] as $i => $sx) {
                    $h = $this->hash($username, 50+$i, 18, 38);
                    $this->polygon($img, [$sx-6, 50, $sx, 50-$h, $sx+6, 50], $bgLL);
                }
                $this->rect($img, 44, 46, 156, 54, $bgLL);
                break;
            case 6: // data cable looping
                imagesetthickness($img, 5);
                imageline($img, 60, 0, 60, 32, $acc);
                imagearc($img, 80, 32, 40, 32, 180, 360, $acc);
                imageline($img, 100, 16, 100, 38, $acc);
                imagearc($img, 118, 38, 36, 28, 180, 360, $acc);
                imageline($img, 136, 24, 136, 0, $acc);
                imagesetthickness($img, 1);
                $this->ellipse($img, 60,  5, 12, 12, $acc2);
                $this->ellipse($img, 136, 5, 12, 12, $acc2);
                break;
        }

        // ── EYE PANEL ────────────────────────────────────────────────
        $eyeY = 88; $eyeH = 38; $lx = 66; $rx = 134;
        $this->rect($img, 20, $eyeY, 180, $eyeY+$eyeH, $bgL);
        $this->rect($img, 22, $eyeY+2, 178, $eyeY+$eyeH-2, $bgLL);

        switch ($eyeType) {
            case 0: // scan-line visor
                $this->rect($img, 24, $eyeY+4, 90, $eyeY+$eyeH-4, $bg);
                $this->rect($img, 110, $eyeY+4, 176, $eyeY+$eyeH-4, $bg);
                for ($i = 0; $i < 4; $i++) {
                    $this->rect($img, 26, $eyeY+6+$i*7, 88,  $eyeY+8+$i*7, $acc);
                    $this->rect($img, 112,$eyeY+6+$i*7, 174, $eyeY+8+$i*7, $acc);
                }
                $this->ellipse($img, $lx, $eyeY+19, 30, 20, $accD);
                $this->ellipse($img, $lx, $eyeY+19, 14, 10, $acc);
                $this->ellipse($img, $rx, $eyeY+19, 30, 20, $accD);
                $this->ellipse($img, $rx, $eyeY+19, 14, 10, $acc);
                break;
            case 1: // targeting reticle
                foreach ([$lx, $rx] as $cx) {
                    $this->rect($img, $cx-16, $eyeY+4, $cx+16, $eyeY+$eyeH-4, $bg);
                    $this->ellipse($img, $cx, $eyeY+19, 24, 24, $acc);
                    $this->ellipse($img, $cx, $eyeY+19, 16, 16, $bg);
                    $this->ellipse($img, $cx, $eyeY+19, 8,  8,  $acc);
                    imageline($img, $cx, $eyeY+4,  $cx, $eyeY+11, $acc);
                    imageline($img, $cx, $eyeY+27, $cx, $eyeY+34, $acc);
                    imageline($img, $cx-16, $eyeY+19, $cx-9,  $eyeY+19, $acc);
                    imageline($img, $cx+9,  $eyeY+19, $cx+16, $eyeY+19, $acc);
                    $this->ellipse($img, $cx, $eyeY+19, 4, 4, $acc2);
                }
                break;
            case 2: // EQ bars visualizer
                foreach ([$lx-28, $rx-28] as $startX) {
                    $heights = [12, 20, 30, 22, 16, 28, 18];
                    foreach ($heights as $i => $h) {
                        $bx  = $startX + $i * 8;
                        $col = ($i === 2 || $i === 5) ? $acc2 : $acc;
                        $this->rect($img, $bx, $eyeY+$eyeH-4-$h, $bx+6, $eyeY+$eyeH-4, $col);
                    }
                }
                break;
            case 3: // camera lens
                foreach ([$lx, $rx] as $cx) {
                    $this->rect($img, $cx-16, $eyeY+4, $cx+16, $eyeY+$eyeH-4, $bg);
                    $this->ellipse($img, $cx, $eyeY+19, 26, 26, $bgLL);
                    $this->ellipse($img, $cx, $eyeY+19, 20, 20, $bg);
                    $this->ellipse($img, $cx, $eyeY+19, 14, 14, $bgLL);
                    $this->ellipse($img, $cx, $eyeY+19, 8,  8,  $acc);
                    $this->ellipse($img, $cx, $eyeY+19, 4,  4,  $acc2);
                    $this->ellipse($img, $cx, $eyeY+19, 2,  2,  $bg);
                    imageline($img, $cx, $eyeY+4,  $cx, $eyeY+8,  $acc);
                    imageline($img, $cx, $eyeY+30, $cx, $eyeY+34, $acc);
                    imageline($img, $cx-16, $eyeY+19, $cx-10, $eyeY+19, $acc);
                    imageline($img, $cx+10, $eyeY+19, $cx+16, $eyeY+19, $acc);
                }
                break;
            case 4: // binary scrolling
                foreach ([$lx-16, $rx-16] as $ex) {
                    $this->rect($img, $ex, $eyeY+4, $ex+32, $eyeY+$eyeH-4, $bg);
                    for ($col = 0; $col < 5; $col++) {
                        $bx = $ex + 2 + $col * 6;
                        for ($row = 0; $row < 5; $row++) {
                            if ($this->hash($username, 60+$col*7+$row, 0, 1)) {
                                $this->rect($img, $bx, $eyeY+6+$row*5, $bx+4, $eyeY+9+$row*5, $acc);
                            }
                        }
                    }
                }
                break;
            case 5: // radar sweep
                foreach ([$lx, $rx] as $cx) {
                    $this->rect($img, $cx-16, $eyeY+4, $cx+16, $eyeY+$eyeH-4, $bg);
                    $this->ellipse($img, $cx, $eyeY+19, 24, 24, $bgLL);
                    $this->ellipse($img, $cx, $eyeY+19, 16, 16, $bg);
                    $this->ellipse($img, $cx, $eyeY+19, 8,  8,  $bgLL);
                    imageline($img, $cx, $eyeY+19, $cx+10, $eyeY+11, $acc);
                    $this->ellipse($img, $cx, $eyeY+19, 4, 4, $acc);
                    $this->ellipse($img, $cx+9, $eyeY+12, 6, 6, $acc2);
                }
                break;
            case 6: // glitch / RGB fragmented
                $rC = $this->color($img, 255, 0, 0);
                $gC = $this->color($img, 0, 255, 0);
                $bC = $this->color($img, 0, 0, 255);
                foreach ([$lx, $rx] as $cx) {
                    $this->rect($img, $cx-16, $eyeY+4, $cx+16, $eyeY+$eyeH-4, $bg);
                    $this->ellipse($img, $cx-3, $eyeY+19, 20, 20, $rC);
                    $this->ellipse($img, $cx,   $eyeY+19, 20, 20, $gC);
                    $this->ellipse($img, $cx+3, $eyeY+19, 20, 20, $bC);
                    $this->ellipse($img, $cx,   $eyeY+19, 10, 10, $acc);
                    $this->ellipse($img, $cx,   $eyeY+19, 4,  4,  $bg);
                    $gshift = $this->hash($username, 70, 3, 10);
                    $this->rect($img, $cx-16, $eyeY+11, $cx-16+$gshift*2, $eyeY+14, $acc);
                    $this->rect($img, $cx+4,  $eyeY+24, $cx+16, $eyeY+27, $acc2);
                }
                break;
        }

        // ── CHEEK MARKINGS ────────────────────────────────────────────
        $cheekY = 118;
        switch ($cheekType) {
            case 0: break;
            case 1: // barcode
                foreach ([16, 148] as $bx) {
                    $widths = [2,1,3,1,2,3,1,2,1,3,2,1];
                    $x = $bx;
                    foreach ($widths as $w) {
                        $this->rect($img, $x, $cheekY, $x+$w-1, $cheekY+20, $acc);
                        $x += $w + 1;
                    }
                }
                break;
            case 2: // warpaint diagonal
                imagesetthickness($img, 4);
                imageline($img, 16, $cheekY,    38, $cheekY+24, $acc);
                imageline($img, 22, $cheekY,    44, $cheekY+24, $acc2);
                imageline($img, 184,$cheekY,    162,$cheekY+24, $acc);
                imageline($img, 178,$cheekY,    156,$cheekY+24, $acc2);
                imagesetthickness($img, 1);
                break;
            case 3: // circuit traces
                foreach ([[16, 1], [148, -1]] as [$sx, $d]) {
                    $ex = $sx + $d * 28;
                    $this->rect($img, min($sx,$ex), $cheekY,    max($sx,$ex), $cheekY+2,  $acc);
                    $this->rect($img, $sx,          $cheekY,    $sx+$d*2,     $cheekY+16, $acc);
                    $this->rect($img, $sx,          $cheekY+14, $sx+$d*20,    $cheekY+16, $acc);
                    $this->rect($img, $sx+$d*18,    $cheekY+14, $sx+$d*20,    $cheekY+26, $acc);
                    $this->ellipse($img, $sx,        $cheekY,    6, 6, $acc2);
                    $this->ellipse($img, $sx+$d*20,  $cheekY+26, 5, 5, $acc2);
                }
                break;
            case 4: // subdermal bumps
                foreach ([16, 148] as $sx) {
                    for ($i = 0; $i < 3; $i++) {
                        $bx = $sx + $i * 12;
                        $by = $cheekY + 4 + ($i % 2) * 8;
                        $this->ellipse($img, $bx, $by, 14, 10, $bgLL);
                        $this->ellipse($img, $bx, $by, 6,  4,  $acc);
                    }
                }
                break;
            case 5: // geometric tattoo
                foreach ([[16, 44], [156, 184]] as [$x1, $x2]) {
                    $y2 = $cheekY + 22;
                    imageline($img, $x1, $cheekY, $x2, $cheekY, $acc);
                    imageline($img, $x1, $y2,     $x2, $y2,     $acc);
                    imageline($img, $x1, $cheekY, $x1, $y2,     $acc);
                    imageline($img, $x2, $cheekY, $x2, $y2,     $acc);
                    imageline($img, $x1, $cheekY, $x2, $y2,     $acc2);
                }
                break;
            case 6: // scar lines
                imagesetthickness($img, 3);
                imageline($img, 16,  $cheekY,    32,  $cheekY+24, $accD);
                imageline($img, 22,  $cheekY+4,  30,  $cheekY+18, $acc);
                imageline($img, 184, $cheekY,    168, $cheekY+24, $accD);
                imageline($img, 178, $cheekY+4,  170, $cheekY+18, $acc);
                imagesetthickness($img, 1);
                break;
        }

        // ── MOUTH ─────────────────────────────────────────────────────
        $mY = 142;
        $this->rect($img, 28, $mY, 172, $mY+24, $bgL);
        $this->rect($img, 30, $mY+2, 170, $mY+22, $bg);

        switch ($mouthType) {
            case 0: // waveform
                $pts = [32,154, 44,144, 56,164, 68,144, 80,164, 92,144, 104,164, 116,144, 128,164, 140,144, 152,164, 164,154, 168,154];
                imagesetthickness($img, 3);
                for ($i = 0; $i < count($pts)-2; $i+=2) {
                    imageline($img, $pts[$i], $pts[$i+1], $pts[$i+2], $pts[$i+3], $acc);
                }
                imagesetthickness($img, 1);
                break;
            case 1: // speaker grille
                foreach ([34, 50, 66, 82, 98, 114, 130, 146, 162] as $gx) {
                    if ($gx + 12 > 168) break;
                    $this->rect($img, $gx, $mY+2, $gx+12, $mY+22, $bgLL);
                }
                $this->rect($img, 30, $mY+5,  170, $mY+7,  $acc);
                $this->rect($img, 30, $mY+12, 170, $mY+14, $acc);
                $this->rect($img, 30, $mY+19, 170, $mY+21, $acc);
                break;
            case 2: // respirator
                $this->rect($img, 24, $mY-4, 176, $mY+30, $bgLL);
                $this->rect($img, 26, $mY-2, 174, $mY+28, $bg);
                $this->ellipse($img, 30,  $mY+12, 22, 30, $bgLL);
                $this->ellipse($img, 170, $mY+12, 22, 30, $bgLL);
                $this->ellipse($img, 30,  $mY+12, 12, 20, $bg);
                $this->ellipse($img, 170, $mY+12, 12, 20, $bg);
                for ($i = 0; $i < 3; $i++) {
                    $this->rect($img, 44, $mY+2+$i*7, 156, $mY+4+$i*7, $bgLL);
                }
                break;
            case 3: // data port
                $this->rect($img, 64, $mY+2, 136, $mY+22, $bgL);
                $this->rect($img, 66, $mY+4, 134, $mY+20, $bgLL);
                for ($i = 0; $i < 8; $i++) {
                    $this->rect($img, 68+$i*8, $mY+7, 68+$i*8+6, $mY+17, $acc);
                }
                break;
            case 4: // exposed gears
                $this->rect($img, 30, $mY+2, 170, $mY+22, $bgL);
                foreach ([46, 78, 100, 122, 154] as $gx) {
                    $r = ($gx === 100) ? 12 : 9;
                    $this->ellipse($img, $gx, $mY+12, $r*2, $r*2, $bgLL);
                    $this->ellipse($img, $gx, $mY+12, ($r-3)*2, ($r-3)*2, $acc);
                    $this->ellipse($img, $gx, $mY+12, max(2,($r-6)*2), max(2,($r-6)*2), $bgL);
                }
                break;
            case 5: // stitched flat
                $this->rect($img, 30, $mY+10, 170, $mY+14, $bgLL);
                for ($sx = 34; $sx < 168; $sx += 12) {
                    $this->rect($img, $sx, $mY+4, $sx+3, $mY+20, $acc);
                }
                break;
            case 6: // smirk arc
                imagesetthickness($img, 4);
                imagearc($img, 124, $mY+32, 108, 46, 208, 332, $acc);
                imagesetthickness($img, 1);
                break;
        }

        // ── COLLAR ────────────────────────────────────────────────────
        $cY = 170;
        switch ($collarType) {
            case 0: break;
            case 1: // armor collar with lights
                $this->rect($img, 0, $cY, 200, 200, $bgLL);
                $this->rect($img, 0, $cY, 200, $cY+4, $acc);
                $this->rect($img, 4,   $cY+4, 96,  200, $bgL);
                $this->rect($img, 104, $cY+4, 196, 200, $bgL);
                foreach ([16, 36, 56, 76, 124, 144, 164, 184] as $lx2) {
                    $this->ellipse($img, $lx2, $cY+16, 10, 10, $acc);
                    $this->ellipse($img, $lx2, $cY+16,  5,  5, $acc2);
                }
                break;
            case 2: // exposed wiring
                $this->rect($img, 0, $cY, 200, 200, $bgL);
                $wireAccents = [$acc, $acc2, $this->color($img, 255,255,0), $acc, $acc2];
                imagesetthickness($img, 3);
                for ($w = 0; $w < 5; $w++) {
                    $wc = $wireAccents[$w];
                    $sx2 = 20 + $w * 32;
                    $cp = $sx2 + $this->hash($username, 80+$w, -12, 12);
                    imageline($img, $sx2, $cY, $cp, $cY+22, $wc);
                    imageline($img, $cp, $cY+22, $sx2+8, 200, $wc);
                }
                imagesetthickness($img, 1);
                break;
            case 3: // turtleneck ribbed
                $this->rect($img, 0, $cY, 200, 200, $bgLL);
                for ($i = 0; $i < 5; $i++) {
                    $this->rect($img, 0, $cY+$i*6, 200, $cY+$i*6+3, $bgL);
                }
                $this->rect($img, 0, $cY, 200, $cY+3, $acc);
                break;
            case 4: // spine implants
                $this->rect($img, 0, $cY, 200, 200, $bgL);
                foreach ([180, 188, 196] as $iy) {
                    $this->ellipse($img, 100, $iy, 16, 11, $bgLL);
                    $this->ellipse($img, 100, $iy,  8,  6, $acc);
                }
                break;
            case 5: // status display
                $this->rect($img, 0,  $cY, 200, 200, $bgLL);
                $this->rect($img, 28, $cY+5, 172, $cY+26, $bg);
                $this->rect($img, 30, $cY+7, 92,  $cY+24, $bgL);
                $this->rect($img, 96, $cY+7, 170, $cY+24, $bgL);
                foreach ([38, 50, 62, 74] as $dx) {
                    $this->ellipse($img, $dx, $cY+15, 8, 8, $acc);
                }
                $this->rect($img, 100, $cY+11, 134, $cY+19, $bg);
                $this->rect($img, 102, $cY+13, 124, $cY+17, $acc);
                $this->rect($img, 128, $cY+13, 162, $cY+17, $acc2);
                break;
            case 6: // studded collar
                $this->rect($img, 0, $cY, 200, $cY+30, $bgLL);
                $this->rect($img, 0, $cY,    200, $cY+3,  $acc);
                $this->rect($img, 0, $cY+27, 200, $cY+30, $acc);
                foreach ([10, 28, 46, 64, 82, 100, 118, 136, 154, 172, 190] as $sx) {
                    $this->ellipse($img, $sx, $cY+15, 12, 12, $bgL);
                    $this->ellipse($img, $sx, $cY+15,  6,  6, $bgLL);
                    $this->ellipse($img, $sx, $cY+15,  3,  3, $acc);
                }
                break;
        }

        return $img;
    }
}
