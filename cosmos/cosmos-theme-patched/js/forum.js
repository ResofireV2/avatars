'use strict';

// Cosmos Theme — forum.js
//
// Self-contained day/night theme toggle. No dependency on fof/nightmode.
//
// Themes:  0 = auto, 1 = day (light), 2 = night (dark)
// Cookie:  cosmos_theme   (per-device / guest storage)
// Pref:    cosmosTheme    (user account preference, saved via Flarum prefs API)
//
// The day CSS is injected by PHP as:
//   <style id="cosmos-day-css" media="all|not all">...</style>
// The night CSS is the normally compiled forum.css.
// We toggle day mode by switching that style element's media attribute.
//
// Flarum wraps this file as:
//   var module={}; [this file]; flarum.extensions['resofire-cosmos-theme']=module.exports;
// window.app is available at execution time (core JS loads first in the bundle).

var extend   = flarum.core.compat['common/extend'].extend;

// Apply effects toggle on JS boot (mirrors PHP injection for client-side accuracy)
// Run at default priority (0) so app.forum is available
app.initializers.add('resofire-cosmos-theme-effects', function() {
  if (!app.forum) return;
  var effects = app.forum.attribute('cosmosThemeEffects');
  if (effects === 0 || effects === '0') {
    if (!document.getElementById('cosmos-effects-disabled')) {
      var s = document.createElement('style');
      s.id = 'cosmos-effects-disabled';
      s.textContent = [
        '.WelcomeHero::after,',
        '.IndexPage-nav .item-nav::after,',
        '.UserCard--directory::after,',
        '.UserCard--small::after{display:none!important}',
        '.WelcomeHero::before,',
        '.IndexPage-nav .item-nav::before{background:none!important}',
        '.DiscussionListItem:hover,',
        '.IndexPage-nav .item-newDiscussion .Button:hover,',
        '.IndexPage-nav .item-newDiscussion .Button.Button--primary:hover{box-shadow:none!important}',
      ].join('');
      document.head.appendChild(s);
    }
  } else {
    var existing = document.getElementById('cosmos-effects-disabled');
    if (existing) existing.remove();
  }
});
var Page     = flarum.core.compat['common/components/Page'];
var Button   = flarum.core.compat['common/components/Button'];
var Select   = flarum.core.compat['common/components/Select'];
var FieldSet = flarum.core.compat['common/components/FieldSet'];

var HeaderSecondary = flarum.core.compat['forum/components/HeaderSecondary'];
var SessionDropdown = flarum.core.compat['forum/components/SessionDropdown'];
var SettingsPage    = flarum.core.compat['forum/components/SettingsPage'];

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------
var COOKIE_KEY = 'cosmos_theme';
var PREF_KEY   = 'cosmosTheme';
var Themes     = { AUTO: 0, LIGHT: 1, DARK: 2 };

function defaultTheme() {
  return parseInt(app.forum.attribute('cosmosTheme_default') || '0', 10) || Themes.AUTO;
}

// ---------------------------------------------------------------------------
// Cookie helpers
// ---------------------------------------------------------------------------
function cookieGet() {
  var match = document.cookie.match(new RegExp('(?:^|;\\s*)' + COOKIE_KEY + '=([^;]*)'));
  if (!match) return null;
  var val = parseInt(match[1], 10);
  return isNaN(val) ? null : val;
}

function cookieSet(val) {
  var secure = location.protocol === 'https:' ? '; Secure' : '';
  document.cookie = COOKIE_KEY + '=' + val + '; Path=/; SameSite=Lax' + secure;
}

// ---------------------------------------------------------------------------
// Theme resolution
// ---------------------------------------------------------------------------
function getTheme() {
  var user = app.session.user;
  if (user) {
    var pref = user.preferences()[PREF_KEY];
    if (typeof pref === 'number' && pref >= 0 && pref <= 2) return pref;
  }
  var cookie = cookieGet();
  if (cookie !== null && cookie >= 0 && cookie <= 2) return cookie;
  return defaultTheme();
}

function isLight(theme) {
  if (theme === Themes.LIGHT) return true;
  if (theme === Themes.AUTO) return !window.matchMedia('(prefers-color-scheme: dark)').matches;
  return false;
}

// ---------------------------------------------------------------------------
// Apply theme
// Toggles the inline <style id="cosmos-day-css"> element's media attribute.
// media="all"     = day CSS active
// media="not all" = day CSS disabled (night CSS from compiled forum.css takes over)
// ---------------------------------------------------------------------------
function applyTheme(animate) {
  var light    = isLight(getTheme());
  var dayStyle = document.getElementById('cosmos-day-css');
  var meta     = document.querySelector('meta[name="color-scheme"]');

  function doSwap() {
    if (dayStyle) dayStyle.media = light ? 'all' : 'not all';
    if (meta) meta.content = light ? 'light' : 'dark';
    document.dispatchEvent(new CustomEvent('cosmosthemechange', { detail: light ? 'day' : 'night' }));
    m.redraw();
  }

  if (animate && dayStyle) {
    // 1. Add transition class so CSS transitions are primed BEFORE the swap
    document.documentElement.classList.add('cosmos-transitioning');
    // 2. Double rAF — ensures browser has painted the transition class first
    requestAnimationFrame(function() {
      requestAnimationFrame(function() {
        doSwap();
        // 3. Remove class after transition completes
        setTimeout(function() {
          document.documentElement.classList.remove('cosmos-transitioning');
        }, 700);
      });
    });
  } else {
    doSwap();
  }
}

// ---------------------------------------------------------------------------
// Switch theme (toggles between day and night — no auto on button click)
// ---------------------------------------------------------------------------
function switchTheme() {
  var current = getTheme();
  var next    = isLight(current) ? Themes.DARK : Themes.LIGHT;
  var user    = app.session.user;

  if (user) {
    var prefs = {};
    prefs[PREF_KEY] = next;
    user.savePreferences(prefs).then(function() { applyTheme(true); });
  } else {
    cookieSet(next);
    applyTheme(true);
  }
}

// ---------------------------------------------------------------------------
// Initializer
// ---------------------------------------------------------------------------
app.initializers.add('resofire-cosmos-theme', function() {
  // Re-apply on every page navigation
  extend(Page.prototype, 'oninit', function() { applyTheme(); });

  // Re-apply when OS dark/light preference changes (for auto mode users)
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function() {
    if (getTheme() === Themes.AUTO) applyTheme();
  });

  // -------------------------------------------------------------------------
  // Header toggle button — ALWAYS visible (no admin gate, unlike nightmode)
  // Currently light → show moon (click to go dark)
  // Currently dark  → show sun  (click to go light)
  // -------------------------------------------------------------------------
  extend(HeaderSecondary.prototype, 'items', function(items) {
    var light = isLight(getTheme());
    var icon  = light ? 'far fa-moon' : 'far fa-sun';
    var title = app.translator.trans('cosmos-theme.forum.header.toggle_button');

    items.add(
      'cosmos-theme-toggle',
      m(Button, {
        className: 'Button Button--flat',
        onclick: function() { switchTheme(); },
        icon: icon,
        'aria-label': title,
      }, title),
      16
    );
  });

  // -------------------------------------------------------------------------
  // Session dropdown entry
  // -------------------------------------------------------------------------
  extend(SessionDropdown.prototype, 'items', function(items) {
    var light = isLight(getTheme());
    var icon  = light ? 'far fa-moon' : 'far fa-sun';
    var label = app.translator.trans(light ? 'cosmos-theme.forum.night' : 'cosmos-theme.forum.day');

    items.add(
      light ? 'cosmos-night' : 'cosmos-day',
      m(Button, { icon: icon, onclick: function() { switchTheme(); } }, label),
      -1
    );
  });

  // -------------------------------------------------------------------------
  // User settings page — theme select
  // -------------------------------------------------------------------------
  extend(SettingsPage.prototype, 'settingsItems', function(items) {
    var user = app.session.user;
    if (!user) return;

    var current = getTheme();
    var t = function(k) { return app.translator.trans('cosmos-theme.forum.user.settings.' + k); };

    items.add(
      'cosmos-theme',
      m(FieldSet, { label: t('heading'), className: 'Settings-cosmosTheme' }, [
        m('p', { className: 'description' }, t('description')),
        m('p', { className: 'description' }, t('description2')),
        m(Select, {
          value: current,
          className: 'Settings-cosmosTheme--input',
          onchange: function(val) {
            var numVal = parseInt(val, 10);
            var prefs = {};
            prefs[PREF_KEY] = numVal;
            user.savePreferences(prefs).then(function() {
              applyTheme();
              m.redraw();
            });
          },
          options: {
            0: t('options.auto'),
            1: t('options.day'),
            2: t('options.night'),
          },
        }),
      ])
    );
  });
});

// ---------------------------------------------------------------------------
// PARTICLE BURST — Like & React buttons
// ---------------------------------------------------------------------------
app.initializers.add('resofire-cosmos-theme-particles', function() {

  // Particle colours: teal shades for Like, warm accent for React
  var LIKE_COLOURS    = ['#69c6b9', '#4db3a5', '#8fd8d0', '#ffffff', '#a8e6e0'];
  var REACT_COLOURS   = ['#69c6b9', '#e88c5a', '#d4a730', '#ffffff', '#b07fd4'];

  function burst(x, y, colours) {
    var count = 8;
    for (var i = 0; i < count; i++) {
      var el = document.createElement('span');
      el.className = 'CosmosParticle';

      // Random outward direction
      var angle  = (i / count) * 2 * Math.PI + (Math.random() - 0.5) * 0.6;
      var dist   = 28 + Math.random() * 22;
      var dx     = Math.cos(angle) * dist;
      var dy     = Math.sin(angle) * dist;
      var colour = colours[Math.floor(Math.random() * colours.length)];
      var size   = 3 + Math.random() * 4;

      el.style.cssText = [
        'left:' + (x - size / 2) + 'px',
        'top:' + (y - size / 2) + 'px',
        'width:' + size + 'px',
        'height:' + size + 'px',
        'background:' + colour,
        '--dx:' + dx + 'px',
        '--dy:' + dy + 'px',
        'animation-duration:' + (480 + Math.random() * 140) + 'ms',
      ].join(';');

      document.body.appendChild(el);
      el.addEventListener('animationend', function() { this.remove(); });
    }
  }

  // Hook Like button
  var LikeButton = flarum.core.compat['likes/components/LikeButton'] ||
                   (flarum.extensions['flarum-likes'] && flarum.extensions['flarum-likes'].LikeButton);
  if (LikeButton) {
    extend(LikeButton.prototype, 'view', function(vnode) {
      var orig = vnode.attrs;
      var prevClick = orig.onclick;
      orig.onclick = function(e) {
        if (e) burst(e.clientX, e.clientY, LIKE_COLOURS);
        if (prevClick) prevClick.call(this, e);
      };
    });
  } else {
    // Fallback: delegate click on .item-like buttons
    document.addEventListener('click', function(e) {
      var btn = e.target.closest && e.target.closest('.item-like .Button');
      if (btn) burst(e.clientX, e.clientY, LIKE_COLOURS);
    });
  }

  // Hook React button (fof/reactions)
  document.addEventListener('click', function(e) {
    // React trigger button
    if (e.target.closest && e.target.closest('.Reactions--ShowReactions')) {
      burst(e.clientX, e.clientY, REACT_COLOURS);
    }
    // Individual reaction selection in the picker
    var reactionBtn = e.target.closest && e.target.closest('.Reactions--Ul .Button-emoji-parent');
    if (reactionBtn) {
      burst(e.clientX, e.clientY, REACT_COLOURS);
    }
  });
});

// ---------------------------------------------------------------------------
// USER MENTION AVATARS — inject avatar before @username in post bodies
// ---------------------------------------------------------------------------
app.initializers.add('resofire-cosmos-theme-mention-avatars', function() {
  var CommentPost = flarum.core.compat['forum/components/CommentPost'];
  if (!CommentPost) return;

  function injectMentionAvatars(postDom) {
    var mentions = postDom.querySelectorAll('a.UserMention:not(.cosmos-avatar-injected)');
    mentions.forEach(function(el) {
      el.classList.add('cosmos-avatar-injected');

      // Extract slug from href: ends with /username-slug
      var href = el.getAttribute('href') || '';
      var slug = href.split('/').pop();
      if (!slug) return;

      // Look up user in store by slug
      var users = app.store.all('users');
      var user = null;
      for (var i = 0; i < users.length; i++) {
        if (users[i].slug && users[i].slug() === slug) {
          user = users[i];
          break;
        }
      }

      var img = document.createElement('img');
      img.className = 'UserMention-avatar';
      img.width = 16;
      img.height = 16;

      if (user && user.avatarUrl && user.avatarUrl()) {
        img.src = user.avatarUrl();
        img.alt = '';
        img.onerror = function() {
          // Fall back to letter avatar on broken image
          this.style.display = 'none';
          var letter = document.createElement('span');
          letter.className = 'UserMention-avatar UserMention-avatar--letter';
          letter.textContent = (user.displayName ? user.displayName() : slug).charAt(0).toUpperCase();
          el.insertBefore(letter, el.firstChild);
        };
      } else {
        // No avatar URL — use letter fallback
        img.style.display = 'none';
        var letter = document.createElement('span');
        letter.className = 'UserMention-avatar UserMention-avatar--letter';
        letter.textContent = slug.charAt(0).toUpperCase();
        el.insertBefore(letter, el.firstChild);
        return;
      }

      el.insertBefore(img, el.firstChild);
    });
  }

  extend(CommentPost.prototype, 'oncreate', function(_, vnode) {
    if (vnode && vnode.dom) injectMentionAvatars(vnode.dom);
  });
  extend(CommentPost.prototype, 'onupdate', function(_, vnode) {
    if (vnode && vnode.dom) injectMentionAvatars(vnode.dom);
  });
});



