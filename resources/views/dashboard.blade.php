<x-app-layout>
    <div class="container-fluid py-3">
        <div class="row row-cols-1 row-cols-md-2 g-3">
            <div class="col">
                <x-screener-widget title="Latest Screener" />
            </div>

            <div class="col">
                <x-volume-breakout-widget title="Volume Breakout" />
            </div>
        </div>
    </div>
</x-app-layout>