<?php

namespace Resofire\Avatars\Generator\Style;

class SugarSkull extends AbstractStyle
{
    public function key(): string { return 'sugar-skull'; }
    public function name(): string { return 'Sugar Skull'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        // Background colors — vivid Día de los Muertos palette
        $bgColors = [
            [255, 34, 153],  // hot pink
            [0,  102, 102],  // deep teal
            [68,  0, 136],   // deep purple
            [170,  0,  34],  // deep red
            [17,  85,   0],  // forest green
            [0,   34, 102],  // cobalt blue
            [153, 85,   0],  // burnt orange
            [85,   0,  85],  // magenta
            [0,   85,  68],  // jade
            [102,  0,   0],  // dark crimson
            [17,   0,  85],  // midnight
            [0,   68,   0],  // emerald
            [136, 51,   0],  // rust
            [0,   51,  85],  // navy
        ];

        [$br, $bg2, $bb] = $this->pick($username, 0, $bgColors);

        // Derived colors
        $bgC   = $this->color($img, $br, $bg2, $bb);
        $dark  = $this->color($img, (int)($br * 0.4), (int)($bg2 * 0.4), (int)($bb * 0.4));
        $mid   = $this->color($img, (int)($br * 0.6), (int)($bg2 * 0.6), (int)($bb * 0.6));
        $skull = $this->color($img, 245, 240, 232); // off-white skull base
        $skullD= $this->color($img, 215, 208, 195);
        $black = $this->color($img, 12, 8, 18);

        // Accent colors for decorations — always vivid against any bg
        $accentSets = [
            [255, 204,   0], // gold
            [255,  68, 136], // rose
            [255, 136,   0], // marigold
            [68,  204, 255], // sky
            [136, 255,  68], // lime
            [255,  68, 255], // magenta
            [68,  255, 204], // aqua
            [255, 200,  68], // amber
        ];
        [$ac1r, $ac1g, $ac1b] = $this->pick($username, 1, $accentSets);
        $acc1 = $this->color($img, $ac1r, $ac1g, $ac1b);

        // Second accent
        $accentSets2 = [
            [255,  68, 136],
            [255, 204,   0],
            [68,  255, 204],
            [255, 136,   0],
            [136,  68, 255],
            [68,  204, 255],
            [255, 255,  68],
            [255,  68, 255],
        ];
        [$ac2r, $ac2g, $ac2b] = $this->pick($username, 2, $accentSets2);
        $acc2 = $this->color($img, $ac2r, $ac2g, $ac2b);

        // Fill background
        $this->rect($img, 0, 0, 200, 200, $bgC);

        // ── SKULL SHAPE ──────────────────────────────────────────────
        // Skull cranium (upper rounded) + jaw (lower rectangle)
        $this->ellipse($img, 100, 88, 150, 140, $skull);
        $this->rect($img, 30, 108, 170, 168, $skull);
        // Cheekbone narrowing
        $this->ellipse($img, 100, 158, 130, 80, $skull);

        // ── SLOT 1: FOREHEAD ORNAMENT ────────────────────────────────
        $forehead = $this->hash($username, 3, 0, 13);
        $this->drawForehead($img, $forehead, $acc1, $acc2, $dark, $skull);

        // ── SLOT 2: EYE SOCKET ORNAMENT ──────────────────────────────
        $socketStyle = $this->hash($username, 4, 0, 7);
        $this->drawSockets($img, $socketStyle, $acc1, $acc2, $dark, $skull, $bgC);

        // ── SKULL NOSE CAVITY ─────────────────────────────────────────
        $this->ellipse($img, 88,  148, 14, 10, $skullD);
        $this->ellipse($img, 110, 148, 14, 10, $skullD);
        $this->polygon($img, [84, 148, 116, 148, 100, 160], $skullD);

        // ── SLOT 3: CHEEK MARKS ───────────────────────────────────────
        $cheekStyle = $this->hash($username, 5, 0, 4);
        $this->drawCheeks($img, $cheekStyle, $acc1, $acc2);

        // ── SLOT 4: TEETH ─────────────────────────────────────────────
        $teethStyle = $this->hash($username, 6, 0, 5);
        $this->drawTeeth($img, $teethStyle, $skull, $skullD, $acc1, $acc2, $bgC);

        // Jaw outline
        imagesetthickness($img, 2);
        imagearc($img, 100, 148, 130, 80, 0, 180, $skullD);
        imagesetthickness($img, 1);

        return $img;
    }

