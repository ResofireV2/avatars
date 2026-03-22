<?php

namespace Resofire\Avatars\Generator\Style;

class Fantasy extends AbstractStyle
{
    public function key(): string { return 'fantasy'; }
    public function name(): string { return 'Fantasy'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        // Creature type is the primary slot — each one is a completely different face
        $creatureType = $this->hash($username, 0, 0, 7);
        // 0=unicorn, 1=dragon, 2=troll, 3=minotaur, 4=cyclops, 5=goblin, 6=treant, 7=fairy

        // Color palette index — 4 palettes per creature
        $paletteIdx  = $this->hash($username, 1, 0, 3);

        // Expression — 5 moods
        $expression  = $this->hash($username, 2, 0, 4);
        // 0=neutral, 1=happy, 2=angry, 3=sad, 4=surprised

        // Detail variant — 4 options
        $detail      = $this->hash($username, 3, 0, 3);
        // 0=normal, 1=scarred/battle, 2=elder/ancient, 3=young/noble

        $this->rect($img, 0, 0, 200, 200, $this->color($img, 10, 10, 10));

        switch ($creatureType) {
            case 0: $this->drawUnicorn($img, $username, $paletteIdx, $expression, $detail); break;
            case 1: $this->drawDragon($img, $username, $paletteIdx, $expression, $detail); break;
            case 2: $this->drawTroll($img, $username, $paletteIdx, $expression, $detail); break;
            case 3: $this->drawMinotaur($img, $username, $paletteIdx, $expression, $detail); break;
            case 4: $this->drawCyclops($img, $username, $paletteIdx, $expression, $detail); break;
            case 5: $this->drawGoblin($img, $username, $paletteIdx, $expression, $detail); break;
            case 6: $this->drawTreant($img, $username, $paletteIdx, $expression, $detail); break;
            case 7: $this->drawFairy($img, $username, $paletteIdx, $expression, $detail); break;
        }

        return $img;
    }

    // ── UNICORN ─────────────────────────────────────────────────────
    private function drawUnicorn(\GdImage $img, string $u, int $p, int $expr, int $det): void
    {
        $palettes = [
            [[232,216,248], [204,136,255], [255,170,238], [136,68,204]],  // white/lavender
            [[26, 10, 42],  [102,0,170],   [34, 0, 68],   [170,68,255]], // dark/midnight
            [[255,240,220], [255,180,100], [255,220,150], [200,100,50]], // golden sunrise
            [[200,240,255], [100,180,255], [150,220,255], [50,100,200]], // ice blue
        ];
        [$bg, $mane1, $mane2, $eye] = $palettes[$p];

        $bgC   = $this->color($img, ...$bg);
        $m1    = $this->color($img, ...$mane1);
        $m2    = $this->color($img, ...$mane2);
        $eyeC  = $this->color($img, ...$eye);
        $eyeD  = $this->color($img, (int)($eye[0]*0.3), (int)($eye[1]*0.3), (int)($eye[2]*0.3));
        $horn  = $det === 2 ? $this->color($img, 180,160,80) : $this->color($img, 255,220,80);
        $hornL = $this->color($img, 255,240,140);
        $white = $this->color($img, 255,255,255);
        $blush = $this->color($img, min(255,$bg[0]+40), (int)($bg[1]*0.7), (int)($bg[2]*0.9));
        $nose  = $this->color($img, max(0,$bg[0]-20), max(0,$bg[1]-20), max(0,$bg[2]-20));

        $this->rect($img, 0, 0, 200, 200, $bgC);

        // Mane tufts — vary count/spread by detail
        $manePositions = $det === 3 ? [44,58,74,90,106,122] : [34,50,64,78,94,110,126,142];
        foreach ($manePositions as $i => $mx) {
            $h = $this->hash($u, 10+$i, 14, 30);
            $col = ($i % 2 === 0) ? $m1 : $m2;
            $this->ellipse($img, $mx, (int)(6+$h/3), 18, $h*2, $col);
        }

        // Horn
        $hornTwist = $det === 2 ? 6 : 4;
        $this->polygon($img, [100,20, 92,64, 108,64], $horn);
        $this->polygon($img, [100,20, 96,56, 100,50], $hornL);
        // Horn spiral lines
        for ($i = 0; $i < 3; $i++) {
            imageline($img, 100-$hornTwist+$i*3, 30+$i*10, 100+$hornTwist-$i*3, 36+$i*10, $hornL);
        }

        // Face
        $this->ellipse($img, 100, 128, 130, 140, $bgC);

        // Eyes — big and liquid
        $lx = 62; $rx = 138; $ey = 116;
        $this->ellipse($img, $lx, $ey, 38, 42, $white);
        $this->ellipse($img, $rx, $ey, 32, 36, $white);
        $this->ellipse($img, $lx, $ey+1, 28, 32, $eyeC);
        $this->ellipse($img, $rx, $ey+1, 22, 26, $eyeC);

        // Expression-based pupils/eyes
        switch ($expr) {
            case 0: // neutral round
                $this->ellipse($img, $lx, $ey+2, 14, 16, $eyeD);
                $this->ellipse($img, $rx, $ey+2, 11, 12, $eyeD);
                break;
            case 1: // happy arc eyes
                $this->ellipse($img, $lx, $ey, 38, 42, $bgC); // cover with bg
                $this->ellipse($img, $rx, $ey, 32, 36, $bgC);
                imagesetthickness($img, 5);
                imagearc($img, $lx, $ey+8, 36, 28, 200, 340, $eyeC);
                imagearc($img, $rx, $ey+6, 30, 22, 200, 340, $eyeC);
                imagesetthickness($img, 1);
                break;
            case 2: // narrow angry
                $this->ellipse($img, $lx, $ey+2, 14, 10, $eyeD);
                $this->ellipse($img, $rx, $ey+2, 11, 8,  $eyeD);
                imageline($img, $lx-18, $ey-12, $lx+4, $ey-6, $eyeC);
                imageline($img, $rx+14, $ey-12, $rx-4, $ey-6, $eyeC);
                break;
            case 3: // sad drooping
                $this->ellipse($img, $lx, $ey+2, 14, 16, $eyeD);
                $this->ellipse($img, $rx, $ey+2, 11, 12, $eyeD);
                // tear
                $this->ellipse($img, $lx-8, $ey+22, 8, 14, $this->color($img,120,160,255));
                break;
            case 4: // wide surprised
                $this->ellipse($img, $lx, $ey+2, 18, 22, $eyeD);
                $this->ellipse($img, $rx, $ey+2, 14, 17, $eyeD);
                break;
        }

        // Shine dots
        $this->ellipse($img, $lx-8, $ey-8, 10, 10, $white);
        $this->ellipse($img, $lx-6, $ey-6, 5,  5,  $white);
        $this->ellipse($img, $rx-6, $ey-7, 8,  8,  $white);

        // Lashes on left eye
        $lashColor = $eyeD;
        $lashes = [[$lx-16,  $ey-19, $lx-20, $ey-26],
                   [$lx-6,   $ey-21, $lx-8,  $ey-29],
                   [$lx+4,   $ey-21, $lx+6,  $ey-29],
                   [$lx+13,  $ey-18, $lx+17, $ey-25]];
        imagesetthickness($img, 2);
        foreach ($lashes as [$x1,$y1,$x2,$y2]) imageline($img,$x1,$y1,$x2,$y2,$lashColor);
        imagesetthickness($img, 1);

        // Blush
        $this->ellipse($img, 44, 138, 30, 14, $blush);
        $this->ellipse($img, 156, 138, 30, 14, $blush);

        // Nostrils
        $this->ellipse($img, 84, 148, 12, 8, $nose);
        $this->ellipse($img, 116, 148, 12, 8, $nose);

        // Mouth
        switch ($expr) {
            case 1: imagesetthickness($img,4); imagearc($img,100,158,70,36,10,170,$eyeC); imagesetthickness($img,1); break;
            case 2: imagesetthickness($img,3); imagearc($img,100,175,60,28,200,340,$eyeC); imagesetthickness($img,1); break;
            case 3: imagesetthickness($img,3); imagearc($img,100,170,60,28,20,160,$eyeC); imagesetthickness($img,1); break;
            default: imagesetthickness($img,3); imagearc($img,100,160,60,28,10,170,$eyeC); imagesetthickness($img,1);
        }

        // Detail variant extras
        if ($det === 1) { // battle scar
            imageline($img, 96,108, 108,140, $this->color($img,max(0,$bg[0]-40),max(0,$bg[1]-40),max(0,$bg[2]-40)));
            imagesetthickness($img, 2);
            imageline($img, 96,108, 108,140, $nose);
            imagesetthickness($img, 1);
        }
        if ($det === 2) { // elder — silver mane overlay
            $silver = $this->color($img, 200, 195, 210);
            foreach ([54,80,110,136] as $i => $mx) {
                $this->ellipse($img, $mx, 8, 14, 26, $silver);
            }
        }
    }

