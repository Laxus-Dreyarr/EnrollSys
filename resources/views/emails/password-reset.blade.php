<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset Code</title>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="logo-container">
                <img src="cid:logo_cid" alt="EnrollSys Logo" class="logo">
            </div>
        </div>
                        
        <div class="email-body">
            <p>Dear user,</p>
                            
            <p>You recently requested to reset your password for your EnrollSys account. Use the verification code below to complete the process.</p>
                            
            <div class="code-container">
                <h3 style="margin-top: 0; color: #2c3e50;">Your Password Reset Code</h3>
                <div class="reset-code">{{ $otp }}</div>
            </div>
                            
            <div class="warning">
                <strong>Security Note:</strong> If you didn\'t request this password reset, please ignore this email or contact support if you have concerns about your account\'s security.
            </div>
                            
            <p>For assistance, please contact the EnrollSys support team at <a href="mailto:support@evsu.ormoc.ph">support@evsu.ormoc.ph</a>.</p>
                            
            <div class="signature">
                <p>Best regards,<br>
                <strong>Team CyberNexus (EnrollSys)</strong><br>
                EVSU Ormoc Campus</p>
            </div>
        </div>
                        
        <div class="email-footer">
            <p>© '.date('Y').' EnrollSys - EVSU Ormoc Campus. All rights reserved.</p>
            <p>This is an automated message, please do not reply directly to this email.</p>
            <p><a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
        </div>
    </div>
</body>
</html>