{{--
    Weekly opening hours (BRANCH-008, HOURS-012): one row per day in week order, today marked with a badge and
    aria-current="date" (not colour alone), a day without hours reads "Closed", and an interval that ends after
    midnight says so ("2:00 AM next day"). `rows` = BranchSummary::$week; `caption` names the table.
--}}
@props([
    'rows',
    'caption',
])
<table {{ $attributes->class('ui-hours') }}>
    <caption class="ui-visually-hidden">{{ $caption }}</caption>
    <thead class="ui-visually-hidden">
        <tr>
            <th scope="col">{{ __('ui.hours.day') }}</th>
            <th scope="col">{{ __('ui.hours.time') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
            <tr @class(['ui-hours__row', 'ui-hours__row--today' => $row['today']]) @if ($row['today']) aria-current="date" @endif>
                <th scope="row" class="ui-hours__day">
                    <span>{{ $row['day'] }}</span>
                    @if ($row['today'])
                        <span class="ui-hours__today">{{ __('ui.hours.today') }}</span>
                    @endif
                </th>
                <td class="ui-hours__time">
                    @forelse ($row['intervals'] as $interval)
                        <span class="ui-hours__interval">
                            <span class="ui-hours__range">{{ $interval['opens'] }} – {{ $interval['closes'] }}</span>
                            @if ($interval['overnight'])
                                <span class="ui-hours__next">{{ __('ui.hours.next_day') }}</span>
                            @endif
                        </span>
                    @empty
                        <span class="ui-hours__closed">{{ __('ui.hours.closed') }}</span>
                    @endforelse
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
