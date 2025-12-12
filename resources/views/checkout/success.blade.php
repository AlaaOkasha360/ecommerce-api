@extends('layouts.app')

@section('title', 'Payment Successful - E-Commerce Store')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="card shadow-sm">
                <div class="card-body py-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="text-success mb-3">Payment Successful!</h2>
                    <p class="text-muted mb-4">
                        Thank you for your purchase. Your order has been placed successfully.
                        You will receive an email confirmation shortly.
                    </p>
                    <hr>
                    <div class="d-grid gap-2 col-md-8 mx-auto">
                        <a href="/orders" class="btn btn-primary btn-lg">
                            <i class="bi bi-box"></i> View My Orders
                        </a>
                        <a href="/products" class="btn btn-outline-secondary">
                            <i class="bi bi-bag"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Verify the session if there's a session_id in URL
    const urlParams = new URLSearchParams(window.location.search);
    const sessionId = urlParams.get('session_id');

    if (sessionId && localStorage.getItem('token')) {
        verifyPaymentSession(sessionId);
    }

    // Clear cart badge
    updateCartBadge(0);
});

async function verifyPaymentSession(sessionId) {
    try {
        await axios.post('/payments/verify-session', { session_id: sessionId });
        console.log('Payment session verified');
    } catch (error) {
        console.error('Error verifying session:', error);
    }
}
</script>
@endsection
