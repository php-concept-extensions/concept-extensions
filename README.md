# Concept Extensions

Monorepo for first-party Concept extensions (`php-concept/extension-*`).

| Role | Repo |
|------|------|
| **Monorepo (source of truth)** | [php-concept-extensions/concept-extensions](https://github.com/php-concept-extensions/concept-extensions) |
| **Split packages** | [php-concept-extensions/extension-*](https://github.com/php-concept-extensions) |

Composer package names stay the same: `php-concept/extension-http`, `php-concept/extension-support`, …

Work only in this monorepo. Split repos are published automatically from `packages/*` (Symfony-style: **one release tag everywhere**).

---

## Layout

```text
concept-extensions/
├── packages/
│   ├── http/                 → php-concept/extension-http
│   ├── support/              → php-concept/extension-support
│   └── …
├── packages.json             # monorepo path → split repo name
├── scripts/
│   ├── create-split-repos.sh # once: create empty GitHub repos (skips existing)
│   └── split-packages.sh     # push subtree (+ optional tag) to splits
└── docker-compose.yml
```

---

## Local development (Docker only)

Sibling:

```text
../concept-core-2
```

```bash
cd /var/www/concept-extensions

docker compose up -d --build
docker compose exec app bash

# inside container:
composer install
composer phpstan
```

Do not run Composer/PHPStan on the host.

---

## Release (tag → push → split)

```bash
git push origin main
git tag v1.0.0
git push origin v1.0.0
```

CI (`.github/workflows/split.yml`) splits every `packages/*` into `php-concept-extensions/extension-*` with the **same** tag.

Local fallback:

```bash
./scripts/split-packages.sh --tag v1.0.0
./scripts/split-packages.sh --tag v1.0.0 extension-http   # one package
```

### CI secret (once)

```bash
gh secret set SPLIT_PUSH_TOKEN --repo php-concept-extensions/concept-extensions
# Fine-grained PAT: Resource owner php-concept-extensions, Contents: Read and write
```

---

## First-time setup

Split repos already exist under the org for most packages. To create any missing ones:

```bash
./scripts/create-split-repos.sh
```

---

## Checklist per release

- [ ] Changes committed on `main`
- [ ] `composer phpstan` green (in Docker)
- [ ] `git push origin main`
- [ ] `git tag vX.Y.Z` && `git push origin vX.Y.Z`
- [ ] Split green (or local `./scripts/split-packages.sh --tag vX.Y.Z`)
- [ ] Packagist shows the new version on split packages