    private function drawForehead(\GdImage $img, int $type, int $a1, int $a2, int $dark, int $skull): void
    {
        switch ($type) {
            case 0: // cross
                $this->rect($img, 92, 18, 108, 58, $a1);
                $this->rect($img, 72, 34, 128, 46, $a1);
                $this->ellipse($img, 100, 18, 10, 10, $a2);
                break;
            case 1: // single gem / circlet
                $this->rect($img, 30, 48, 170, 56, $a1);
                $this->ellipse($img, 100, 36, 28, 28, $a1);
                $this->ellipse($img, 100, 36, 18, 18, $a2);
                $this->ellipse($img, 100, 36, 8,  8,  $skull);
                break;
            case 2: // butterfly
                $this->ellipse($img, 74,  38, 40, 26, $a1);
                $this->ellipse($img, 126, 38, 40, 26, $a1);
                $this->ellipse($img, 74,  44, 26, 16, $a2);
                $this->ellipse($img, 126, 44, 26, 16, $a2);
                $this->rect($img, 96, 28, 104, 54, $dark);
                break;
            case 3: // marigold crown
                foreach ([30, 50, 70, 100, 130, 150, 170] as $cx) {
                    $h = ($cx === 100) ? 32 : (($cx === 70 || $cx === 130) ? 26 : 18);
                    $this->ellipse($img, $cx, (int)(40 - $h / 2), 18, $h * 2, $a1);
                }
                foreach ([40, 60, 85, 100, 115, 140, 160] as $cx) {
                    $this->ellipse($img, $cx, 46, 12, 16, $a2);
                }
                break;
            case 4: // sun / star
                // rays
                for ($i = 0; $i < 8; $i++) {
                    $angle = $i * 45 * M_PI / 180;
                    $x1 = (int)(100 + cos($angle) * 18);
                    $y1 = (int)(36  + sin($angle) * 18);
                    $x2 = (int)(100 + cos($angle) * 32);
                    $y2 = (int)(36  + sin($angle) * 32);
                    imagesetthickness($img, 4);
                    imageline($img, $x1, $y1, $x2, $y2, $a1);
                    imagesetthickness($img, 1);
                }
                $this->ellipse($img, 100, 36, 34, 34, $a1);
                $this->ellipse($img, 100, 36, 20, 20, $a2);
                $this->ellipse($img, 100, 36,  8,  8, $skull);
                break;
            case 5: // crescent moon + planet
                $this->ellipse($img, 100, 36, 30, 30, $a1);
                $this->ellipse($img, 108, 32, 26, 26, $dark);
                $this->ellipse($img, 100, 36,  8,  8, $a1);
                // saturn ring
                $this->ellipse($img, 100, 36, 44,  8, $a2);
                break;
            case 6: // lotus petals
                foreach ([64, 82, 100, 118, 136] as $i => $cx) {
                    $h = ($i === 2) ? 36 : ($i === 1 || $i === 3 ? 30 : 22);
                    $this->ellipse($img, $cx, (int)(44 - $h / 2), 16, $h, ($i % 2 === 0) ? $a1 : $a2);
                }
                break;
            case 7: // teardrop
                $this->ellipse($img, 100, 44, 20, 24, $a1);
                $this->ellipse($img, 100, 34, 12, 14, $a2);
                $this->polygon($img, [90, 50, 110, 50, 100, 64], $a1);
                $this->ellipse($img, 100, 42, 8, 8, $skull);
                break;
            case 8: // stacked diamonds
                $this->polygon($img, [100, 22, 112, 34, 100, 46,  88, 34], $a1);
                $this->polygon($img, [100, 42, 112, 54, 100, 66,  88, 54], $a2);
                $this->ellipse($img, 100, 34, 6, 6, $dark);
                $this->ellipse($img, 100, 54, 6, 6, $dark);
                break;
            case 9: // mushroom
                $this->ellipse($img, 100, 32, 44, 26, $a1);
                $this->rect($img, 90, 32, 110, 52, $a2);
                $this->ellipse($img, 84,  30, 10,  8, $skull);
                $this->ellipse($img, 100, 26, 10,  8, $skull);
                $this->ellipse($img, 116, 30, 10,  8, $skull);
                break;
            case 10: // all-seeing eye / triangle
                $this->polygon($img, [100, 18, 128, 60, 72, 60], $a1);
                $this->polygon($img, [100, 26, 122, 56, 78, 56], $a2);
                $this->ellipse($img, 100, 46, 14, 14, $dark);
                $this->ellipse($img, 100, 46,  6,  6, $a1);
                break;
            case 11: // lightning bolt
                $this->polygon($img, [108, 20, 96, 44, 104, 44, 92, 66, 112, 38, 102, 38], $a1);
                break;
            case 12: // anchor
                imagesetthickness($img, 5);
                imageline($img, 100, 24, 100, 56, $a1);
                imageline($img, 80,  32, 120, 32, $a1);
                imagesetthickness($img, 1);
                $this->ellipse($img, 100, 56, 20, 20, $a1);
                $this->ellipse($img, 100, 56, 12, 12, $dark);
                imageline($img, 80, 60, 88, 56, $a1);
                imageline($img, 120, 60, 112, 56, $a1);
                imagesetthickness($img, 5);
                break;
            case 13: // flame crown
                foreach ([44, 62, 80, 100, 120, 138, 156] as $i => $cx) {
                    $h = ($i % 2 === 0) ? 28 : 38;
                    $col = ($i % 2 === 0) ? $a2 : $a1;
                    $this->ellipse($img, $cx, (int)(46 - $h / 2), 16, $h, $col);
                }
                break;
        }
    }

