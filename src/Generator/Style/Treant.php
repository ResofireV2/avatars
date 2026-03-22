<?php

namespace Resofire\Avatars\Generator\Style;

class Treant extends AbstractStyle
{
    public function key(): string { return 'treant'; }
    public function name(): string { return 'Treant'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        // ── SLOTS ────────────────────────────────────────────────────
        $barkType  = $this->hash($username, 0, 0, 7); // 8 species
        $eyeType   = $this->hash($username, 1, 0, 6); // 7 eye types
        $mouthType = $this->hash($username, 2, 0, 5); // 6 mouth types
        $crownType = $this->hash($username, 3, 0, 6); // 7 crown types
        $extraType = $this->hash($username, 4, 0, 5); // 6 extras
        $season    = $this->hash($username, 5, 0, 3); // 4 seasons

        // ── BARK PALETTES ─────────────────────────────────────────────
        // [base, dark, light, heartwood]
        $barks = [
            [[90, 62, 40],  [58, 38, 20],  [120, 86, 56],  [42, 24, 10]], // oak
            [[120, 60, 30], [80, 36, 14],  [160, 90, 50],  [50, 22,  8]], // pine
            [[160,150,130], [110,100, 85], [195,188,170],  [80, 70, 55]], // willow (silvery)
            [[150, 80, 60], [100, 48, 32], [190,120, 90],  [60, 28, 16]], // cherry
            [[50,  44, 38], [28, 24, 18],  [ 72, 64, 54],  [18, 14, 10]], // dead/withered
            [[44, 60, 30],  [26, 38, 14],  [ 66, 88, 46],  [18, 28,  8]], // ancient mossy
            [[220,210,195], [160,148,130], [240,235,224],  [100, 90, 76]], // birch (pale)
            [[58, 70, 36],  [34, 44, 18],  [ 84,100, 52],  [20, 28,  8]], // jungle
        ];

        // Season tints applied to crown/leaf colors
        $seasonLeaf = [
            [[80,160, 60], [50,130, 40]],  // spring — fresh green
            [[40,130, 30], [20, 90, 20]],  // summer — deep green
            [[180, 80, 20],[140, 50, 10]], // autumn — orange-red
            [[160,160,170],[120,120,130]], // winter — grey-blue
        ];

        [$bR,$bG,$bB]   = $barks[$barkType][0];
        [$dR,$dG,$dB]   = $barks[$barkType][1];
        [$lR,$lG,$lB]   = $barks[$barkType][2];
        [$hR,$hG,$hB]   = $barks[$barkType][3];
        [$lfR,$lfG,$lfB]= $seasonLeaf[$season][0];
        [$ldR,$ldG,$ldB]= $seasonLeaf[$season][1];

        $bark   = $this->color($img, $bR, $bG, $bB);
        $barkD  = $this->color($img, $dR, $dG, $dB);
        $barkL  = $this->color($img, $lR, $lG, $lB);
        $heart  = $this->color($img, $hR, $hG, $hB);
        $leaf   = $this->color($img, $lfR, $lfG, $lfB);
        $leafD  = $this->color($img, $ldR, $ldG, $ldB);
        $amber  = $this->color($img, 200, 130,  10);
        $amberL = $this->color($img, 240, 180,  40);
        $moss   = $this->color($img,  60,  90,  30);
        $mossL  = $this->color($img,  90, 130,  50);
        $white  = $this->color($img, 220, 210, 190);
        $black  = $this->color($img,  10,   8,   6);
        $fire   = $this->color($img, 220,  80,  10);
        $fireL  = $this->color($img, 255, 160,  20);
        $sap    = $this->color($img, 180, 140,  20);
        $sapL   = $this->color($img, 220, 180,  60);
        $blossom= $this->color($img, 240, 180, 190);
        $blossomD=$this->color($img, 200, 120, 140);
        $mushC  = $this->color($img, 200, 160, 100);
        $mushD  = $this->color($img, 160, 110,  60);

        // ── BASE BARK FILL ────────────────────────────────────────────
        $this->rect($img, 0, 0, 200, 200, $bark);

        // ── BARK TEXTURE (species-specific) ──────────────────────────
        switch ($barkType) {
            case 0: // oak — deep vertical furrows
                foreach ([18,34,50,66,82,98,114,130,146,162,178] as $x) {
                    $this->rect($img, $x, 0, $x+3, 200, $barkD);
                }
                foreach ([24,40,56,72,88,104,120,136,152,168] as $x) {
                    $this->rect($img, $x, 0, $x+2, 200, $barkL);
                }
                break;
            case 1: // pine — horizontal plate scales
                foreach ([0,22,44,66,88,110,132,154,176] as $y) {
                    $this->rect($img, 0, $y, 200, $y+18, $barkD);
                    $this->rect($img, 0, $y+16, 200, $y+20, $barkL);
                    // scale edge bumps
                    foreach ([20,50,80,110,140,170] as $x) {
                        $this->ellipse($img, $x, $y+18, 24, 8, $barkL);
                    }
                }
                break;
            case 2: // willow — smooth with faint horizontal lines
                foreach ([30,60,90,120,150,180] as $y) {
                    imagesetthickness($img, 1);
                    imageline($img, 0, $y, 200, $y, $barkD);
                }
                // subtle diagonal grain
                foreach ([0,40,80,120,160,200] as $x) {
                    imagesetthickness($img, 1);
                    imageline($img, $x, 0, $x+30, 200, $barkD);
                }
                imagesetthickness($img, 1);
                break;
            case 3: // cherry — horizontal lenticels (small dashes)
                foreach ([15,35,55,75,95,115,135,155,175] as $y) {
                    foreach ([10,28,48,68,88,108,128,148,168,188] as $x) {
                        $this->rect($img, $x, $y, $x+12, $y+3, $barkD);
                    }
                }
                break;
            case 4: // dead — cracked, peeling, dark
                // large crack lines
                imagesetthickness($img, 3);
                imageline($img, 60,  0,  50, 200, $barkD);
                imageline($img, 110, 0, 120, 200, $barkD);
                imageline($img, 160, 0, 150, 200, $barkD);
                imageline($img,  30, 0,  20, 200, $barkD);
                imagesetthickness($img, 2);
                imageline($img, 60, 80, 100, 60, $barkD);
                imageline($img, 110, 100, 150, 80, $barkD);
                imagesetthickness($img, 1);
                // peeling patches
                $this->ellipse($img,  30, 60, 30, 20, $barkL);
                $this->ellipse($img, 160, 90, 28, 18, $barkL);
                $this->ellipse($img,  80, 150, 32, 16, $barkL);
                break;
            case 5: // ancient mossy — patches of moss everywhere
                foreach ([18,34,50,66,82,98,114,130,146,162,178] as $x) {
                    $this->rect($img, $x, 0, $x+3, 200, $barkD);
                }
                // moss patches
                foreach ([[20,30,36,22],[80,20,44,18],[150,40,38,20],[10,100,30,24],
                          [160,110,34,22],[40,160,40,20],[120,150,36,18],[90,80,28,16]] as [$mx,$my,$mw,$mh]) {
                    $this->ellipse($img, $mx, $my, $mw, $mh, $moss);
                    $this->ellipse($img, $mx-4, $my-2, (int)($mw*0.6), (int)($mh*0.6), $mossL);
                }
                break;
            case 6: // birch — pale with black horizontal marks
                foreach ([20,48,70,92,118,144,165,186] as $y) {
                    $w = $this->hash($username, 30+$y, 30, 80);
                    $x = $this->hash($username, 31+$y,  0, 80);
                    $this->rect($img, $x, $y, $x+$w, $y+6, $barkD);
                    $this->rect($img, $x+4, $y+1, $x+$w-4, $y+4, $this->color($img, 30, 22, 14));
                }
                break;
            case 7: // jungle — vines wrapping the bark
                foreach ([18,50,82,114,146,178] as $x) {
                    $this->rect($img, $x, 0, $x+3, 200, $barkD);
                }
                // vines
                foreach ([[0,40],[60,80],[130,20],[170,100]] as [$vx,$vy]) {
                    imagesetthickness($img, 3);
                    imagearc($img, $vx, $vy, 60, 100, 0, 180, $leafD);
                    imagearc($img, $vx+30, $vy+50, 50, 80, 180, 360, $leaf);
                    imagesetthickness($img, 1);
                }
                break;
        }

        // ── BROW RIDGE (always — gives face structure) ────────────────
        $this->ellipse($img, 100, 84, 160, 28, $barkD);

        // ── EYES ──────────────────────────────────────────────────────
        $lx = 62; $rx = 138; $ey = 90;

        switch ($eyeType) {
            case 0: // hollow knot holes — dark oval cavities
                $this->ellipse($img, $lx, $ey, 44, 36, $barkD);
                $this->ellipse($img, $rx, $ey, 44, 36, $barkD);
                $this->ellipse($img, $lx, $ey, 30, 24, $heart);
                $this->ellipse($img, $rx, $ey, 30, 24, $heart);
                $this->ellipse($img, $lx, $ey, 16, 12, $black);
                $this->ellipse($img, $rx, $ey, 16, 12, $black);
                break;
            case 1: // glowing amber sap eyes
                $this->ellipse($img, $lx, $ey, 44, 36, $barkD);
                $this->ellipse($img, $rx, $ey, 44, 36, $barkD);
                $this->ellipse($img, $lx, $ey, 32, 26, $amber);
                $this->ellipse($img, $rx, $ey, 32, 26, $amber);
                $this->ellipse($img, $lx, $ey, 20, 16, $amberL);
                $this->ellipse($img, $rx, $ey, 20, 16, $amberL);
                $this->ellipse($img, $lx, $ey,  8,  6, $heart);
                $this->ellipse($img, $rx, $ey,  8,  6, $heart);
                // catch light
                $this->ellipse($img, $lx-8, $ey-6, 8, 8, $this->color($img, 255, 220, 100));
                $this->ellipse($img, $rx-8, $ey-6, 8, 8, $this->color($img, 255, 220, 100));
                break;
            case 2: // heavy bark brow ridge, deep-set narrow
                $this->ellipse($img, $lx, $ey+4, 46, 28, $barkD);
                $this->ellipse($img, $rx, $ey+4, 46, 28, $barkD);
                // brow plates hanging over
                $this->ellipse($img, $lx, $ey-12, 52, 20, $barkD);
                $this->ellipse($img, $rx, $ey-12, 52, 20, $barkD);
                $this->ellipse($img, $lx, $ey+4, 28, 16, $heart);
                $this->ellipse($img, $rx, $ey+4, 28, 16, $heart);
                $this->ellipse($img, $lx, $ey+6, 14, 7, $black);
                $this->ellipse($img, $rx, $ey+6, 14, 7, $black);
                break;
            case 3: // growth rings visible — concentric circles
                $this->ellipse($img, $lx, $ey, 46, 46, $barkD);
                $this->ellipse($img, $rx, $ey, 46, 46, $barkD);
                foreach ([20,14,10,6] as $i => $r) {
                    $rc = ($i % 2 === 0) ? $heart : $bark;
                    $this->ellipse($img, $lx, $ey, $r*2, $r*2, $rc);
                    $this->ellipse($img, $rx, $ey, $r*2, $r*2, $rc);
                }
                $this->ellipse($img, $lx, $ey, 4, 4, $black);
                $this->ellipse($img, $rx, $ey, 4, 4, $black);
                break;
            case 4: // fire from within — burning orange glow
                $this->ellipse($img, $lx, $ey, 46, 38, $barkD);
                $this->ellipse($img, $rx, $ey, 46, 38, $barkD);
                $this->ellipse($img, $lx, $ey, 34, 28, $this->color($img, 80, 20, 0));
                $this->ellipse($img, $rx, $ey, 34, 28, $this->color($img, 80, 20, 0));
                $this->ellipse($img, $lx, $ey, 24, 20, $fire);
                $this->ellipse($img, $rx, $ey, 24, 20, $fire);
                $this->ellipse($img, $lx, $ey, 14, 12, $fireL);
                $this->ellipse($img, $rx, $ey, 14, 12, $fireL);
                $this->ellipse($img, $lx, $ey,  5,  4, $this->color($img, 255, 240, 180));
                $this->ellipse($img, $rx, $ey,  5,  4, $this->color($img, 255, 240, 180));
                break;
            case 5: // narrow suspicious slits
                $this->ellipse($img, $lx, $ey, 46, 36, $barkD);
                $this->ellipse($img, $rx, $ey, 46, 36, $barkD);
                $this->ellipse($img, $lx, $ey, 34, 24, $heart);
                $this->ellipse($img, $rx, $ey, 34, 24, $heart);
                // heavy eyelid covering top 2/3
                $this->ellipse($img, $lx, $ey-14, 46, 28, $barkD);
                $this->ellipse($img, $rx, $ey-14, 46, 28, $barkD);
                // just a slit visible
                $this->ellipse($img, $lx, $ey+8, 32, 8, $amber);
                $this->ellipse($img, $rx, $ey+8, 32, 8, $amber);
                $this->ellipse($img, $lx, $ey+8, 20, 4, $heart);
                $this->ellipse($img, $rx, $ey+8, 20, 4, $heart);
                break;
            case 6: // overgrown with moss — barely visible under growth
                $this->ellipse($img, $lx, $ey, 44, 36, $barkD);
                $this->ellipse($img, $rx, $ey, 44, 36, $barkD);
                $this->ellipse($img, $lx, $ey, 18, 14, $heart);
                $this->ellipse($img, $rx, $ey, 18, 14, $heart);
                // moss growing over eye sockets
                $this->ellipse($img, $lx-6, $ey-8, 28, 18, $moss);
                $this->ellipse($img, $rx+6, $ey-8, 28, 18, $moss);
                $this->ellipse($img, $lx-4, $ey-6, 18, 12, $mossL);
                $this->ellipse($img, $rx+4, $ey-6, 18, 12, $mossL);
                // peek of eye beneath
                $this->ellipse($img, $lx+2, $ey+4, 12, 8, $amber);
                $this->ellipse($img, $rx-2, $ey+4, 12, 8, $amber);
                break;
        }

        // ── NOSE (root-bump or knot) ───────────────────────────────────
        $this->ellipse($img, 100, 116, 24, 18, $barkD);
        $this->ellipse($img,  88, 120, 10, 10, $heart);
        $this->ellipse($img, 112, 120, 10, 10, $heart);

        // ── MOUTH ────────────────────────────────────────────────────
        switch ($mouthType) {
            case 0: // wide bark split — heartwood showing
                $this->ellipse($img, 100, 152, 100, 36, $barkD);
                $this->ellipse($img, 100, 154,  84, 26, $heart);
                $this->ellipse($img, 100, 156,  66, 16, $black);
                // jagged split edge
                foreach ([54,68,82,96,110,124,138,152] as $i => $mx) {
                    $my = ($i % 2 === 0) ? 138 : 142;
                    $this->polygon($img, [$mx-5, 142, $mx, $my, $mx+5, 142], $barkD);
                }
                break;
            case 1: // root-curl lips — roots curling up
                $this->ellipse($img, 100, 154, 90, 30, $barkD);
                $this->ellipse($img, 100, 155, 72, 20, $heart);
                // upper root curls pointing DOWN
                foreach ([60,76,90,100,110,124,140] as $rx2) {
                    $this->polygon($img, [$rx2-5,148, $rx2,140, $rx2+5,148], $barkL);
                }
                // lower root curls pointing UP
                foreach ([66,82,100,118,136] as $rx2) {
                    $this->polygon($img, [$rx2-5,162, $rx2,170, $rx2+5,162], $barkL);
                }
                break;
            case 2: // jagged broken grin — irregular bark teeth
                $this->ellipse($img, 100, 154, 100, 32, $barkD);
                $this->ellipse($img, 100, 156,  82, 22, $heart);
                // uneven broken teeth pointing DOWN
                $teeth = [[52,16],[66,22],[78,14],[92,24],[104,20],[116,24],[128,14],[142,22],[152,14]];
                foreach ($teeth as [$tx,$th]) {
                    $this->polygon($img, [$tx-6,148, $tx,148-$th, $tx+6,148], $barkL);
                }
                // lower broken teeth pointing UP
                $lteeth = [[60,12],[78,18],[100,14],[122,18],[140,12]];
                foreach ($lteeth as [$tx,$th]) {
                    $this->polygon($img, [$tx-5,162, $tx,162+$th, $tx+5,162], $barkD);
                }
                break;
            case 3: // sealed — bark grown over, just a line/crack
                imagesetthickness($img, 4);
                imageline($img, 46, 150, 154, 152, $barkD);
                imageline($img, 46, 150, 154, 152, $heart);
                imagesetthickness($img, 2);
                imageline($img, 46, 153, 154, 155, $barkD);
                imagesetthickness($img, 1);
                // sap seeping from sealed mouth
                $this->ellipse($img, 72, 158, 10, 12, $sap);
                $this->ellipse($img, 72, 166,  8,  8, $sapL);
                break;
            case 4: // mossy gaping hole — open O with moss inside
                $this->ellipse($img, 100, 154, 80, 40, $barkD);
                $this->ellipse($img, 100, 155, 60, 28, $black);
                // moss fringing the hole
                foreach ([[66,138,18,10],[88,136,14,9],[110,136,16,9],[130,140,18,10],
                          [58,150,12,8],[142,152,12,8],[66,168,18,10],[100,170,20,10],[134,168,18,10]] as [$mx,$my,$mw,$mh]) {
                    $this->ellipse($img, $mx, $my, $mw, $mh, $moss);
                    $this->ellipse($img, $mx, $my, (int)($mw*0.6), (int)($mh*0.6), $mossL);
                }
                break;
            case 5: // wide screaming split — dramatic open
                $this->ellipse($img, 100, 155, 104, 50, $barkD);
                $this->ellipse($img, 100, 158,  86, 38, $heart);
                $this->ellipse($img, 100, 160,  68, 26, $black);
                // upper teeth pointing DOWN
                foreach ([56,70,84,100,116,130,144] as $tx) {
                    $this->polygon($img, [$tx-6,142, $tx,132, $tx+6,142], $barkL);
                    $this->polygon($img, [$tx-4,142, $tx,136, $tx+4,142], $barkD);
                }
                // lower teeth pointing UP
                foreach ([62,78,100,122,138] as $tx) {
                    $this->polygon($img, [$tx-5,170, $tx,180, $tx+5,170], $barkL);
                }
                break;
        }

        // ── CROWN / TOP GROWTH ────────────────────────────────────────
        switch ($crownType) {
            case 0: break; // bare — no top growth

            case 1: // spreading branches
                imagesetthickness($img, 4);
                imageline($img, 100, 40, 100,  0, $barkD);
                imageline($img, 100, 20,  50,  0, $barkD);
                imageline($img, 100, 20, 150,  0, $barkD);
                imageline($img, 100, 10,  20,  0, $barkD);
                imageline($img, 100, 10, 180,  0, $barkD);
                imagesetthickness($img, 2);
                imageline($img,  50,  0,  10,  0, $barkD);
                imageline($img, 150,  0, 190,  0, $barkD);
                imagesetthickness($img, 1);
                // leaves on branch tips (season-colored)
                foreach ([[20,2],[50,0],[80,0],[120,0],[150,0],[180,2]] as [$lx2,$ly]) {
                    $this->ellipse($img, $lx2, $ly, 20, 14, $leaf);
                    $this->ellipse($img, $lx2, $ly, 12, 8,  $leafD);
                }
                break;

            case 2: // pine cones cluster
                $coneC = $this->color($img, 110, 70, 30);
                $coneD = $this->color($img,  80, 46, 16);
                foreach ([[76,8],[100,4],[124,8],[60,20],[140,20]] as [$cx,$cy]) {
                    // cone body
                    $this->polygon($img, [$cx-8,$cy+20, $cx,$cy, $cx+8,$cy+20], $coneC);
                    // scale rows
                    foreach ([4,8,12,16] as $row) {
                        $this->ellipse($img, $cx-4, $cy+$row, 10, 6, $coneD);
                        $this->ellipse($img, $cx+4, $cy+$row+2, 10, 6, $coneC);
                    }
                }
                break;

            case 3: // willow strands drooping
                $wC = $this->color($img, 130, 160, 80);
                imagesetthickness($img, 2);
                foreach ([20, 44, 68, 100, 132, 156, 180] as $wx) {
                    $wlen = $this->hash($username, 20+$wx, 20, 50);
                    imageline($img, $wx, 0, $wx-8, $wlen, $wC);
                }
                foreach ([30, 56, 80, 120, 144, 168] as $wx) {
                    $wlen = $this->hash($username, 21+$wx, 15, 40);
                    imageline($img, $wx, 0, $wx+6, $wlen, $leafD);
                }
                imagesetthickness($img, 1);
                break;

            case 4: // cherry blossoms
                foreach ([[56,10],[80,4],[100,6],[120,4],[144,10],[36,18],[164,18]] as [$fx,$fy]) {
                    // 5-petal flower
                    for ($p = 0; $p < 5; $p++) {
                        $ang = $p * 72 * M_PI / 180;
                        $px = (int)($fx + cos($ang) * 9);
                        $py = (int)($fy + sin($ang) * 9);
                        $this->ellipse($img, $px, $py, 10, 10, $blossom);
                    }
                    $this->ellipse($img, $fx, $fy, 8, 8, $blossomD);
                    $this->ellipse($img, $fx, $fy, 4, 4, $this->color($img, 255, 230, 180));
                }
                // branches connecting them
                imagesetthickness($img, 2);
                imageline($img, 100, 40, 80, 4, $barkD);
                imageline($img, 100, 40, 120, 4, $barkD);
                imageline($img,  80,  4,  56, 10, $barkD);
                imageline($img, 120,  4, 144, 10, $barkD);
                imagesetthickness($img, 1);
                break;

            case 5: // mushrooms growing from head
                foreach ([[68,22],[100,14],[132,22],[52,34],[148,34]] as [$mx,$my]) {
                    $ms = ($mx === 100) ? 1 : 0; // centre one bigger
                    $mw = $ms ? 28 : 22;
                    $mh = $ms ? 16 : 12;
                    $stemH = $ms ? 12 : 9;
                    // stem
                    $this->rect($img, $mx-3, $my, $mx+3, $my+$stemH, $mushC);
                    // cap
                    $this->ellipse($img, $mx, $my, $mw, $mh, $mushD);
                    $this->ellipse($img, $mx, $my-2, $mw-4, $mh-4, $mushC);
                    // spots
                    $this->ellipse($img, $mx-6, $my-2, 6, 5, $white);
                    $this->ellipse($img, $mx+5, $my-1, 5, 4, $white);
                }
                break;

            case 6: // leaves bursting out — full canopy
                // dense leaf cluster filling top of circle
                foreach ([[100,8],[72,10],[128,10],[50,20],[100,16],[150,20],
                          [34,32],[80,22],[120,22],[166,32]] as [$lx2,$ly]) {
                    $lw = $this->hash($username, 50+$lx2, 22, 36);
                    $lh = $this->hash($username, 51+$lx2, 14, 24);
                    $this->ellipse($img, $lx2, $ly, $lw, $lh, $leaf);
                    $this->ellipse($img, $lx2, $ly, (int)($lw*0.6), (int)($lh*0.6), $leafD);
                }
                break;
        }

        // ── EXTRAS ───────────────────────────────────────────────────
        switch ($extraType) {
            case 0: break; // none

            case 1: // moss patches on cheeks
                $this->ellipse($img,  22, 118, 30, 18, $moss);
                $this->ellipse($img, 178, 118, 30, 18, $moss);
                $this->ellipse($img,  20, 116, 18, 10, $mossL);
                $this->ellipse($img, 180, 116, 18, 10, $mossL);
                break;

            case 2: // sap dripping from eyes
                $this->ellipse($img, $lx+4, $ey+22, 8, 18, $sap);
                $this->ellipse($img, $lx+4, $ey+36, 10, 10, $sapL);
                $this->ellipse($img, $rx-4, $ey+22, 8, 18, $sap);
                $this->ellipse($img, $rx-4, $ey+36, 10, 10, $sapL);
                break;

            case 3: // lichen patches on face
                $lichenC = $this->color($img, 160, 170, 100);
                $lichenD = $this->color($img, 110, 120,  60);
                foreach ([[20,60,22,14],[170,50,18,12],[30,140,20,14],
                          [170,140,20,14],[100,44,18,12]] as [$lx2,$ly,$lw,$lh]) {
                    $this->ellipse($img, $lx2, $ly, $lw, $lh, $lichenC);
                    $this->ellipse($img, $lx2+2, $ly-2, (int)($lw*0.5), (int)($lh*0.5), $lichenD);
                }
                break;

            case 4: // runes carved into bark
                $runeC = $this->color($img, max(0,$hR-10), max(0,$hG-10), max(0,$hB-10));
                // simple rune shapes on cheeks and forehead
                // left cheek rune
                imagesetthickness($img, 2);
                imageline($img, 22, 126, 22, 142, $runeC);
                imageline($img, 16, 130, 28, 130, $runeC);
                imageline($img, 16, 138, 28, 138, $runeC);
                // right cheek rune
                imageline($img, 178, 126, 178, 142, $runeC);
                imageline($img, 172, 132, 184, 132, $runeC);
                imageline($img, 174, 138, 182, 138, $runeC);
                imageline($img, 178, 138, 182, 142, $runeC);
                // forehead rune
                imageline($img,  96, 52, 104, 52, $runeC);
                imageline($img, 100, 48, 100, 58, $runeC);
                imageline($img,  94, 56, 106, 56, $runeC);
                imagesetthickness($img, 1);
                break;

            case 5: // fungal growths on face edges
                foreach ([[8,80,20,14],[192,80,20,14],[8,120,18,12],[192,120,18,12]] as [$fx,$fy,$fw,$fh]) {
                    $this->ellipse($img, $fx, $fy, $fw, $fh, $mushD);
                    $this->ellipse($img, $fx, $fy-2, $fw-4, $fh-4, $mushC);
                    $this->ellipse($img, $fx-4, $fy-2, 8, 6, $white);
                }
                break;
        }

        return $img;
    }
}
