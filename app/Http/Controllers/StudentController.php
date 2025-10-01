<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    //
    public function register(Request $request) {
        $action = $request->input('action');
        
        switch($action) {
            case 'check_email':
                if(!$request->has('email')) {
                    return response()->json(['success' => false, 'message' => 'Email is required']);
                }
                $email = $request->input('email');
                $exists = \App\Models\User::where('email2', $email)->exists();
                if($exists) {
                    return response()->json(['exists' => true, 'message' => 'Email already exists']);
                }else{
                    return response()->json(['exists' => false]);
                }
                return response()->json(['success' => true, 'message' => 'Email is available']);
                
            
                
            default:
                return response()->json(['success' => false, 'message' => 'Invalid action']);
        }
    }
}