    // ── DRAGON ──────────────────────────────────────────────────────
    private function drawDragon(\GdImage $img, string $u, int $p, int $expr, int $det): void
    {
        $palettes = [
            [[26,58,16],  [34,90,20],   [170,204,0],  [68,102,0]],   // forest green
            [[58,8,8],    [120,16,16],  [255,170,0],  [90,20,0]],    // fire red
            [[10,26,90],  [20,50,140],  [100,160,255],[30,70,180]],  // ocean blue
            [[42,42,42],  [80,80,80],   [180,180,180],[50,50,50]],   // stone grey
        ];
        [$bg, $face, $eyeCol, $dark] = $palettes[$p];

        $bgC   = $this->color($img, ...$bg);
        $faceC = $this->color($img, ...$face);
        $eyeC  = $this->color($img, ...$eyeCol);
        $darkC = $this->color($img, ...$dark);
        $scaleC= $this->color($img, max(0,$bg[0]-10), max(0,$bg[1]-10), max(0,$bg[2]-10));
        $white = $this->color($img, 245, 245, 230);
        $snout = $this->color($img, max(0,$face[0]-8), max(0,$face[1]-8), max(0,$face[2]-8));

        $this->rect($img, 0, 0, 200, 200, $bgC);

        // Scale pattern on background
        for ($row = 0; $row < 4; $row++) {
            for ($col = 0; $col < 5; $col++) {
                $sx = 20 + $col * 36 + ($row % 2) * 18;
                $sy = 20 + $row * 20;
                $this->ellipse($img, $sx, $sy, 26, 16, $scaleC);
            }
        }

        // Horns — vary by detail
        if ($det === 2) { // elder — large dramatic horns
            $this->polygon($img, [72,72, 58,20, 82,62], $darkC);
            $this->polygon($img, [72,72, 62,22, 74,60], $this->color($img,min(255,$dark[0]+20),min(255,$dark[1]+20),min(255,$dark[2]+20)));
            $this->polygon($img, [128,72, 142,20, 118,62], $darkC);
            $this->polygon($img, [128,72, 138,22, 126,60], $this->color($img,min(255,$dark[0]+20),min(255,$dark[1]+20),min(255,$dark[2]+20)));
        } else {
            $this->polygon($img, [72,72, 60,30, 82,66], $darkC);
            $this->polygon($img, [128,72, 140,30, 118,66], $darkC);
        }

        // Face
        $this->ellipse($img, 100, 120, 150, 130, $faceC);

        // Scale rows on face
        foreach ([[70,100],[100,94],[130,100]] as [$sx,$sy]) {
            $this->ellipse($img, $sx, $sy, 22, 12, $scaleC);
        }

        // Brow ridges
        $this->ellipse($img, 72, 100, 44, 16, $darkC);
        $this->ellipse($img, 128, 100, 44, 16, $darkC);

        // Expression brow angle
        if ($expr === 2) { // angry
            $this->polygon($img, [50,96, 72,104, 72,96], $darkC);
            $this->polygon($img, [150,96, 128,104, 128,96], $darkC);
        }

        // Eyes — slit reptile
        $lx = 72; $rx = 128; $ey = 112;
        $this->ellipse($img, $lx, $ey, 32, 26, $eyeC);
        $this->ellipse($img, $rx, $ey, 32, 26, $eyeC);

        switch ($expr) {
            case 0: case 2: // neutral/angry — vertical slit
                $this->ellipse($img, $lx, $ey, 9, 22, $darkC);
                $this->ellipse($img, $rx, $ey, 9, 22, $darkC);
                break;
            case 1: // happy — wider slit
                $this->ellipse($img, $lx, $ey, 9, 16, $darkC);
                $this->ellipse($img, $rx, $ey, 9, 16, $darkC);
                break;
            case 3: // sad — drooping slit
                $this->ellipse($img, $lx, $ey+2, 9, 20, $darkC);
                $this->ellipse($img, $rx, $ey+2, 9, 20, $darkC);
                break;
            case 4: // surprised — wide round
                $this->ellipse($img, $lx, $ey, 14, 22, $darkC);
                $this->ellipse($img, $rx, $ey, 14, 22, $darkC);
                break;
        }
        $this->ellipse($img, $lx-7, $ey-5, 8, 8, $white);
        $this->ellipse($img, $rx-7, $ey-5, 8, 8, $white);

        // Snout
        $this->ellipse($img, 100, 144, 50, 30, $snout);
        $this->ellipse($img, 86,  140, 14, 10, $darkC);
        $this->ellipse($img, 114, 140, 14, 10, $darkC);

        // Mouth / teeth
        $toothC = $this->color($img, 240, 235, 220);
        switch ($expr) {
            case 1: // happy — closed smile
                imagesetthickness($img,3); imagearc($img,100,154,60,24,10,170,$darkC); imagesetthickness($img,1);
                break;
            case 2: // angry snarl — many teeth
                $this->polygon($img, [66,158, 134,158, 134,174, 66,174], $darkC);
                foreach ([70,82,94,106,118,130] as $tx) {
                    $this->polygon($img, [$tx,158, $tx+4,170, $tx+8,158], $toothC);
                }
                break;
            default: // neutral — two fangs
                $this->polygon($img, [78,158, 74,170, 86,158], $toothC);
                $this->polygon($img, [114,158, 118,170, 126,158], $toothC);
        }

        // Detail extras
        if ($det === 1) { // battle scar
            imagesetthickness($img, 2);
            imageline($img, 88,100, 96,124, $darkC);
            imagesetthickness($img, 1);
        }
        if ($det === 3) { // noble — gold accent on horns
            $gold = $this->color($img, 255,200,50);
            $this->ellipse($img, 68, 48, 14, 8, $gold);
            $this->ellipse($img, 132, 48, 14, 8, $gold);
        }
    }

