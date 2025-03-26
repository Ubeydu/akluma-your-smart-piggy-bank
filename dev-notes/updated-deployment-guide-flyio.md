### Deploying Your Laravel Application on Fly.io

This guide covers deploying your Laravel application on Fly.io with:
1. **Setting Up Fly.io CLI**
2. **Launching Your Laravel Application**
3. **Configuring Environment Variables and Secrets**
4. **Specifying PHP and Node.js Versions**
5. **Deploying the Basic Application**
6. **Setting Up MySQL Database (Staging and Production)**
7. **Configuring Laravel Scheduler and Queues**
8. **Post-Deployment Management**
9. **Continuous Deployment with GitHub Actions**

---

### 1. **Setting Up Fly.io CLI**
First, install the Fly.io command-line interface (CLI) and authenticate:

```bash
# Install Fly.io CLI
curl -L https://fly.io/install.sh | sh

# Authenticate with Fly.io
fly auth login
```

---

### 2. **Launching Your Laravel Application (Staging and Production)**

First, create a deployment branch to avoid modifying your main branch directly:

```bash
# Start from your main branch
git checkout main

# Create and switch to a deployment setup branch
git checkout -b setup/fly-deployment
```

#### ✅ **Setting Up Multiple Fly Apps**

Fly.io supports managing multiple applications from a single codebase using configuration files in different directories. Here's how to properly set it up:

1. **Create directories for each environment:**
```bash
mkdir -p fly/production fly/staging
```

2. **Create the production app:**
```bash
fly launch --name myapp-prod --generate-name --no-deploy
```
- Choose a region (e.g., `mad` for Madrid).
- When asked to deploy now, say **No**.
- This creates `fly.toml` and other files.

3. **Move the production configuration:**
```bash
mv fly.toml fly/production/fly.toml
```

4. **Create the staging app:**
```bash
fly launch --name myapp-staging --generate-name --no-deploy
```
- Choose the same region as your production app.
- When asked to deploy now, say **No**.

5. **Move the staging configuration:**
```bash
mv fly.toml fly/staging/fly.toml
```

Now you have separate configuration files for each environment with the rest of the files (Dockerfile, .dockerignore, etc.) shared between environments.

#### ✅ **Deploying to Different Environments**

When deploying, simply specify which configuration file to use:

```bash
# Deploy to production
fly deploy -c fly/production/fly.toml

# Deploy to staging
fly deploy -c fly/staging/fly.toml
```

**Note:** In your GitHub Actions workflows, you'll use these commands with the `-c` flag to specify which configuration to use, eliminating the need for manual file copying.

After setting up the configuration, commit these changes:

```bash
git add .
git commit -m "Add Fly.io deployment configuration"
git checkout main
git merge setup/fly-deployment
git push origin main  # This will trigger deployment to staging
```

---

### 3. **Configuring Environment Variables and Secrets**

Fly.io automatically generates a secure `APP_KEY` during the launch process, so you don't need to manually set it.

#### **Configuring the `fly.toml` file**
Fly.io automatically generates the `fly.toml` file during the `fly launch`. You can manually update it for non-sensitive environment variables:

```toml
[env]
# Set any env vars you want here
# Caution: Don't add secrets here
APP_ENV = "production"
APP_DEBUG = "false"
APP_URL = "https://myapp-prod.fly.dev"
```

**Setting Up Secrets:**
For sensitive data, use secrets instead of putting them in `fly.toml`:

```bash
fly secrets set DB_USERNAME=your_db_user DB_PASSWORD=your_db_password -a myapp-prod
```

---

### 4. **Specifying PHP and Node.js Versions**

By default, Fly.io tries to detect your local PHP version (with a minimum of PHP 7.4). If it can't detect the version, it defaults to PHP 8.2. For Node.js, it defaults to version 18.

You can specify which versions to use by adding build arguments to your `fly.toml` file:

```toml
[build]
  [build.args]
    PHP_VERSION = "8.4"
    NODE_VERSION = "18"
```

You can also override these settings when deploying:

```bash
fly deploy --build-arg "PHP_VERSION=8.4" --build-arg "NODE_VERSION=20"
```

---

### 5. **Deploying the Basic Application**
Deploy your application to Fly.io:

```bash
fly deploy -a myapp-prod
```

For the staging app:
```bash
fly deploy -a myapp-staging
```

You can monitor logs to ensure everything is running:
```bash
fly logs -a myapp-prod
```

Try these other useful commands:
* `fly apps open -a myapp-prod` - Open your deployed app in a browser
* `fly status -a myapp-prod` - View your app's current deployment status
* `fly ssh console -a myapp-prod` - Open a terminal on your VM

