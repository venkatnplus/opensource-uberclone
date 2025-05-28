<!DOCTYPE html>
<html>
    <head>
        <title>Saas Taxi</title>
    </head>
    <body>
        <div style="font-family: Helvetica,Arial,sans-serif;min-width:800px;overflow:auto;line-height:2">
            <div style="margin:50px auto;width:70%;padding:20px 0">
                <div style="border-bottom:1px solid #eee;padding:20px 0">
                <img src="{{ $appLogo }}">
                </div>
                <p style="font-size:1.1em">Hi,</p>
                <p>Thank you for choosing SAAS TAXI. Use the following OTP to complete your Signup/Password Reset procedures. OTP is valid for 5 minutes</p>
                <h2 style=" font-family: Georgia, serif; color: #000;margin: 0 auto;width: max-content;padding: 0 10px;border-radius: 4px;">{{ $details }}</h2>
                <label for="" >Click here : <a href="{{ route('email.otp',$user->slug) }}">{{ route('email.otp',$user->slug) }}</a></label>
                <p> Please do not share this One Time Password with anyone.
                    For any problem please contact us at 24*7 Hrs. Customer Support at 0422-4722999 (Language: Tamil and English).. or mail us at <a href="mailto:{{ $mailTo }}" style="color:#000" rel="noreferrer" target="_blank">E-mail: {{ $mailTo }}</a> We solicit your continued patronage to our services.</p>
                <p style="font-size:0.9em;">Regards,</p> 
                <hr style="border:none;border-top:1px solid #eee" />
                <div style="float:right;padding:8px 0;color:#aaa;font-size:0.8em;line-height:1;font-weight:300">
                
                </div>
            </div>
        </div>
    </body>
</html>

