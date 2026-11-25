# Upgrade from Drupal 9 to Drupal 11

This document gives the procedure to upgrade a Drupal 9.5 database (release `v2.0.2` or earlier) to Drupal 11.

Drupal 11 does not accept a Drupal 9 database. You must upgrade to Drupal 10 first.

| Step | Git reference | Drupal core | PHP image |
| ---- | ------------- | ----------- | --------- |
| Start | `v2.0.2` | 9.5.1 | 8.1 |
| 1 | `2.0.0` | 10.6.9 | 8.3 |
| 2 | `develop` (release 3.0.0 or later) | 11.5 | 8.5 |

Note: The tag `2.0.0` (no `v` prefix) is newer than the tag `v2.0.2`.

The procedure was tested on 2026-11-25 with a production database dump from 2026-11-17.

## Before you start

- Make a backup of the database.
- Make sure that `web/sites/default/settings.local.php` has the database connection and the hash salt.
- Make sure that the git working tree is clean.

To do a test with a database dump, load the dump first:

```sh
docker compose exec phpfpm vendor/bin/drush sql:drop --yes
docker compose exec -T phpfpm vendor/bin/drush sql:cli < dump.sql
```

## Step 1: Upgrade to Drupal 10

```sh
git checkout 2.0.0
docker compose up --detach
docker compose exec phpfpm rm -rf vendor
docker compose exec phpfpm composer install
docker compose exec phpfpm vendor/bin/drush deploy --yes
docker compose exec phpfpm vendor/bin/drush theme:install claro --yes
```

Remove the `vendor` directory before each `composer install`.
The Composer plugins from a different core version can stop the installation.

The `theme:install claro` command is mandatory.
In Drupal 11, `bibbox_theme` uses `claro` as the base theme.
If `claro` is not installed, the database update in step 2 stops with this error:
`Base theme claro has not been installed.`

## Step 2: Upgrade to Drupal 11

```sh
git checkout develop
docker compose up --detach
docker compose exec phpfpm rm -rf vendor
docker compose exec phpfpm composer install
docker compose exec phpfpm vendor/bin/drush deploy --yes
```

The database update shows this error:
`Incompatible themes ... Adminimal, Seven`.
This is correct.
The `--yes` option continues the update, and the configuration import removes the two themes.

## Step 3: Examine the result

```sh
docker compose exec phpfpm vendor/bin/drush status
docker compose exec phpfpm vendor/bin/drush updatedb:status
docker compose exec phpfpm vendor/bin/drush config:status
```

These are the correct results:

- `drush status` shows Drupal 11 and a successful bootstrap.
- `drush updatedb:status` shows `No database updates required`.
- `drush config:status` shows only `views.settings` as different. The difference is only the sequence of the keys.

Log in with `drush uli`. Make sure that the content list and a machine edit page open.

## Known conditions

- Drupal 11.5 moves the `text_with_summary` field type into a new `text_with_summary` module.
  The configuration in `config/sync` must enable this module.
  If it does not, the configuration import in step 2 stops with this error:
  `The Long text with summary field type is used in the following field: node.body`.
- Drupal 11.5 reports the `claro` theme and the `text_with_summary` module as deprecated.
  They continue to operate in Drupal 11. Replace them before an upgrade to Drupal 12.
