@extends('layouts.app')

@section('content')
    <script>
        setTimeout(() => {
            window.location = "{{ route('profile') }}"
        }, 5000);
    </script>
    <div class="container">
        <h1>Checkout Cancel</h1>
        <p>Your payment has been canceled.</p>
    </div>
@endsection
