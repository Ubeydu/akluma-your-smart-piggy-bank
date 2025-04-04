Perfect. Since you're starting from **zero** with GitHub Actions, and your deployment to Fly.io (staging and production) already works manually, we'll now set up **end-to-end Continuous Deployment (CD)**.

---

## ✅ **Overview of What We’ll Do**

You’ve already done:
- ✅ Manual deployment to `akluma-staging` and `akluma-prod`
- ✅ `fly.staging.toml` and `fly.production.toml` set up correctly

Now we will:
1. **Create `dev` branch** in your local repo and push it
2. **Create GitHub Secrets** for both staging and production Fly.io tokens
3. **Add GitHub Actions workflows** for staging (`main`) and production (`prod`)
4. **Commit and push workflows** to GitHub
5. **Test the setup end-to-end** (optional but recommended)

---

## ✅ **Step-by-step Setup Instructions**

### **🧱 Step 1 — Create the `dev` branch**

> 💻 In your Laravel project directory

```bash
cd ~/path/to/your/laravel/project

# Make sure you're up to date
git fetch origin

# Create dev from main
git checkout main
git pull origin main
git checkout -b dev

# Push dev branch to remote
git push -u origin dev
```

---

### 🔐 **Step 2 — Add Secrets to GitHub**

Go to your repository on GitHub:

> **Settings → Secrets and variables → Actions → New repository secret**

Add the following secrets:

1. **Name:** `FLY_API_TOKEN_STAGING`  
   **Value:** your Fly.io token for `akluma-staging`

2. **Name:** `FLY_API_TOKEN`  
   **Value:** your Fly.io token for `akluma-prod`

> To get tokens, run:
> ```bash
> fly tokens create deploy -a akluma-staging
> ```
> and
> ```bash
> fly tokens create deploy -a akluma-prod
> ```

Save these secrets in GitHub.

---

### ⚙️ **Step 3 — Add GitHub Actions Workflows**

In your Laravel project:

> 💻 In your Laravel project root

```bash
mkdir -p .github/workflows
```

#### `fly-staging.yml`

> 📄 Create file: `.github/workflows/fly-staging.yml`

```yaml
name: Fly Staging Deploy

on:
    push:
        branches:
            - main

jobs:
    deploy:
        name: Deploy to Staging
        runs-on: ubuntu-latest
        steps:
            - uses: actions/checkout@v4
            - uses: superfly/flyctl-actions/setup-flyctl@master

            - name: Deploy to Fly.io
              run: flyctl deploy --remote-only -c fly.staging.toml
              env:
                  FLY_API_TOKEN: ${{ secrets.FLY_API_TOKEN_STAGING }}

            - name: Ensure Laravel required directories exist
              run: |
                  flyctl ssh console -a akluma-staging --command 'sh -c "
                    mkdir -p storage/framework/views &&
                    mkdir -p storage/framework/cache &&
                    mkdir -p storage/framework/sessions &&
                    mkdir -p storage/framework/testing &&
                    mkdir -p bootstrap/cache &&
                    chmod -R 775 storage bootstrap/cache
                  "'
              env:
                  FLY_API_TOKEN: ${{ secrets.FLY_API_TOKEN_STAGING }}

            - name: Laravel Optimize
              run: |
                  flyctl ssh console -a akluma-staging --command 'sh -c "
                    php artisan optimize:clear &&
                    php artisan config:cache &&
                    php artisan view:cache &&
                    php artisan route:cache
                  "'
              env:
                  FLY_API_TOKEN: ${{ secrets.FLY_API_TOKEN_STAGING }}

            - name: Run Migrations
              run: flyctl ssh console -a akluma-staging --command "php artisan migrate --force"
              env:
                  FLY_API_TOKEN: ${{ secrets.FLY_API_TOKEN_STAGING }}


```

#### `fly-prod.yml`

> 📄 Create file: `.github/workflows/fly-prod.yml`

```yaml
name: Fly Production Deploy

on:
  push:
    branches:
      - prod

jobs:
  deploy:
    name: Deploy to Production
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: superfly/flyctl-actions/setup-flyctl@master
      - run: flyctl deploy --remote-only -c fly.production.toml
        env:
          FLY_API_TOKEN: ${{ secrets.FLY_API_TOKEN }}
      - name: Run Migrations
        run: flyctl ssh console -a akluma-prod --command "php artisan migrate --force"
        env:
          FLY_API_TOKEN: ${{ secrets.FLY_API_TOKEN }}
```

---

### 💾 **Step 4 — Commit and Push Workflows**

> 💻 In your Laravel project root

```bash
git add .github/workflows
git commit -m "Add GitHub Actions for Fly staging and production deploy"
git push origin dev
```

> ✅ You’ve now added the CD system to the `dev` branch — the logic is ready but not triggered until you merge `dev` → `main` or `main` → `prod`.

---

### 🧪 **Step 5 — (Optional) Test Full CD Flow**

Would you like to walk through a **test flow** now?

It would go like this:

1. Create a small commit on a `feature/test-deploy` branch
2. Merge it into `dev`, test locally
3. Merge `dev` → `main`, auto deploys to `akluma-staging`
4. Merge `main` → `prod`, auto deploys to `akluma-prod`