---

### 6. **Setting Up MySQL Database (Staging and Production)**

Fly.io allows you to run MySQL as a Fly App.

#### ✅ **Setting up MySQL Fly App:**
1. **Create a new MySQL application:**
   ```bash
   fly launch --name myapp-mysql
   ```

2. **Configure MySQL in the fly.toml file:**
   ```toml
   app = "myapp-mysql"
   
   [env]
   MYSQL_DATABASE = "myapp"
   MYSQL_USER = "laravel_user"
   ```

3. **Set MySQL password as secret:**
   ```bash
   fly secrets set MYSQL_PASSWORD=your_secure_password -a myapp-mysql
   fly secrets set MYSQL_ROOT_PASSWORD=your_secure_root_password -a myapp-mysql
   ```

4. **Deploy the MySQL app:**
   ```bash
   fly deploy -a myapp-mysql
   ```

#### ✅ **Production Database Configuration:**
1. **Get your MySQL app's internal address:**
   ```bash
   fly status -a myapp-mysql
   ```
   Note the `.internal` address (e.g., `myapp-mysql.internal`)

2. **Configure your production Laravel app:**
   ```bash
   # Set secrets
   fly secrets set \
     DB_CONNECTION=mysql \
     DB_HOST=myapp-mysql.internal \
     DB_PORT=3306 \
     DB_DATABASE=myapp \
     DB_USERNAME=laravel_user \
     DB_PASSWORD=your_secure_password \
     -a myapp-prod
   ```

3. **Redeploy to apply the database configuration:**
   ```bash
   fly deploy -a myapp-prod
   ```

#### ✅ **Staging Database Configuration:**
You can use the same MySQL instance but with a different database name or create a separate staging MySQL app.

**Option 1: Use same MySQL instance with different database:**
```bash
# Create staging database on your MySQL app
fly ssh console -a myapp-mysql
mysql -u root -p
CREATE DATABASE myapp_staging;
GRANT ALL PRIVILEGES ON myapp_staging.* TO 'laravel_user'@'%';
FLUSH PRIVILEGES;
exit

# Configure staging app
fly secrets set \
  DB_CONNECTION=mysql \
  DB_HOST=myapp-mysql.internal \
  DB_PORT=3306 \
  DB_DATABASE=myapp_staging \
  DB_USERNAME=laravel_user \
  DB_PASSWORD=your_secure_password \
  -a myapp-staging

# Redeploy staging app to apply changes
fly deploy -a myapp-staging
```

**Option 2: Create separate MySQL instance for staging:**
```bash
fly launch --name myapp-mysql-staging
# Configure similarly to production MySQL
```

---

### 📝 **Database Configuration in .env**
Update your environment variables to match your database settings:

**For Production:**
```env
DB_CONNECTION=mysql
DB_HOST=myapp-mysql.internal
DB_PORT=3306
DB_DATABASE=myapp
DB_USERNAME=laravel_user
DB_PASSWORD=your_secure_password
```

**For Staging:**
```env
DB_CONNECTION=mysql
DB_HOST=myapp-mysql.internal
DB_PORT=3306
DB_DATABASE=myapp_staging
DB_USERNAME=laravel_user
DB_PASSWORD=your_secure_password
```

**For Local Development (connecting to Fly MySQL):**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=myapp
DB_USERNAME=laravel_user
DB_PASSWORD=your_secure_password
```

To connect locally, use:
```bash
fly proxy 3306 -a myapp-mysql
```

---

### 7. **Configuring Laravel Scheduler and Queues**

After your basic application is deployed and connected to the database, you can add scheduler and queue workers.

#### **Setting Up Process Groups**

Update your `fly.toml` file to define different process groups:

```toml
[processes]
  app = ""         # Web server (default process)
  cron = "cron -f" # Scheduler process
  worker = "php artisan queue:listen" # Queue worker
```

- **app**: The empty string means it will use the default entrypoint script to run Nginx/PHP-FPM
- **cron**: Runs the cron daemon, which executes Laravel's scheduler
- **worker**: Runs the Laravel queue worker

You can customize the queue worker command with additional parameters:
```toml
worker = "php artisan queue:listen --tries=3 --timeout=90"
```

#### **Process Configuration**

Ensure your `fly.toml` has the correct HTTP service configuration:

```toml
[http_service]
  internal_port = 8080
  force_https = true
  auto_stop_machines = true
  auto_start_machines = true
  min_machines_running = 0
  processes = ["app"]  # Only applies to the app process
