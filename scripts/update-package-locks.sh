#!/usr/bin/env bash
#
# Update composer.lock for packages/* (and optionally the monorepo root).
#
# Usage (prefer inside Docker):
#   docker compose exec app ./scripts/update-package-locks.sh
#   docker compose exec app ./scripts/update-package-locks.sh error-handler-whoops
#   docker compose exec app ./scripts/update-package-locks.sh --root
#   docker compose exec app ./scripts/update-package-locks.sh --dry-run http
#
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DRY_RUN=0
DO_ROOT=0
FILTERS=()

while [[ $# -gt 0 ]]; do
    case "$1" in
        --dry-run)
            DRY_RUN=1
            shift
            ;;
        --root)
            DO_ROOT=1
            shift
            ;;
        -h|--help)
            sed -n '3,12p' "$0"
            exit 0
            ;;
        -*)
            echo "Unknown option: $1" >&2
            exit 1
            ;;
        *)
            FILTERS+=("$1")
            shift
            ;;
    esac
done

if ! command -v composer >/dev/null 2>&1; then
    echo "composer not found (run inside Docker: docker compose exec app ...)" >&2
    exit 1
fi

matches_filter() {
    local name="$1"
    if [[ ${#FILTERS[@]} -eq 0 ]]; then
        return 0
    fi
    local f
    for f in "${FILTERS[@]}"; do
        if [[ "$name" == "$f" || "$name" == "extension-$f" || "extension-$name" == "$f" ]]; then
            return 0
        fi
    done
    return 1
}

run_update() {
    local dir="$1"
    local label="$2"

    if [[ $DRY_RUN -eq 1 ]]; then
        echo "dry-run: composer update --no-install in $label"
        return 0
    fi

    echo "==> $label"
    (cd "$dir" && composer update --no-install)
}

cd "$ROOT"

shopt -s nullglob
for composer_json in packages/*/composer.json; do
    pkg_dir="$(dirname "$composer_json")"
    pkg_name="$(basename "$pkg_dir")"

    if ! matches_filter "$pkg_name"; then
        continue
    fi

    run_update "$pkg_dir" "packages/$pkg_name"
done

if [[ $DO_ROOT -eq 1 ]]; then
    if [[ ${#FILTERS[@]} -gt 0 ]]; then
        echo "Note: --root ignores package filters; updating monorepo root lock."
    fi
    run_update "$ROOT" "monorepo root"
fi
