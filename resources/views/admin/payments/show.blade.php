@extends('layouts.admin')

@section('title', 'Payment Detail | Cakrawala')
@section('page-title', 'Payment Detail')

@section('content')
    <section class="space-y-6">
        <article class="admin-hero-card">
            <div class="admin-section-head">
                <div class="max-w-3xl">
                    <p class="admin-section-kicker">Payment Review</p>
                    <h2 class="admin-section-title">Detail transaksi pembayaran</h2>
                    <p class="admin-section-copy">Panel ini menggabungkan data transaksi, data pengirim, proof file, dan penumpang booking agar verifikasi manual lebih cepat.</p>
                </div>
                <div>@include('admin.partials.status-badge', ['status' => $payment->payment_status])</div>
            </div>

            <div class="mt-5 grid gap-4 xl:grid-cols-[1.35fr_.95fr]">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="admin-info-card"><p class="text-sm text-slate-500">Payment ID</p><p class="mt-2 font-semibold text-slate-800">{{ $payment->id }}</p></div>
                    <div class="admin-info-card"><p class="text-sm text-slate-500">Booking</p><p class="mt-2 font-semibold text-slate-800">{{ $payment->booking?->booking_code }}</p></div>
                    <div class="admin-info-card"><p class="text-sm text-slate-500">User</p><p class="mt-2 font-semibold text-slate-800">{{ $payment->booking?->user?->name }}</p></div>
                    <div class="admin-info-card"><p class="text-sm text-slate-500">Method</p><p class="mt-2 font-semibold text-slate-800">{{ \App\Support\PaymentMethodCatalog::label($payment->payment_method) }}</p></div>
                    <div class="admin-info-card"><p class="text-sm text-slate-500">Amount</p><p class="mt-2 font-semibold text-slate-800">Rp{{ number_format((float) $payment->amount, 0, ',', '.') }}</p></div>
                    <div class="admin-info-card"><p class="text-sm text-slate-500">Transaction Code</p><p class="mt-2 font-semibold text-slate-800">{{ $payment->transaction_code ?: '-' }}</p></div>
                    <div class="admin-info-card"><p class="text-sm text-slate-500">Paid At</p><p class="mt-2 font-semibold text-slate-800">{{ $payment->paid_at?->format('d M Y H:i') ?: '-' }}</p></div>
                    <div class="admin-info-card"><p class="text-sm text-slate-500">Submitted At</p><p class="mt-2 font-semibold text-slate-800">{{ $payment->submitted_at?->format('d M Y H:i') ?: '-' }}</p></div>
                    <div class="admin-info-card"><p class="text-sm text-slate-500">Payer Name</p><p class="mt-2 font-semibold text-slate-800">{{ $payment->payer_name ?: '-' }}</p></div>
                    <div class="admin-info-card"><p class="text-sm text-slate-500">Payer Phone</p><p class="mt-2 font-semibold text-slate-800">{{ $payment->payer_phone ?: '-' }}</p></div>
                    <div class="admin-info-card"><p class="text-sm text-slate-500">Sender Bank</p><p class="mt-2 font-semibold text-slate-800">{{ $payment->payer_bank_name ?: '-' }}</p></div>
                    <div class="admin-info-card"><p class="text-sm text-slate-500">Sender Account</p><p class="mt-2 font-semibold text-slate-800">{{ $payment->payer_bank_account_number ?: '-' }}</p></div>
                </div>

                <aside class="space-y-4">
                    <div class="admin-list-card">
                        <p class="admin-section-kicker">Booking Link</p>
                        <h3 class="mt-2 font-heading text-lg font-bold text-slate-800">Informasi booking</h3>
                        <div class="mt-4 space-y-3">
                            <div class="admin-list-row">
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">{{ $payment->booking?->flight?->flight_number }} - {{ $payment->booking?->flight?->airline?->name }}</p>
                                    <p class="text-sm text-slate-500">{{ $payment->booking?->flight?->departureAirport?->code }} - {{ $payment->booking?->flight?->arrivalAirport?->code }}</p>
                                </div>
                            </div>
                            <div class="admin-list-row">
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">Notes</p>
                                    <p class="text-sm text-slate-500">{{ $payment->payment_notes ?: '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-surface-muted">
                        <p class="admin-section-kicker">Proof File</p>
                        <h3 class="mt-2 font-heading text-lg font-bold text-slate-800">Bukti pembayaran</h3>
                        @if ($payment->proof_file)
                            @php($proofUrl = str_starts_with($payment->proof_file, 'http') ? $payment->proof_file : asset('storage/'.$payment->proof_file))
                            <a href="{{ $proofUrl }}" target="_blank" class="mt-3 inline-flex text-sm font-semibold text-[#0f3f78] underline">Lihat bukti pembayaran</a>
                        @else
                            <p class="mt-3 text-sm text-slate-600">Bukti pembayaran belum diupload.</p>
                        @endif
                    </div>

                    @if ($payment->payment_status === 'pending' && (auth()->user()->isAdmin() || auth()->user()->isStaff()))
                        <div class="admin-list-card space-y-3">
                            <p class="admin-section-kicker">Verification</p>
                            <h3 class="font-heading text-lg font-bold text-slate-800">Aksi transaksi</h3>
                            <form method="POST" action="{{ route('admin.payments.verify', $payment) }}">
                                @csrf
                                <button class="admin-btn-primary w-full" type="submit">Verifikasi Payment</button>
                            </form>

                            <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" onsubmit="return confirm('Tolak payment ini?')">
                                @csrf
                                <button class="admin-btn-secondary w-full" type="submit">Tolak Payment</button>
                            </form>
                            <p class="text-sm text-slate-500">Transaction code akan digenerate otomatis saat payment diverifikasi.</p>
                        </div>
                    @endif
                </aside>
            </div>
        </article>

        <article class="admin-card">
            <div class="admin-section-head">
                <div>
                    <p class="admin-section-kicker">Passenger Manifest</p>
                    <h2 class="admin-section-title">Passenger dalam booking</h2>
                </div>
                <span class="admin-chip">{{ collect($payment->booking?->details ?? [])->count() }} passenger</span>
            </div>
            <div class="admin-table-wrap mt-4">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Passenger</th>
                            <th>Seat</th>
                            <th>Ticket</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($payment->booking?->details ?? [] as $detail)
                            <tr>
                                <td>{{ $detail->passenger?->full_name }}</td>
                                <td>{{ $detail->seat?->seat_number }} ({{ ucfirst($detail->seat?->class ?? '-') }})</td>
                                <td>
                                    @if ($detail->ticket)
                                        <a href="{{ route('admin.tickets.show', $detail->ticket) }}" class="admin-btn-secondary">Lihat Ticket</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-slate-500">Tidak ada detail passenger.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection
