#!/usr/bin/env bash
#
# Push each packages/* subtree to its split repo (Symfony-style: same tag everywhere).
#
# Usage:
#   ./scripts/split-packages.sh                         # main only (or tag if HEAD has v*)
#   ./scripts/split-packages.sh --tag v1.0.0            # main + tag v1.0.0 on all splits
#   ./scripts/split-packages.sh --tag v1.0.0 extension-http # one package
#   ./scripts/split-packages.sh --dry-run --tag v1.0.0
#
# Typical release:
#   git tag v1.0.0 && git push origin main v1.0.0
#   ./scripts/split-packages.sh --tag v1.0.0
#
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ORG="php-concept-extensions"
BRANCH="${SPLIT_BRANCH:-main}"
DRY_RUN=0
TAG=""
FILTERS=()

while [[ $# -gt 0 ]]; do
    case "$1" in
        --dry-run)
            DRY_RUN=1
            shift
            ;;
        --tag)
            TAG="${2:-}"
            if [[ -z "$TAG" ]]; then
                echo "--tag requires a value (e.g. v1.0.0)" >&2
                exit 1
            fi
            shift 2
            ;;
        -h|--help)
            sed -n '3,14p' "$0"
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

if ! git -C "$ROOT" rev-parse HEAD >/dev/null 2>&1; then
    echo "Monorepo needs at least one commit before split." >&2
    exit 1
fi

cd "$ROOT"

# If no --tag, reuse a single v* tag that points at HEAD (release checkout).
if [[ -z "$TAG" ]]; then
    mapfile -t HEAD_TAGS < <(git tag --points-at HEAD | grep -E '^v[0-9]' || true)
    if [[ ${#HEAD_TAGS[@]} -eq 1 ]]; then
        TAG="${HEAD_TAGS[0]}"
        echo "Using tag on HEAD: $TAG"
    elif [[ ${#HEAD_TAGS[@]} -gt 1 ]]; then
        echo "Multiple v* tags on HEAD (${HEAD_TAGS[*]}). Pass --tag explicitly." >&2
        exit 1
    fi
fi

if [[ -n "$TAG" && ! "$TAG" =~ ^v[0-9] ]]; then
    echo "Tag must look like v1.0.0 (got: $TAG)" >&2
    exit 1
fi

matches_filter() {
    local repo="$1"
    if [[ ${#FILTERS[@]} -eq 0 ]]; then
        return 0
    fi
    local f
    for f in "${FILTERS[@]}"; do
        if [[ "$repo" == "$f" ]]; then
            return 0
        fi
    done
    return 1
}

while IFS=$'\t' read -r prefix repo; do
    if ! matches_filter "$repo"; then
        continue
    fi

    split_branch="split/$repo"
    remote="git@github.com:$ORG/$repo.git"

    echo "==> $prefix -> $ORG/$repo"

    if [[ $DRY_RUN -eq 1 ]]; then
        echo "  git subtree split --prefix=$prefix -b $split_branch"
        echo "  git push $remote $split_branch:$BRANCH --force"
        if [[ -n "$TAG" ]]; then
            echo "  git push $remote <split-sha>:refs/tags/$TAG"
        fi
        continue
    fi

    git branch -D "$split_branch" 2>/dev/null || true
    git subtree split --prefix="$prefix" -b "$split_branch"
    git push "$remote" "$split_branch:$BRANCH" --force

    if [[ -n "$TAG" ]]; then
        sha="$(git rev-parse "$split_branch")"
        echo "  tag $TAG -> $sha"
        git push "$remote" "$sha:refs/tags/$TAG"
    fi
done < <(php -r '
    $map = json_decode(file_get_contents("packages.json"), true)["packages"];
    foreach ($map as $path => $repo) {
        echo $path, "\t", $repo, "\n";
    }
')

echo "Done."
if [[ -n "$TAG" ]]; then
    echo "Split repos tagged: $TAG"
fi
