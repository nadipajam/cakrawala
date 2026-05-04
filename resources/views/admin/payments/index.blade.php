@extends('layouts.admin')

@section('title', 'Admin Payments | Cakrawala')
@section('page-title', 'Payments')

@section('content')
    <section class="space-y-5">
        <div class="grid gap-5 xl:grid-cols-[320px_minmax(0,1fr)]">
            <form method="GET" class="admin-ops-filter space-y-5">
                <div>
                    <p class="admin-section-kicker">Payment Queue</p>
                    <h2 class="admin-section-title">Filter transaksi pembayaran</h2>
                    <p class="admin-section-copy">Panel filter dipisahkan dari tabel agar pemeriksaan transaksi lebih nyaman di layar lebar maupun layar kecil.</p>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="admin-label" for="search">Cari booking/pengguna/metode/transaksi</label>
                        <input id="search" name="search" value="{{ $search }}" class="admin-field" placeholder="Booking code, user, method">
                    </div>
                    <div>
                        <label class="admin-label" for="status">Payment Status</label>
                        <select id="status" name="status" class="admin-field">
                            <option value="">Semua status</option>
                            <option value="pending" @selected($status === 'pending')>Pending</option>
                            <option value="paid" @selected($status === 'paid')>Paid</option>
                            <option value="failed" @selected($status === 'failed')>Failed</option>
                            <option value="refunded" @selected($status === 'refunded')>Refunded</option>
                        </select>
                    </div>
                </div>
                <div class="admin-divider space-y-3">
                    <p class="text-sm text-slate-500">Gunakan kombinasi pencarian dan status untuk mempercepat verifikasi, penolakan, atau audit manual.</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button class="admin-btn-primary" type="submit">Filter</button>
                        <a href="{{ route('admin.payments.index') }}" class="admin-btn-secondary">Atur Ulang</a>
                    </div>
                </div>
            </form>

            <div class="space-y-5">
                <article class="admin-ops-hero">
                    <div class="admin-section-head">
                        <div>
                            <p class="admin-section-kicker">Payment Ledger</p>
                            <h2 class="admin-section-title">Antrian transaksi</h2>
                            <p class="admin-section-copy">Tabel pembayaran sekarang ditemani ringkasan cepat supaya status pending, paid, dan bukti transfer lebih mudah ditinjau.</p>
                        </div>
                        <span class="admin-chip">{{ $payments->total() }} transaksi</span>
                    </div>

                    <div class="admin-ops-summary-grid">
                        <article class="admin-ops-summary-card">
                            <p class="label">Visible records</p>
                            <p class="value">{{ $payments->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Row yang tampil pada halaman aktif.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Pending</p>
                            <p class="value text-amber-600">{{ $payments->where('payment_status', 'pending')->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Transaksi pending di halaman aktif.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Paid</p>
                            <p class="value text-emerald-700">{{ $payments->where('payment_status', 'paid')->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Transaksi paid di halaman aktif.</p>
                        </article>
                    </div>
                </article>

                <article class="admin-ops-table-card">
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Booking Code</th>
                                    <th>User</th>
                                    <th>Method</th>
                                    <th>Midtrans</th>
                                    <th>Submitted Data</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Proof</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($payments as $payment)
                                    <tr>
                                        <td>{{ ($payments->firstItem() ?? 1) + $loop->index }}</td>
                                        <td>{{ $payment->booking?->booking_code }}</td>
                                        <td>{{ $payment->booking?->user?->name }}</td>
                                        <td>{{ \App\Support\PaymentMethodCatalog::label($payment->payment_method) }}</td>
                                        <td>
                                            <div class="text-xs leading-5 text-slate-600">
                                                <div>{{ $payment->midtrans_order_id ?: '-' }}</div>
                                                <div>Status code: {{ $payment->midtrans_status_code ?: '-' }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-xs leading-5 text-slate-600">
                                                <div>{{ $payment->payer_name ?: '-' }}</div>
                                                @if ($payment->payer_phone)
                                                    <div>HP: {{ $payment->payer_phone }}</div>
                                                @endif
                                                @if ($payment->payer_bank_name || $payment->payer_bank_account_number)
                                                    <div>{{ $payment->payer_bank_name }} {{ $payment->payer_bank_account_number ? '- '.$payment->payer_bank_account_number : '' }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>Rp{{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                                        <td>@include('admin.partials.status-badge', ['status' => $payment->payment_status])</td>
                                        <td>{{ $payment->proof_file ? 'Ada' : 'Tidak ada' }}</td>
                                        <td><a class="admin-btn-secondary" href="{{ route('admin.payments.show', $payment) }}">Detail</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="10" class="text-center text-slate-500">Data payment belum tersedia.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $payments->links() }}</div>
                </article>
            </div>
        </div>
    </section>
@endsection
