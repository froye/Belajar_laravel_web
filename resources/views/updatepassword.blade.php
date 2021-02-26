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
                <div class="card-header">
                    <h3 class="text-center">Update Password</h3>
                </div>
                <form action="/updatepass_process" method="post">
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
                    @if (Session::has('error'))
                        <div class="alert alert-danger">
                            {{ Session::get('error') }}
                        </div>
                    @endif

                    <input type="hidden" name="userid" value="{{$userid}}">
                    <input type="hidden" name="token" value="{{$token}}">

                    <div class="form-group">
                        <label for=""><strong>Password</strong></label>
                        <input type="password" name="password" class="form-control" placeholder="Password">
                    </div>
                    <div class="form-group">
                        <label for=""><strong>Confirm Password</strong></label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password">
                    </div>
                    <p class="text-left"> <a href="/login">Back to Login</a></p>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-secondary btn-block">Submit</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>