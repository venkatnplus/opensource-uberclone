<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Traits\RandomHelper;
use App\Models\User;
use Mail;
use App\Mail\OtpMail;


class PasswordResetLinkController extends Controller
{
    use RandomHelper;
    /**
     * Display the password reset link request view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */


    public function save(Request $request)
    {
        $request->validate([
            'email' => ['required','email'],
        ]);

        $code = $this->UniqueRandomNumbers(4);
        $user= User::where('email',$request->email)->update([
                'otp' => $code ,
                'otp_expires_at' => now()->addMinutes(5)
                ]);
        $user = User::where('email',$request->email)->first();
        if(!$user){
            return back()->with('error', 'Given email id is not found');
        }
        $mailToAddress = env('EMAIL_TO_ADDRESS');
        $appLogo = env('APP_LOGO_URL');
        Mail::to($request->email)->send(new OtpMail($code,$user,$mailToAddress,$appLogo));

        return view('auth.login');

    }
    
    public function otp($slug)
    {
        return view('auth.otp-password',['slug'=>$slug]);
    }

    public function otpcheck(Request $request)
    {
        // dd($request);
       $request->validate([
        'otp' => ['required'],
       ]);

       $find = User::where('slug',$request->slug)->where('otp',$request->otp)->first();
// dd($find);
       if(!is_null($find)){
        return redirect()->route('password.create',$request->slug);
       }
    }

    public function changepassword($slug)
    {

        // dd($slug);
        return view('auth.reset-password',['slug'=>$slug]);
    }

    public function savepassword(Request $request)
    {
       $request->validate([
        'password' => ['required','confirmed'],
        'password_confirmation'=>['required']
       ]);

       if($request->password == $request->password_confirmation)
       {
           User::where('slug',$request->slug)->update([
            'password'=>Hash::make($request->password),
           ]);
           return redirect()->route('login');
        //    return back()->with('success', 'Password changed successfully');
       }
       return back()->with('error', 'Your Password did not match.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                            ->withErrors(['email' => __($status)]);
    }
}