// ---------------------------------------------------------------------------
// HOLIDAY HATS — Santa hat on every avatar
// ---------------------------------------------------------------------------
app.initializers.add('resofire-cosmos-theme-holiday', function() {
  function _run() { cosmosHolidayInit(); }
  if (app.booted && typeof app.booted.then === 'function') {
    app.booted.then(_run, _run);
  } else {
    setTimeout(_run, 0);
  }

  function cosmosHolidayInit() {
    if (!app.forum) { console.warn('[CosmosHat] app.forum not ready'); return; }

    function forumAttr(key) {
      try { return app.forum.attribute(key); } catch(e) { return undefined; }
    }

    function hatsActive() {
      var auto = forumAttr('cosmosHoliday_hatsAuto');
      if (auto === 1 || auto === '1') {
        var mo = new Date().getMonth() + 1, d = new Date().getDate();
        if (mo === 12 || (mo === 1 && d <= 6)) return true;
        return false;
      }
      var v = forumAttr('cosmosHoliday_hats');
      return v === 1 || v === '1' || v === true;
    }

    if (!hatsActive()) return;

    function attr(key, def) {
      var v = parseInt(forumAttr(key), 10);
      return isNaN(v) ? def : v;
    }

    var HAT_CFG = {
      flop:  attr('cosmosHoliday_flop',  51),
      width: attr('cosmosHoliday_width',  30),
      brim:  attr('cosmosHoliday_brim',   38),
      pomp:  attr('cosmosHoliday_pomp',   10),
      angle: attr('cosmosHoliday_angle', -22),
      top:   attr('cosmosHoliday_top',   -52),
      left:  attr('cosmosHoliday_left',  -22),
      size:  attr('cosmosHoliday_size',  112),
    };

    // ── Exclusion list ────────────────────────────────────────────────
    var EXCLUDE_SELECTORS = [
      '.SessionDropdown',
      '.MobileTab',
      '.NotificationList',
      '.Search-results',
      '.Post-reactions',
      '.DiscussionParticipants-avatar',
      '.ParticipantsModal-item',
      '.TextEditor-controls',
      '.UserCard--popover',
    ];

    function isExcluded(el) {
      for (var i = 0; i < EXCLUDE_SELECTORS.length; i++) {
        if (el.closest(EXCLUDE_SELECTORS[i])) return true;
      }
      return false;
    }

    // ── Hat SVG (built once, cached as base64) ────────────────────────
    var _hatB64 = null;
    function getHatB64() {
      if (_hatB64) return _hatB64;
      var ns = 'http://www.w3.org/2000/svg';
      function el(tag, attrs, parent) {
        var e = document.createElementNS(ns, tag);
        Object.keys(attrs).forEach(function(k) { e.setAttribute(k, attrs[k]); });
        if (parent) parent.appendChild(e);
        return e;
      }
      var c = HAT_CFG;
      var tipX = 62 - c.flop, tipY = 8 + c.flop * 0.35, rightX = 40 + c.width;
      var s = el('svg', { xmlns: ns, viewBox: '0 0 120 95' });
      var d = el('defs', {}, s);
      var g1 = el('linearGradient', { id: 'hh1', x1:'0%', y1:'0%', x2:'100%', y2:'100%' }, d);
      el('stop', { offset:'0%',   'stop-color':'#e74c3c' }, g1);
      el('stop', { offset:'100%', 'stop-color':'#922b21' }, g1);
      var g2 = el('linearGradient', { id: 'hh2', x1:'0%', y1:'0%', x2:'0%', y2:'100%' }, d);
      el('stop', { offset:'0%',   'stop-color':'#f6f6f6' }, g2);
      el('stop', { offset:'100%', 'stop-color':'#d2d2d2' }, g2);
      el('ellipse', { cx:'40', cy:'86', rx:String(c.brim*0.8), ry:'5', fill:'rgba(0,0,0,0.1)' }, s);
      var cp = ['M',tipX,tipY,'Q',tipX+14,tipY+(78-tipY)*0.55,8,72,
        'Q',16,80,40-c.brim+6,82,'Q',40,78,rightX,70,
        'Q',rightX-8,tipY+(70-tipY)*0.6,tipX,tipY,'Z'].join(' ');
      el('path', { d:cp, fill:'url(#hh1)' }, s);
      var hl = ['M',tipX+2,tipY+4,'Q',tipX+10,tipY+(78-tipY)*0.5,tipX+4,70,
        'Q',tipX+14,64,tipX+20,56,'Q',tipX+12,tipY+(56-tipY)*0.5,tipX+2,tipY+4,'Z'].join(' ');
      el('path', { d:hl, fill:'rgba(255,255,255,0.13)' }, s);
      el('ellipse', { cx:'40', cy:'82', rx:String(c.brim), ry:'11',
        fill:'url(#hh2)', stroke:'#c5c5c5', 'stroke-width':'0.5' }, s);
      el('ellipse', { cx:'38', cy:'78', rx:String(c.brim-4), ry:'5.5',
        fill:'rgba(255,255,255,0.52)' }, s);
      el('circle', { cx:String(tipX), cy:String(tipY), r:String(c.pomp),
        fill:'#f2f2f2', stroke:'#ddd', 'stroke-width':'0.5' }, s);
      [[-3,-3,0.27,0.44],[3,-2,0.22,0.40],[0,3,0.27,0.44],
       [-4,2,0.20,0.34],[3,2,0.18,0.34]].forEach(function(f) {
        el('circle', { cx:String(tipX+f[0]), cy:String(tipY+f[1]),
          r:String(c.pomp*f[2]), fill:'rgba(200,200,200,'+f[3]+')' }, s);
      });
      el('circle', { cx:String(tipX-c.pomp*0.22), cy:String(tipY-c.pomp*0.25),
        r:String(c.pomp*0.38), fill:'rgba(255,255,255,0.76)' }, s);
      var xml = new XMLSerializer().serializeToString(s);
      _hatB64 = 'data:image/svg+xml;base64,' + btoa(xml);
      return _hatB64;
    }

    // ── Inject keyframe for hat sway ──────────────────────────────────
    if (!document.getElementById('cosmosHatKF')) {
      var kfEl = document.createElement('style');
      kfEl.id = 'cosmosHatKF';
      kfEl.textContent = '.CosmosHatWrap{position:relative;display:inline-block;}'
        + '.CosmosHatWrap .CosmosHatImg{'
        + 'position:absolute;pointer-events:none;'
        + 'transform-origin:80% 88%;'
        + '}';
      document.head.appendChild(kfEl);
    }

    // ── Resolve avatar pixel size ─────────────────────────────────────
    function resolveSize(avEl) {
      var px = avEl.offsetWidth || avEl.clientWidth || 0;
      if (px > 0) return px;
      px = parseInt(avEl.getAttribute('width'), 10);
      if (px > 0) return px;
      var style = window.getComputedStyle(avEl);
      px = parseInt(style.getPropertyValue('--size'), 10);
      if (px > 0) return px;
      px = parseInt(style.width, 10);
      if (px > 0) return px;
      return 40;
    }

    // ── Build the hat img element ─────────────────────────────────────
    function buildHat(avPx) {
      var img = document.createElement('img');
      img.src = getHatB64();
      img.setAttribute('aria-hidden', 'true');
      img.className = 'CosmosHatImg';
      var w = avPx * (HAT_CFG.size / 100);
      img.style.cssText = [
        'width:'  + w + 'px',
        'height:' + w + 'px',
        'left:'   + (avPx * (HAT_CFG.left / 100)) + 'px',
        'top:'    + (avPx * (HAT_CFG.top  / 100)) + 'px',
        'transform:rotate(' + HAT_CFG.angle + 'deg)',
      ].join(';');
      return img;
    }

    // ── APPROACH DETECTION ────────────────────────────────────────────
    // PostUser-avatar is position:absolute left:-85px inside .PostUser
    // (position:relative). Wrapping it breaks the absolute layout.
    // For that case we attach the hat directly to .PostUser instead.
    // For all other avatars we wrap in a .CosmosHatWrap span.

    function isPostUserAvatar(avEl) {
      return avEl.classList.contains('PostUser-avatar') ||
             (avEl.parentNode && avEl.parentNode.classList &&
              avEl.parentNode.classList.contains('PostUser-avatar'));
    }

    // ── Attach hat to a PostUser-avatar context ───────────────────────
    // .PostUser is display:inline + position:relative. Absolute children of
    // an inline element are positioned relative to the first LINE BOX, not
    // the visual bounding rect — so hat.top measured from .PostUser is wrong.
    // .Post is display:block + position:relative — a clean coordinate system.
    // We attach the hat to .Post and measure the avatar's offset from .Post.
    function attachHatToPostUser(avEl) {
      var post = avEl.closest('.Post');
      if (!post) return;
      // Use data attribute on the avatar itself so multiple hats don't stack
      if (avEl.dataset.cosmosHat) return;
      avEl.dataset.cosmosHat = '1';

      function place() {
        var avPx = avEl.offsetWidth || resolveSize(avEl);
        if (avPx < 1) { setTimeout(place, 50); return; }

        // Walk up the offsetParent chain to accumulate the avatar's position
        // relative to .Post (which is position:relative, so it's the containing
        // block for any absolute descendants we place inside it).
        var left = 0, top = 0;
        var el = avEl;
        while (el && el !== post) {
          left += el.offsetLeft;
          top  += el.offsetTop;
          el    = el.offsetParent;
        }

        var w = avPx * (HAT_CFG.size / 100);
        var hat = document.createElement('img');
        hat.src = getHatB64();
        hat.setAttribute('aria-hidden', 'true');
        hat.className = 'CosmosHatImg';

        // POST_OFFSET_X/Y: empirically measured per avatar size.
        // Desktop/tablet (64px): X=-13, Y=14. Mobile (32px): X=-6, Y=7.
        var POST_OFFSET_X, POST_OFFSET_Y;
        if (avPx <= 32) {
          // Mobile (32px avatar): tuned X=-6, Y=7
          POST_OFFSET_X = -6;
          POST_OFFSET_Y = 7;
        } else {
          // Desktop/tablet (64px avatar): tuned X=-13, Y=14
          POST_OFFSET_X = -13;
          POST_OFFSET_Y = 14;
        }

        hat.style.cssText = [
          'position:absolute',
          'width:'     + w + 'px',
          'height:'    + w + 'px',
          'left:'      + (left + avPx * (HAT_CFG.left / 100) + POST_OFFSET_X) + 'px',
          'top:'       + (top  + avPx * (HAT_CFG.top  / 100) + POST_OFFSET_Y) + 'px',
          'transform:rotate(' + HAT_CFG.angle + 'deg)',
          'pointer-events:none',
          'z-index:10',
        ].join(';');
        post.appendChild(hat);
      }

      requestAnimationFrame(place);
    }



    // ── Attach hat via wrapper for all other avatars ──────────────────
    function attachHatViaWrapper(avEl) {
      if (avEl.dataset.cosmosHat) return;
      avEl.dataset.cosmosHat = '1';

      var avPx = resolveSize(avEl);
      if (avPx < 1) avPx = 36;

      // Use resolveSize (which reads --size CSS var) for wrapper dimensions
      // offsetWidth/offsetHeight can be 0 on mobile before paint
      var wrapPx = avPx;

      var wrap = document.createElement('span');
      wrap.className = 'CosmosHatWrap';
      wrap.style.cssText = [
        'display:inline-block',
        'position:relative',
        'width:'  + wrapPx + 'px',
        'height:' + wrapPx + 'px',
        'vertical-align:top',
        'overflow:visible',
      ].join(';');

      avEl.parentNode.insertBefore(wrap, avEl);
      wrap.appendChild(avEl);

      var hat = buildHat(avPx);
      hat.style.position = 'absolute';
      wrap.appendChild(hat);
    }

    // ── Main attach dispatcher ────────────────────────────────────────
    function attachHat(avEl) {
      if (avEl.dataset.cosmosHat) return;
      if (isExcluded(avEl)) return;
      // Skip UserMention inline avatars
      if (avEl.classList.contains('UserMention-avatar')) return;

      if (isPostUserAvatar(avEl)) {
        attachHatToPostUser(avEl);
      } else {
        attachHatViaWrapper(avEl);
      }
    }

    // ── Scan DOM for unhatted avatars ─────────────────────────────────
    function processAvatars(root) {
      var els = (root || document).querySelectorAll('.Avatar');
      for (var i = 0; i < els.length; i++) {
        attachHat(els[i]);
      }
    }

    // ── MutationObserver — catch dynamically inserted avatars ─────────
    var _observer = new MutationObserver(function(mutations) {
      for (var m = 0; m < mutations.length; m++) {
        var nodes = mutations[m].addedNodes;
        for (var n = 0; n < nodes.length; n++) {
          var node = nodes[n];
          if (node.nodeType !== 1) continue;
          if (node.classList && node.classList.contains('Avatar')) {
            attachHat(node);
          }
          var inner = node.querySelectorAll &&
            node.querySelectorAll('.Avatar');
          if (inner && inner.length) {
            for (var i = 0; i < inner.length; i++) attachHat(inner[i]);
          }
        }
      }
    });
    _observer.observe(document.body, {
      childList: true,
      subtree: true,
    });

    // ── Page lifecycle hooks ──────────────────────────────────────────
    extend(Page.prototype, 'oncreate', function(_, vnode) {
      if (vnode && vnode.dom) requestAnimationFrame(function() {
        processAvatars(vnode.dom);
      });
    });
    extend(Page.prototype, 'onupdate', function(_, vnode) {
      if (vnode && vnode.dom) requestAnimationFrame(function() {
        processAvatars(vnode.dom);
      });
    });

    // Initial scan
    requestAnimationFrame(function() { processAvatars(); });

  } // end cosmosHolidayInit
});

