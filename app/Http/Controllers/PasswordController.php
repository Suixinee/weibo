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
    public function __construct()
    {
        $this->middleware('throttle:10,1', [
            'only' => ['showLinkRequestForm']
        ]);
    }
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
            session()->flash('danger', '邮箱未注册');
            return redirect()->back()->withInput();
        }
        $token = hash_hmac('sha256', Str::random(40), config('app.key'));

        DB::table('password_resets')->updateOrInsert([
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => new Carbon,
        ]);
        Mail::send('emails.reset_link', compact('token'), function ($message) use ($email) {
            $message->to($email)->subject("忘记密码");
        });
        session()->flash('success', '重置邮件发送成功');
        return redirect()->back();
    }
    public function showResetForm(Request $request)
    {
        $token = $request->route()->parameter('token');
        return view('auth.password.reset', compact('token'));
    }
    public function reset(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'email' => 'required',
            'password' => 'required|confirmed|min:6'
        ]);
        $email = $request->email;
        $token = $request->token;
        $expires = 600;

        $user = User::where('email', $email)->first();
        if (is_null($user)) {
            session()->flash('danger', '邮箱未注册');
            return redirect()->back()->withInput();
        }
        $recode = (array)DB::table('password_resets')->where('email', $email)->first();
        if ($recode) {
            if (Carbon::parse($recode['created_at'])->addSeconds($expires)->isPast()) {
                session()->flash('danger', '链接已经过期,请重新尝试');
                return redirect()->back();
            }
            if (!Hash::check($token, $recode['token'])) {
                session()->flash('denager', '令牌错误');
                return redirect()->back();
            }
            $user->update(['password' => bcrypt($request->password)]);
            session()->flash('success', '密码重置成功,请重新登录');
            return redirect()->route('login');
        }
        session()->flash('danger', '未找到重置记录');
        return redirect()->back();
    }
}
