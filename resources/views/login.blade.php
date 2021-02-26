<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>
    <!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">-->
    <link rel="stylesheet" href="{{asset('css/app.css')}}">
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
    <div class="container">
        <div class="col-md-4 offset-md-4 mt-5">
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="text-center font-weight-bold text-white" >Form Login</h3>
                </div>
                <form action="/login" method="post">
                @csrf
                <div class="card-body">

                @if($message = Session::get('fail')) <!--Ini adalah fungsi Session dari Laravel, sbiasanya utuk memunculkan pesan-->
                 <div class="alert alert-danger alert-block"> <!--Code Session ini sendiri akan memunculkan kotak pesan dengan kondisi bila menerima pesan Session sukses dari file lain seperti dari ArticleController@edit_process-->
                 <button type="button" class="close" data-dismiss="alert">x</button> 
                    <strong>{{ $message }}</strong>
                </div>
                 @endif

                    @if(session('errors'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Something it's wrong:
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (Session::has('success'))
                        <div class="alert alert-success">
                            {{ Session::get('success') }}
                        </div>
                    @endif
                    @if (Session::has('error'))
                        <div class="alert alert-danger">
                            {{ Session::get('error') }}
                        </div>
                    @endif
                    <div class="form-group">
                        <label for=""><strong>Email</strong></label>
                        <input type="text" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}">
                    </div>
                    <div class="form-group">
                        <label for=""><strong>Password</strong></label>
                        <input type="password" name="password" class="form-control" placeholder="Password">
                    </div>
                    <p class="text-left"> <a href="/forgotpass">Forgot password ?</a></p>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block">Log In</button>
                    <p class="text-center">Not Registered ? <a href="{{ route('register') }}">Sign Up</a></p>
                </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>