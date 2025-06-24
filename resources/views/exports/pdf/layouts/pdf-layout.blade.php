<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Questionnaire Results' }}</title>
    <style>
        @page {
            margin: 0.5cm 1cm;
        }
        * {
            box-sizing: border-box;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }
        body {
            margin: 0;
            padding: 0;
            color: #333;
            font-size: 12px;
            line-height: 1.5;
        }
        .page-break {
            page-break-after: always;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 10px;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 10px;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo-container {
            max-width: 200px;
        }
        .logo {
            max-width: 100%;
            height: auto;
        }
        .title-container {
            text-align: right;
        }
        .report-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #2563eb;
        }
        .report-subtitle {
            font-size: 14px;
            color: #64748b;
        }
        .report-date {
            font-size: 12px;
            color: #64748b;
        }
        .footer {
            font-size: 10px;
            color: #64748b;
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e0e0e0;
        }
        section {
            margin-bottom: 20px;
        }
        h1, h2, h3, h4, h5, h6 {
            margin-top: 0;
            color: #1e293b;
        }
        h1 {
            font-size: 20px;
        }
        h2 {
            font-size: 18px;
        }
        h3 {
            font-size: 16px;
        }
        h4 {
            font-size: 14px;
        }
        .card {
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            page-break-inside: avoid;
        }
        .card-header {
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .card-title {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            color: #1e293b;
        }
        .card-meta {
            font-size: 12px;
            color: #64748b;
            margin-top: 5px;
        }
        .stats-summary {
            display: flex;
            flex-wrap: wrap;
            margin: 15px -10px 0;
        }
        .stat-item {
            flex: 1 0 21%;
            padding: 10px;
            background-color: #f8fafc;
            border-radius: 4px;
            margin: 0 10px 10px;
            text-align: center;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 12px;
            color: #64748b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background-color: #f8fafc;
            font-weight: bold;
            color: #1e293b;
        }
        .section-title {
            border-left: 3px solid #2563eb;
            padding-left: 10px;
            margin-bottom: 15px;
        }
        .chart-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto 15px;
            text-align: center;
        }
        .chart-image {
            max-width: 100%;
            height: auto;
        }
        .text-center {
            text-align: center;
        }
        .question-type {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            color: white;
            background-color: #64748b;
            margin-left: 5px;
        }
        .response-item {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .response-item:last-child {
            border-bottom: none;
        }
        .text-muted {
            color: #64748b;
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-primary {
            background-color: #e0f2fe;
            color: #0369a1;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #15803d;
        }
        /* Question type specific colors */
        .question-multichoice {
            background-color: #6366f1;
        }
        .question-text {
            background-color: #0ea5e9;
        }
        .question-numeric {
            background-color: #10b981;
        }
        .question-date {
            background-color: #f59e0b;
        }
        .question-file {
            background-color: #8b5cf6;
        }
        .question-matrix {
            background-color: #ec4899;
        }
        .question-likert {
            background-color: #06b6d4;
        }
        .question-ranking {
            background-color: #ef4444;
        }
        .question-yesno {
            background-color: #14b8a6;
        }
        /* Columns helpers */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        .col {
            flex: 1 0 0%;
            padding: 0 10px;
        }
        .col-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 10px;
        }
        .col-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
            padding: 0 10px;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        
        /* Bar chart styles - Optimized for DomPDF */
        .bar-chart-cell {
            width: 100%;
            background-color: #f8fafc;
            border-radius: 4px;
            height: 20px;
            border: 1px solid #e5e7eb;
        }
        .bar {
            height: 20px;
            background-color: #2563eb;
            display: inline-block;
            min-width: 4px;
            max-width: 100%;
            border-radius: 2px;
        }
        
        /* Additional styles to improve compatibility with DomPDF */
        .ranking-list, .selected-options-list {
            margin: 0;
            padding-left: 20px;
        }
        
        .statistics-summary {
            margin-bottom: 15px;
        }
        
        .stat-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .stat-row .stat-item {
            display: table-cell;
            width: auto;
            text-align: center;
            padding: 8px;
            background-color: #f8fafc;
            border: 1px solid #e5e7eb;
        }
        
        /* Ensure tables are not cut off */
        .responses-table {
            page-break-inside: avoid;
            margin-top: 20px;
        }
        
        /* Ensure proper spacing in the document */
        p {
            margin: 5px 0;
        }
        
        /* Make sure links are visible */
        a {
            color: #2563eb;
            text-decoration: underline;
        }
    </style>
    @yield('extra-styles')
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <div class="logo-container">
                    @if(isset($logoUrl))
                        <img src="{{ $logoUrl }}" alt="Logo" class="logo">
                    @else
                        <h2>TraceStudy UPNVJ</h2>
                    @endif
                </div>
                <div class="title-container">
                    <div class="report-title">{{ $title ?? 'Questionnaire Results' }}</div>
                    <div class="report-subtitle">{{ $subtitle ?? 'Detailed Report' }}</div>
                    <div class="report-date">Generated on: {{ now()->format('F d, Y \a\t h:i A') }}</div>
                </div>
            </div>
        </div>

        <main>
            @yield('content')
        </main>

        <div class="footer">
            <p>
                TraceStudy UPNVJ &copy; {{ date('Y') }} | Report Generated: {{ now()->format('Y-m-d H:i:s') }}
                @if(isset($user))
                | Generated by: {{ $user->name }}
                @endif
            </p>
        </div>
    </div>

    @yield('scripts')
</body>
</html> 