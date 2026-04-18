<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Repair In Progress</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a;">
    <h2 style="margin-bottom: 12px;">Repair Status Update</h2>

    <p>Hello {{ $customerName ?? 'Customer' }},</p>

    <p>Your <strong>{{ $serviceRecord->phone_model }}</strong> repair request is now <strong>In Progress</strong>.</p>
    <p>Shop: <strong>{{ $shopName ?? 'the shop' }}</strong></p>

    <p style="margin-top: 16px;">Request ID: <strong>#{{ $serviceRecord->service_id }}</strong></p>

    <p style="margin-top: 24px;">Thank you,<br>RepairiX Team</p>
</body>
</html>
