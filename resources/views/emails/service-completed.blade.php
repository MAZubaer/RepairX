<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Repair Completed</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a;">
    <h2 style="margin-bottom: 12px;">Repair Completed</h2>

    <p>Hello {{ $customerName ?? 'Customer' }},</p>

    <p>Your <strong>{{ $serviceRecord->phone_model }}</strong> repair request is now <strong>Completed</strong>.</p>
    <p>Shop: <strong>{{ $shopName ?? 'the shop' }}</strong></p>

    <p style="margin-top: 14px;">
        <strong>Identified Problem:</strong><br>
        {{ $serviceRecord->shop_problem }}
    </p>

    <p style="margin-top: 14px;">
        <strong>Repair Cost:</strong>
        ৳{{ number_format((float) $serviceRecord->repair_cost, 2) }}
    </p>

    <p style="margin-top: 16px;">Request ID: <strong>#{{ $serviceRecord->service_id }}</strong></p>

    <p style="margin-top: 24px;">Thank you,<br>RepairiX Team</p>
</body>
</html>
