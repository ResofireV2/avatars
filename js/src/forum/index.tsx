import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import SettingsPage from 'flarum/forum/components/SettingsPage';
import Component from 'flarum/common/Component';
import Stream from 'flarum/common/utils/Stream';

const STYLES = [
  { key: 'retro-pixel',      label: 'Retro Pixel' },
  { key: 'cyberpunk',        label: 'Cyberpunk' },
  { key: 'android',          label: 'Android' },
  { key: 'fantasy',          label: 'Fantasy' },
  { key: 'orc',              label: 'Orc' },
  { key: 'anime',            label: 'Anime' },
  { key: 'undead',           label: 'Undead' },
  { key: 'space-explorer',   label: 'Space Explorer' },
  { key: 'fantasy-creature', label: 'Fantasy Creature' },
  { key: 'pirate',           label: 'Pirate' },
  { key: 'glitch',           label: 'Glitch' },
  { key: 'emoji',            label: 'Emoji' },
];

class AvatarStylePicker extends Component<any> {
  selected!: Stream<string>;
  saving!: Stream<boolean>;
  saved!: Stream<boolean>;
  error!: Stream<string>;

  oninit(vnode: any) {
    super.oninit(vnode);
    this.selected = Stream(app.session.user?.attribute('rfAvatarStyle') || 'retro-pixel');
    this.saving   = Stream(false);
    this.saved    = Stream(false);
    this.error    = Stream('');
  }

  previewUrl(styleKey: string): string {
    const username = app.session.user?.username() || 'user';
    return (
      app.forum.attribute('apiUrl') +
      '/resofire-avatars/preview?username=' +
      encodeURIComponent(username) +
      '&style=' +
      encodeURIComponent(styleKey)
    );
  }

  save() {
    if (this.saving()) return;
    this.saving(true);
    this.saved(false);
    this.error('');

    app
      .request({
        method: 'POST',
        url: app.forum.attribute('apiUrl') + '/resofire-avatars/style',
        body: {
          style:  this.selected(),
          userId: app.session.user?.id(),
        },
      })
      .then(() => {
        this.saving(false);
        this.saved(true);
        app.session.user?.pushAttributes({ rfAvatarStyle: this.selected() });
        m.redraw();
        setTimeout(() => {
          this.saved(false);
          m.redraw();
        }, 3000);
      })
      .catch(() => {
        this.saving(false);
        this.error(
          app.translator.trans('resofire-avatars.forum.error').toString()
        );
        m.redraw();
      });
  }

  view() {
    return (
      <div className="Form-group RfAvatarPicker">
        <label>
          {app.translator.trans('resofire-avatars.forum.avatar_style_heading')}
        </label>
        <p className="helpText">
          {app.translator.trans('resofire-avatars.forum.avatar_style_help')}
        </p>

        <div className="RfAvatarPicker-grid">
          {STYLES.map(({ key, label }) => (
            <div
              className={'RfAvatarPicker-item' + (this.selected() === key ? ' active' : '')}
              onclick={() => { this.selected(key); m.redraw(); }}
              title={label}
            >
              <img
                src={this.previewUrl(key)}
                alt={label}
                className="RfAvatarPicker-preview"
                style={{ width: '80px', height: '80px', borderRadius: '50%', display: 'block' }}
              />
              <span className="RfAvatarPicker-label">{label}</span>
            </div>
          ))}
        </div>

        <div style={{ marginTop: '12px' }}>
          <button
            className="Button Button--primary"
            onclick={this.save.bind(this)}
            disabled={this.saving()}
          >
            {this.saving()
              ? app.translator.trans('resofire-avatars.forum.saving')
              : this.saved()
              ? app.translator.trans('resofire-avatars.forum.saved')
              : app.translator.trans('resofire-avatars.forum.save_button')}
          </button>
          {this.error()
            ? <span style={{ marginLeft: '10px', color: 'red' }}>{this.error()}</span>
            : null}
        </div>
      </div>
    );
  }
}

app.initializers.add('resofire/avatars', () => {
  extend(SettingsPage.prototype, 'settingsItems', function (items: any) {
    if (!app.session.user) return;
    items.add('rf-avatar-style', <AvatarStylePicker />, 0);
  });
});
