#!/bin/bash
# Builds a bb-theme ZIP and uploads it to R2.
# Usage: ./scripts/build.sh <pr_number> <version_slug> <dest_prefix> [source_dir]
#   dest_prefix:  "branch" or "changelog"
#   source_dir:   path to the source to zip (defaults to GITHUB_WORKSPACE)
# Required env vars: R2_BUCKET, R2_ENDPOINT, GITHUB_WORKSPACE

set -e

PR_NUMBER=$1
VERSION_SLUG=$2
DEST_PREFIX=$3
SOURCE_DIR=${4:-$GITHUB_WORKSPACE}
SLUG="bb-theme"
WORK_DIR=/tmp/bb-zip-work

# Rsync source to a clean working directory. less/, json/, build/ and css/ are
# runtime assets and ship; only dev tooling is excluded.
rm -rf "$WORK_DIR"
mkdir -p "$WORK_DIR/$SLUG"

rsync -a "$SOURCE_DIR/" "$WORK_DIR/$SLUG/" \
  --exclude='.babelrc' \
  --exclude='.editorconfig' \
  --exclude='.eslintignore' \
  --exclude='.eslintrc.json' \
  --exclude='eslint.config.js' \
  --exclude='.git' \
  --exclude='.gitattributes' \
  --exclude='.gitignore' \
  --exclude='.github' \
  --exclude='.npmrc' \
  --exclude='.DS_Store' \
  --exclude='Thumbs.db' \
  --exclude='composer.json' \
  --exclude='composer.lock' \
  --exclude='CLAUDE.md' \
  --exclude='agents/' \
  --exclude='gulpfile.js' \
  --exclude='webpack.config.js' \
  --exclude='package.json' \
  --exclude='package-lock.json' \
  --exclude='yarn.lock' \
  --exclude='phpcs.xml' \
  --exclude='phpunit.xml' \
  --exclude='node_modules/' \
  --exclude='scripts/' \
  --exclude='src/' \
  --exclude='*.zip'

# Token replacements. {FL_THEME_VERSION} is baked wherever it is a real value.
# It is deliberately NOT replaced in classes/class-fl-theme-update.php: that file
# uses the literal '{FL_THEME_VERSION}' as a dev sentinel
# ( '{FL_THEME_VERSION}' === FL_THEME_VERSION means "unbuilt checkout, skip
# migrations" ). Replacing it there would make the guard always true and disable
# the theme's update migrations in the built copy, so that file is left alone.
sed -i "s/{FL_THEME_VERSION}/${VERSION_SLUG}/g" \
  "$WORK_DIR/$SLUG/functions.php" \
  "$WORK_DIR/$SLUG/style.css" \
  "$WORK_DIR/$SLUG/includes/updater-config.php"

sed -i "s/{FL_THEME_NAME}/Beaver Builder Theme/g" \
  "$WORK_DIR/$SLUG/style.css" \
  "$WORK_DIR/$SLUG/includes/updater-config.php"

# Zip and upload
cd "$WORK_DIR"
zip -r "${SLUG}.zip" "$SLUG"
aws s3 cp "${SLUG}.zip" \
  "s3://${R2_BUCKET}/pr-${PR_NUMBER}/${DEST_PREFIX}/${SLUG}.zip" \
  --endpoint-url "$R2_ENDPOINT"

echo "${SLUG} uploaded to R2 (${DEST_PREFIX})"

rm -rf "$WORK_DIR"
