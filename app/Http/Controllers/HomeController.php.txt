<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Human;
use Illuminate\Auth\SessionGuard;

class HomeController extends Controller
{
    public function index(){
        return view("index");
    }

    public function register(){
        return view("register");
    }

    public function login(){
        return view("/login");
    }

    public function form(Request $request){
        
        $email = $request->input('email');
        if(empty($request->input('name')) || empty($request->input('email')) || empty($request->input('password')) || empty($request->input('pwdRepeat'))){
            $x = 0;
            return $x;
        }elseif(!filter_var($request->input('email'), FILTER_VALIDATE_EMAIL)){
            $x = 1;
            return $x;
        }elseif($request->input('password')!==$request->input('pwdRepeat')){
            $x = 2;
            return $x;
        }else{
            $user_data = Human::where('email', $email)->get();
            $count = $user_data->count();

            if($count == 0){
                $save = new Human();
                $save->name = $request->input('name');
                $save->email = $email;
                $save->password = Hash::make($request->input('password'));
                $save->save();
                $x = 500;
                return $x;
            }else{
                $x = 3;
                return $x;
            }
        }


        // $data = $request->validate([
        //     'name' => 'required',
        //     'email' => 'required',
        //     'password' => 'required',
        // ]);

        // $register = new Human();
        // $register->name = $request->input('name');
        // $register->email = $request->input('email');
        // $register->password = $request->input('pwd');
        // $register->save();

        // $save = Human::create($data);

    }


    // public function log(Request $request){
    //     $email = $request->input('email');
    //     $pwd = $request->input('pwd');
        
    //     if(empty($request->input('email')) || empty($request->input('pwd'))){
    //         $x = 0;
    //         return $x;
    //     }else{
    //         $email = $request->input('email');
    //         $pwd = $request->input('pwd');
    //         $y = Human::where('email', $email)->get();
    //         if($y->count()==0){
    //             $y = null;
    //             $x = 1;
    //             return $x;
    //         }else{
    //             if(!Hash::check($pwd, $y[0]['password'])){
    //                 $x = 2;
    //                 return $x;
    //             }else{
    //                 // $token = $y[0]['email']->createToken('auth_token')->plainTextToken;
    //                 // // return response()->json(['token' => $token]);
    //                 // return $token;
    //                 // auth()->attempt(array('email' => $email, 'password' => $pwd));
    //                 // $x = 500;
    //                 // return $x;
                    
    //                 $a = auth()->attempt(array('email '=> $y[0]['email']));
    //                 return $a;
    //                 // return response()->json([[500]]);
    //             }
    //         }
    //     }
    // }

    // public function log(Request $request)
    // {
        
    //     $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required',
    //     ]);
    //     $email = $request->input('email');
    //     $y = Human::where('email', $email)->first();
    //     // Hash::check($pwd, $y[0]['password'])
    //     Auth::login($y);
    //     // auth()->attempt(array('name' => $y));
        
    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Login successful',
    //         'redirect' => '/dashboard',
    //     ]);
    //     // $credentials = $request->only('email', 'password');

    //     // if (Auth::attempt($credentials)) {
    //     //     // Authentication passed
    //     //     return response()->json([
    //     //         'status' => 'success',
    //     //         'message' => 'Login successful',
    //     //         'redirect' => '/dashboard',
    //     //     ]);
    //     // } else {
    //     //     return response()->json([
    //     //         'status' => 'error',
    //     //         'message' => 'Invalid credentials',
    //     //     ]);
    //     // }
    // }

//     public function log(Request $request)
// {
//     $request->validate([
//         'email' => 'required',
//         'password' => 'required',
//     ]);

//     $credentials = $request->only('email', 'password');

//     if (Auth::attempt($credentials)) {
//         // Regenerate the session to prevent session fixation attacks
//         $request->session()->regenerate();

//         return response()->json([
//             'status' => 'success',
//             'message' => 'Login successful',
//             'redirect' => '/dashboard',
//         ]);
//     } else {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Invalid credentials',
//         ]);
//     }
// }

public function log(Request $request)
{
    $credentials = $request->only('email', 'password');
    $user = Human::where('email', $credentials['email'])->first();

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User not found with this email.',
        ]);
    }

    // Debug password matching
    if (!Hash::check($credentials['password'], $user->password)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Password does not match.',
            'provided_password' => $credentials['password'],
            'hashed_password' => $user->password,
        ]);
    }

    // Authenticate the user
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'redirect' => '/dashboard',
        ]);
    }

    return response()->json([
        'status' => 'error',
        'message' => 'Invalid credentials.',
    ]);
}
public function dashboard()
{
    if (!Auth::check()) {
        return redirect('/login')->with('error', 'You must be logged in to access the dashboard.');
    }

    $user = Auth::user();
    return view('dashboard', compact('user'));
}

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }

}
