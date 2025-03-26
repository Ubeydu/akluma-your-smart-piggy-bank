1. Created account on fly.io
2. Installed flyctl by running "brew install flyctl"
3. confirmed that it ran succesfully by running "flyctl version"
4. create a deployment branch to avoid modifying your main branch 
   directly by running "git checkout -b deploy-flyio-2nd-attempt"
5. Create directories for each environment by running "mkdir -p fly/production fly/staging"
6. run "fly launch --name akluma-prod --generate-name --no-deploy"  command to automagically configure your app for Fly
7. if you are not logged in, it will ask: "? You must be logged in to do this. Would you like to sign in? (y/N)" say yes
8. After logging in successfully through your browser, fly.io will make some sensible assumptions and show them to you
9. Change them by changing region to paris and by removing redis.
10. Now, if there was no error, you have your app launched on fly.io
   You can go to fly.io and check. and you will see it is not deployed.
11. run "mv fly.toml fly.production.toml" to rename the file.
12. run "cp fly.production.toml fly.staging.toml" to
    create a staging configuration by copying the production one
13. edit the staging configuration file to change:
    - The app name to "akluma-staging"
    - Set environment variables (e.g., `APP_ENV = "staging"`)
14. run "fly apps create akluma-staging" to create an app without 
     its own configuration
15. then you can go check it on fly.io and see that 
    there is no configuration in it
16. to prevent mixed-content error after deploy, do the following changes;
    
    Open the app/Http/Middleware/TrustProxies.php file in your Laravel application.
    Update the $proxies property to include Fly.io's proxy IPs. 
    Instead of adding a specific IP, you should use '*' to trust all proxies since Fly.io uses a dynamic IP range:

    protected $proxies = '*';
    This change will tell Laravel to trust the proxy headers from all proxies,
    which is appropriate for Fly.io's infrastructure.

    Make sure the $headers property is set correctly:

    protected $headers = Request::HEADER_X_FORWARDED_FOR |
    Request::HEADER_X_FORWARDED_HOST |
    Request::HEADER_X_FORWARDED_PORT |
    Request::HEADER_X_FORWARDED_PROTO |
    Request::HEADER_X_FORWARDED_AWS_ELB;

    and, just to be safe, do this change as well:

    Open your app/Providers/AppServiceProvider.php file.
    First, add the URL facade import at the top of the file:

    use Illuminate\Support\Facades\URL;

    Then modify the boot() method to force HTTPS in production:

    public function boot()
    {
       if($this->app->environment('production')) {
          URL::forceScheme('https');
       }
    }
17. 

