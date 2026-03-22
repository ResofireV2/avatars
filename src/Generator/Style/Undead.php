<?php

namespace Resofire\Avatars\Generator\Style;

class Undead extends AbstractStyle
{
    public function key(): string { return 'undead'; }
    public function name(): string { return 'Undead'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        // ── SLOT PICKS ──────────────────────────────────────────────
        $skinColors = [
            [74, 90, 42],   // grey-green decay
            [58, 74, 106],  // blue-pale fresh
            [90, 106, 16],  // plague yellow-green
            [26, 16, 12],   // charred black
            [154, 138, 100],// desiccated parchment
            [90, 58, 26],   // deep rot brown
            [58, 90, 58],   // swamp green
            [180, 170, 160],// pallid white
        ];

        [$sr, $sg, $sb] = $this->pick($username, 0, $skinColors);

        $eyeType    = $this->hash($username, 1, 0, 5); // 0-5
        $mouthType  = $this->hash($username, 2, 0, 4); // 0-4
        $damageType = $this->hash($username, 3, 0, 5); // 0-5
        $extraType  = $this->hash($username, 4, 0, 5); // 0-5
        $hairType   = $this->hash($username, 5, 0, 4); // 0-4
        $decayLevel = $this->hash($username, 6, 0, 3); // 0=fresh 1=moderate 2=heavy 3=skeletal

        // ── BASE COLORS ─────────────────────────────────────────────
        $skin   = $this->color($img, $sr, $sg, $sb);
        $skinD  = $this->color($img, max(0,(int)($sr*0.6)), max(0,(int)($sg*0.6)), max(0,(int)($sb*0.6)));
        $skinL  = $this->color($img, min(255,$sr+20), min(255,$sg+20), min(255,$sb+20));
        $bone   = $this->color($img, 210, 200, 176);
        $boneD  = $this->color($img, 170, 160, 138);
        $black  = $this->color($img, 8, 8, 8);
        $darkR  = $this->color($img, 40, 8, 8);
        $blood  = $this->color($img, 136, 0, 0);
        $bloodD = $this->color($img, 80, 0, 0);
        $white  = $this->color($img, 220, 215, 200);
        $green  = $this->color($img, 0, 170, 68);
        $greenL = $this->color($img, 0, 255, 136);
        $ooze   = $this->color($img, 100, 160, 0);
        $oozeL  = $this->color($img, 136, 204, 0);
        $red    = $this->color($img, 200, 0, 0);
        $redD   = $this->color($img, 120, 0, 0);
        $tongue = $this->color($img, 150, 34, 68);
        $tongueL= $this->color($img, 180, 50, 80);
        $mold   = $this->color($img, 42, 74, 16);
        $moldL  = $this->color($img, 60, 100, 20);
        $brain  = $this->color($img, 200, 160, 160);
        $brainD = $this->color($img, 170, 120, 120);
        $maggot = $this->color($img, 230, 228, 210);
        $fly    = $this->color($img, 16, 16, 16);
        $stitch = $this->color($img, 90, 58, 24);
        $stitchD= $this->color($img, 60, 34, 12);

        // ── SKIN FILL ───────────────────────────────────────────────
        $this->rect($img, 0, 0, 200, 200, $skin);

        // ── DECAY TEXTURE on skin ────────────────────────────────────
        // Darker rot patches get more intense with higher decay level
        $patchC = $this->color($img, max(0,$sr-16), max(0,$sg-16), max(0,$sb-16));
        $patchD = $this->color($img, max(0,$sr-30), max(0,$sg-30), max(0,$sb-30));
        $patchPositions = [[22,44],[70,38],[108,34],[154,40],[178,52],[18,68],[80,60],[140,58],[186,64],[28,84],[100,78],[172,80]];
        foreach ($patchPositions as $i => [$px, $py]) {
            if ($i > $decayLevel * 3 + 2) break;
            $this->ellipse($img, $px, $py, 22, 14, $patchC);
        }

        // Exposed bone patches for heavy/skeletal decay
        if ($decayLevel >= 2) {
            $this->ellipse($img, 30,  98, 18, 12, $bone);
            $this->ellipse($img, 28,  96, 12, 8,  $boneD);
        }
        if ($decayLevel === 3) {
            $this->ellipse($img, 170, 94, 18, 12, $bone);
            $this->ellipse($img, 172, 92, 12, 8,  $boneD);
            // Temple bone showing
            $this->ellipse($img, 14, 88, 14, 20, $bone);
            $this->ellipse($img, 186,88, 14, 20, $bone);
        }

        // ── HAIR ────────────────────────────────────────────────────
        $hairC  = $this->color($img, 26,  18,  8);
        $hairC2 = $this->color($img, 40,  28, 14);
        switch ($hairType) {
            case 0: // matted clumps across top
                $positions = [24, 46, 72, 100, 130, 158, 178];
                foreach ($positions as $i => $hx) {
                    $hw = $this->hash($username, 30+$i, 10, 20);
                    $hh = $this->hash($username, 37+$i, 8, 22);
                    $this->ellipse($img, $hx, (int)(4+$hh/3), $hw, $hh, ($i%2===0)?$hairC:$hairC2);
                }
                break;
            case 1: // patchy mange — gaps intentional
                foreach ([22, 54, 154, 178] as $i => $hx) {
                    $hw = $this->hash($username, 30+$i, 14, 24);
                    $this->ellipse($img, $hx, 10, $hw, 20, $hairC);
                }
                // bare scalp patch centre visible
                break;
            case 2: // long stringy at sides
                $this->ellipse($img, 10, 100, 18, 80, $hairC);
                $this->ellipse($img, 190,100, 18, 80, $hairC);
                $this->ellipse($img, 12, 100, 8,  70, $hairC2);
                $this->ellipse($img, 188,100, 8,  70, $hairC2);
                foreach ([30, 60, 100, 140, 170] as $hx) {
                    $this->ellipse($img, $hx, 8, 16, 16, $hairC);
                }
                break;
            case 3: // bald — just bare skin, nothing to draw
                break;
            case 4: // decayed mohawk — patchy remnant
                $mohawkSegs = [38, 52, 68, 100, 132, 148, 164];
                foreach ($mohawkSegs as $i => $hx) {
                    if ($this->hash($username, 40+$i, 0, 2) > 0) { // gaps
                        $hh = $this->hash($username, 47+$i, 10, 28);
                        $this->ellipse($img, $hx, (int)(4+$hh/4), 12, $hh, $hairC);
                    }
                }
                break;
        }

        // ── BROW RIDGE ──────────────────────────────────────────────
        $this->ellipse($img, 100, 72, 148, 24, $skinD);

        // ── EYES ────────────────────────────────────────────────────
        // Eye sockets always sunken/dark
        $this->ellipse($img, 62,  90, 50, 44, $black);
        $this->ellipse($img, 138, 90, 50, 44, $black);

        switch ($eyeType) {
            case 0: // cloudy milky
                $milky  = $this->color($img, 176, 176, 160);
                $milkyL = $this->color($img, 210, 210, 196);
                $this->ellipse($img, 62,  90, 38, 34, $milky);
                $this->ellipse($img, 138, 90, 38, 34, $milky);
                $this->ellipse($img, 62,  90, 22, 20, $milkyL);
                $this->ellipse($img, 138, 90, 22, 20, $milkyL);
                $this->ellipse($img, 62,  91, 10, 10, $this->color($img,60,60,54));
                $this->ellipse($img, 138, 91, 10, 10, $this->color($img,60,60,54));
                break;
            case 1: // hollow black sockets with faint red glow
                $this->ellipse($img, 62,  92, 20, 22, $darkR);
                $this->ellipse($img, 138, 92, 20, 22, $darkR);
                $this->ellipse($img, 62,  92, 8,  10, $redD);
                $this->ellipse($img, 138, 92, 8,  10, $redD);
                break;
            case 2: // glowing green infection
                $this->ellipse($img, 62,  90, 36, 32, $this->color($img,0,40,0));
                $this->ellipse($img, 138, 90, 36, 32, $this->color($img,0,40,0));
                $this->ellipse($img, 62,  90, 24, 22, $green);
                $this->ellipse($img, 138, 90, 24, 22, $green);
                $this->ellipse($img, 62,  90, 14, 14, $greenL);
                $this->ellipse($img, 138, 90, 14, 14, $greenL);
                $this->ellipse($img, 62,  90, 6,  6,  $this->color($img,200,255,220));
                $this->ellipse($img, 138, 90, 6,  6,  $this->color($img,200,255,220));
                // ooze drip from glowing eyes
                $this->ellipse($img, 58,  112, 7, 18, $ooze);
                $this->ellipse($img, 134, 112, 7, 18, $ooze);
                break;
            case 3: // burst red vessels
                $this->ellipse($img, 62,  90, 36, 32, $this->color($img,80,10,10));
                $this->ellipse($img, 138, 90, 36, 32, $this->color($img,80,10,10));
                $this->ellipse($img, 62,  90, 22, 20, $red);
                $this->ellipse($img, 138, 90, 22, 20, $red);
                $this->ellipse($img, 62,  90, 10, 10, $this->color($img,30,30,30));
                $this->ellipse($img, 138, 90, 10, 10, $this->color($img,30,30,30));
                // vessel lines
                foreach ([[48,76,60,88],[50,90,62,82],[74,76,62,88],[76,90,64,82]] as [$x1,$y1,$x2,$y2]) {
                    imageline($img, $x1,$y1,$x2,$y2, $red);
                }
                foreach ([[124,76,136,88],[126,90,138,82],[150,76,138,88],[152,90,140,82]] as [$x1,$y1,$x2,$y2]) {
                    imageline($img, $x1,$y1,$x2,$y2, $red);
                }
                break;
            case 4: // X eyes — fully dead
                $xColor = $this->color($img, 200, 192, 170);
                imagesetthickness($img, 6);
                imageline($img, 42, 70, 82, 110, $xColor);
                imageline($img, 82, 70, 42, 110, $xColor);
                imageline($img, 118, 70, 158, 110, $xColor);
                imageline($img, 158, 70, 118, 110, $xColor);
                imagesetthickness($img, 1);
                break;
            case 5: // one eye missing — left cloudy, right empty socket
                $milky = $this->color($img, 176, 176, 160);
                $this->ellipse($img, 62,  90, 38, 34, $milky);
                $this->ellipse($img, 62,  90, 18, 16, $this->color($img,210,210,196));
                $this->ellipse($img, 62,  91, 8,  8,  $this->color($img,60,60,54));
                // right socket empty + dried wound marks
                $this->ellipse($img, 138, 92, 16, 16, $this->color($img,26,10,8));
                imagesetthickness($img, 2);
                imageline($img, 122, 76, 130, 104, $skinD);
                imageline($img, 148, 76, 142, 104, $skinD);
                imagesetthickness($img, 1);
                break;
        }

        // ── NOSE ────────────────────────────────────────────────────
        // Heavy decay shows more nasal cavity
        if ($decayLevel >= 2) {
            $this->ellipse($img, 100, 122, 22, 28, $black);
            $this->ellipse($img, 90,  124, 10, 12, $this->color($img,max(0,$sr-40),max(0,$sg-40),max(0,$sb-40)));
            $this->ellipse($img, 110, 124, 10, 12, $this->color($img,max(0,$sr-40),max(0,$sg-40),max(0,$sb-40)));
        } else {
            $this->ellipse($img, 100, 122, 26, 18, $skinD);
            $this->ellipse($img, 88,  126, 10, 10, $this->color($img,max(0,$sr-30),max(0,$sg-30),max(0,$sb-30)));
            $this->ellipse($img, 112, 126, 10, 10, $this->color($img,max(0,$sr-30),max(0,$sg-30),max(0,$sb-30)));
        }

        // ── DAMAGE / WOUNDS ─────────────────────────────────────────
        switch ($damageType) {
            case 0: break; // none
            case 1: // head crack with blood seep
                imagesetthickness($img, 3);
                imageline($img, 104, 0, 96, 60, $black);
                imageline($img, 96, 28, 112, 38, $black);
                imagesetthickness($img, 1);
                $this->ellipse($img, 100, 28, 10, 24, $blood);
                break;
            case 2: // bite mark on cheek
                $biteC = $this->color($img, max(0,$sr-40), max(0,$sg-40), max(0,$sb-40));
                $this->ellipse($img, 36, 118, 32, 14, $biteC);
                // upper bite teeth marks pointing into flesh (down)
                foreach ([24,30,36,42,48] as $tx) {
                    $this->polygon($img, [$tx-3, 112, $tx, 104, $tx+3, 112], $skinD);
                }
                // lower bite teeth marks pointing up
                foreach ([24,30,36,42,48] as $tx) {
                    $this->polygon($img, [$tx-3, 124, $tx, 132, $tx+3, 124], $skinD);
                }
                $this->ellipse($img, 36, 118, 34, 16, $blood);
                break;
            case 3: // exposed cheekbone
                $this->ellipse($img, 32, 112, 34, 22, $bone);
                $this->ellipse($img, 30, 110, 26, 16, $boneD);
                // torn flesh edges
                $this->ellipse($img, 14,  110, 12, 16, $skinD);
                $this->ellipse($img, 34,  96,  16, 10, $skinD);
                $this->ellipse($img, 52,  112, 12, 14, $skinD);
                $this->ellipse($img, 34,  126, 18, 10, $skinD);
                break;
            case 4: // axe gash across forehead
                // draw a diagonal slash
                $gashC = $this->color($img, 8, 4, 4);
                imagesetthickness($img, 8);
                imageline($img, 40, 48, 130, 68, $gashC);
                imagesetthickness($img, 4);
                imageline($img, 42, 48, 132, 68, $blood);
                imagesetthickness($img, 1);
                $this->ellipse($img, 82, 56, 28, 8, $bloodD);
                break;
            case 5: // multiple small wounds
                foreach ([[28,78],[154,84],[40,130],[166,118],[100,58]] as $i => [$wx,$wy]) {
                    $wr = $this->hash($username, 60+$i, 6, 14);
                    $this->ellipse($img, $wx, $wy, $wr, (int)($wr*0.6), $black);
                    $this->ellipse($img, $wx, $wy, (int)($wr*0.6), (int)($wr*0.4), $blood);
                }
                break;
        }

        // ── MOUTH ───────────────────────────────────────────────────
        switch ($mouthType) {
            case 0: // slack open jaw — wide, tongue lolling
                $this->ellipse($img, 100, 156, 68, 36, $black);
                $this->ellipse($img, 100, 162, 50, 24, $darkR);
                // tongue
                $this->ellipse($img, 100, 166, 34, 20, $tongue);
                $this->ellipse($img, 102, 170, 22, 12, $tongueL);
                // upper teeth pointing DOWN
                foreach ([66,78,90,102,114,126,136] as $tx) {
                    $this->polygon($img, [$tx-6, 146, $tx, 134, $tx+6, 146], $bone);
                    $this->polygon($img, [$tx-4, 146, $tx, 138, $tx+4, 146], $boneD);
                }
                // lower teeth pointing UP
                foreach ([70,82,94,106,118,130] as $tx) {
                    $this->polygon($img, [$tx-5, 168, $tx, 178, $tx+5, 168], $bone);
                }
                break;
            case 1: // stitched shut
                $this->rect($img, 48, 148, 152, 158, $skinD);
                // stitches
                imagesetthickness($img, 2);
                foreach ([56,68,80,92,104,116,128,140,152] as $sx) {
                    imageline($img, $sx, 142, $sx, 164, $stitch);
                }
                imagesetthickness($img, 1);
                imageline($img, 48, 146, 152, 146, $stitchD);
                imageline($img, 48, 160, 152, 160, $stitchD);
                // some stitches pulling flesh bulges
                $this->ellipse($img, 74, 148, 10, 6, $skinL);
                $this->ellipse($img, 122, 152, 10, 6, $skinL);
                break;
            case 2: // wide grin with broken/missing teeth
                $this->ellipse($img, 100, 158, 78, 36, $black);
                $this->ellipse($img, 100, 164, 60, 24, $darkR);
                // upper teeth — jagged, some missing, pointing DOWN
                $teethData = [[60,10],[70,14],[80,16],[94,0],[104,14],[116,16],[128,0],[136,12],[146,10]];
                foreach ($teethData as $i => [$tx, $th]) {
                    if ($th === 0) continue; // gap
                    $toothH = $this->hash($username, 70+$i, (int)($th*0.6), $th+2);
                    $this->polygon($img, [$tx-5, 146, $tx, 146-$toothH, $tx+5, 146], $bone);
                    $this->polygon($img, [$tx-3, 146, $tx, 146-$toothH+4, $tx+3, 146], $boneD);
                }
                // lower teeth pointing UP
                foreach ([64,76,92,108,124,138,148] as $i => $tx) {
                    if ($this->hash($username, 80+$i, 0, 3) > 0) { // some missing
                        $this->polygon($img, [$tx-4, 172, $tx, 182, $tx+4, 172], $bone);
                    }
                }
                break;
            case 3: // screaming — wide vertical
                $this->ellipse($img, 100, 160, 46, 56, $black);
                $this->ellipse($img, 100, 164, 30, 44, $darkR);
                // upper teeth pointing DOWN
                foreach ([80,90,100,110,120] as $tx) {
                    $this->polygon($img, [$tx-5, 142, $tx, 132, $tx+5, 142], $bone);
                }
                // lower teeth pointing UP
                foreach ([82,94,106,118] as $tx) {
                    $this->polygon($img, [$tx-4, 178, $tx, 188, $tx+4, 178], $bone);
                }
                break;
            case 4: // lower jaw missing — only upper teeth dangling
                // torn flesh where jaw was
                $this->ellipse($img, 70,  154, 22, 12, $skinD);
                $this->ellipse($img, 100, 158, 28, 12, $skinD);
                $this->ellipse($img, 130, 154, 22, 12, $skinD);
                // upper teeth pointing DOWN with nothing below
                foreach ([58,68,78,90,100,112,124,136,146] as $tx) {
                    $this->polygon($img, [$tx-5, 148, $tx, 136, $tx+5, 148], $bone);
                    $this->polygon($img, [$tx-3, 148, $tx, 140, $tx+3, 148], $boneD);
                }
                // dangling tongue
                $this->ellipse($img, 100, 166, 22, 32, $tongue);
                $this->ellipse($img, 102, 174, 14, 18, $tongueL);
                break;
        }

        // ── EXTRAS ──────────────────────────────────────────────────
        switch ($extraType) {
            case 0: break; // none
            case 1: // maggots in wound patches
                $woundC = $this->color($img, max(0,$sr-40), max(0,$sg-40), max(0,$sb-40));
                foreach ([[28,66,3],[160,72,3],[38,108,3]] as [$mx,$my,$count]) {
                    $this->ellipse($img, $mx, $my, 20, 14, $woundC);
                    for ($m = 0; $m < $count+1; $m++) {
                        $mox = $mx - 8 + $m * 6;
                        $this->ellipse($img, $mox, $my, 6, 10, $maggot);
                    }
                }
                // maggots near an eye
                $this->ellipse($img, 52, 102, 6, 10, $maggot);
                $this->ellipse($img, 62, 104, 6, 10, $maggot);
                break;
            case 2: // flies buzzing
                $flyWing = $this->color($img, 80, 80, 80);
                $flyData = [[22,40],[52,28],[156,32],[172,48],[182,34],[14,62],[188,66],[60,56],[148,56]];
                foreach ($flyData as [$fx, $fy]) {
                    $this->ellipse($img, $fx, $fy, 6, 6, $fly);
                    imageline($img, $fx-3, $fy-1, $fx-8, $fy-3, $flyWing);
                    imageline($img, $fx-3, $fy+1, $fx-8, $fy+3, $flyWing);
                }
                break;
            case 3: // mold patches
                foreach ([[22,52,28,20],[162,58,24,18],[96,30,20,14],[36,128,22,16],[168,122,20,14]] as [$mx,$my,$mw,$mh]) {
                    $this->ellipse($img, $mx, $my, $mw, $mh, $mold);
                    $this->ellipse($img, $mx-4, $my-2, (int)($mw*0.6), (int)($mh*0.6), $moldL);
                }
                break;
            case 4: // exposed brain
                $this->ellipse($img, 100, 36, 60, 34, $brain);
                // brain fold lumps
                foreach ([[82,34],[96,28],[110,26],[122,32],[88,42],[102,40],[116,38]] as [$bx,$by]) {
                    $this->ellipse($img, $bx, $by, 18, 10, $brainD);
                }
                // skull rim
                imagesetthickness($img, 5);
                imagearc($img, 100, 36, 64, 38, 180, 360, $boneD);
                imagesetthickness($img, 1);
                break;
            case 5: // dripping ooze everywhere
                // from eyes
                $this->ellipse($img, 56,  116, 8,  24, $ooze);
                $this->ellipse($img, 56,  130, 10, 12, $oozeL);
                $this->ellipse($img, 132, 116, 8,  24, $ooze);
                $this->ellipse($img, 132, 130, 10, 12, $oozeL);
                // from nose
                $this->ellipse($img, 94,  140, 6,  16, $ooze);
                $this->ellipse($img, 106, 138, 6,  14, $ooze);
                // pool at bottom
                $this->ellipse($img, 100, 178, 28, 12, $oozeL);
                break;
        }

        return $img;
    }
}
