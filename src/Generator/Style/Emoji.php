<?php
// just checking key
namespace Resofire\Avatars\Generator\Style;
class Emoji extends AbstractStyle {
    public function key(): string { return 'emoji'; }
    public function name(): string { return 'Emoji'; }
    public function generate(string $username): \GdImage { return $this->canvas(); }
}
