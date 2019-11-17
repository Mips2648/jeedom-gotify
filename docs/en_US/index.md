---
layout: default
title: Index
lang: en_US
---

# Description

Plugin allowing Jeedom to act as a Gotify application that can send messages (notifications) to a Gotify server. You will find more details in <a href="https://gotify.net/docs/" target="_blank">Gotify's documentation</a> .

The advantage of this system is that it can be self-hosted (a docker container is enough), your data remains in your possession.

# Installation

In order to use the plugin, you must download, install and activate it like any Jeedom plugin.

It is needed to already have a Gotify server setup, the installation will not be detailed here because it is very clearly explained on the site.

# Plugin configuration

In the configuration of the plugin it will be necessary to setup the URL of the Gotify server in the format:

```HTTP
http://yourdomain.com:32768
```

# Device configuration

Après avoir créé un nouvel équipement, les options habituelles sont disponibles.
Il faudra également renseigner le token de l'application que vous aurez précédemment créé dans Gotify.
Donc un équipement Jeedom correspond à une application Gotify.

# Changelog

[Voir le changelog](./changelog)

# Support

Si malgré cette documentation et après voir lu les sujets en rapport avec le plugin sur [community]({{site.forum}}) vous ne trouvez pas de réponse à votre question, n'hésitez pas à créer un nouveau sujet en n'oubliant pas de mettre le tag du plugin ({{site.tagPlugin}}).
