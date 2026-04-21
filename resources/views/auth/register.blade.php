@extends('partials.guest')

@section('title', 'Register')

@section('content')
<style>
    .register-shell {
        min-height: 100vh;
        display: flex;
        align-items: center;
        padding: 24px;
    }

    .register-panel {
        width: 100%;
        max-width: 1180px;
        margin: 0 auto;
        border-radius: 32px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(255, 255, 255, 0.04);
        box-shadow: 0 24px 60px rgba(2, 8, 23, 0.45);
        backdrop-filter: blur(16px);
    }

    .register-side {
        height: 100%;
        padding: 52px;
        background:
            radial-gradient(circle at top left, rgba(56, 189, 248, 0.18), transparent 26%),
            linear-gradient(160deg, rgba(4, 16, 28, 0.96), rgba(7, 24, 39, 0.92));
        color: #f8fafc;
    }

    .register-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.08);
        color: #e2e8f0;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .register-badge img {
        width: 34px;
        height: 34px;
        object-fit: contain;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.12);
        padding: 4px;
    }

    .register-title {
        margin-top: 28px;
        font-size: clamp(2.3rem, 4vw, 4rem);
        line-height: 1.04;
        font-weight: 700;
        color: #f8fafc;
    }

    .register-title span {
        background: linear-gradient(135deg, #38bdf8, #22c55e);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .register-copy {
        max-width: 560px;
        color: rgba(226, 232, 240, 0.76);
        font-size: 1rem;
        margin: 18px 0 28px;
    }

    .register-points {
        display: grid;
        gap: 16px;
    }

    .register-point {
        padding: 18px 20px;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.05);
    }

    .register-point strong {
        display: block;
        color: #f8fafc;
        margin-bottom: 6px;
    }

    .register-point span {
        color: rgba(226, 232, 240, 0.72);
    }

    .register-form-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 38px;
        background: rgba(255, 255, 255, 0.03);
    }

    .register-card {
        width: 100%;
        max-width: 430px;
        padding: 32px;
        border-radius: 28px;
        background: rgba(7, 24, 39, 0.78);
        border: 1px solid rgba(226, 232, 240, 0.12);
        box-shadow: 0 24px 60px rgba(2, 8, 23, 0.35);
    }

    .register-label {
        display: block;
        margin-bottom: 8px;
        color: #cbd5e1;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .register-control {
        min-height: 52px;
        border-radius: 16px;
        border: 1px solid rgba(226, 232, 240, 0.12);
        background: rgba(255, 255, 255, 0.06);
        color: #fff;
    }

    .register-control:focus {
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
    }

    .register-btn {
        width: 100%;
        min-height: 54px;
        border: 0;
        border-radius: 16px;
        background: linear-gradient(135deg, #22c55e, #38bdf8);
        color: #03111c;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .register-card .text-muted,
    .register-card small {
        color: rgba(226, 232, 240, 0.68) !important;
    }

    @media (max-width: 991.98px) {
        .register-side,
        .register-form-wrap,
        .register-card {
            padding: 24px;
        }
    }
</style>

<div class="register-shell">
    <div class="register-panel">
        <div class="row g-0">
            <div class="col-lg-7 register-side">
                <div class="register-badge">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Vincatis LMS logo">
                    Vincatis LMS
                </div>

                <h1 class="register-title">
                    Create your learning account.
                    <span>Start as a trainee.</span>
                </h1>

                <p class="register-copy">
                    New registrations are automatically assigned the trainee role, so each new user begins with the correct access level for training, exams, and progress tracking.
                </p>

                <div class="register-points">
                    <div class="register-point">
                        <strong>Automatic role assignment</strong>
                        <span>Every registered account is created with the default `Trainee` role.</span>
                    </div>
                    <div class="register-point">
                        <strong>Ready for onboarding</strong>
                        <span>Users can sign in immediately and access trainee-specific modules after registration.</span>
                    </div>
                    <div class="register-point">
                        <strong>Simple first step</strong>
                        <span>Only the essentials are required: full name, email, and password. Your trainee user ID is generated automatically.</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 register-form-wrap">
                <div class="register-card">
                    <div class="mb-4">
                        <div class="text-uppercase mb-2" style="font-size: 0.74rem; letter-spacing: 0.16em; color: rgba(226,232,240,0.72);">Registration</div>
                        <h3 class="text-white font-weight-bold mb-2">Create trainee account</h3>
                        <p class="text-muted mb-0">Fill in the details below and we’ll set up your trainee access automatically.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.post') }}">
                        @csrf

                        <div class="form-group">
                            <label class="register-label">Full Name</label>
                            <input type="text" name="name" class="form-control register-control" value="{{ old('name') }}" required>
                        </div>

                        <div class="form-group">
                            <label class="register-label">Email Address</label>
                            <input type="email" name="email" class="form-control register-control" value="{{ old('email') }}" required>
                        </div>

                        <div class="form-group">
                            <label class="register-label">Password</label>
                            <input type="password" name="password" class="form-control register-control" required>
                        </div>

                        <div class="form-group">
                            <label class="register-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control register-control" required>
                        </div>

                        <button type="submit" class="register-btn mt-3">Create Account</button>
                    </form>

                    <p class="mt-4 mb-0 text-center text-muted">
                        Already have an account?
                        <a href="{{ route('login') }}" class="text-white">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
