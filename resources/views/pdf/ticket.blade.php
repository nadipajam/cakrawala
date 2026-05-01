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
            background: #f8fafc;
        }

        .page {
            padding: 28px;
        }

        .ticket {
            overflow: hidden;
            border: 1px solid #d7e3f2;
            border-radius: 28px;
            background: #ffffff;
        }

        .hero {
            padding: 28px 30px 24px;
            background:
                radial-gradient(circle at top right, rgba(125, 211, 252, 0.2) 0%, transparent 24%),
                linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
            color: #f8fafc;
        }

        .eyebrow {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .32em;
            text-transform: uppercase;
            color: rgba(191, 219, 254, 0.92);
        }

        .code {
            margin-top: 16px;
            font-size: 34px;
            font-weight: 700;
            line-height: 1.05;
            word-break: break-word;
        }

        .sub {
            margin-top: 6px;
            font-size: 13px;
            color: rgba(226, 232, 240, 0.88);
        }

        .issued {
            margin-top: 14px;
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.14);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .body {
            padding: 24px 30px 30px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px;
            margin: 0 -12px;
        }

        td {
            width: 50%;
            padding: 16px 18px;
            border: 1px solid #dbe5f0;
            border-radius: 20px;
            background: #f8fafc;
            vertical-align: top;
        }

        .label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: #64748b;
        }

        .value {
            margin-top: 6px;
            font-size: 19px;
            font-weight: 700;
            line-height: 1.2;
            word-break: break-word;
            color: #0f172a;
        }

        .bottom {
            margin-top: 18px;
            overflow: hidden;
        }

        .qr-wrap {
            float: left;
            width: 210px;
            padding: 16px;
            border: 1px dashed #cbd5e1;
            border-radius: 20px;
            background: #ffffff;
            text-align: center;
        }

        .qr-wrap svg {
            width: 155px;
            height: 155px;
        }

        .meta {
            margin-left: 236px;
        }

        .meta-card {
            margin-bottom: 12px;
            padding: 14px 16px;
            border: 1px solid #dbe5f0;
            border-radius: 18px;
            background: #f8fafc;
        }

        .note {
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
            <div class="hero">
                <div class="eyebrow">E-Ticket Archive</div>
                <div class="code">{{ $detail->ticket_number }}</div>
                <div class="sub">{{ $flight->airline->name }} | {{ $booking->booking_code }}</div>
                <div class="issued">Issued {{ $ticket->issued_at?->format('d M Y H:i') }}</div>
            </div>

            <div class="body">
                <table>
                    <tr>
                        <td>
                            <div class="label">Passenger</div>
                            <div class="value">{{ $detail->passenger?->full_name }}</div>
                        </td>
                        <td>
                            <div class="label">Seat</div>
                            <div class="value">{{ $detail->seat?->seat_number }} | {{ $cabinLabel }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Route</div>
                            <div class="value">{{ $flight->departureAirport->code }} -> {{ $flight->arrivalAirport->code }}</div>
                        </td>
                        <td>
                            <div class="label">Boarding Time</div>
                            <div class="value">{{ $flight->departure_time->format('d M Y H:i') }}</div>
                        </td>
                    </tr>
                </table>

                <div class="bottom">
                    <div class="qr-wrap">
                        {!! $qrMarkup !!}
                    </div>
                    <div class="meta">
                        <div class="meta-card">
                            <div class="label">Flight</div>
                            <div class="value">{{ $flight->flight_number }}</div>
                        </div>
                        <div class="meta-card">
                            <div class="label">Boarding Status</div>
                            <div class="value">{{ ucfirst(str_replace('_', ' ', (string) $detail->boarding_status)) }}</div>
                        </div>
                    </div>
                </div>

                <div class="note">Present this e-ticket and a valid ID during check-in.</div>
            </div>
        </div>
    </div>
</body>
</html>
