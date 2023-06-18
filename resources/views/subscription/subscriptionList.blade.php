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
                        {{ $plan->name }} (RM{{ $plan->price }} / {{ $plan->duration }} {{ __('days') }})
                    </h4>
                    <p class="white-space-pre-line">{{ $plan->description }}</p>
                </div>
                <div class="col-12 col-md-2">
                    <form action="{{ route('subscribe') }}" method="POST">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <button type="submit" class="btn btn-primary btn-block h-100">
                            {{ __('Subscribe') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
