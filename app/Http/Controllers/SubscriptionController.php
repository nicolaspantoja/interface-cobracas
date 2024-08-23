<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Cashier\Exceptions\IncompletePayment;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /** @var User $user */
        $user = auth()->user();
        return view('subscription', [
            'intent' => $user->createSetupIntent([
                'payment_method_types' => ['card', 'boleto']
            ])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            /** @var User $user */
            $user = $request->user();
            $user->newSubscription(
                $request->plan,
                $request->price_id
            )
                ->create($request->payment_method, subscriptionOptions: [
                    'metadata' => [
                        'plan' => $request->plan,
                        'price_id' => $request->price_id
                    ]
                ]);

            return redirect()->route('subscription.success');
        } catch (IncompletePayment $exception) {
            dd($exception);
            if ($exception->payment->status === 'requires_action' && $exception->payment->next_action->type === 'boleto_display_details') {
                dd($exception->payment->next_action->boleto_display_details->pdf);
            }
            return redirect()->route(
                'cashier.payment',
                [$exception->payment->id, 'redirect' => route('home')]
            );
        }

        // return $request->user()
        //   ->newSubscription(request('plan'), request('price_id'))
        //   // ->trialDays(5)
        //   ->allowPromotionCodes()
        //   ->checkout([
        //     'success_url' => route('subscription.success'),
        //     'cancel_url' => route('subscription.cancelled'),
        //     'metadata' => [
        //       'plan' => request('plan'),
        //       'price_id' => request('price_id')
        //     ],
        //   ]);
    }

    public function success()
    {
        dd('Subscription successful');
    }

    public function cancel()
    {
        dd('Subscription canceled');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
