@extends('layouts.app')

@section('content')
    @php
        $user = auth()->user();
    @endphp
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>
                    {{ __('Profile') }}
                </h1>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h2>
                            {{ __('About me') }}
                        </h2>
                        <hr>
                        <div class="row">
                            <div class="col-6 col-md-3 font-weight-bold">
                                {{ __('ID') }}
                            </div>
                            <div class="col-6 col-md-3">
                                U-{{ str_pad($user->id, 6, '0', STR_PAD_LEFT) }}
                            </div>
                            <div class="col-6 col-md-3 font-weight-bold">
                                {{ __('Name') }}
                            </div>
                            <div class="col-6 col-md-3">
                                {{ $user->name }}
                            </div>
                            <div class="col-6 col-md-3 font-weight-bold">
                                {{ __('Email') }}
                            </div>
                            <div class="col-6 col-md-3">
                                {{ $user->email }}
                            </div>
                            <div class="col-6 col-md-3 font-weight-bold">
                                {{ __('Verified at') }}
                            </div>
                            <div id="email-verified-at" class="col-6 col-md-3">
                            </div>
                        </div>
                        <h2 class="mt-4">
                            {{ __('Subscription') }}
                        </h2>
                        <hr>
                        @include('subscription.subscriptionList', [
                            'subscriptionPlans' => $subscriptionPlans,
                            'user' => $user,
                        ])
                        <hr>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        const datetime = moment.utc('{{ $user->email_verified_at }}').local().format('LLL')
        document.getElementById('email-verified-at').innerText = datetime
    </script>
@endsection