    // ── TROLL ───────────────────────────────────────────────────────
    private function drawTroll(\GdImage $img, string $u, int $p, int $expr, int $det): void
    {
        $palettes = [
            [[74,90,32],  [85,102,40],  [136,170,0],  [50,70,10]],  // swamp green
            [[90,90,90],  [110,110,110],[170,170,170], [60,60,60]],  // stone grey
            [[58,44,16],  [80,60,20],   [140,110,40],  [40,30,10]],  // mud brown
            [[44,70,44],  [60,90,60],   [120,180,120], [30,50,30]],  // forest green
        ];
        [$bg, $face, $toothC, $dark] = $palettes[$p];

        $bgC   = $this->color($img, ...$bg);
        $faceC = $this->color($img, ...$face);
        $tooth = $this->color($img, ...$toothC);
        $darkC = $this->color($img, ...$dark);
        $wart  = $this->color($img, max(0,$face[0]-14), max(0,$face[1]-14), max(0,$face[2]-14));
        $eye1  = $this->color($img, 140,120,0);
        $eye2  = $this->color($img, 20,18,0);

        $this->rect($img, 0, 0, 200, 200, $bgC);

        // Warts/bumps on background
        foreach ([[24,40],[38,32],[76,28],[144,32],[168,38],[158,44],[26,56]] as [$wx,$wy]) {
            $this->ellipse($img, $wx, $wy, 12, 10, $wart);
        }

        // Wide face
        $this->ellipse($img, 100, 120, 170, 140, $faceC);

        // Warts on face
        if ($det !== 3) { // young has fewer warts
            foreach ([[36,104],[62,96],[144,100],[166,108],[44,134],[156,130]] as [$wx,$wy]) {
                $wr = $this->hash($u, 20+(int)$wx, 6, 12);
                $this->ellipse($img, $wx, $wy, $wr, (int)($wr*0.8), $wart);
            }
        }

        // Moss overlay for detail=2 (elder)
        if ($det === 2) {
            $moss = $this->color($img, 40,80,10);
            $this->ellipse($img, 34,  108, 24, 16, $moss);
            $this->ellipse($img, 168, 102, 20, 14, $moss);
            $this->ellipse($img, 100,  92, 18, 12, $moss);
        }

        // Brow ridges
        $this->rect($img, 30, 96, 90, 110, $darkC);
        $this->rect($img, 110, 96, 170, 110, $darkC);
        if ($expr === 2) { // angry — angled brows
            $this->polygon($img, [30,96, 90,100, 90,96], $darkC);
            $this->polygon($img, [170,96, 110,100, 110,96], $darkC);
        }

        // Small mean eyes
        $lx = 60; $rx = 140; $ey = 118;
        $this->ellipse($img, $lx, $ey, 28, 22, $darkC);
        $this->ellipse($img, $rx, $ey, 28, 22, $darkC);

        if ($expr === 1) { $eyeH = 8; }
        elseif ($expr === 2) { $eyeH = 7; }
        elseif ($expr === 3) { $eyeH = 8; }
        elseif ($expr === 4) { $eyeH = 16; }
        else { $eyeH = 12; }
        $this->ellipse($img, $lx, $ey, 20, $eyeH, $eye1);
        $this->ellipse($img, $rx, $ey, 20, $eyeH, $eye1);
        $this->ellipse($img, $lx, $ey, 10, (int)($eyeH*0.7), $eye2);
        $this->ellipse($img, $rx, $ey, 10, (int)($eyeH*0.7), $eye2);

        if ($expr === 3) { // sad — one tear
            $this->ellipse($img, $lx-10, $ey+18, 8, 14, $this->color($img,100,140,200));
        }

        // Big bulbous nose
        $this->ellipse($img, 100, 140, 44, 30, $wart);
        $this->ellipse($img, 86,  138, 16, 12, $darkC);
        $this->ellipse($img, 114, 138, 16, 12, $darkC);

        // Wide jagged mouth with teeth
        $mouthY = $det === 3 ? 156 : 154;
        $this->polygon($img, [36,$mouthY, 164,$mouthY, 164,$mouthY+22, 36,$mouthY+22], $darkC);

        $teethCount = $det === 3 ? 4 : 6; // young has fewer teeth
        $spacing    = (int)(128 / ($teethCount + 1));
        for ($i = 0; $i < $teethCount; $i++) {
            $tx = 36 + $spacing + $i * $spacing;
            $this->polygon($img, [$tx-6,$mouthY, $tx,$mouthY+14, $tx+6,$mouthY], $tooth);
        }

        switch ($expr) {
            case 1: // happy — upward curve
                imagesetthickness($img,3); imagearc($img,100,$mouthY+28,100,34,190,350,$faceC); imagesetthickness($img,1);
                break;
            case 2: // angry — downward line at top of mouth
                $this->rect($img, 36,$mouthY, 164,$mouthY+4, $darkC);
                break;
        }

        if ($det === 1) { // battle scar
            imagesetthickness($img,3);
            imageline($img, 80,100, 90,130, $darkC);
            imagesetthickness($img,1);
        }
    }

