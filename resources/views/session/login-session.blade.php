@extends('layouts.user_type.guest')

@section('content')

    <main class="main-content">
        <section>
            <div class="page-header min-vh-100 d-flex align-items-center">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-xl-4 col-lg-5 col-md-6">
                            <div class="card border-0 shadow-lg">
                                <div class="card-body p-5">
                                    <div class="text-center mb-4">
                                        <h2 class="font-weight-bold mb-2">Sign In</h2>
                                        <p class="text-muted small">Enter your credentials to continue</p>
                                    </div>

                                    <form role="form" method="POST" action="/session">
                                        @csrf

                                        <div class="mb-3">
                                            <label class="form-label small text-muted">Email</label>
                                            <input type="email"
                                                   class="form-control form-control-lg"
                                                   name="email"
                                                   id="email"
                                                   placeholder="your@email.com"
                                                   required>
                                            @error('email')
                                            <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small text-muted">Password</label>
                                            <input type="password"
                                                   class="form-control form-control-lg"
                                                   name="password"
                                                   id="password"
                                                   placeholder="••••••••"
                                                   required>
                                            @error('password')
                                            <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="rememberMe">
                                                <label class="form-check-label small text-muted" for="rememberMe">
                                                    Remember me
                                                </label>
                                            </div>
                                            <a href="/login/forgot-password" class="small text-decoration-none">
                                                Forgot password?
                                            </a>
                                        </div>

                                        <button type="submit" class="btn btn-dark btn-lg w-100 mb-3">
                                            Sign In
                                        </button>

                                        <div class="text-center">
                                            <span class="small text-muted">Don't have an account?</span>
                                            <a href="/register" class="small text-decoration-none ms-1">Sign up</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

@endsection
