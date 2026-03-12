<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Academic Transcript &mdash; {{ $student->user->first_name }} {{ $student->user->last_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.5;
            padding: 30px 40px;
        }

        /* ── Header ────────────────────────────────── */
        .header {
            text-align: center;
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 20px;
            color: #1e3a5f;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header .subtitle {
            font-size: 12px;
            color: #555;
            margin-top: 4px;
        }
        .header .generated {
            font-size: 10px;
            color: #888;
            margin-top: 4px;
        }

        /* ── Student info ───────────────────────────── */
        .student-info {
            background: #f4f6fa;
            border: 1px solid #d0d8e8;
            border-radius: 4px;
            padding: 12px 16px;
            margin-bottom: 24px;
        }
        .student-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .student-info td {
            padding: 3px 8px;
            width: 25%;
        }
        .student-info .label {
            font-weight: bold;
            color: #1e3a5f;
        }

        /* ── Term block ─────────────────────────────── */
        .term-block {
            margin-bottom: 22px;
            page-break-inside: avoid;
        }
        .term-header {
            background: #1e3a5f;
            color: #fff;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 3px 3px 0 0;
        }

        /* ── Courses table ─────────────────────────── */
        .courses-table {
            width: 100%;
            border-collapse: collapse;
        }
        .courses-table th {
            background: #e8edf5;
            padding: 5px 8px;
            text-align: left;
            font-size: 10px;
            color: #1e3a5f;
            border-bottom: 1px solid #c5cedf;
        }
        .courses-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #eaeef4;
            font-size: 10px;
        }
        .courses-table tr:last-child td { border-bottom: none; }
        .courses-table .text-center { text-align: center; }
        .courses-table .text-right  { text-align: right; }

        /* ── Term summary row ───────────────────────── */
        .term-summary {
            background: #e8edf5;
            font-weight: bold;
        }

        /* ── Cumulative footer ──────────────────────── */
        .cumulative {
            margin-top: 20px;
            padding: 12px 16px;
            border: 2px solid #1e3a5f;
            border-radius: 4px;
            text-align: right;
        }
        .cumulative .label {
            font-weight: bold;
            color: #1e3a5f;
            font-size: 12px;
        }
        .cumulative .value {
            font-size: 14px;
            color: #1e3a5f;
            font-weight: bold;
        }

        /* ── No-data ────────────────────────────────── */
        .no-data {
            color: #888;
            text-align: center;
            padding: 30px;
            font-style: italic;
        }

        /* ── Footer ─────────────────────────────────── */
        .footer {
            margin-top: 30px;
            border-top: 1px solid #ccc;
            padding-top: 8px;
            font-size: 9px;
            color: #aaa;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- ── Header ──────────────────────────────────────────── --}}
    <div class="header">
        @php $university = \App\Models\University::first(); @endphp
        <h1>{{ $university?->name ?? 'UniOne University' }}</h1>
        <div class="subtitle">Official Academic Transcript</div>
        <div class="generated">Generated: {{ now()->format('d M Y, H:i') }}</div>
    </div>

    {{-- ── Student information ────────────────────────────── --}}
    <div class="student-info">
        <table>
            <tr>
                <td class="label">Student Name</td>
                <td>{{ $student->user->first_name }} {{ $student->user->last_name }}</td>
                <td class="label">Student Number</td>
                <td>{{ $student->student_number }}</td>
            </tr>
            <tr>
                <td class="label">Faculty</td>
                <td>{{ $student->faculty?->name ?? '—' }}</td>
                <td class="label">Department</td>
                <td>{{ $student->department?->name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Cumulative GPA</td>
                <td>{{ $student->gpa !== null ? number_format($student->gpa, 2) : '—' }}</td>
                <td class="label">Academic Standing</td>
                <td>{{ ucfirst($student->academic_standing ?? '—') }}</td>
            </tr>
        </table>
    </div>

    {{-- ── Term-by-term records ────────────────────────────── --}}
    @if($terms->isEmpty())
        <p class="no-data">No completed course records found.</p>
    @else
        @foreach($terms as $term)
            <div class="term-block">
                <div class="term-header">
                    {{ $term['academic_term']->name }}
                    &nbsp;&bull;&nbsp;
                    Academic Year {{ $term['academic_term']->academic_year }}
                    &nbsp;&bull;&nbsp;
                    Semester {{ $term['academic_term']->semester }}
                </div>
                <table class="courses-table">
                    <thead>
                        <tr>
                            <th style="width:10%">Code</th>
                            <th style="width:40%">Course Name</th>
                            <th class="text-center" style="width:12%">Credits</th>
                            <th class="text-center" style="width:10%">Total</th>
                            <th class="text-center" style="width:10%">Grade</th>
                            <th class="text-center" style="width:10%">Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($term['courses'] as $entry)
                            <tr>
                                <td>{{ $entry['course']->code }}</td>
                                <td>{{ $entry['course']->name }}</td>
                                <td class="text-center">{{ $entry['course']->credit_hours }}</td>
                                <td class="text-center">{{ $entry['grade']->total !== null ? number_format($entry['grade']->total, 1) : '—' }}</td>
                                <td class="text-center">{{ $entry['grade']->letter_grade ?? '—' }}</td>
                                <td class="text-center">{{ $entry['grade']->grade_points !== null ? number_format($entry['grade']->grade_points, 2) : '—' }}</td>
                            </tr>
                        @endforeach
                        {{-- Term summary row --}}
                        <tr class="term-summary">
                            <td colspan="2" class="text-right">Term Totals</td>
                            <td class="text-center">{{ $term['term_credits'] }}</td>
                            <td colspan="2" class="text-right">Term GPA</td>
                            <td class="text-center">{{ $term['term_gpa'] !== null ? number_format($term['term_gpa'], 2) : '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

    {{-- ── Cumulative GPA summary ──────────────────────────── --}}
    @if($terms->isNotEmpty())
        <div class="cumulative">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td>
                        <span class="label">Total Credit Hours Earned: </span>
                        <span class="value">{{ $terms->sum('term_credits') }}</span>
                    </td>
                    <td style="text-align:right;">
                        <span class="label">Cumulative GPA: </span>
                        <span class="value">{{ $student->gpa !== null ? number_format($student->gpa, 2) : '—' }}</span>
                        <span style="font-size:10px; color:#555;"> / 4.00</span>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    {{-- ── Footer ──────────────────────────────────────────── --}}
    <div class="footer">
        This is an official academic transcript. Unauthorized modification of this document is prohibited.
        &nbsp;&bull;&nbsp;
        {{ $university?->name ?? 'UniOne University' }}
        &nbsp;&bull;&nbsp;
        Printed: {{ now()->format('d M Y') }}
    </div>

</body>
</html>