    // ── MINOTAUR ────────────────────────────────────────────────────
    private function drawMinotaur(\GdImage $img, string $u, int $p, int $expr, int $det): void
    {
        $palettes = [
            [[58,40,16],  [90,60,26],   [136,88,0],   [40,26,8]],   // dark brown
            [[10,8,8],    [30,20,20],   [204,34,0],   [16,8,8]],    // black war
            [[58,28,28],  [90,44,44],   [170,68,68],  [40,18,18]],  // rust red
            [[74,74,42],  [100,100,60], [170,170,100],[50,50,28]],  // tawny gold
        ];
        [$bg, $face, $eyeCol, $dark] = $palettes[$p];

        $bgC   = $this->color($img, ...$bg);
        $faceC = $this->color($img, ...$face);
        $eyeC  = $this->color($img, ...$eyeCol);
        $darkC = $this->color($img, ...$dark);
        $snout = $this->color($img, max(0,$face[0]-10), max(0,$face[1]-8), max(0,$face[2]-6));
        $ring  = $det === 3 ? $this->color($img,255,200,50) : $this->color($img,180,140,30);
        $white = $this->color($img, 255,255,255);

        $this->rect($img, 0, 0, 200, 200, $bgC);

        // Fur texture hints
        $fur = $this->color($img, min(255,$bg[0]+14), min(255,$bg[1]+12), min(255,$bg[2]+10));
        foreach ([[14,60],[22,40],[36,28],[164,28],[178,40],[184,60]] as [$fx,$fy]) {
            $this->ellipse($img, $fx, $fy, 18, 28, $fur);
        }

        // Curved bull horns
        imagesetthickness($img, 10);
        imagearc($img, 58,  80, 80, 100, 230, 310, $darkC);
        imagearc($img, 142, 80, 80, 100, 230, 310, $darkC);
        imagesetthickness($img, 1);

        // Horn rings (noble detail)
        if ($det === 3) {
            $this->ellipse($img, 38, 48, 14, 8, $ring);
            $this->ellipse($img, 162,48, 14, 8, $ring);
        }

        // Wide face
        $this->ellipse($img, 100, 120, 160, 140, $faceC);

        // Heavy brow ridges
        $this->ellipse($img, 72,  102, 50, 18, $darkC);
        $this->ellipse($img, 128, 102, 50, 18, $darkC);

        if ($expr === 2) { // angry brow angle
            $this->polygon($img, [46,98, 72,106, 72,98], $darkC);
            $this->polygon($img, [154,98, 128,106, 128,98], $darkC);
        }

        // Deep-set eyes
        $lx = 72; $rx = 128; $ey = 114;
        $this->ellipse($img, $lx, $ey, 32, 28, $darkC);
        $this->ellipse($img, $rx, $ey, 32, 28, $darkC);

        if ($det === 2) { // blind elder — milky eyes
            $milky = $this->color($img, 200,190,180);
            $this->ellipse($img, $lx, $ey, 22, 20, $milky);
            $this->ellipse($img, $rx, $ey, 22, 20, $milky);
        } else {
            $this->ellipse($img, $lx, $ey, 22, 20, $eyeC);
            $this->ellipse($img, $rx, $ey, 22, 20, $eyeC);
        if ($expr === 4) { $eyePupil = 12; }
        elseif ($expr === 1) { $eyePupil = 10; }
        else { $eyePupil = 8; }
            $this->ellipse($img, $lx, $ey, $eyePupil, $eyePupil, $darkC);
            $this->ellipse($img, $rx, $ey, $eyePupil, $eyePupil, $darkC);
            $this->ellipse($img, $lx-6, $ey-4, 7, 7, $white);
            $this->ellipse($img, $rx-6, $ey-4, 7, 7, $white);
        }

        // Drooping lids for tired (expr=3)
        if ($expr === 3) {
            $this->rect($img, $lx-16, $ey-14, $lx+16, $ey-2, $faceC);
            $this->rect($img, $rx-16, $ey-14, $rx+16, $ey-2, $faceC);
        }

        // Bull snout
        $this->ellipse($img, 100, 144, 56, 34, $snout);
        $this->ellipse($img, 84,  140, 18, 14, $darkC);
        $this->ellipse($img, 116, 140, 18, 14, $darkC);

        // Nose ring
        imagesetthickness($img, 4);
        imagearc($img, 100, 150, 40, 20, 10, 170, $ring);
        imagesetthickness($img, 1);

        // Mouth
        switch ($expr) {
            case 1: imagesetthickness($img,4); imagearc($img,100,164,70,28,10,170,$darkC); imagesetthickness($img,1); break;
            case 2: imagesetthickness($img,3); imagearc($img,100,175,70,28,200,340,$eyeC); imagesetthickness($img,1); break;
            default: imagesetthickness($img,3); imagearc($img,100,168,60,18,10,170,$darkC); imagesetthickness($img,1);
        }

        if ($det === 1) { // battle scar
            imagesetthickness($img,2);
            imageline($img, 90,96, 100,128, $darkC);
            imagesetthickness($img,1);
        }
    }