// ---------------------------------------------------------------------------
// USER HERO — stat pills (posts, discussions, likes received)
// ---------------------------------------------------------------------------
app.initializers.add('resofire-cosmos-theme-userhero', function() {
  var UserCard = flarum.core.compat['forum/components/UserCard'];

  extend(UserCard.prototype, 'view', function(vnode) {
    // Only apply to the hero on the profile page
    if (!vnode.attrs || !vnode.attrs.className ||
        vnode.attrs.className.indexOf('UserHero') === -1) return;

    var user = this.attrs.user;
    if (!user) return;

    function injectStats(cardDom) {
      // Avoid double-injection
      if (cardDom.querySelector('.UserHero-stats')) return;

      var info = cardDom.querySelector('.UserCard-info');
      if (!info) return;

      var posts       = user.commentCount()    || 0;
      var discussions = user.discussionCount() || 0;

      // Build stat pills
      var stats = document.createElement('div');
      stats.className = 'UserHero-stats';

      function pill(value, label) {
        var el = document.createElement('span');
        el.className = 'UserHero-stat';
        el.innerHTML = '<span class="UserHero-stat-value">' + value + '</span> ' + label;
        return el;
      }

      stats.appendChild(pill(posts,       posts       === 1 ? 'post'       : 'posts'));
      stats.appendChild(pill(discussions, discussions === 1 ? 'discussion' : 'discussions'));

      var attrs = (user.data && user.data.attributes) || {};

      function addStat(key, singular, plural) {
        var val = attrs[key];
        if (val !== null && val !== undefined) {
          stats.appendChild(pill(val, val === 1 ? singular : plural));
        }
      }

      addStat('likesReceived',     'like received',     'likes received');
      addStat('likesGiven',        'like given',        'likes given');
      addStat('reactionsReceived', 'reaction received', 'reactions received');
      addStat('reactionsGiven',    'reaction given',    'reactions given');

      info.parentNode.insertBefore(stats, info.nextSibling);
    }

    var orig = vnode.attrs;
    var prevCreate = orig.oncreate;
    var prevUpdate = orig.onupdate;

    orig.oncreate = function(v) {
      injectStats(v.dom);
      if (prevCreate) prevCreate.call(this, v);
    };
    orig.onupdate = function(v) {
      injectStats(v.dom);
      if (prevUpdate) prevUpdate.call(this, v);
    };
  });
});

// ---------------------------------------------------------------------------
// USER CARD POPOVER — inject stat pills into hover popover
// ---------------------------------------------------------------------------
app.initializers.add('resofire-cosmos-theme-popover', function() {
  var UserCard = flarum.core.compat['forum/components/UserCard'];

  extend(UserCard.prototype, 'view', function(vnode) {
    // Only target the hover popover, not the profile hero or directory cards
    if (!vnode.attrs || !vnode.attrs.className ||
        vnode.attrs.className.indexOf('UserCard--popover') === -1) return;

    var user = this.attrs.user;
    if (!user) return;

    function injectPopoverStats(cardDom) {
      if (cardDom.querySelector('.UserHero-stats')) return;

      var info = cardDom.querySelector('.UserCard-info');
      if (!info) return;

      var posts       = user.commentCount()    || 0;
      var discussions = user.discussionCount() || 0;
      var attrs       = (user.data && user.data.attributes) || {};

      var stats = document.createElement('div');
      stats.className = 'UserHero-stats';

      function pill(value, label) {
        var el = document.createElement('span');
        el.className = 'UserHero-stat';
        el.innerHTML = '<span class="UserHero-stat-value">' + value + '</span> ' + label;
        return el;
      }

      stats.appendChild(pill(posts,       posts       === 1 ? 'post'       : 'posts'));
      stats.appendChild(pill(discussions, discussions === 1 ? 'discussion' : 'discussions'));

      function addStat(key, singular, plural) {
        var val = attrs[key];
        if (val !== null && val !== undefined) {
          stats.appendChild(pill(val, val === 1 ? singular : plural));
        }
      }

      addStat('likesReceived',     'like received',     'likes received');
      addStat('reactionsReceived', 'reaction received', 'reactions received');

      info.parentNode.insertBefore(stats, info.nextSibling);
    }

    var orig = vnode.attrs;
    var prevCreate = orig.oncreate;
    var prevUpdate = orig.onupdate;

    orig.oncreate = function(v) {
      injectPopoverStats(v.dom);
      // Allow dropdown menus (kebab) to overflow the card bounds
      v.dom.style.overflow = 'visible';
      if (prevCreate) prevCreate.call(this, v);
    };
    orig.onupdate = function(v) {
      injectPopoverStats(v.dom);
      if (prevUpdate) prevUpdate.call(this, v);
    };
  });
});

// ---------------------------------------------------------------------------
// FOF/USER-DIRECTORY — Move badge inline with username on directory cards
// ---------------------------------------------------------------------------
// Extend core UserCard.view — works regardless of fof-user-directory load order.
// Only moves badges when the card has .UserCard--directory class.
app.initializers.add('resofire-cosmos-theme-directory', function() {
  var UserCard = flarum.core.compat['forum/components/UserCard'];

  extend(UserCard.prototype, 'view', function(vnode) {
    // Only apply to directory cards
    if (!vnode.attrs || !vnode.attrs.className ||
        vnode.attrs.className.indexOf('UserCard--directory') === -1) return;

    function moveBadges(cardDom) {
      var identity = cardDom.querySelector('.UserCard-identity');
      var badges   = cardDom.querySelector('.UserCard-badges');
      if (!identity || !badges) return;
      // Only restructure if this card has a group badge (admin/mod icon)
      var groupBadges = badges.querySelectorAll('li [class*="Badge--group"]');
      if (!groupBadges.length) return;
      // Already moved
      if (identity.contains(badges)) return;
      // Find the link inside identity
      var link = identity.querySelector('a');
      if (!link) return;
      // Append the badges UL inside the link so badge sits next to username text
      link.appendChild(badges);
    }

    var orig = vnode.attrs;
    var prevCreate = orig.oncreate;
    var prevUpdate = orig.onupdate;

    orig.oncreate = function(v) {
      moveBadges(v.dom);
      if (prevCreate) prevCreate.call(this, v);
    };
    orig.onupdate = function(v) {
      moveBadges(v.dom);
      if (prevUpdate) prevUpdate.call(this, v);
    };
  });
});

// ---------------------------------------------------------------------------
// COSMOS SLIDER
// ---------------------------------------------------------------------------

// ── CosmosSlider component ─────────────────────────────────────────────────
// Defined at module level so it's always available when view() is called.

function CosmosSlider() {
  this.index       = 0;
  this.interval    = null;
  this.touchStartX = null;
}

CosmosSlider.prototype.oncreate = function(vnode) {
  var self = this;
  var ms   = vnode.attrs.autoplayMs;
  var len  = vnode.attrs.slides.length;
  if (len > 1 && ms > 0) {
    this.interval = window.setInterval(function() {
      self.index = (self.index + 1) % len;
      m.redraw();
    }, ms);
  }
};

CosmosSlider.prototype.onremove = function() {
  if (this.interval) {
    window.clearInterval(this.interval);
    this.interval = null;
  }
};

CosmosSlider.prototype.view = function(vnode) {
  var self   = this;
  var slides = vnode.attrs.slides;
  var len    = slides.length;
  var hd     = vnode.attrs.heightDesktop;
  var hm     = vnode.attrs.heightMobile;
  var offset = -(this.index * 100);

  return m('div.CosmosSlider-hero', [
    m('div.CosmosSlider', {
      style: '--cs-h-desktop:' + hd + 'px;--cs-h-mobile:' + hm + 'px',
      onpointerdown: function(e) { self.touchStartX = e.clientX; },
      onpointerup: function(e) {
        if (self.touchStartX === null) return;
        var dx = e.clientX - self.touchStartX;
        self.touchStartX = null;
        if (Math.abs(dx) > 40) {
          self.index = dx < 0
            ? (self.index + 1) % len
            : (self.index - 1 + len) % len;
          m.redraw();
        }
      },
    }, [
      // Track
      m('div.CosmosSlider-track', {
        style: { transform: 'translateX(' + offset + '%)' }
      }, slides.map(function(s, i) {
        return m('a.CosmosSlide', {
          key: i,
          href: s.link || '#',
          target: s.link && s.newTab ? '_blank' : undefined,
          rel:    s.link && s.newTab ? 'noopener' : undefined,
          style:  { backgroundImage: "url('" + s.image + "')" },
          onclick: function(e) { if (!s.link) e.preventDefault(); },
        });
      })),

      // Prev / Next
      len > 1 ? m('button.CosmosSlider-btn.prev', {
        'aria-label': 'Previous',
        onclick: function() { self.index = (self.index - 1 + len) % len; }
      }, m('i.fas.fa-chevron-left')) : null,

      len > 1 ? m('button.CosmosSlider-btn.next', {
        'aria-label': 'Next',
        onclick: function() { self.index = (self.index + 1) % len; }
      }, m('i.fas.fa-chevron-right')) : null,

      // Dots
      len > 1 ? m('div.CosmosSlider-dots', slides.map(function(_, i) {
        return m('button.dot' + (self.index === i ? '.is-active' : ''), {
          key: i,
          'aria-label': 'Slide ' + (i + 1),
          onclick: function() { self.index = i; },
        });
      })) : null,

      // Star field overlay
      m('div.CosmosSlider-stars'),

      // Bottom gradient fade
      m('div.CosmosSlider-fade'),
    ])
  ]);
};

// ── Wire into Flarum ────────────────────────────────────────────────────────
// All compat lookups and override() happen inside the initializer so they run
// after boot when everything is guaranteed to be resolved.

app.initializers.add('resofire-cosmos-theme-slider', function() {
  var override  = flarum.core.compat['common/extend'].override;
  var IndexPage = flarum.core.compat['forum/components/IndexPage'];

  function isMobile() {
    return window.matchMedia && window.matchMedia('(max-width: 768px)').matches;
  }

  function isTagPage() {
    try {
      var cur = app.current;
      if (cur && cur.routeName && cur.routeName.indexOf('tag') === 0) return true;
    } catch(e) {}
    return /^\/t\/.+/.test(location.pathname || '');
  }

  function buildSlider() {
    var enabled = app.forum.attribute('cosmosSlider_enabled');
    if (!enabled || enabled === '0' || enabled === 0) return null;

    var disableMobile = app.forum.attribute('cosmosSlider_disableMobile');
    if ((disableMobile === 1 || disableMobile === '1') && isMobile()) return null;

    var hideOnTagPages = app.forum.attribute('cosmosSlider_hideOnTagPages');
    if ((hideOnTagPages === 1 || hideOnTagPages === '1') && isTagPage()) return null;

    var raw = app.forum.attribute('cosmosSlider_slides') || '[]';
    var slides = [];
    try { slides = JSON.parse(raw); } catch(e) {}
    slides = slides.filter(function(s) { return s && s.image; });
    if (!slides.length) return null;

    var heightDesktop = parseInt(app.forum.attribute('cosmosSlider_heightDesktop') || 320, 10);
    var heightMobile  = parseInt(app.forum.attribute('cosmosSlider_heightMobile')  || 200, 10);
    var autoplayMs    = parseInt(app.forum.attribute('cosmosSlider_autoplay')       || 0,   10) * 1000;

    return m(CosmosSlider, {
      slides:        slides,
      heightDesktop: heightDesktop,
      heightMobile:  heightMobile,
      autoplayMs:    autoplayMs,
    });
  }

  override(IndexPage.prototype, 'hero', function(original) {
    return buildSlider() || original();
  });
});

