<?php

use Flarum\Extend;
use Flarum\Frontend\Document;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Arr;
use Flarum\Api\Serializer\UserSerializer;
use Flarum\Extend\ApiSerializer;
use Resofire\CosmosTheme\Api\Controller\GiftHuntCompleteController;
use Resofire\CosmosTheme\Notification\GiftHuntCompleteBlueprint;
use Resofire\CosmosTheme\Api\Controller\EggHuntCompleteController;
use Resofire\CosmosTheme\Notification\EggHuntCompleteBlueprint;

$cosmosFonts = [
    'Outfit'            => ['url' => 'Outfit:wght@300;400;500;600;700;800',             'stack' => "'Outfit', sans-serif"],
    'Space Grotesk'     => ['url' => 'Space+Grotesk:wght@400;500;700',                  'stack' => "'Space Grotesk', sans-serif"],
    'DM Sans'           => ['url' => 'DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,700', 'stack' => "'DM Sans', sans-serif"],
    'Sora'              => ['url' => 'Sora:wght@400;600;700',                            'stack' => "'Sora', sans-serif"],
    'Plus Jakarta Sans' => ['url' => 'Plus+Jakarta+Sans:wght@400;500;700',               'stack' => "'Plus Jakarta Sans', sans-serif"],
    'Nunito'            => ['url' => 'Nunito:wght@400;600;700',                          'stack' => "'Nunito', sans-serif"],
    'system-ui'         => ['url' => null, 'stack' => 'system-ui, -apple-system, BlinkMacSystemFont, sans-serif'],
];

