### **Step-by-Step Guide: Deploying Laravel on Hetzner Cloud (Manual Setup)**
This guide will walk you through deploying your **Laravel app on Hetzner Cloud** using a **bare VPS (Ubuntu 22.04)**.

---

## **1. Create and Set Up Your Server**

1. **Go to Hetzner Cloud Console**:
    - Sign up/log in at [Hetzner Cloud](https://www.hetzner.com/cloud).
    - Click **Create Server**.

2. **Choose Your Server Configuration**:
    - **Location**: Choose the nearest data center.
    - **Image**: Select **Ubuntu 22.04**.
    - **Type**: Start with the **CX11 ($5/month)** plan (1vCPU, 2GB RAM, 20GB SSD).
    - **Add SSH Key**:
        - Generate a key on your local machine (if you don’t have one):
          ```sh
          ssh-keygen -t rsa -b 4096 -C "your_email@example.com"
          ```
        - Copy the key from `~/.ssh/id_rsa.pub` and add it to Hetzner under **SSH Keys**.

3. **Create & Boot the Server**.
    - You’ll receive an **IP address** after your server is created.

---

## **2. Connect to Your Server via SSH**
1. Open a terminal and connect using:
   ```sh
   ssh root@your_server_ip
   ```
   (Replace `your_server_ip` with the actual IP from Hetzner.)

2. **Update and Upgrade the Server**:
   ```sh
   apt update && apt upgrade -y
   ```

---

## **3. Install Nginx, PHP, and MySQL**

### **A. Install Nginx (Web Server)**
```sh
apt install nginx -y
```
Check if it's running:
```sh
systemctl status nginx
```

### **B. Install PHP (8.3 Recommended) and Dependencies**
```sh
apt install php8.3 php8.3-fpm php8.3-cli php8.3-mbstring php8.3-xml php8.3-bcmath php8.3-curl php8.3-mysql unzip -y
```
Check PHP version:
```sh
php -v
```

### **C. Install MySQL (Database Server)**
```sh
apt install mysql-server -y
mysql_secure_installation
```
- Set a root password (secure it).
- Remove anonymous users and test databases.

---

## **4. Set Up Your Laravel Project on the Server**

### **A. Install Composer (For Laravel Dependencies)**
```sh
apt install curl -y
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

### **B. Deploy Your Laravel Project**
1. **Navigate to Web Directory**:
   ```sh
   cd /var/www/
   ```

2. **Clone Your Laravel Project (if using GitHub/GitLab)**:
   ```sh
   git clone https://github.com/yourusername/your-laravel-project.git myapp
   ```
   (Replace with your actual repository.)

3. **Set Permissions**:
   ```sh
   chown -R www-data:www-data /var/www/myapp
   chmod -R 775 /var/www/myapp/storage /var/www/myapp/bootstrap/cache
   ```

4. **Install Laravel Dependencies**:
   ```sh
   cd /var/www/myapp
   composer install --no-dev --optimize-autoloader
   ```

5. **Set Environment Variables**:
   ```sh
   cp .env.example .env
   nano .env
   ```
   Update the `.env` file with:
    - `APP_URL=http://your_domain_or_ip`
    - Database settings (`DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

6. **Generate Application Key**:
   ```sh
   php artisan key:generate
   ```

7. **Run Database Migrations** (If needed):
   ```sh
   php artisan migrate --force
   ```

---

## **5. Configure Nginx for Laravel**

1. Create a new Nginx configuration file:
   ```sh
   nano /etc/nginx/sites-available/laravel
   ```
2. Add the following configuration:
   ```nginx
   server {
       listen 80;
       server_name your_domain_or_ip;
       root /var/www/myapp/public;

       index index.php index.html index.htm;
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/run/php/php8.3-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }

       location ~ /\.ht {
           deny all;
       }
   }
   ```
   (Replace `your_domain_or_ip` with your actual domain or IP.)

3. **Enable the Nginx Configuration**:
   ```sh
   ln -s /etc/nginx/sites-available/laravel /etc/nginx/sites-enabled/
   ```

4. **Restart Nginx**:
   ```sh
   systemctl restart nginx
   ```

---

## **6. Set Up Supervisor for Queued Jobs (Optional)**
If your Laravel app uses **queues**, install Supervisor to manage them:
```sh
apt install supervisor -y
nano /etc/supervisor/conf.d/laravel-worker.conf
```
Add this:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/myapp/artisan queue:work --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/laravel-worker.log
```
Save and restart Supervisor:
```sh
supervisorctl reread
supervisorctl update
supervisorctl start laravel-worker:*
```

---

## **7. Secure Your Server (Firewall & SSL)**

### **A. Set Up a Firewall**
Allow only **SSH, HTTP, and HTTPS**:
```sh
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw enable
```

### **B. Install SSL (Let’s Encrypt, If You Have a Domain)**
```sh
apt install certbot python3-certbot-nginx -y
certbot --nginx -d yourdomain.com -d www.yourdomain.com
```
Test auto-renewal:
```sh
certbot renew --dry-run
```

---

## **8. Test Your Laravel App**
- Open **your server’s IP or domain** in a browser.
- If everything is configured correctly, you should see your Laravel app live!

---

## **Final Notes**
✅ This setup gives you a **real-world deployment experience** without overwhelming complexity.  
✅ You **own your server** and can tweak things as you learn more.  
✅ If you want automated deployments, consider **GitHub Actions or Forge later**.

Would you like help with **automating deployment or additional security settings**?
