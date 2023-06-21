@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@foreach ($subscriptionPlans as $plan)
    <div class="card my-3">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-10">
                    <h4>
                        {{ $plan->name }} (RM{{ $plan->price }} / {{ $plan->recurring }}
                    </h4>
                    <p class="white-space-pre-line">{{ $plan->description }}</p>
                </div>
                <div class="col-12 col-md-2">
                    @if (empty($user->subscription))
                        <form action="{{ route('subscribe') }}" method="POST">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <button type="submit" class="btn btn-primary btn-block h-100">
                                {{ __('Subscribe') }}
                            </button>
                        </form>
                    @elseif($plan->id === $user->subscription->subscription_plan_id)
                        @if ($user->subscription->is_canceled === 0)
                            {{ __('Next bill: ') }}
                        @else
                            {{ __('Service end: ') }}
                        @endif
                        <span id="end-date"></span>
                        <script>
                            const date = moment.utc('{{ $user->subscription->subscription_end_datetime }}').local().format('DD/MM/YYYY');
                            document.getElementById("end-date").innerText = date;
                        </script>
                        @if ($user->subscription->is_canceled === 0)
                            <form action="{{ route('unsubscribe') }}" method="POST">
                                @csrf
                                <input type="hidden" name="user_subscription_id" value="{{ $userSubscription->id }}">
                                <button type="submit" class="btn btn-danger btn-block">
                                    {{ __('Unsubscribe') }}
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach
