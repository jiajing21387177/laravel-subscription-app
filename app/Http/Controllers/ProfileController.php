<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show()
    {
        $subscriptionPlans = SubscriptionPlan::all();

        return view('profile', compact('subscriptionPlans'));
    }
}
