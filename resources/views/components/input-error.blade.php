@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'invalid-feedback d-block list-unstyled']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
