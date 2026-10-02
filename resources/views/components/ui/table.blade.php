{{--
    Table (DS §8): caption + scoped headers inside a labelled, keyboard-scrollable region — the page itself never
    scrolls sideways. Data-driven: `columns` = [['key' => …, 'label' => …, 'numeric' => bool]], `rows` = list of
    [key => value], `row-header` = the key rendered as <th scope="row">. Or pass your own <thead>/<tbody> in the slot.
    `stack`: rows become label/value cards below 600px (explicit table roles keep the semantics when CSS changes display);
    `stack="wide"`: below 1200px, for tables with many columns next to the dashboard navigation.
--}}
@props([
    'caption',
    'columns' => [],
    'rows' => [],
    'rowHeader' => null,
    'stack' => false,
])
@php
    $tableId = $attributes->get('id', 'table-'.substr(sha1((string) $caption), 0, 10));
@endphp
<div class="ui-table-wrap" role="region" aria-labelledby="{{ $tableId }}-caption" tabindex="0">
    <table {{ $attributes->class(['ui-table', 'ui-table--stack' => (bool) $stack, 'ui-table--stack-wide' => $stack === 'wide'])->merge(['id' => $tableId] + ($stack ? ['role' => 'table'] : [])) }}>
        <caption class="ui-table__caption" id="{{ $tableId }}-caption">{{ $caption }}</caption>
        @if (count($columns) > 0)
            <thead @if ($stack) role="rowgroup" @endif>
                <tr @if ($stack) role="row" @endif>
                    @foreach ($columns as $column)
                        <th scope="col" @class(['ui-table__numeric' => $column['numeric'] ?? false]) @if ($stack) role="columnheader" @endif>{{ $column['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody @if ($stack) role="rowgroup" @endif>
                @foreach ($rows as $row)
                    <tr @if ($stack) role="row" @endif>
                        @foreach ($columns as $column)
                            @if ($rowHeader !== null && $column['key'] === $rowHeader)
                                <th scope="row" data-label="{{ $column['label'] }}" @class(['ui-table__numeric' => $column['numeric'] ?? false]) @if ($stack) role="rowheader" @endif>{{ $row[$column['key']] ?? '' }}</th>
                            @else
                                <td data-label="{{ $column['label'] }}" @class(['ui-table__numeric' => $column['numeric'] ?? false]) @if ($stack) role="cell" @endif>{{ $row[$column['key']] ?? '' }}</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        @else
            {{ $slot }}
        @endif
    </table>
</div>
