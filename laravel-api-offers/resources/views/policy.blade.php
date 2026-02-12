@if($policy)

    <div class="policy-container">

        <h2>✅ RCA Policy Issued Successfully</h2>

        <div class="policy-card">

            <div class="row">
                <span>Provider:</span>
                <strong>{{ strtoupper($policy['data']['policies'][0]['provider']['organization']['businessName']) }}</strong>
            </div>

            <div class="row">
                <span>Policy Number:</span>
                <strong>{{ $policy['data']['policies'][0]['number'] }}</strong>
            </div>

            <div class="row">
                <span>Series:</span>
                <strong>{{ $policy['data']['policies'][0]['series'] }}</strong>
            </div>

            <div class="row">
                <span>Coverage Period:</span>
                <strong>{{ $policy['data']['policies'][0]['startDate'] }} → {{ $policy['data']['policies'][0]['endDate'] }}</strong>
            </div>

            <div class="row price">
                <span>Total Premium:</span>
                <strong>{{ number_format($policy['data']['policies'][0]['premiumAmount'],2) }} {{ $policy['data']['policies'][0]['currency'] }}</strong>
            </div>

            <div class="row">
                <span>Payment Method:</span>
                <strong>{{ ucfirst($policy['data']['policies'][0]['payment']['method']) }}</strong>
            </div>

            <div class="row">
                <span>Document Number:</span>
                <strong>{{ $policy['data']['policies'][0]['payment']['documentNumber'] }}</strong>
            </div>

            <a class="download-btn"
               href="{{ route('rca.policy.download', $policy['data']['policies'][0]['policyId']) }}"
               target="_blank">

                Download Policy PDF

            </a>
        </div>

        <a class="go-back-btn" href="{{ route('rca.form') }}">
            Go Back
        </a>

    </div>

@endif

<style>.policy-container {
        max-width:600px;
        margin:40px auto;
        font-family:Arial, sans-serif;
    }

    .policy-card {
        border:1px solid #ddd;
        padding:25px;
        border-radius:10px;
        box-shadow:0 3px 12px rgba(0,0,0,0.08);
        background:#fff;
    }

    .row {
        display:flex;
        justify-content:space-between;
        margin-bottom:12px;
        font-size:15px;
    }

    .row span {
        color:#666;
    }

    .price strong {
        color:#0a7a2f;
        font-size:18px;
    }

    .download-btn {
        display:block;
        text-align:center;
        margin-top:20px;
        padding:12px;
        background:#007bff;
        color:white;
        text-decoration:none;
        border-radius:6px;
        font-weight:bold;
        transition:0.2s;
    }

    .download-btn:hover {
        background:#0056b3;
    }

    .go-back-btn {
        display:inline-block;
        text-align:center;
        margin-top:15px;
        padding:10px 18px;
        background:#6c757d; /* Gray color */
        color:white;
        text-decoration:none;
        border-radius:6px;
        font-weight:bold;
        transition:0.2s;
    }

    .go-back-btn:hover {
        background:#495057; /* Darker gray on hover */
    }
</style>
