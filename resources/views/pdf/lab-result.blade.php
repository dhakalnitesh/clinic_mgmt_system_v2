<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lab Report - {{ $labOrder->order_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #374151; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; margin: 0; color: #111827; }
        .header .sub { color: #6b7280; font-size: 11px; }
        .header .right { text-align: right; float: right; }
        .header .right .num { font-size: 16px; font-weight: bold; }
        .clearfix { clear: both; }
        .info-box { margin-bottom: 16px; }
        .info-box .col { width: 48%; float: left; }
        .info-box .col + .col { float: right; }
        .info-box h3 { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 4px; }
        .info-box .box { background: #f3f4f6; padding: 8px 12px; border-radius: 6px; }
        .info-box .box .name { font-size: 14px; font-weight: bold; }
        .info-box .box .text { color: #4b5563; }
        .test-section { margin-bottom: 16px; }
        .test-header { background: #eef2ff; padding: 8px 12px; border-radius: 6px 6px 0 0; }
        .test-header h3 { margin: 0; font-size: 13px; color: #3730a3; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th { background: #f9fafb; text-align: left; padding: 6px 8px; font-size: 9px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #d1d5db; }
        td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        .abnormal { background: #fef2f2; }
        .abnormal td.result { color: #dc2626; font-weight: bold; }
        .result { font-weight: bold; }
        .footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid #d1d5db; text-align: center; font-size: 9px; color: #9ca3af; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    <div class="header">
        <div class="right">
            <div class="num">{{ $labOrder->order_number }}</div>
            <div class="sub">{{ $labOrder->created_at->format('F d, Y') }}</div>
        </div>
        <h1>Laboratory Report</h1>
        <div class="sub">Clinical Laboratory Test Results</div>
        <div class="clearfix"></div>
    </div>

    <div class="info-box">
        <div class="col">
            <h3>Patient Information</h3>
            <div class="box">
                <div class="name">{{ $labOrder->patient?->name ?? '—' }}</div>
                <div class="text">UHID: {{ $labOrder->patient?->uhid ?? '—' }}</div>
                @if($labOrder->patient?->gender)
                    <div class="text">Gender: {{ $labOrder->patient->gender }}</div>
                @endif
                @if($labOrder->patient?->age)
                    <div class="text">Age: {{ $labOrder->patient->age }}</div>
                @endif
            </div>
        </div>
        <div class="col">
            <h3>Order Information</h3>
            <div class="box">
                <div class="text">Doctor: <strong>{{ $labOrder->doctor?->name ?? '—' }}</strong></div>
                <div class="text">Date: {{ $labOrder->created_at->format('Y-m-d') }} (BS: {{ $labOrder->created_at_bs }})</div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>

    @foreach ($labOrder->items as $item)
        <div class="test-section">
            <div class="test-header">
                <h3>{{ $item->labTest?->name ?? 'Unknown Test' }}
                    @if($item->labTest?->code)
                        <span style="font-weight:normal;font-size:10px;color:#6b7280;"> ({{ $item->labTest->code }})</span>
                    @endif
                </h3>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Result</th>
                        <th>Unit</th>
                        <th>Ref. Range</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($item->labTest?->parameters ?? [] as $param)
                        @php
                            $result = $item->results->firstWhere('lab_test_parameter_id', $param->id);
                            $val = $result?->result_value;
                            $abnormal = false;
                            if ($val && $param->reference_range && str_contains($param->reference_range, '-')) {
                                $parts = explode('-', $param->reference_range);
                                $num = is_numeric($val) ? (float) $val : null;
                                if ($num !== null && count($parts) === 2) {
                                    $lo = (float) trim($parts[0]);
                                    $hi = (float) trim($parts[1]);
                                    $abnormal = $num < $lo || $num > $hi;
                                }
                            }
                        @endphp
                        <tr class="{{ $abnormal ? 'abnormal' : '' }}">
                            <td>{{ $param->name }}</td>
                            <td class="result">{{ $val ?? '—' }}</td>
                            <td>{{ $param->unit ?? '—' }}</td>
                            <td>{{ $param->reference_range ?? '—' }}</td>
                            <td>{{ $result?->remarks ?? '' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="color:#9ca3af;font-style:italic;">No parameters defined for this test.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="footer">
        <p>Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
        <p>This is a computer-generated report.</p>
    </div>

</body>
</html>
