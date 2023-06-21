@extends('layouts.app')

@section('content')
    <script>
        setTimeout(() => {
            window.location = "{{ route('profile') }}"
        }, 5000);
    </script>
    <div class="container">
        <h1>Checkout Success</h1>
        <p>Your payment will be process.</p>
        <p>Thank you for your subscription!</p>
    </div>
@endsection
