@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'text-danger small mt-1']) }}>
        @foreach ((array) $messages as $message)
            <span class="d-block">{{ $message }}</span>
        @endforeach
    </div>
@endif