// ---------------------------------------------------------------------------
// CHRISTMAS LIGHTS — coloured bulbs on a wire along the bottom of the header
// ---------------------------------------------------------------------------
app.initializers.add('resofire-cosmos-theme-lights', function() {
  function _run() { cosmosLightsInit(); }
  if (app.booted && typeof app.booted.then === 'function') {
    app.booted.then(_run, _run);
  } else {
    setTimeout(_run, 0);
  }
  function cosmosLightsInit() {
  if (!app.forum) return;

  function forumAttr(key) {
    try { return app.forum.attribute(key); } catch(e) { return undefined; }
  }

  function lightsActive() {
    var auto = forumAttr('cosmosHoliday_hatsAuto');
    if (auto === 1 || auto === '1') {
      var m = new Date().getMonth() + 1, d = new Date().getDate();
      if (m === 12 || (m === 1 && d <= 6)) return true;
      return false;
    }
    var v = forumAttr('cosmosHoliday_lights');
    return v === 1 || v === '1';
  }

  if (!lightsActive()) return;

  var COLORS = ['#e74c3c','#f39c12','#2ecc71','#3498db','#9b59b6','#e67e22','#1abc9c'];
  var SPACING = 38;
  var _strip = null;

  function buildStrip() {
    if (_strip) return;
    var header = document.querySelector('.App-header');
    if (!header) return;

    // Ensure header is a positioning context
    var hStyle = window.getComputedStyle(header);
    if (hStyle.position === 'static') header.style.position = 'relative';
    header.style.overflow = 'visible';

    _strip = document.createElement('div');
    _strip.id = 'CosmosLightsStrip';
    _strip.setAttribute('aria-hidden', 'true');
    _strip.style.cssText = [
      'position:absolute',
      'bottom:-28px',
      'left:0',
      'right:0',
      'height:28px',
      'pointer-events:none',
      'z-index:3',
      'overflow:visible',
    ].join(';');

    // Wire
    var wire = document.createElement('div');
    wire.style.cssText = 'position:absolute;top:6px;left:0;right:0;height:2px;background:rgba(255,255,255,0.18);';
    _strip.appendChild(wire);

    // Inject keyframe CSS once
    if (!document.getElementById('cosmosLightKF')) {
      var kf = document.createElement('style');
      kf.id = 'cosmosLightKF';
      kf.textContent = [
        '@keyframes cLightA{0%,100%{opacity:1}50%{opacity:0.45}}',
        '@keyframes cLightB{0%,100%{opacity:0.55}50%{opacity:1}}',
        '@keyframes cLightC{0%,100%{opacity:0.8}33%{opacity:0.35}66%{opacity:1}}',
      ].join('');
      document.head.appendChild(kf);
    }

    var anims = ['cLightA','cLightB','cLightC'];

    function populate() {
      // Remove old bulbs (not the wire)
      var old = _strip.querySelectorAll('.cosmos-bulb');
      old.forEach(function(b) { b.remove(); });

      var w = _strip.offsetWidth || window.innerWidth;
      var count = Math.floor(w / SPACING);
      for (var i = 0; i < count; i++) {
        var col = COLORS[i % COLORS.length];
        var dur = (1.2 + (i % 7) * 0.25).toFixed(2);
        var delay = (i * 0.11).toFixed(2);
        var anim = anims[i % 3];

        var bulb = document.createElement('div');
        bulb.className = 'cosmos-bulb';
        bulb.style.cssText = [
          'position:absolute',
          'left:' + (i * SPACING + 10) + 'px',
          'top:0',
          'display:flex',
          'flex-direction:column',
          'align-items:center',
        ].join(';');

        // Cord
        var cord = document.createElement('div');
        cord.style.cssText = 'width:1.5px;height:6px;background:rgba(255,255,255,0.22);';
        bulb.appendChild(cord);

        // Globe
        var globe = document.createElement('div');
        globe.style.cssText = [
          'width:10px',
          'height:13px',
          'border-radius:50% 50% 50% 50% / 40% 40% 60% 60%',
          'background:' + col,
          'box-shadow:0 0 7px 3px ' + col + 'aa',
          'animation:' + anim + ' ' + dur + 's ease-in-out ' + delay + 's infinite',
        ].join(';');
        bulb.appendChild(globe);

        // Cap
        var cap = document.createElement('div');
        cap.style.cssText = 'width:6px;height:3px;background:rgba(255,255,255,0.28);border-radius:2px 2px 0 0;margin-top:1px;';
        bulb.appendChild(cap);

        _strip.appendChild(bulb);
      }
    }

    header.appendChild(_strip);
    populate();
    window.addEventListener('resize', populate, { passive: true });
  }

  // Hook into page lifecycle — header may not exist until page renders
  var Page = flarum.core.compat['common/components/Page'];
  extend(Page.prototype, 'oncreate', function() { buildStrip(); });
  extend(Page.prototype, 'onupdate', function() { buildStrip(); });
  requestAnimationFrame(buildStrip);
  } // end cosmosLightsInit
});

// ---------------------------------------------------------------------------
// CHRISTMAS SNOW — light dusting of falling snowflakes
// ---------------------------------------------------------------------------
app.initializers.add('resofire-cosmos-theme-snow', function() {
  function _run() { cosmosSnowInit(); }
  if (app.booted && typeof app.booted.then === 'function') {
    app.booted.then(_run, _run);
  } else {
    setTimeout(_run, 0);
  }
  function cosmosSnowInit() {
  if (!app.forum) return;

  function forumAttr(key) {
    try { return app.forum.attribute(key); } catch(e) { return undefined; }
  }

  function snowActive() {
    var auto = forumAttr('cosmosHoliday_hatsAuto');
    if (auto === 1 || auto === '1') {
      var m = new Date().getMonth() + 1, d = new Date().getDate();
      if (m === 12 || (m === 1 && d <= 6)) return true;
      return false;
    }
    var v = forumAttr('cosmosHoliday_snow');
    return v === 1 || v === '1';
  }

  if (!snowActive()) return;

  // Config — light dusting
  var CFG = { count: 35, minR: 1.5, maxR: 3, minV: 0.3, maxV: 0.8, drift: 0.4 };

  var canvas = document.createElement('canvas');
  canvas.id = 'CosmosSnowCanvas';
  canvas.setAttribute('aria-hidden', 'true');
  canvas.style.cssText = [
    'position:fixed',
    'top:0','left:0',
    'width:100%','height:100%',
    'pointer-events:none',
    'z-index:1',         // below everything, just above base background
  ].join(';');
  document.body.appendChild(canvas);

  var ctx = canvas.getContext('2d');
  var flakes = [];
  var W, H, af;

  function resize() {
    W = window.innerWidth;
    H = window.innerHeight;
    canvas.width  = W;
    canvas.height = H;
  }

  function makeFlake(scatter) {
    var r = CFG.minR + Math.random() * (CFG.maxR - CFG.minR);
    return {
      x: Math.random() * W,
      y: scatter ? Math.random() * H : -r * 2,
      r: r,
      v: CFG.minV + Math.random() * (CFG.maxV - CFG.minV),
      drift: (Math.random() - 0.5) * CFG.drift,
      wobble: Math.random() * Math.PI * 2,
      wobbleSpeed: 0.008 + Math.random() * 0.015,
      opacity: 0.35 + Math.random() * 0.55,
    };
  }

  function init() {
    resize();
    flakes = [];
    for (var i = 0; i < CFG.count; i++) flakes.push(makeFlake(true));
  }

  function tick() {
    ctx.clearRect(0, 0, W, H);
    for (var i = 0; i < flakes.length; i++) {
      var f = flakes[i];
      f.wobble += f.wobbleSpeed;
      f.x += f.drift + Math.sin(f.wobble) * 0.3;
      f.y += f.v;
      if (f.y > H + f.r * 2) { flakes[i] = makeFlake(false); continue; }
      if (f.x < -f.r * 2 || f.x > W + f.r * 2) { flakes[i] = makeFlake(false); continue; }
      ctx.beginPath();
      ctx.arc(f.x, f.y, f.r, 0, Math.PI * 2);
      // Check Cosmos day/night via the injected style tag media attribute
      var dayStyle = document.getElementById('cosmos-day-css');
      var isDay = dayStyle && dayStyle.media === 'all';
      ctx.fillStyle = isDay
        ? 'rgba(10,20,45,' + f.opacity + ')'
        : 'rgba(255,255,255,' + f.opacity + ')';
      ctx.fill();
    }
    af = requestAnimationFrame(tick);
  }

  window.addEventListener('resize', function() { resize(); }, { passive: true });
  init();
  tick();
  } // end cosmosSnowInit
});

// ---------------------------------------------------------------------------
// CHRISTMAS GIFT HUNT
// ---------------------------------------------------------------------------
// Tree: fixed bottom-right, hidden on mobile.
// 12 placement spots defined; 8 active per day (seeded by date so all users
// see the same 8 spots on the same day).
// Progress stored in localStorage keyed by date + userId.
// Admin notification fired server-side on first-ever completion per season.
// ---------------------------------------------------------------------------

