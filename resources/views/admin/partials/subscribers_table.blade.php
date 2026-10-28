<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500">
            <tr>
                <th class="px-4 py-3 text-left font-semibold">Subscriber</th>
                <th class="px-4 py-3 text-left font-semibold">Contact</th>
                <th class="px-4 py-3 text-left font-semibold">Plan</th>
                <th class="px-4 py-3 text-left font-semibold">Monthly Fee</th>
                <th class="px-4 py-3 text-left font-semibold">Date Created</th>
                <th class="px-4 py-3 text-left font-semibold">Status</th>
                <th class="px-4 py-3 text-left font-semibold">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
            @forelse($subscribers as $subscriber)
                @php
                    $serviceMonths = max(0, (int) $subscriber->created_at->diffInMonths(now()));
                @endphp
                <tr class="hover:bg-slate-100 transition">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            @if($subscriber->profile_photo)
                                <img src="{{ asset('storage/' . $subscriber->profile_photo) }}" class="w-9 h-9 rounded-full object-cover border border-slate-200 shadow-sm" alt="Subscriber Photo">
                            @else
                                <div class="w-9 h-9 rounded-full bg-slate-900 text-white flex items-center justify-center text-sm font-bold shadow-sm">
                                    {{ strtoupper(substr($subscriber->full_name ?? 'S', 0, 1)) }}
                                </div>
                            @endif

                            <div>
                                <p class="font-semibold text-slate-800">{{ $subscriber->full_name }}</p>
                                <p class="text-xs text-slate-400">{{ '@' . $subscriber->username }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-slate-700">{{ $subscriber->phone_number ?? 'No phone' }}</p>
                        <p class="text-xs text-slate-400">{{ $subscriber->email ?? 'No email' }}</p>
                    </td>
                    <td class="px-4 py-3">{{ $subscriber->plan_name ?? 'No plan' }}</td>
                    <td class="px-4 py-3 font-semibold">₱{{ number_format($subscriber->monthly_fee ?? 0, 2) }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span>{{ $subscriber->created_at->format('M d, Y') }}</span>
                        <span class="text-xs text-slate-500">({{ $serviceMonths === 0 ? 'Less than 1 month' : $serviceMonths . ' ' . ($serviceMonths === 1 ? 'month' : 'months') }})</span>
                    </td>
                    <td class="px-4 py-3">{{ ucfirst($subscriber->status) }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <button
                                type="button"
                                onclick="openEditSubscriberModal(this)"
                                data-id="{{ $subscriber->id }}"
                                data-full-name="{{ $subscriber->full_name }}"
                                data-username="{{ $subscriber->username }}"
                                data-email="{{ $subscriber->email }}"
                                data-phone-number="{{ $subscriber->phone_number }}"
                                data-address="{{ $subscriber->address }}"
                                data-subscription-plan-id="{{ $subscriber->subscription_plan_id }}"
                                data-plan-name="{{ $subscriber->plan_name }}"
                                data-monthly-fee="{{ $subscriber->monthly_fee }}"
                                class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-200 hover:text-slate-900 transition-all duration-200"
                                title="Edit"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>

                            @if($subscriber->status === 'disconnected')
                                @if($subscriber->past_due_pending_count > 0)
                                    <span class="text-xs text-slate-500">Payment awaiting review</span>
                                @elseif((float) $subscriber->past_due_balance > 0)
                                    <form method="POST" action="{{ route('subscriber.record-past-due-payment', $subscriber->id) }}" onsubmit="return confirm('Record the full past-due payment of ₱{{ number_format((float) $subscriber->past_due_balance, 2) }}?')">
                                        @csrf
                                        <button type="submit" class="text-xs font-semibold text-slate-700 hover:underline">Record Payment</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('subscriber.reactivate', $subscriber->id) }}" onsubmit="return confirm('Reactivate this subscriber account?')">
                                        @csrf
                                        <button type="submit" class="text-xs font-semibold text-slate-700 hover:underline">Reactivate</button>
                                    </form>
                                @endif
                            @endif

                            <button
                                type="button"
                                onclick="openDeleteSubscriberModal({{ $subscriber->id }}, @js($subscriber->full_name))"
                                class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-200 hover:text-slate-900 transition-all duration-200"
                                title="Delete"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                        {{ $search !== '' ? 'No subscribers match your search.' : 'No subscribers found. Create your first subscriber account.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($subscribers->hasPages())
    <div class="px-4 py-3 border-t border-slate-200">
        {{ $subscribers->links() }}
    </div>
@endif