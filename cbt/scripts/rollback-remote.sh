#!/usr/bin/env bash
set -Eeuo pipefail

app_base="${1:?app base required}"
public_path="${2:?public path required}"
previous_file="${app_base}/shared/previous_release"
test -f "$previous_file"
previous_release="$(cat "$previous_file")"

case "$previous_release" in
  "$app_base"/releases/*) ;;
  *) echo "No safe previous release available" >&2; exit 1 ;;
esac

test -d "$previous_release"
ln -sfn "$previous_release" "${app_base}/current.next"
mv -Tf "${app_base}/current.next" "${app_base}/current"
rsync -a --delete --exclude='.well-known/' "${previous_release}/public/" "${public_path}/"
echo "Rolled back to: ${previous_release}"