    private function drawSockets(\GdImage $img, int $style, int $a1, int $a2, int $dark, int $skull, int $bg): void
    {
        $lx = 68; $rx = 132; $sy = 108;

        // Always draw dark socket base first
        $this->ellipse($img, $lx, $sy, 48, 48, $dark);
        $this->ellipse($img, $rx, $sy, 48, 48, $dark);

        switch ($style) {
            case 0: // rose — concentric + petals
                foreach ([$lx, $rx] as $cx) {
                    $this->ellipse($img, $cx, $sy, 36, 36, $a1);
                    $this->ellipse($img, $cx, $sy, 24, 24, $a2);
                    $this->ellipse($img, $cx, $sy, 12, 12, $a1);
                    $this->ellipse($img, $cx, $sy,  6,  6, $skull);
                    // petals
                    $this->ellipse($img, $cx,        $sy - 22, 12, 16, $a1);
                    $this->ellipse($img, $cx,        $sy + 22, 12, 16, $a1);
                    $this->ellipse($img, $cx - 22,   $sy,      16, 12, $a1);
                    $this->ellipse($img, $cx + 22,   $sy,      16, 12, $a1);
                    $this->ellipse($img, $cx - 16,   $sy - 16, 12, 14, $a2);
                    $this->ellipse($img, $cx + 16,   $sy - 16, 12, 14, $a2);
                    $this->ellipse($img, $cx - 16,   $sy + 16, 12, 14, $a2);
                    $this->ellipse($img, $cx + 16,   $sy + 16, 12, 14, $a2);
                }
                break;
            case 1: // web — radial lines + rings
                foreach ([$lx, $rx] as $cx) {
                    for ($i = 0; $i < 8; $i++) {
                        $angle = $i * 45 * M_PI / 180;
                        imageline($img, $cx, $sy,
                            (int)($cx + cos($angle) * 24),
                            (int)($sy + sin($angle) * 24), $a1);
                    }
                    $this->ellipse($img, $cx, $sy, 16, 16, $a1);
                    $this->ellipse($img, $cx, $sy, 28, 28, $a1);
                    $this->ellipse($img, $cx, $sy,  8,  8, $a2);
                    $this->ellipse($img, $cx, $sy,  4,  4, $skull);
                }
                break;
            case 2: // 6-petal flower
                foreach ([$lx, $rx] as $cx) {
                    $this->ellipse($img, $cx,      $sy - 14, 14, 20, $a1);
                    $this->ellipse($img, $cx,      $sy + 14, 14, 20, $a1);
                    $this->ellipse($img, $cx - 14, $sy,      20, 14, $a1);
                    $this->ellipse($img, $cx + 14, $sy,      20, 14, $a1);
                    $this->ellipse($img, $cx - 10, $sy - 10, 14, 18, $a2);
                    $this->ellipse($img, $cx + 10, $sy - 10, 14, 18, $a2);
                    $this->ellipse($img, $cx - 10, $sy + 10, 14, 18, $a2);
                    $this->ellipse($img, $cx + 10, $sy + 10, 14, 18, $a2);
                    $this->ellipse($img, $cx, $sy, 16, 16, $dark);
                    $this->ellipse($img, $cx, $sy,  8,  8, $a2);
                }
                break;
            case 3: // heart sockets
                foreach ([$lx, $rx] as $cx) {
                    $this->ellipse($img, $cx - 8, $sy - 8, 22, 20, $a1);
                    $this->ellipse($img, $cx + 8, $sy - 8, 22, 20, $a1);
                    $this->polygon($img, [$cx - 22, $sy, $cx + 22, $sy, $cx, $sy + 22], $a1);
                    $this->ellipse($img, $cx, $sy + 4, 10, 10, $dark);
                    $this->ellipse($img, $cx - 6, $sy - 6, 6, 6, $skull);
                }
                break;
            case 4: // sun rays
                foreach ([$lx, $rx] as $cx) {
                    for ($i = 0; $i < 8; $i++) {
                        $angle = $i * 45 * M_PI / 180;
                        $x1 = (int)($cx + cos($angle) * 18);
                        $y1 = (int)($sy + sin($angle) * 18);
                        $x2 = (int)($cx + cos($angle) * 26);
                        $y2 = (int)($sy + sin($angle) * 26);
                        imagesetthickness($img, 4);
                        imageline($img, $x1, $y1, $x2, $y2, $a1);
                        imagesetthickness($img, 1);
                    }
                    $this->ellipse($img, $cx, $sy, 30, 30, $a1);
                    $this->ellipse($img, $cx, $sy, 18, 18, $dark);
                    $this->ellipse($img, $cx, $sy,  8,  8, $a2);
                }
                break;
            case 5: // crescent moon
                foreach ([$lx, $rx] as $cx) {
                    $this->ellipse($img, $cx, $sy, 38, 38, $a1);
                    $this->ellipse($img, $cx + 8, $sy - 4, 34, 34, $dark);
                    $this->ellipse($img, $cx, $sy,  8,  8, $a1);
                }
                break;
            case 6: // lotus petals
                foreach ([$lx, $rx] as $cx) {
                    $this->ellipse($img, $cx,      $sy - 16, 14, 22, $a1);
                    $this->ellipse($img, $cx - 14, $sy - 8,  14, 22, $a2);
                    $this->ellipse($img, $cx + 14, $sy - 8,  14, 22, $a2);
                    $this->ellipse($img, $cx - 14, $sy + 8,  14, 20, $a1);
                    $this->ellipse($img, $cx + 14, $sy + 8,  14, 20, $a1);
                    $this->ellipse($img, $cx,      $sy + 16, 14, 20, $a2);
                    $this->ellipse($img, $cx, $sy, 14, 14, $dark);
                    $this->ellipse($img, $cx, $sy,  6,  6, $a2);
                }
                break;
            case 7: // star / diamond
                foreach ([$lx, $rx] as $cx) {
                    $this->polygon($img, [
                        $cx, $sy - 22,  $cx + 6, $sy - 6,
                        $cx + 22, $sy,  $cx + 6, $sy + 6,
                        $cx, $sy + 22,  $cx - 6, $sy + 6,
                        $cx - 22, $sy,  $cx - 6, $sy - 6
                    ], $a1);
                    $this->ellipse($img, $cx, $sy, 16, 16, $dark);
                    $this->ellipse($img, $cx, $sy,  6,  6, $a2);
                }
                break;
        }

        // Always draw shine dots
        $white = $this->color($img, 255, 255, 255);
        $this->ellipse($img, $lx - 4, $sy - 4, 6, 6, $white);
        $this->ellipse($img, $rx - 4, $sy - 4, 6, 6, $white);
    }

