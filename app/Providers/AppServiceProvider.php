<?php

namespace App\Providers;

use App\Models\PaymentSubmission;
use App\Models\SubscriberDisconnection;
use App\Models\SubscriberOfficeVisitRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view): void {
            if (!Auth::check()) {
                $view->with('adminNotifications', collect());
                return;
            }

            $notifications = collect();

            PaymentSubmission::with('subscriber')
                ->where('status', 'pending')
                ->latest()
                ->limit(20)
                ->get()
                ->each(function (PaymentSubmission $payment) use ($notifications): void {
                    $notifications->push([
                        'id' => "payment-submission:{$payment->id}:{$payment->updated_at->timestamp}",
                        'title' => 'New payment request',
                        'message' => sprintf(
                            '%s submitted a payment request for PHP %s.',
                            $payment->subscriber?->full_name ?? 'A subscriber',
                            number_format((float) $payment->amount_paid, 2)
                        ),
                        'date' => $payment->created_at,
                        'url' => route('billing'),
                    ]);
                });

            SubscriberOfficeVisitRequest::with('subscriber')
                ->where('status', 'pending')
                ->latest()
                ->limit(20)
                ->get()
                ->each(function (SubscriberOfficeVisitRequest $request) use ($notifications): void {
                    $notifications->push([
                        'id' => "office-visit-request:{$request->id}:{$request->updated_at->timestamp}",
                        'title' => 'New payment reduction request',
                        'message' => sprintf(
                            '%s requested an ISP office visit for %s.',
                            $request->subscriber?->full_name ?? 'A subscriber',
                            $request->visit_date->format('M d, Y')
                        ),
                        'date' => $request->created_at,
                        'url' => route('reports', ['show_requests' => 1]),
                    ]);
                });

            SubscriberDisconnection::with('subscriber')
                ->latest('disconnected_at')
                ->limit(20)
                ->get()
                ->each(function (SubscriberDisconnection $disconnection) use ($notifications): void {
                    $notifications->push([
                        'id' => "subscriber-disconnected:{$disconnection->id}:{$disconnection->disconnected_at->timestamp}",
                        'title' => 'Subscriber disconnected',
                        'message' => sprintf(
                            '%s was disconnected.',
                            $disconnection->subscriber?->full_name ?? 'A subscriber'
                        ),
                        'date' => $disconnection->disconnected_at,
                        'url' => route('subscriber'),
                    ]);
                });

            $view->with(
                'adminNotifications',
                $notifications->sortByDesc(fn (array $notification) => $notification['date'])->values()
            );
        });
    }
}
