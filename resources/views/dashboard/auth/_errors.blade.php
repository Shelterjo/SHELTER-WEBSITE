@if ($errors->any())
    <div class="ui-alert ui-alert--danger" role="alert" id="form-errors">
        <ul role="list">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
