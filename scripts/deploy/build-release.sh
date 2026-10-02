#!/usr/bin/env bash
# SHELTER — builds one release artifact from a commit (docs/platform/DEPLOYMENT.md §1: built once, the same file goes
# to Staging and later Production; nothing is built on the server). Output: <out>/shelter-<id>.tar.gz + .sha256 and
# release.json inside the archive. No secret is read or written here.
#   usage: build-release.sh <out-dir> [commit]
set -euo pipefail

out="${1:?output directory}"
commit="${2:-HEAD}"
sha="$(git rev-parse "$commit")"
id="$(date -u +%Y.%m.%d-%H%M%S)-${sha:0:7}"
mkdir -p "$out"
out="$(cd "$out" && pwd)"
work="$(mktemp -d)"
trap 'rm -rf "$work"' EXIT

git archive --format=tar "$sha" | tar -x -C "$work"
cd "$work"

composer install --no-dev --no-interaction --no-progress --prefer-dist --optimize-autoloader
npm ci --no-audit --no-fund
npm run build

# Runtime only: no tests, tooling, docs, sources of the build or Node modules on the server — except the menu files the
# seeder reads on the first release (named in database/seeders/data/menu.php, so the list cannot drift).
keep="$(mktemp -d)"
for file in $(php -r '$m = require "database/seeders/data/menu.php"; echo $m["inventory_csv"], "\n", $m["subcategory_csv"], "\n";'); do
    mkdir -p "$keep/$(dirname "$file")" && cp "$file" "$keep/$file"
done
rm -rf node_modules tests tooling docs .github .githooks .storybook storybook-static design-system/stories \
    scripts/registers reports public/hot
cp -R "$keep/docs" docs && rm -rf "$keep"
find . -maxdepth 1 -type f \( -name '*.md' -o -name 'phpunit.xml' -o -name 'phpstan*.neon*' -o -name 'pint.json' \
    -o -name '.semgrepignore' -o -name '.gitleaks*' -o -name 'vitest.config.*' -o -name 'eslint.config.*' \) -delete

printf '{"release_id":"%s","commit":"%s","built_at":"%s"}\n' "$id" "$sha" "$(date -u +%Y-%m-%dT%H:%M:%SZ)" > release.json

tar -czf "$out/shelter-$id.tar.gz" .
(cd "$out" && sha256sum "shelter-$id.tar.gz" > "shelter-$id.tar.gz.sha256")
echo "$id"