    private function drawCheeks(\GdImage $img, int $style, int $a1, int $a2): void
    {
        $positions = [[24, 120], [176, 120]]; // left cheek center, right cheek center

        switch ($style) {
            case 0: // dots triangle
                foreach ($positions as [$cx, $cy]) {
                    $this->ellipse($img, $cx,     $cy - 6, 8, 8, $a1);
                    $this->ellipse($img, $cx - 7, $cy + 4, 8, 8, $a1);
                    $this->ellipse($img, $cx + 7, $cy + 4, 8, 8, $a1);
                }
                break;
            case 1: // spiral
                foreach ($positions as [$cx, $cy]) {
                    $this->ellipse($img, $cx, $cy, 18, 18, $a1);
                    $this->ellipse($img, $cx, $cy, 12, 12, $a2);
                    $this->ellipse($img, $cx, $cy,  6,  6, $a1);
                    $this->ellipse($img, $cx, $cy,  3,  3, $a2);
                }
                break;
            case 2: // wave lines
                foreach ([[14, 122], [166, 122]] as [$startX, $cy]) {
                    $pts = [];
                    $dir = ($startX < 100) ? 1 : -1;
                    for ($i = 0; $i <= 4; $i++) {
                        $pts[] = $startX + $dir * $i * 5;
                        $pts[] = $cy + (($i % 2 === 0) ? -5 : 5);
                    }
                    imagesetthickness($img, 3);
                    for ($i = 0; $i < count($pts) - 2; $i += 2) {
                        imageline($img, $pts[$i], $pts[$i+1], $pts[$i+2], $pts[$i+3], $a1);
                    }
                    imagesetthickness($img, 1);
                }
                break;
            case 3: // leaf
                foreach ([[16, 120, 1], [184, 120, -1]] as [$cx, $cy, $dir]) {
                    $this->ellipse($img, $cx, $cy, 20, 12, $a1);
                    imageline($img, $cx - $dir * 10, $cy, $cx + $dir * 10, $cy, $a2);
                    imageline($img, $cx, $cy - 6, $cx + $dir * 8, $cy + 4, $a2);
                }
                break;
            case 4: // spark / cross marks
                foreach ($positions as [$cx, $cy]) {
                    imagesetthickness($img, 3);
                    imageline($img, $cx - 8, $cy, $cx + 8, $cy, $a1);
                    imageline($img, $cx, $cy - 8, $cx, $cy + 8, $a1);
                    imageline($img, $cx - 6, $cy - 6, $cx + 6, $cy + 6, $a2);
                    imageline($img, $cx + 6, $cy - 6, $cx - 6, $cy + 6, $a2);
                    imagesetthickness($img, 1);
                }
                break;
        }
    }