    // ── CYCLOPS ─────────────────────────────────────────────────────
    private function drawCyclops(\GdImage $img, string $u, int $p, int $expr, int $det): void
    {
        $palettes = [
            [[90,58,42],  [106,74,58],  [68,170,255], [60,30,14]],  // rocky tan/blue eye
            [[58,42,90],  [80,60,110],  [255,136,0],  [38,26,60]],  // purple/orange eye
            [[42,58,42],  [58,78,58],   [80,220,80],  [28,40,28]],  // mossy/green eye
            [[70,50,30],  [90,68,46],   [255,60,60],  [48,32,16]],  // brown/red eye
        ];
        [$bg, $face, $eyeIris, $dark] = $palettes[$p];

        $bgC   = $this->color($img, ...$bg);
        $faceC = $this->color($img, ...$face);
        $irisC = $this->color($img, ...$eyeIris);
        $darkC = $this->color($img, ...$dark);
        $pupil = $this->color($img, 8, 8, 18);
        $white = $this->color($img, 255, 255, 255);
        $milky = $this->color($img, 220, 210, 200);

        $this->rect($img, 0, 0, 200, 200, $bgC);

        // Rocky skin texture
        $rock = $this->color($img, max(0,$bg[0]-12), max(0,$bg[1]-12), max(0,$bg[2]-12));
        foreach ([[18,40],[36,28],[164,30],[174,46],[100,24],[60,50],[148,52]] as [$rx2,$ry]) {
            $this->ellipse($img, $rx2, $ry, 16, 12, $rock);
        }

        // Wide face
        $this->ellipse($img, 100, 118, 170, 150, $faceC);

        // Single thick brow
        $browY = $expr === 4 ? 70 : ($expr === 2 ? 78 : 74);
        $this->rect($img, 30, $browY, 170, $browY+18, $darkC);
        if ($expr === 2) { // angry — V shape brow
            $this->polygon($img, [30,$browY, 100,$browY+8, 170,$browY, 170,$browY+18, 100,$browY+26, 30,$browY+18], $darkC);
        }
        // Battle scar on brow
        if ($det === 1) {
            imagesetthickness($img,2);
            imageline($img, 70,$browY-4, 80,$browY+22, $darkC);
            imageline($img, 130,$browY-4, 120,$browY+22, $darkC);
            imagesetthickness($img,1);
        }

        // THE SINGLE GIANT EYE — centered
        $eyeW = 72; $eyeH = 64;
        $ey = 110;
        if ($expr === 4) { $eyeH = 72; } // surprised — taller
        if ($expr === 1) { $eyeH = 48; } // happy — squinter

        $this->ellipse($img, 100, $ey, $eyeW, $eyeH, $white);

        if ($det === 2) { // elder — milky/clouded
            $this->ellipse($img, 100, $ey, (int)($eyeW-8), (int)($eyeH-8), $milky);
            $this->ellipse($img, 100, $ey, (int)($eyeW-20), (int)($eyeH-18), $this->color($img,200,195,190));
        } else {
            $this->ellipse($img, 100, $ey, (int)($eyeW-8), (int)($eyeH-8), $irisC);
            $irisD = $this->color($img, (int)($eyeIris[0]*0.5), (int)($eyeIris[1]*0.5), (int)($eyeIris[2]*0.5));
            $this->ellipse($img, 100, $ey, (int)($eyeW-24), (int)($eyeH-22), $irisD);
            $pupilW = $expr === 4 ? 20 : ($expr === 1 ? 14 : 18);
            $this->ellipse($img, 100, $ey, $pupilW, $pupilW+4, $pupil);
            $this->ellipse($img, 84, $ey-10, 14, 14, $white);
            $this->ellipse($img, 80, $ey-12, 7,  7,  $white);
        }

        // Winking — cover top half of eye
        if ($expr === 1) {
            $this->rect($img, 28, $ey-32, 172, $ey-4, $faceC);
            imagesetthickness($img,3); imagearc($img,100,$ey,$eyeW+6,22,190,350,$darkC); imagesetthickness($img,1);
        }

        // Nose — two bumps
        $this->ellipse($img, 88,  148, 16, 12, $faceC);
        $this->ellipse($img, 112, 148, 16, 12, $faceC);
        $this->ellipse($img, 88,  146, 10, 8,  $darkC);
        $this->ellipse($img, 112, 146, 10, 8,  $darkC);

        // Mouth
        switch ($expr) {
            case 1: imagesetthickness($img,4); imagearc($img,100,168,100,36,10,170,$darkC); imagesetthickness($img,1); break;
            case 2: imagesetthickness($img,4); imagearc($img,100,178,90,32,200,340,$irisC); imagesetthickness($img,1); break;
            case 3: imagesetthickness($img,3); imagearc($img,100,172,80,28,20,160,$darkC); imagesetthickness($img,1); break;
            case 4: $this->ellipse($img,100,170,50,34,$darkC); $this->ellipse($img,100,170,36,24,$pupil); break;
            default: imagesetthickness($img,3); imagearc($img,100,170,90,28,10,170,$darkC); imagesetthickness($img,1);
        }
    }

