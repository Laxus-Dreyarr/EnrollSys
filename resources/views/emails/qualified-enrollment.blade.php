<!DOCTYPE html>
<html>
<head>
    <title>Qualified for Enrollment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #003366;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        .highlight {
            background-color: #e8f4fd;
            padding: 10px;
            border-left: 4px solid #003366;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>EVSU Ormoc Campus</h1>
        <h2>Enrollment Qualification Notice</h2>
    </div>
    
    <div class="content">
        <p>Dear {{ $name }},</p>
        
        <p>We are pleased to inform you that you have been <strong>qualified for enrollment</strong> at Eastern Visayas State University (EVSU) Ormoc Campus!</p>
        
        <div class="highlight">
            <p><strong>Application Number:</strong> {{ $applicationNumber }}</p>
            <p><strong>Program:</strong> {{ $program }}</p>
        </div>
        
        <h3>Next Steps:</h3>
        <ol>
            <li>Log in to the EVSU Enrollment System using your registered email</li>
            <li>Complete your student profile information</li>
            <li>Upload required documents (Form 138, Good Moral Certificate, PSA/NSO Birth Certificate, ID Picture)</li>
            <li>Wait for verification and enrollment approval</li>
            <li>Proceed with payment and enrollment</li>
        </ol>
        
        <p><strong>Enrollment Period:</strong> Ongoing for Academic Year 2025-2026</p>
        <p><strong>Campus:</strong> EVSU Ormoc Campus</p>
        
        <p>If you have any questions, please contact the Registrar's Office at:</p>
        <ul>
            <li>Email: registrar.ormoc@evsu.edu.ph</li>
            <li>Phone: (053) 555-1234</li>
            <li>Visit: EVSU Ormoc Campus, Ormoc City, Leyte</li>
        </ul>
        
        <p>Congratulations and welcome to EVSU Ormoc Campus!</p>
        
        <p>Sincerely,<br>
        <strong>EVSU Ormoc Enrollment Committee</strong></p>
    </div>
    
    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>Eastern Visayas State University - Ormoc Campus</p>
        <p>Ormoc City, Leyte, Philippines</p>
    </div>
</body>
</html>