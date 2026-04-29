<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $detail->ticket_number }}</title>
    <style>
        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #1e293b;
            background: #f6fbff;
        }

        .page {
            padding: 30px;
        }

        .ticket {
            border: 1px solid #cbd5e1;
            border-radius: 24px;
            background: linear-gradient(160deg, #e8f4ff 0%, #f9fbff 65%, #ffffff 100%);
            overflow: hidden;
        }

        .header {
            padding: 28px 30px 20px;
            border-bottom: 1px solid #d8e6f3;
        }

        .eyebrow {
            font-size: 11px;
            letter-spacing: 0.35em;
            text-transform: uppercase;
            color: #315b8c;
        }

        .brand {
            margin-top: 12px;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: 0.14em;
            color: #102b4f;
        }

        .ticket-number {
            margin: 16px 0 6px;
            font-size: 34px;
            font-weight: 700;
            line-height: 1.1;
            word-break: break-word;
            color: #1f3150;
        }

        .sub {
            font-size: 13px;
            color: #51657e;
        }

        .issued {
            margin-top: 12px;
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            background: #d1fae5;
            color: #0f766e;
            font-size: 12px;
            font-weight: 700;
        }

        .body {
            padding: 24px 30px 28px;
        }

        .grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px;
            margin: 0 -12px;
        }

        .card {
            width: 50%;
            padding: 16px 18px;
            border: 1px solid #d6e2ef;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.9);
            vertical-align: top;
        }

        .label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #64748b;
        }

        .value {
            margin-top: 6px;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.2;
            word-break: break-word;
            color: #1e293b;
        }

        .qr-wrap {
            margin-top: 20px;
            padding: 18px;
            border: 1px dashed #cdd8e5;
            border-radius: 18px;
            background: #ffffff;
            text-align: center;
        }

        .qr-wrap svg {
            width: 165px;
            height: 165px;
        }

        .footer-note {
            margin-top: 14px;
            font-size: 11px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="ticket">
            <div class="header">
                <div class="eyebrow">E-Ticket</div>
                <div class="brand">ZANNORA</div>
                <div class="ticket-number">{{ $detail->ticket_number }}</div>
                <div class="sub">{{ $flight->airline->name }} | {{ $booking->booking_code }}</div>
                <div class="issued">Issued {{ $ticket->issued_at?->format('d M Y H:i') }}</div>
            </div>

            <div class="body">
                <table class="grid">
                    <tr>
                        <td class="card">
                            <div class="label">Passenger</div>
                            <div class="value">{{ $detail->passenger?->full_name }}</div>
                        </td>
                        <td class="card">
                            <div class="label">Seat</div>
                            <div class="value">{{ $detail->seat?->seat_number }} | {{ $cabinLabel }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="card">
                            <div class="label">Route</div>
                            <div class="value">{{ $flight->departureAirport->code }} -> {{ $flight->arrivalAirport->code }}</div>
                        </td>
                        <td class="card">
                            <div class="label">Boarding Time</div>
                            <div class="value">{{ $flight->departure_time->format('d M Y H:i') }}</div>
                        </td>
                    </tr>
                </table>

                <div class="qr-wrap">
                    {!! $qrMarkup !!}
                </div>
                <div class="footer-note">Present this e-ticket and a valid ID during check-in.</div>
            </div>
        </div>
    </div>
</body>
</html>
