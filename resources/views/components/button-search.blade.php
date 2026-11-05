<button type="button" {{ $attributes->merge(['class' => 'btn-solid theme-primary btn-sm']) }}>
    <i class="bi bi-search"></i>{{ $slot->isEmpty() ? ' Search' : ' ' . $slot }}
</button>