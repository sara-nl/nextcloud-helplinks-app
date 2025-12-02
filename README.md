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

