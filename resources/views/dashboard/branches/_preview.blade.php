{{--
    One language of the branch details preview (BRANCH-010), rendered in that language: the card exactly as the
    locations page draws it, then what the branch page lists under "Address and services". `$summary` = BranchSummary
    built from the details not published yet (BranchDirectory::preview) — nothing here is saved.
--}}
<x-ui.branch-card :branch="$summary" variant="panel" :level="4" :details-label="__('site.locations.details')" />
@if ($summary->landmark !== null || $summary->address !== null || $summary->mapsUrl !== null || $summary->services !== [] || $summary->payments !== [])
    <div class="ui-branch-preview__place">
        <h4 class="ui-record__subtitle">{{ __('site.branch.place') }}</h4>
        @if ($summary->landmark !== null)
            <p>{{ $summary->landmark }}</p>
        @endif
        @if ($summary->address !== null)
            <p>{{ $summary->address }}</p>
        @endif
        @if ($summary->mapsUrl !== null)
            <x-ui.button variant="secondary" size="sm" icon="map-pin" :href="$summary->mapsUrl" rel="noopener" target="_blank">{{ __('site.branch.directions') }}</x-ui.button>
        @endif
        @foreach (['services' => 'service', 'payments' => 'payment'] as $list => $group)
            @if ($summary->{$list} !== [])
                <h5 class="ui-record__subtitle">{{ __('site.branch.'.$list) }}</h5>
                <ul class="ui-cluster" role="list">
                    @foreach ($summary->{$list} as $key)
                        <li><x-ui.badge>{{ __('site.attributes.'.$group.'.'.$key) }}</x-ui.badge></li>
                    @endforeach
                </ul>
            @endif
        @endforeach
    </div>
@endif