    private function drawTeeth(\GdImage $img, int $style, int $skull, int $skullD, int $a1, int $a2, int $bg): void
    {
        $ty = 170; $tx = 38; $tw = 124; $th = 22;

        // Jaw bar
        $this->rect($img, $tx, $ty, $tx + $tw, $ty + $th, $skullD);

        $toothW = 14;
        $count  = 8;
        $startX = $tx + 4;

        switch ($style) {
            case 0: // classic even teeth
                for ($i = 0; $i < $count; $i++) {
                    $this->rect($img, $startX + $i * $toothW, $ty, $startX + $i * $toothW + $toothW - 2, $ty + 16, $skull);
                }
                break;
            case 1: // gap tooth (missing slot 3)
                for ($i = 0; $i < $count; $i++) {
                    if ($i === 3) continue;
                    $this->rect($img, $startX + $i * $toothW, $ty, $startX + $i * $toothW + $toothW - 2, $ty + 16, $skull);
                }
                break;
            case 2: // jewel teeth — alternating accent colors
                for ($i = 0; $i < $count; $i++) {
                    $col = ($i % 2 === 0) ? $a1 : $a2;
                    $this->rect($img, $startX + $i * $toothW, $ty, $startX + $i * $toothW + $toothW - 2, $ty + 16, $col);
                }
                break;
            case 3: // floral topped — teeth with dot on top
                for ($i = 0; $i < $count; $i++) {
                    $this->rect($img, $startX + $i * $toothW, $ty + 4, $startX + $i * $toothW + $toothW - 2, $ty + 18, $skull);
                    $col = ($i % 2 === 0) ? $a1 : $a2;
                    $this->ellipse($img, $startX + $i * $toothW + 6, $ty + 4, 10, 10, $col);
                }
                break;
            case 4: // zigzag mouth — no individual teeth
                $this->rect($img, $tx, $ty, $tx + $tw, $ty + $th, $skullD);
                $pts = [];
                for ($i = 0; $i <= $count; $i++) {
                    $pts[] = $tx + 2 + $i * ($tw / $count);
                    $pts[] = ($i % 2 === 0) ? $ty : $ty + 18;
                }
                imagesetthickness($img, 4);
                for ($i = 0; $i < count($pts) - 2; $i += 2) {
                    imageline($img, (int)$pts[$i], (int)$pts[$i+1], (int)$pts[$i+2], (int)$pts[$i+3], $skull);
                }
                imagesetthickness($img, 1);
                break;
            case 5: // patterned — alternating white and accent
                for ($i = 0; $i < $count; $i++) {
                    $col = ($i % 3 === 0) ? $a1 : ($i % 3 === 1 ? $skull : $a2);
                    $this->rect($img, $startX + $i * $toothW, $ty, $startX + $i * $toothW + $toothW - 2, $ty + 16, $col);
                }
                break;
        }
    }
}
