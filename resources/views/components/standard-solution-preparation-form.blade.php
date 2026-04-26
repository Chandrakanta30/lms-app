@props([
    'action' => '#',
    'method' => 'POST',
    'values' => [],
])

@php
    $field = fn (string $key, string $default = '') => old($key, data_get($values, $key, $default));
@endphp

<style>
    .prep-card {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: #ffffff;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .prep-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        color: #263238;
        font-size: 18px;
        font-weight: 700;
    }

    .prep-card__body {
        padding: 28px 18px 24px;
    }

    .prep-step {
        margin-bottom: 16px;
        color: #263238;
        font-size: 18px;
        line-height: 2.05;
    }

    .prep-step__label {
        display: inline-block;
        margin-right: 4px;
        padding: 0 3px;
        background: #cfe4ff;
        color: #263238;
        font-weight: 800;
        line-height: 1.2;
    }

    .prep-text {
        background: #cfe4ff;
        box-decoration-break: clone;
        -webkit-box-decoration-break: clone;
    }

    .prep-input {
        display: inline-block;
        width: 130px;
        min-height: 36px;
        margin: 0 5px;
        padding: 6px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        background: #ffffff;
        color: #334155;
        font-size: 16px;
        line-height: 1.2;
        vertical-align: baseline;
    }

    .prep-input--sm {
        width: 78px;
    }

    .prep-input--md {
        width: 106px;
    }

    .prep-input--lg {
        width: 150px;
    }

    .prep-input::placeholder {
        color: #64748b;
    }

    .prep-input:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.22);
        outline: none;
    }

    .prep-actions {
        margin-top: 24px;
    }

    .prep-save {
        min-width: 70px;
        min-height: 42px;
        border: 0;
        border-radius: 6px;
        background: #47ad6d;
        color: #ffffff;
        font-weight: 700;
        cursor: pointer;
    }

    .prep-save:hover {
        background: #3b985f;
    }

    @media (max-width: 768px) {
        .prep-step {
            font-size: 16px;
            line-height: 2.25;
        }

        .prep-input,
        .prep-input--sm,
        .prep-input--md,
        .prep-input--lg {
            width: 100px;
        }
    }
</style>

<form action="{{ $action }}" method="{{ strtoupper($method) === 'GET' ? 'GET' : 'POST' }}" class="prep-card">
    @if(!in_array(strtoupper($method), ['GET', 'POST']))
        @method($method)
    @endif

    @if(strtoupper($method) !== 'GET')
        @csrf
    @endif

    <div class="prep-card__header">
        <span>Preparation of standard solutions — Preparation of Standard stock Solution</span>
        <span aria-hidden="true">-</span>
    </div>

    <div class="prep-card__body">
        <div class="prep-step">
            <span class="prep-step__label">Step-1:</span><br>
            <span class="prep-text">Weighed and transferred</span>
            <input class="prep-input prep-input--lg" type="text" name="step1[ndba_mg]" placeholder="NDBA mg" value="{{ $field('step1.ndba_mg') }}">
            <span class="prep-text">(10 mg) of NDBA,</span>
            <input class="prep-input prep-input--lg" type="text" name="step1[ndpa_mg]" placeholder="NDPA mg" value="{{ $field('step1.ndpa_mg') }}">
            <span class="prep-text">(10 mg) of NDPA,</span>
            <input class="prep-input prep-input--lg" type="text" name="step1[neipa_mg]" placeholder="NEIPA mg" value="{{ $field('step1.neipa_mg') }}">
            <span class="prep-text">(10 mg) of NEIPA and</span>
            <input class="prep-input prep-input--lg" type="text" name="step1[ndipa_mg]" placeholder="NDIPA mg" value="{{ $field('step1.ndipa_mg') }}">
            <span class="prep-text">(10 mg) of NDIPA standard into an individual</span>
            <input class="prep-input prep-input--md" type="text" name="step1[flask_volume_ml]" placeholder="vol mL" value="{{ $field('step1.flask_volume_ml') }}">
            <span class="prep-text">(10 mL) volumetric flask containing</span>
            <input class="prep-input prep-input--lg" type="text" name="step1[methanol_ml]" placeholder="methanol mL" value="{{ $field('step1.methanol_ml') }}">
            <span class="prep-text">(5 mL) of methanol and vortex to dissolve. Diluted up to the mark with methanol and mixed well.</span>
        </div>

        <div class="prep-step">
            <span class="prep-step__label">Step-2:</span><br>
            <span class="prep-text">Transferred</span>
            <input class="prep-input prep-input--md" type="text" name="step2[ndba_volume_ml]" placeholder="vol mL" value="{{ $field('step2.ndba_volume_ml') }}">
            <span class="prep-text">(0.11 mL) of NDBA,</span>
            <input class="prep-input prep-input--md" type="text" name="step2[ndpa_volume_ml]" placeholder="vol mL" value="{{ $field('step2.ndpa_volume_ml') }}">
            <span class="prep-text">(0.11 mL) of NDPA,</span>
            <input class="prep-input prep-input--md" type="text" name="step2[neipa_volume_ml]" placeholder="vol mL" value="{{ $field('step2.neipa_volume_ml') }}">
            <span class="prep-text">(0.11 mL) of NEIPA,</span>
            <input class="prep-input prep-input--md" type="text" name="step2[ndipa_volume_ml]" placeholder="vol mL" value="{{ $field('step2.ndipa_volume_ml') }}">
            <span class="prep-text">(0.11 mL) of NDIPA (Step-1) standard stock solution into a</span>
            <input class="prep-input prep-input--md" type="text" name="step2[flask_volume_ml]" placeholder="vol mL" value="{{ $field('step2.flask_volume_ml') }}">
            <span class="prep-text">(50 mL) volumetric flask containing</span>
            <input class="prep-input prep-input--lg" type="text" name="step2[diluent_ml]" placeholder="diluent mL" value="{{ $field('step2.diluent_ml') }}">
            <span class="prep-text">(20 mL) of diluent and vortex to dissolve. Diluted up to the mark with diluent and mixed well.</span>
        </div>

        <div class="prep-step">
            <span class="prep-step__label">Step-3:</span><br>
            <span class="prep-text">Transferred</span>
            <input class="prep-input prep-input--md" type="text" name="step3[standard_volume_ml]" placeholder="vol mL" value="{{ $field('step3.standard_volume_ml') }}">
            <span class="prep-text">(0.5 mL) of (Step-2) standard stock solution into a</span>
            <input class="prep-input prep-input--md" type="text" name="step3[flask_volume_ml]" placeholder="vol mL" value="{{ $field('step3.flask_volume_ml') }}">
            <span class="prep-text">(25 mL) volumetric flask containing</span>
            <input class="prep-input prep-input--lg" type="text" name="step3[diluent_ml]" placeholder="diluent mL" value="{{ $field('step3.diluent_ml') }}">
            <span class="prep-text">(10 mL) of diluent and vortex for</span>
            <input class="prep-input prep-input--sm" type="text" name="step3[vortex_minutes]" placeholder="minute" value="{{ $field('step3.vortex_minutes') }}">
            <span class="prep-text">(2 minutes). Diluted up to the mark with diluent and mixed well.</span>
        </div>

        <div class="prep-actions">
            <button type="submit" class="prep-save">Save</button>
        </div>
    </div>
</form>
