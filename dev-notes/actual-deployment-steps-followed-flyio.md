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
10. 
