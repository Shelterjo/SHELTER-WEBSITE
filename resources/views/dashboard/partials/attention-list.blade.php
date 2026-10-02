{{-- One list for the attention screen, the Command Center and the AI Assistant: severity, title, when, the fix. --}}
@php $A = 'dashboard.attention.'; @endphp
<ul class="ui-stack" role="list">
    @foreach ($items as $item)
        @php $s = $item['signal']; @endphp
        <li class="ui-record__section">
            <p class="ui-record__status">
                <x-ui.badge :variant="in_array($s->severity->value, ['CRITICAL', 'HIGH'], true) ? 'danger' : ($s->severity->value === 'MEDIUM' ? 'warning' : 'info')" :icon="in_array($s->severity->value, ['CRITICAL', 'HIGH'], true) ? 'circle-alert' : 'info'">{{ __($A.'severity.'.$s->severity->value) }}</x-ui.badge>
                <strong>{{ $item['title'] }}</strong>
            </p>
            <p class="ui-note">
                {{ __($A.'seen', ['when' => $s->last_seen_at?->timezone('Asia/Amman')->format('Y-m-d H:i')]) }}
                @if ($s->occurrences > 1)
                    · {{ __($A.'times', ['n' => $s->occurrences]) }}
                @endif
                @if ($s->recommended_action && __($A.'actions.'.$s->recommended_action) !== $A.'actions.'.$s->recommended_action)
                    · {{ __($A.'actions.'.$s->recommended_action) }}
                @endif
            </p>
            <div class="ui-cluster">
                @if ($item['href'])
                    <x-ui.button size="sm" variant="secondary" :href="$item['href']" icon-end="arrow-right">{{ __($A.'fix') }}</x-ui.button>
                @endif
                @if ($item['dismissible'])
                    <form method="post" action="{{ route('dashboard.attention.dismiss', $s) }}">
                        @csrf
                        <x-ui.button type="submit" size="sm" variant="ghost">{{ __($A.'dismiss') }}</x-ui.button>
                    </form>
                @endif
            </div>
        </li>
    @endforeach
</ul>
