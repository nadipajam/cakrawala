<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $detail->ticket_number }} Boarding Pass</title>
    <style>
        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #1e293b;
            background: #fffaf5;
        }

        .page {
            padding: 28px;
        }

        .pass {
            overflow: hidden;
            border: 1px solid #f1d7c0;
            border-radius: 24px;
            background: #ffffff;
        }

        .hero {
            padding: 22px 24px;
            background:
                radial-gradient(circle at top right, rgba(255,255,255,.18) 0%, transparent 24%),
                linear-gradient(135deg, #9a3412 0%, #f08a3a 100%);
            color: #fff7ed;
        }

        .eyebrow {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .28em;
            text-transform: uppercase;
            color: rgba(255, 237, 213, 0.9);
        }

        .code {
            margin-top: 10px;
            font-size: 30px;
            font-weight: 700;
            word-break: break-all;
        }

        .sub {
            margin-top: 4px;
            font-size: 13px;
            color: rgba(255, 247, 237, 0.92);
        }

        .body {
            padding: 20px 24px 24px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px;
            margin: 0 -10px;
        }

        td {
            width: 50%;
            padding: 12px 14px;
            border: 1px solid #f1ddca;
            border-radius: 16px;
            background: #fffaf5;
            vertical-align: top;
        }

        .label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: #9a5a2d;
        }

        .value {
            margin-top: 4px;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.2;
            word-break: break-word;
        }

        .bottom {
            margin-top: 14px;
            overflow: hidden;
        }

        .qr {
            float: left;
            width: 196px;
            text-align: center;
            padding: 14px;
            border: 1px dashed #efc8a7;
            border-radius: 18px;
            background: #ffffff;
        }

        .qr svg {
            width: 145px;
            height: 145px;
        }

        .meta {
            margin-left: 222px;
        }

        .meta-card {
            margin-bottom: 10px;
            padding: 12px 14px;
            border: 1px solid #f1ddca;
            border-radius: 16px;
            background: #fffaf5;
        }

        .note {
            margin-top: 12px;
            font-size: 11px;
            color: #9a5a2d;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="pass">
            <div class="hero">
                <div class="eyebrow">Boarding Pass</div>
                <div class="code">{{ $detail->checkin_reference ?: '-' }}</div>
                <div class="sub">{{ $flight->airline->name }} | {{ $booking->booking_code }}</div>
            </div>

            <div class="body">
                <table>
                    <tr>
                        <td>
                            <div class="label">Passenger</div>
                            <div class="value">{{ $detail->passenger?->full_name }}</div>
                        </td>
                        <td>
                            <div class="label">Flight</div>
                            <div class="value">{{ $flight->flight_number }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Route</div>
                            <div class="value">{{ $flight->departureAirport->code }} -> {{ $flight->arrivalAirport->code }}</div>
                        </td>
                        <td>
                            <div class="label">Departure</div>
                            <div class="value">{{ $flight->departure_time->format('d M Y H:i') }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Seat</div>
                            <div class="value">{{ $detail->seat?->seat_number }} / {{ $cabinLabel }}</div>
                        </td>
                        <td>
                            <div class="label">Group / Gate</div>
                            <div class="value">{{ $detail->boarding_group ?: '-' }} / {{ $detail->gate_number ?: '-' }}</div>
                        </td>
                    </tr>
                </table>

                <div class="bottom">
                    <div class="qr">
                        {!! $qrMarkup !!}
                    </div>
                    <div class="meta">
                        <div class="meta-card">
                            <div class="label">Booking Code</div>
                            <div class="value">{{ $booking->booking_code }}</div>
                        </div>
                        <div class="meta-card">
                            <div class="label">Boarding Status</div>
                            <div class="value">{{ ucfirst(str_replace('_', ' ', (string) $detail->boarding_status)) }}</div>
                        </div>
                    </div>
                </div>

                <div class="note">Tunjukkan boarding pass ini saat boarding dan siapkan identitas resmi.</div>
            </div>
        </div>
    </div>
</body>
</html>
