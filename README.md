# RF Avatars

A [Flarum](https://flarum.org) extension that automatically generates unique, gamer-style avatars for users — entirely locally, with no external API calls.

Avatars are generated using PHP's GD library and saved to `assets/avatars` as standard PNG files. Every avatar is deterministic: the same username always produces the same avatar within a chosen style.

## Features

- **6 gamer-style avatar styles** — Pixel Gamer, Cyberpunk, Fantasy Warrior, Sci-Fi Android, Orc Warrior, Anime Chibi
- **Fully local** — no external API calls, no dependencies beyond PHP's built-in GD extension
- **User style picker** — users can select their preferred style from their settings page and see a live preview of all 6 options rendered from their actual username
- **Admin default** — admins choose a default style for new users via the admin panel
- **Automatic generation** — avatars are generated on registration and lazily for existing users on first load
- **Safe file management** — all generated files are prefixed with `rf_` to distinguish them from manually uploaded avatars; a flush button in the admin panel clears them all cleanly

## Requirements

- Flarum 1.x
- PHP with GD extension enabled (standard on virtually all hosts)

## Installation

```bash
composer require resofire/avatars
php flarum migrate
```

Then enable the extension in your admin panel.

## How It Works

When a new user registers, an avatar is generated in the admin's chosen default style and saved to `assets/avatars/rf_*.png`. The user's `avatar_url` column is updated to point to this file — it behaves identically to a manually uploaded avatar from Flarum's perspective.

If a user opens their settings page, they will see a style picker showing all 6 avatar styles rendered as live previews from their own username. Selecting a style and saving regenerates their avatar, deletes the old file, and updates their record — all in one request.

Existing users without an avatar get one generated lazily on their first page load.

## Admin Panel

The extension adds two settings to the admin panel:

- **Default Avatar Style** — the style applied to new users and used as the fallback
- **Flush Avatars** — deletes all `rf_`-prefixed avatar files from `assets/avatars` and clears the corresponding database records, allowing all avatars to be regenerated fresh

## Avatar Styles

| Style | Description |
|---|---|
| Pixel Gamer | 8-bit face with a gaming headset |
| Cyberpunk | Green-skinned face with a cyber eye implant |
| Fantasy Warrior | Elven face with pointed ears and a gemstone circlet |
| Sci-Fi Android | Robotic face with scan-line eyes and panel details |
| Orc Warrior | Green brutish face with tusks and wild spiked hair |
| Anime Chibi | Large expressive eyes with blush marks and sailor collar |

All visual features — skin tone, hair color, eye color, accessories, scars, expressions — are derived deterministically from the username hash, so every user gets a unique face within their chosen style.

## License

MIT — see [LICENSE](LICENSE) for details.
