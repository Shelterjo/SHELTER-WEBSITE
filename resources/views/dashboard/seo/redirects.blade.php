@extends('layouts.dashboard')

@section('title', __('dashboard.redirects.title'))

@section('content')
    {{--
        Google visibility → Old links (SEO-018, App\Services\Seo\LegacyRedirects): add an old address and its new page
        (saved switched off), then switch it on — that is the approval. On / off / archive per row; hits show which old
        links still bring visitors. Archive instead of delete.
    --}}
    @php
        $R = 'dashboard.redirects.';
        $codeOptions = collect($codes)->mapWithKeys(fn (int $c): array => [(string) $c => __($R.'codes.'.$c)])->all();
    @endphp
    <x-ui.page-header :title="__($R.'title')" :description="__($R.'description')" />

    <p class="ui-note">{{ __($R.'summary', ['active' => $activeCount, 'total' => $rows->where('state', '!=', 'archived')->count()]) }}</p>

    <div class="ui-record">
        @forelse ($rows as $row)
            @php
                $bag = $errors->getBag('r'.$row->id);
                $fresh = $bag->any();
            @endphp
            <section class="ui-record__section" id="r{{ $row->id }}" aria-labelledby="r{{ $row->id }}-title">
                <h2 class="ui-record__title" id="r{{ $row->id }}-title"><bdi dir="ltr">{{ $row->source_path }}</bdi></h2>
                <p class="ui-record__status">
                    @if ($row->state === 'active')
                        <x-ui.badge variant="success" icon="circle-check">{{ __($R.'states.active') }}</x-ui.badge>
                    @elseif ($row->state === 'draft')
                        <x-ui.badge>{{ __($R.'states.draft') }}</x-ui.badge>
                    @else
                        <x-ui.badge variant="info">{{ __($R.'states.archived') }}</x-ui.badge>
                    @endif
                    <span>{{ __($R.'codes.'.$row->status_code) }}</span>
                </p>
                <p>
                    <span>{{ __($R.'columns.target') }}:</span>
                    @if ($row->target !== null)
                        <bdi dir="ltr">{{ $row->target }}</bdi>
                    @else
                        {{ __($R.'gone') }}
                    @endif
                </p>
                <p class="ui-note">
                    {{ $row->hits > 0 ? trans_choice('dashboard.redirects_hits', $row->hits, ['count' => $row->hits]).' · '.__($R.'last_hit', ['when' => $row->last_hit_at?->timezone('Asia/Amman')->format('Y-m-d H:i')]) : __($R.'never') }}
                    @if ($row->origin === 'plan')
                        · {{ __($R.'origin_plan') }}@if ($row->decision_ref) ({{ $row->decision_ref }})@endif
                    @endif
                    @if ($row->note)
                        · {{ $row->note }}
                    @endif
                </p>
                @if ($bag->has('command'))
                    <x-ui.alert variant="danger">{{ $bag->first('command') }}</x-ui.alert>
                @endif
                <div class="ui-cluster">
                    @foreach ($row->state === 'archived' ? ['restore'] : ($row->state === 'active' ? ['deactivate', 'archive'] : ['activate', 'archive']) as $command)
                        <form method="post" action="{{ route('dashboard.redirects.command', [$row, $command]) }}">
                            @csrf
                            <x-ui.button type="submit" size="sm" :variant="$command === 'activate' ? 'primary' : 'outline'">{{ __($R.'commands.'.$command) }}</x-ui.button>
                        </form>
                    @endforeach
                </div>
                @if ($row->state !== 'archived')
                    <x-ui.disclosure :summary="__($R.'edit')" :open="$fresh && ! $bag->has('command')">
                        <form method="post" action="{{ route('dashboard.redirects.update', $row) }}" class="ui-stack">
                            @csrf
                            @method('PUT')
                            @include('dashboard.seo.redirect-fields', ['prefix' => 'r'.$row->id, 'bag' => $bag, 'fresh' => $fresh, 'row' => $row])
                            <div><x-ui.button type="submit">{{ __($R.'save') }}</x-ui.button></div>
                        </form>
                    </x-ui.disclosure>
                @endif
            </section>
        @empty
            <p class="ui-note">{{ __($R.'empty') }}</p>
        @endforelse

        @php
            $bag = $errors->getBag('new');
            $fresh = $bag->any();
        @endphp
        <form class="ui-record__section ui-stack" id="new" method="post" action="{{ route('dashboard.redirects.store') }}" aria-labelledby="new-title">
            @csrf
            <h2 class="ui-record__title" id="new-title">{{ __($R.'add_title') }}</h2>
            @include('dashboard.seo.redirect-fields', ['prefix' => 'new', 'bag' => $bag, 'fresh' => $fresh, 'row' => null])
            <div><x-ui.button type="submit">{{ __($R.'add') }}</x-ui.button></div>
        </form>
    </div>
@endsection
