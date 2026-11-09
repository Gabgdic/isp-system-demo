<?php

namespace App\Http\Controllers;

use App\Models\SubscriberOfficeVisitRequest;
use App\Services\PhilSmsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriberOfficeVisitRequestController extends Controller
{
    public function review(
        Request $request,
        SubscriberOfficeVisitRequest $officeVisitRequest,
        PhilSmsService $smsService
    ) {
        if ($officeVisitRequest->status !== 'pending') {
            return back()->with('warning', 'This office visit request has already been reviewed.');
        }

        $validated = $request->validate([
            'action' => ['required', 'in:accept,reschedule,reject'],
            'visit_date' => [
                Rule::requiredIf(fn () => $request->input('action') === 'reschedule'
                    || ($request->input('action') === 'accept' && $officeVisitRequest->request_type === 'internet_fix')),
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ]);

        $rescheduled = $validated['action'] === 'reschedule';
        $rejected = $validated['action'] === 'reject';
        $officeVisitRequest->update([
            'status' => $rescheduled ? 'rescheduled' : ($rejected ? 'rejected' : 'accepted'),
            'visit_date' => $rescheduled || (!$rejected && $officeVisitRequest->request_type === 'internet_fix')
                ? $validated['visit_date']
                : $officeVisitRequest->visit_date,
        ]);

        $smsSent = $smsService->sendOfficeVisitRequestUpdate(
            $officeVisitRequest->fresh('subscriber'),
            $officeVisitRequest->status
        );

        $message = match ($officeVisitRequest->status) {
            'rescheduled' => 'The office visit date was moved.',
            'rejected' => 'The office visit request was rejected.',
            default => 'The office visit request was accepted.',
        };

        if (!$smsSent) {
            return back()->with('warning', $message . ' SMS could not be sent; the update is available in the subscriber portal and is recorded in SMS logs.');
        }

        return back()->with('success', $message . ' An SMS notification was sent.');
    }
}
