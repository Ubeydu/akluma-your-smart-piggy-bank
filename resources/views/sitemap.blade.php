{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @php
        $locales = array_keys(config('app.available_languages', []));
    @endphp

    {{-- Welcome pages for each locale --}}
    @foreach($locales as $locale)
        <url>
            <loc>{{ url("/{$locale}") }}</loc>
            <changefreq>weekly</changefreq>
            <priority>1.0</priority>
        </url>
    @endforeach

    {{-- Login pages --}}
    @foreach($locales as $locale)
        <url>
            <loc>{{ url("/{$locale}/login") }}</loc>
            <changefreq>monthly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach

    {{-- Register pages --}}
    @foreach($locales as $locale)
        <url>
            <loc>{{ url("/{$locale}/register") }}</loc>
            <changefreq>monthly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach

    {{-- Create piggy bank pages --}}
    @foreach($locales as $locale)
        <url>
            <loc>{{ url("/{$locale}/create-piggy-bank/step-1") }}</loc>
            <changefreq>monthly</changefreq>
            <priority>0.9</priority>
        </url>
    @endforeach
</urlset>
