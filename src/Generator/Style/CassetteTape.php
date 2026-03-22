<?php

namespace Resofire\Avatars\Generator\Style;

class CassetteTape extends AbstractStyle
{
    public function key(): string { return 'cassette'; }
    public function name(): string { return 'Cassette'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        // Housing colors
        $housingColors = [
            [20,  20,  20],  // black
            [232, 224, 208], // cream
            [10,  26,  58],  // deep blue
            [58,  10,  10],  // dark red
            [10,  42,  10],  // dark green
            [42,  28,   0],  // dark brown
            [36,  10,  58],  // dark purple
            [42,  42,  42],  // slate gray
        ];

        // Label colors — vivid contrast
        $labelColors = [
            [255,  68, 136], // hot pink
            [255, 102,   0], // orange
            [68,  136, 255], // blue
            [255, 204,   0], // yellow
            [68,  204, 136], // mint
            [204,  68, 255], // violet
            [255,  68,  68], // red
            [68,  220, 220], // cyan
            [255, 170,   0], // amber
            [136, 255,  68], // lime
        ];

        // Label design variants
        $labelDesigns = [0, 1, 2, 3, 4]; // solid, text-lines, waveform, stripes, minimal

        [$hr, $hg, $hb] = $this->pick($username, 0, $housingColors);
        [$lr, $lg, $lb] = $this->pick($username, 1, $labelColors);
        $labelDesign     = $this->pick($username, 2, $labelDesigns);

        // Reel sizes — asymmetric winding is the key structural differentiator
        // [left radius, right radius] — tape moves from right to left as it plays
        $reelSizes = [
            [28, 12], // almost full left, nearly empty right
            [12, 28], // nearly empty left, almost full right
            [20, 20], // equal / middle
            [24, 16], // mostly left
            [16, 24], // mostly right
            [30,  8], // nearly complete on left
            [ 8, 30], // nearly complete on right
            [22, 18], // slight lean left
        ];
        [$reelL, $reelR] = $this->pick($username, 3, $reelSizes);

        // Spoke count varies 4 or 6
        $spokeCount = $this->pick($username, 4, [4, 6]);

        $housing = $this->color($img, $hr, $hg, $hb);
        $housingL= $this->color($img, min(255, $hr + 18), min(255, $hg + 18), min(255, $hb + 18));
        $housingD= $this->color($img, max(0, $hr - 12),  max(0, $hg - 12),  max(0, $hb - 12));
        $label   = $this->color($img, $lr, $lg, $lb);
        $labelD  = $this->color($img, (int)($lr * 0.65), (int)($lg * 0.65), (int)($lb * 0.65));
        $labelL  = $this->color($img, min(255, (int)($lr * 1.2)), min(255, (int)($lg * 1.2)), min(255, (int)($lb * 1.2)));
        $white   = $this->color($img, 245, 242, 235);
        $black   = $this->color($img, 12, 12, 12);
        $reel    = $this->color($img, max(0, $hr - 20), max(0, $hg - 20), max(0, $hb - 20));
        $reelHub = $this->color($img, min(255, $hr + 30), min(255, $hg + 30), min(255, $hb + 30));
        $tape    = $this->color($img, 40, 30, 18); // tape color — dark brown

        // Fill background with housing color
        $this->rect($img, 0, 0, 200, 200, $housing);

        // ── HOUSING OUTER SHELL ──────────────────────────────────────
        $this->rect($img, 10, 28, 190, 172, $housingL);
        $this->rect($img, 14, 32, 186, 168, $housing);
        // rounded corner suggestion
        $this->ellipse($img,  24,  42, 20, 20, $housingL);
        $this->ellipse($img, 176,  42, 20, 20, $housingL);
        $this->ellipse($img,  24, 158, 20, 20, $housingL);
        $this->ellipse($img, 176, 158, 20, 20, $housingL);

        // Screw holes at corners
        $this->ellipse($img,  28,  46, 12, 12, $housingD);
        $this->ellipse($img, 172,  46, 12, 12, $housingD);
        $this->ellipse($img,  28, 154, 12, 12, $housingD);
        $this->ellipse($img, 172, 154, 12, 12, $housingD);
        // Screw slot
        $this->rect($img, 25, 45, 31, 47, $housing);
        $this->rect($img, 169, 45, 175, 47, $housing);
        $this->rect($img, 25, 153, 31, 155, $housing);
        $this->rect($img, 169, 153, 175, 155, $housing);

        // ── LABEL AREA ───────────────────────────────────────────────
        $this->rect($img, 28, 38, 172, 96, $label);

        switch ($labelDesign) {
            case 0: // solid with text lines
                $this->rect($img, 34, 44, 120, 50, $labelD);
                $this->rect($img, 34, 55, 90,  59, $labelL);
                $this->rect($img, 34, 64, 104, 68, $labelL);
                $this->rect($img, 34, 73, 80,  76, $labelL);
                break;

            case 1: // text lines only - minimal clean
                $this->rect($img, 32, 42, 168, 48, $labelD);
                $this->rect($img, 32, 52, 140, 56, $labelL);
                $this->rect($img, 32, 60, 155, 64, $labelL);
                $this->rect($img, 32, 68, 130, 72, $labelD);
                $this->rect($img, 32, 76, 148, 80, $labelL);
                $this->rect($img, 32, 84, 120, 88, $labelL);
                break;

            case 2: // waveform — bars of varying height like audio visualization
                $barW = 6; $barGap = 3;
                $waveHeights = [18, 28, 22, 36, 44, 38, 30, 44, 48, 36, 40, 28, 44, 38, 24, 36, 44, 28, 20, 36];
                $baseY = 94;
                for ($i = 0; $i < count($waveHeights); $i++) {
                    $bx = 32 + $i * ($barW + $barGap);
                    if ($bx + $barW > 168) break;
                    $h = $waveHeights[$i];
                    $this->rect($img, $bx, $baseY - $h, $bx + $barW - 1, $baseY, $labelD);
                }
                break;

            case 3: // horizontal color stripes
                $stripeH = (int)((96 - 38) / 5);
                $cols = [$label, $labelD, $labelL, $labelD, $label];
                for ($i = 0; $i < 5; $i++) {
                    $this->rect($img, 28, 38 + $i * $stripeH, 172, 38 + ($i + 1) * $stripeH, $cols[$i]);
                }
                $this->rect($img, 34, 50, 140, 56, $white);
                break;

            case 4: // minimal — single bold stripe + dot
                $this->rect($img, 28, 60, 172, 74, $labelD);
                $this->ellipse($img, 155, 58, 20, 20, $labelD);
                $this->ellipse($img, 155, 58, 12, 12, $labelL);
                $this->rect($img, 34, 44, 130, 50, $labelL);
                break;
        }

        // ── TAPE WINDOW ──────────────────────────────────────────────
        // The opening showing the tape path between reels
        $this->rect($img, 68, 100, 132, 120, $black);
        $this->rect($img, 70, 102, 130, 118, $tape);

        // Tape guides (the little bumps tape runs over)
        $this->ellipse($img,  72, 110, 10, 10, $housing);
        $this->ellipse($img, 128, 110, 10, 10, $housing);

        // ── REEL WINDOWS ─────────────────────────────────────────────
        $lReelX = 60; $rReelX = 140; $reelY = 136;
        $windowR = 34;

        // Left reel window
        $this->ellipse($img, $lReelX, $reelY, $windowR * 2, $windowR * 2, $black);
        $this->ellipse($img, $lReelX, $reelY, ($windowR - 2) * 2, ($windowR - 2) * 2, $reel);

        // Wound tape on left reel — the bigger the $reelL, the more tape
        if ($reelL > 14) {
            $this->ellipse($img, $lReelX, $reelY, $reelL * 2, $reelL * 2, $tape);
        }
        $this->ellipse($img, $lReelX, $reelY, ($windowR - 12) * 2, ($windowR - 12) * 2, $reel);

        // Spokes on left reel
        for ($i = 0; $i < $spokeCount; $i++) {
            $angle = $i * (360 / $spokeCount) * M_PI / 180;
            $spokeLen = $windowR - 14;
            imageline($img,
                $lReelX, $reelY,
                (int)($lReelX + cos($angle) * $spokeLen),
                (int)($reelY  + sin($angle) * $spokeLen),
                $reelHub);
        }
        $this->ellipse($img, $lReelX, $reelY, 16, 16, $reelHub);
        $this->ellipse($img, $lReelX, $reelY,  8,  8, $reel);

        // Right reel window
        $this->ellipse($img, $rReelX, $reelY, $windowR * 2, $windowR * 2, $black);
        $this->ellipse($img, $rReelX, $reelY, ($windowR - 2) * 2, ($windowR - 2) * 2, $reel);

        if ($reelR > 14) {
            $this->ellipse($img, $rReelX, $reelY, $reelR * 2, $reelR * 2, $tape);
        }
        $this->ellipse($img, $rReelX, $reelY, ($windowR - 12) * 2, ($windowR - 12) * 2, $reel);

        // Spokes on right reel — slightly different angle for variation
        $angleOffset = $this->hash($username, 5, 0, 359) * M_PI / 180;
        for ($i = 0; $i < $spokeCount; $i++) {
            $angle = $angleOffset + $i * (360 / $spokeCount) * M_PI / 180;
            $spokeLen = $windowR - 14;
            imageline($img,
                $rReelX, $reelY,
                (int)($rReelX + cos($angle) * $spokeLen),
                (int)($reelY  + sin($angle) * $spokeLen),
                $reelHub);
        }
        $this->ellipse($img, $rReelX, $reelY, 16, 16, $reelHub);
        $this->ellipse($img, $rReelX, $reelY,  8,  8, $reel);

        // ── BOTTOM HOUSING DETAIL ────────────────────────────────────
        // Tape slot notch at bottom center
        $this->rect($img, 86, 164, 114, 172, $black);

        // Side grip ridges
        for ($i = 0; $i < 4; $i++) {
            $this->rect($img, 14, 110 + $i * 8, 20, 114 + $i * 8, $housingD);
            $this->rect($img, 180, 110 + $i * 8, 186, 114 + $i * 8, $housingD);
        }

        return $img;
    }
}
