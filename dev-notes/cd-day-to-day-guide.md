# 🚀 Day-to-Day Deployment Guide (Dev → Staging → Prod)

This guide explains the **everyday deployment flow** now that Continuous Deployment (CD) is fully set up via GitHub Actions and Fly.io.

It assumes your workflows and secrets are already in place.

---

## ✅ Overview

```text
feature → dev → main → prod
  (you)    (QA/staging)   (live)
```

- You work on feature/fix branches
- Merge them into `dev`
- Merge `dev` into `main` to trigger **staging deploy**
- Once verified, merge `main` into `prod` to trigger **production deploy**

---

## 🔁 Step-by-Step: Feature to Production

### ✅ Step 1: Start a Feature Branch

```bash
git checkout main
git pull origin main
git checkout -b feature/some-feature
```

Work on your code. Then test locally:

```bash
composer run dev
# Visit http://localhost:8000
```

---

### ✅ Step 2: Commit and Push

```bash
git add .
git commit -m "Add: some feature"
git push --set-upstream origin feature/some-feature
```

---

### ✅ Step 3: Create Pull Request → `dev`

- Open GitHub
- Create PR from `feature/some-feature` → `dev`
- Review & merge

---

### ✅ Step 4: Sync Local `dev` After GitHub Merge

```bash
git checkout dev
git pull origin dev # <--- Pull the changes you just merged on GitHub into your local dev
```

---

### ✅ Step 5: Sync `dev` with `main` (if needed)

```bash
git checkout dev
git pull origin main
git push origin dev
```

Do this only if `main` has commits `dev` hasn’t seen yet.

---

### ✅ Step 6: Deploy to Staging

```bash
git checkout main
git pull origin main
git merge dev
git push origin main
```

✅ This triggers staging deploy to:
```
https://akluma-staging.fly.dev
```

Visit the site and manually verify everything works.

---

### ✅ Step 7: Promote to Production

```bash
git checkout prod
git pull origin prod
git merge main
git push origin prod
```

✅ This triggers production deploy to:
```
https://akluma-prod.fly.dev
```

---

## 🧼 Bonus: Sync `dev` When You're Done

To ensure `dev` always stays clean and current:

```bash
git checkout dev
git merge main
git push origin dev
```

---

## 🔁 Summary Flow

```text
feature branch → dev → main → prod
                   ▲     ▲     ▲
                local   staging live
```

This is your new professional-grade daily CD flow. Use it every time you push code.

Let staging catch problems. Let production stay safe.

You're running things the right way. 🚀

