#!/usr/bin/env bash
#
# Create empty split repos in php-concept-extensions org (one per package).
# Run once before first split push.
#
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ORG="php-concept-extensions"

if ! command -v gh >/dev/null 2>&1; then
    echo "gh CLI required" >&2
    exit 1
fi

mapfile -t REPOS < <(ROOT="$ROOT" php -r '
    $map = json_decode(file_get_contents(getenv("ROOT") . "/packages.json"), true)["packages"];
    foreach ($map as $path => $repo) {
        echo $repo, "\n";
    }
')

for repo in "${REPOS[@]}"; do
    if gh repo view "$ORG/$repo" >/dev/null 2>&1; then
        echo "exists: $ORG/$repo"
        continue
    fi

    gh repo create "$ORG/$repo" \
        --public \
        --description "Concept extension (split from concept-extensions monorepo)" \
        --add-readme
    echo "created: $ORG/$repo"
done
