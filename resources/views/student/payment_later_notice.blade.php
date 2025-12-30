<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Notice - {{ $student['id_no'] ?? '' }}</title>
    <style>
        /* Base Styles */
        @page {
            margin: 20mm 15mm;
            size: A4;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            font-size: 11pt;
            line-height: 1.6;
        }
        
        /* Container */
        .container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            box-sizing: border-box;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 15mm;
            border-bottom: 2pt solid #1f2937;
            padding-bottom: 5mm;
        }
        
        .university-name {
            font-size: 16pt;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
            text-transform: uppercase;
        }
        
        .university-address {
            font-size: 10pt;
            color: #666;
            margin: 3pt 0;
        }
        
        .document-title {
            font-size: 18pt;
            font-weight: 700;
            color: #222;
            margin: 8mm 0 5mm 0;
        }
        
        /* Content Section */
        .content-section {
            margin-bottom: 10mm;
        }
        
        .notice-box {
            background: #fff3cd;
            border: 2pt solid #ffc107;
            border-radius: 5pt;
            padding: 8mm;
            margin: 8mm 0;
        }
        
        .notice-title {
            font-size: 14pt;
            font-weight: 700;
            color: #856404;
            margin-bottom: 5mm;
            text-align: center;
        }
        
        .notice-content {
            font-size: 11pt;
            color: #856404;
            line-height: 1.8;
            text-align: justify;
        }
        
        /* Student Information */
        .student-info {
            background: #f8f9fa;
            border: 1pt solid #e0e0e0;
            border-radius: 3pt;
            padding: 6mm;
            margin: 8mm 0;
        }
        
        .info-item {
            margin-bottom: 3mm;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
            display: inline-block;
            width: 50mm;
        }
        
        .info-value {
            color: #222;
        }
        
        /* Payment Details */
        .payment-details {
            background: #e7f3ff;
            border: 1pt solid #b3d9ff;
            border-radius: 3pt;
            padding: 6mm;
            margin: 8mm 0;
        }
        
        .payment-title {
            font-size: 12pt;
            font-weight: 700;
            color: #004085;
            margin-bottom: 4mm;
        }
        
        .payment-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3mm;
            padding-bottom: 2mm;
            border-bottom: 1pt dotted #ccc;
        }
        
        .payment-item:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 12pt;
            padding-top: 2mm;
        }
        
        /* Instructions */
        .instructions {
            margin: 10mm 0;
        }
        
        .instructions-title {
            font-size: 12pt;
            font-weight: 700;
            color: #222;
            margin-bottom: 4mm;
        }
        
        .instructions-list {
            list-style: none;
            padding: 0;
        }
        
        .instructions-list li {
            margin-bottom: 3mm;
            padding-left: 6mm;
            position: relative;
        }
        
        .instructions-list li:before {
            content: "•";
            position: absolute;
            left: 0;
            font-weight: 700;
            color: #1f2937;
        }
        
        /* Footer */
        .footer {
            margin-top: 15mm;
            padding-top: 5mm;
            border-top: 1pt solid #e0e0e0;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }
        
        .signature-section {
            margin-top: 15mm;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            width: 45%;
            text-align: center;
        }
        
        .signature-line {
            border-top: 1pt solid #333;
            margin-top: 15mm;
            padding-top: 2mm;
            font-size: 10pt;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="university-name">University Name</div>
            <div class="university-address">University Address, City, Country</div>
            <div class="document-title">PAYMENT NOTICE</div>
        </div>
        
        <!-- Notice Box -->
        <div class="notice-box">
            <div class="notice-title">IMPORTANT: PAYMENT WILL BE MADE LATER</div>
            <div class="notice-content">
                This document serves as an official notice that the student has chosen to proceed with enrollment 
                without submitting a payment receipt at this time. The student acknowledges that payment of the 
                organizational fee (₱150.00) will be made at a later date as agreed upon with the administration.
            </div>
        </div>
        
        <!-- Student Information -->
        <div class="student-info">
            <div class="info-item">
                <span class="info-label">Student ID:</span>
                <span class="info-value">{{ $student['id_no'] ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ ($student['firstname'] ?? '') . ' ' . ($student['middlename'] ?? '') . ' ' . ($student['lastname'] ?? '') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Year Level:</span>
                <span class="info-value">{{ $student['year_level'] ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Course:</span>
                <span class="info-value">{{ $student['course'] ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Date Generated:</span>
                <span class="info-value">{{ $dateGenerated ?? now()->format('F d, Y h:i A') }}</span>
            </div>
        </div>
        
        <!-- Payment Details -->
        <div class="payment-details">
            <div class="payment-title">Payment Information</div>
            <div class="payment-item">
                <span>Organizational Fee:</span>
                <span>₱150.00</span>
            </div>
            <div class="payment-item">
                <span>Status:</span>
                <span>Payment Pending</span>
            </div>
            <div class="payment-item">
                <span>Total Amount Due:</span>
                <span>₱150.00</span>
            </div>
        </div>
        
        <!-- Instructions -->
        <div class="instructions">
            <div class="instructions-title">Payment Instructions:</div>
            <ul class="instructions-list">
                <li>Payment of ₱150.00 (Organizational Fee) must be completed before the deadline specified by the administration.</li>
                <li>Payment can be made via GCash to the number: <strong>0912-345-6789</strong></li>
                <li>After payment, please upload the payment receipt through the student portal.</li>
                <li>Failure to complete payment within the specified period may result in enrollment cancellation.</li>
                <li>For inquiries, please contact the administration office.</li>
            </ul>
        </div>
        
        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Student Signature</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Date</div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>This is an official document generated by the Enrollment System.</p>
            <p>Please keep this document for your records.</p>
        </div>
    </div>
</body>
</html>

