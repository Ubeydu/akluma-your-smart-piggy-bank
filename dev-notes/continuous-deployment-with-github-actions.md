# ✅ Initial Setup Guide for Continuous Deployment (CD) with GitHub Actions + Fly.io

This guide documents the **one-time setup** steps used to configure end-to-end Continuous Deployment (CD) for the Akluma project using GitHub Actions and Fly.io.

---

## ✅ Overview of What We’ll Do

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

## 🧱 Step 1 — Create the `dev` Branch

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
⚠️ You don’t need to create the `prod` branch yet — it will be created manually later once staging is working.

---

## 🔐 Step 2 — Add Secrets to GitHub

Go to your repository on GitHub:

> **Settings → Secrets and variables → Actions → New repository secret**

Add the following secrets:

1. **Name:** `FLY_API_TOKEN_STAGING`  
   **Value:** your Fly.io token for `akluma-staging`

2. **Name:** `FLY_API_TOKEN`  
   **Value:** your Fly.io token for `akluma-prod`

To get tokens, run:

```bash
fly tokens create deploy -a akluma-staging
```

and

```bash
fly tokens create deploy -a akluma-prod
```

Save these secrets in GitHub.

---

## ⚙️ Step 3 — Add GitHub Actions Workflows

> 💻 In your Laravel project root

```bash
mkdir -p .github/workflows
```

Then create the two files:

### 📄 `.github/workflows/fly-staging.yml`

(*already up to date — not shown here*)

### 📄 `.github/workflows/fly-prod.yml`

(*already up to date — not shown here*)

---

## 💾 Step 4 — Commit and Push Workflows

> 💻 In your Laravel project root

```bash
git add .github/workflows
git commit -m "Add GitHub Actions for Fly staging and production deploy"
git push origin dev
```

✅ The CD system is now added to the `dev` branch.

**The logic is ready but not triggered until you merge `dev` → `main` or `main` → `prod`.**

---

## 🧪 Step 5 — (Optional) Test Full CD Flow

You can test everything end to end:

1. Create a small commit on a `feature/test-deploy` branch
2. Merge it into `dev`, test locally
3. Merge `dev` → `main`, auto deploys to `akluma-staging`
4. Merge `main` → `prod`, auto deploys to `akluma-prod`

---

Just leaving this here to see if auto-deploy actually skips deployment when 
only change is in an .md file.
