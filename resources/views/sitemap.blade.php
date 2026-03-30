{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
    @php
        $locales = array_keys(config('app.available_languages', []));

        $pages = [
            'welcome'                    => null,
            'localized.login'            => 'localized.login',
            'localized.register'         => 'localized.register',
            'localized.password.request' => 'localized.password.request',
            'localized.terms'            => 'localized.terms',
            'localized.privacy'          => 'localized.privacy',
            'localized.about'            => 'localized.about',
        ];
    @endphp

    @foreach($pages as $key => $routeName)
        @foreach($locales as $locale)
        <url>
            <loc>{{ $key === 'welcome' ? url("/{$locale}") : localizedRoute($routeName, [], $locale) }}</loc>
            @foreach($locales as $altLocale)
            <xhtml:link rel="alternate" hreflang="{{ $altLocale }}" href="{{ $key === 'welcome' ? url("/{$altLocale}") : localizedRoute($routeName, [], $altLocale) }}" />
            @endforeach
        </url>
        @endforeach
    @endforeach
</urlset>
