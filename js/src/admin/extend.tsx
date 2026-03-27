import Extend from 'flarum/common/extenders';
import app from 'flarum/admin/app';

let flushing = false;
let flushMessage: any = '';

export default [
  new Extend.Admin()
    .setting(() => ({
      setting: 'resofire-avatars.default_style',
      label: app.translator.trans('resofire-avatars.admin.default_style'),
      help: app.translator.trans('resofire-avatars.admin.default_style_help'),
      type: 'select',
      options: {
        random:        app.translator.trans('resofire-avatars.admin.style_options.random'),
        cyberpunk:     app.translator.trans('resofire-avatars.admin.style_options.cyberpunk'),
        android:       app.translator.trans('resofire-avatars.admin.style_options.android'),
        orc:           app.translator.trans('resofire-avatars.admin.style_options.orc'),
        undead:        app.translator.trans('resofire-avatars.admin.style_options.undead'),
        pirate:        app.translator.trans('resofire-avatars.admin.style_options.pirate'),
        glitch:        app.translator.trans('resofire-avatars.admin.style_options.glitch'),
        emoji:         app.translator.trans('resofire-avatars.admin.style_options.emoji'),
        'sugar-skull': app.translator.trans('resofire-avatars.admin.style_options.sugar-skull'),
        'lcd-face':    app.translator.trans('resofire-avatars.admin.style_options.lcd-face'),
        cassette:      app.translator.trans('resofire-avatars.admin.style_options.cassette'),
        eye:           app.translator.trans('resofire-avatars.admin.style_options.eye'),
        treant:        app.translator.trans('resofire-avatars.admin.style_options.treant'),
      },
    }))
    .customSetting(() => {
      const flush = () => {
        if (flushing) return;
        flushing = true;
        flushMessage = '';
        app.request({ method: 'POST', url: app.forum.attribute('apiUrl') + '/resofire-avatars/flush' })
          .then((data: any) => {
            flushing = false;
            flushMessage = app.translator.trans('resofire-avatars.admin.flush_success', {
              users: data.flushed, files: data.filesDeleted,
            });
            m.redraw();
          })
          .catch(() => {
            flushing = false;
            flushMessage = app.translator.trans('resofire-avatars.admin.flush_error');
            m.redraw();
          });
      };

      return (
        <div className="Form-group">
          <label>{app.translator.trans('resofire-avatars.admin.flush_label')}</label>
          <p className="helpText">{app.translator.trans('resofire-avatars.admin.flush_help')}</p>
          <button className="Button Button--danger" onclick={flush} disabled={flushing}>
            {flushing
              ? app.translator.trans('resofire-avatars.admin.flush_running')
              : app.translator.trans('resofire-avatars.admin.flush_button')}
          </button>
          {flushMessage ? <p style={{ marginTop: '8px' }}>{flushMessage}</p> : null}
        </div>
      );
    }),
];
