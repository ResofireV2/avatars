<?php

namespace Resofire\Avatars\Generator\Style;

class Glitch extends AbstractStyle
{
    public function key(): string { return 'glitch'; }
    public function name(): string { return 'Glitch'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        // ── DARK BG TINTS (near-black with colour cast) ──────────────
        $bgTints = [
            [10, 14, 20],   // void black
            [ 6,  8, 24],   // deep navy
            [ 3, 14,  6],   // terminal green
            [14,  6, 24],   // dark indigo
            [20,  4,  6],   // blood red
            [ 2, 12, 14],   // deep ocean
        ];
        // Neon accents — independent slot from bg
        $accentNeons = [
            [  0, 255, 204],  // cyan
            [255,   0, 170],  // magenta
            [  0, 255,  68],  // acid green
            [255, 238,   0],  // yellow
            [255, 102,   0],  // orange
            [ 68, 221, 255],  // ice blue
        ];

        $eyeType   = $this->hash($username, 0, 0, 3);
        $mouthType = $this->hash($username, 1, 0, 5);
        $glitchFx  = $this->hash($username, 2, 0, 5);
        $headDecor = $this->hash($username, 3, 0, 4);
        $bgIdx     = $this->hash($username, 4, 0, 5);
        $accentIdx = $this->hash($username, 5, 0, 5);
        $scanlines = $this->hash($username, 6, 2, 5);
        $glitchAmt = $this->hash($username, 7, 6, 16);

        [$bgR, $bgG, $bgB] = $bgTints[$bgIdx];
        [$acR, $acG, $acB] = $accentNeons[$accentIdx];

        $bg    = $this->color($img, $bgR, $bgG, $bgB);
        $bgM   = $this->color($img, max(0,$bgR+10), max(0,$bgG+10), max(0,$bgB+10));
        $acc   = $this->color($img, $acR, $acG, $acB);
        $accD  = $this->color($img, (int)($acR*0.55), (int)($acG*0.55), (int)($acB*0.55));
        $white = $this->color($img, 232, 236, 244);
        $black = $this->color($img,   5,   5,  10);
        $redS  = $this->color($img, 255,   0,  68);
        $bluS  = $this->color($img,   0,  68, 255);

        // ── FILL ──────────────────────────────────────────────────────
        $this->rect($img, 0, 0, 200, 200, $bg);

        // ── SCANLINES (subtle on dark) ────────────────────────────────
        $gap = (int)(200 / ($scanlines + 1));
        for ($i = 1; $i <= $scanlines; $i++) {
            $y = $i * $gap;
            $this->rect($img, 0, $y, 200, $y+1, $accD);
        }

        // ── HEAD DECORATION ───────────────────────────────────────────
        switch ($headDecor) {
            case 0: break;
            case 1: // antenna + signal rings
                $this->rect($img, 97, 0, 103, 24, $white);
                $this->ellipse($img, 100, 0, 14, 14, $white);
                imagesetthickness($img, 2);
                imagearc($img, 100, 6, 30, 18, 180, 360, $acc);
                imagearc($img, 100, 6, 50, 28, 180, 360, $acc);
                imagearc($img, 100, 6, 70, 38, 180, 360, $accD);
                imagesetthickness($img, 1);
                break;
            case 2: // broken glitch halo
                imagesetthickness($img, 5);
                imagearc($img, 100, 100, 192, 192, 210, 335, $acc);
                imagearc($img, 100+(int)($glitchAmt/2), 100, 192, 192, 345, 100, $acc);
                imagesetthickness($img, 2);
                imagearc($img, 97, 100, 194, 194, 210, 335, $redS);
                imagearc($img, 103+(int)($glitchAmt/2), 100, 194, 194, 345, 100, $bluS);
                imagesetthickness($img, 1);
                break;
            case 3: // pixel ERR text
                // E
                $ex = 54; $ey = 8;
                $this->rect($img, $ex,    $ey,    $ex+2,  $ey+13, $acc);
                $this->rect($img, $ex,    $ey,    $ex+10, $ey+2,  $acc);
                $this->rect($img, $ex,    $ey+5,  $ex+8,  $ey+7,  $acc);
                $this->rect($img, $ex,    $ey+11, $ex+10, $ey+13, $acc);
                // R
                $ex += 14;
                $this->rect($img, $ex,    $ey,    $ex+2,  $ey+13, $acc);
                $this->rect($img, $ex,    $ey,    $ex+9,  $ey+2,  $acc);
                $this->rect($img, $ex,    $ey+5,  $ex+9,  $ey+7,  $acc);
                $this->rect($img, $ex+7,  $ey,    $ex+9,  $ey+6,  $acc);
                $this->rect($img, $ex+5,  $ey+7,  $ex+11, $ey+13, $acc);
                // R
                $ex += 14;
                $this->rect($img, $ex,    $ey,    $ex+2,  $ey+13, $acc);
                $this->rect($img, $ex,    $ey,    $ex+9,  $ey+2,  $acc);
                $this->rect($img, $ex,    $ey+5,  $ex+9,  $ey+7,  $acc);
                $this->rect($img, $ex+7,  $ey,    $ex+9,  $ey+6,  $acc);
                $this->rect($img, $ex+5,  $ey+7,  $ex+11, $ey+13, $acc);
                break;
            case 4: // binary rain
                $cols   = [18,30,42,54,66,78,90,102,114,126,138,150,162,174];
                $digits = [1,0,1,1,0,0,1,0,1,1,0,1,0,1];
                for ($ci = 0; $ci < count($cols); $ci++) {
                    for ($ri = 0; $ri < 4; $ri++) {
                        $py = $ri * 10 + 4;
                        $dc = ($ri === 0) ? $acc : $accD;
                        $d  = $digits[($ci + $ri) % count($digits)];
                        $px = $cols[$ci];
                        if ($d === 1) {
                            $this->rect($img, $px, $py, $px+3, $py+7, $dc);
                        } else {
                            $this->rect($img, $px,   $py,   $px+5, $py+2,  $dc);
                            $this->rect($img, $px,   $py+3, $px+5, $py+5,  $dc);
                            $this->rect($img, $px,   $py+6, $px+5, $py+8,  $dc);
                            $this->rect($img, $px,   $py,   $px+2, $py+8,  $dc);
                            $this->rect($img, $px+3, $py,   $px+5, $py+8,  $dc);
                        }
                    }
                }
                break;
        }

        // ── EYES ──────────────────────────────────────────────────────
        $eyeY = 90;
        $lx   = 66;
        $rx   = 134;

        switch ($eyeType) {
            case 0: // RGB split
                foreach ([[$lx, $eyeY], [$rx, $eyeY]] as [$ex, $ey]) {
                    $this->ellipse($img, $ex-5, $ey, 50, 50, $redS);
                    $this->ellipse($img, $ex,   $ey, 50, 50, $acc);
                    $this->ellipse($img, $ex+5, $ey, 50, 50, $bluS);
                    $this->ellipse($img, $ex,   $ey,   34, 34, $white);
                    $this->ellipse($img, $ex,   $ey+1, 20, 22, $acc);
                    $this->ellipse($img, $ex,   $ey+2,  9,  9, $black);
                    $this->ellipse($img, $ex-8, $ey-8, 10, 10, $white);
                }
                break;
            case 1: // X eyes
                $this->ellipse($img, $lx, $eyeY, 50, 50, $white);
                $this->ellipse($img, $rx, $eyeY, 50, 50, $white);
                imagesetthickness($img, 9);
                imageline($img, $lx-20, $eyeY-20, $lx+20, $eyeY+20, $acc);
                imageline($img, $lx+20, $eyeY-20, $lx-20, $eyeY+20, $acc);
                imageline($img, $rx-20, $eyeY-20, $rx+20, $eyeY+20, $acc);
                imageline($img, $rx+20, $eyeY-20, $rx-20, $eyeY+20, $acc);
                imagesetthickness($img, 2);
                imageline($img, $lx-21-(int)($glitchAmt/3), $eyeY-20, $lx+19-(int)($glitchAmt/3), $eyeY+20, $redS);
                imageline($img, $rx-19+(int)($glitchAmt/3), $eyeY-20, $rx+21+(int)($glitchAmt/3), $eyeY+20, $bluS);
                imagesetthickness($img, 1);
                break;
            case 2: // star pupils
                foreach ([[$lx, $eyeY], [$rx, $eyeY]] as [$ex, $ey]) {
                    $this->ellipse($img, $ex-4, $ey, 52, 52, $redS);
                    $this->ellipse($img, $ex+4, $ey, 52, 52, $bluS);
                    $this->ellipse($img, $ex,   $ey, 50, 50, $white);
                    $this->ellipse($img, $ex,   $ey+1, 34, 34, $acc);
                    $starPts = [];
                    for ($a = 0; $a < 360; $a += 36) {
                        $r = ($a % 72 === 0) ? 22 : 11;
                        $starPts[] = (int)($ex + cos(($a-90)*M_PI/180) * $r);
                        $starPts[] = (int)($ey + sin(($a-90)*M_PI/180) * $r);
                    }
                    $this->polygon($img, $starPts, $white);
                }
                break;
            case 3: // normal + corruption block
                foreach ([[$lx, $eyeY], [$rx, $eyeY]] as [$ex, $ey]) {
                    $this->ellipse($img, $ex, $ey,    50, 50, $white);
                    $this->ellipse($img, $ex, $ey+1,  34, 34, $acc);
                    $this->ellipse($img, $ex, $ey+2,  16, 16, $black);
                    $this->ellipse($img, $ex-8, $ey-8, 10, 10, $white);
                }
                $bx = $this->hash($username, 20, 46, 56);
                $this->rect($img, $bx, $eyeY-8, $bx+$glitchAmt*2, $eyeY-2, $acc);
                $this->rect($img, 200-$bx-$glitchAmt*2, $eyeY+2, 200-$bx, $eyeY+8, $accD);
                break;
        }

        // ── GLITCH EFFECT ─────────────────────────────────────────────
        $sliceY  = $this->hash($username, 10, 72, 108);
        $sliceY2 = $this->hash($username, 11, 112, 148);

        switch ($glitchFx) {
            case 0: // wide data slice
                $shift = $glitchAmt + 8;
                $this->rect($img, 0,      $sliceY, 200,    $sliceY+20, $bg);
                $this->rect($img, $shift, $sliceY, 200,    $sliceY+20, $bgM);
                imagesetthickness($img, 2);
                imageline($img, 0, $sliceY,    200, $sliceY,    $redS);
                imageline($img, 0, $sliceY+20, 200, $sliceY+20, $bluS);
                $this->rect($img, 0, $sliceY2, 200-$shift, $sliceY2+8, $bgM);
                imageline($img, 0, $sliceY2,   200, $sliceY2,   $bluS);
                imageline($img, 0, $sliceY2+8, 200, $sliceY2+8, $redS);
                imagesetthickness($img, 1);
                break;
            case 1: // chromatic aberration
                $half = (int)($glitchAmt / 2);
                imagesetthickness($img, 2);
                imageline($img, 0, $sliceY,    200, $sliceY,    $redS);
                imageline($img, 0, $sliceY+4,  200, $sliceY+4,  $bluS);
                imageline($img, 0, $sliceY2,   200, $sliceY2,   $bluS);
                imageline($img, 0, $sliceY2+4, 200, $sliceY2+4, $redS);
                imagesetthickness($img, 1);
                // face-wide colour ghost bands
                for ($gy = 56; $gy < 160; $gy += 16) {
                    $this->rect($img, 0,     $gy, $half,     $gy+2, $redS);
                    $this->rect($img, 200-$half, $gy, 200,   $gy+2, $bluS);
                }
                break;
            case 2: // static patch over one eye
                $patchX = ($this->hash($username, 12, 0, 1) === 0) ? 18 : 110;
                $patchW = 72;
                $patchH = 72;
                $patchY = 56;
                for ($py = 0; $py < $patchH; $py += 4) {
                    for ($px = 0; $px < $patchW; $px += 4) {
                        $pidx = $this->hash($username, 100 + $py * 20 + $px, 0, 5);
                        $pc = match($pidx) {
                            0 => $white,
                            1 => $black,
                            2 => $redS,
                            3 => $bluS,
                            4 => $acc,
                            default => $bgM,
                        };
                        $this->rect($img, $patchX+$px, $patchY+$py, $patchX+$px+3, $patchY+$py+3, $pc);
                    }
                }
                imagesetthickness($img, 2);
                imagearc($img, $patchX+$patchW/2, $patchY+$patchH/2, $patchW+4, $patchH+4, 0, 360, $acc);
                imagesetthickness($img, 1);
                break;
            case 3: // pixel sort — vertical colour strips
                for ($px = 0; $px < 200; $px += 8) {
                    $len    = $this->hash($username, 40 + (int)($px/8), 60, 160);
                    $startY = $this->hash($username, 41 + (int)($px/8), 10, 40);
                    $pidx   = $this->hash($username, 42 + (int)($px/8), 0, 3);
                    $stripC = match($pidx) { 0 => $acc, 1 => $accD, 2 => $bgM, default => $bg };
                    $this->rect($img, $px, $startY, $px+5, $startY+$len, $stripC);
                }
                // clear top so face is readable
                $this->rect($img, 0, 0, 200, 50, $bg);
                break;
            case 4: // interlace failure
                for ($iy = 54; $iy < 162; $iy += 5) {
                    if ($iy % 10 === 0) {
                        $shift = (int)($glitchAmt / 2);
                        $this->rect($img, 0, $iy, $shift, $iy+3, $bgM);
                        imageline($img, 0, $iy, 200, $iy, ($iy % 20 === 0) ? $redS : $bluS);
                    }
                }
                break;
            case 5: // full static
                for ($sy = 0; $sy < 200; $sy += 4) {
                    for ($sx = 0; $sx < 200; $sx += 4) {
                        $sidx = $this->hash($username, 200 + (int)($sy/4) * 50 + (int)($sx/4), 0, 7);
                        $sc = match($sidx) {
                            0, 1 => $white, 2 => $black, 3 => $redS,
                            4 => $bluS, 5 => $acc, 6 => $bgM, default => $bg,
                        };
                        $this->rect($img, $sx, $sy, $sx+3, $sy+3, $sc);
                    }
                }
                // faint eye ghost through static
                $faintAcc = $this->colorA($img, $acR, $acG, $acB, 40);
                $this->ellipse($img, $lx, $eyeY, 54, 54, $faintAcc);
                $this->ellipse($img, $rx, $eyeY, 54, 54, $faintAcc);
                break;
        }

        // ── MOUTH ─────────────────────────────────────────────────────
        $mY = 152;
        $mL = 44;
        $mR = 156;

        switch ($mouthType) {
            case 0: // flatline + blip
                $this->rect($img, $mL, $mY, $mR, $mY+4, $acc);
                $bx = $this->hash($username, 15, 74, 126);
                $this->rect($img, $bx-4, $mY-12, $bx+4, $mY+16, $acc);
                $this->rect($img, $bx-7, $mY-8,  $bx+7, $mY+12, $acc);
                $this->rect($img, $mL-3, $mY+1, $mR-3, $mY+3, $redS);
                $this->rect($img, $mL+3, $mY+3, $mR+3, $mY+5, $bluS);
                break;
            case 1: // sine wave
                $pts = [];
                for ($sx = $mL; $sx <= $mR; $sx += 6) {
                    $pts[] = $sx;
                    $pts[] = (int)($mY + sin(($sx-$mL) / 14.0 * M_PI) * 13);
                }
                imagesetthickness($img, 4);
                for ($i = 0; $i < count($pts)-2; $i += 2) imageline($img, $pts[$i], $pts[$i+1], $pts[$i+2], $pts[$i+3], $acc);
                imagesetthickness($img, 2);
                for ($i = 0; $i < count($pts)-2; $i += 2) {
                    imageline($img, $pts[$i]-3, $pts[$i+1], $pts[$i+2]-3, $pts[$i+3], $redS);
                    imageline($img, $pts[$i]+3, $pts[$i+1], $pts[$i+2]+3, $pts[$i+3], $bluS);
                }
                imagesetthickness($img, 1);
                break;
            case 2: // frequency spike
                $this->rect($img, $mL, $mY, $mR, $mY+4, $acc);
                $spx = $this->hash($username, 16, 80, 120);
                $sph = $this->hash($username, 17, 22, 40);
                $spPts = [$mL,$mY, $spx-14,$mY, $spx-6,$mY-$sph, $spx,$mY+(int)($sph/3), $spx+6,$mY-$sph, $spx+14,$mY, $mR,$mY];
                imagesetthickness($img, 3);
                for ($i = 0; $i < count($spPts)-2; $i += 2) imageline($img, $spPts[$i],$spPts[$i+1],$spPts[$i+2],$spPts[$i+3], $acc);
                imagesetthickness($img, 1);
                $this->rect($img, $mL-3, $mY+1, $mR-3, $mY+3, $redS);
                $this->rect($img, $mL+3, $mY+3, $mR+3, $mY+5, $bluS);
                break;
            case 3: // equalizer bars
                $bHeights = [16, 26, 34, 22, 30, 38, 24, 28, 20, 14];
                $floor = $mY + 18;
                $this->rect($img, $mL-2, $floor+2, $mR+2, $floor+4, $accD);
                for ($bi = 0; $bi < 10; $bi++) {
                    $bh = $bHeights[$bi];
                    $bx = $mL + $bi * 12;
                    $bc = ($bi % 2 === 0) ? $acc : $accD;
                    $this->rect($img, $bx, $floor-$bh, $bx+9, $floor, $bc);
                }
                break;
            case 4: // corrupted waveform
                $midX = $this->hash($username, 18, 86, 114);
                $offset = $this->hash($username, 19, 10, 22);
                $pts = [];
                for ($sx = $mL; $sx <= $midX; $sx += 6) {
                    $pts[] = $sx;
                    $pts[] = (int)($mY + sin(($sx-$mL) / 14.0 * M_PI) * 13);
                }
                imagesetthickness($img, 4);
                for ($i = 0; $i < count($pts)-2; $i += 2) imageline($img, $pts[$i],$pts[$i+1],$pts[$i+2],$pts[$i+3], $acc);
                $this->rect($img, $midX, $mY-12, $midX+14, $mY+18, $bgM);
                $pts2 = [];
                for ($sx = $midX+14; $sx <= $mR; $sx += 6) {
                    $pts2[] = $sx;
                    $pts2[] = (int)($mY - $offset + sin(($sx-$mL) / 14.0 * M_PI) * 13);
                }
                for ($i = 0; $i < count($pts2)-2; $i += 2) {
                    imageline($img, $pts2[$i],$pts2[$i+1],$pts2[$i+2],$pts2[$i+3], $acc);
                }
                imagesetthickness($img, 2);
                for ($i = 0; $i < count($pts2)-2; $i += 2) imageline($img, $pts2[$i]+3,$pts2[$i+1],$pts2[$i+2]+3,$pts2[$i+3], $redS);
                imagesetthickness($img, 1);
                break;
            case 5: // sawtooth
                $toothW = 9;
                imagesetthickness($img, 4);
                $px = $mL; $up = true;
                while ($px < $mR) {
                    $nx = min($px+$toothW, $mR);
                    imageline($img, $px, $up?$mY+14:$mY-14, $nx, $up?$mY-14:$mY+14, $acc);
                    $px = $nx; $up = !$up;
                }
                imagesetthickness($img, 2);
                $px = $mL; $up = true;
                while ($px < $mR) {
                    $nx = min($px+$toothW, $mR);
                    imageline($img, $px-3, $up?$mY+14:$mY-14, $nx-3, $up?$mY-14:$mY+14, $redS);
                    imageline($img, $px+3, $up?$mY+14:$mY-14, $nx+3, $up?$mY-14:$mY+14, $bluS);
                    $px = $nx; $up = !$up;
                }
                imagesetthickness($img, 1);
                break;
        }

        return $img;
    }
}
