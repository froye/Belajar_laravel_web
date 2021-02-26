<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Validator;
use Hash;
use Session;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Mail\TestEmail;
use Illuminate\Support\Facades\Mail;

 
 
class AuthController extends Controller
{
    private function is_login()
    {
        if(Auth::user()) {
            return true;
        }
        else {
            return false;
        }
    }

    public function showFormLogin()
    {
        if (Auth::check()) { // cek autentikasi lewat fungsi Auth yang akan cek ke tabel users lewat model App.php 
            //Login Success
            $email = Auth::user()->email;
            $cekadmin = DB::table('users')->select('role')->where('email',$email)->where('role','ADMIN')->first();

            if ($cekadmin){
                $userlist = DB::table('users')->orderby('id', 'desc')->paginate(10);
                return view('admin', ['listuser'=>$userlist]); 
            }
            else{
                return redirect('/list_app');
            }
        }
        return view('login');
    }
 
    public function login(Request $request)
    {
        if($this->is_login()){
            return redirect('/list_app');
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required' 
        ]);

        if ($validator->fails()) {
            return redirect()->route('login')
                        ->withErrors($validator);
        }

        $user = User::where('email',$request->email)->first(); // ngambil data user berdasarkan email
        //$user_exist = User::where('email',$request->email)->exists(); // cek apakah email sudah ada

        if (User::where('email', $request->email)->first()=== null) { //menggunakan Model User.php yang ada d folder App/Model, dimana secara otomatis ia akan mengenali tabelnya adalah bentuk jamak dari user yaitu tabel users
            // email doesn't exists
            Session::flash('fail', 'Email belum terdaftar');
             return redirect()->route('login')->withInput();     
        }

        if($user->email_verified_at == null){
            Session::flash('fail', 'Email belum terverifikasi');
             return redirect()->route('login')->withInput();        
         }

        $data = [                                           // membuat Array untuk Autentikasi bersarakan email-password
            'email'     => $request->input('email'),
            'password'  => $request->input('password'),
        ];
 
        Auth::attempt($data); //melakukan percobaan untuk autentikasi dimana akan dicari apakah username-pass cocok dengan tabel users
                              
