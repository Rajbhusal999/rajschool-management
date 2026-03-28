<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Smart विद्यालय</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .payment-bg {
            background-image: url('assets/images/payment_bg.png');
            background-attachment: fixed;
        }

        .payment-card {
            background: rgba(20, 20, 30, 0.7);
            /* Darker glass for finance look */
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 3rem;
            border-radius: 24px;
            max-width: 900px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
        }

        .plan-option {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .plan-option:hover,
        .plan-option.active {
            background: rgba(79, 70, 229, 0.2);
            border-color: #4f46e5;
        }

        .holo-badge {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
            display: inline-block;
        }
    </style>
</head>

<body>

    <div class="fullscreen-bg-container payment-bg">
        <div class="payment-card fade-in">
            <!-- Left Side: Invoice Info -->
            <div>
                <span class="holo-badge">Secure Payment</span>
                <h1
                    style="font-size: 2.5rem; margin-bottom: 1rem; background: linear-gradient(to right, #fff, #a5b4fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    Tuition Fees</h1>
                <p style="color: #cbd5e1; margin-bottom: 2rem; line-height: 1.6;">
                    Complete your transaction securely using our antigravity encryption protocols.
                    Your education is the best investment for the future.
                </p>

                <div style="margin-bottom: 2rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: #94a3b8;">
                        <span>Tuition (Semester 1)</span>
                        <span>$1,200.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: #94a3b8;">
                        <span>Lab Fees</span>
                        <span>$150.00</span>
                    </div>
                    <div style="width: 100%; height: 1px; background: rgba(255,255,255,0.1); margin: 1rem 0;"></div>
                    <div style="display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 700;">
                        <span>Total</span>
                        <span>$1,350.00</span>
                    </div>
                </div>

                <a href="dashboard.php" style="color: #a5b4fc; font-size: 0.9rem;">&larr; Back to Dashboard</a>
            </div>

            <!-- Right Side: Payment Methods -->
            <div>
                <h3 style="margin-bottom: 1.5rem;">Select Payment Method</h3>

                <div class="plan-option active" style="margin-bottom: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 24px; height: 24px; background: white; border-radius: 50%;"></div>
                        <div>
                            <div style="font-weight: 600;">Credit Card</div>
                            <div style="font-size: 0.8rem; color: #94a3b8;">Visa, Mastercard, Amex</div>
                        </div>
                    </div>
                </div>

                <div class="plan-option" style="margin-bottom: 2rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 24px; height: 24px; background: #3b82f6; border-radius: 50%;"></div>
                        <div>
                            <div style="font-weight: 600;">Crypto Wallet</div>
                            <div style="font-size: 0.8rem; color: #94a3b8;">BTC, ETH, SOL</div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary" style="width: 100%; padding: 16px; font-size: 1.1rem;">Pay
                    $1,350.00</button>
                <div style="text-align: center; margin-top: 1rem; font-size: 0.8rem; color: #64748b;">
                    <span style="display: inline-block; vertical-align: middle;">🔒</span> 256-bit SSL Encrypted
                </div>
            </div>
        </div>
    </div>

</body>

</html>