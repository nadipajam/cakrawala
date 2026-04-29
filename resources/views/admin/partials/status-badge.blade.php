@php($statusText = ucfirst(str_replace('_', ' ', (string) ($status ?? '-'))))
<span @class([
    'admin-badge',
    'admin-badge-pending' => in_array($status, ['pending', 'delayed', 'open', 'in_progress', 'submitted', 'in_review', 'selected', 'not_checked_in'], true),
    'admin-badge-confirmed' => in_array($status, ['confirmed', 'paid', 'completed', 'scheduled', 'resolved', 'approved', 'checked_in', 'boarded'], true),
    'admin-badge-cancelled' => in_array($status, ['cancelled', 'failed', 'refunded', 'closed', 'rejected'], true),
    'admin-badge-default' => ! in_array($status, ['pending', 'delayed', 'open', 'in_progress', 'submitted', 'in_review', 'selected', 'not_checked_in', 'confirmed', 'paid', 'completed', 'scheduled', 'resolved', 'approved', 'checked_in', 'boarded', 'cancelled', 'failed', 'refunded', 'closed', 'rejected'], true),
])>
    {{ $statusText }}
</span>
