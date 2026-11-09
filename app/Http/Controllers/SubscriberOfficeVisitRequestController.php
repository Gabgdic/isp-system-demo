<?php

namespace App\Http\Controllers;

use App\Models\SubscriberOfficeVisitRequest;
use App\Services\PhilSmsService;
use Illuminate\Http\Request;

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
            'action' => ['required', 'in:accept,reschedule'],
            'visit_date' => ['required_if:action,reschedule', 'nullable', 'date', 'after_or_equal:today'],
        ]);

        $rescheduled = $validated['action'] === 'reschedule';
        $officeVisitRequest->update([
            'status' => $rescheduled ? 'rescheduled' : 'accepted',
            'visit_date' => $rescheduled
                ? $validated['visit_date']
                : $officeVisitRequest->visit_date,
        ]);

        $smsSent = $smsService->sendOfficeVisitRequestUpdate($officeVisitRequest->fresh('subscriber'), $rescheduled);

        $message = $rescheduled
            ? 'The office visit date was moved.'
            : 'The office visit request was accepted.';

        if (!$smsSent) {
            return back()->with('warning', $message . ' SMS could not be sent; the update is available in the subscriber portal and is recorded in SMS logs.');
        }

        return back()->with('success', $message . ' An SMS notification was sent.');
    }
}