return [
    (new Extend\Theme)
        ->overrideFileSource('variables.less', __DIR__ . '/less/variables.less'),

    (new Extend\Settings)
        ->default('resofire-cosmos-theme.font', 'Outfit')
        ->default('cosmos-theme.default_theme', 0)
        ->default('cosmos-theme.effects', 1)
        ->default('cosmos-theme.slider_enabled', 0)
        ->default('cosmos-theme.slider_disable_mobile', 1)
        ->default('cosmos-theme.slider_hide_on_tag_pages', 0)
        ->default('cosmos-theme.slider_height_desktop', 320)
        ->default('cosmos-theme.slider_height_mobile', 200)
        ->default('cosmos-theme.slider_autoplay', 0)
        ->default('cosmos-theme.slider_slides', '[]')
        ->default('cosmos-theme.always_show_controls', 0)
        ->default('cosmos-theme.holiday_hats', 0)
        ->default('cosmos-theme.holiday_hats_auto', 0)
        ->default('cosmos-theme.holiday_lights', 0)
        ->default('cosmos-theme.holiday_snow', 0)
        ->default('cosmos-theme.holiday_gifts', 0)
        ->default('cosmos-theme.holiday_hat_angle', -22)
        ->default('cosmos-theme.holiday_hat_top', -52)
        ->default('cosmos-theme.holiday_hat_left', -22)
        ->default('cosmos-theme.holiday_hat_size', 112)
        ->default('cosmos-theme.holiday_hat_flop', 51)
        ->default('cosmos-theme.holiday_hat_width', 30)
        ->default('cosmos-theme.holiday_hat_brim', 38)
        ->default('cosmos-theme.holiday_hat_pomp', 10)

        // Easter settings
        ->default('cosmos-theme.easter_ears', 0)
        ->default('cosmos-theme.easter_streamers', 0)
        ->default('cosmos-theme.easter_basket', 0)
        ->default('cosmos-theme.easter_bunny', 0)
        ->default('cosmos-theme.easter_start', '')
        ->default('cosmos-theme.easter_end', '')
        ->default('cosmos-theme.easter_ear_size', 100)
        ->default('cosmos-theme.easter_ear_top', -90)

        ->serializeToForum('cosmosHoliday_gifts', 'cosmos-theme.holiday_gifts', 'intval')
        ->serializeToForum('cosmosHoliday_hats', 'cosmos-theme.holiday_hats', 'intval')
        ->serializeToForum('cosmosHoliday_hatsAuto', 'cosmos-theme.holiday_hats_auto', 'intval')
        ->serializeToForum('cosmosHoliday_lights', 'cosmos-theme.holiday_lights', 'intval')
        ->serializeToForum('cosmosHoliday_snow', 'cosmos-theme.holiday_snow', 'intval')
        ->serializeToForum('cosmosHoliday_angle', 'cosmos-theme.holiday_hat_angle', 'intval')
        ->serializeToForum('cosmosHoliday_top', 'cosmos-theme.holiday_hat_top', 'intval')
        ->serializeToForum('cosmosHoliday_left', 'cosmos-theme.holiday_hat_left', 'intval')
        ->serializeToForum('cosmosHoliday_size', 'cosmos-theme.holiday_hat_size', 'intval')
        ->serializeToForum('cosmosHoliday_flop', 'cosmos-theme.holiday_hat_flop', 'intval')
        ->serializeToForum('cosmosHoliday_width', 'cosmos-theme.holiday_hat_width', 'intval')
        ->serializeToForum('cosmosHoliday_brim', 'cosmos-theme.holiday_hat_brim', 'intval')
        ->serializeToForum('cosmosHoliday_pomp', 'cosmos-theme.holiday_hat_pomp', 'intval')

        // Easter serializations
        ->serializeToForum('cosmosEaster_ears',      'cosmos-theme.easter_ears',      'intval')
        ->serializeToForum('cosmosEaster_streamers', 'cosmos-theme.easter_streamers', 'intval')
        ->serializeToForum('cosmosEaster_basket',    'cosmos-theme.easter_basket',    'intval')
        ->serializeToForum('cosmosEaster_bunny',     'cosmos-theme.easter_bunny',     'intval')
        ->serializeToForum('cosmosEaster_start',     'cosmos-theme.easter_start',     'strval')
        ->serializeToForum('cosmosEaster_end',       'cosmos-theme.easter_end',       'strval')
        ->serializeToForum('cosmosEaster_earSize',   'cosmos-theme.easter_ear_size',  'intval')
        ->serializeToForum('cosmosEaster_earTop',    'cosmos-theme.easter_ear_top',   'intval')

        ->serializeToForum('cosmosTheme_default', 'cosmos-theme.default_theme', 'intval')
        ->serializeToForum('cosmosThemeEffects', 'cosmos-theme.effects', 'intval')
        ->serializeToForum('cosmosSlider_enabled', 'cosmos-theme.slider_enabled', 'intval')
        ->serializeToForum('cosmosSlider_disableMobile', 'cosmos-theme.slider_disable_mobile', 'intval')
        ->serializeToForum('cosmosSlider_hideOnTagPages', 'cosmos-theme.slider_hide_on_tag_pages', 'intval')
        ->serializeToForum('cosmosSlider_heightDesktop', 'cosmos-theme.slider_height_desktop', 'intval')
        ->serializeToForum('cosmosSlider_heightMobile', 'cosmos-theme.slider_height_mobile', 'intval')
        ->serializeToForum('cosmosSlider_autoplay', 'cosmos-theme.slider_autoplay', 'intval')
        ->serializeToForum('cosmosSlider_slides', 'cosmos-theme.slider_slides', 'strval'),

    // Register cosmosTheme user preference (0=auto, 1=day, 2=night)
    (new Extend\User)
        ->registerPreference('cosmosTheme', function ($value) {
            if ($value === '' || $value === null) {
                return (int) resolve(SettingsRepositoryInterface::class)->get('cosmos-theme.default_theme', 0);
            }
            return (int) $value;
        }),

    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/forum.js')
        ->css(__DIR__ . '/less/forum.css')
        ->content(function (Document $document, $request) use ($cosmosFonts) {
            $settings = resolve(SettingsRepositoryInterface::class);

            // Font injection
            $font = $settings->get('resofire-cosmos-theme.font', 'Outfit');
            if (!isset($cosmosFonts[$font])) $font = 'Outfit';
            $fontData = $cosmosFonts[$font];
            $fontHead = '';
            if ($fontData['url'] !== null) {
                $googleUrl = 'https://fonts.googleapis.com/css2?family=' . $fontData['url'] . '&display=swap';
                $fontHead .= '<link rel="preconnect" href="https://fonts.googleapis.com">';
                $fontHead .= '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
                $fontHead .= '<link rel="stylesheet" href="' . htmlspecialchars($googleUrl, ENT_QUOTES, 'UTF-8') . '">';
            }
            $fontHead .= '<style>:root{--cosmos-font:' . $fontData['stack'] . ';}</style>';
            $document->head[] = $fontHead;

            // Resolve theme preference server-side for initial page render
            $actor    = RequestUtil::getActor($request);
            $default  = (int) $settings->get('cosmos-theme.default_theme', 0);
            $preference = null;

            if (!$actor->isGuest()) {
                $pref = $actor->getPreference('cosmosTheme');
                if ($pref !== null && $pref !== '') {
                    $preference = (int) $pref;
                }
            }

            if ($preference === null) {
                $cookieTheme = Arr::get($request->getCookieParams(), 'cosmos_theme');
                if ($cookieTheme !== null) $preference = (int) $cookieTheme;
            }

            if ($preference === null || $preference < 0 || $preference > 2) {
                $preference = $default;
            }

            // Inject day CSS as an inline <style> so no extra HTTP request is needed
            // and the asset pipeline doesn't need a second compiled file.
            // PHP injects it; JS toggles its media attribute to enable/disable it.
            // media="all"     = active (day mode)
            // media="not all" = disabled (night mode)
            $dayCss = file_get_contents(__DIR__ . '/less/forum-day.css');
            $media  = ($preference === 1) ? 'all' : 'not all';

            $document->head[] = '<style id="cosmos-day-css" media="' . $media . '">' . $dayCss . '</style>';

            // Effects (stars + glows) — inject disable override when setting is off
            $effects = (int) $settings->get('cosmos-theme.effects', 1);
            if ($effects === 0) {
                $document->head[] = '<style id="cosmos-effects-disabled">'
                    . '.WelcomeHero::after,'
                    . '.IndexPage-nav .item-nav::after,'
                    . '.UserCard--directory::after,'
                    . '.UserCard--small::after,'
                    . '.CosmosSlider-stars::after{'
                    . 'display:none!important}'
                    . '.CosmosSlider-fade{display:none!important}'
                    . '.WelcomeHero::before,'
                    . '.IndexPage-nav .item-nav::before{'
                    . 'background:none!important}'
                    . '.DiscussionListItem:hover,'
                    . '.IndexPage-nav .item-newDiscussion .Button:hover,'
                    . '.IndexPage-nav .item-newDiscussion .Button.Button--primary:hover{'
                    . 'box-shadow:none!important}'
                    . '</style>';
            }

            // Always-visible post controls — inject override when setting is on
            $alwaysShowControls = (int) $settings->get('cosmos-theme.always_show_controls', 0);
            if ($alwaysShowControls === 1) {
                $document->head[] = '<style id="cosmos-always-show-controls">'
                    . '.no-touch .Post-actions{'
                    . 'opacity:1!important}'
                    . '</style>';
            }

            // color-scheme meta
            if ($preference === 1) {
                $document->meta['color-scheme'] = 'light';
            } elseif ($preference === 2) {
                $document->meta['color-scheme'] = 'dark';
            } else {
                $document->meta['color-scheme'] = 'light dark';
            }
        }),

    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/admin.js')
        ->css(__DIR__ . '/less/admin.css')
        ->content(function (Document $document) use ($cosmosFonts) {
            $settings = resolve(SettingsRepositoryInterface::class);
            $font = $settings->get('resofire-cosmos-theme.font', 'Outfit');
            if (!isset($cosmosFonts[$font])) $font = 'Outfit';
            $fontData = $cosmosFonts[$font];
            $head = '';
            if ($fontData['url'] !== null) {
                $googleUrl = 'https://fonts.googleapis.com/css2?family=' . $fontData['url'] . '&display=swap';
                $head .= '<link rel="preconnect" href="https://fonts.googleapis.com">';
                $head .= '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
                $head .= '<link rel="stylesheet" href="' . htmlspecialchars($googleUrl, ENT_QUOTES, 'UTF-8') . '">';
            }
            $head .= '<style>:root{--cosmos-font:' . $fontData['stack'] . ';}</style>';
            $document->head[] = $head;
        }),

    new Extend\Locales(__DIR__ . '/resources/locale'),

    // Gift hunt completion endpoint
    (new Extend\Routes('api'))
        ->post(
            '/resofire/cosmos/gift-complete',
            'resofire.cosmos.gift-complete',
            GiftHuntCompleteController::class
        ),

    // Register the gift hunt notification type
    (new Extend\Notification)
        ->type(GiftHuntCompleteBlueprint::class, UserSerializer::class, ['alert']),

    // Egg hunt completion endpoint
    (new Extend\Routes('api'))
        ->post(
            '/resofire/cosmos/egg-complete',
            'resofire.cosmos.egg-complete',
            EggHuntCompleteController::class
        ),

    // Register the egg hunt notification type
    (new Extend\Notification)
        ->type(EggHuntCompleteBlueprint::class, UserSerializer::class, ['alert']),

    // Serialize like/reaction counts (given + received) onto the user API response
    (new Extend\ApiSerializer(UserSerializer::class))
        ->attribute('likesReceived', function (UserSerializer $serializer, $user) {
            $extensions = resolve(\Flarum\Extension\ExtensionManager::class);
            if (!$extensions->isEnabled('flarum-likes')) return null;
            try {
                return (int) resolve('db')->table('post_likes')
                    ->join('posts', 'post_likes.post_id', '=', 'posts.id')
                    ->where('posts.user_id', $user->id)
                    ->count();
            } catch (\Exception $e) {
                return null;
            }
        })
        ->attribute('likesGiven', function (UserSerializer $serializer, $user) {
            $extensions = resolve(\Flarum\Extension\ExtensionManager::class);
            if (!$extensions->isEnabled('flarum-likes')) return null;
            try {
                return (int) resolve('db')->table('post_likes')
                    ->where('user_id', $user->id)
                    ->count();
            } catch (\Exception $e) {
                return null;
            }
        })
        ->attribute('reactionsReceived', function (UserSerializer $serializer, $user) {
            $extensions = resolve(\Flarum\Extension\ExtensionManager::class);
            if (!$extensions->isEnabled('fof-reactions')) return null;
            try {
                return (int) resolve('db')->table('post_reactions')
                    ->join('posts', 'post_reactions.post_id', '=', 'posts.id')
                    ->where('posts.user_id', $user->id)
                    ->count();
            } catch (\Exception $e) {
                return null;
            }
        })
        ->attribute('reactionsGiven', function (UserSerializer $serializer, $user) {
            $extensions = resolve(\Flarum\Extension\ExtensionManager::class);
            if (!$extensions->isEnabled('fof-reactions')) return null;
            try {
                return (int) resolve('db')->table('post_reactions')
                    ->where('user_id', $user->id)
                    ->count();
            } catch (\Exception $e) {
                return null;
            }
        }),
];
