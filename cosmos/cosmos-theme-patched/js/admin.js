'use strict';

// Cosmos Theme — admin.js
//
// Registers:
//   1. The font picker setting (from previous work)
//   2. The theme mode default setting (auto/day/night dropdown)
//
// Boot sequence (confirmed from app.blade.php + Application.tsx):
//   1. All JS executes — app.initializers.add() queues callbacks
//   2. bootExtensions() — processes extend[] arrays
//   3. boot() — runs app.initializers in order

app.initializers.add('resofire-cosmos-theme', function() {
  var Switch = flarum.core.compat['common/components/Switch'];

  app.extensionData
    .for('resofire-cosmos-theme')

    // ── Section: General ─────────────────────────────────────────────────
    .registerSetting(function() {
      return m('div', { style: 'margin-bottom: 4px;' }, [
        m('h3', { style: 'font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted-color); margin: 0 0 16px;' }, 'General'),
      ]);
    })

    // Font picker
    .registerSetting({
      setting: 'resofire-cosmos-theme.font',
      label: 'Forum Font',
      help: 'Applied to all text across the forum and admin panel. Code blocks always use DM Mono regardless of this setting.',
      type: 'select',
      options: {
        'Outfit':            'Outfit (default)',
        'Space Grotesk':     'Space Grotesk',
        'DM Sans':           'DM Sans',
        'Sora':              'Sora',
        'Plus Jakarta Sans': 'Plus Jakarta Sans',
        'Nunito':            'Nunito',
        'system-ui':         'System UI (Flarum default)',
      },
      default: 'Outfit',
    })
    // Default theme mode
    .registerSetting({
      setting: 'cosmos-theme.default_theme',
      label: 'Default Colour Theme',
      help: 'Applied for guests and users who have not set a preference. 0 = Auto, 1 = Day, 2 = Night.',
      type: 'select',
      options: {
        0: 'Automatic (follow device setting)',
        1: 'Day Mode',
        2: 'Night Mode',
      },
      default: '0',
    })
    // Stars and glow effects toggle
    .registerSetting({
      setting: 'cosmos-theme.effects',
      label: 'Star Fields & Glow Effects',
      help: 'Enable or disable all star field animations and glow effects across the forum.',
      type: 'boolean',
      default: '1',
    })
    // Post controls visibility
    .registerSetting({
      setting: 'cosmos-theme.always_show_controls',
      label: 'Always Show Post Controls',
      help: 'When enabled, the Like, Reply, and Reactions buttons are always visible on every post. By default they only appear when hovering over a post.',
      type: 'boolean',
      default: '0',
    })


        // ── Section: Cosmos Slider ───────────────────────────────────────────
    .registerSetting(function() {
      return m('div', { style: 'margin: 8px 0 4px;' }, [
        m('hr', { style: 'border: none; border-top: 1px solid rgba(255,255,255,0.07); margin-bottom: 20px;' }),
        m('h3', { style: 'font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted-color); margin: 0 0 16px;' }, 'Cosmos Slider'),
      ]);
    })

    .registerSetting({
      setting: 'cosmos-theme.slider_enabled',
      label: 'Enable Cosmos Slider',
      help: 'Replace the hero/welcome area with an image slider. Has no effect if no slides are configured.',
      type: 'boolean',
      default: '0',
    })
    .registerSetting({
      setting: 'cosmos-theme.slider_disable_mobile',
      label: 'Disable Slider on Mobile',
      help: 'On mobile viewports the default hero will be shown instead of the slider.',
      type: 'boolean',
      default: '1',
    })
    .registerSetting({
      setting: 'cosmos-theme.slider_hide_on_tag_pages',
      label: 'Hide Slider on Tag Pages',
      help: 'Show the default hero on tag/category pages instead of the slider.',
      type: 'boolean',
      default: '0',
    })
    .registerSetting({
      setting: 'cosmos-theme.slider_height_desktop',
      label: 'Slider Height — Desktop (px)',
      type: 'number',
      default: '320',
    })
    .registerSetting({
      setting: 'cosmos-theme.slider_height_mobile',
      label: 'Slider Height — Mobile (px)',
      type: 'number',
      default: '200',
    })
    .registerSetting({
      setting: 'cosmos-theme.slider_autoplay',
      label: 'Autoplay Interval (seconds, 0 = off)',
      type: 'number',
      default: '0',
    })
    .registerSetting(function() {
      // Custom slide manager UI
      var self = this;

      function getSlides() {
        var raw = self.setting('cosmos-theme.slider_slides')() || '[]';
        try { return JSON.parse(raw); } catch(e) { return []; }
      }

      function saveSlides(slides) {
        self.setting('cosmos-theme.slider_slides')(JSON.stringify(slides));
        m.redraw();
      }

      function moveUp(i) {
        var s = getSlides();
        if (i === 0) return;
        var tmp = s[i - 1]; s[i - 1] = s[i]; s[i] = tmp;
        saveSlides(s);
      }

      function moveDown(i) {
        var s = getSlides();
        if (i === s.length - 1) return;
        var tmp = s[i + 1]; s[i + 1] = s[i]; s[i] = tmp;
        saveSlides(s);
      }

      function remove(i) {
        var s = getSlides();
        s.splice(i, 1);
        saveSlides(s);
      }

      function addSlide() {
        var s = getSlides();
        s.push({ image: '', link: '', newTab: false });
        saveSlides(s);
      }

      function update(i, key, value) {
        var s = getSlides();
        s[i][key] = value;
        saveSlides(s);
      }

      var slides = getSlides();

      return m('div.Form-group.CosmosSlides-manager', [
        m('label.label', 'Slides'),
        m('p.helpText', 'Add image slides. Each slide requires an image URL. Link and new-tab are optional.'),
        m('div.CosmosSlides-list', slides.map(function(s, i) {
          return m('div.CosmosSlides-item', { key: i }, [
            m('input.FormControl.CosmosSlides-image', {
              type: 'text',
              placeholder: 'Image URL (https://...)',
              value: s.image,
              oninput: function(e) { update(i, 'image', e.target.value); },
            }),
            m('input.FormControl.CosmosSlides-link', {
              type: 'text',
              placeholder: 'Link URL (optional)',
              value: s.link || '',
              oninput: function(e) { update(i, 'link', e.target.value); },
            }),
            m('label.CosmosSlides-newtab', [
              m('input', {
                type: 'checkbox',
                checked: !!s.newTab,
                onchange: function(e) { update(i, 'newTab', e.target.checked); },
              }),
              ' New tab',
            ]),
            m('button.Button.Button--icon', {
              title: 'Move Up',
              disabled: i === 0,
              onclick: function(e) { e.preventDefault(); moveUp(i); },
            }, m('i.fas.fa-arrow-up')),
            m('button.Button.Button--icon', {
              title: 'Move Down',
              disabled: i === slides.length - 1,
              onclick: function(e) { e.preventDefault(); moveDown(i); },
            }, m('i.fas.fa-arrow-down')),
            m('button.Button.Button--danger.Button--icon', {
              title: 'Remove',
              onclick: function(e) { e.preventDefault(); remove(i); },
            }, m('i.fas.fa-trash')),
          ]);
        })),
        m('button.Button.Button--primary', {
          onclick: function(e) { e.preventDefault(); addSlide(); },
        }, [m('i.fas.fa-plus'), ' Add Slide']),
      ]);
    })


    // ── Section: Holidays ────────────────────────────────────────────────
    .registerSetting(function() {
      return m('div', { style: 'margin: 8px 0 4px;' }, [
        m('hr', { style: 'border: none; border-top: 1px solid rgba(255,255,255,0.07); margin-bottom: 20px;' }),
        m('h3', { style: 'font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted-color); margin: 0 0 4px;' }, 'Holidays'),
        m('p', { style: 'font-size: 12px; color: var(--muted-color); margin: 0 0 16px;' }, 'Configure seasonal decorations. Each holiday has its own collapsible section.'),
      ]);
    })

    // ── Christmas (collapsible) ───────────────────────────────────────────
    .registerSetting((function() {
      // State lives outside the render function so m.redraw() doesn't reset it
      var _expanded = false;

      return function() {
      var self = this;

      // ── SVG helpers ─────────────────────────────────────────────────
      var ns = 'http://www.w3.org/2000/svg';
      function svgEl(tag, attrs, parent) {
        var e = document.createElementNS(ns, tag);
        Object.keys(attrs).forEach(function(k) { e.setAttribute(k, attrs[k]); });
        if (parent) parent.appendChild(e);
        return e;
      }

      function getVal(key, def) {
        var v = parseInt(self.setting('cosmos-theme.' + key)(), 10);
        return isNaN(v) ? def : v;
      }
      function setVal(key, val) {
        self.setting('cosmos-theme.' + key)(String(val));
      }

      var cfg = {
        flop:  getVal('holiday_hat_flop',  51),
        width: getVal('holiday_hat_width',  30),
        brim:  getVal('holiday_hat_brim',   38),
        pomp:  getVal('holiday_hat_pomp',   10),
        angle: getVal('holiday_hat_angle', -22),
        top:   getVal('holiday_hat_top',   -52),
        left:  getVal('holiday_hat_left',  -22),
        size:  getVal('holiday_hat_size',  112),
      };

      function makeHatSVG(c) {
        var flop = c.flop, width = c.width, brim = c.brim, pomp = c.pomp;
        var tipX = 62 - flop;
        var tipY = 8 + flop * 0.35;
        var rightX = 40 + width;
        var s = svgEl('svg', { xmlns: ns, viewBox: '0 0 120 95' });
        var d = svgEl('defs', {}, s);
        var g1 = svgEl('linearGradient', { id: 'hh1', x1: '0%', y1: '0%', x2: '100%', y2: '100%' }, d);
        svgEl('stop', { offset: '0%', 'stop-color': '#e74c3c' }, g1);
        svgEl('stop', { offset: '100%', 'stop-color': '#922b21' }, g1);
        var g2 = svgEl('linearGradient', { id: 'hh2', x1: '0%', y1: '0%', x2: '0%', y2: '100%' }, d);
        svgEl('stop', { offset: '0%', 'stop-color': '#f6f6f6' }, g2);
        svgEl('stop', { offset: '100%', 'stop-color': '#d2d2d2' }, g2);
        svgEl('ellipse', { cx: '40', cy: '86', rx: String(brim * 0.8), ry: '5', fill: 'rgba(0,0,0,0.1)' }, s);
        var cp = ['M', tipX, tipY, 'Q', tipX + 14, tipY + (78 - tipY) * 0.55, 8, 72,
          'Q', 16, 80, 40 - brim + 6, 82, 'Q', 40, 78, rightX, 70,
          'Q', rightX - 8, tipY + (70 - tipY) * 0.6, tipX, tipY, 'Z'].join(' ');
        svgEl('path', { d: cp, fill: 'url(#hh1)' }, s);
        var hl = ['M', tipX + 2, tipY + 4, 'Q', tipX + 10, tipY + (78 - tipY) * 0.5, tipX + 4, 70,
          'Q', tipX + 14, 64, tipX + 20, 56,
          'Q', tipX + 12, tipY + (56 - tipY) * 0.5, tipX + 2, tipY + 4, 'Z'].join(' ');
        svgEl('path', { d: hl, fill: 'rgba(255,255,255,0.13)' }, s);
        svgEl('ellipse', { cx: '40', cy: '82', rx: String(brim), ry: '11', fill: 'url(#hh2)', stroke: '#c5c5c5', 'stroke-width': '0.5' }, s);
        svgEl('ellipse', { cx: '38', cy: '78', rx: String(brim - 4), ry: '5.5', fill: 'rgba(255,255,255,0.52)' }, s);
        svgEl('circle', { cx: String(tipX), cy: String(tipY), r: String(pomp), fill: '#f2f2f2', stroke: '#ddd', 'stroke-width': '0.5' }, s);
        [[-3,-3,0.27,0.44],[3,-2,0.22,0.4],[0,3,0.27,0.44],[-4,2,0.2,0.34],[3,2,0.18,0.34]].forEach(function(f) {
          svgEl('circle', { cx: String(tipX + f[0]), cy: String(tipY + f[1]), r: String(pomp * f[2]), fill: 'rgba(200,200,200,' + f[3] + ')' }, s);
        });
        svgEl('circle', { cx: String(tipX - pomp * 0.22), cy: String(tipY - pomp * 0.25), r: String(pomp * 0.38), fill: 'rgba(255,255,255,0.76)' }, s);
        return s;
      }

      function hatB64(c) {
        var svg = makeHatSVG(c);
        var xml = new XMLSerializer().serializeToString(svg);
        return 'data:image/svg+xml;base64,' + btoa(xml);
      }

      function buildHatEl(avPx, c) {
        var img = document.createElement('img');
        img.src = hatB64(c);
        img.setAttribute('aria-hidden', 'true');
        var w = avPx * (c.size / 100);
        img.style.cssText = [
          'position:absolute', 'pointer-events:none', 'z-index:20',
          'width:' + w + 'px', 'height:' + w + 'px',
          'top:' + (avPx * (c.top / 100)) + 'px',
          'left:' + (avPx * (c.left / 100)) + 'px',
          'transform-origin:80% 88%',
          'transform:rotate(' + c.angle + 'deg)',
        ].join(';');
        return img;
      }

      function avWrap(letter, bg, sizePx, c) {
        var wrap = document.createElement('span');
        wrap.style.cssText = 'position:relative;display:inline-flex;overflow:visible;flex-shrink:0;vertical-align:bottom;';
        var av = document.createElement('span');
        av.style.cssText = 'display:inline-flex;align-items:center;justify-content:center;border-radius:50%;' +
          'width:' + sizePx + 'px;height:' + sizePx + 'px;font-size:' + Math.round(sizePx * 0.37) + 'px;' +
          'font-weight:500;color:#fff;background:' + bg + ';flex-shrink:0;';
        av.textContent = letter;
        wrap.appendChild(av);
        wrap.appendChild(buildHatEl(sizePx, c));
        return wrap;
      }

      // ── Mithril vnode — renders the collapsible Christmas block ─────
      return m('div.Form-group', [

        // ── Collapsible header ─────────────────────────────────────────
        m('div', {
          style: 'display:flex;align-items:center;gap:10px;cursor:pointer;' +
                 'padding:10px 14px;background:rgba(255,255,255,0.04);' +
                 'border:1px solid rgba(255,255,255,0.08);border-radius:6px;margin-bottom:4px;' +
                 'user-select:none;',
          onclick: function() {
            _expanded = !_expanded;
            m.redraw();
          },
        }, [
          m('span', { style: 'font-size:16px;' }, '\uD83C\uDF84'),
          m('span', { style: 'font-size:13px;font-weight:600;color:var(--body-color,#fff);flex:1;' }, 'Christmas'),
          m('span', { style: 'font-size:11px;color:var(--muted-color,#999);' }, _expanded ? '\u25B2 collapse' : '\u25BC expand'),
        ]),

        // ── Collapsible body ───────────────────────────────────────────
        _expanded ? m('div', {
          style: 'border:1px solid rgba(255,255,255,0.08);border-top:none;border-radius:0 0 6px 6px;' +
                 'padding:16px 14px;margin-bottom:8px;',
        }, [

          // Enable toggle — Flarum native Switch
          m('div.Form-group', { style: 'margin-bottom:12px;' }, [
            m(Switch, {
              state: self.setting('cosmos-theme.holiday_hats')() == '1',
              onchange: function(val) { self.setting('cosmos-theme.holiday_hats')(val ? '1' : '0'); },
            }, 'Santa hats'),
            m('p.helpText', 'Show a Santa hat on every user avatar across the forum.'),
          ]),

          // Auto-schedule toggle — Flarum native Switch
          m('div.Form-group', { style: 'margin-bottom:12px;' }, [
            m(Switch, {
              state: self.setting('cosmos-theme.holiday_hats_auto')() == '1',
              onchange: function(val) { self.setting('cosmos-theme.holiday_hats_auto')(val ? '1' : '0'); },
            }, 'Auto-schedule (Dec 1 – Jan 6)'),
            m('p.helpText', 'Automatically enable hats during the holiday season, ignoring the manual toggle above.'),
          ]),

          // Gift hunt toggle
          m('div.Form-group', { style: 'margin-bottom:12px;' }, [
            m(Switch, {
              state: self.setting('cosmos-theme.holiday_gifts')() == '1',
              onchange: function(val) { self.setting('cosmos-theme.holiday_gifts')(val ? '1' : '0'); },
            }, 'Gift hunt'),
            m('p.helpText', '8 wrapped gifts hidden around the forum. Members find and collect them. Admins notified when a member completes the hunt.'),
          ]),

          // Christmas lights toggle
          m('div.Form-group', { style: 'margin-bottom:12px;' }, [
            m(Switch, {
              state: self.setting('cosmos-theme.holiday_lights')() == '1',
              onchange: function(val) { self.setting('cosmos-theme.holiday_lights')(val ? '1' : '0'); },
            }, 'Christmas lights'),
            m('p.helpText', 'Show coloured bulb lights along the bottom of the header bar.'),
          ]),

          // Snow toggle
          m('div.Form-group', { style: 'margin-bottom:12px;' }, [
            m(Switch, {
              state: self.setting('cosmos-theme.holiday_snow')() == '1',
              onchange: function(val) { self.setting('cosmos-theme.holiday_snow')(val ? '1' : '0'); },
            }, 'Falling snow'),
            m('p.helpText', 'Light dusting of snowflakes drifting down across the forum.'),
          ]),

          // Hat placement live preview + sliders
          m('div.Form-group', [
            m('label.label', 'Hat placement'),
            m('p.helpText', { style: 'margin-bottom:8px;' }, 'Use the sliders to position the hat. The preview updates live — what you see here is exactly what appears on the forum.'),
            m('div', {
              oncreate: function(vnode) {
                var container = vnode.dom;
                var SIZES = [
                  { px: 40,  letter: 'A', bg: '#2980b9', label: '40px' },
                  { px: 52,  letter: 'J', bg: '#16a085', label: '52px' },
                  { px: 80,  letter: 'M', bg: '#8e44ad', label: '80px' },
                ];
                var sliders = [
                  { key: 'angle', label: 'Angle',      min: -70, max: 10,  step: 1, unit: '\u00b0', cfgKey: 'angle' },
                  { key: 'top',   label: 'Vertical',   min: -90, max: -10, step: 1, unit: '%',        cfgKey: 'top'   },
                  { key: 'left',  label: 'Horizontal', min: -50, max: 20,  step: 1, unit: '%',        cfgKey: 'left'  },
                  { key: 'size',  label: 'Size',        min: 60,  max: 160, step: 1, unit: '%',        cfgKey: 'size'  },
                ];

                var previewDiv = document.createElement('div');
                previewDiv.style.cssText = 'display:flex;align-items:flex-end;gap:24px;padding:60px 16px 20px;' +
                  'background:var(--body-bg,#1a1d23);border-radius:6px;margin-bottom:12px;flex-wrap:wrap;overflow:visible;';

                function rebuildPreview() {
                  previewDiv.innerHTML = '';
                  SIZES.forEach(function(s) {
                    var item = document.createElement('div');
                    item.style.cssText = 'display:flex;flex-direction:column;align-items:center;gap:8px;overflow:visible;';
                    item.appendChild(avWrap(s.letter, s.bg, s.px, cfg));
                    var lbl = document.createElement('span');
                    lbl.style.cssText = 'font-size:11px;color:var(--muted-color,#888);';
                    lbl.textContent = s.label;
                    item.appendChild(lbl);
                    previewDiv.appendChild(item);
                  });
                }

                container.appendChild(previewDiv);
                rebuildPreview();

                sliders.forEach(function(sl) {
                  var row = document.createElement('div');
                  row.style.cssText = 'display:flex;align-items:center;gap:10px;margin-bottom:8px;';
                  var lbl = document.createElement('span');
                  lbl.style.cssText = 'font-size:12px;color:var(--muted-color,#999);width:80px;flex-shrink:0;';
                  lbl.textContent = sl.label;
                  row.appendChild(lbl);
                  var input = document.createElement('input');
                  input.type = 'range';
                  input.min  = sl.min;
                  input.max  = sl.max;
                  input.step = sl.step;
                  input.value = cfg[sl.cfgKey];
                  input.style.cssText = 'flex:1;';
                  var badge = document.createElement('span');
                  badge.style.cssText = 'font-size:12px;font-weight:500;color:var(--body-color,#fff);' +
                    'background:rgba(255,255,255,0.07);border-radius:4px;padding:2px 7px;min-width:46px;text-align:center;';
                  function updateBadge(v) {
                    badge.textContent = (v < 0 ? '\u2212' : '') + Math.abs(v) + sl.unit;
                  }
                  updateBadge(cfg[sl.cfgKey]);
                  input.addEventListener('input', function() {
                    var v = parseInt(this.value, 10);
                    cfg[sl.cfgKey] = v;
                    setVal('holiday_hat_' + sl.key, v);
                    updateBadge(v);
                    rebuildPreview();
                  });
                  row.appendChild(input);
                  row.appendChild(badge);
                  container.appendChild(row);
                });
              },
            }),
          ]),

        ]) : null, // end collapsible body

      ]); // end Christmas block
      }; // end returned render function
    })())

});