app.initializers.add('resofire-cosmos-theme-gifthunt', function() {
  function _run() { cosmosGiftHuntInit(); }
  if (app.booted && typeof app.booted.then === 'function') {
    app.booted.then(_run, _run);
  } else {
    setTimeout(_run, 0);
  }

  function cosmosGiftHuntInit() {
    if (!app.forum) return;

    function forumAttr(key) {
      try { return app.forum.attribute(key); } catch(e) { return undefined; }
    }

    function giftHuntActive() {
      var auto = forumAttr('cosmosHoliday_hatsAuto');
      if (auto === 1 || auto === '1') {
        var mo = new Date().getMonth() + 1, d = new Date().getDate();
        if (mo === 12 || (mo === 1 && d <= 6)) return true;
        return false;
      }
      var v = forumAttr('cosmosHoliday_gifts');
      return v === 1 || v === '1';
    }

    if (!giftHuntActive()) return;

    // Hide on mobile — tree has no good placement and gifts would be too cramped
    if (window.matchMedia && window.matchMedia('(max-width: 767px)').matches) return;

    var TOTAL_GIFTS = 8;
    var PALETTE = [
      { box:'#c0392b', ribbon:'#f1c40f', bow:'#f39c12', stripe:'#e74c3c' },
      { box:'#2980b9', ribbon:'#e74c3c', bow:'#c0392b', stripe:'#3498db' },
      { box:'#27ae60', ribbon:'#f1c40f', bow:'#f39c12', stripe:'#2ecc71' },
      { box:'#8e44ad', ribbon:'#69c6b9', bow:'#16a085', stripe:'#9b59b6' },
      { box:'#d35400', ribbon:'#ffffff', bow:'#ecf0f1', stripe:'#e67e22' },
      { box:'#c0392b', ribbon:'#69c6b9', bow:'#16a085', stripe:'#e74c3c' },
      { box:'#2c3e50', ribbon:'#f1c40f', bow:'#f39c12', stripe:'#34495e' },
      { box:'#16a085', ribbon:'#e74c3c', bow:'#c0392b', stripe:'#1abc9c' },
    ];

    // ── SVG builders ────────────────────────────────────────────────────

    function giftSVG(p, size) {
      var s = size || 28;
      // Unique clip id per palette to avoid SVG id collisions in document
      var clipId = 'gc' + p.box.replace('#','') + s;
      return '<svg width="' + s + '" height="' + s + '" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">'
        + '<rect x="8" y="16" width="32" height="30" rx="3" fill="' + p.box + '"/>'
        + '<clipPath id="' + clipId + '"><rect x="8" y="16" width="32" height="30" rx="3"/></clipPath>'
        + '<g clip-path="url(#' + clipId + ')">'
        + '<path d="M0 30 L20 10 L26 10 L6 30Z" fill="rgba(255,255,255,0.12)"/>'
        + '<path d="M16 46 L36 26 L42 26 L22 46Z" fill="rgba(255,255,255,0.12)"/>'
        + '</g>'
        + '<rect x="21" y="16" width="6" height="30" fill="' + p.ribbon + '"/>'
        + '<rect x="8" y="26" width="32" height="5" fill="' + p.ribbon + '"/>'
        + '<rect x="8" y="12" width="32" height="6" rx="2" fill="' + p.stripe + '"/>'
        + '<path d="M18 12 Q16 5 20 4 Q22 8 24 12Z" fill="' + p.bow + '"/>'
        + '<path d="M30 12 Q32 5 28 4 Q26 8 24 12Z" fill="' + p.bow + '"/>'
        + '<circle cx="24" cy="12" r="3" fill="' + p.ribbon + '"/>'
        + '<rect x="8" y="16" width="32" height="30" rx="3" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="1"/>'
        + '</svg>';
    }

    function treeSVG(found, glowing) {
      var total = TOTAL_GIFTS;
      var starColor = found >= total ? '#f1c40f' : 'rgba(255,255,255,0.3)';
      var fillAlpha = found >= total ? '0.16' : '0.08';
      var glowFilter = glowing ? ' filter:drop-shadow(0 0 10px #69c6b9);' : '';
      var giftsHtml = '';
      for (var i = 0; i < Math.min(found, 8); i++) {
        var gx = 8 + (i % 4) * 28;
        var gy = 108 - Math.floor(i / 4) * 20;
        giftsHtml += '<g transform="translate(' + gx + ',' + gy + ') scale(0.38)">'
          + giftSVG(PALETTE[i], 48)
          + '</g>';
      }
      return '<svg width="130" height="140" viewBox="0 0 130 140" xmlns="http://www.w3.org/2000/svg"'
        + ' style="' + glowFilter + '">'
        + '<polygon points="65,14 20,62 45,62 22,90 45,90 14,124 116,124 95,90 118,90 95,62 110,62"'
        + ' fill="rgba(105,198,185,' + fillAlpha + ')" stroke="#69c6b9" stroke-width="2" stroke-linejoin="round"/>'
        + '<line x1="65" y1="14" x2="65" y2="124" stroke="#69c6b9" stroke-width="1" opacity="0.15"/>'
        + '<circle cx="45" cy="72" r="3.5" fill="#69c6b9" opacity="0.8"/>'
        + '<circle cx="65" cy="66" r="3" fill="#e74c3c" opacity="0.9"/>'
        + '<circle cx="85" cy="72" r="3.5" fill="#f1c40f" opacity="0.8"/>'
        + '<circle cx="37" cy="96" r="3" fill="#f1c40f" opacity="0.8"/>'
        + '<circle cx="65" cy="92" r="3.5" fill="#69c6b9" opacity="0.9"/>'
        + '<circle cx="93" cy="96" r="3" fill="#e74c3c" opacity="0.8"/>'
        + '<polygon points="65,7 68,17 58,17" fill="' + starColor + '"/>'
        + '<circle cx="65" cy="9" r="5" fill="' + starColor + '" '
        + (found >= total ? 'style="animation:cosmosStarPulse 1.5s ease-in-out infinite"' : '') + '/>'
        + '<rect x="57" y="124" width="16" height="12" rx="2" fill="rgba(105,198,185,0.25)" stroke="#69c6b9" stroke-width="1"/>'
        + giftsHtml
        + '<text x="65" y="138" text-anchor="middle" font-size="7.5" fill="rgba(255,255,255,'
        + (found >= total ? '0.7' : '0.4') + ')" font-family="sans-serif">' + found + '/' + total + '</text>'
        + '</svg>';
    }

    // ── Keyframes ────────────────────────────────────────────────────────
    if (!document.getElementById('cosmosGiftKF')) {
      var kf = document.createElement('style');
      kf.id = 'cosmosGiftKF';
      kf.textContent = [
        '@keyframes cosmosStarPulse{0%,100%{filter:drop-shadow(0 0 3px #f1c40f)}',
        '50%{filter:drop-shadow(0 0 14px #f1c40f) drop-shadow(0 0 28px #f1c40f88)}}',
        '@keyframes cosmosGiftWiggle{0%,100%{transform:rotate(-3deg) scale(1)}',
        '50%{transform:rotate(3deg) scale(1.08)}}',
        '@keyframes cosmosGiftAppear{from{transform:translateY(-16px) scale(0.5);opacity:0}',
        '60%{transform:translateY(4px) scale(1.05);opacity:1}',
        'to{transform:translateY(0) scale(1);opacity:1}}',
        '@keyframes cosmosGiftHide{to{transform:scale(0);opacity:0}}',
      ].join('');
      document.head.appendChild(kf);
    }

    // ── Date seed — pick 8 of 12 spots, consistent for all users today ───
    function todaySeed() {
      var d = new Date();
      return d.getFullYear() * 10000 + (d.getMonth() + 1) * 100 + d.getDate();
    }

    function seededRandom(seed) {
      // Simple LCG — good enough for picking spots
      var x = Math.sin(seed) * 10000;
      return x - Math.floor(x);
    }

    function pickSpots() {
      var seed = todaySeed();
      var indices = [0,1,2,3,4,5,6,7,8,9,10,11];
      // Fisher-Yates shuffle with seed
      for (var i = indices.length - 1; i > 0; i--) {
        var j = Math.floor(seededRandom(seed + i) * (i + 1));
        var tmp = indices[i]; indices[i] = indices[j]; indices[j] = tmp;
      }
      return indices.slice(0, TOTAL_GIFTS);
    }

    // ── localStorage progress ─────────────────────────────────────────────
    var LS_KEY_PREFIX = 'cosmos_gifts_';

    function todayKey() {
      var d = new Date();
      var userId = (app.session && app.session.user) ? app.session.user.id() : 'guest';
      return LS_KEY_PREFIX
        + d.getFullYear() + '-'
        + String(d.getMonth() + 1).padStart(2, '0') + '-'
        + String(d.getDate()).padStart(2, '0') + '_'
        + userId;
    }

    function loadProgress() {
      try {
        var raw = localStorage.getItem(todayKey());
        return raw ? JSON.parse(raw) : [];
      } catch(e) { return []; }
    }

    function saveProgress(found) {
      try { localStorage.setItem(todayKey(), JSON.stringify(found)); } catch(e) {}
    }

    // ── State ──────────────────────────────────────────────────────────────
    var activeSpots  = pickSpots();   // indices into SPOTS array for today
    var foundSpots   = loadProgress(); // array of activeSpots indices already found
    var giftElements = {};             // spotIndex -> DOM element
    var celebrating  = false;
    var treeEl       = null;
    var treeMounted  = false;

    // ── 12 placement definitions ──────────────────────────────────────────
    // Each defines: selector (anchor element), position styles, and
    // an optional check function so we skip if the element doesn't exist.
    var SPOTS = [
      // 0: Bottom of sidebar nav
      {
        sel: '.IndexPage-nav',
        css: 'bottom:-6px;right:-8px;',
        title: 'A hidden gift in the sidebar!',
      },
      // 1: Hero banner corner
      {
        sel: '.WelcomeHero',
        css: 'bottom:8px;right:12px;',
        title: 'A hidden gift in the welcome banner!',
      },
      // 2: Header bar — right side
      {
        sel: '.App-header',
        css: 'bottom:-10px;left:24px;',
        title: 'A hidden gift by the header!',
      },
      // 3: Discussion list — bottom-right edge
      {
        sel: '.DiscussionList',
        css: 'bottom:4px;right:-10px;',
        title: 'A hidden gift by the discussion list!',
      },
      // 4: Side nav (always present on index/tag pages)
      {
        sel: '.sideNav',
        css: 'bottom:8px;right:-10px;',
        title: 'A hidden gift in the sidebar!',
      },
      // 5: Post actions area — first post on page
      {
        sel: '.Post-actions',
        css: 'top:-8px;right:4px;',
        title: 'A hidden gift near the post actions!',
        first: true,
      },
      // 6: Reply button area — first occurrence
      {
        sel: '.item-reply',
        css: 'top:-10px;left:-8px;',
        title: 'A hidden gift near the reply button!',
        first: true,
      },
      // 7: Profile hero — only on user profile pages
      {
        sel: '.UserCard.UserHero',
        css: 'bottom:12px;right:16px;',
        title: 'A hidden gift in the profile!',
      },
      // 8: Inside a post body — second post if available
      {
        sel: '.Post-body',
        css: 'bottom:4px;right:4px;',
        title: 'A hidden gift inside a post!',
        nth: 2,
      },
      // 9: Search area
      {
        sel: '.Search',
        css: 'bottom:-10px;right:0px;',
        title: 'A hidden gift by the search bar!',
      },
      // 10: Inside a discussion list row — 3rd row if available
      {
        sel: '.DiscussionListItem',
        css: 'top:4px;left:-10px;',
        title: 'A hidden gift in the discussion list!',
        nth: 3,
      },
      // 11: Composer — appears when writing a reply or new discussion
      {
        sel: '.Composer',
        css: 'top:-10px;right:48px;',
        title: 'A hidden gift in the composer!',
      },
    ];

    // ── Build and place a gift element ─────────────────────────────────────
    function placeGift(spotIndex) {
      var spot    = SPOTS[activeSpots[spotIndex]];
      var palIdx  = activeSpots[spotIndex] % PALETTE.length;
      var pal     = PALETTE[palIdx];
      var anchor  = null;

      var all = document.querySelectorAll(spot.sel);
      if (!all || !all.length) return null;

      if (spot.first) {
        anchor = all[0];
      } else if (spot.nth) {
        anchor = all[spot.nth - 1] || all[all.length - 1];
      } else {
        anchor = all[0];
      }

      if (!anchor) return null;

      // Ensure the anchor is a positioning context
      var pos = window.getComputedStyle(anchor).position;
      if (pos === 'static') anchor.style.position = 'relative';

      var el = document.createElement('div');
      el.className = 'CosmosGift';
      el.setAttribute('aria-label', spot.title);
      el.setAttribute('title', spot.title);
      el.style.cssText = [
        'position:absolute',
        spot.css,
        'z-index:50',
        'cursor:pointer',
        'pointer-events:auto',
        'animation:cosmosGiftWiggle 2.5s ease-in-out infinite',
        'animation-delay:' + (spotIndex * 0.4) + 's',
        'filter:drop-shadow(0 2px 6px rgba(0,0,0,0.5))',
      ].join(';');
      el.innerHTML = giftSVG(pal, 28);

      el.addEventListener('click', function(e) {
        e.stopPropagation();
        collectGift(spotIndex, el);
      });

      anchor.appendChild(el);
      return el;
    }

    // ── Collect a gift ─────────────────────────────────────────────────────
    function collectGift(spotIndex, el) {
      if (foundSpots.indexOf(spotIndex) !== -1) return; // already found

      // Animate out
      el.style.animation = 'cosmosGiftHide 0.35s ease-in forwards';
      setTimeout(function() {
        if (el.parentNode) el.parentNode.removeChild(el);
        delete giftElements[spotIndex];
      }, 360);

      foundSpots.push(spotIndex);
      saveProgress(foundSpots);
      updateTree(false);

      // Small sparkle burst at click position
      spawnCollectBurst(el);

      // Check completion
      if (foundSpots.length >= TOTAL_GIFTS && !celebrating) {
        celebrating = true;
        setTimeout(function() { triggerCelebration(); }, 400);
        // Fire server notification (first-ever completion this season)
        if (app.session && app.session.user && !app.session.user.isGuest) {
          app.request({
            method: 'POST',
            url: app.forum.attribute('apiUrl') + '/resofire/cosmos/gift-complete',
            body: {},
          }).catch(function() {
            // Fail silently — celebration fires regardless
          });
        }
      }
    }

    // ── Particle burst on collect ──────────────────────────────────────────
    function spawnCollectBurst(el) {
      var r   = el.getBoundingClientRect();
      var cx  = r.left + r.width / 2;
      var cy  = r.top  + r.height / 2;
      var cols = ['#69c6b9','#e74c3c','#f1c40f','#fff','#9b59b6'];

      for (var i = 0; i < 10; i++) {
        var p = document.createElement('div');
        var angle = (i / 10) * Math.PI * 2;
        var dist  = 22 + Math.random() * 18;
        var size  = 4 + Math.random() * 4;
        p.style.cssText = [
          'position:fixed',
          'left:' + cx + 'px',
          'top:'  + cy + 'px',
          'width:'  + size + 'px',
          'height:' + size + 'px',
          'border-radius:50%',
          'background:' + cols[i % cols.length],
          'pointer-events:none',
          'z-index:9998',
          'transition:all 0.5s ease-out',
          'opacity:1',
        ].join(';');
        document.body.appendChild(p);
        requestAnimationFrame(function(pp, a, d) {
          return function() {
            pp.style.left    = (cx + Math.cos(a) * d) + 'px';
            pp.style.top     = (cy + Math.sin(a) * d) + 'px';
            pp.style.opacity = '0';
            setTimeout(function() { if (pp.parentNode) pp.parentNode.removeChild(pp); }, 520);
          };
        }(p, angle, dist));
      }
    }

    // ── Update tree display ────────────────────────────────────────────────
    function updateTree(glowing) {
      if (!treeEl) return;
      var inner = treeEl.querySelector('#CosmosTreeInner');
      if (inner) inner.innerHTML = treeSVG(foundSpots.length, glowing);
    }

    // ── Celebration ────────────────────────────────────────────────────────
    function triggerCelebration() {
      updateTree(true);

      // Confetti canvas
      var canvas = document.createElement('canvas');
      canvas.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:9997;';
      canvas.width  = window.innerWidth;
      canvas.height = window.innerHeight;
      document.body.appendChild(canvas);
      var ctx    = canvas.getContext('2d');
      var pieces = [];
      var cols   = ['#69c6b9','#e74c3c','#f1c40f','#fff','#9b59b6','#3498db','#2ecc71'];

      // Spawn confetti from tree position
      var treeBounds = treeEl.getBoundingClientRect();
      var tx = treeBounds.left + treeBounds.width / 2;
      var ty = treeBounds.top  + treeBounds.height * 0.15; // near star

      for (var i = 0; i < 120; i++) {
        pieces.push({
          x:   tx + (Math.random() - 0.5) * 60,
          y:   ty,
          vx:  (Math.random() - 0.5) * 7,
          vy:  -3 - Math.random() * 7,
          g:   0.2,
          rot: Math.random() * Math.PI * 2,
          rs:  (Math.random() - 0.5) * 0.18,
          w:   5 + Math.random() * 7,
          h:   3 + Math.random() * 4,
          col: cols[Math.floor(Math.random() * cols.length)],
          life: 1,
          dec: 0.007 + Math.random() * 0.005,
        });
      }

      function drawConfetti() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        pieces = pieces.filter(function(p) { return p.life > 0; });
        pieces.forEach(function(p) {
          p.vy  += p.g;
          p.x   += p.vx;
          p.y   += p.vy;
          p.rot += p.rs;
          p.life -= p.dec;
          ctx.save();
          ctx.translate(p.x, p.y);
          ctx.rotate(p.rot);
          ctx.globalAlpha = Math.max(0, p.life);
          ctx.fillStyle = p.col;
          ctx.fillRect(-p.w/2, -p.h/2, p.w, p.h);
          ctx.restore();
        });
        if (pieces.length > 0) {
          requestAnimationFrame(drawConfetti);
        } else {
          ctx.clearRect(0, 0, canvas.width, canvas.height);
          if (canvas.parentNode) canvas.parentNode.removeChild(canvas);
        }
      }
      requestAnimationFrame(drawConfetti);

      // Celebration message
      var msg = document.createElement('div');
      msg.id = 'CosmosGiftMsg';
      msg.style.cssText = [
        'position:fixed',
        'bottom:170px',
        'right:20px',
        'background:rgba(13,18,32,0.96)',
        'border:1px solid rgba(105,198,185,0.45)',
        'border-radius:10px',
        'padding:14px 18px',
        'z-index:9999',
        'pointer-events:none',
        'opacity:0',
        'transform:translateY(10px)',
        'transition:opacity 0.4s ease,transform 0.4s ease',
        'box-shadow:0 0 24px rgba(105,198,185,0.18)',
        'text-align:center',
        'max-width:220px',
      ].join(';');
      msg.innerHTML = '<div style="font-size:15px;font-weight:600;color:#69c6b9;margin-bottom:5px;">'
        + '\uD83C\uDF84 All gifts found!</div>'
        + '<div style="font-size:12px;color:rgba(255,255,255,0.5);">'
        + 'You found all 8 hidden gifts today</div>';
      document.body.appendChild(msg);

      // Animate message in
      requestAnimationFrame(function() {
        requestAnimationFrame(function() {
          msg.style.opacity = '1';
          msg.style.transform = 'translateY(0)';
        });
      });

      // Auto-dismiss after 5 seconds
      setTimeout(function() {
        msg.style.opacity = '0';
        msg.style.transform = 'translateY(10px)';
        setTimeout(function() {
          if (msg.parentNode) msg.parentNode.removeChild(msg);
        }, 420);
      }, 5000);
    }

    // ── Mount tree ────────────────────────────────────────────────────────
    function mountTree() {
      if (treeMounted) return;
      treeMounted = true;

      treeEl = document.createElement('div');
      treeEl.id = 'CosmosTree';
      treeEl.style.cssText = [
        'position:fixed',
        'bottom:0',
        'right:16px',
        'z-index:40',
        'pointer-events:none',
        'width:130px',
      ].join(';');
      treeEl.innerHTML = '<div id="CosmosTreeInner">' + treeSVG(foundSpots.length, false) + '</div>';
      document.body.appendChild(treeEl);
    }

    // ── Place all active gifts ─────────────────────────────────────────────
    function placeAllGifts() {
      for (var i = 0; i < TOTAL_GIFTS; i++) {
        if (foundSpots.indexOf(i) !== -1) continue; // already found today
        // Check element is still in the live DOM — Mithril navigation
        // removes page content but giftElements{} keeps stale references.
        if (giftElements[i] && document.body.contains(giftElements[i])) continue;
        if (giftElements[i]) delete giftElements[i]; // stale — clear it
        var el = placeGift(i);
        if (el) giftElements[i] = el;
      }
    }

    // ── Hook into page lifecycle ───────────────────────────────────────────
    extend(Page.prototype, 'oncreate', function() {
      requestAnimationFrame(function() {
        mountTree();
        placeAllGifts();
      });
    });
    extend(Page.prototype, 'onupdate', function() {
      requestAnimationFrame(function() {
        mountTree();
        placeAllGifts();
      });
    });

    // Initial mount
    requestAnimationFrame(function() {
      mountTree();
      placeAllGifts();
    });

  } // end cosmosGiftHuntInit
});


