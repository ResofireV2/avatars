# Cosmos Theme

A deep-space dark theme for [Flarum](https://flarum.org/) with a built-in day/night toggle, ambient star fields, and a fully styled post interaction system.

---

## Features

### Palette & Typography
- Deep space dark palette (`#080c14` background, teal `#69c6b9` primary accent)
- Matching **day mode** (light) stylesheet that mirrors all structure — only colours flip
- **Font picker** in admin: choose from Outfit (default), Space Grotesk, DM Sans, Sora, Plus Jakarta Sans, Nunito, or System UI. Code blocks always use DM Mono regardless of font choice

### Day / Night Toggle
- Self-contained theme switcher — **no dependency on fof/nightmode**
- Three modes: **Auto** (follows OS dark/light preference), **Day**, **Night**
- Preference persisted per user account via Flarum's prefs API; falls back to a cookie for guests
- Admin can set the forum-wide default mode (Auto / Day / Night)
- Toggle button always visible in the header; also accessible from the session dropdown and user settings page

### Visual Effects
- Ambient radial glow bloom behind the hero and page
- CSS star field (box-shadow technique, no images) on hero and discussion page
- Star fields and glow effects can be **disabled globally** from admin settings (e.g. for accessibility or performance)
- Avatar ring glow that picks up the user's avatar colour
- Connector line between posts in the avatar column on desktop

### Hero / Welcome Area
- Cosmos-styled hero with star field layer and gradient wash
- Optional **image slider** replacing the hero — configurable entirely from admin:
  - Enable/disable globally
  - Disable on mobile (falls back to default hero)
  - Hide on tag/category pages
  - Desktop and mobile height (px)
  - Autoplay interval (0 = manual only)

### Discussion List
- Unread indicator via left accent band
- Styled discussion item controls (3-dot menu)
- Pinned discussion highlighting (compatible with flarum/sticky)
- Styled "Load more" button

### User Profile Hero

The profile hero is redesigned to match the rest of the Cosmos aesthetic:

- Background gradient blended from the user's avatar colour into the Cosmos dark, using the same technique as the discussion hero
- Animated star field tinted from the avatar colour (suppressed when the effects toggle is off)
- Ambient glow bloom from the avatar colour on the left, teal counter-bloom on the right
- Avatar ring glow using the avatar's dominant colour
- **Stat pills** displayed below the user info: posts, discussions, and likes received (likes shown only when `flarum/likes` is active)
- Both dark and light mode fully themed

### Post Stream
- Styled post headers, body, blockquotes, code blocks, tables, and images
- Event posts (renamed, tagged, etc.) in muted style
- Post number accent in secondary colour

### Post Controls (flarum/likes + fof/reactions)
Fully styled to match the Cosmos aesthetic:
- **Reply button** — dimmed at rest, teal on hover
- **Like button** — dimmed at rest, teal on hover and when active (already liked)
- **Reaction count pills** — rounded-rect pills with a subtle border; teal tint on hover; stronger teal border + fill for your own active reaction
- **React smiley button** — ghost button, teal on hover; inline SVG icon inherits `currentColor`
- **Reaction picker dropdown** — dark surface card (`#131b2e`) with deep shadow in night mode; white card with soft shadow in day mode
- **Emoji buttons inside picker** — teal-tinted background + scale pop on hover
- All styles applied in both dark (`forum.css`) and light (`forum-day.css`)

### Extension Compatibility
Tested and styled for:
- `flarum/tags` — tag pills styled as flat dark pills; white text overrides in hero
- `flarum/sticky` — pinned discussion highlight
- `flarum/likes` — like button with active state
- `fof/reactions` — reaction pills, picker dropdown, emoji buttons, react trigger
- `fof/user-directory` — 2-column card grid with star field, avatar glow, ambient colour bloom per card
- Blog/cards extensions — card grid layout with image wrapper, body, footer, author, controls

### Admin Settings
All settings are available in **Admin → Extensions → Cosmos Theme**:

| Setting | Description |
|---|---|
| Forum Font | Font applied globally (code blocks always use DM Mono) |
| Default Colour Theme | Forum-wide default: Auto, Day, or Night |
| Star Fields & Glow Effects | Enable/disable all star and glow visual effects |
| Always Show Post Controls | Show Like, Reply, and Reactions buttons permanently on every post (default: visible on hover only) |
| Enable Cosmos Slider | Replace the hero with an image slider |
| Disable Slider on Mobile | Show default hero on mobile instead of slider |
| Hide Slider on Tag Pages | Show default hero on tag/category pages |
| Slider Height — Desktop (px) | Height of the slider on desktop viewports |
| Slider Height — Mobile (px) | Height of the slider on mobile viewports |
| Autoplay Interval (seconds) | Slide autoplay speed; 0 disables autoplay |

---

## Requirements

- Flarum `^1.8`
- PHP 8.3+

## Installation

Install via Composer:

```bash
composer require resofire/cosmos-theme
```

Then enable the extension in your Flarum admin panel.

## Optional Extensions

The theme works without these but includes explicit styling for them:

- `flarum/likes`
- `fof/reactions`
- `fof/user-directory`
- `flarum/sticky`
- `flarum/tags`

## License

MIT
