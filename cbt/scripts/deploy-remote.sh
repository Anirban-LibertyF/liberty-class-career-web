#!/usr/bin/env bash
set -Eeuo pipefail

release_id="${1:?release id required}"
archive_path="${2:?archive path required}"
app_base="${3:?app base required}"
public_path="${4:?public path required}"
release_path="${app_base}/releases/${release_id}"

case "$release_path" in
  "$app_base"/releases/*) ;;
  *) echo "Unsafe release path" >&2; exit 1 ;;
esac

test -f "${app_base}/shared/.env"
mkdir -p "$release_path" "$public_path"
tar -xzf "$archive_path" -C "$release_path"
ln -s "${app_base}/shared/.env" "${release_path}/.env"
ln -s "${app_base}/shared/storage" "${release_path}/storage"

php "${release_path}/scripts/migrate.php"

previous_release=""
if test -L "${app_base}/current"; then
  previous_release="$(readlink "${app_base}/current")"
fi
printf '%s' "$previous_release" > "${app_base}/shared/previous_release"

ln -sfn "$release_path" "${app_base}/current.next"
mv -Tf "${app_base}/current.next" "${app_base}/current"
rsync -a --delete --exclude='.well-known/' "${release_path}/public/" "${public_path}/"

rm -f "$archive_path"
echo "Deployment active: ${release_path}"