```

#### **Deploying Process Groups**

After configuring process groups, redeploy your application to apply the changes:

```bash
fly deploy -a myapp-prod
```

#### **Scaling Process Groups**

Once your application with multiple process groups is deployed, you can scale them independently:

```bash
# Scale web servers to 2 instances and workers to 4
fly scale count app=2 worker=4 -a myapp-prod
```

---

### 8. **Post-Deployment Management**

#### **Console Access**
SSH into your application to run artisan commands, check logs, or troubleshoot issues:

```bash
fly ssh console -a myapp-prod
cd /var/www/html
```

You can run Laravel commands just as you would locally:
```bash
php artisan migrate
php artisan cache:clear
```

#### **Logging Configuration**
By default, Fly.io configures Laravel to use the `stderr` logging channel and only retains logs for 2 days. To modify this:

**Option 1: Use Fly.io's default logging (logs kept for 2 days only)**
```toml
# In fly.toml
[env]
  LOG_CHANNEL = "stderr"
  LOG_LEVEL = "info"
  LOG_STDERR_FORMATTER = "Monolog\\Formatter\\JsonFormatter"
```

View logs with:
```bash
fly logs -a myapp-prod
```

**Option 2: Persistent logging with volumes**
For longer retention, create a persistent volume for logs:

1. Create a volume:
```bash
fly volumes create app_logs --region mad --size 1 -a myapp-prod
```

2. Mount it in your fly.toml:
```toml
[mounts]
  source = "app_logs"
  destination = "/var/www/html/storage/logs"
```

3. Configure Laravel to use file logging:
```toml
# In fly.toml
[env]
  LOG_CHANNEL = "stack"  # or "daily"
  LOG_LEVEL = "info"
```

4. Update your logging stack in config/logging.php to include both stderr and file logging:
```php
'stack' => [
    'driver' => 'stack',
    'channels' => ['single', 'stderr'],
    'ignore_exceptions' => false,
],
```

5. Redeploy to apply logging changes:
```bash
fly deploy -a myapp-prod
```

---

### 9. **Continuous Deployment with GitHub Actions**

#### **Branch Strategy**
This deployment guide uses the following branch strategy:
* **Main Branch (`main`)**: Used for active development. Pushing to this branch triggers deployment to the **staging environment** on Fly.io.
* **Production Branch (`prod`)**: Used for stable, production-ready code. Pushing to this branch triggers deployment to the **production environment** on Fly.io.
* **Feature Branches**: Created from `main` for development work and merged back into `main` when completed.

#### **Staging Workflow (.github/workflows/fly-staging.yml)**
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
      - run: flyctl deploy --remote-only -c fly/staging/fly.toml
        env:
          FLY_API_TOKEN: ${{ secrets.FLY_API_TOKEN_STAGING }}
      - name: Run Migrations
        run: flyctl ssh console -a myapp-staging --command "php artisan migrate --force"
        env:
          FLY_API_TOKEN: ${{ secrets.FLY_API_TOKEN_STAGING }}
```

#### **Production Workflow (.github/workflows/fly-prod.yml)**
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
      - run: flyctl deploy --remote-only -c fly/production/fly.toml
        env:
          FLY_API_TOKEN: ${{ secrets.FLY_API_TOKEN }}
      - name: Run Migrations
        run: flyctl ssh console -a myapp-prod --command "php artisan migrate --force"
        env:
          FLY_API_TOKEN: ${{ secrets.FLY_API_TOKEN }}
```

---

### ✅ **Final Adjustments and Verification**
1. **Test Staging Environment:**
   ```bash
   git checkout main
   # Make your changes
   git add .
   git commit -m "Update feature for staging testing"
   git push origin main
   ```
    - This will trigger deployment to: `https://myapp-staging.fly.dev`

2. **Test Production Deployment:**
   ```bash
   # After verifying changes in staging
   git checkout prod
   git merge main
   git push origin prod
   ```
    - This will trigger deployment to: `https://myapp-prod.fly.dev`

3. **Feature Branch Workflow:**
   ```bash
   # Create feature branch from main
   git checkout main
   git checkout -b feature/new-feature
   
   # Work on your feature
   # ...
   
   # Commit changes
   git add .
   git commit -m "Implement new feature"
   
   # Push to remote (optional)
   git push origin feature/new-feature
   
   # When ready, merge back to main
   git checkout main
   git merge feature/new-feature
   git push origin main  # This triggers staging deployment
   ```

4. **Check Logs:**
   ```bash
   fly logs -a myapp-prod
   fly logs -a myapp-staging
   ```

---
