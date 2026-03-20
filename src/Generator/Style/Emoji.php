<?php

namespace Resofire\Avatars\Generator\Style;

class Emoji extends AbstractStyle
{
    public function key(): string { return 'emoji'; }
    public function name(): string { return 'Emoji'; }

    public function generate(string $username): \GdImage
    {
        $img = $this->canvas();

        // Classic emoji yellows — tight palette so they all feel cohesive
        $bgColors = [
            [255, 204,   0], // classic yellow
            [255, 187,   0], // golden
            [255, 221,  34], // bright
            [238, 204,  17], // warm
            [255, 200,  50], // sunflower
            [220, 180,   0], // deep gold
        ];

        // Eye types — maximum structural difference
        $eyeTypes = [0, 1, 2, 3, 4, 5, 6, 7];
        // 0: classic dots
        // 1: heart eyes
        // 2: sunglasses
        // 3: X eyes
        // 4: star eyes
        // 5: wide open surprise
        // 6: wink (one closed)
        // 7: squint happy

        // Mouth types
        $mouthTypes = [0, 1, 2, 3, 4, 5];
        // 0: big smile
        // 1: big grin with teeth
        // 2: open laugh
        // 3: sad frown
        // 4: flat neutral
        // 5: tongue out

        [$br, $bg2, $bb] = $this->pick($username, 0, $bgColors);
        $eyeType          = $this->pick($username, 1, $eyeTypes);
        $mouthType        = $this->pick($username, 2, $mouthTypes);
        $hasBlush         = $this->hash($username, 3, 0, 1);
        $hasTear          = $this->hash($username, 4, 0, 1);
        $hasSweat         = $this->hash($username, 5, 0, 1);

        $bg     = $this->color($img, $br, $bg2, $bb);
        $dark   = $this->color($img, (int)($br * 0.6), (int)($bg2 * 0.6), (int)($bb * 0.6));
        $darker = $this->color($img, (int)($br * 0.4), (int)($bg2 * 0.4), (int)($bb * 0.4));
        $white  = $this->color($img, 255, 255, 255);
        $black  = $this->color($img, 20, 20, 20);
        $blush  = $this->color($img, 255, 100, 120);
        $red    = $this->color($img, 220, 50, 50);
        $blue   = $this->color($img, 100, 150, 255);
        $pink   = $this->color($img, 255, 100, 150);

        $this->rect($img, 0, 0, 200, 200, $bg);

        // Eyes
        switch ($eyeType) {
            case 0: // classic dots
                $this->ellipse($img, 70,  88, 34, 34, $darker);
                $this->ellipse($img, 130, 88, 34, 34, $darker);
                $this->ellipse($img, 70,  88, 20, 20, $black);
                $this->ellipse($img, 130, 88, 20, 20, $black);
                $this->ellipse($img, 63,  82, 8,  8,  $white);
                $this->ellipse($img, 123, 82, 8,  8,  $white);
                break;

            case 1: // heart eyes
                // left heart
                $this->ellipse($img, 58,  84, 30, 28, $white);
                $this->ellipse($img, 82,  84, 30, 28, $white);
                $this->ellipse($img, 58,  86, 28, 26, $red);
                $this->ellipse($img, 82,  86, 28, 26, $red);
                $this->polygon($img, [46,88, 94,88, 70,114], $red);
                $this->polygon($img, [46,88, 94,88, 70,112], $white);
                // right heart
                $this->ellipse($img, 118, 84, 30, 28, $white);
                $this->ellipse($img, 142, 84, 30, 28, $white);
                $this->ellipse($img, 118, 86, 28, 26, $red);
                $this->ellipse($img, 142, 86, 28, 26, $red);
                $this->polygon($img, [106,88, 154,88, 130,114], $red);
                $this->polygon($img, [106,88, 154,88, 130,112], $white);
                break;

            case 2: // sunglasses
                $this->rect($img, 38,  76, 92,  104, $black);
                $this->rect($img, 108, 76, 162, 104, $black);
                $this->rect($img, 92,  84, 108, 92,  $black);
                $this->rect($img, 30,  84, 38,  92,  $black);
                $this->rect($img, 162, 84, 170, 92,  $black);
                // lens sheen
                $this->ellipse($img, 55,  84, 28, 18, $dark);
                $this->ellipse($img, 115, 84, 28, 18, $dark);
                break;

            case 3: // X eyes
                $this->ellipse($img, 70,  88, 44, 44, $white);
                $this->ellipse($img, 130, 88, 44, 44, $white);
                imagesetthickness($img, 7);
                imageline($img, 50, 68, 90, 108, $darker);
                imageline($img, 90, 68, 50, 108, $darker);
                imageline($img, 110, 68, 150, 108, $darker);
                imageline($img, 150, 68, 110, 108, $darker);
                imagesetthickness($img, 1);
                break;

            case 4: // star eyes
                $this->ellipse($img, 70,  88, 50, 50, $white);
                $this->ellipse($img, 130, 88, 50, 50, $white);
                $this->polygon($img, [70,65, 74,80, 88,80, 77,90, 81,105, 70,95, 59,105, 63,90, 52,80, 66,80], $darker);
                $this->polygon($img, [130,65, 134,80, 148,80, 137,90, 141,105, 130,95, 119,105, 123,90, 112,80, 126,80], $darker);
                break;

            case 5: // wide open surprise
                $this->ellipse($img, 70,  88, 56, 60, $white);
                $this->ellipse($img, 130, 88, 56, 60, $white);
                $this->ellipse($img, 70,  89, 38, 42, $darker);
                $this->ellipse($img, 130, 89, 38, 42, $darker);
                $this->ellipse($img, 70,  90, 20, 22, $black);
                $this->ellipse($img, 130, 90, 20, 22, $black);
                $this->ellipse($img, 60,  80, 12, 12, $white);
                $this->ellipse($img, 120, 80, 12, 12, $white);
                break;

            case 6: // wink — right eye closed
                // left eye open
                $this->ellipse($img, 70,  88, 34, 34, $darker);
                $this->ellipse($img, 70,  88, 20, 20, $black);
                $this->ellipse($img, 63,  82, 8,  8,  $white);
                // right eye wink arc
                imagesetthickness($img, 6);
                imagearc($img, 130, 92, 46, 30, 200, 340, $darker);
                imagesetthickness($img, 1);
                break;

            case 7: // squint happy (^_^ style)
                imagesetthickness($img, 6);
                imagearc($img, 70,  94, 46, 32, 200, 340, $darker);
                imagearc($img, 130, 94, 46, 32, 200, 340, $darker);
                imagesetthickness($img, 1);
                break;
        }

        // Optional blush
        if ($hasBlush) {
            $this->ellipse($img, 38,  120, 42, 22, $blush);
            $this->ellipse($img, 162, 120, 42, 22, $blush);
        }

        // Optional tear (only on non-laugh expressions)
        if ($hasTear && $eyeType !== 7) {
            $this->ellipse($img, 155, 106, 14, 20, $blue);
            $this->ellipse($img, 155, 118, 10, 10, $blue);
        }

        // Optional sweat drop top right
        if ($hasSweat && !$hasTear) {
            $this->ellipse($img, 170, 50, 12, 18, $blue);
            $this->ellipse($img, 170, 60, 8,  8,  $blue);
        }

        // Mouth
        switch ($mouthType) {
            case 0: // big smile arc
                imagesetthickness($img, 7);
                imagearc($img, 100, 148, 110, 70, 15, 165, $darker);
                imagesetthickness($img, 1);
                break;

            case 1: // grin with teeth
                $this->ellipse($img, 100, 156, 110, 50, $darker);
                $this->rect($img, 46, 138, 154, 158, $darker);
                $this->rect($img, 48, 140, 152, 155, $white);
                // teeth dividers
                $this->rect($img, 70,  140, 73,  155, $darker);
                $this->rect($img, 92,  140, 95,  155, $darker);
                $this->rect($img, 114, 140, 117, 155, $darker);
                $this->rect($img, 136, 140, 139, 155, $darker);
                break;

            case 2: // open laugh O
                $this->ellipse($img, 100, 155, 80, 60, $darker);
                $this->ellipse($img, 100, 157, 62, 44, $black);
                break;

            case 3: // sad frown
                imagesetthickness($img, 7);
                imagearc($img, 100, 170, 100, 70, 195, 345, $darker);
                imagesetthickness($img, 1);
                break;

            case 4: // flat neutral
                $this->rect($img, 60, 148, 140, 156, $darker);
                break;

            case 5: // tongue out
                $this->ellipse($img, 100, 152, 90, 44, $darker);
                $this->rect($img, 56,  134, 144, 152, $darker);
                $this->rect($img, 58,  136, 142, 150, $white);
                // tongue
                $this->ellipse($img, 100, 164, 52, 34, $pink);
                $this->ellipse($img, 100, 167, 30, 16, $red);
                break;
        }

        return $img;
    }
}
