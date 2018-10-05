@extends('base')

@section('content')

    <h1>Login</h1>

    <hr>

    <div class="panel panel-default">

        <form method="POST" action="{{ route('loginPost') }}">

            <div class="panel-body">

                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ csrf_field() }}

                <div class="form-group">
                    <label for="email">Username</label>
                    <input type="email" class="form-control" id="username" name="username" placeholder="Username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                </div>

            </div>

            <div class="panel-footer">

                <button type="submit" class="btn btn-lg btn-block btn-primary">
                    Login
                </button>

            </div>

        </form>

    </div>

@endsection
