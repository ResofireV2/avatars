<?php

namespace Resofire\Avatars\Generator\Style;

class Pirate extends AbstractStyle
{
    public function key(): string { return 'pirate'; }
    public function name(): string { return 'Pirate'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        // ── PALETTES ────────────────────────────────────────────────
        $bgColors = [
            [10,  42,  90],  // deep navy
            [17,  17,  17],  // near black
            [26,  90,  74],  // dark teal
            [170, 136,  0],  // dark gold
            [74,  74,  90],  // steel blue-grey
            [122, 74,  26],  // dark brown
        ];
        $bandanaColors = [
            [204,  34,  34],  // pirate red
            [17,   17,  17],  // black
            [10,   42,  90],  // navy
            [136,   0, 136],  // purple
            [34,  102,  34],  // dark green
            [204, 136,   0],  // gold
        ];
        $eyeColors = [
            [34,  102, 255],  // bright blue
            [255, 255, 255],  // white/pale
            [68,  204, 136],  // sea green
            [255, 204,   0],  // amber
            [136, 136, 204],  // lavender
            [255, 136,  34],  // orange
        ];

        [$br, $bg2, $bb]   = $this->pick($username, 0, $bgColors);
        [$bdr, $bdg, $bdb] = $this->pick($username, 1, $bandanaColors);
        [$er, $eg, $eb]    = $this->pick($username, 2, $eyeColors);

        $bandanaDecor = $this->hash($username, 3, 0, 6); // 0-6
        $eyepatchSide = $this->hash($username, 4, 0, 1); // 0=left, 1=right
        $patchDecor   = $this->hash($username, 5, 0, 5); // 0-5
        $piercingType = $this->hash($username, 6, 0, 5); // 0-5
        $earringType  = $this->hash($username, 7, 0, 5); // 0-5
        $beardType    = $this->hash($username, 8, 0, 5); // 0-5
        $mouthType    = $this->hash($username, 9, 0, 6); // 0-6
        $hasScar      = $this->hash($username, 10, 0, 1);

        // ── COLORS ──────────────────────────────────────────────────
        $bg      = $this->color($img, $br, $bg2, $bb);
        $bgD     = $this->color($img, (int)($br*0.6), (int)($bg2*0.6), (int)($bb*0.6));
        $bandana = $this->color($img, $bdr, $bdg, $bdb);
        $bandanaD= $this->color($img, (int)($bdr*0.7), (int)($bdg*0.7), (int)($bdb*0.7));
        $bandanaL= $this->color($img, min(255,$bdr+40), min(255,$bdg+40), min(255,$bdb+40));
        $eye     = $this->color($img, $er, $eg, $eb);
        $eyeD    = $this->color($img, (int)($er*0.3), (int)($eg*0.3), (int)($eb*0.3));
        $patch   = $this->color($img, 10, 10, 10);
        $patchRim= $this->color($img, 58, 58, 58);
        $white   = $this->color($img, 255, 255, 255);
        $black   = $this->color($img, 10, 10, 10);
        $skull   = $this->color($img, 220, 215, 195);
        $skullD  = $this->color($img, 170, 164, 144);
        $gold    = $this->color($img, 204, 170,   0);
        $goldL   = $this->color($img, 255, 210,  30);
        $silver  = $this->color($img, 180, 180, 180);
        $red     = $this->color($img, 200,  30,  30);
        $brown   = $this->color($img,  90,  54,  18);
        $brownD  = $this->color($img,  58,  32,   8);
        $hairC   = $this->color($img,  26,  18,   8);
        $hairC2  = $this->color($img,  50,  36,  14);
        $stitch  = $this->color($img, 130,  90,  40);

        // ── BACKGROUND ──────────────────────────────────────────────
        $this->rect($img, 0, 0, 200, 200, $bg);

        // ── BANDANA STRIP ───────────────────────────────────────────
        $this->rect($img, 0,  0, 200, 50, $bandana);
        $this->rect($img, 0, 48, 200, 56, $bandanaD);

        // ── BANDANA DECORATION (small, fits in strip) ────────────────
        switch ($bandanaDecor) {
            case 0: // skull
                $this->ellipse($img, 100, 24, 36, 36, $skull);
                $this->ellipse($img, 90,  22,  9, 10, $black);
                $this->ellipse($img, 110, 22,  9, 10, $black);
                $this->rect($img,  88, 30,  94, 36, $black);
                $this->rect($img,  97, 30, 103, 36, $black);
                $this->rect($img, 106, 30, 112, 36, $black);
                break;
            case 1: // crossbones
                imagesetthickness($img, 3);
                imageline($img, 72, 6,  130, 48, $skull);
                imageline($img, 130, 6,  72, 48, $skull);
                imagesetthickness($img, 1);
                foreach ([[72,6],[130,6],[72,48],[130,48]] as [$cx,$cy]) {
                    $this->ellipse($img, $cx, $cy, 12, 12, $skull);
                }
                break;
            case 2: // compass rose
                $this->polygon($img, [100,4, 104,24, 100,20, 96,24], $skull);
                $this->polygon($img, [100,48, 104,28, 100,32, 96,28], $skull);
                $this->polygon($img, [72,26, 92,22, 88,26, 92,30], $skull);
                $this->polygon($img, [128,26, 108,22, 112,26, 108,30], $skull);
                $this->ellipse($img, 100, 26, 14, 14, $skull);
                $this->ellipse($img, 100, 26, 6,  6,  $bandana);
                break;
            case 3: // anchor
                imagesetthickness($img, 3);
                imageline($img, 100, 8, 100, 46, $skull);
                imagesetthickness($img, 1);
                imagesetthickness($img, 2);
                imagearc($img, 100, 12, 20, 12, 180, 360, $skull);
                imageline($img, 84, 46, 116, 46, $skull);
                imagearc($img, 86, 38, 14, 16, 90, 230, $skull);
                imagearc($img, 114, 38, 14, 16, 310, 90, $skull);
                imagesetthickness($img, 1);
                break;
            case 4: // gold coins
                foreach ([64, 84, 100, 116, 136] as $cx) {
                    $this->ellipse($img, $cx, 24, 18, 18, $gold);
                    $this->ellipse($img, $cx, 24, 10, 10, $goldL);
                }
                break;
            case 5: // plain knot on right
                $this->ellipse($img, 166, 26, 28, 28, $bandanaD);
                $this->ellipse($img, 166, 26, 18, 18, $bandana);
                $this->rect($img, 166, 34, 200, 48, $bandana);
                break;
            case 6: // torn/battle worn
                // rips cutting into bandana from top
                foreach ([30, 56, 88, 120, 152, 174] as $rx) {
                    $this->polygon($img, [$rx-4, 0, $rx+4, 0, $rx+2, 16, $rx-2, 16], $bg);
                }
                // frayed bottom edge
                foreach ([20, 38, 58, 78, 100, 122, 142, 162, 180] as $rx) {
                    $this->polygon($img, [$rx-4, 48, $rx, 40, $rx+4, 48], $bg);
                }
                // blood stain
                $this->ellipse($img, 70, 28, 20, 16, $this->color($img, 80, 0, 0));
                break;
        }

        // ── EYEPATCH SIDE SETUP ─────────────────────────────────────
        $openX  = $eyepatchSide === 0 ? 134 : 66;
        $patchX = $eyepatchSide === 0 ? 66  : 134;

        // ── SCAR (behind patch) ──────────────────────────────────────
        if ($hasScar) {
            $scarC = $this->color($img, max(0,$br-24), max(0,$bg2-24), max(0,$bb-24));
            imagesetthickness($img, 3);
            imageline($img, $patchX-4, 68, $patchX+4, 132, $scarC);
            imagesetthickness($img, 1);
        }

        // ── OPEN EYE ────────────────────────────────────────────────
        $this->ellipse($img, $openX, 100, 52, 52, $white);
        $this->ellipse($img, $openX, 101, 36, 36, $eye);
        $this->ellipse($img, $openX, 102, 18, 18, $eyeD);
        $this->ellipse($img, $openX-8, 93, 12, 12, $white);

        // ── EYEPATCH BASE ───────────────────────────────────────────
        $this->ellipse($img, $patchX, 100, 52, 48, $patch);
        $this->ellipse($img, $patchX, 100, 44, 40, $patchRim);
        $this->ellipse($img, $patchX, 100, 36, 32, $patch);

        // ── EYEPATCH STRAP ───────────────────────────────────────────
        $this->rect($img, $patchX-26, 97, $patchX+26, 103, $patchRim);

        // ── EYEPATCH DECORATION (drawn AFTER strap so it's on top) ──
        switch ($patchDecor) {
            case 0: break; // plain
            case 1: // skull riveted
                $this->ellipse($img, $patchX, 100, 22, 20, $skull);
                $this->ellipse($img, $patchX-5, 98,  6,  7, $patch);
                $this->ellipse($img, $patchX+5, 98,  6,  7, $patch);
                $this->rect($img, $patchX-8, 103, $patchX-4, 108, $patch);
                $this->rect($img, $patchX-2, 103, $patchX+2, 108, $patch);
                $this->rect($img, $patchX+4, 103, $patchX+8, 108, $patch);
                break;
            case 2: // gold trim ring
                imagesetthickness($img, 4);
                imagearc($img, $patchX, 100, 48, 44, 0, 360, $gold);
                imagesetthickness($img, 1);
                // re-draw strap in gold
                $this->rect($img, $patchX-26, 97, $patchX+26, 103, $gold);
                break;
            case 3: // X stitching
                imagesetthickness($img, 2);
                imageline($img, $patchX-14, $patchX > 100 ? 88 : 88, $patchX+14, 112, $stitch);
                imageline($img, $patchX+14, $patchX > 100 ? 88 : 88, $patchX-14, 112, $stitch);
                imagesetthickness($img, 1);
                break;
            case 4: // cracked
                imagesetthickness($img, 2);
                imageline($img, $patchX-4, 88, $patchX,   104, $patchRim);
                imageline($img, $patchX,   104, $patchX+8, 112, $patchRim);
                imageline($img, $patchX,   104, $patchX-6, 112, $patchRim);
                imagesetthickness($img, 1);
                break;
            case 5: // gem inset
                $gemColors = [$this->color($img,220,30,60), $this->color($img,30,100,220),
                              $this->color($img,30,180,80),  $this->color($img,180,30,180)];
                $gemIdx = $this->hash($username, 50, 0, 3);
                $this->ellipse($img, $patchX, 100, 16, 16, $gold);
                $this->ellipse($img, $patchX, 100, 11, 11, $gemColors[$gemIdx]);
                $this->ellipse($img, $patchX-3, 97,  4,  4, $white);
                break;
        }

        // ── NOSE ────────────────────────────────────────────────────
        $this->ellipse($img, 100, 122, 26, 18, $bgD);
        $this->ellipse($img, 88,  126, 10, 10, $this->color($img, max(0,$br-30), max(0,$bg2-30), max(0,$bb-30)));
        $this->ellipse($img, 112, 126, 10, 10, $this->color($img, max(0,$br-30), max(0,$bg2-30), max(0,$bb-30)));

        // ── PIERCINGS ───────────────────────────────────────────────
        $pierceGold = $this->hash($username, 51, 0, 1) ? $gold : $silver;
        switch ($piercingType) {
            case 0: break; // none
            case 1: // septum ring
                imagesetthickness($img, 3);
                imagearc($img, 100, 128, 22, 16, 0, 180, $pierceGold);
                imagesetthickness($img, 1);
                break;
            case 2: // right nostril stud
                $this->ellipse($img, 114, 125, 7, 7, $gold);
                $this->ellipse($img, 113, 124, 3, 3, $white);
                break;
            case 3: // brow ring above open eye
                imagesetthickness($img, 3);
                imagearc($img, $openX, 82, 20, 14, 200, 340, $pierceGold);
                imagesetthickness($img, 1);
                break;
            case 4: // both nostril rings
                imagesetthickness($img, 3);
                imagearc($img, 88,  130, 14, 10, 0, 180, $pierceGold);
                imagearc($img, 112, 130, 14, 10, 0, 180, $pierceGold);
                imagesetthickness($img, 1);
                break;
            case 5: // brow stud + septum
                $this->ellipse($img, $openX+4, 80, 8, 8, $gold);
                $this->ellipse($img, $openX+3, 79, 3, 3, $white);
                imagesetthickness($img, 3);
                imagearc($img, 100, 128, 22, 16, 0, 180, $pierceGold);
                imagesetthickness($img, 1);
                break;
        }

        // ── EARRING ─────────────────────────────────────────────────
        // Earring on the open-eye side (ear is visible there)
        $earX = $openX > 100 ? 188 : 12;
        $earY = 100;
        switch ($earringType) {
            case 0: break; // none
            case 1: // gold hoop
                imagesetthickness($img, 4);
                imagearc($img, $earX, $earY, 18, 18, 0, 360, $gold);
                imagesetthickness($img, 1);
                break;
            case 2: // dangling gem
                imageline($img, $earX, $earY-6, $earX, $earY+4, $gold);
                $gemC = $this->color($img, 30, 80, 220);
                $this->ellipse($img, $earX, $earY+8, 10, 12, $gold);
                $this->ellipse($img, $earX, $earY+8, 7,  9,  $gemC);
                $this->ellipse($img, $earX-2, $earY+5, 3, 3, $white);
                break;
            case 3: // skull drop
                imageline($img, $earX, $earY-6, $earX, $earY+2, $silver);
                $this->ellipse($img, $earX, $earY+8,  14, 14, $skull);
                $this->ellipse($img, $earX-3, $earY+7,  4,  5, $patch);
                $this->ellipse($img, $earX+3, $earY+7,  4,  5, $patch);
                $this->rect($img, $earX-5, $earY+11, $earX-2, $earY+14, $patch);
                $this->rect($img, $earX-1, $earY+11, $earX+1, $earY+14, $patch);
                $this->rect($img, $earX+2, $earY+11, $earX+5, $earY+14, $patch);
                break;
            case 4: // multiple hoops
                foreach ([0, 10, 20] as $i => $offset) {
                    $hc = $i < 2 ? $gold : $silver;
                    imagesetthickness($img, 3);
                    imagearc($img, $earX, $earY - 8 + $offset, 14, 14, 0, 360, $hc);
                    imagesetthickness($img, 1);
                }
                break;
            case 5: // hook earring
                imagesetthickness($img, 3);
                imagearc($img, $earX, $earY, 16, 20, 270, 180, $silver);
                imagesetthickness($img, 1);
                $this->ellipse($img, $earX, $earY-10, 5, 5, $silver);
                break;
        }

        // ── MOUTH ───────────────────────────────────────────────────
        switch ($mouthType) {
            case 0: // smirk (original)
                $this->ellipse($img, 100, 152, 80, 28, $bgD);
                $this->polygon($img, [82, 148, 76, 166, 90, 148], $skull);
                break;
            case 1: // gold tooth smirk
                $this->ellipse($img, 100, 152, 80, 28, $bgD);
                $this->polygon($img, [100, 148, 94, 166, 106, 148], $goldL);
                $this->ellipse($img, 100, 156, 8, 7, $gold);
                break;
            case 2: // gap-toothed grin
                $this->ellipse($img, 100, 154, 90, 32, $bgD);
                foreach ([72, 84, 114, 126] as $tx) {
                    $this->polygon($img, [$tx-6, 146, $tx, 134, $tx+6, 146], $skull);
                }
                // gap in middle (no tooth at 100)
                break;
            case 3: // pipe
                $this->ellipse($img, 100, 152, 70, 24, $bgD);
                $this->polygon($img, [90, 148, 84, 162, 96, 148], $skull);
                // pipe stem going to the side
                imagesetthickness($img, 5);
                imageline($img, 72, 154, 28, 150, $brown);
                imagesetthickness($img, 1);
                $this->ellipse($img, 24, 146, 14, 18, $brownD);
                $this->ellipse($img, 24, 143, 10,  8, $brownD);
                // smoke
                imagesetthickness($img, 2);
                imagearc($img, 20, 134, 12, 12, 200, 360, $this->color($img,160,160,160));
                imagearc($img, 24, 124, 12, 12, 180, 340, $this->color($img,140,140,140));
                imagesetthickness($img, 1);
                break;
            case 4: // full snarl — open mouth
                $this->ellipse($img, 100, 154, 80, 32, $bgD);
                $this->ellipse($img, 100, 158, 64, 22, $this->color($img,max(0,$br-30),max(0,$bg2-30),max(0,$bb-30)));
                // upper teeth pointing DOWN
                foreach ([68, 80, 92, 100, 108, 120, 132] as $tx) {
                    $this->polygon($img, [$tx-5, 144, $tx, 136, $tx+5, 144], $skull);
                }
                // lower teeth pointing UP
                foreach ([72, 84, 100, 116, 128] as $tx) {
                    $this->polygon($img, [$tx-4, 166, $tx, 174, $tx+4, 166], $skullD);
                }
                break;
            case 5: // broken tooth
                $this->ellipse($img, 100, 152, 80, 28, $bgD);
                $this->polygon($img, [82, 148, 76, 164, 90, 148], $skull);
                // broken centre — jagged tip
                $this->polygon($img, [98, 148, 94, 162, 100, 156, 106, 162, 110, 148], $skull);
                $this->polygon($img, [118, 148, 114, 164, 122, 148], $skull);
                break;
            case 6: // scowl — tight line
                imagesetthickness($img, 4);
                imagearc($img, 100, 168, 70, 28, 200, 340, $bgD);
                imagesetthickness($img, 1);
                break;
        }

        // ── BEARD ───────────────────────────────────────────────────
        switch ($beardType) {
            case 0: break; // clean shaven
            case 1: // stubble — dark shadow on lower face
                $stubble = $this->color($img, max(0,$br-14), max(0,$bg2-14), max(0,$bb-14));
                $this->ellipse($img, 100, 162, 110, 40, $stubble);
                break;
            case 2: // goatee + thin moustache
                $this->ellipse($img, 100, 168, 36, 28, $hairC);
                $this->ellipse($img, 100, 165, 26, 18, $hairC2);
                $this->ellipse($img, 80,  150, 20,  8, $hairC);
                $this->ellipse($img, 120, 150, 20,  8, $hairC);
                break;
            case 3: // full beard
                $this->ellipse($img, 100, 172, 100, 44, $hairC);
                $this->ellipse($img, 60,  164,  32, 40, $hairC);
                $this->ellipse($img, 140, 164,  32, 40, $hairC);
                $this->ellipse($img, 100, 168,  80, 28, $hairC2);
                break;
            case 4: // braided beard with bead
                $this->ellipse($img, 100, 160, 50, 16, $hairC);
                $this->ellipse($img, 100, 170, 18, 22, $hairC);
                // bead
                $this->ellipse($img, 100, 178, 12, 10, $gold);
                $this->ellipse($img, 100, 183, 10, 10, $hairC);
                $this->ellipse($img, 100, 190,  9,  8, $hairC2);
                // second bead
                $this->ellipse($img, 100, 190, 10,  8, $this->color($img,180,30,30));
                break;
            case 5: // mutton chops — thick sideburns
                $this->ellipse($img, 28,  148, 24, 44, $hairC);
                $this->ellipse($img, 172, 148, 24, 44, $hairC);
                $this->ellipse($img, 28,  146, 16, 32, $hairC2);
                $this->ellipse($img, 172, 146, 16, 32, $hairC2);
                break;
        }

        return $img;
    }
}
