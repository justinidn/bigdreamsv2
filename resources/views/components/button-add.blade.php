<button type="button" {{ $attributes->merge(['class' => 'btn-solid theme-success btn-sm']) }}>
    <i class="bi bi-plus-circle"></i>{{ $slot->isEmpty() ? ' Add' : ' ' . $slot }}
</button>