// ---------------------------------------------------------------------------
// GIFT HUNT NOTIFICATION COMPONENT
// ---------------------------------------------------------------------------
app.initializers.add('resofire-cosmos-theme-gifthunt-notif', function() {
  function _run() {
    if (!app.notificationComponents) return;
    app.notificationComponents['cosmosGiftHuntComplete'] = {
      view: function(vnode) {
        var n    = vnode.attrs.notification;
        var user = n.fromUser();
        var name = user
          ? (user.displayName ? user.displayName() : user.username())
          : 'A member';
        var href = user ? app.route('user', { username: user.slug() }) : '#';
        return m('a.NotificationList-item', {
          href: href,
          style: 'display:flex;align-items:center;gap:8px;padding:10px 14px;text-decoration:none;color:inherit;'
        }, [
          m('span', { style: 'font-size:18px;flex-shrink:0;' }, '\uD83C\uDF84'),
          m('span', { style: 'font-size:13px;' }, name + ' found all 8 hidden gifts!'),
        ]);
      },
    };
  }
  if (app.booted && typeof app.booted.then === 'function') {
    app.booted.then(_run, _run);
  } else {
    setTimeout(_run, 0);
  }
});



// ===========================================================================
// EASTER — BUNNY EARS
// ===========================================================================
app.initializers.add('resofire-cosmos-theme-ears', function() {
  function _run() { cosmosEarsInit(); }
  if (app.booted && typeof app.booted.then === 'function') {
    app.booted.then(_run, _run);
  } else {
    setTimeout(_run, 0);
  }
  function cosmosEarsInit() {
  if (!app.forum) return;
  var forum = app.forum;

  // ── Active check ──────────────────────────────────────────────────────────
  function earsActive() {
    var on = parseInt(forum.attribute('cosmosEaster_ears'), 10);
    if (!on) return false;
    var start = forum.attribute('cosmosEaster_start') || '';
    var end   = forum.attribute('cosmosEaster_end')   || '';
    if (!start && !end) return true;
    var today = new Date().toISOString().slice(0, 10);
    if (start && today < start) return false;
    if (end   && today > end)   return false;
    return true;
  }

  if (!earsActive()) return;

  var EAR_SIZE = parseInt(forum.attribute('cosmosEaster_earSize'), 10) || 100;
  var EAR_TOP  = parseInt(forum.attribute('cosmosEaster_earTop'),  10) || -90;

  // ── Get forum primary colour from CSS vars ────────────────────────────────
  function getPrimary() {
    return getComputedStyle(document.documentElement)
      .getPropertyValue('--primary-color').trim()
      || getComputedStyle(document.documentElement)
         .getPropertyValue('--primary').trim()
      || '#1D9E75';
  }

  // ── Build the ears SVG ────────────────────────────────────────────────────
  // Two elliptical ears, centred on the avatar.
  // The SVG is sized to (avPx * size/100) wide and tall.
  // Ears are centred horizontally, sitting above the avatar top.
  function buildEarsSvg(avPx) {
    var scale   = EAR_SIZE / 100;
    // Ear dimensions relative to avatar size
    var earW    = avPx * 0.28 * scale;   // outer rx
    var earH    = avPx * 0.45 * scale;   // outer ry
    var innerW  = earW * 0.55;
    var innerH  = earH * 0.62;
    // SVG canvas: wide enough for both ears with spacing, tall enough for ear height
    var spread  = avPx * 0.22 * scale;   // horizontal distance from centre to ear centre
    var svgW    = avPx;
    var svgH    = earH * 2 + 4;
    var cy      = earH + 2;              // ear centre y within SVG
    var lx      = svgW / 2 - spread;    // left ear cx
    var rx      = svgW / 2 + spread;    // right ear cx
    var primary = getPrimary();

    return '<svg xmlns="http://www.w3.org/2000/svg"'
      + ' width="' + svgW + '" height="' + svgH + '"'
      + ' viewBox="0 0 ' + svgW + ' ' + svgH + '"'
      + ' style="overflow:visible">'
      // left outer ear
      + '<ellipse cx="' + lx + '" cy="' + cy + '" rx="' + earW + '" ry="' + earH + '"'
      + ' fill="' + primary + '" fill-opacity="0.12" stroke="' + primary + '" stroke-width="1.6"/>'
      // left inner ear (hardcoded pink)
      + '<ellipse cx="' + lx + '" cy="' + (cy + earH * 0.08) + '" rx="' + innerW + '" ry="' + innerH + '"'
      + ' fill="rgba(212,83,126,0.32)" stroke="#D4537E" stroke-width="1"/>'
      // right outer ear
      + '<ellipse cx="' + rx + '" cy="' + cy + '" rx="' + earW + '" ry="' + earH + '"'
      + ' fill="' + primary + '" fill-opacity="0.12" stroke="' + primary + '" stroke-width="1.6"/>'
      // right inner ear
      + '<ellipse cx="' + rx + '" cy="' + (cy + earH * 0.08) + '" rx="' + innerW + '" ry="' + innerH + '"'
      + ' fill="rgba(212,83,126,0.32)" stroke="#D4537E" stroke-width="1"/>'
      + '</svg>';
  }

  // ── Exclusion list ────────────────────────────────────────────────────────
  var EXCLUDE = [
    '.SessionDropdown', '.MobileTab', '.NotificationList',
    '.Search-results', '.Post-reactions', '.DiscussionParticipants-avatar',
    '.ParticipantsModal-item', '.TextEditor-controls', '.UserMention-avatar',
    '.UserCard--popover',
  ];
  function isExcluded(el) {
    return EXCLUDE.some(function(sel) {
      return el.closest(sel);
    });
  }

  // ── Resolve avatar pixel size ─────────────────────────────────────────────
  function resolveSize(el) {
    if (el.offsetWidth > 0) return el.offsetWidth;
    var s = getComputedStyle(el).getPropertyValue('--size');
    if (s) { var v = parseFloat(s); if (v > 0) return v; }
    var w = parseFloat(getComputedStyle(el).width);
    return isNaN(w) ? 0 : w;
  }

  // ── Attach ears via wrapper (all non-PostUser avatars) ────────────────────
  function attachEarsWrapper(avEl) {
    if (avEl.dataset.cosmosEars) return;
    avEl.dataset.cosmosEars = '1';

    var avPx = resolveSize(avEl);
    if (avPx < 1) avPx = 36;

    var wrap = document.createElement('span');
    wrap.className = 'CosmosEarWrap';
    wrap.style.cssText = [
      'display:inline-block',
      'position:relative',
      'width:'  + avPx + 'px',
      'height:' + avPx + 'px',
      'vertical-align:top',
      'overflow:visible',
    ].join(';');

    avEl.parentNode.insertBefore(wrap, avEl);
    wrap.appendChild(avEl);

    var scale  = EAR_SIZE / 100;
    var earH   = avPx * 0.45 * scale;
    var svgH   = earH * 2 + 4;
    var topPx  = avPx * (EAR_TOP / 100);

    var div = document.createElement('span');
    div.className = 'CosmosEarSvg';
    div.style.cssText = [
      'position:absolute',
      'left:0',
      'top:' + topPx + 'px',
      'width:' + avPx + 'px',
      'height:' + svgH + 'px',
      'pointer-events:none',
      'z-index:10',
    ].join(';');
    div.innerHTML = buildEarsSvg(avPx);
    wrap.appendChild(div);
  }

  // ── Attach ears to PostUser-avatar (anchor to .Post block element) ────────
  function attachEarsPostUser(avEl) {
    if (avEl.dataset.cosmosEars) return;
    avEl.dataset.cosmosEars = '1';

    var post = avEl.closest('.Post');
    if (!post) return;
    post.style.overflow = 'visible';

    requestAnimationFrame(function() {
      var avPx = avEl.offsetWidth || resolveSize(avEl);
      if (avPx < 1) { setTimeout(function(){ attachEarsPostUser._place(avEl, post); }, 50); return; }

      // Walk offsetParent chain to get avatar position relative to .Post
      var left = 0, top = 0, el = avEl;
      while (el && el !== post) {
        left += el.offsetLeft;
        top  += el.offsetTop;
        el    = el.offsetParent;
      }

      var scale  = EAR_SIZE / 100;
      var earH   = avPx * 0.45 * scale;
      var svgH   = earH * 2 + 4;
      var topPx  = top + avPx * (EAR_TOP / 100);

      var div = document.createElement('span');
      div.className = 'CosmosEarSvg';
      div.style.cssText = [
        'position:absolute',
        'left:' + left + 'px',
        'top:'  + topPx + 'px',
        'width:' + avPx + 'px',
        'height:' + svgH + 'px',
        'pointer-events:none',
        'z-index:10',
      ].join(';');
      div.innerHTML = buildEarsSvg(avPx);
      post.appendChild(div);
    });
  }

  // ── Dispatcher ────────────────────────────────────────────────────────────
  function attachEars(avEl) {
    if (avEl.dataset.cosmosEars) return;
    if (isExcluded(avEl)) return;
    if (avEl.classList.contains('PostUser-avatar')) {
      attachEarsPostUser(avEl);
    } else {
      attachEarsWrapper(avEl);
    }
  }

  // ── DOM scan ──────────────────────────────────────────────────────────────
  function processAvatars(root) {
    root = root || document.body;
    root.querySelectorAll('.Avatar').forEach(attachEars);
  }

  // ── MutationObserver ──────────────────────────────────────────────────────
  var observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(m) {
      m.addedNodes.forEach(function(node) {
        if (node.nodeType !== 1) return;
        if (node.classList && node.classList.contains('Avatar')) {
          attachEars(node);
        } else {
          node.querySelectorAll && node.querySelectorAll('.Avatar').forEach(attachEars);
        }
      });
    });
  });

    processAvatars();
    observer.observe(document.body, { childList: true, subtree: true });
  } // end cosmosEarsInit
});


// ===========================================================================
// EASTER — PASTEL STREAMERS
// ===========================================================================
app.initializers.add('resofire-cosmos-theme-streamers', function() {
  function _run() { cosmosStreamersInit(); }
  if (app.booted && typeof app.booted.then === 'function') {
    app.booted.then(_run, _run);
  } else {
    setTimeout(_run, 0);
  }
  function cosmosStreamersInit() {
  if (!app.forum) return;
  var forum = app.forum;

  function streamersActive() {
    var on = parseInt(forum.attribute('cosmosEaster_streamers'), 10);
    if (!on) return false;
    var start = forum.attribute('cosmosEaster_start') || '';
    var end   = forum.attribute('cosmosEaster_end')   || '';
    if (!start && !end) return true;
    var today = new Date().toISOString().slice(0, 10);
    if (start && today < start) return false;
    if (end   && today > end)   return false;
    return true;
  }

  if (!streamersActive()) return;

  // Hardcoded pastel palette
  var COLOURS = ['#1D9E75', '#D4537E', '#7F77DD', '#BA7517'];

  function buildStreamers() {
    var header = document.querySelector('.App-header');
    if (!header) return;
    if (document.getElementById('cosmos-easter-streamer-strip')) return;

    var W = header.offsetWidth || window.innerWidth;
    var svgNs = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(svgNs, 'svg');
    svg.setAttribute('xmlns', svgNs);
    svg.setAttribute('width', '100%');
    svg.setAttribute('height', '180');
    svg.setAttribute('viewBox', '0 0 ' + W + ' 180');
    svg.style.cssText = 'position:absolute;top:100%;left:0;display:block;pointer-events:none;z-index:5;overflow:visible;';

    // Draw wire along top
    var wireSpacing = 22;
    var numRibbons  = Math.floor(W / wireSpacing);
    var wirePath    = 'M0,2';
    for (var i = 1; i <= numRibbons; i++) {
      var x  = i * wireSpacing;
      var dy = (i % 2 === 0) ? 10 : -2;
      wirePath += ' Q' + (x - wireSpacing / 2) + ',' + (4 + dy) + ' ' + x + ',2';
    }
    var wire = document.createElementNS(svgNs, 'path');
    wire.setAttribute('d', wirePath);
    wire.setAttribute('fill', 'none');
    wire.setAttribute('stroke', getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim() || '#1D9E75');
    wire.setAttribute('stroke-width', '0.9');
    wire.setAttribute('opacity', '0.5');
    svg.appendChild(wire);

    // Draw ribbons
    for (var j = 0; j < numRibbons; j++) {
      var x0     = (j + 0.5) * wireSpacing;
      var colour = COLOURS[j % COLOURS.length];
      var len    = 80 + Math.sin(j * 1.3) * 30;
      var sway   = (j % 2 === 0) ? 3 : -3;

      // Ribbon path: gentle S-curve
      var path = document.createElementNS(svgNs, 'path');
      var d    = 'M' + x0 + ',2'
        + ' Q' + (x0 + sway) + ',' + (len * 0.35)
        + ' ' + x0 + ',' + (len * 0.6)
        + ' Q' + (x0 - sway) + ',' + (len * 0.8)
        + ' ' + (x0 + sway * 0.5) + ',' + len;
      path.setAttribute('d', d);
      path.setAttribute('fill', 'none');
      path.setAttribute('stroke', colour);
      path.setAttribute('stroke-width', '2');
      path.setAttribute('stroke-linecap', 'round');
      path.setAttribute('opacity', '0.8');

      // CSS sway animation via inline style
      var duration = (2.2 + (j % 5) * 0.4).toFixed(1);
      var delay    = (j % 7 * 0.3).toFixed(1);
      path.style.cssText = 'transform-origin:' + x0 + 'px 2px;'
        + 'animation:cosmos-streamer-sway ' + duration + 's ease-in-out ' + delay + 's infinite alternate;';

      svg.appendChild(path);

      // Curl at bottom
      var curl = document.createElementNS(svgNs, 'path');
      var cx   = x0 + sway * 0.5;
      curl.setAttribute('d', 'M' + cx + ',' + len + ' Q' + (cx + 5) + ',' + (len + 8) + ' ' + (cx - 3) + ',' + (len + 10));
      curl.setAttribute('fill', 'none');
      curl.setAttribute('stroke', colour);
      curl.setAttribute('stroke-width', '1.5');
      curl.setAttribute('stroke-linecap', 'round');
      curl.setAttribute('opacity', '0.55');
      svg.appendChild(curl);
    }

    // Inject sway keyframes once
    if (!document.getElementById('cosmos-streamer-kf')) {
      var style = document.createElement('style');
      style.id  = 'cosmos-streamer-kf';
      style.textContent = '@keyframes cosmos-streamer-sway{'
        + '0%{transform:rotate(-2deg)}'
        + '100%{transform:rotate(2deg)}'
        + '}';
      document.head.appendChild(style);
    }

    var hStyle = window.getComputedStyle(header);
    if (hStyle.position === 'static') header.style.position = 'relative';
    header.style.overflow = 'visible';

    // Wrap in a strip div (height:0) so the SVG doesn't push header layout
    var strip = document.createElement('div');
    strip.id = 'cosmos-easter-streamer-strip';
    strip.style.cssText = 'position:absolute;top:100%;left:0;right:0;height:0;overflow:visible;pointer-events:none;z-index:5;';
    strip.appendChild(svg);
    svg.style.cssText = 'position:absolute;top:0;left:0;display:block;pointer-events:none;overflow:visible;';
    svg.id = 'cosmos-easter-streamers';
    header.appendChild(strip);
  }

  buildStreamers();
  window.addEventListener('resize', function() {
    var old = document.getElementById('cosmos-easter-streamer-strip');
    if (old) old.remove();
    buildStreamers();
  });
  } // end cosmosStreamersInit
});


