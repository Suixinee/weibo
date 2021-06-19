<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
class PasswordController extends Controller
{
    //
    public function showLinkRequestForm()
    {
        return view('auth.password.email');
    }
    public function sendResetLinkEmail(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email'
        ]);
        $email = $request->email;

        $user = User::where('email', $email)->first();
        if (is_null($user)) {
            session()->flash('danger','邮箱未注册');
            return redirect()->back()->withInput();
        }
        $token = hash_hmac('sha256',Str::random(40),config('app.key'));

        DB::table('password_resets')->updateOrInsert([
            'email'=>$email,
            'token'=>Hash::make($token),
            'created_at'=>new Carbon,
        ]);
        Mail::send('emails.reset_link',compact('token'),function($message)use($email){
            $message->to($email)->subject("忘记密码");
        });
        session()->flash('success','重置邮件发送成功');
        return redirect()->back();
    }
}
