Numerology
============

A Symfony project created on January 13, 2019, 7:44 pm.

## Purpose :
Automatically calculate a person's numerology theme.

## Roadmap
- ~~History~~
- ~~Homepage~~
- Fill analysis with book's data
- ~~Graphic redesign~~
- ~~Users' list~~
- ~~Basic log in~~
- ~~Auth0~~
- ~~Roles' managing~~
- Internationalization
- ~~Chat~~
- ~~Data editing~~
- ~~PDF export~~
- Comparison
- ~~Notifications~~

## How to
- Create an account in Slack with the Gmail alias and customer's real name
- Copy this new Slack user's id (in profile)
- Create an Auth0 account with customer's email
- Fill Auth0's user_metadata with :
```json
{
    "slack_id": "XXX"
}
```
- Fill Auth0's app_metadata with :
```json
{
    "roles": [
        "ROLE_USER"
    ]
}
```
- Done

## Dev
Add a pre-commit hook with this script to automatically update :
```bash
#!/bin/bash

echo "$(date +%d\ %b\ %Y)" > ./app/Resources/views/latest.html.twig
```