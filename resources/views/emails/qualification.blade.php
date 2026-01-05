<!DOCTYPE html>
<html>
<head>
    <title>Qualification Notification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #004080; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .footer { padding: 10px; text-align: center; color: #666; }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #004080;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Congratulations!</h1>
        </div>
        
        <div class="content">
            <h2>Dear {{ $student->first_name }} {{ $student->last_name }},</h2>
            
            <p>We are pleased to inform you that you have been <strong>QUALIFIED</strong> 
            for the Freshman Enrollment at Your College Name for Academic Year 2024-2025.</p>
            
            <h3>Your Application Details:</h3>
            <ul>
                <li><strong>Student ID:</strong> {{ $student->student_id }}</li>
                <li><strong>Name:</strong> {{ $student->first_name }} {{ $student->last_name }}</li>
                <li><strong>Email:</strong> {{ $student->email }}</li>
                <li><strong>Program:</strong> {{ $student->course }}</li>
            </ul>
            
            <p><strong>Enrollment Deadline:</strong> {{ $deadline }}</p>
            
            <p>To proceed with your enrollment, please click the button below:</p>
            
            <a href="{{ route('enrollment.form', ['id' => $student->id]) }}" class="button">
                Complete Your Enrollment
            </a>
            
            <p>If you have any questions, please contact the admissions office.</p>
        </div>
        
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} Your College Name. All rights reserved.</p>
        </div>
    </div>
</body>
</html>