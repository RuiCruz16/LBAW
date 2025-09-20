<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f8f9fa; line-height: 1.6;">
    <div style="background-color: #f8f9fa; padding: 20px 0; text-align: center;">
        <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden;">

            <div style="background-color: #007bff; padding: 20px; text-align: center;">
                <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: bold;">Reset Your Password</h1>
            </div>

            <div style="padding: 30px; text-align: left; color: #333333;">
                <p style="margin: 0 0 20px; font-size: 16px;">
                    Hello <strong>{{ $mailData['name'] }}</strong>,
                </p>
                <p style="margin: 20px 0; font-size: 16px; color: #555555;">
                    We received a request to reset your password. Click the button below to proceed:
                </p>
                <div style="text-align: center; margin: 30px 0;">
                    <a href="{{ url('/password/reset/' . $mailData['token']) }}"
                       style="display: inline-block; padding: 12px 25px; font-size: 16px; color: #ffffff; background-color: #007bff; border-radius: 5px; text-decoration: none; font-weight: bold;">
                        Reset Password
                    </a>
                </div>
                <p style="margin: 0; font-size: 14px; color: #777777;">
                    If you did not request this, please ignore this email. This link will expire in 60 minutes.
                </p>
            </div>

            <div style="background-color: #f1f1f1; text-align: center; padding: 15px; font-size: 12px; color: #777777;">
                <p style="margin: 0;">&copy; {{ date('Y') }} Planora. All rights reserved.</p>
            </div>

        </div>
    </div>
</body>
