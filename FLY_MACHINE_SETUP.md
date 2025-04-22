# Fly.io Machine Configuration - Last working setup on Tue Apr 22 11:38:24 GMT 2025
## Active Machines
7 machines have been retrieved from app akluma-prod.
View them in the UI here (​https://fly.io/apps/akluma-prod/machines/)

[1makluma-prod[0m
ID            	NAME                 	STATE  	CHECKS	REGION	ROLE	IMAGE                                            	IP ADDRESS                      	VOLUME	CREATED             	LAST UPDATED        	PROCESS GROUP	SIZE                 
32876331b04618	bitter-resonance-2583	started	      	cdg   	    	akluma-prod:deployment-01JS5G35HCZ900PNETW9WJJXNN	fdaa:13:ee2e:a7b:404:9e18:6769:2	      	2025-04-18T21:32:44Z	2025-04-18T22:26:39Z	worker       	shared-cpu-1x:1024MB	
18536dda472d48	silent-flower-5767   	stopped	      	cdg   	    	akluma-prod:deployment-01JS5G35HCZ900PNETW9WJJXNN	fdaa:13:ee2e:a7b:39d:d047:bf6c:2	      	2025-04-18T21:32:43Z	2025-04-18T22:26:32Z	cron         	shared-cpu-1x:1024MB	
7814d22f924658	weathered-flower-3995	stopped	      	cdg   	    	akluma-prod:deployment-01JS5G35HCZ900PNETW9WJJXNN	fdaa:13:ee2e:a7b:39d:ed72:b3ad:2	      	2025-04-18T21:32:55Z	2025-04-18T22:26:32Z	worker       	shared-cpu-1x:1024MB	
4d899660a70487	akluma-prod-queue    	stopped	      	cdg   	    	akluma-prod:deployment-01JRWP2WSK22201WA1126V9BDE	fdaa:13:ee2e:a7b:404:fe56:aed7:2	      	2025-04-16T09:35:19Z	2025-04-18T22:13:37Z	worker       	shared-cpu-1x:1024MB	
90802551c57e87	akluma-prod-cron     	stopped	      	cdg   	    	akluma-prod:deployment-01JRWP2WSK22201WA1126V9BDE	fdaa:13:ee2e:a7b:404:2481:70de:2	      	2025-04-15T13:49:49Z	2025-04-18T22:32:19Z	cron         	shared-cpu-1x:256MB 	
6e822995c73028	summer-mountain-6143 	started	      	cdg   	    	akluma-prod:deployment-01JS5G35HCZ900PNETW9WJJXNN	fdaa:13:ee2e:a7b:404:b5ca:beda:2	      	2025-04-18T21:32:27Z	2025-04-18T22:26:36Z	cron         	shared-cpu-1x:1024MB	
e7840d56c940d8	snowy-grass-4679     	started	      	cdg   	    	akluma-prod:deployment-01JS5G35HCZ900PNETW9WJJXNN	fdaa:13:ee2e:a7b:39e:8f54:cc73:2	      	2025-03-28T15:11:37Z	2025-04-18T22:26:38Z	app          	shared-cpu-1x:1024MB	

## App Configuration
{
  "app": "akluma-prod",
  "primary_region": "cdg",
  "console_command": "php /var/www/html/artisan tinker",

  "build": {
    "args": {
      "NODE_VERSION": "18",
      "PHP_VERSION": "8.2"
    }
  },

  "env": {
    "APP_ENV": "production",
    "LOG_CHANNEL": "stderr",
    "LOG_LEVEL": "info",
    "LOG_STDERR_FORMATTER": "Monolog\\Formatter\\JsonFormatter",
    "REMINDERS_TEST_MODE": "true",
    "SESSION_DRIVER": "cookie",
    "SESSION_SECURE_COOKIE": "true"
  },

  "processes": {
    "app": "",
    "cron": "bash -c '/var/www/html/.fly/scripts/setup-cron.sh \u0026\u0026 tail -f /dev/null'",
    "worker": "bash -c '/var/www/html/.fly/scripts/start-worker.sh \u0026\u0026 tail -f /dev/null'"
  },

  "http_service": {
    "internal_port": 8080,
    "force_https": true,
    "auto_stop_machines": "suspend",
    "auto_start_machines": true,
    "min_machines_running": 1,

    "processes": [
      "app"
    ]
  },

  "vm": [
    {
      "memory": "1gb",
      "cpu_kind": "shared",
      "cpus": 1
    }
  ]
}
## Environment Variables
NAME                 	DIGEST          	CREATED AT        
APP_KEY              	debde5962e5c73a0	Mar 26 2025 09:23	
APP_URL              	0437495d18daf11a	Mar 28 2025 10:13	
DB_CONNECTION        	f0c68e21cb9831dd	Mar 26 2025 15:10	
DB_DATABASE          	3d05f5f97a175d79	Mar 26 2025 15:14	
DB_HOST              	cc2f2601208d8c8d	Mar 26 2025 15:14	
DB_PASSWORD          	64f55dbd07247177	Mar 26 2025 15:14	
DB_PORT              	ebba40b7ee206c4f	Mar 26 2025 15:14	
DB_USERNAME          	4b9bc2d82c9b8063	Mar 26 2025 15:14	
MAIL_FROM_ADDRESS    	94ee082c292b424c	Apr 2 2025 16:16 	
MAIL_FROM_NAME       	4de40908530b8858	Apr 2 2025 16:36 	
MAILGUN_DOMAIN       	eca6c39f02e9189e	Apr 2 2025 16:16 	
MAILGUN_ENDPOINT     	cd91f0048e7e9bbc	Apr 2 2025 16:16 	
MAILGUN_SECRET       	54f0cc6cc49f40f6	Apr 2 2025 16:16 	
MAIL_MAILER          	d0545e0188202359	Apr 2 2025 16:16 	
SESSION_DOMAIN       	524bde215f976878	Mar 28 2025 10:13	
SESSION_DRIVER       	89c4dc5fe975b7e2	Mar 28 2025 10:50	
SESSION_SAME_SITE    	1aecfe610ffcbed3	Mar 28 2025 10:13	
SESSION_SECURE_COOKIE	d8c5ac2e11c8e492	Mar 28 2025 10:13	

## Cron Machine Setup

To check crontab: `crontab -l`
* * * * * cd /var/www/html && /var/www/html/load-env.sh php artisan schedule:run >> /var/www/html/storage/logs/scheduler.log 2>&1

To check cron script: `cat /var/www/html/.fly/scripts/setup-cron.sh`
#!/usr/bin/env bash

# Only run on cron process
if [[ "${FLY_PROCESS_GROUP}" == "cron" ]]; then
echo "↪️ Setting up Laravel cron job inside cron process..."

    # Create helper to load env and run artisan command
    cat > /var/www/html/load-env.sh << 'EOF'
#!/bin/bash
set -e
export $(printenv | grep -v "^HOME=" | grep -v "^PWD=" | grep -v "^TERM=" | grep -v "^SHLVL=" | grep -v "^PATH=" | grep "^[A-Z]" | xargs -0)
exec "$@"
EOF

    chmod +x /var/www/html/load-env.sh

    # Register cron job
    echo "* * * * * cd /var/www/html && /var/www/html/load-env.sh php artisan schedule:run >> /var/www/html/storage/logs/scheduler.log 2>&1" | crontab -

    # Make sure cron service is running
    service cron start

    echo "✅ Cron job registered successfully."
fi


## Worker Machine Setup

To check worker process: `ps aux | grep artisan`
root       649  0.0  8.1 244720 80180 ?        S    Apr18   1:52 php artisan queue:work --tries=3
root       699  0.0  0.1   3472  1832 pts/0    S+   11:53   0:00 grep --color=auto artisan

To check worker script: `cat /var/www/html/.fly/scripts/start-worker.sh`
#!/usr/bin/env bash

# Only run on worker process
if [[ "${FLY_PROCESS_GROUP}" == "worker" ]]; then
echo "Starting queue worker..."
cd /var/www/html || exit
exec php artisan queue:work --tries=3
fi
