<body style="font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; background-color: #f8f9fa;">
    <div style="background-color: #ffffff; padding: 20px; border-radius: 8px; max-width: 600px; margin: 40px auto; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
        <h2 style="color: #007bff; text-align: center; margin-bottom: 20px;">Project Invitation</h2>

        <p style="color: #333333; font-size: 16px; margin-bottom: 20px;">
            Hello,
        </p>
        <p style="color: #333333; font-size: 16px; margin-bottom: 20px;">
            <strong>{{ $mailData['sender_name'] }}</strong> invited you to join the project
            <strong>{{ $mailData['project_name'] }}</strong>.
        </p>
        <p style="color: #555555; font-size: 14px; margin-bottom: 20px;">
            {{ $mailData['message'] }}
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/projects/invitations/accept/' . $mailData['token']) }}"
               style="display: inline-block; padding: 12px 24px; background-color: #28a745; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                Accept Invitation
            </a>
        </div>

        <p style="color: #888888; font-size: 12px; text-align: center; margin-top: 30px;">
            If you did not expect this invitation, feel free to ignore this email.
        </p>

        <div style="text-align: center; margin-top: 20px;">
            <p style="color: #777777; font-size: 14px;">
                Thank you,<br>
                <strong>Planora Team</strong>
            </p>
            <small style="color: #aaaaaa; font-size: 12px;">&copy; {{ date('Y') }} Planora. All rights reserved.</small>
        </div>
    </div>
</body>
