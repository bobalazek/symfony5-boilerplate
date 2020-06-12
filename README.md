# Corcoviewer

![CI](https://github.com/bobalazek/corcoviewer/workflows/Development%20Workflow/badge.svg)

The Corcoviewer project.

## Features

* User system
  * Login
  * Register
  * Password reset
  * Settings
  * Notifications
  * Messaging
  * Profile
    * Following, followers & follower requests
    * Blocking
  * OAuth
    * Facebook
    * Twitter
  * Two-factor authentication/TFA
    * Email
    * Google authenticator
    * Recovery codes
  * Action auditing (logs every action the user does: login, reset password, setting changes, ...)
  * Device sessions (allows the user to invalidate sessions from current or other devices they are logged in)
  * Points (allows awarding points to users)
* Moderation
  * User locking & deletion (allow moderators with certain roles to lock or delete the users)
* Administration via EasyAdmin
* Multi language ready
* Contact form

## Resources

* [Setup](docs/setup.md)
* [Development](docs/development.md)
* [Commands](docs/commands.md)
