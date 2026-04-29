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
            background: #fff7f1;
        }

        .page {
            padding: 30px;
        }

        .pass {
            border: 1px solid #f2cfb3;
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
        }

        .header {
            padding: 20px 24px;
            background: linear-gradient(130deg, #f08a3a 0%, #c85716 100%);
            color: #fff;
        }

        .title {
            font-size: 12px;
            letter-spacing: .25em;
            text-transform: uppercase;
            opacity: .9;
        }

        .code {
            margin-top: 8px;
            font-size: 30px;
            font-weight: 700;
            word-break: break-all;
        }

        .sub {
            margin-top: 4px;
            font-size: 13px;
            opacity: .92;
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
            border: 1px solid #f4dcc6;
            border-radius: 12px;
            padding: 10px 12px;
            background: #fffaf5;
            vertical-align: top;
        }

        .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: #9a5a2d;
        }

        .value {
            margin-top: 4px;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.2;
            word-break: break-word;
        }

        .qr {
            margin-top: 14px;
            border: 1px dashed #efc8a7;
            border-radius: 14px;
            text-align: center;
            padding: 16px;
            background: #fff;
        }

        .qr svg {
            width: 150px;
            height: 150px;
        }

        .note {
            margin-top: 10px;
            font-size: 11px;
            color: #9a5a2d;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="pass">
            <div class="header">
                <div class="title">Boarding Pass</div>
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

                <div class="qr">
                    {!! $qrMarkup !!}
                </div>
                <div class="note">Tunjukkan boarding pass ini saat boarding dan siapkan identitas resmi.</div>
            </div>
        </div>
    </div>
</body>
</html>
