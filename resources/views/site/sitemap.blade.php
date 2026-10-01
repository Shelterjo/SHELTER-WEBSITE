{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}{{-- nosemgrep: shelter-blade-unescaped-output — constant XML declaration, no data --}}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($clusters as $cluster)
@foreach (array_unique(array_values($cluster)) as $url)
    <url>
        <loc>{{ $url }}</loc>
@foreach ($cluster as $hreflang => $alternate)
        <xhtml:link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $alternate }}"/>
@endforeach
    </url>
@endforeach
@endforeach
</urlset>
