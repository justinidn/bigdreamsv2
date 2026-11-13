<x-app-layout>
    <div class="container-fluid py-3">
        <div class="row g-3">
            <div class="col-12 col-lg-6 col-xl-5">
                <x-screener-widget title="Latest Screener" />
            </div>

            {{-- Slot untuk card lainnya di samping kanan --}}
            <div class="col-12 col-lg-6 col-xl-7">
                {{-- Card berikutnya bisa ditaruh di sini --}}
            </div>
        </div>
    </div>
</x-app-layout>