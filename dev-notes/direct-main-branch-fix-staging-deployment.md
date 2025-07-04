## 🔁 Direct Main Branch Fix → Staging

### ✅ Step 1: Work Directly on Main
```bash
git switch main
git pull origin main
```

Make your minimal changes directly on `main` branch.

### ✅ Step 2: Commit and Push to Staging
```bash
git add .
git commit -m "Fix: [describe your minimal fix]"
git push origin main
```

✅ This triggers staging deploy to:
```
https://akluma-staging.fly.dev
```

### ✅ Step 3: Verify on Staging
Visit staging and verify your fix works.

### ✅ Step 4: Sync Dev with Main
```bash
git switch dev
git pull origin dev
git merge main
git push origin dev
```

---

**That's it!** Your `dev` branch now has the same code as `main`, and staging has your fix deployed.

When you're ready to promote to production later, just follow your usual Step 7:
```bash
git switch prod
git pull origin prod
git merge main
git push origin prod
```
