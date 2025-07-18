# WWW to Non-WWW Redirect Implementation Guide

## Problem Summary

The website was serving identical content on both `www.akluma.com` and `akluma.com`, creating duplicate content issues that hurt SEO. Search engines were indexing both domains separately, splitting page authority and potentially causing ranking penalties.

## Root Cause

Despite having correct redirect rules in `fly.production.toml`, Laravel was processing requests from the www domain before Fly.io could apply its redirect configuration. The Laravel routes were accepting requests from ANY domain and processing them internally.

### Original Fly.io Configuration (Was Correct)
```toml
# fly.production.toml
[[http_service.redirects]]
  source = "https://www.akluma.com/{*}"
  destination = "https://akluma.com/{*}"
  status_code = 301
```

### Laravel Routes Issue
```php
// routes/web.php - This was processing www requests before Fly.io redirects
Route::get('/', function () {
    $locale = Auth::check() ? Auth::user()->language : (session('locale') ?? 'en');
    return redirect("/$locale");
});
```

## Solution Implemented

Created a Laravel middleware to handle www → non-www redirects at the application level, ensuring they happen before any routing occurs.

### Step 1: Create Redirect Middleware

**File:** `app/Http/Middleware/RedirectWww.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectWww
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->getHost() === 'www.akluma.com') {
            return redirect()->to('https://akluma.com' . $request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
```

### Step 2: Register Middleware Globally

**File:** `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    // Handle www to non-www redirect FIRST before any other middleware
    $middleware->prepend(\App\Http\Middleware\RedirectWww::class);
    
    // ... other middleware
})
```

**Key Point:** Used `prepend()` to ensure this middleware runs before any routing or other middleware.

## Testing Commands

### Local Testing (with Sail)
```bash
# Test redirect logic locally
curl -I -H "Host: www.akluma.com" http://127.0.0.1/en/terms-of-service

# Expected: HTTP/1.0 301 with Location: https://akluma.com/en/terms-of-service
```

### Production Testing
```bash
# Test www URLs redirect properly
curl -I https://www.akluma.com/en/terms-of-service
curl -I https://www.akluma.com/
curl -I https://www.akluma.com/en
curl -I https://www.akluma.com/en/privacy-policy

# Expected: HTTP/2 301 with location pointing to non-www domain

# Test non-www URLs work normally
curl -I https://akluma.com/en/terms-of-service

# Expected: HTTP/2 200 (normal content delivery)

# Test different HTTP methods
curl -X POST -I https://www.akluma.com/en/terms-of-service

# Expected: HTTP/2 301 (redirects work for all methods)

# Test complex URLs preserve full path
curl -I https://www.akluma.com/en/create-piggy-bank/step-1

# Expected: HTTP/2 301 with location: https://akluma.com/en/create-piggy-bank/step-1
```

## Deployment

```bash
# Commit the changes
git add .
git commit -m "fix: implement www to non-www redirect middleware to resolve SEO duplicate content

- Add RedirectWww middleware to handle www.akluma.com → akluma.com redirects
- Register middleware globally with prepend() to execute before routing
- Fixes duplicate content SEO issues where both domains served identical pages
- Ensures 301 redirects for all www requests to consolidate page authority"

# Deploy to production
git push origin main
```

## Why This Solution Works

1. **Middleware runs before routing:** Using `prepend()` ensures www redirects happen before Laravel processes any routes
2. **Global application:** Affects ALL requests to the application, not just specific routes
3. **Preserves request URI:** Maintains the full path and query parameters in redirects
4. **Proper HTTP status:** Uses 301 (permanent redirect) for SEO benefits
5. **Works with all HTTP methods:** POST, PUT, DELETE requests also redirect properly

## Alternative Approaches Considered

1. **Route-level redirects:** Would require modifying every route definition
2. **Server-level redirects:** Fly.io configuration was correct but Laravel intercepted requests first
3. **DNS-level redirects:** Not suitable for our hosting setup

## SEO Benefits

- ✅ Eliminates duplicate content issues
- ✅ Consolidates page authority to single domain (akluma.com)
- ✅ Provides consistent URL structure across the site
- ✅ Improves search engine crawling efficiency
- ✅ Maintains link equity consolidation

## Future Considerations

- Monitor search console for any remaining duplicate content issues
- Consider implementing canonical tags as additional SEO protection
- Update any internal links that point to www domain
- Update sitemap.xml to use non-www URLs consistently

## Troubleshooting

If redirects stop working:

1. Check middleware is still registered in `bootstrap/app.php`
2. Verify middleware class exists and is properly namespaced
3. Test locally with curl commands above
4. Check Laravel logs for any middleware errors
5. Ensure no other middleware is interfering with request flow

## Related Files Modified

- `app/Http/Middleware/RedirectWww.php` (new file)
- `bootstrap/app.php` (middleware registration)

No changes needed to:
- `routes/web.php`
- `fly.production.toml`
- DNS configuration
