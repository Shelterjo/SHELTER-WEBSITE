#!/usr/bin/env bash
# SHELTER — release steps that run ON the Cloudways server, as the application's own SSH user (never master).
# Nothing is built here: the release arrives with vendor/ and public/build/ (docs/platform/DEPLOYMENT.md §1).
#
# Layout (inside the application folder the SSH user lands in):
#   public_html/releases/<id>/          one release (code + vendor + built assets)
#   public_html/current -> releases/<id>  the live release; the Cloudways web root is public_html/current/public
#   private_html/shelter/.env             the environment's own secrets (written once by the pipeline, never in Git)
#   private_html/shelter/storage/         uploads, sessions, logs, cache — shared by every release
#   private_html/shelter-public/media/    approved image variants served at /media/ (public/media of every release);
#                                         outside the 700 folder because the web server (group www-data) reads them
#
# Modes: the web server (nginx, group www-data) reads the release through the group (dirs 750, files 640); PHP runs as
# the application user. What holds secrets stays owner-only: private_html/shelter (700), .env (600) and the cached
# config in bootstrap/cache (700/600).
#
# Steps (the pipeline checks the web root between `stage` and `activate`, so no secret is ever linked into a
# folder the web server would hand out as a file):
#   stage <id> <tarball>   unpack a release; on the very first run also create a placeholder current/public
#   discard <id>           remove a staged release (used when the web-root check fails)
#   activate <id>          link .env + storage + media, back up the database (not on the first run), migrate, seed
#                          once, cache, switch `current`, keep the last 3
#   has-env                exit 0 when the shared .env exists
set -euo pipefail
umask 027

ROOT="${SHELTER_APP_ROOT:-.}"
cd "$ROOT"
if [ ! -d public_html ]; then
    echo "public_html not found in $(pwd): set SHELTER_APP_ROOT to the application folder" >&2
    exit 2
fi
PUB="$(pwd)/public_html"
SHARED="$(pwd)/private_html/shelter"
MEDIA="$(pwd)/private_html/shelter-public/media"
KEEP=3

valid_id() {
    [[ "$1" =~ ^[0-9]{4}\.[0-9]{2}\.[0-9]{2}-[0-9]{6}-[0-9a-f]{7}$ ]] || { echo "bad release id: $1" >&2; exit 2; }
}

cmd="${1:-}"
case "$cmd" in
    has-env)
        [ -f "$SHARED/.env" ]
        ;;

    stage)
        id="${2:?release id}"; tarball="${3:?tarball}"
        valid_id "$id"
        mkdir -p "$PUB/releases/$id"
        tar -xzf "$tarball" -C "$PUB/releases/$id"
        rm -f "$tarball"
        # The archive root is the build's private temp folder (700); the web server must be able to enter it.
        chmod 750 "$PUB/releases/$id"
        if [ ! -e "$PUB/current" ]; then
            # First run: a harmless page so the web root can be set to an existing folder.
            mkdir -p "$PUB/current/public"
            printf '%s\n' '<!doctype html><meta charset="utf-8"><title>SHELTER staging</title><p>Staging is being prepared.</p>' > "$PUB/current/public/index.html"
        fi
        echo "staged $id"
        ;;

    discard)
        id="${2:?release id}"
        valid_id "$id"
        rm -rf "${PUB:?}/releases/$id"
        echo "discarded $id"
        ;;

    activate)
        id="${2:?release id}"
        valid_id "$id"
        release="$PUB/releases/$id"
        [ -d "$release" ] || { echo "release $id is not staged" >&2; exit 2; }
        [ -f "$SHARED/.env" ] || { echo "no shared .env yet" >&2; exit 2; }
        chmod 600 "$SHARED/.env"
        mkdir -p "$SHARED/storage/app/public" "$SHARED/storage/app/private" "$SHARED/storage/framework/cache/data" \
            "$SHARED/storage/framework/sessions" "$SHARED/storage/framework/views" "$SHARED/storage/logs"

        cd "$release"
        rm -rf storage
        ln -s "$SHARED/storage" storage
        ln -sfn "$SHARED/.env" .env
        mkdir -p bootstrap/cache "$MEDIA"
        if [ -d "$SHARED/media-public" ]; then
            # Earlier layout (inside the 700 folder, unreadable by the web server): move what is there.
            cp -a "$SHARED/media-public/." "$MEDIA/" && rm -rf "${SHARED:?}/media-public"
        fi
        chmod 750 "$(dirname "$MEDIA")" "$MEDIA"
        # Approved image variants are made on the server (MediaLibrary): one shared folder, so a release keeps them.
        rm -rf public/media
        ln -s "$MEDIA" public/media

        if [ -f "$SHARED/.seeded" ]; then
            # DEPLOY-005: a copy of the database before any migration; a failed backup stops the release here.
            php artisan ops:backup-db --reason=pre-deploy --no-interaction
        fi
        php artisan migrate --force --no-interaction
        if [ ! -f "$SHARED/.seeded" ]; then
            # Approved master data and the menu, once per environment (the seeders never overwrite an Owner edit).
            php artisan db:seed --force --no-interaction
            php artisan search:rebuild --no-interaction
            touch "$SHARED/.seeded"
        fi
        # No storage:link: the public disk is unused and /storage/ is reserved (deploy-facts §e).
        rm -f public/storage
        php artisan optimize --no-interaction
        # The cached config holds every secret of .env: owner only.
        chmod 700 bootstrap/cache
        find bootstrap/cache -maxdepth 1 -type f -name '*.php' -exec chmod 600 {} +

        # Switch atomically; the first-run placeholder is a real folder and is replaced once.
        ln -sfn "releases/$id" "$PUB/current.next"
        if [ -d "$PUB/current" ] && [ ! -L "$PUB/current" ]; then
            rm -rf "${PUB:?}/current"
        fi
        mv -Tf "$PUB/current.next" "$PUB/current"
        php artisan queue:restart --no-interaction >/dev/null 2>&1 || true

        # Keep the last $KEEP releases; never the live one.
        live="$(readlink "$PUB/current")"
        mapfile -t old < <(ls -1dt "$PUB"/releases/*/ 2>/dev/null | tail -n +$((KEEP + 1)))
        for dir in "${old[@]}"; do
            [ "releases/$(basename "$dir")" = "$live" ] || rm -rf "$dir"
        done
        echo "activated $id"
        ;;

    *)
        echo "usage: remote-release.sh stage|discard|activate|has-env ..." >&2
        exit 2
        ;;
esac
