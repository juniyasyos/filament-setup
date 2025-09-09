#!/usr/bin/env bash
set -euo pipefail

# Usage:
#   tools/prepare-dev-package.sh vendor/package [target-version]
#
# Example:
#   tools/prepare-dev-package.sh pxlrbt/filament-excel 3.1.0
#   tools/prepare-dev-package.sh vendor/package 4.0.0
#
# Copies an installed Composer package from vendor/ into packages/ for local development,
# and assigns a stable version so Composer can resolve it via the path repository.

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 vendor/package [target-version]" >&2
  exit 1
fi

PKG="$1"
TARGET_VERSION="${2:-}"

if [[ "$PKG" != */* ]]; then
  echo "Package name must be in vendor/package format" >&2
  exit 1
fi

VENDOR="${PKG%%/*}"
NAME="${PKG#*/}"

SRC="vendor/$VENDOR/$NAME"
DST="packages/$VENDOR/$NAME"

if [[ ! -d "$SRC" ]]; then
  echo "Source package not found: $SRC. Install it first (composer require $PKG)." >&2
  exit 1
fi

mkdir -p "packages/$VENDOR"

echo "Copying $SRC -> $DST ..."
rm -rf "$DST"
cp -a "$SRC" "$DST"

# Clean vendor + lock from copied package
rm -rf "$DST/vendor" || true
rm -f "$DST/composer.lock" || true
rm -rf "$DST/.git" || true

PKG_COMPOSER="$DST/composer.json"
if [[ ! -f "$PKG_COMPOSER" ]]; then
  echo "No composer.json found in $DST, creating a minimal one..."
  cat > "$PKG_COMPOSER" <<JSON
{
  "name": "$VENDOR/$NAME",
  "description": "$PKG (local dev fork)",
  "type": "library",
  "license": "MIT",
  "autoload": { "psr-4": { "": "src/" } }
}
JSON
fi

# Package name in composer.json should already be correct from vendor; skipping.

# Insert or update a stable version so Composer accepts the path package without VCS
if [[ -n "$TARGET_VERSION" ]]; then
  echo "Setting version=$TARGET_VERSION in $PKG_COMPOSER"
  if grep -q '"version"' "$PKG_COMPOSER"; then
    sed -i "s/\"version\"[[:space:]]*:[[:space:]]*\"[^\"]*\"/\"version\": \"$TARGET_VERSION\"/" "$PKG_COMPOSER"
  else
    sed -i '0,/^{/s//{\
  "version": "'"$TARGET_VERSION"'",/' "$PKG_COMPOSER"
  fi
fi

cat <<EOM

Done. Local dev package prepared at: $DST

Next steps:
1) Edit $PKG_COMPOSER if needed:
   - Ensure \"version\" is a stable value matching your root constraint (e.g. 4.0.0).
   - If upgrading to Filament v4, bump any \"filament/*\" constraints to \"^4.0\".
2) In the project root, keep this repository config (already present):
   composer.json -> "repositories": [ { "type": "path", "url": "packages/*/*", "options": { "symlink": true } } ]
3) Run: composer update -W
   Composer will prefer the local path package over Packagist for $PKG.

Tip: Commit only your changes; vendors inside packages/ are ignored via .gitignore.
EOM