        if (Auth::check()) {  //sebetulnya bisa langsung pake if (Auth::attempt($data)) gak perlu Auth::check(), tp ini hanya mengikuti sesuai tutorial dulu
            //Login Success
          //  return redirect()->route('admin');
         // $userlist = DB::table('users')->orderby('id', 'desc')->get();
         $email = Auth::user()->email;
            $cekadmin = DB::table('users')->select('role')->where('email',$email)->where('role','ADMIN')->first();

            if ($cekadmin){
                $userlist = DB::table('users')->orderby('id', 'desc')->paginate(10);
                return view('admin', ['listuser'=>$userlist]); 
            }
            else{
                return redirect('/list_app');
            }
          //$userlist = DB::table('users')->orderby('id', 'desc')->paginate(10);
           //return view('admin', ['listuser'=>$userlist]); 
 
        } else { // false
 
            //Login Fail
            Session::flash('error', 'Email atau password salah');
            return redirect()->route('login')->withInput();
        }
 
    }
 
    public function show_by_admin()
    {
        if($this->is_login())
        {
            $email = Auth::user()->email;
            $cekadmin = DB::table('users')->select('role')->where('email',$email)->where('role','ADMIN')->first();
            if ($cekadmin){
                $userlist = DB::table('users')->orderby('id', 'desc')->paginate(10);
                return view('admin', ['listuser'=>$userlist]); 
            }
            else{
                return redirect('/list_app');
            }
          //  $email = Auth::user()->email; 
          //  $user = DB::table('users')->where('email', $email)->first();
           // $userlist = DB::table('users')->orderby('id', 'desc')->get();
          // $userlist = DB::table('users')->orderby('id', 'desc')->paginate(10);
           //return view('admin', ['listuser'=>$userlist]); 
        }
        else
        {
           return redirect('/login');
        }
    }

    public function verif($id)
    {
        $tanggal = Carbon::now()->timezone('Asia/Jakarta');
        $user = DB::table('users')->where('id', $id)->first();
        $email = Auth::user()->email;
        $cekadmin = DB::table('users')->select('role')->where('email',$email)->where('role','ADMIN')->first();

        if($cekadmin)
        {
            if($user->email_verified_at== null){
            DB::table('users')->where('id', $id)
                            ->update(['email_verified_at' => $tanggal]);
            
            //Session::flash('success', 'User berhasil diverifikasi');

            $username = $user->email;
            $data = [
                'name' => $user->name
            ];
            $send_email = Mail::send('isi_email_verif', $data, function($message) use ($email, $username) {
                $message->to($email, $username)
                        ->subject('Verifikasi Email');
                $message->from('laravel@blog.example','Laravel');
            }); 
            
            if(Mail::failures()){
                Session::flash('fail', 'Verifikasi sukses, Mengirim email verifikasi gagal.');
                return redirect()->action('AuthController@show_by_admin');
               }
            else{
                Session::flash('success', 'Verifikasi sukses, E-mail konfirmasi berhasil dikirim');
                return redirect()->action('AuthController@show_by_admin');
               }

            return redirect()->action('AuthController@show_by_admin');
            }
            else{
                $tanggal_verif= $user->email_verified_at;
                $pesan='User Sudah terverifikasi pada ';
                Session::flash('fail', $pesan.$tanggal_verif);
                return redirect()->action('AuthController@show_by_admin');
            }
            
        }
        else
        {
           return redirect('/login');
        }
    }

    public function edit_user($id)
    {
        $email = Auth::user()->email;
        $cekadmin = DB::table('users')->select('role')->where('email',$email)->where('role','ADMIN')->first();

        if($cekadmin)
        {
            $edituser = DB::table('users')->where('id', $id)->first();
            return view('edit_user', ['userdata'=>$edituser]);
        }
 
        else
        {
           return redirect('/login');
        }
    }

    public function edit_user_process(Request $request)
    {
     /*   $user_id = $request->id;
        $user_name = $request->name;
        $user_email = $request->email;
     //   $cek_user_name = DB::table('users')->where('app_name', $request->name_app)->exists();
        $edituser = DB::table('users')->where('id', $user_id)->first();
        
        if (is_null($user_name)) //cek apakah input app_name kosong atau tidak
        {
            
            Session::flash('failed', 'User Name Tidak Boleh Kosong');
            return view('edit_user', ['appdata'=>$editapp]);
        }
        if (is_null($user_email)) //cek apakah input app_desc kosong atau tidak
        {
            Session::flash('failed', 'User Email Tidak Boleh Kosong');
            return view('edit_user', ['appdata'=>$editapp]);
        }
    */
    $rules = [
        'name'                  => 'required',
        'email'                 => 'required|email'
    ];

    $messages = [
        'name.required'         => 'Nama wajib diisi',
        'email.required'        => 'Email wajib diisi',
        'email.email'           => 'Email tidak valid'
    ];

    $validator = Validator::make($request->all(), $rules, $messages);
    if($validator->fails()){
        return redirect()->back()->withErrors($validator)->withInput($request->all);
    }
    
        $user_id = $request->id;
        $user_name = $request->name;
        $user_email = $request->email;
        $user_role = $request->role;
        DB::table('users')->where('id', $user_id)
                            ->update(['name' => $user_name, 'email' => $user_email, 'role' => $user_role]);
        Session::flash('success', 'Data User berhasil diedit');
        return redirect('/admin');
    }

    public function delete($id){
        if($this->is_login())
        {
             //menghapus artikel dengan ID sesuai pada URL
            DB::table('users')->where('id', $id)
                                ->delete();
 
            //membuat pesan yang akan ditampilkan ketika artikel berhasil dihapus
            Session::flash('success', 'User berhasil dihapus');
            return redirect('/admin');
        }
 
        else
        {
           return redirect('/login');
        }
    }

    public function forgotpass(){
      return view ('forgot_pass');
    }

    public function forgotpass_process(Request $request){
       $email = $request->email;
       
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('forgotpass')
                        ->withErrors($validator);
           }

           $user = User::where('email',$request->email)->first();
       
           if($user == null){
               $msg = ['email does not exist'];
               // dd($msg);
                return redirect()->route('forgotpass')
                           ->withErrors($msg);
           }   

        $token = Str::random(40);
        $user->remember_token = $token;
        $saveuser = $user->save();
        
        if(!$saveuser){
            Session::flash('fail', 'Please Try again..');
            return redirect()->route('login');
        }
        
        $data = [
            'name' => $user->name,
            'token' => $token
        ];

        $useremail= $request->email;
        $username = $user->name;

   // $send_email=Mail::to($email)->send(new TestEmail());
    
    $send_email = Mail::send('isi_email', $data, function($message) use ($useremail, $username) {
        $message->to($useremail, $username)
                ->subject('Reset password');
        $message->from('laravel@blog.example','Laravel');
    }); 
	
    if(Mail::failures()){
        Session::flash('fail', 'Mengirim email gagal, Silahkan coba lagi.');
        return view ('login');
       }
    else{
        Session::flash('success', 'E-mail konfirmasi berhasil dikirim');
        return view ('login');
       }
           
    }

    public function updatepass_edit($token){
        $user = User::where('remember_token',$token)->first();
        if(!$user){
            return redirect()->route('login');
        }
        return view('updatepassword',['userid'=>$user->id,'token'=>$token]);
      }
      
      public function updatepass_edit_process(Request $request){
        $rules = [
            'password'              => 'required|min:8|confirmed'
        ];
 
        $messages = [
            'password.required'     => 'Password wajib diisi',
            'password.min'              => 'Password minimal 8 karakter',
            'password.confirmed'    => 'Password tidak sama dengan konfirmasi password'
        ];
 
        $validator = Validator::make($request->all(), $rules, $messages);
 
        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput($request->all);
        }

        $userid = $request->userid;
        $new_password= Hash::make($request->password);
        $delete_token = '';
        // DB::table('users')->where('id', $userid)
          //                  ->update(['password' => $new_password, 'remember_token' => $delete_token]);
 
        try {
            DB::table('users')->where('id', $userid)
            ->update(['password' => $new_password, 'remember_token' => $delete_token]);
         } catch (\Illuminate\Database\QueryException $e) {
            Session::flash('errors', ['' => 'Update Password gagal, coba beberapa saat lagi.']);
            return redirect()->route('login');
         } catch (\Exception $e) {
            Session::flash('errors', ['' => 'Update Password gagal, coba beberapa saat lagi.']);
            return redirect()->route('login');
         }

         Session::flash('success', 'Password berhasil diubah');
         return redirect()->route('login');

       /* if($simpan){
            Session::flash('success', 'Password berhasil diedit');
            return redirect()->route('login');
        } else {
            Session::flash('errors', ['' => 'Update Password gagal, coba beberapa saat lagi.']);
            return redirect()->route('login');
        }*/

      }


    public function user_search(Request $request)
{
	// menangkap data pencarian
	$search = $request->search;
 
 	// mengambil data dari table users sesuai pencarian data
	$user = DB::table('users')
	->where('name','like',"%".$search."%")
	->paginate();
 
    	// mengirim data user ke form admin
	return view('admin',['listuser' => $user]);
 
}


    public function showFormRegister()
    {
        return view('register');
    }
 
    public function register(Request $request)
    {
        $rules = [
            'name'                  => 'required|min:3|max:35',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|confirmed'
        ];
 
        $messages = [
            'name.required'         => 'Nama Lengkap wajib diisi',
            'name.min'              => 'Nama lengkap minimal 3 karakter',
            'name.max'              => 'Nama lengkap maksimal 35 karakter',
            'email.required'        => 'Email wajib diisi',
            'email.email'           => 'Email tidak valid',
            'email.unique'          => 'Email sudah terdaftar',
            'password.required'     => 'Password wajib diisi',
            'password.confirmed'    => 'Password tidak sama dengan konfirmasi password'
        ];
 
        $validator = Validator::make($request->all(), $rules, $messages);
 
        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput($request->all);
        }
 
        $user = new User; // ini adalah mendefinisikan $user sebagai Model, dimana modelnya bernama user.php ada di folder App/Model
        $user->name = ucwords(strtolower($request->name)); //jd karakter yg masuk akan di lower case kan dulu semua oleh fungsi strtolower lalu akan di buat huruf besar hanya untuk huruf pertama oleh fungsi ucwords 
        $user->email = strtolower($request->email); 
        $user->password = Hash::make($request->password);
      //  $user->email_verified_at = \Carbon\Carbon::now();
        $simpan = $user->save();
 
        if($simpan){
            Session::flash('success', 'Register berhasil! Silahkan Verifikasi untuk dapat login');
            return redirect()->route('login');
        } else {
            Session::flash('errors', ['' => 'Register gagal! Silahkan ulangi beberapa saat lagi']);
            return redirect()->route('register');
        }
    }
 
    public function logout()
    {
        Auth::logout(); // menghapus session yang aktif
        return redirect()->route('login');
    }
 
 
}