// ===========================================================================
// EASTER — HOPPING BUNNY
// ===========================================================================
app.initializers.add('resofire-cosmos-theme-bunny', function() {
  function _run() { cosmosBunnyInit(); }
  if (app.booted && typeof app.booted.then === 'function') {
    app.booted.then(_run, _run);
  } else {
    setTimeout(_run, 0);
  }
  function cosmosBunnyInit() {
  if (!app.forum) return;
  var forum = app.forum;

  function bunnyActive() {
    var on = parseInt(forum.attribute('cosmosEaster_bunny'), 10);
    if (!on) return false;
    var start = forum.attribute('cosmosEaster_start') || '';
    var end   = forum.attribute('cosmosEaster_end')   || '';
    if (!start && !end) return true;
    var today = new Date().toISOString().slice(0, 10);
    if (start && today < start) return false;
    if (end   && today > end)   return false;
    return true;
  }

  if (!bunnyActive()) return;

  function getPrimary() {
    return getComputedStyle(document.documentElement)
      .getPropertyValue('--primary-color').trim()
      || '#1D9E75';
  }

  // Solid bunny SVG — cream/white body with pink inner ears.
  // Solid fill is readable on both light and dark backgrounds.
  function bunnyHtml(flipX) {
    var flip = flipX ? 'transform="scale(-1,1) translate(-80,0)"' : '';
    return '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80">'
      + '<g ' + flip + '>'
      + '<path d="M28,32 Q23,12 26,4 Q29,-2 33,4 Q35,12 33,32Z" fill="#f0ede8" stroke="#c8c4be" stroke-width="1.2"/>'
      + '<path d="M29,31 Q25,14 27.5,7 Q29.5,2 31.5,7 Q33,14 31,31Z" fill="#e8a0b0" stroke="#d4537e" stroke-width="0.8"/>'
      + '<path d="M52,32 Q57,12 54,4 Q51,-2 47,4 Q45,12 47,32Z" fill="#f0ede8" stroke="#c8c4be" stroke-width="1.2"/>'
      + '<path d="M51,31 Q55,14 52.5,7 Q50.5,2 48.5,7 Q47,14 49,31Z" fill="#e8a0b0" stroke="#d4537e" stroke-width="0.8"/>'
      + '<ellipse cx="40" cy="60" rx="20" ry="17" fill="#f0ede8" stroke="#c8c4be" stroke-width="1.2"/>'
      + '<circle cx="40" cy="36" r="14" fill="#f0ede8" stroke="#c8c4be" stroke-width="1.2"/>'
      + '<circle cx="58" cy="58" r="5" fill="#ffffff" stroke="#c8c4be" stroke-width="1"/>'
      + '<ellipse cx="40" cy="38" rx="2.5" ry="1.8" fill="#e8708a" stroke="none"/>'
      + '<circle cx="34" cy="32" r="2" fill="#2c2420" stroke="none"/>'
      + '<circle cx="46" cy="32" r="2" fill="#2c2420" stroke="none"/>'
      + '<circle cx="34.8" cy="31.2" r="0.7" fill="#ffffff" stroke="none"/>'
      + '<circle cx="46.8" cy="31.2" r="0.7" fill="#ffffff" stroke="none"/>'
      + '<path d="M37,40 Q40,43 43,40" fill="none" stroke="#c87080" stroke-width="1" stroke-linecap="round"/>'
      + '<ellipse cx="24" cy="73" rx="6" ry="4" fill="#f0ede8" stroke="#c8c4be" stroke-width="1"/>'
      + '<ellipse cx="56" cy="73" rx="6" ry="4" fill="#f0ede8" stroke="#c8c4be" stroke-width="1"/>'
      + '</g>'
      + '</svg>';
  }

  var bunnyEl = null;

  function hopAcross() {
    if (bunnyEl) return;
    if (window.innerWidth < 480) return;

    var ltr        = Math.random() > 0.5;
    var vw         = window.innerWidth;
    var totalDist  = vw + 160;
    // Target ~18s total on screen. pauseDur is 2–4s, so travel gets ~14–16s.
    var pauseDur   = 2000 + Math.random() * 2000;
    var travelSecs = 18 - pauseDur / 1000;     // travel time in seconds
    var speed      = totalDist / travelSecs;   // px/s derived from target duration
    var preFrac    = 0.25 + Math.random() * 0.40;
    var pauseDist  = preFrac * totalDist;
    var postDist   = totalDist - pauseDist;
    var preDur     = pauseDist / speed;
    var postDur    = postDist  / speed;

    var startX = ltr ? -80 : vw + 80;
    var pauseX = ltr ? (startX + pauseDist) : (startX - pauseDist);
    var endX   = ltr ? (vw + 80) : -80;
    var hopDur = 1.4; // slow, natural hop cycle

    bunnyEl = document.createElement('div');
    bunnyEl.id = 'cosmos-easter-bunny';
    bunnyEl.innerHTML = bunnyHtml(!ltr);
    bunnyEl.style.cssText = [
      'position:fixed',
      'bottom:0',
      'left:' + startX + 'px',
      'pointer-events:none',
      'z-index:9000',
      'width:80px',
      'height:80px',
    ].join(';');

    var inner = bunnyEl.querySelector('svg');
    if (inner) {
      inner.style.cssText = 'display:block;animation:cosmos-bunny-hop ' + hopDur + 's ease-in-out infinite;transform-origin:40px 78px;';
    }

    document.body.appendChild(bunnyEl);

    // Phase 1: travel to pause point
    requestAnimationFrame(function() {
      requestAnimationFrame(function() {
        if (!bunnyEl) return;
        bunnyEl.style.transition = 'left ' + preDur.toFixed(2) + 's linear';
        bunnyEl.style.left = pauseX + 'px';
      });
    });

    // Phase 2: pause — stop hopping and sit still
    setTimeout(function() {
      if (!bunnyEl) return;
      bunnyEl.style.transition = 'none';
      if (inner) inner.style.animationPlayState = 'paused';

      // Phase 3: resume travel to exit
      setTimeout(function() {
        if (!bunnyEl) return;
        if (inner) inner.style.animationPlayState = 'running';
        requestAnimationFrame(function() {
          requestAnimationFrame(function() {
            if (!bunnyEl) return;
            bunnyEl.style.transition = 'left ' + postDur.toFixed(2) + 's linear';
            bunnyEl.style.left = endX + 'px';
          });
        });
        setTimeout(function() {
          if (bunnyEl && bunnyEl.parentNode) bunnyEl.parentNode.removeChild(bunnyEl);
          bunnyEl = null;
        }, postDur * 1000 + 300);
      }, pauseDur);
    }, preDur * 1000);
  }

  function scheduleNextHop() {
    var delay = (2 + Math.random() * 3) * 60 * 1000; // 2–5 minutes
    setTimeout(function() {
      hopAcross();
      scheduleNextHop();
    }, delay);
  }

  // First hop after 30–90 seconds so it doesn't appear immediately on load
  var firstDelay = (30 + Math.random() * 60) * 1000;
  setTimeout(function() {
    hopAcross();
    scheduleNextHop();
  }, firstDelay);
  } // end cosmosBunnyInit
});


