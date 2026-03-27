import Extend from 'flarum/common/extenders';
import app from 'flarum/admin/app';

let flushing = false;
let flushMessage = '';

function FlushSetting() {
  const flush = () => {
    if (flushing) return;
    flushing = true;
    flushMessage = '';
    app.request({ method: 'POST', url: app.forum.attribute('apiUrl') + '/resofire-avatars/flush' })
      .then((data: any) => {
        flushMessage = app.translator.trans('resofire-avatars.admin.flush_success', {
          users: data.flushed, files: data.filesDeleted,
        }) as string;
      })
      .catch(() => {
        flushMessage = app.translator.trans('resofire-avatars.admin.flush_error') as string;
      })
      .finally(() => { flushing = false; m.redraw(); });
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
}

export default [
  new Extend.Admin()
    .setting(() => ({
      setting: 'resofire-avatars.default_style',
      label: app.translator.trans('resofire-avatars.admin.default_style') as string,
      help: app.translator.trans('resofire-avatars.admin.default_style_help') as string,
      type: 'select',
      options: {
        random:        app.translator.trans('resofire-avatars.admin.style_options.random') as string,
        cyberpunk:     app.translator.trans('resofire-avatars.admin.style_options.cyberpunk') as string,
        android:       app.translator.trans('resofire-avatars.admin.style_options.android') as string,
        orc:           app.translator.trans('resofire-avatars.admin.style_options.orc') as string,
        undead:        app.translator.trans('resofire-avatars.admin.style_options.undead') as string,
        pirate:        app.translator.trans('resofire-avatars.admin.style_options.pirate') as string,
        glitch:        app.translator.trans('resofire-avatars.admin.style_options.glitch') as string,
        emoji:         app.translator.trans('resofire-avatars.admin.style_options.emoji') as string,
        'sugar-skull': app.translator.trans('resofire-avatars.admin.style_options.sugar-skull') as string,
        'lcd-face':    app.translator.trans('resofire-avatars.admin.style_options.lcd-face') as string,
        cassette:      app.translator.trans('resofire-avatars.admin.style_options.cassette') as string,
        eye:           app.translator.trans('resofire-avatars.admin.style_options.eye') as string,
        treant:        app.translator.trans('resofire-avatars.admin.style_options.treant') as string,
      },
    }))
    .customSetting(() => <FlushSetting />),
];
