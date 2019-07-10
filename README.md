Numerology
============

A Symfony project created on January 13, 2019, 7:44 pm.

## Purpose :
Generate a person's numerology theme.

## Roadmap
| #  | priority (1 to 3) | status | label                                           |
|----|-------------------|--------|-------------------------------------------------|
| 1  | -                 | -      | History                                         |
| 2  | -                 | -      | Homepage                                        |
| 3  | -                 | -      | Fill analysis with book's data                  |
| 4  | -                 | -      | Graphic redesign                                |
| 5  | -                 | -      | Users' list                                     |
| 6  | -                 | -      | Basic log in                                    |
| 7  | -                 | -      | Auth0                                           |
| 8  | -                 | -      | Roles' managing                                 |
| 9  | 3                 | `todo` | Internationalization                            |
| 10 | -                 | -      | Chat                                            |
| 11 | -                 | -      | Data editing                                    |
| 12 | -                 | -      | PDF export public site                          |
| 13 | -                 | -      | PDF export extranet                             |
| 14 | -                 | -      | Comparison                                      |
| 15 | -                 | -      | Notifications                                   |
| 16 | -                 | -      | Paypal payment                                  |
| 17 | -                 | -      | Admin UI to manage numbers' details             |
| 18 | -                 | -      | Admin UI to manage definitions                  |
| 19 | -                 | -      | Roles management                                |
| 20 | -                 | -      | Numbers total to improve its admin UI           |
| 21 | 2                 | -      | Analysis example attribute                      |
| 22 | 1                 | -      | Button on extranet show page to share front url |
| 23 | 1                 | -      | Lifepath extranet block visual redesign         |
| 24 | 1                 | `todo` | Identity extranet block visual redesign         |
| 25 | 2                 | -      | Review PDF letters block and redesign           |
| 26 | 1                 | `todo` | Add recaptcha to front forms                    |
| 27 | 1                 | `todo` | Work on birth field to avoid uncertainties      |

## How to add a new customer
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

## Pro-tip for developers
Add a pre-commit hook with this script to automatically update pages' shared footer :
```bash
#!/bin/bash

echo "$(date +%d\ %b\ %Y)" > ./app/Resources/views/latest.html.twig
```