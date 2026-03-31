<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annealing Check Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #3b82f6;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background-color: #f9fafb;
        }
        .details {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
        }
        .details td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .details td:first-child {
            font-weight: bold;
            width: 150px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Material Inspection Recording System</h1>
            <p>Annealing Check Notification</p>
        </div>
        
        <div class="content">
            <p>Hello {{ $user->name }},</p>
            
            @if($type === 'new_submission')
                <p>A new annealing check has been submitted and requires your attention.</p>
            @else
                <p>An annealing check has been updated and requires your review.</p>
            @endif
            
            <div class="details">
                <h3>Annealing Check Details:</h3>
                <table>
                    <tr>
                        <td>Item Code:</td>
                        <td>{{ $annealingCheck->item_code }}</td>
                    </tr>
                    <tr>
                        <td>Supplier Lot #:</td>
                        <td>{{ $annealingCheck->supplier_lot_number }}</td>
                    </tr>
                    <tr>
                        <td>Quantity:</td>
                        <td>{{ $annealingCheck->quantity }}</td>
                    </tr>
                    <tr>
                        <td>Annealing Date:</td>
                        <td>{{ $annealingCheck->annealing_date->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <td>Machine #:</td>
                        <td>{{ $annealingCheck->machine_number }}</td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td>{{ ucfirst($annealingCheck->status) }}</td>
                    </tr>
                </table>
            </div>
            
            <p>
                <a href="{{ route('annealing-checks.show', $annealingCheck->id) }}" class="button">
                    View Details
                </a>
            </p>
            
            <p>Please log in to the system to review this submission.</p>
        </div>
        
        <div class="footer">
            <p>This is an automated message from the Material Inspection Recording System.</p>
            <p>If you received this email in error, please contact the system administrator.</p>
        </div>
    </div>
</body>
</html>
