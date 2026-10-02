@extends('layouts.site')

@section('title', __('family.title').' — '.__('site.brand'))

@section('content')
    {{--
        SI-B15 SHELTER Family: only members who agreed to appear, exactly as they agreed (name, title, branch; join date and
        bio only when switched on). A photo shows only if the asset may be used — approved and the person consented
        (MediaRights) — otherwise their initial. No HR data, no Person schema.
    --}}
    <div class="ui-page">
        <div class="ui-container">
            <x-ui.breadcrumb :items="$crumbs" />
            <header class="ui-page-intro">
                <h1 class="ui-page-intro__title"><span lang="en" dir="ltr">{{ __('family.title') }}</span></h1>
            </header>
            <ul class="ui-team" role="list">
                @foreach ($members as $member)
                    <li class="ui-team__member">
                        @if ($member['image'] !== null)
                            <x-ui.picture :image="$member['image']" ratio="portrait" sizes="(min-width: 1024px) 22vw, (min-width: 600px) 30vw, 50vw" class="ui-team__photo" />
                        @else
                            <span class="ui-team__initial" aria-hidden="true">{{ $member['initial'] }}</span>
                        @endif
                        <h2 class="ui-team__name">{{ $member['name'] }}</h2>
                        <p class="ui-team__title">{{ $member['title'] }}</p>
                        @if ($member['branch'] !== null)
                            <p class="ui-team__meta">{{ $member['branch'] }}</p>
                        @endif
                        @if ($member['joined'] !== null)
                            <p class="ui-team__meta">{{ __('family.joined', ['date' => $member['joined']]) }}</p>
                        @endif
                        @if ($member['bio'] !== null)
                            <p class="ui-team__bio">{{ $member['bio'] }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
