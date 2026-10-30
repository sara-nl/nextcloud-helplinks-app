# Nextcloud Helplinks App

App that allows you to create a list of links to external documentation.

![HelplinksUserInterface.png](https://github.com/sara-nl/nextcloud-helplinks-app/raw/main/docs/screenshots/HelplinksUserInterface.png)

![HelplinksAdminInterface.png](https://github.com/sara-nl/nextcloud-helplinks-app/raw/main/docs/screenshots/HelplinksAdminInterface.png)

### Helplinks

You can add help sections yourself via the Nextcloud Admin interface.

A section consists of a Section Title with a Description and a main link with a title.  
In addition, you can specify multiple Sub-links within the section, consisting of a title and a URL.

This allows you to easily create a clear help page yourself.

### Introvox Interactive Tutorial

The app can be combined with the Introvox Interactive Tutorial. When this app is enabled, this option will become visible as an option within the app. The Introvox Interactive tutorial can be found in the Nextcloud App Store.

### Supportdesk

In addition to a reference to your wiki and the Introvox Interactive Tutorial, you can also add a reference to a service desk email address or service desk URL if the user requires further support.

These options must be entered directly into the database.

```
insert into oc_appconfig (appid,configkey,configvalue) VALUES ('helplinks','support_email','helpdesk@surf.nl');
insert into oc_appconfig (appid,configkey,configvalue) VALUES ('helplinks','support_url','https://servicedesk.surf.nl');
```


### Tests

The end-to-end test starts the official Nextcloud Docker image, installs the app from this
checkout and checks access for anonymous users, users and admins, the Help page with its
navigation entry and script, the admin settings, and creating, changing, reordering and
deleting sections and support settings through both the API of the frontend and the OCS API.
Any error in the Nextcloud log fails the run.

```bash
npm ci && npm run build                          # the test installs the built frontend
tests/e2e/run-docker.sh 35                       # image tag: 34, 35, latest, ...
tests/e2e/run-docker.sh master                   # daily build of the next version (36)
FORCE_ENABLE=1 tests/e2e/run-docker.sh latest    # a version newer than max-version
```

It needs Docker, curl and python3. `tests/e2e/e2e.sh` can also test an existing server over ssh;
see the comment at the top of the script.

`composer psalm` runs static analysis on `lib/`. IntroVox is an optional app and not part of
`nextcloud/ocp`, so `tests/stubs/introvox.php` describes the IntroVox class this app uses; add a
method there when the code starts using it.

GitHub Actions runs `composer lint` and the end-to-end test on Nextcloud 34, 35 and master for
every pull request, and monthly against the newest Nextcloud release. Psalm runs against the
`nextcloud/ocp` of the oldest and the newest Nextcloud that `info.xml` allows.
