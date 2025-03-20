### Deploying Your Laravel Application on Fly.io

This guide covers deploying your Laravel application on Fly.io with:
1. **Setting Up Fly.io CLI**
2. **Launching Your Laravel Application**
3. **Configuring Environment Variables and Secrets**
4. **Specifying PHP and Node.js Versions**
5. **Configuring Laravel Scheduler and Queues**
6. **Deploying the Application**
7. **Setting Up MySQL Database (Staging and Production)**
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

Inside your Laravel project directory:

#### ✅ **Create a Production App:**
```bash
fly launch --name myapp-prod
```
- Choose a region (e.g., `mad` for Madrid).
- When asked to deploy now, say **No** to configure settings first.

#### ✅ **Create a Staging App:**
```bash
fly launch --name myapp-staging
```
- Choose the same region as your production app (e.g., `mad` for Madrid).
- Again, choose **No** when asked to deploy.

**Note:** The `fly launch` command will add several files to your codebase:
1. `Dockerfile` - Used to build a container image
2. `.dockerignore` - Ensures certain files aren't included in your container
3. `fly.toml` - Configuration specific to hosting on Fly
4. `.fly` - A directory containing configuration files for running Nginx/PHP in a container

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

### 5. **Configuring Laravel Scheduler and Queues**

Laravel applications often need to run scheduled tasks (via cron) and process queued jobs. Fly.io handles these through process groups in your `fly.toml` file.

#### **Setting Up Process Groups**

Update your `fly.toml` to define different process groups:

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

For the web server process, ensure your `fly.toml` has the correct HTTP service configuration:

```toml
[http_service]
  internal_port = 8080
  force_https = true
  auto_stop_machines = true
  auto_start_machines = true
  min_machines_running = 0
  processes = ["app"]  # Only applies to the app process
```

#### **Scaling Process Groups**

You can scale different process groups independently:

```bash
# Scale web servers to 2 instances and workers to 4
fly scale count app=2 worker=4 -a myapp-prod
```

---

### 6. **Deploying the Application**
Deploy your application to Fly.io:

```bash
fly deploy
```

You can monitor logs to ensure everything is running:
```bash
fly logs
```

Try these other useful commands:
* `fly apps open` - Open your deployed app in a browser
* `fly status` - View your app's current deployment status
* `fly ssh console` - Open a terminal on your VM

---

### 7. **Setting Up MySQL Database (Staging and Production)**

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

5. Redeploy:
```bash
fly deploy -a myapp-prod
```

---

### 9. **Continuous Deployment with GitHub Actions**

#### **Staging Workflow (.github/workflows/fly-staging.yml)**
```yaml
name: Fly Staging Deploy

on:
  push:
    branches:
      - staging

jobs:
  deploy:
    name: Deploy to Staging
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: superfly/flyctl-actions/setup-flyctl@master
      - run: flyctl deploy --remote-only -a myapp-staging
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
      - main

jobs:
  deploy:
    name: Deploy to Production
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: superfly/flyctl-actions/setup-flyctl@master
      - run: flyctl deploy --remote-only -a myapp-prod
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
   git checkout staging
   git push origin staging
   ```
    - Access at: `https://myapp-staging.fly.dev`

2. **Test Production Deployment:**
   ```bash
   git checkout main
   git merge staging
   git push origin main
   ```
    - Access at: `https://myapp-prod.fly.dev`

3. **Check Logs:**
   ```bash
   fly logs -a myapp-prod
   fly logs -a myapp-staging
   ```

---