    // ── GOBLIN ──────────────────────────────────────────────────────
    private function drawGoblin(\GdImage $img, string $u, int $p, int $expr, int $det): void
    {
        $palettes = [
            [[42,74,16],  [58,102,24],  [255,238,0],  [28,50,8]],   // lime green
            [[16,52,16],  [24,80,24],   [170,204,0],  [10,34,10]],  // deep forest
            [[58,74,16],  [80,102,24],  [255,200,80], [36,50,8]],   // yellow-green
            [[26,44,26],  [40,68,40],   [100,220,100],[14,28,14]],  // bright green
        ];
        [$bg, $face, $eyeYellow, $dark] = $palettes[$p];

        $bgC   = $this->color($img, ...$bg);
        $faceC = $this->color($img, ...$face);
        $eyeY  = $this->color($img, ...$eyeYellow);
        $darkC = $this->color($img, ...$dark);
        $eyeOrg= $this->color($img, (int)($eyeYellow[0]*0.7), (int)($eyeYellow[1]*0.6), 0);
        $pupil = $this->color($img, 14, 12, 0);
        $tooth = $this->color($img, 220, 218, 180);
        $white = $this->color($img, 255, 255, 255);

        $this->rect($img, 0, 0, 200, 200, $bgC);

        // Huge ears sticking out from sides
        $earColor = $faceC;
        $earInner = $this->color($img, max(0,$face[0]-10), max(0,$face[1]-10), max(0,$face[2]-10));
        $this->ellipse($img, 16, 108, 40, 56, $earColor);
        $this->ellipse($img, 184, 108, 40, 56, $earColor);
        $this->ellipse($img, 16, 108, 24, 38, $earInner);
        $this->ellipse($img, 184, 108, 24, 38, $earInner);

        // Narrow pointed face
        $this->ellipse($img, 100, 118, 130, 140, $faceC);

        // Big bugging eyes
        $lx = 76; $rx = 124; $ey = 104;
        $eyeW = $det === 3 ? 30 : 34; // young = slightly smaller
        $eyeH = $expr === 4 ? 40 : ($expr === 3 ? 28 : 36);

        $this->ellipse($img, $lx, $ey, $eyeW, $eyeH, $eyeY);
        $this->ellipse($img, $rx, $ey, $eyeW, $eyeH, $eyeY);
        $this->ellipse($img, $lx, $ey+1, (int)($eyeW-8), (int)($eyeH-6), $eyeOrg);
        $this->ellipse($img, $rx, $ey+1, (int)($eyeW-8), (int)($eyeH-6), $eyeOrg);

        if ($expr === 2) { $pupilH = (int)($eyeH*0.4); }
        elseif ($expr === 1) { $pupilH = (int)($eyeH*0.35); }
        else { $pupilH = (int)($eyeH*0.45); }
        $this->ellipse($img, $lx, $ey+2, 12, $pupilH, $pupil);
        $this->ellipse($img, $rx, $ey+2, 12, $pupilH, $pupil);
        $this->ellipse($img, $lx-8, $ey-8, 8, 8, $white);
        $this->ellipse($img, $rx-8, $ey-8, 8, 8, $white);

        // Squint lids for sneaky (expr=2)
        if ($expr === 2) {
            $this->rect($img, $lx-17, $ey-18, $lx+17, $ey-6, $bgC);
            $this->rect($img, $rx-17, $ey-18, $rx+17, $ey-6, $bgC);
        }
        // Scared — whites showing at bottom
        if ($expr === 4) {
            $this->ellipse($img, $lx, $ey+14, (int)($eyeW-10), 10, $this->color($img,255,255,200));
            $this->ellipse($img, $rx, $ey+14, (int)($eyeW-10), 10, $this->color($img,255,255,200));
        }
        // Tear for sad
        if ($expr === 3) {
            $this->ellipse($img, $lx-6, $ey+24, 8, 14, $this->color($img,100,150,220));
        }

        // Big pointy nose
        $this->polygon($img, [100,122, 88,148, 112,148], $darkC);
        $this->ellipse($img, 100, 146, 22, 14, $darkC);

        // Wide grinning mouth
        $toothCount = $det === 3 ? 3 : 5; // young = fewer teeth
        $mouthY = 158;
        $this->polygon($img, [48,$mouthY, 152,$mouthY, 152,$mouthY+24, 48,$mouthY+24], $darkC);

        switch ($expr) {
            case 1: // happy — bigger grin arc
                imagesetthickness($img,2); imagearc($img,100,$mouthY+28,96,44,195,345,$faceC); imagesetthickness($img,1);
                break;
            case 3: // sad — downward
                $this->polygon($img, [48,$mouthY, 152,$mouthY, 152,$mouthY+24, 48,$mouthY+24], $bgC);
                imagesetthickness($img,3); imagearc($img,100,$mouthY-4,96,36,20,160,$darkC); imagesetthickness($img,1);
                break;
            case 4: // scared O
                $this->polygon($img, [48,$mouthY, 152,$mouthY, 152,$mouthY+24, 48,$mouthY+24], $bgC);
                $this->ellipse($img, 100, $mouthY+10, 40, 30, $darkC);
                $this->ellipse($img, 100, $mouthY+10, 28, 20, $pupil);
                break;
        }

        if ($expr !== 3 && $expr !== 4) {
            $spacing = (int)(80 / ($toothCount + 1));
            for ($i = 0; $i < $toothCount; $i++) {
                $tx = 56 + $spacing + $i * $spacing;
                $this->polygon($img, [$tx-5,$mouthY, $tx,$mouthY+13, $tx+5,$mouthY], $tooth);
            }
        }

        if ($det === 1) { // battle scar on face
            imagesetthickness($img,2);
            imageline($img, 86,92, 96,120, $darkC);
            imagesetthickness($img,1);
        }
    }

