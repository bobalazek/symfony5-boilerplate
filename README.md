# Symfony5 boilerplate

![CI](https://github.com/bobalazek/symfony5-boilerplate/workflows/Development%20Workflow/badge.svg)

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
  * User blocking
  * Account data export
  * Account deletion
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
  * Admin user impersonation (allows admins to impersonate as other users)
* Moderation
  * User locking & deletion (allow moderators with certain roles to lock or delete the users)
* Administration (EasyAdmin3)
* Multi language support
* Contact form

## Resources

* [Setup](docs/setup.md)
* [Development](docs/development.md)
* [Commands](docs/commands.md)
