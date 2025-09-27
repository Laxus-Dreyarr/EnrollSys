<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Mail\PasswordResetOtp;
use App\Models\Admin;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;


class AdminController extends Controller
{

    public function login(Request $request)
    {   
        $credentials = $request->only('email', 'password');
        $user = Admin::where('email', $credentials['email'])->first();

        if(!$user) {
            $x = 1;
            return $x;
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            $x = 2;
            return $x;  
        }

        if(!$user->is_active) {
            $x = 0;
            return $x;  
        }

        // if(Auth::guard('admin')->login($user)){
        //     $request->session()->put('admin', $user);
        //     $request->session()->regenerate();
        //     $x = 3;
        //     return $x;  

        // }

        if (Auth::guard('admin')->attempt([
            'email' => $request->email,
            'password' => $request->password
            ])) {
            $request->session()->regenerate();
            return response()->json(3);
        }


    }

    public function dashboard(){

        if (!Auth::guard('admin')->check()) {
            return redirect('/welcome_admin')->with('error', 'Please login first.');
        }

        $user = Auth::guard('admin')->user();
        return view('dashboard', compact('user'));
        
    }


    public function forgotPassword(Request $request){
        $credentials = $request->only('email', 'password', 'confirmPassword');
        $user = Admin::where('email', $credentials['email'])->first();
        if(!$user) {
            $x = 1;
            return $x;
        }

        if($credentials['password'] !== $credentials['confirmPassword']){
            $x = 2;
            return $x;
        }
        $thePassword = $user->password = Hash::make($credentials['password']);
        // $thePassword = Hash::make($credentials['password']);
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store in cache with email
        Cache::put('password_reset_' . $request->email, [
            'otp' => $otp,
            'password' => Hash::make($request->password),
            'email' => $request->email
        ], now()->addMinutes(10));

        // Store email in Laravel session for the reset page
        session(['password_reset_email' => $request->email]);

        try {
            Mail::to($request->email)->send(new PasswordResetOtp($otp));
            $x = 5;
            return $x;
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send email'], 500);
        }

    }


    public function resetPassword(Request $request){
        $credentials = $request->only('email', 'password', 'otp');
        $user = Admin::where('email', $credentials['email'])->first();
        if(!$user) {
            $x = 0;
            return $x;
        }
        
        $cachedData = Cache::get('password_reset_' . $request->email);
        
        if (!$cachedData || $cachedData['otp'] !== $request->otp) {
            $x = 0;
            return $x;
        }

        // Update password
        // $user->update(['password' => Hash::make($credentials['password'])]);
        $user->update([
            'password' => $credentials['password']
        ]);
        
        // Clear OTP from cache
        Cache::forget('password_reset_' . $request->email);

        $x = 1;
        return $x;

    }

    


    
}