    // ── TREANT ──────────────────────────────────────────────────────
    private function drawTreant(\GdImage $img, string $u, int $p, int $expr, int $det): void
    {
        $palettes = [
            [[58,40,16],  [90,58,26],   [136,170,0],  [38,24,8]],   // oak / spring
            [[42,52,24],  [62,78,34],   [170,220,68], [28,36,12]],  // moss green
            [[44,26,10],  [68,44,18],   [204,68,0],   [28,16,6]],   // autumn
            [[42,52,68],  [58,72,90],   [136,196,255],[28,38,50]],  // winter ice
        ];
        [$bg, $bark, $leafCol, $dark] = $palettes[$p];

        $bgC   = $this->color($img, ...$bg);
        $barkC = $this->color($img, ...$bark);
        $leafC = $this->color($img, ...$leafCol);
        $darkC = $this->color($img, ...$dark);
        $leaf2 = $this->color($img, min(255,$leafCol[0]+30), min(255,$leafCol[1]+20), min(255,$leafCol[2]-10));
        $knotC = $this->color($img, max(0,$bark[0]-14), max(0,$bark[1]-14), max(0,$bark[2]-14));
        $eyeGlow= $this->color($img, min(255,$leafCol[0]+20), min(255,$leafCol[1]+30), max(0,$leafCol[2]-20));
        $mossC = $this->color($img, 50,80,16);

        $this->rect($img, 0, 0, 200, 200, $bgC);

        // Bark texture lines on background
        $barkLine = $darkC;
        foreach ([18,34,54,76,100,124,148,168,184] as $bx) {
            $wobble = $this->hash($u, 30+(int)$bx, -6, 6);
            imageline($img, $bx+$wobble, 0, $bx-$wobble, 200, $barkLine);
        }

        // Foliage on top — varies by palette/detail
        if ($det === 2) { // elder/autumn — larger spread
            $leafPositions = [30,52,72,100,128,150,170];
        } else {
            $leafPositions = [40,58,76,100,124,144,162];
        }
        foreach ($leafPositions as $i => $lx) {
            $lh = $this->hash($u, 40+$i, 16, 34);
            $col = ($i % 2 === 0) ? $leafC : $leaf2;
            $this->ellipse($img, $lx, (int)(8+$lh/4), 22, $lh*2, $col);
        }
        // Flower/blossom centers for spring (p=0)
        if ($p === 0 || $det === 3) {
            foreach ([58,100,142] as $fx) {
                $this->ellipse($img, $fx, 18, 10, 10, $this->color($img,255,220,80));
            }
        }
        // Icicle tips for winter (p=3)
        if ($p === 3 || $det === 1) {
            $iceC = $this->color($img, 180,220,255);
            foreach ([44,68,92,116,140,162] as $ix) {
                $ih = $this->hash($u, 50+(int)$ix, 8, 20);
                $this->polygon($img, [$ix-5, 40, $ix, 40+$ih, $ix+5, 40], $iceC);
                $this->ellipse($img, $ix, 40+$ih, 8, 5, $this->color($img,220,240,255));
            }
        }

        // Woody face
        $this->ellipse($img, 100, 128, 155, 140, $barkC);

        // Bark lines on face
        foreach ([68,90,110,132] as $bx) {
            $wobble = $this->hash($u, 60+(int)$bx, -4, 4);
            imageline($img, $bx+$wobble, 80, $bx-$wobble, 175, $knotC);
        }

        // Mossy brow ridges
        $this->ellipse($img, 72,  102, 44, 16, $mossC);
        $this->ellipse($img, 128, 102, 44, 16, $mossC);

        if ($expr === 2) { // angry mossy brows angle
            $this->polygon($img, [50,98, 72,106, 72,98], $mossC);
            $this->polygon($img, [150,98, 128,106, 128,98], $mossC);
        }
        if ($expr === 1) { // happy brows lift
            $this->ellipse($img, 72,  96, 44, 14, $mossC);
            $this->ellipse($img, 128, 96, 44, 14, $mossC);
        }

        // Knothole eyes
        $lx = 72; $rx = 128; $ey = 118;
        $this->ellipse($img, $lx, $ey, 34, 28, $darkC);
        $this->ellipse($img, $rx, $ey, 34, 28, $darkC);
        $this->ellipse($img, $lx, $ey, 24, 20, $knotC);
        $this->ellipse($img, $rx, $ey, 24, 20, $knotC);
        $this->ellipse($img, $lx, $ey, 14, 12, $eyeGlow);
        $this->ellipse($img, $rx, $ey, 14, 12, $eyeGlow);
        $this->ellipse($img, $lx-4, $ey-3, 6, 6, $this->color($img,min(255,$leafCol[0]+60),min(255,$leafCol[1]+60),min(255,$leafCol[2]+20)));
        $this->ellipse($img, $rx-4, $ey-3, 6, 6, $this->color($img,min(255,$leafCol[0]+60),min(255,$leafCol[1]+60),min(255,$leafCol[2]+20)));

        // Drooping lids for sad/elder
        if ($expr === 3 || $det === 2) {
            $this->rect($img, $lx-17, $ey-14, $lx+17, $ey-4, $barkC);
            $this->rect($img, $rx-17, $ey-14, $rx+17, $ey-4, $barkC);
        }
        if ($expr === 4) { // surprised — wider eyes
            $this->ellipse($img, $lx, $ey, 34, 34, $darkC);
            $this->ellipse($img, $rx, $ey, 34, 34, $darkC);
            $this->ellipse($img, $lx, $ey, 24, 24, $knotC);
            $this->ellipse($img, $rx, $ey, 24, 24, $knotC);
            $this->ellipse($img, $lx, $ey, 14, 14, $eyeGlow);
            $this->ellipse($img, $rx, $ey, 14, 14, $eyeGlow);
        }

        // Root/branch mouth
        $mouthY = 148;
        switch ($expr) {
            case 1: // happy branch smile
                imagesetthickness($img,4);
                imagearc($img, 72,  $mouthY+10, 24, 16, 200, 340, $darkC);
                imagearc($img, 100, $mouthY+6,  28, 14, 190, 350, $darkC);
                imagearc($img, 128, $mouthY+10, 24, 16, 200, 340, $darkC);
                imagesetthickness($img,1);
                break;
            case 2: // angry — straight slash
                imagesetthickness($img,4);
                imageline($img, 54, $mouthY+8, 146, $mouthY+8, $darkC);
                imagesetthickness($img,1);
                break;
            case 3: // sad drooping
                imagesetthickness($img,4);
                imagearc($img, 72,  $mouthY+6, 24, 16, 20, 160, $darkC);
                imagearc($img, 100, $mouthY,   28, 14, 10, 170, $darkC);
                imagearc($img, 128, $mouthY+6, 24, 16, 20, 160, $darkC);
                imagesetthickness($img,1);
                break;
            default:
                imagesetthickness($img,4);
                imageline($img, 56, $mouthY+8, 80,  $mouthY+4,  $darkC);
                imageline($img, 80, $mouthY+4, 120, $mouthY+4,  $darkC);
                imageline($img, 120,$mouthY+4, 144, $mouthY+8,  $darkC);
                imagesetthickness($img,1);
        }

        // Moss patch detail
        if ($det !== 3) {
            $this->ellipse($img, 46, 112, 20, 14, $mossC);
            $this->ellipse($img, 158, 108, 18, 12, $mossC);
        }
    }

