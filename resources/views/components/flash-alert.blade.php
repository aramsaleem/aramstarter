{{-- Picked up by resources/js/app.js and shown as a SweetAlert toast. --}}
@if (session()->has('alert'))
    <script type="application/json" id="flash-alert">@json(session('alert'))</script>
@endif
