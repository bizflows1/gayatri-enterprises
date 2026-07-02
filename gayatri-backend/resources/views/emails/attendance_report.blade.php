<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Attendance Summary Report - Gayatri Enterprises</title>
    <style>
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
            line-height: 1.6; 
            color: #1e293b; 
            background-color: #f8fafc; 
            margin: 0; 
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            background-color: #f8fafc;
            padding: 40px 20px;
            box-sizing: border-box;
        }
        .container { 
            max-width: 680px; 
            margin: 0 auto; 
            background-color: #ffffff;
            border-radius: 16px; 
            overflow: hidden;
            box-shadow: 0 4px 25px rgba(15, 23, 42, 0.05);
            border: 1px solid #e2e8f0; 
        }
        .header { 
            text-align: center; 
            padding: 35px 30px; 
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff;
        }
        .logo {
            width: 64px;
            height: 64px;
            border-radius: 14px;
            background-color: #ffffff;
            padding: 5px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            display: inline-block;
            margin-bottom: 15px;
        }
        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .header h1 {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.025em;
            line-height: 1.3;
        }
        .header p {
            font-size: 13px;
            color: #bfdbfe;
            margin: 6px 0 0 0;
            font-weight: 500;
        }
        .content { 
            padding: 35px 30px; 
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 8px;
        }
        .intro-text {
            font-size: 14px;
            color: #475569;
            margin: 0 0 25px 0;
            line-height: 1.6;
        }
        .section-title {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #1e3a8a;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 12px;
            border-left: 3px solid #2563eb;
            padding-left: 10px;
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }
        .attendance-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            text-align: center;
            padding: 12px 10px;
            border-bottom: 2px solid #e2e8f0;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        .attendance-table th.emp-col {
            text-align: left;
        }
        .attendance-table td {
            padding: 14px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }
        .emp-name {
            font-weight: 600;
            color: #0f172a;
            font-size: 13.5px;
            display: block;
        }
        .emp-email {
            font-size: 11px;
            color: #64748b;
            display: block;
            margin-top: 2px;
        }
        .badge {
            display: inline-block;
            font-weight: 700;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 12.5px;
            text-align: center;
            min-width: 24px;
        }
        .badge-present { background-color: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-late { background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .badge-halfday { background-color: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }
        .badge-leave { background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-absent { background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .total-hours {
            font-weight: 700;
            color: #0f172a;
            font-size: 13px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 3px 8px;
            display: inline-block;
        }
        .lunch-box {
            margin-top: 10px;
            background-color: #fafafa;
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            padding: 10px 12px;
        }
        .lunch-title {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #4f46e5;
            font-weight: 700;
            display: block;
            margin-bottom: 4px;
        }
        .lunch-list {
            margin: 0;
            padding-left: 14px;
            font-size: 11px;
            color: #475569;
            line-height: 1.5;
        }
        .lunch-none {
            font-size: 11px;
            color: #94a3b8;
            font-style: italic;
            margin: 0;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0 10px 0;
        }
        .btn { 
            display: inline-block; 
            padding: 12px 28px; 
            background-color: #1e3a8a; 
            color: #ffffff !important; 
            text-decoration: none; 
            border-radius: 10px; 
            font-weight: 600; 
            font-size: 13px;
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.15);
        }
        .info-card {
            background-color: #f8fafc;
            border-radius: 10px;
            padding: 18px;
            border: 1px solid #e2e8f0;
            font-size: 12px;
            color: #64748b;
            text-align: center;
            line-height: 1.5;
        }
        .footer { 
            text-align: center; 
            padding: 30px; 
            font-size: 12px; 
            color: #64748b; 
            border-top: 1px solid #f1f5f9;
        }
        .footer p {
            margin: 5px 0;
        }
        .socials {
            margin-top: 15px;
            font-size: 11px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Brand Header -->
            <div class="header">
                <div class="logo">
                    <img src="https://gayatrient.com/pwa-icon.png" alt="Gayatri Enterprises Logo">
                </div>
                <h1>Gayatri Enterprises</h1>
                <p>Chartered Accountants &bull; Attendance Summary</p>
            </div>
            
            <!-- Email Content -->
            <div class="content">
                <p class="greeting">Hello {{ $admin->name }},</p>
                <p class="intro-text">
                    Please find below the consolidated Attendance and Work Hours Summary Report for your firm's staff members. This report captures records logged between <strong>{{ $startDate }}</strong> and <strong>{{ $endDate }}</strong>.
                </p>
                
                @if(!empty($perfectAttendanceStaff))
                    <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 1px solid #f59e0b; border-radius: 12px; padding: 20px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.15);">
                        <h4 style="margin: 0 0 10px 0; color: #b45309; font-size: 15px; font-weight: 700; display: flex; align-items: center; letter-spacing: -0.01em;">
                            <span style="font-size: 20px; margin-right: 8px;">🏆</span> Perfect Attendance Achievers (100% On-Time)
                        </h4>
                        <p style="margin: 0 0 8px 0; color: #78350f; font-size: 13px; line-height: 1.5;">
                            Kudos to the following team members who maintained perfect attendance with zero late arrivals, half-days, or unapproved absences during this period:
                        </p>
                        <div style="margin-top: 8px;">
                            @foreach ($perfectAttendanceStaff as $name)
                                <span style="background-color: #ffffff; color: #b45309; font-size: 12.5px; font-weight: 700; padding: 5px 12px; border-radius: 20px; border: 1px solid #f59e0b; display: inline-block; margin: 4px 6px 4px 0;">
                                    {{ $name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <h3 class="section-title">Staff Summary Sheet ({{ $monthName }})</h3>
                
                <!-- Attendance Grid -->
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th class="emp-col" style="width: 31%;">Employee & Activity</th>
                            <th style="width: 9%;">Present</th>
                            <th style="width: 9%;">Late</th>
                            <th style="width: 9%;">Half Day</th>
                            <th style="width: 9%;">Leaves</th>
                            <th style="width: 9%;">Absent</th>
                            <th style="width: 12%;">Regular Hours</th>
                            <th style="width: 12%;">Comp-Off Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reportData as $row)
                            <tr>
                                <td>
                                    <span class="emp-name">{{ $row['name'] }}</span>
                                    <span class="emp-email">{{ $row['email'] }}</span>
                                    
                                    <!-- Lunch summaries -->
                                    <div class="lunch-box">
                                        <span class="lunch-title">Lunch Log</span>
                                        @php $hasAnyLunch = false; @endphp
                                        <ul class="lunch-list">
                                            @foreach($row['daily_details'] as $detail)
                                                @if($detail['has_lunch'])
                                                    @php $hasAnyLunch = true; @endphp
                                                    <li><strong>{{ $detail['date'] }}:</strong> {{ $detail['lunch'] }}</li>
                                                @endif
                                            @endforeach
                                        </ul>
                                        @if(!$hasAnyLunch)
                                            <p class="lunch-none">No lunch breaks logged</p>
                                        @endif
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-present">{{ $row['present'] }}</span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-late">{{ $row['late'] }}</span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-halfday">{{ $row['half_day'] }}</span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-leave">{{ $row['leave'] }}</span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-absent">{{ $row['absent'] }}</span>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <span class="total-hours">{{ $row['total_hours'] }}h</span>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <span class="total-hours" style="background-color: #f5f3ff; border-color: #ddd6fe; color: #6d28d9; white-space: nowrap;">{{ $row['overtime_formatted'] }}</span>
                                    @if($row['comp_off_days'] > 0)
                                        <span style="display: block; font-size: 10px; color: #4f46e5; font-weight: 700; margin-top: 4px; white-space: nowrap;">({{ $row['comp_off_days'] }} Comp-off)</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Call to Action -->
                <div class="btn-container">
                    <a href="https://gayatrient.com/portal" class="btn" target="_blank">Open Admin Dashboard</a>
                </div>
                
                <!-- Notice card -->
                <div class="info-card">
                    This administrative summary is generated automatically by the ASA Portal. Full attendance records, leaves, logs, and edits can be managed directly through the Portal's Management Dashboard.
                </div>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <p>&copy; {{ date('Y') }} Gayatri Enterprises. All rights reserved.</p>
                <p>This is an automated administrative notification, please do not reply directly to this mail.</p>
                <div class="socials">
                    Website: <a href="https://gayatrient.com" style="color: #1e3a8a; text-decoration: none; font-weight: 500;">gayatrient.com</a> | Address: Gayatri Enterprises
                </div>
            </div>
        </div>
    </div>
</body>
</html>
