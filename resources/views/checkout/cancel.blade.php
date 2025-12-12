@extends('layouts.app')

@section('title', 'Payment Cancelled - E-Commerce Store')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="card shadow-sm">
                <div class="card-body py-5">
                    <div class="mb-4">
                        <i class="bi bi-x-circle-fill text-warning" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="text-warning mb-3">Payment Cancelled</h2>
                    <p class="text-muted mb-4">
                        Your payment was cancelled. Don't worry - your cart items are still saved.
                        You can try again whenever you're ready.
                    </p>
                    <hr>
                    <div class="d-grid gap-2 col-md-8 mx-auto">
                        <a href="/cart" class="btn btn-primary btn-lg">
                            <i class="bi bi-cart"></i> Return to Cart
                        </a>
                        <a href="/products" class="btn btn-outline-secondary">
                            <i class="bi bi-bag"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <p class="text-muted">
                    <i class="bi bi-question-circle"></i>
                    Having trouble? <a href="#" class="text-decoration-none">Contact our support team</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
