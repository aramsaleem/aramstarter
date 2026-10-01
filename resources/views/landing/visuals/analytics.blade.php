{{-- A small growing bar chart. --}}
<div aria-hidden="true" class="flex h-16 items-end gap-1.5">
    @foreach ([30, 55, 40, 70, 50, 85, 65, 100] as $height)
        <span class="flex-1 rounded-t-[4px] bg-linear-to-t from-primary-300 to-primary-500" style="height: {{ $height }}%"></span>
    @endforeach
</div>
