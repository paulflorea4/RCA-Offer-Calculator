<style>

    .rca-container {
        max-width: 900px;
        margin: 40px auto;
        font-family: Arial, sans-serif;
    }

    .rca-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .rca-card {
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        background: #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transition: 0.25s ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .rca-card:hover {
        transform: translateY(-3px);
    }

    .rca-left h3 {
        margin: 0;
        font-size: 18px;
    }

    .rca-price {
        font-size: 22px;
        font-weight: bold;
        color: #2e7d32;
        margin-top: 6px;
    }

    .rca-meta {
        font-size: 14px;
        color: #666;
        margin-top: 4px;
    }

    .rca-actions {
        display: flex;
        gap: 10px;
        flex-direction: column;
    }

    .rca-btn {
        padding: 8px 14px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        font-size: 14px;
        text-align: center;
    }

    .rca-btn-primary {
        background: #6c5ce7;
        color: white;
    }

    .rca-btn-primary:hover {
        background: #5a4cd3;
    }

    .rca-btn-secondary {
        background: #f3f3f3;
        color: #333;
    }

    .rca-btn-secondary:hover {
        background: #e4e4e4;
    }

</style>


<div class="rca-container">

    <h2 class="rca-title">RCA Offers</h2>

    @foreach($offers as $offer)

        <div class="rca-card">

            <div class="rca-left">
                <h3>{{ strtoupper($offer['provider']) }}</h3>

                <div class="rca-price">
                    {{ $offer['price'] }} {{ $offer['currency'] }}
                </div>

                <div class="rca-meta">
                    {{ $offer['startDate'] }} → {{ $offer['endDate'] }}
                </div>

                <div class="rca-meta">
                    Bonus Malus: {{ $offer['bonusMalus'] }}
                </div>
            </div>

            <div class="rca-actions">

                @if($offer['pid'])
                    <a class="rca-btn rca-btn-secondary"
                       href="{{ $offer['pid'] }}"
                       target="_blank">
                        PID Document
                    </a>
                @endif

                <a class="rca-btn rca-btn-secondary"
                   href="{{ route('rca.offer.download', $offer['offerId']) }}"
                   target="_blank">
                    Download PDF
                </a>

                <form method="POST" action="{{ route('rca.policy') }}">
                    @csrf
                    <input type="hidden" name="offerId" value="{{ $offer['offerId'] }}">
                    <input type="hidden" name="amount" value="{{ $offer['price'] }}">

                    <button class="rca-btn rca-btn-primary" type="submit">
                        Issue Policy
                    </button>
                </form>

            </div>

        </div>

    @endforeach

</div>