// ===========================================================================
// ADMIN — EASTER SECTION
// ===========================================================================
app.initializers.add('resofire-cosmos-theme-admin-easter', function() {
  if (!app.extensionData) return;
  var Switch = flarum.core.compat['common/components/Switch'];

  app.extensionData
    .for('resofire-cosmos-theme')
    .registerSetting((function() {
      // State outside render so m.redraw() doesn't reset it
      var _expanded = false;

      return function() {
        var self = this;

        function getInt(key, def) {
          var v = parseInt(self.setting('cosmos-theme.' + key)(), 10);
          return isNaN(v) ? def : v;
        }
        function getStr(key) {
          return self.setting('cosmos-theme.' + key)() || '';
        }

        var earSize = getInt('easter_ear_size', 100);
        var earTop  = getInt('easter_ear_top',  -90);

        // ── Ear preview helper ─────────────────────────────────────────
        function avWrap(letter, bg, sizePx) {
          var primary = '#1D9E75';
          var scale   = earSize / 100;
          var earW    = sizePx * 0.28 * scale;
          var earH    = sizePx * 0.45 * scale;
          var innerW  = earW * 0.55;
          var innerH  = earH * 0.62;
          var spread  = sizePx * 0.22 * scale;
          var svgW    = sizePx;
          var svgH    = earH * 2 + 4;
          var cy      = earH + 2;
          var lx      = svgW / 2 - spread;
          var rx      = svgW / 2 + spread;
          var topPx   = sizePx * (earTop / 100);

          var earSvg = '<svg xmlns="http://www.w3.org/2000/svg"'
            + ' width="' + svgW + '" height="' + svgH + '"'
            + ' viewBox="0 0 ' + svgW + ' ' + svgH + '"'
            + ' style="overflow:visible">'
            + '<ellipse cx="' + lx + '" cy="' + cy + '" rx="' + earW + '" ry="' + earH + '"'
            + ' fill="' + primary + '" fill-opacity="0.12" stroke="' + primary + '" stroke-width="1.6"/>'
            + '<ellipse cx="' + lx + '" cy="' + (cy + earH * 0.08) + '" rx="' + innerW + '" ry="' + innerH + '"'
            + ' fill="rgba(212,83,126,0.32)" stroke="#D4537E" stroke-width="1"/>'
            + '<ellipse cx="' + rx + '" cy="' + cy + '" rx="' + earW + '" ry="' + earH + '"'
            + ' fill="' + primary + '" fill-opacity="0.12" stroke="' + primary + '" stroke-width="1.6"/>'
            + '<ellipse cx="' + rx + '" cy="' + (cy + earH * 0.08) + '" rx="' + innerW + '" ry="' + innerH + '"'
            + ' fill="rgba(212,83,126,0.32)" stroke="#D4537E" stroke-width="1"/>'
            + '</svg>';

          var wrap = document.createElement('div');
          wrap.style.cssText = 'display:flex;flex-direction:column;align-items:center;gap:8px;overflow:visible;';

          var avWrapDiv = document.createElement('div');
          avWrapDiv.style.cssText = 'position:relative;width:' + sizePx + 'px;height:' + sizePx + 'px;overflow:visible;'
            + 'margin-top:' + (earH * 2 + 10) + 'px;';

          var avatar = document.createElement('div');
          avatar.style.cssText = 'width:' + sizePx + 'px;height:' + sizePx + 'px;border-radius:50%;'
            + 'background:' + bg + ';display:flex;align-items:center;justify-content:center;'
            + 'color:#fff;font-weight:500;font-size:' + Math.round(sizePx * 0.35) + 'px;';
          avatar.textContent = letter;

          var earEl = document.createElement('div');
          earEl.style.cssText = 'position:absolute;left:0;top:' + topPx + 'px;width:' + svgW + 'px;pointer-events:none;';
          earEl.innerHTML = earSvg;

          avWrapDiv.appendChild(avatar);
          avWrapDiv.appendChild(earEl);

          var lbl = document.createElement('span');
          lbl.style.cssText = 'font-size:11px;color:var(--muted-color,#888);';
          lbl.textContent = sizePx + 'px';

          wrap.appendChild(avWrapDiv);
          wrap.appendChild(lbl);
          return wrap;
        }

        // ── Main render ────────────────────────────────────────────────
        return m('div.Form-group', [

          // Collapsible header
          m('div', {
            style: 'display:flex;align-items:center;gap:10px;cursor:pointer;'
              + 'padding:10px 14px;background:rgba(255,255,255,0.04);'
              + 'border:1px solid rgba(255,255,255,0.08);border-radius:6px;margin-bottom:4px;'
              + 'user-select:none;',
            onclick: function() { _expanded = !_expanded; m.redraw(); },
          }, [
            m('span', { style: 'font-size:16px;' }, '\uD83D\uDC23'),
            m('span', { style: 'font-size:13px;font-weight:600;color:var(--body-color,#fff);flex:1;' }, 'Easter'),
            m('span', { style: 'font-size:11px;color:var(--muted-color,#888);' }, _expanded ? '\u25B2 collapse' : '\u25BC expand'),
          ]),

          // Collapsible body
          _expanded ? m('div', {
            style: 'padding:16px;border:1px solid rgba(255,255,255,0.08);'
              + 'border-top:none;border-radius:0 0 6px 6px;margin-bottom:8px;',
          }, [

            // Feature toggles — Flarum native Switch components
            m('div.Form-group', { style: 'margin-bottom:12px;' }, [
              m(Switch, {
                state: self.setting('cosmos-theme.easter_ears')() == '1',
                onchange: function(val) { self.setting('cosmos-theme.easter_ears')(val ? '1' : '0'); },
              }, '\uD83D\uDC30 Bunny ears'),
            ]),

            m('div.Form-group', { style: 'margin-bottom:12px;' }, [
              m(Switch, {
                state: self.setting('cosmos-theme.easter_streamers')() == '1',
                onchange: function(val) { self.setting('cosmos-theme.easter_streamers')(val ? '1' : '0'); },
              }, '\uD83C\uDF80 Pastel streamers'),
            ]),

            m('div.Form-group', { style: 'margin-bottom:12px;' }, [
              m(Switch, {
                state: self.setting('cosmos-theme.easter_basket')() == '1',
                onchange: function(val) { self.setting('cosmos-theme.easter_basket')(val ? '1' : '0'); },
              }, '\uD83E\uDDFA Egg hunt'),
            ]),

            m('div.Form-group', { style: 'margin-bottom:12px;' }, [
              m(Switch, {
                state: self.setting('cosmos-theme.easter_bunny')() == '1',
                onchange: function(val) { self.setting('cosmos-theme.easter_bunny')(val ? '1' : '0'); },
              }, '\uD83D\uDC07 Hopping bunny'),
            ]),

            // Date range — optional, leave blank to use the toggles above directly
            m('p.helpText', { style: 'margin-top:8px;margin-bottom:8px;' },
              'Optional: set a date range to auto-activate all enabled features. Leave blank to control features manually with the toggles above.'),

            m('div', { style: 'display:flex;gap:16px;margin-bottom:20px;flex-wrap:wrap;' }, [
              m('div', { style: 'display:flex;flex-direction:column;gap:4px;' }, [
                m('label', { style: 'font-size:12px;color:var(--muted-color,#888);' }, 'Start date'),
                m('input.FormControl', {
                  type: 'date',
                  value: getStr('easter_start'),
                  style: 'width:160px;',
                  onchange: function() {
                    self.setting('cosmos-theme.easter_start')(this.value);
                  },
                }),
              ]),
              m('div', { style: 'display:flex;flex-direction:column;gap:4px;' }, [
                m('label', { style: 'font-size:12px;color:var(--muted-color,#888);' }, 'End date'),
                m('input.FormControl', {
                  type: 'date',
                  value: getStr('easter_end'),
                  style: 'width:160px;',
                  onchange: function() {
                    self.setting('cosmos-theme.easter_end')(this.value);
                  },
                }),
              ]),
            ]),

            // Ear placement
            m('label.label', { style: 'margin-bottom:4px;display:block;' }, 'Ear placement'),
            m('p.helpText', { style: 'margin-bottom:10px;' },
              'Ears are centred automatically. Adjust size and vertical position.'),

            // Live ear preview — sliders read/write settings via self.setting()
            // rebuildPreview() re-reads current values each time so sliders update live
            m('div', {
              oncreate: function(vnode) {
                var SIZES = [
                  { px: 40, letter: 'A', bg: '#2980b9' },
                  { px: 52, letter: 'J', bg: '#16a085' },
                  { px: 80, letter: 'M', bg: '#8e44ad' },
                ];
                var container  = vnode.dom;
                var previewDiv = document.createElement('div');
                previewDiv.style.cssText = 'display:flex;align-items:flex-end;gap:28px;padding:20px 16px 16px;'
                  + 'background:var(--body-bg,#1a1d23);border-radius:6px;margin-bottom:12px;flex-wrap:wrap;overflow:visible;';

                // Always re-read current setting values so the preview stays in sync
                function currentEarSize() {
                  return parseInt(self.setting('cosmos-theme.easter_ear_size')(), 10) || 100;
                }
                function currentEarTop() {
                  return parseInt(self.setting('cosmos-theme.easter_ear_top')(), 10) || -90;
                }

                function buildAvWrap(letter, bg, sizePx) {
                  var primary = '#1D9E75';
                  var scale   = currentEarSize() / 100;
                  var earW    = sizePx * 0.28 * scale;
                  var earH    = sizePx * 0.45 * scale;
                  var innerW  = earW * 0.55;
                  var innerH  = earH * 0.62;
                  var spread  = sizePx * 0.22 * scale;
                  var svgW    = sizePx;
                  var svgH    = earH * 2 + 4;
                  var cy      = earH + 2;
                  var lx      = svgW / 2 - spread;
                  var rx      = svgW / 2 + spread;
                  var topPx   = sizePx * (currentEarTop() / 100);

                  var earSvg = '<svg xmlns="http://www.w3.org/2000/svg"'
                    + ' width="' + svgW + '" height="' + svgH + '"'
                    + ' viewBox="0 0 ' + svgW + ' ' + svgH + '"'
                    + ' style="overflow:visible">'
                    + '<ellipse cx="' + lx + '" cy="' + cy + '" rx="' + earW + '" ry="' + earH + '"'
                    + ' fill="' + primary + '" fill-opacity="0.12" stroke="' + primary + '" stroke-width="1.6"/>'
                    + '<ellipse cx="' + lx + '" cy="' + (cy + earH * 0.08) + '" rx="' + innerW + '" ry="' + innerH + '"'
                    + ' fill="rgba(212,83,126,0.32)" stroke="#D4537E" stroke-width="1"/>'
                    + '<ellipse cx="' + rx + '" cy="' + cy + '" rx="' + earW + '" ry="' + earH + '"'
                    + ' fill="' + primary + '" fill-opacity="0.12" stroke="' + primary + '" stroke-width="1.6"/>'
                    + '<ellipse cx="' + rx + '" cy="' + (cy + earH * 0.08) + '" rx="' + innerW + '" ry="' + innerH + '"'
                    + ' fill="rgba(212,83,126,0.32)" stroke="#D4537E" stroke-width="1"/>'
                    + '</svg>';

                  var wrap = document.createElement('div');
                  wrap.style.cssText = 'display:flex;flex-direction:column;align-items:center;gap:8px;overflow:visible;';
                  var avDiv = document.createElement('div');
                  avDiv.style.cssText = 'position:relative;width:' + sizePx + 'px;height:' + sizePx + 'px;overflow:visible;'
                    + 'margin-top:' + (earH * 2 + 10) + 'px;';
                  var av = document.createElement('div');
                  av.style.cssText = 'width:' + sizePx + 'px;height:' + sizePx + 'px;border-radius:50%;'
                    + 'background:' + bg + ';display:flex;align-items:center;justify-content:center;'
                    + 'color:#fff;font-weight:500;font-size:' + Math.round(sizePx * 0.35) + 'px;';
                  av.textContent = letter;
                  var earEl = document.createElement('div');
                  earEl.style.cssText = 'position:absolute;left:0;top:' + topPx + 'px;width:' + svgW + 'px;pointer-events:none;';
                  earEl.innerHTML = earSvg;
                  avDiv.appendChild(av);
                  avDiv.appendChild(earEl);
                  var lbl = document.createElement('span');
                  lbl.style.cssText = 'font-size:11px;color:var(--muted-color,#888);';
                  lbl.textContent = sizePx + 'px';
                  wrap.appendChild(avDiv);
                  wrap.appendChild(lbl);
                  return wrap;
                }

                function rebuildPreview() {
                  previewDiv.innerHTML = '';
                  SIZES.forEach(function(s) {
                    previewDiv.appendChild(buildAvWrap(s.letter, s.bg, s.px));
                  });
                }

                container.appendChild(previewDiv);
                rebuildPreview();

                [
                  { label: 'Size',     settingKey: 'easter_ear_size', min: 50,  max: 160, unit: '%'  },
                  { label: 'Vertical', settingKey: 'easter_ear_top',  min: -120, max: -40, unit: '%'  },
                ].forEach(function(sl) {
                  var row = document.createElement('div');
                  row.style.cssText = 'display:flex;align-items:center;gap:10px;margin-bottom:8px;';
                  var lbl = document.createElement('span');
                  lbl.style.cssText = 'font-size:12px;color:var(--muted-color,#999);width:70px;flex-shrink:0;';
                  lbl.textContent = sl.label;
                  row.appendChild(lbl);
                  var input = document.createElement('input');
                  input.type  = 'range';
                  input.min   = sl.min;
                  input.max   = sl.max;
                  input.step  = '1';
                  input.value = self.setting('cosmos-theme.' + sl.settingKey)() || (sl.settingKey === 'easter_ear_size' ? '100' : '-90');
                  input.style.cssText = 'flex:1;';
                  var badge = document.createElement('span');
                  badge.style.cssText = 'font-size:12px;font-weight:500;color:var(--body-color,#fff);'
                    + 'background:rgba(255,255,255,0.07);border-radius:4px;padding:2px 7px;min-width:46px;text-align:center;';
                  function updateBadge(v) {
                    badge.textContent = (v < 0 ? '\u2212' : '') + Math.abs(v) + sl.unit;
                  }
                  updateBadge(parseInt(input.value, 10));
                  input.addEventListener('input', function() {
                    var v = parseInt(this.value, 10);
                    self.setting('cosmos-theme.' + sl.settingKey)(String(v));
                    updateBadge(v);
                    rebuildPreview();
                  });
                  row.appendChild(input);
                  row.appendChild(badge);
                  container.appendChild(row);
                });
              },
            }),
          ]) : null,
        ]);
      };
    })());
});

module.exports = { extend: [] };
