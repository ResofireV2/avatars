import app from 'flarum/admin/app';

app.initializers.add('resofire/avatars', () => {
  const styles = [
    'random',
    'retro-pixel',
    'cyberpunk',
    'android',
    'fantasy',
    'orc',
    'anime',
    'undead',
    'space-explorer',
    'fantasy-creature',
    'pirate',
    'glitch',
    'emoji',
  ];

  const styleOptions: Record<string, string> = {};
  styles.forEach((key) => {
    styleOptions[key] = app.translator
      .trans(`resofire-avatars.admin.style_options.${key}`)
      .toString();
  });

  let flushing = false;
  let flushMessage = '';

  const flush = () => {
    if (flushing) return;
    flushing = true;
    flushMessage = '';

    app
      .request({
        method: 'POST',
        url: app.forum.attribute('apiUrl') + '/resofire-avatars/flush',
      })
      .then((data: any) => {
        flushMessage = app.translator
          .trans('resofire-avatars.admin.flush_success', {
            users: data.flushed,
            files: data.filesDeleted,
          })
          .toString();
      })
      .catch(() => {
        flushMessage = app.translator
          .trans('resofire-avatars.admin.flush_error')
          .toString();
      })
      .finally(() => {
        flushing = false;
        m.redraw();
      });
  };

  app.extensionData
    .for('resofire-avatars')
    .registerSetting({
      setting: 'resofire-avatars.default_style',
      label: app.translator.trans('resofire-avatars.admin.default_style'),
      help: app.translator.trans('resofire-avatars.admin.default_style_help'),
      type: 'select',
      options: styleOptions,
    })
    .registerSetting(() => (
      <div className="Form-group">
        <label>{app.translator.trans('resofire-avatars.admin.flush_label')}</label>
        <p className="helpText">
          {app.translator.trans('resofire-avatars.admin.flush_help')}
        </p>
        <button
          className="Button Button--danger"
          onclick={flush}
          disabled={flushing}
        >
          {flushing
            ? app.translator.trans('resofire-avatars.admin.flush_running')
            : app.translator.trans('resofire-avatars.admin.flush_button')}
        </button>
        {flushMessage ? (
          <p style={{ marginTop: '8px' }}>{flushMessage}</p>
        ) : null}
      </div>
    ));
});
