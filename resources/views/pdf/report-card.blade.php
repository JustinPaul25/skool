<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Report Card — {{ $student->full_name }} — {{ $schoolYear->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; }
        .muted { color: #555; font-size: 10px; }
        .meta { margin-bottom: 16px; }
        .meta table { width: 100%; border-collapse: collapse; }
        .meta th, .meta td { text-align: left; padding: 4px 8px; }
        .meta th { width: 28%; color: #333; }
        .grades { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 10px; }
        .grades th, .grades td { border: 1px solid #ccc; padding: 6px 4px; text-align: center; }
        .grades th:first-child, .grades td:first-child { text-align: left; }
        .grades thead th { background: #f0f0f0; font-weight: bold; }
        .footer { margin-top: 24px; font-size: 9px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ config('app.name', 'School') }}</div>
        <div class="muted">Report Card</div>
    </div>

    <div class="meta">
        <table>
            <tr>
                <th>Student</th>
                <td>{{ $student->full_name }}</td>
            </tr>
            <tr>
                <th>Student ID</th>
                <td>{{ $student->student_id }}</td>
            </tr>
            <tr>
                <th>School year</th>
                <td>{{ $schoolYear->name }}</td>
            </tr>
            @if($student->branch)
                <tr>
                    <th>Branch</th>
                    <td>{{ $student->branch->name }}</td>
                </tr>
            @endif
            <tr>
                <th>Status</th>
                <td>{{ str($enrollment->status)->headline() }}</td>
            </tr>
        </table>
    </div>

    <table class="grades">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Code</th>
                @foreach($periods as $p)
                    <th>{{ $periodLabels[$p] ?? str($p)->upper() }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['code'] }}</td>
                    @foreach($periods as $p)
                        <td>{{ $row[$p] }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 2 + count($periods) }}" style="text-align: center; padding: 12px;">No grades recorded for this enrollment.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated {{ now()->format('M d, Y g:i A') }}. This document is for reference only.
    </div>
</body>
</html>