    // ── FAIRY ───────────────────────────────────────────────────────
    private function drawFairy(\GdImage $img, string $u, int $p, int $expr, int $det): void
    {
        $palettes = [
            [[16,8,40],   [100,40,180], [255,100,220],[200,80,255]], // violet/pink
            [[8,24,40],   [20,120,180], [100,220,255],[60,180,255]], // sky blue
            [[8,40,16],   [30,140,60],  [100,255,140],[60,220,100]], // forest green
            [[40,24,8],   [160,80,20],  [255,180,80], [220,140,50]], // golden amber
        ];
        [$bg, $wing, $glow, $accent] = $palettes[$p];

        $bgC   = $this->color($img, ...$bg);
        $wingC = $this->color($img, ...$wing);
        $glowC = $this->color($img, ...$glow);
        $accC  = $this->color($img, ...$accent);
        $skin  = $this->color($img, min(255,$bg[0]+160), min(255,$bg[1]+140), min(255,$bg[2]+120));
        $eyeC  = $accC;
        $eyeD  = $this->color($img, (int)($accent[0]*0.4), (int)($accent[1]*0.4), (int)($accent[2]*0.4));
        $white = $this->color($img, 255,255,255);

        $this->rect($img, 0, 0, 200, 200, $bgC);

        // Wings framing the circle sides — gossamer
        $this->ellipse($img, 18,  90, 40, 80, $wingC);
        $this->ellipse($img, 182, 90, 40, 80, $wingC);
        $this->ellipse($img, 14,  110, 28, 50, $wingC);
        $this->ellipse($img, 186, 110, 28, 50, $wingC);
        // Wing glow outline
        $this->ellipse($img, 18,  90, 36, 76, $glowC);
        $this->ellipse($img, 182, 90, 36, 76, $glowC);
        $this->ellipse($img, 18,  90, 30, 68, $bgC);
        $this->ellipse($img, 182, 90, 30, 68, $bgC);
        // Wing veins
        imagesetthickness($img, 1);
        imageline($img, 18,54,  18,126, $glowC);
        imageline($img, 12,66,  24,66,  $glowC);
        imageline($img, 10,90,  26,90,  $glowC);
        imageline($img, 12,112, 24,112, $glowC);
        imageline($img, 182,54, 182,126,$glowC);
        imageline($img, 176,66, 188,66, $glowC);
        imageline($img, 174,90, 190,90, $glowC);
        imageline($img, 176,112,188,112,$glowC);

        // Pointed ears
        $this->polygon($img, [28,88, 14,68, 34,82], $skin);
        $this->polygon($img, [172,88, 186,68, 166,82], $skin);

        // Delicate face
        $this->ellipse($img, 100, 110, 120, 120, $skin);

        // Sparkle aura around head
        foreach ([0,45,90,135,180,225,270,315] as $a) {
            $rad = $a * M_PI / 180;
            $sx = (int)(100 + cos($rad) * 66);
            $sy = (int)(110 + sin($rad) * 66);
            $this->ellipse($img, $sx, $sy, 8, 8, $glowC);
            $this->ellipse($img, $sx, $sy, 4, 4, $white);
        }

        // Large expressive eyes
        $lx = 72; $rx = 128; $ey = 102;
        $this->ellipse($img, $lx, $ey, 36, 40, $white);
        $this->ellipse($img, $rx, $ey, 36, 40, $white);
        $this->ellipse($img, $lx, $ey+1, 26, 30, $eyeC);
        $this->ellipse($img, $rx, $ey+1, 26, 30, $eyeC);

        switch ($expr) {
            case 0: case 2:
                $this->ellipse($img, $lx, $ey+2, 13, 16, $eyeD);
                $this->ellipse($img, $rx, $ey+2, 13, 16, $eyeD);
                break;
            case 1: // happy arcs
                $this->ellipse($img, $lx, $ey, 36, 40, $bgC);
                $this->ellipse($img, $rx, $ey, 36, 40, $bgC);
                imagesetthickness($img,5);
                imagearc($img,$lx,$ey+10,34,26,200,340,$eyeC);
                imagearc($img,$rx,$ey+10,34,26,200,340,$eyeC);
                imagesetthickness($img,1);
                break;
            case 3: // sad
                $this->ellipse($img, $lx, $ey+2, 13, 16, $eyeD);
                $this->ellipse($img, $rx, $ey+2, 13, 16, $eyeD);
                $this->ellipse($img, $lx-6, $ey+24, 7, 12, $this->color($img,120,160,255));
                break;
            case 4: // wide
                $this->ellipse($img, $lx, $ey+2, 17, 22, $eyeD);
                $this->ellipse($img, $rx, $ey+2, 17, 22, $eyeD);
                break;
        }

        $this->ellipse($img, $lx-7, $ey-9, 9, 9, $white);
        $this->ellipse($img, $lx-5, $ey-7, 5, 5, $white);
        $this->ellipse($img, $rx-7, $ey-9, 9, 9, $white);

        // Lashes
        $lashC = $eyeD;
        imagesetthickness($img,2);
        foreach ([[$lx-16,$ey-22,$lx-20,$ey-29],[$lx-5,$ey-22,$lx-6,$ey-30],[$lx+6,$ey-21,$lx+8,$ey-29],[$lx+14,$ey-18,$lx+18,$ey-25]] as [$x1,$y1,$x2,$y2]) {
            imageline($img,$x1,$y1,$x2,$y2,$lashC);
        }
        imagesetthickness($img,1);

        // Tiny nose
        $this->ellipse($img, 94, 122, 8, 6, $this->color($img,max(0,$skin[0]-20),max(0,$skin[1]-18),max(0,$skin[2]-16)));
        $this->ellipse($img, 106,122, 8, 6, $this->color($img,max(0,$skin[0]-20),max(0,$skin[1]-18),max(0,$skin[2]-16)));

        // Mouth
        switch ($expr) {
            case 1: imagesetthickness($img,3); imagearc($img,100,138,52,28,10,170,$eyeC); imagesetthickness($img,1); break;
            case 2: imagesetthickness($img,2); imagearc($img,100,150,46,22,200,340,$eyeC); imagesetthickness($img,1); break;
            case 3: imagesetthickness($img,2); imagearc($img,100,144,46,20,20,160,$eyeC); imagesetthickness($img,1); break;
            case 4: $this->ellipse($img,100,140,24,18,$eyeD); $this->ellipse($img,100,140,16,12,$eyeD); break;
            default: imagesetthickness($img,2); imagearc($img,100,140,44,20,10,170,$eyeC); imagesetthickness($img,1);
        }

        // Detail
        if ($det === 1) { // battle — torn wing
            $this->polygon($img, [18,54, 10,76, 22,70, 14,90], $bgC);
        }
        if ($det === 3) { // noble — crown
            $gold = $this->color($img,255,210,60);
            foreach ([78,90,100,110,122] as $cx) {
                $this->polygon($img, [$cx-5,76, $cx,62, $cx+5,76], $gold);
            }
            $this->rect($img, 72,74, 128,80, $gold);
        }
    }
}