// ===========================================================================
// EASTER — EGG HUNT
// ===========================================================================
app.initializers.add('resofire-cosmos-theme-egghunt', function() {
  function _run() { cosmosEggHuntInit(); }
  if (app.booted && typeof app.booted.then === 'function') {
    app.booted.then(_run, _run);
  } else {
    setTimeout(_run, 0);
  }
  function cosmosEggHuntInit() {
  if (!app.forum) return;
  var forum = app.forum;

  function eggHuntActive() {
    var on = parseInt(forum.attribute('cosmosEaster_basket'), 10);
    if (!on) return false;
    var start = forum.attribute('cosmosEaster_start') || '';
    var end   = forum.attribute('cosmosEaster_end')   || '';
    if (!start && !end) return true;
    var today = new Date().toISOString().slice(0, 10);
    if (start && today < start) return false;
    if (end   && today > end)   return false;
    return true;
  }

  if (!eggHuntActive()) return;

  var userId     = app.session && app.session.user ? app.session.user.id() : 'guest';
  var today      = new Date().toISOString().slice(0, 10);
  var storageKey = 'cosmos_eggs_' + today + '_' + userId;
  var foundSpots = JSON.parse(localStorage.getItem(storageKey) || '[]');

  var TOTAL_EGGS  = 8;
  var eggElements = {};

  // Hardcoded egg colours
  var EGG_PALETTES = [
    { fill: 'rgba(29,158,117,0.45)',  stroke: '#1D9E75' },
    { fill: 'rgba(212,83,126,0.45)',  stroke: '#D4537E' },
    { fill: 'rgba(127,119,221,0.45)', stroke: '#7F77DD' },
    { fill: 'rgba(186,117,23,0.45)',  stroke: '#BA7517' },
    { fill: 'rgba(29,158,117,0.55)',  stroke: '#1D9E75' },
    { fill: 'rgba(212,83,126,0.55)',  stroke: '#D4537E' },
    { fill: 'rgba(127,119,221,0.55)', stroke: '#7F77DD' },
    { fill: 'rgba(186,117,23,0.55)',  stroke: '#BA7517' },
  ];

  // 12 possible spots — 8 active per day, seeded by date
  var ALL_SPOTS = [
    '.IndexPage-nav',
    '.WelcomeHero',
    '.App-header',
    '.DiscussionList',
    '.sideNav',
    '.Post-actions',
    '.item-reply',
    '.UserCard.UserHero',
    '.Post-body',
    '.Search',
    '.DiscussionListItem',
    '.IndexPage-toolbar .Dropdown-toggle',
  ];

  // Seeded shuffle — same 8 spots for all users on a given day
  function seededRandom(seed) {
    var x = Math.sin(seed + 1) * 10000;
    return x - Math.floor(x);
  }
  function getDailySpots() {
    var seed  = today.split('-').reduce(function(a, b) { return parseInt(a) + parseInt(b); }, 0);
    var idxs  = [0,1,2,3,4,5,6,7,8,9,10,11];
    for (var i = idxs.length - 1; i > 0; i--) {
      var j = Math.floor(seededRandom(seed + i) * (i + 1));
      var t = idxs[i]; idxs[i] = idxs[j]; idxs[j] = t;
    }
    return idxs.slice(0, TOTAL_EGGS);
  }
  var dailySpots = getDailySpots();

  // ── Basket SVG (primary colour outline) ───────────────────────────────────
  function getPrimary() {
    return getComputedStyle(document.documentElement)
      .getPropertyValue('--primary-color').trim()
      || '#1D9E75';
  }

  function basketSvg(foundCount) {
    var p = getPrimary();
    var eggs = '';
    var eggPositions = [
      { cx: 20, cy: 28, idx: 0 },
      { cx: 40, cy: 22, idx: 1 },
      { cx: 60, cy: 27, idx: 2 },
      { cx: 30, cy: 16, idx: 3 },
      { cx: 50, cy: 15, idx: 4 },
      { cx: 14, cy: 20, idx: 5 },
      { cx: 64, cy: 19, idx: 6 },
      { cx: 40, cy: 32, idx: 7 },
    ];
    for (var i = 0; i < foundCount && i < eggPositions.length; i++) {
      var ep  = eggPositions[i];
      var pal = EGG_PALETTES[ep.idx % EGG_PALETTES.length];
      eggs += '<ellipse cx="' + ep.cx + '" cy="' + ep.cy + '" rx="7" ry="9"'
        + ' fill="' + pal.stroke + '" fill-opacity="0.4" stroke="' + pal.stroke + '" stroke-width="2"/>'
        + '<line x1="' + (ep.cx - 5) + '" y1="' + ep.cy + '" x2="' + (ep.cx + 5) + '" y2="' + ep.cy
        + '" stroke="' + pal.stroke + '" stroke-width="1.2" opacity="0.8"/>';
    }
    return '<svg xmlns="http://www.w3.org/2000/svg" width="90" height="130" viewBox="0 0 80 130">'
      + '<path d="M18,42 Q40,8 62,42" fill="none" stroke="' + p + '" stroke-width="2.5" stroke-linecap="round"/>'
      + '<path d="M33,22 Q27,14 31,10 Q35,6 39,14 Q40,17 40,22" fill="rgba(212,83,126,0.3)" stroke="#D4537E" stroke-width="1.3"/>'
      + '<path d="M47,22 Q53,14 49,10 Q45,6 41,14 Q40,17 40,22" fill="rgba(212,83,126,0.3)" stroke="#D4537E" stroke-width="1.3"/>'
      + '<circle cx="40" cy="22" r="3.5" fill="rgba(212,83,126,0.5)" stroke="#D4537E" stroke-width="1"/>'
      + '<path d="M8,42 Q6,108 16,118 L64,118 Q74,108 72,42Z" fill="' + p + '" fill-opacity="0.12" stroke="' + p + '" stroke-width="2"/>'
      + '<line x1="9" y1="58" x2="71" y2="58" stroke="' + p + '" stroke-width="1.2" opacity="0.55"/>'
      + '<line x1="9" y1="72" x2="71" y2="72" stroke="' + p + '" stroke-width="1.2" opacity="0.55"/>'
      + '<line x1="9" y1="86" x2="71" y2="86" stroke="' + p + '" stroke-width="1.2" opacity="0.55"/>'
      + '<line x1="10" y1="100" x2="70" y2="100" stroke="' + p + '" stroke-width="1.2" opacity="0.55"/>'
      + '<line x1="22" y1="43" x2="21" y2="118" stroke="' + p + '" stroke-width="0.9" opacity="0.35"/>'
      + '<line x1="34" y1="42" x2="33" y2="118" stroke="' + p + '" stroke-width="0.9" opacity="0.35"/>'
      + '<line x1="46" y1="42" x2="45" y2="118" stroke="' + p + '" stroke-width="0.9" opacity="0.35"/>'
      + '<line x1="58" y1="43" x2="57" y2="118" stroke="' + p + '" stroke-width="0.9" opacity="0.35"/>'
      + '<path d="M6,42 Q40,30 74,42" fill="none" stroke="' + p + '" stroke-width="2.5" stroke-linecap="round"/>'
      + '<path d="M16,118 Q40,125 64,118" fill="none" stroke="' + p + '" stroke-width="1.8" stroke-linecap="round"/>'
      + '<path d="M10,42 Q13,34 12,30 Q15,36 18,32 Q18,39 22,35 Q22,41 26,37 Q27,42 31,38" stroke="' + p + '" stroke-width="1.3" fill="none" opacity="0.5"/>'
      + '<path d="M42,41 Q45,33 49,29 Q49,35 53,31 Q53,38 57,34 Q58,40 62,36 Q63,40 67,38" stroke="' + p + '" stroke-width="1.3" fill="none" opacity="0.5"/>'
      + eggs
      + '</svg>';
  }

  // ── Build/update basket ────────────────────────────────────────────────────
  var basketEl = null;

  function mountBasket() {
    if (window.innerWidth <= 767) return; // hidden on mobile
    if (basketEl && document.body.contains(basketEl)) {
      basketEl.innerHTML = basketSvg(foundSpots.length);
      return;
    }
    basketEl = document.createElement('div');
    basketEl.id = 'CosmosEasterBasket';
    basketEl.style.cssText = [
      'position:fixed',
      'bottom:0',
      'right:16px',
      'z-index:40',
      'pointer-events:none',
      'width:90px',
    ].join(';');
    basketEl.innerHTML = basketSvg(foundSpots.length);
    document.body.appendChild(basketEl);
  }

  // ── Egg SVG ────────────────────────────────────────────────────────────────
  function eggSvg(spotIdx) {
    var pal = EGG_PALETTES[spotIdx % EGG_PALETTES.length];
    return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="30" viewBox="0 0 24 30">'
      + '<ellipse cx="12" cy="15" rx="11" ry="14" fill="' + pal.stroke + '" fill-opacity="0.4" stroke="' + pal.stroke + '" stroke-width="2"/>'
      + '<line x1="2" y1="15" x2="22" y2="15" stroke="' + pal.stroke + '" stroke-width="1.2" opacity="0.8"/>'
      + '<line x1="3" y1="10" x2="21" y2="10" stroke="' + pal.stroke + '" stroke-width="1" opacity="0.6"/>'
      + '<line x1="3" y1="20" x2="21" y2="20" stroke="' + pal.stroke + '" stroke-width="1" opacity="0.6"/>'
      + '</svg>';
  }

  // ── Place a single egg ─────────────────────────────────────────────────────
  function placeEgg(spotIdx) {
    var selectorIdx = dailySpots[spotIdx];
    var selector    = ALL_SPOTS[selectorIdx];
    var target      = document.querySelector(selector);
    if (!target) return null;

    var egg = document.createElement('div');
    egg.className      = 'CosmosEasterEgg';
    egg.dataset.spot   = spotIdx;
    egg.innerHTML      = eggSvg(spotIdx);
    egg.style.cssText  = [
      'position:absolute',
      'z-index:50',
      'cursor:pointer',
      'transition:transform 0.15s',
    ].join(';');

    // For the sort dropdown — peek behind the button
    if (selector === '.IndexPage-toolbar .Dropdown-toggle') {
      egg.style.top    = '-8px';
      egg.style.right  = '-6px';
      egg.style.zIndex = '1'; // behind the button
    } else {
      // Default: slightly offset within target
      egg.style.top   = (4 + (spotIdx * 7) % 12) + 'px';
      egg.style.right = (4 + (spotIdx * 5) % 10) + 'px';
    }

    egg.addEventListener('mouseenter', function() { egg.style.transform = 'scale(1.2)'; });
    egg.addEventListener('mouseleave', function() { egg.style.transform = 'scale(1)'; });
    egg.addEventListener('click', function(e) {
      e.stopPropagation();
      collectEgg(spotIdx, egg);
    });

    // Make target a positioning context if it isn't already
    var pos = getComputedStyle(target).position;
    if (pos === 'static') target.style.position = 'relative';
    target.appendChild(egg);
    return egg;
  }

  // ── Collect egg ────────────────────────────────────────────────────────────
  function collectEgg(spotIdx, el) {
    if (foundSpots.indexOf(spotIdx) !== -1) return;
    foundSpots.push(spotIdx);
    localStorage.setItem(storageKey, JSON.stringify(foundSpots));

    // Pop animation then remove
    el.style.transform  = 'scale(1.4)';
    el.style.opacity    = '0';
    el.style.transition = 'transform 0.3s, opacity 0.3s';
    setTimeout(function() { if (el.parentNode) el.parentNode.removeChild(el); }, 320);

    mountBasket();

    if (foundSpots.length >= TOTAL_EGGS) {
      setTimeout(huntComplete, 400);
    }
  }

  // ── Hunt complete ──────────────────────────────────────────────
  function huntComplete() {
    // Confetti from basket position, same style as Christmas
    var canvas = document.createElement('canvas');
    canvas.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:9997;';
    canvas.width  = window.innerWidth;
    canvas.height = window.innerHeight;
    document.body.appendChild(canvas);
    var ctx    = canvas.getContext('2d');
    var pieces = [];
    var cols   = ['#1D9E75','#D4537E','#7F77DD','#BA7517','#ffffff'];
    var bx = canvas.width - 61;
    var by = canvas.height - 80;
    if (basketEl) {
      var br = basketEl.getBoundingClientRect();
      bx = br.left + br.width / 2;
      by = br.top  + br.height * 0.2;
    }
    for (var i = 0; i < 120; i++) {
      pieces.push({
        x: bx + (Math.random() - 0.5) * 40, y: by,
        vx: (Math.random() - 0.5) * 7,
        vy: -3 - Math.random() * 7,
        g: 0.2,
        rot: Math.random() * Math.PI * 2,
        rs: (Math.random() - 0.5) * 0.18,
        w: 5 + Math.random() * 7,
        h: 3 + Math.random() * 4,
        col: cols[Math.floor(Math.random() * cols.length)],
        life: 1,
        dec: 0.007 + Math.random() * 0.005,
      });
    }
    function drawConf() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      pieces = pieces.filter(function(p) { return p.life > 0; });
      pieces.forEach(function(p) {
        p.vy += p.g; p.x += p.vx; p.y += p.vy;
        p.rot += p.rs; p.life -= p.dec;
        ctx.save();
        ctx.translate(p.x, p.y);
        ctx.rotate(p.rot);
        ctx.globalAlpha = Math.max(0, p.life);
        ctx.fillStyle = p.col;
        ctx.fillRect(-p.w/2, -p.h/2, p.w, p.h);
        ctx.restore();
      });
      if (pieces.length > 0) requestAnimationFrame(drawConf);
      else {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        if (canvas.parentNode) canvas.parentNode.removeChild(canvas);
      }
    }
    requestAnimationFrame(drawConf);

    // Basket glow
    if (basketEl) {
      basketEl.style.filter = 'drop-shadow(0 0 8px #1D9E75)';
      setTimeout(function() { if (basketEl) basketEl.style.filter = ''; }, 2000);
    }

    // Completion message
    var msg = document.createElement('div');
    msg.id = 'CosmosEggMsg';
    msg.style.cssText = [
      'position:fixed',
      'bottom:170px',
      'right:20px',
      'background:rgba(13,18,32,0.96)',
      'border:1px solid rgba(29,158,117,0.45)',
      'border-radius:10px',
      'padding:14px 18px',
      'z-index:9999',
      'pointer-events:none',
      'opacity:0',
      'transform:translateY(10px)',
      'transition:opacity 0.4s ease,transform 0.4s ease',
      'box-shadow:0 0 24px rgba(29,158,117,0.18)',
      'text-align:center',
      'max-width:220px',
    ].join(';');
    msg.innerHTML = '<div style="font-size:15px;font-weight:600;color:#1D9E75;margin-bottom:5px;">'
      + '\uD83E\uDD5A All eggs found!</div>'
      + '<div style="font-size:12px;color:rgba(255,255,255,0.5);">'
      + 'You found all 8 hidden eggs today</div>';
    document.body.appendChild(msg);
    requestAnimationFrame(function() {
      requestAnimationFrame(function() {
        msg.style.opacity = '1';
        msg.style.transform = 'translateY(0)';
      });
    });
    setTimeout(function() {
      msg.style.opacity = '0';
      msg.style.transform = 'translateY(10px)';
      setTimeout(function() {
        if (msg.parentNode) msg.parentNode.removeChild(msg);
      }, 420);
    }, 5000);

    // Notify server
    var year = new Date().getFullYear().toString();
    if (app.session && app.session.user &&
        !app.session.user.preferences()['cosmosEggHunt' + year]) {
      app.request({
        method: 'POST',
        url: app.forum.attribute('apiUrl') + '/resofire/cosmos/egg-complete',
      }).catch(function() {});
    }
  }

  // ── Place all active eggs ──────────────────────────────────────────────────
  function placeAllEggs() {
    for (var i = 0; i < TOTAL_EGGS; i++) {
      if (foundSpots.indexOf(i) !== -1) continue;
      if (eggElements[i] && document.body.contains(eggElements[i])) continue;
      if (eggElements[i]) delete eggElements[i];
      var el = placeEgg(i);
      if (el) eggElements[i] = el;
    }
  }

  // ── Hook into page lifecycle ───────────────────────────────────────────────
  extend(Page.prototype, 'oncreate', function() {
    requestAnimationFrame(function() {
      mountBasket();
      placeAllEggs();
    });
  });
  extend(Page.prototype, 'onupdate', function() {
    requestAnimationFrame(function() {
      mountBasket();
      placeAllEggs();
    });
  });

  requestAnimationFrame(function() {
    mountBasket();
    placeAllEggs();
  });
  } // end cosmosEggHuntInit
});


// ===========================================================================
// EGG HUNT NOTIFICATION COMPONENT
// ===========================================================================
app.initializers.add('resofire-cosmos-theme-egghunt-notif', function() {
  function _run() {
    if (!app.notificationComponents) return;
    app.notificationComponents['cosmosEggHuntComplete'] = {
      view: function(vnode) {
        var n    = vnode.attrs.notification;
        var user = n.fromUser();
        var name = user
          ? (user.displayName ? user.displayName() : user.username())
          : 'A member';
        var href = user ? app.route('user', { username: user.slug() }) : '#';
        return m('a.NotificationList-item', {
          href: href,
          style: 'display:flex;align-items:center;gap:8px;padding:10px 14px;text-decoration:none;color:inherit;'
        }, [
          m('span', { style: 'font-size:18px;flex-shrink:0;' }, '\uD83E\uDD5A'),
          m('span', { style: 'font-size:13px;' }, name + ' found all 8 hidden Easter eggs!'),
        ]);
      },
    };
  }
  if (app.booted && typeof app.booted.then === 'function') {
    app.booted.then(_run, _run);
  } else {
    setTimeout(_run, 0);
  }
});

module.exports = { extend: [] };
