# Personal Dev Environment 

Local development setup with Docker for WordPress (Bedrock) and Drupal projects.

## Prerequisites

- Docker & Docker Compose

## Quick Start

### WordPress (Bedrock)

```bash
cd wordpress
cp .env.example .env
```

Edit `.env` — update at minimum:
- `DB_PASSWORD` — database password
- `WP_HOME` — site URL (default `http://localhost:8080`)
- Salt keys — generate at https://roots.io/salts.html

```bash
docker compose up -d --build
```

Once containers are running, complete the installation:

**Option A — Browser:** Open http://localhost:8080 and follow the installation wizard.

**Option B — WP-CLI:**
```bash
docker exec bedrock-wp wp core install \
  --url="http://localhost:8080" \
  --title="My Site" \
  --admin_user=admin \
  --admin_password=your_password \
  --admin_email=you@example.com \
  --allow-root \
  --path=/var/www/html/web/wp
```

Install a theme (Bedrock ships without one):
```bash
docker exec bedrock-wp wp theme install twentytwentyfour --activate \
  --allow-root --path=/var/www/html/web/wp
```

| Service     | URL                    |
|-------------|------------------------|
| WordPress   | http://localhost:8080   |
| Admin       | http://localhost:8080/wp/wp-admin/ |
| phpMyAdmin  | http://localhost:8081   |
| MariaDB     | localhost:3306         |

### Drupal

```bash
cd drupal
cp .env.example .env
```

Edit `.env` — update at minimum:
- `DB_PASSWORD` — database password
- `DRUPAL_HASH_SALT` — generate a random string

```bash
docker compose up -d --build
```

Once containers are running, complete the installation:

**Option A — Browser:** Open http://localhost:8082 and follow the installation wizard.

**Option B — Drush:**
```bash
docker exec drupal-app vendor/bin/drush site:install standard \
  --db-url=mysql://drupal:secret_change_me@db:3306/drupal \
  --site-name="My Drupal Site" \
  --account-name=admin \
  --account-pass=your_password \
  --account-mail=you@example.com \
  -y
```

| Service     | URL                    |
|-------------|------------------------|
| Drupal      | http://localhost:8082   |
| phpMyAdmin  | http://localhost:8083   |
| MariaDB     | localhost:3307         |

> **Note:** Drupal uses port `3307` on the host to avoid collisions with WordPress. Inside the container, the DB listens on the standard port `3306` — `DB_PORT` controls the internal connection, `DB_EXTERNAL_PORT` controls the host mapping.

## Stack

Both projects share the same base stack:

- **PHP 8.3** (Apache)
- **MariaDB 11** (Jammy)
- **Composer 2**
- **phpMyAdmin**

## CI/CD (GitHub Actions)

Each project has its own workflow (`.github/workflows/`) that runs on push/PR to `main` when files in its directory change.

### Pipeline stages

```
Build Image  -->  Smoke Test  -->  Security Scan  -->  Deploy
                  (parallel)      (parallel)           (only on push to main)
```

- **Build** — Builds the Docker image with Buildx + layer caching. Pushes to Docker Hub on merge to `main`.
- **Smoke Test** — Spins up the full stack (app + db + pma), verifies HTTP responses, DB connectivity, PHP extensions, and Apache modules.
- **Security Scan** — Runs [Trivy](https://github.com/aquasecurity/trivy) to detect CRITICAL/HIGH/MEDIUM vulnerabilities in the image.
- **Deploy** — SSHs into the server, pulls the new image, restarts containers. Includes post-deploy verification and automatic rollback on failure.

### GitHub Secrets

Configure these in **Settings > Secrets and variables > Actions**:

| Secret | Description | How to obtain |
|--------|-------------|---------------|
| `DOCKERHUB_USERNAME` | Docker Hub username | Your Docker Hub account username |
| `DOCKERHUB_TOKEN` | Docker Hub access token | Docker Hub > Account Settings > Security > New Access Token |
| `DEPLOY_HOST` | Server hostname or IP | Your server's public IP or domain (e.g. `203.0.113.10`) |
| `DEPLOY_USER` | SSH username on the server | The user that runs Docker on the target server (e.g. `deploy`) |
| `DEPLOY_SSH_KEY` | SSH private key for deployment | Generate with `ssh-keygen -t ed25519 -C "github-actions"`, add the public key to the server's `~/.ssh/authorized_keys`, paste the private key as the secret value |
| `DEPLOY_PATH` | Absolute path to the project root on the server | e.g. `/opt/apps/personal` — the directory containing the `wordpress/` and `drupal/` folders |

### Required environment

Create a `production` environment in **Settings > Environments** to enable the deploy job. Optionally add approval gates for manual deploy confirmation.

## Stopping

```bash
# From each project directory:
docker compose down

# To also remove volumes (database data):
docker compose down -v
```
