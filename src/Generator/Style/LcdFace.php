<?php

namespace Resofire\Avatars\Generator\Style;

class LcdFace extends AbstractStyle
{
    public function key(): string { return 'lcd-face'; }
    public function name(): string { return 'LCD Face'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        // ── PALETTES ─────────────────────────────────────────────────
        $bgColors = [
            [8,  18,  8],   // terminal green
            [8,   8, 24],   // phosphor blue
            [20,  8, 28],   // purple screen
            [24,  6,  6],   // red screen
            [20, 14,  0],   // amber screen
            [0,  18, 18],   // teal screen
            [16, 16, 16],   // white/grey
            [4,  12, 20],   // midnight blue
        ];
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

        // ── SLOTS ─────────────────────────────────────────────────────
        $screenIdx  = $this->hash($username, 0, 0, 7);   // 8 screens
        $scanCount  = $this->hash($username, 1, 3, 7);   // 5 scanline counts
        $expression = $this->hash($username, 2, 0, 7);   // 8 expressions
        $pixelSize  = $this->hash($username, 3, 0, 2);   // 3 sizes: 6, 10, 16
        $browType   = $this->hash($username, 4, 0, 3);   // 4 brow types
        $statusBar  = $this->hash($username, 5, 0, 6);   // 7 status indicators
        $bezelType  = $this->hash($username, 6, 0, 4);   // 5 bezel styles
        $glitchLine = $this->hash($username, 7, 0, 4);   // 5 glitch overlays

        [$br, $bg2, $bb] = $bgColors[$screenIdx];
        [$pr, $pg2, $pb] = $pixelColors[$screenIdx];

        $bgC  = $this->color($img, $br,  $bg2,  $bb);
        $px   = $this->color($img, $pr,  $pg2,  $pb);
        $pxD  = $this->color($img, (int)($pr*0.45), (int)($pg2*0.45), (int)($pb*0.45));
        $scan = $this->color($img, min(255,$br+8), min(255,$bg2+8), min(255,$bb+8));
        $bezelC = $this->color($img, min(255,$br+14), min(255,$bg2+14), min(255,$bb+14));

        $p = [6, 10, 16][$pixelSize]; // pixel block size

        // ── FILL ─────────────────────────────────────────────────────
        $this->rect($img, 0, 0, 200, 200, $bgC);

        // ── SCANLINES ────────────────────────────────────────────────
        $gap = (int)(200 / $scanCount);
        for ($i = 0; $i < $scanCount; $i++) {
            $this->rect($img, 0, $i * $gap, 200, $i * $gap + 1, $scan);
        }

        // ── BEZEL ────────────────────────────────────────────────────
        switch ($bezelType) {
            case 0: // plain corner dots (current)
                $this->rect($img, 14, 14, 186, 186, $bezelC);
                $this->rect($img, 18, 18, 182, 182, $bgC);
                $cornerC = $pxD;
                $this->ellipse($img,  24,  24, 8, 8, $cornerC);
                $this->ellipse($img, 176,  24, 8, 8, $cornerC);
                $this->ellipse($img,  24, 176, 8, 8, $cornerC);
                $this->ellipse($img, 176, 176, 8, 8, $cornerC);
                break;
            case 1: // pixel border — checkered single-pixel edge
                $this->rect($img, 14, 14, 186, 186, $bezelC);
                $this->rect($img, 18, 18, 182, 182, $bgC);
                for ($i = 14; $i < 186; $i += 4) {
                    $this->rect($img, $i, 14, $i+2, 16, $px);
                    $this->rect($img, $i, 184, $i+2, 186, $px);
                    $this->rect($img, 14, $i, 16, $i+2, $px);
                    $this->rect($img, 184, $i, 186, $i+2, $px);
                }
                break;
            case 2: // rounded screen bezel — thick arc
                $this->rect($img, 10, 10, 190, 190, $bezelC);
                imagefilledellipse($img, 20, 20, 20, 20, $bgC);
                imagefilledellipse($img, 180, 20, 20, 20, $bgC);
                imagefilledellipse($img, 20, 180, 20, 20, $bgC);
                imagefilledellipse($img, 180, 180, 20, 20, $bgC);
                $this->rect($img, 16, 16, 184, 184, $bgC);
                // recut the rounded corners
                $this->rect($img, 10, 10, 20, 20, $bgC);
                $this->rect($img, 180, 10, 190, 20, $bgC);
                $this->rect($img, 10, 180, 20, 190, $bgC);
                $this->rect($img, 180, 180, 190, 190, $bgC);
                imagefilledellipse($img, 24, 24, 24, 24, $bezelC);
                imagefilledellipse($img, 176, 24, 24, 24, $bezelC);
                imagefilledellipse($img, 24, 176, 24, 24, $bezelC);
                imagefilledellipse($img, 176, 176, 24, 24, $bezelC);
                $this->rect($img, 18, 18, 182, 182, $bgC);
                break;
            case 3: // antenna at top
                $this->rect($img, 14, 14, 186, 186, $bezelC);
                $this->rect($img, 18, 18, 182, 182, $bgC);
                $this->ellipse($img,  24,  24, 8, 8, $pxD);
                $this->ellipse($img, 176,  24, 8, 8, $pxD);
                $this->ellipse($img,  24, 176, 8, 8, $pxD);
                $this->ellipse($img, 176, 176, 8, 8, $pxD);
                // antenna
                $this->rect($img, 97, 0, 103, 18, $bezelC);
                $this->ellipse($img, 100, 0, 10, 10, $px);
                break;
            case 4: // power bar at top
                $this->rect($img, 14, 14, 186, 186, $bezelC);
                $this->rect($img, 18, 18, 182, 182, $bgC);
                $this->ellipse($img,  24,  24, 8, 8, $pxD);
                $this->ellipse($img, 176,  24, 8, 8, $pxD);
                $this->ellipse($img,  24, 176, 8, 8, $pxD);
                $this->ellipse($img, 176, 176, 8, 8, $pxD);
                // indicator strip at very top of screen
                $this->rect($img, 18, 18, 182, 26, $pxD);
                $this->rect($img, 20, 20, 80, 24, $px);
                break;
        }

        // ── EYEBROWS ─────────────────────────────────────────────────
        // brow positions adapt to pixel size
        $bY = 52; // brow Y
        $lbX = 48; $rbX = 118; $bW = (int)($p * 3.5);
        switch ($browType) {
            case 0: break; // none
            case 1: // flat — horizontal bar
                $this->rect($img, $lbX, $bY, $lbX+$bW, $bY+$p, $px);
                $this->rect($img, $rbX, $bY, $rbX+$bW, $bY+$p, $px);
                break;
            case 2: // angled angry — inner end lower
                $this->rect($img, $lbX,      $bY,    $lbX+$p,    $bY+$p,   $px);
                $this->rect($img, $lbX+$p,   $bY,    $lbX+$p*2,  $bY+$p,   $px);
                $this->rect($img, $lbX+$p*2, $bY+$p, $lbX+$p*3,  $bY+$p*2, $px);
                $this->rect($img, $rbX,      $bY+$p, $rbX+$p,    $bY+$p*2, $px);
                $this->rect($img, $rbX+$p,   $bY,    $rbX+$p*2,  $bY+$p,   $px);
                $this->rect($img, $rbX+$p*2, $bY,    $rbX+$p*3,  $bY+$p,   $px);
                break;
            case 3: // raised — single pixel dot high up
                $this->rect($img, $lbX+$p, $bY-$p, $lbX+$p*2, $bY, $px);
                $this->rect($img, $rbX+$p, $bY-$p, $rbX+$p*2, $bY, $px);
                break;
        }

        // ── EXPRESSION ───────────────────────────────────────────────
        // Eye and mouth positions scaled by pixel size
        $lEyeX = 48;  $rEyeX = 118;
        $eyeY  = 72;  $mouthY = 136;

        switch ($expression) {
            case 0: // ^_^ happy
                $this->rect($img, $lEyeX,      $eyeY,    $lEyeX+$p,    $eyeY+$p,    $px);
                $this->rect($img, $lEyeX+$p,   $eyeY-$p, $lEyeX+$p*2,  $eyeY,       $px);
                $this->rect($img, $lEyeX+$p*2, $eyeY,    $lEyeX+$p*3,  $eyeY+$p,    $px);
                $this->rect($img, $rEyeX,      $eyeY,    $rEyeX+$p,    $eyeY+$p,    $px);
                $this->rect($img, $rEyeX+$p,   $eyeY-$p, $rEyeX+$p*2,  $eyeY,       $px);
                $this->rect($img, $rEyeX+$p*2, $eyeY,    $rEyeX+$p*3,  $eyeY+$p,    $px);
                // smile
                $this->rect($img, 44,  $mouthY,    54,  $mouthY+$p,    $px);
                $this->rect($img, 54,  $mouthY+$p, 64,  $mouthY+$p*2,  $px);
                $this->rect($img, 64,  $mouthY+$p*2, 136, $mouthY+$p*2, $px);
                $this->rect($img, 136, $mouthY+$p, 146, $mouthY+$p*2,  $px);
                $this->rect($img, 146, $mouthY,    156, $mouthY+$p,    $px);
                break;
            case 1: // -- neutral
                $this->rect($img, $lEyeX, $eyeY, $lEyeX+$p*4, $eyeY+$p, $px);
                $this->rect($img, $rEyeX, $eyeY, $rEyeX+$p*4, $eyeY+$p, $px);
                $this->rect($img, 54, $mouthY+$p, 146, $mouthY+$p*2, $px);
                break;
            case 2: // O_O surprised
                // left O
                $this->rect($img, $lEyeX,      $eyeY-$p, $lEyeX+$p*3,  $eyeY,       $px);
                $this->rect($img, $lEyeX,      $eyeY,    $lEyeX+$p,    $eyeY+$p*2,  $px);
                $this->rect($img, $lEyeX+$p*2, $eyeY,    $lEyeX+$p*3,  $eyeY+$p*2,  $px);
                $this->rect($img, $lEyeX,      $eyeY+$p*2,$lEyeX+$p*3, $eyeY+$p*3,  $px);
                // right O
                $this->rect($img, $rEyeX,      $eyeY-$p, $rEyeX+$p*3,  $eyeY,       $px);
                $this->rect($img, $rEyeX,      $eyeY,    $rEyeX+$p,    $eyeY+$p*2,  $px);
                $this->rect($img, $rEyeX+$p*2, $eyeY,    $rEyeX+$p*3,  $eyeY+$p*2,  $px);
                $this->rect($img, $rEyeX,      $eyeY+$p*2,$rEyeX+$p*3, $eyeY+$p*3,  $px);
                // small o mouth
                $this->rect($img, 88, $mouthY,    112, $mouthY+$p,    $px);
                $this->rect($img, 84, $mouthY+$p, 90,  $mouthY+$p*2,  $px);
                $this->rect($img, 110,$mouthY+$p, 116, $mouthY+$p*2,  $px);
                $this->rect($img, 88, $mouthY+$p*2,112, $mouthY+$p*3, $px);
                break;
            case 3: // >_< squint angry
                $this->rect($img, $lEyeX,      $eyeY-$p, $lEyeX+$p,    $eyeY,       $px);
                $this->rect($img, $lEyeX+$p,   $eyeY,    $lEyeX+$p*2,  $eyeY+$p,    $px);
                $this->rect($img, $lEyeX,      $eyeY+$p, $lEyeX+$p,    $eyeY+$p*2,  $px);
                $this->rect($img, $rEyeX+$p*2, $eyeY-$p, $rEyeX+$p*3,  $eyeY,       $px);
                $this->rect($img, $rEyeX+$p,   $eyeY,    $rEyeX+$p*2,  $eyeY+$p,    $px);
                $this->rect($img, $rEyeX+$p*2, $eyeY+$p, $rEyeX+$p*3,  $eyeY+$p*2,  $px);
                // grin
                $this->rect($img, 44,  $mouthY,    54,  $mouthY+$p,    $px);
                $this->rect($img, 54,  $mouthY+$p, 146, $mouthY+$p*2,  $px);
                $this->rect($img, 146, $mouthY,    156, $mouthY+$p,    $px);
                break;
            case 4: // UwU
                // U eyes
                $this->rect($img, $lEyeX,      $eyeY-$p, $lEyeX+$p,    $eyeY+$p,    $px);
                $this->rect($img, $lEyeX+$p,   $eyeY,    $lEyeX+$p*2,  $eyeY+$p,    $px);
                $this->rect($img, $lEyeX+$p*2, $eyeY-$p, $lEyeX+$p*3,  $eyeY+$p,    $px);
                $this->rect($img, $rEyeX,      $eyeY-$p, $rEyeX+$p,    $eyeY+$p,    $px);
                $this->rect($img, $rEyeX+$p,   $eyeY,    $rEyeX+$p*2,  $eyeY+$p,    $px);
                $this->rect($img, $rEyeX+$p*2, $eyeY-$p, $rEyeX+$p*3,  $eyeY+$p,    $px);
                // w mouth
                for ($wi = 0; $wi < 8; $wi++) {
                    $wx = 44 + $wi * $p * 2;
                    $wy = ($wi % 2 === 0) ? $mouthY : $mouthY + $p;
                    $this->rect($img, $wx, $wy, $wx+$p*2, $wy+$p, $px);
                }
                break;
            case 5: // X_X
                $this->rect($img, $lEyeX,      $eyeY-$p, $lEyeX+$p,    $eyeY,       $px);
                $this->rect($img, $lEyeX+$p*2, $eyeY-$p, $lEyeX+$p*3,  $eyeY,       $px);
                $this->rect($img, $lEyeX+$p,   $eyeY,    $lEyeX+$p*2,  $eyeY+$p,    $px);
                $this->rect($img, $lEyeX,      $eyeY+$p, $lEyeX+$p,    $eyeY+$p*2,  $px);
                $this->rect($img, $lEyeX+$p*2, $eyeY+$p, $lEyeX+$p*3,  $eyeY+$p*2,  $px);
                $this->rect($img, $rEyeX,      $eyeY-$p, $rEyeX+$p,    $eyeY,       $px);
                $this->rect($img, $rEyeX+$p*2, $eyeY-$p, $rEyeX+$p*3,  $eyeY,       $px);
                $this->rect($img, $rEyeX+$p,   $eyeY,    $rEyeX+$p*2,  $eyeY+$p,    $px);
                $this->rect($img, $rEyeX,      $eyeY+$p, $rEyeX+$p,    $eyeY+$p*2,  $px);
                $this->rect($img, $rEyeX+$p*2, $eyeY+$p, $rEyeX+$p*3,  $eyeY+$p*2,  $px);
                // teeth bar
                $this->rect($img, 44, $mouthY, 156, $mouthY+$p*2, $px);
                for ($ti = 44; $ti < 156; $ti += $p*2) {
                    $this->rect($img, $ti+2, $mouthY+2, $ti+$p*2-2, $mouthY+$p*2-2, $bgC);
                }
                break;
            case 6: // :D big grin
                $this->rect($img, $lEyeX+$p,   $eyeY-$p, $lEyeX+$p*2,  $eyeY,       $px);
                $this->rect($img, $lEyeX+$p,   $eyeY,    $lEyeX+$p*2,  $eyeY+$p,    $px);
                $this->rect($img, $rEyeX+$p,   $eyeY-$p, $rEyeX+$p*2,  $eyeY,       $px);
                $this->rect($img, $rEyeX+$p,   $eyeY,    $rEyeX+$p*2,  $eyeY+$p,    $px);
                $this->rect($img, 38,  $mouthY,    162, $mouthY+$p,    $px);
                $this->rect($img, 30,  $mouthY+$p, 40,  $mouthY+$p*2,  $px);
                $this->rect($img, 160, $mouthY+$p, 170, $mouthY+$p*2,  $px);
                $this->rect($img, 28,  $mouthY+$p*2,38,  $mouthY+$p*3, $px);
                $this->rect($img, 162, $mouthY+$p*2,172, $mouthY+$p*3, $px);
                $this->rect($img, 30,  $mouthY+$p*3,40,  $mouthY+$p*4, $px);
                $this->rect($img, 160, $mouthY+$p*3,170, $mouthY+$p*4, $px);
                $this->rect($img, 38,  $mouthY+$p*4,162, $mouthY+$p*5, $px);
                break;
            case 7: // T_T crying
                $this->rect($img, $lEyeX,      $eyeY-$p, $lEyeX+$p*4,  $eyeY,       $px);
                $this->rect($img, $lEyeX+$p,   $eyeY,    $lEyeX+$p*2,  $eyeY+$p*2,  $px);
                $this->rect($img, $rEyeX,      $eyeY-$p, $rEyeX+$p*4,  $eyeY,       $px);
                $this->rect($img, $rEyeX+$p,   $eyeY,    $rEyeX+$p*2,  $eyeY+$p*2,  $px);
                // tears
                $this->rect($img, $lEyeX+$p,   $eyeY+$p*2,$lEyeX+$p*2, $eyeY+$p*4,  $pxD);
                $this->rect($img, $rEyeX+$p,   $eyeY+$p*2,$rEyeX+$p*2, $eyeY+$p*4,  $pxD);
                // sad mouth
                $this->rect($img, 44,  $mouthY+$p, 54,  $mouthY+$p*2,  $px);
                $this->rect($img, 54,  $mouthY,    64,  $mouthY+$p,    $px);
                $this->rect($img, 64,  $mouthY,    136, $mouthY+$p,    $px);
                $this->rect($img, 136, $mouthY,    146, $mouthY+$p,    $px);
                $this->rect($img, 146, $mouthY+$p, 156, $mouthY+$p*2,  $px);
                break;
        }

        // ── GLITCH OVERLAY ────────────────────────────────────────────
        switch ($glitchLine) {
            case 0: break; // none
            case 1: // single horizontal glitch line through face
                $gY = $this->hash($username, 20, 70, 130);
                $shift = $this->hash($username, 21, 8, 24);
                $this->rect($img, $shift, $gY, 200, $gY+3, $bgC);
                $this->rect($img, 0, $gY, 200-$shift, $gY+3, $bgC);
                $this->rect($img, 0, $gY, 200, $gY+1, $px);
                break;
            case 2: // RGB shift on eyes
                $shift2 = $this->hash($username, 22, 4, 10);
                $redC = $this->color($img, 255, 0, 68);
                $bluC = $this->color($img, 0, 68, 255);
                $this->rect($img, $lEyeX-$shift2, $eyeY-$p, $lEyeX-$shift2+$p*3, $eyeY+$p*2, $redC);
                $this->rect($img, $rEyeX+$shift2, $eyeY-$p, $rEyeX+$shift2+$p*3, $eyeY+$p*2, $bluC);
                break;
            case 3: // static band across eyes
                $sbW = $this->hash($username, 23, 6, 14);
                for ($sx = 18; $sx < 182; $sx += $sbW) {
                    $sc = ($this->hash($username, 60+$sx, 0, 2) === 0) ? $px : $bgC;
                    $this->rect($img, $sx, 62, $sx+$sbW-2, 98, $sc);
                }
                break;
            case 4: // corrupted block one side
                $cbX = ($this->hash($username, 24, 0, 1) === 0) ? 18 : 120;
                for ($cy = 60; $cy < 110; $cy += 6) {
                    $cc = ($this->hash($username, 70+$cy, 0, 2) === 0) ? $pxD : $bgC;
                    $this->rect($img, $cbX, $cy, $cbX+56, $cy+4, $cc);
                }
                break;
        }

        // ── STATUS BAR ───────────────────────────────────────────────
        switch ($statusBar) {
            case 0: // single dot (current)
                $this->ellipse($img, 170, 170, 14, 14, $pxD);
                $this->ellipse($img, 170, 170,  8,  8, $px);
                break;
            case 1: // battery icon
                $this->rect($img, 154, 164, 178, 178, $pxD);
                $this->rect($img, 156, 166, 172, 176, $px);
                $this->rect($img, 178, 167, 182, 175, $pxD);
                break;
            case 2: // signal bars
                for ($b = 0; $b < 4; $b++) {
                    $bh = ($b + 1) * 4;
                    $bx = 154 + $b * 8;
                    $by = 178 - $bh;
                    $bc = ($b < 3) ? $px : $pxD;
                    $this->rect($img, $bx, $by, $bx+5, 178, $bc);
                }
                break;
            case 3: // blinking cursor
                $this->rect($img, 158, 164, 164, 178, $px);
                break;
            case 4: // heart
                $this->rect($img, 156, 166, 162, 170, $px);
                $this->rect($img, 164, 166, 170, 170, $px);
                $this->rect($img, 154, 170, 172, 174, $px);
                $this->rect($img, 156, 174, 170, 178, $px);
                $this->rect($img, 158, 178, 168, 182, $px);
                $this->rect($img, 160, 182, 166, 186, $px);
                break;
            case 5: // progress bar
                $this->rect($img, 40, 172, 160, 180, $pxD);
                $progW = $this->hash($username, 25, 20, 118);
                $this->rect($img, 42, 174, 42+$progW, 178, $px);
                break;
            case 6: // two digit clock-style blocks
                // left digit
                $this->rect($img, 148, 164, 162, 168, $px);
                $this->rect($img, 148, 168, 152, 176, $px);
                $this->rect($img, 158, 168, 162, 176, $px);
                $this->rect($img, 148, 176, 162, 180, $px);
                // colon
                $this->rect($img, 164, 168, 168, 172, $px);
                $this->rect($img, 164, 174, 168, 178, $px);
                // right digit
                $this->rect($img, 170, 164, 184, 168, $px);
                $this->rect($img, 180, 168, 184, 180, $px);
                $this->rect($img, 170, 180, 184, 184, $px);
                break;
        }

        return $img;
    }
}
