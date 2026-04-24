@extends('partials.guest')

@section('title', 'Sign in')

@section('content')
<style>
    :root {
        --login-surface: rgba(7, 24, 39, 0.74);
        --login-surface-soft: rgba(255, 255, 255, 0.06);
        --login-line: rgba(226, 232, 240, 0.12);
        --login-text-soft: rgba(226, 232, 240, 0.72);
        --login-accent: #22c55e;
        --login-accent-2: #38bdf8;
        --login-shadow: 0 24px 60px rgba(2, 8, 23, 0.45);
    }

    .login-shell {
        min-height: 100vh;
        display: flex;
        align-items: center;
        padding: 24px;
    }

    .login-panel {
        width: 100%;
        max-width: 1360px;
        margin: 0 auto;
        border-radius: 32px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(255, 255, 255, 0.04);
        box-shadow: var(--login-shadow);
        backdrop-filter: blur(16px);
    }

    .login-brand-side {
        position: relative;
        padding: 56px;
        min-height: 100%;
        background:
            radial-gradient(circle at top left, rgba(56, 189, 248, 0.2), transparent 26%),
            linear-gradient(160deg, rgba(4, 16, 28, 0.96), rgba(7, 24, 39, 0.92));
    }

    .login-brand-side::after {
        content: "";
        position: absolute;
        right: -60px;
        bottom: -70px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(34, 197, 94, 0.24), transparent 68%);
    }

    .brand-pill {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.08);
        color: #e2e8f0;
        font-size: 0.78rem;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .brand-pill img {
        width: 34px;
        height: 34px;
        object-fit: contain;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.12);
        padding: 4px;
    }

    .hero-title {
        margin-top: 30px;
        margin-bottom: 18px;
        font-size: clamp(2.5rem, 4vw, 4.3rem);
        line-height: 1.02;
        font-weight: 700;
        color: #f8fafc;
    }

    .hero-title .gradient-word {
        background: linear-gradient(135deg, #38bdf8, #22c55e);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .hero-copy {
        max-width: 600px;
        font-size: 1rem;
        color: var(--login-text-soft);
        margin-bottom: 32px;
    }

    .signal-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .signal-card {
        padding: 20px;
        border-radius: 22px;
        background: var(--login-surface-soft);
        border: 1px solid var(--login-line);
        transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
    }

    .signal-card:hover {
        transform: translateY(-4px);
        border-color: rgba(56, 189, 248, 0.32);
        background: rgba(255, 255, 255, 0.08);
    }

    .signal-card i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 14px;
        margin-bottom: 14px;
        background: rgba(56, 189, 248, 0.14);
        color: #7dd3fc;
        font-size: 1.2rem;
    }

    .signal-card h6 {
        color: #f8fafc;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .signal-card p {
        margin: 0;
        color: var(--login-text-soft);
        font-size: 0.92rem;
    }

    .compliance-strip {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 28px;
    }

    .compliance-strip span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.06);
        color: #e2e8f0;
        font-size: 0.83rem;
    }

    .login-form-side {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px;
        background: rgba(255, 255, 255, 0.03);
    }

    .login-form-card {
        width: 100%;
        max-width: 420px;
        padding: 32px;
        border-radius: 28px;
        background: var(--login-surface);
        border: 1px solid var(--login-line);
        box-shadow: var(--login-shadow);
    }

    .login-label {
        display: block;
        margin-bottom: 8px;
        color: #cbd5e1;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .login-input-wrap {
        position: relative;
    }

    .login-input-wrap i {
        position: absolute;
        top: 50%;
        left: 16px;
        transform: translateY(-50%);
        color: rgba(226, 232, 240, 0.48);
        font-size: 1rem;
    }

    .login-control {
        width: 100%;
        min-height: 54px;
        padding: 0 16px 0 46px;
        border-radius: 16px;
        border: 1px solid rgba(226, 232, 240, 0.12);
        background: rgba(255, 255, 255, 0.06);
        color: #ffffff;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .login-control::placeholder {
        color: rgba(226, 232, 240, 0.34);
    }

    .login-control:focus {
        outline: 0;
        border-color: rgba(56, 189, 248, 0.42);
        box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.14);
        background: rgba(255, 255, 255, 0.09);
    }

    .signin-btn {
        width: 100%;
        min-height: 54px;
        border: 0;
        border-radius: 16px;
        background: linear-gradient(135deg, var(--login-accent), var(--login-accent-2));
        color: #03111c;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .signin-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 30px rgba(34, 197, 94, 0.22);
    }

    .trust-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-top: 18px;
    }

    .trust-item {
        padding: 12px 10px;
        border-radius: 14px;
        text-align: center;
        background: rgba(255, 255, 255, 0.05);
        color: var(--login-text-soft);
        font-size: 0.76rem;
        font-weight: 600;
    }

    .trust-item i {
        display: block;
        margin-bottom: 6px;
        color: #7dd3fc;
        font-size: 1rem;
    }

    .login-helper {
        color: var(--login-text-soft);
    }

    @media (max-width: 991.98px) {
        .login-brand-side {
            padding: 36px 28px;
        }

        .login-form-side {
            padding: 28px;
        }
    }

    @media (max-width: 767.98px) {
        .login-shell {
            padding: 14px;
        }

        .login-brand-side,
        .login-form-side,
        .login-form-card {
            padding: 22px;
        }

        .signal-grid {
            grid-template-columns: 1fr;
        }

        .trust-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="login-shell">
    <div class="login-panel">
        <div class="row g-0">
            <div class="col-lg-7 login-brand-side">
                <div class="brand-pill">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Vincatis LMS logo">
                    Vincatis LMS
                </div>

                <h1 class="hero-title">
                    Train smarter.
                    <span class="gradient-word">Stay audit ready.</span>
                </h1>

                <p class="hero-copy">
                    A modern learning workspace for regulated teams, combining role-based delivery, qualification tracking, session logging, and assessment workflows in one secure place.
                </p>

                <div class="signal-grid">
                    <div class="signal-card">
                        <i class="mdi mdi-shield-check-outline"></i>
                        <h6>Compliance-first</h6>
                        <p>Support GMP, GLP, SOP, and controlled-document training with clearer visibility.</p>
                    </div>
                    <div class="signal-card">
                        <i class="mdi mdi-account-tie-outline"></i>
                        <h6>Role-based journeys</h6>
                        <p>Map training to departments, designations, responsibilities, and trainer assignments.</p>
                    </div>
                    <div class="signal-card">
                        <i class="mdi mdi-clipboard-data-outline"></i>
                        <h6>Progress clarity</h6>
                        <p>Track logs, enrollments, and assessments without losing the operational story.</p>
                    </div>
                    <div class="signal-card">
                        <i class="mdi mdi-lightning-bolt-outline"></i>
                        <h6>Faster daily use</h6>
                        <p>The refreshed UI reduces friction for admins, trainers, and learners alike.</p>
                    </div>
                </div>

                <div class="compliance-strip">
                    <span><i class="mdi mdi-check-decagram"></i> GMP aligned</span>
                    <span><i class="mdi mdi-file-document-check-outline"></i> Audit traceable</span>
                    <span><i class="mdi mdi-lock-outline"></i> Secured access</span>
                </div>
            </div>

            <div class="col-lg-5 login-form-side">
                <div class="login-form-card">
                    <div class="mb-4">
                        <div class="login-helper text-uppercase mb-2" style="font-size: 0.74rem; letter-spacing: 0.16em;">Welcome back</div>
                        <h3 class="text-white font-weight-bold mb-2">Sign in to continue</h3>
                        <p class="login-helper mb-0">Access your training dashboard, assignments, records, and exam workflows.</p>
                    </div>

                    <form method="POST" action="{{ route('login.post') }}">
                        @csrf

                        <div class="form-group">
                            <label class="login-label">Corporate ID</label>
                            <div class="login-input-wrap">
                                <i class="mdi mdi-account-outline"></i>
                                <input type="text" name="corporate_id" class="login-control @error('corporate_id') is-invalid @enderror" placeholder="Enter your corporate ID" value="{{ old('corporate_id') }}" required autofocus>
                            </div>
                            @error('corporate_id')
                                <span class="text-danger small d-block mt-2">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="login-label">Password</label>
                            <div class="login-input-wrap">
                                <i class="mdi mdi-lock-outline"></i>
                                <input type="password" name="password" class="login-control" placeholder="Enter your password" required>
                            </div>
                        </div>

                        <button type="submit" class="signin-btn mt-3">
                            <i class="mdi mdi-login-variant mr-2"></i>
                            Sign in
                        </button>
                    </form>

                    @if ($errors->any())
                        <div class="alert alert-danger mt-4 mb-0">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div class="trust-row">
                        <div class="trust-item">
                            <i class="mdi mdi-feather"></i>
                            E-signature
                        </div>
                        <div class="trust-item">
                            <i class="mdi mdi-history"></i>
                            Audit trail
                        </div>
                        <div class="trust-item">
                            <i class="mdi mdi-shield-lock-outline"></i>
                            Protected
                        </div>
                    </div>

                    <p class="mt-4 mb-0 text-center login-helper">
                        Need a new account?
                        <a href="{{ route('register') }}" class="text-white">Register as trainee</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
