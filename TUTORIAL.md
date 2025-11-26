# E-commerce API Development Tutorial

This tutorial will guide you through building a complete e-commerce API from scratch using Laravel, covering authentication, products, cart, orders, and payment integration.

---

## Table of Contents

1. [Project Setup](#1-project-setup)
2. [Authentication System](#2-authentication-system)
3. [Product Management](#3-product-management)
4. [Shopping Cart](#4-shopping-cart)
5. [Order Management](#5-order-management)
6. [Payment Integration](#6-payment-integration)
7. [Advanced Topics](#7-advanced-topics)

---

## 1. Project Setup

### Learn by Searching:
- 🔍 "Laravel project setup and installation"
- 🔍 "Laravel environment configuration .env file"
- 🔍 "Laravel database migrations basics"

### Steps:

```bash
# Create new Laravel project
composer create-project laravel/laravel ecommerce-api

# Install JWT authentication
composer require php-open-source-saver/jwt-auth

# Install Stripe SDK
composer require stripe/stripe-php

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce
DB_USERNAME=root
DB_PASSWORD=
```

### Tasks to Complete:
1. ✅ Install Laravel
2. ✅ Configure database connection
3. ✅ Run `php artisan migrate`
4. ✅ Test server with `php artisan serve`

---

## 2. Authentication System

### Learn by Searching:
- 🔍 "Laravel JWT authentication implementation"
- 🔍 "Laravel API authentication best practices"
- 🔍 "JWT token generation and validation"
- 🔍 "Laravel middleware for API authentication"

### Database Schema:

```php
// Migration: create_users_table.php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('first_name');
    $table->string('last_name');
    $table->string('email')->unique();
    $table->string('password');
    $table->string('phone_number')->nullable();
    $table->enum('role', ['admin', 'customer'])->default('customer');
    $table->timestamps();
});
```

### Controller Structure:

```php
// app/Http/Controllers/AuthController.php
class AuthController extends Controller
{
    public function register(Request $request) {
        // 1. Validate input
        // 2. Hash password
        // 3. Create user
        // 4. Generate JWT token
        // 5. Return token + user data
    }
    
    public function login(Request $request) {
        // 1. Validate credentials
        // 2. Attempt authentication
        // 3. Generate JWT token
        // 4. Return token
    }
    
    public function logout() {
        // Invalidate current token
    }
    
    public function refresh() {
        // Generate new token from existing one
    }
    
    public function me() {
        // Return authenticated user data
    }
}
```

### Learn More:
- 🔍 "Laravel request validation rules"
- 🔍 "Laravel password hashing bcrypt"
- 🔍 "JWT refresh token implementation"
- 🔍 "Laravel API resource transformers"

### Tasks to Complete:
1. ✅ Create User model and migration
2. ✅ Implement registration endpoint
3. ✅ Implement login endpoint
4. ✅ Setup JWT configuration
5. ✅ Create auth middleware
6. ✅ Test all auth endpoints with Postman

---

## 3. Product Management

### Learn by Searching:
- 🔍 "Laravel Eloquent relationships"
- 🔍 "Laravel model factories and seeders"
- 🔍 "Laravel resource controllers REST API"
- 🔍 "Laravel pagination for large datasets"

### Database Schema:

```php
// Categories
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100);
    $table->text('description')->nullable();
    $table->string('slug', 100)->unique();
    $table->foreignId('parent_id')->nullable()
          ->constrained('categories')->nullOnDelete();
    $table->timestamps();
    $table->index('slug');
});

// Products
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')
          ->constrained('categories')->restrictOnDelete();
    $table->string('name', 255);
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2);
    $table->decimal('compare_price', 10, 2)->nullable();
    $table->decimal('cost_per_item', 10, 2)->nullable();
    $table->string('sku', 100)->unique();
    $table->string('barcode', 100)->nullable();
    $table->integer('quantity')->default(0);
    $table->decimal('weight', 8, 2)->nullable();
    $table->string('dimensions', 100)->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### Model Relationships:

```php
// Product Model
class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'description', 'price',
        'compare_price', 'cost_per_item', 'sku', 'barcode',
        'quantity', 'weight', 'dimensions', 'is_active'
    ];
    
    // Define relationship to category
    public function category() {
        return $this->belongsTo(Category::class);
    }
    
    // Define relationship to reviews
    public function reviews() {
        return $this->hasMany(Review::class);
    }
}

// Category Model
class Category extends Model
{
    protected $fillable = ['name', 'description', 'slug', 'parent_id'];
    
    // Category has many products
    public function products() {
        return $this->hasMany(Product::class);
    }
    
    // Category can have parent category
    public function parent() {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    
    // Category can have child categories
    public function children() {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
```

### Learn More:
- 🔍 "Laravel eager loading to prevent N+1 queries"
- 🔍 "Laravel query scopes for filtering"
- 🔍 "Laravel search functionality implementation"
- 🔍 "Laravel image upload and storage"

### Tasks to Complete:
1. ✅ Create Category model, migration, controller
2. ✅ Create Product model, migration, controller
3. ✅ Implement CRUD operations for categories
4. ✅ Implement CRUD operations for products
5. ✅ Add product search functionality
6. ✅ Implement product filtering by category
7. ✅ Add pagination to product listings
8. ✅ Create API resources for clean JSON responses

---

## 4. Shopping Cart

### Learn by Searching:
- 🔍 "Laravel session vs database cart implementation"
- 🔍 "Shopping cart best practices web development"
- 🔍 "Laravel unique constraint multiple columns"
- 🔍 "Calculate cart totals in Laravel"

### Database Schema:

```php
// Shopping Carts
Schema::create('shopping_carts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->unique()
          ->constrained('users')->onDelete('cascade');
    $table->timestamps();
});

// Cart Items
Schema::create('cart_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cart_id')
          ->constrained('shopping_carts')->onDelete('cascade');
    $table->foreignId('product_id')
          ->constrained('products')->onDelete('cascade');
    $table->integer('quantity')->default(1);
    $table->decimal('price_at_addition', 10, 2);
    $table->timestamps();
    
    // Prevent duplicate products in cart
    $table->unique(['cart_id', 'product_id']);
});
```

### Controller Logic:

```php
class CartController extends Controller
{
    public function index() {
        // 1. Get or create user's cart
        // 2. Load cart items with product details
        // 3. Calculate totals
        // 4. Return cart data
    }
    
    public function addItem(Request $request) {
        // 1. Validate product exists and quantity
        // 2. Get or create user's cart
        // 3. Check if product already in cart
        //    - If yes: increment quantity
        //    - If no: create new cart item
        // 4. Store current product price
        // 5. Return success message
    }
    
    public function updateItem(Request $request, $itemId) {
        // 1. Find cart item
        // 2. Verify it belongs to user's cart
        // 3. Update quantity
        // 4. Return updated cart
    }
    
    public function removeItem($itemId) {
        // 1. Find cart item
        // 2. Verify ownership
        // 3. Delete item
        // 4. Return success
    }
    
    public function clearCart() {
        // Delete all items from user's cart
    }
}
```

### Learn More:
- 🔍 "Laravel authorization policies for resource ownership"
- 🔍 "Laravel accessor and mutator for computed values"
- 🔍 "Handling cart abandonment in e-commerce"
- 🔍 "Cart item price snapshot vs dynamic pricing"

### Tasks to Complete:
1. ✅ Create Cart and CartItem models/migrations
2. ✅ Implement add to cart functionality
3. ✅ Implement update cart item quantity
4. ✅ Implement remove from cart
5. ✅ Implement get cart with totals
6. ✅ Add authorization checks
7. ✅ Handle edge cases (product deleted, out of stock)

---

## 5. Order Management

### Learn by Searching:
- 🔍 "E-commerce order workflow and states"
- 🔍 "Laravel database transactions for data integrity"
- 🔍 "Order number generation strategies"
- 🔍 "Laravel events and listeners for order processing"

### Database Schema:

```php
// Orders
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')
          ->constrained('users')->restrictOnDelete();
    $table->foreignId('shipping_address_id')
          ->constrained('addresses');
    $table->foreignId('billing_address_id')
          ->constrained('addresses');
    $table->string('order_number', 50)->unique();
    $table->enum('status', [
        'pending', 'processing', 'shipped', 
        'delivered', 'cancelled'
    ])->default('pending');
    $table->decimal('subtotal', 10, 2);
    $table->decimal('tax', 10, 2)->default(0);
    $table->decimal('shipping_cost', 10, 2)->default(0);
    $table->decimal('total_amount', 10, 2);
    $table->enum('payment_status', [
        'pending', 'paid', 'failed', 'refunded'
    ])->default('pending');
    $table->string('payment_method', 50)->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'status', 'payment_status']);
});

// Order Items
Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')
          ->constrained('orders')->onDelete('cascade');
    $table->foreignId('product_id')
          ->constrained('products')->restrictOnDelete();
    $table->string('product_name', 255);
    $table->string('product_sku', 100);
    $table->integer('quantity');
    $table->decimal('price', 10, 2);
    $table->decimal('subtotal', 10, 2);
    $table->timestamps();
});
```

### Order Creation Flow:

```php
public function createOrder(Request $request)
{
    // Step 1: Validate addresses
    $shippingAddress = Address::where('id', $request->shipping_address_id)
        ->where('user_id', auth()->id())->firstOrFail();
    
    // Step 2: Get cart items
    $cart = Cart::where('user_id', auth()->id())
        ->with('cartItems.product')->first();
    
    if (!$cart || $cart->cartItems->isEmpty()) {
        return error('Cart is empty');
    }
    
    // Step 3: Calculate totals
    $subtotal = $cart->cartItems->sum(function($item) {
        return $item->quantity * $item->price_at_addition;
    });
    $tax = round($subtotal * 0.08, 2);
    $shipping = 10.00;
    $total = $subtotal + $tax + $shipping;
    
    // Step 4: Create order in transaction
    DB::beginTransaction();
    try {
        // Create order
        $order = Order::create([
            'user_id' => auth()->id(),
            'order_number' => $this->generateOrderNumber(),
            'shipping_address_id' => $shippingAddress->id,
            'billing_address_id' => $request->billing_address_id,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping_cost' => $shipping,
            'total_amount' => $total,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => $request->payment_method,
        ]);
        
        // Create order items
        foreach ($cart->cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_sku' => $item->product->sku,
                'quantity' => $item->quantity,
                'price' => $item->price_at_addition,
                'subtotal' => $item->quantity * $item->price_at_addition,
            ]);
        }
        
        // Clear cart
        $cart->cartItems()->delete();
        
        DB::commit();
        return $order;
        
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}

private function generateOrderNumber()
{
    $date = date('Ymd');
    $lastOrder = Order::whereDate('created_at', today())
        ->latest()->first();
    $sequence = $lastOrder ? 
        (int) substr($lastOrder->order_number, -4) + 1 : 1;
    
    return 'ORD-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
}
```

### Learn More:
- 🔍 "Laravel database transactions and rollback"
- 🔍 "Order status state machine implementation"
- 🔍 "Inventory management when order created"
- 🔍 "Laravel queue jobs for order emails"
- 🔍 "Order cancellation and refund logic"

### Tasks to Complete:
1. ✅ Create Order and OrderItem models/migrations
2. ✅ Implement order creation from cart
3. ✅ Generate unique order numbers
4. ✅ Implement order history for users
5. ✅ Implement order details view
6. ✅ Add order cancellation (with rules)
7. ✅ Admin: Update order status
8. ✅ Add order status tracking

---

## 6. Payment Integration (Stripe)

### Learn by Searching:
- 🔍 "Stripe payment intent implementation"
- 🔍 "Laravel Stripe integration tutorial"
- 🔍 "Stripe webhook signature verification"
- 🔍 "PCI compliance payment handling"
- 🔍 "Stripe test cards and testing"

### Setup:

```bash
# Install Stripe PHP SDK
composer require stripe/stripe-php

# Add to .env
STRIPE_KEY=pk_test_your_key_here
STRIPE_SECRET=sk_test_your_secret_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
```

### Database Schema:

```php
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->unique()
          ->constrained('orders')->restrictOnDelete();
    $table->string('stripe_payment_id', 255)->nullable();
    $table->string('stripe_payment_intent', 255)->nullable();
    $table->decimal('amount', 10, 2);
    $table->string('currency', 3)->default('USD');
    $table->enum('status', [
        'pending', 'succeeded', 'failed', 'cancelled'
    ])->default('pending');
    $table->string('payment_method_type', 50)->nullable();
    $table->timestamps();
    
    $table->index('stripe_payment_intent');
});
```

### Payment Flow:

```php
// Step 1: Create Payment Intent
public function createIntent(Request $request)
{
    $order = Order::findOrFail($request->order_id);
    
    $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
    
    $paymentIntent = $stripe->paymentIntents->create([
        'amount' => round($order->total_amount * 100), // cents
        'currency' => 'usd',
        'metadata' => [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]
    ]);
    
    return [
        'payment_intent_id' => $paymentIntent->id,
        'client_secret' => $paymentIntent->client_secret,
    ];
}

// Step 2: Confirm Payment (after Stripe.js confirms)
public function confirmPayment(Request $request)
{
    $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
    $paymentIntent = $stripe->paymentIntents->retrieve(
        $request->payment_intent_id
    );
    
    if ($paymentIntent->status === 'succeeded') {
        $order = Order::find($request->order_id);
        
        // Create payment record
        Payment::create([
            'order_id' => $order->id,
            'stripe_payment_id' => $paymentIntent->latest_charge,
            'stripe_payment_intent' => $paymentIntent->id,
            'amount' => $paymentIntent->amount / 100,
            'currency' => strtoupper($paymentIntent->currency),
            'status' => 'succeeded',
        ]);
        
        // Update order
        $order->update([
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);
        
        return ['success' => true];
    }
}

// Step 3: Webhook Handler
public function webhook(Request $request)
{
    $payload = $request->getContent();
    $sigHeader = $request->header('Stripe-Signature');
    $webhookSecret = env('STRIPE_WEBHOOK_SECRET');
    
    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload, $sigHeader, $webhookSecret
        );
    } catch(\Exception $e) {
        return response()->json(['error' => 'Invalid signature'], 400);
    }
    
    switch ($event->type) {
        case 'payment_intent.succeeded':
            $this->handlePaymentSuccess($event->data->object);
            break;
        case 'payment_intent.payment_failed':
            $this->handlePaymentFailed($event->data->object);
            break;
        case 'charge.refunded':
            $this->handleRefund($event->data->object);
            break;
    }
    
    return response()->json(['received' => true]);
}
```

### Frontend Integration (Example):

```javascript
// Using Stripe.js on frontend
const stripe = Stripe('pk_test_your_publishable_key');

// 1. Create order and get payment intent
const response = await fetch('/api/orders', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer ' + token,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        shipping_address_id: 1,
        billing_address_id: 1,
        payment_method: 'stripe'
    })
});

const { order, next_step } = await response.json();

// 2. Confirm payment with Stripe
const { error } = await stripe.confirmCardPayment(
    next_step.client_secret,
    {
        payment_method: {
            card: cardElement,
            billing_details: { name: 'Customer Name' }
        }
    }
);

// 3. Confirm on backend
if (!error) {
    await fetch('/api/payments/confirm', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            payment_intent_id: next_step.payment_intent_id,
            order_id: order.id
        })
    });
}
```

### Learn More:
- 🔍 "Stripe payment intent vs payment method"
- 🔍 "Stripe SCA (Strong Customer Authentication)"
- 🔍 "Handling Stripe webhook retries"
- 🔍 "Stripe refund implementation"
- 🔍 "Stripe dashboard for testing"

### Tasks to Complete:
1. ✅ Setup Stripe account and get API keys
2. ✅ Create Payment model and migration
3. ✅ Implement create payment intent endpoint
4. ✅ Implement confirm payment endpoint
5. ✅ Setup webhook endpoint
6. ✅ Verify webhook signatures
7. ✅ Handle payment success event
8. ✅ Handle payment failure event
9. ✅ Handle refund event
10. ✅ Test with Stripe test cards

---

## 7. Advanced Topics

### 7.1 API Resources & Transformers

**Learn by Searching:**
- 🔍 "Laravel API resources for JSON responses"
- 🔍 "Laravel resource collections"
- 🔍 "Conditional attributes in API resources"

```php
// app/Http/Resources/ProductResource.php
class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'sku' => $this->sku,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'average_rating' => $this->when($this->reviews_count > 0, 
                round($this->reviews_avg_rating, 1)
            ),
        ];
    }
}
```

### 7.2 Request Validation

**Learn by Searching:**
- 🔍 "Laravel form request validation"
- 🔍 "Laravel custom validation rules"
- 🔍 "Laravel validation error messages"

```php
// app/Http/Requests/CreateOrderRequest.php
class CreateOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Or check user permissions
    }
    
    public function rules()
    {
        return [
            'shipping_address_id' => [
                'required', 
                'integer', 
                'exists:addresses,id'
            ],
            'billing_address_id' => [
                'required', 
                'integer', 
                'exists:addresses,id'
            ],
            'payment_method' => [
                'required', 
                'string', 
                'in:stripe,paypal,cod'
            ],
            'notes' => ['nullable', 'string', 'max:500']
        ];
    }
    
    public function messages()
    {
        return [
            'shipping_address_id.exists' => 
                'The selected shipping address is invalid.',
        ];
    }
}
```

### 7.3 Middleware & Authorization

**Learn by Searching:**
- 🔍 "Laravel middleware creation and usage"
- 🔍 "Laravel gates and policies authorization"
- 🔍 "Role-based access control Laravel"

```php
// app/Http/Middleware/CheckAdmin.php
class CheckAdmin
{
    public function handle($request, Closure $next)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        
        return $next($request);
    }
}

// Register in bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\CheckAdmin::class,
    ]);
})
```

### 7.4 Error Handling

**Learn by Searching:**
- 🔍 "Laravel exception handling"
- 🔍 "Laravel custom error responses"
- 🔍 "API error response format standards"

```php
// bootstrap/app.php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->shouldRenderJsonWhen(function ($request) {
        return $request->is('api/*');
    });
})

// Create trait for consistent responses
// app/HttpResponses.php
trait HttpResponses
{
    protected function success($data, $message = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }
    
    protected function error($data, $message = null, $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $data
        ], $code);
    }
}
```

### 7.5 Testing

**Learn by Searching:**
- 🔍 "Laravel API testing with PHPUnit"
- 🔍 "Laravel feature tests for API endpoints"
- 🔍 "Laravel database factories for testing"
- 🔍 "Mocking external services in Laravel tests"

```php
// tests/Feature/OrderTest.php
class OrderTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_create_order()
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['price' => 100]);
        
        // Add to cart
        $this->actingAs($user, 'api')
            ->postJson('/api/cart/items', [
                'product_id' => $product->id,
                'quantity' => 2
            ])
            ->assertStatus(200);
        
        // Create order
        $response = $this->actingAs($user, 'api')
            ->postJson('/api/orders', [
                'shipping_address_id' => $address->id,
                'billing_address_id' => $address->id,
                'payment_method' => 'stripe'
            ])
            ->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'order' => ['id', 'order_number', 'total_amount'],
                    'next_step' => ['payment_intent_id', 'client_secret']
                ]
            ]);
        
        // Verify order created
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending'
        ]);
        
        // Verify cart cleared
        $this->assertDatabaseCount('cart_items', 0);
    }
}
```

### 7.6 Performance Optimization

**Learn by Searching:**
- 🔍 "Laravel query optimization N+1 problem"
- 🔍 "Laravel caching strategies"
- 🔍 "Laravel queue jobs for background tasks"
- 🔍 "Database indexing best practices"

```php
// Eager loading to prevent N+1 queries
$orders = Order::with([
    'items.product', 
    'shippingAddress', 
    'billingAddress',
    'payment'
])->where('user_id', auth()->id())->get();

// Caching frequently accessed data
$categories = Cache::remember('categories', 3600, function () {
    return Category::with('products')->get();
});

// Queue jobs for heavy tasks
dispatch(new SendOrderConfirmationEmail($order));
```

---

## API Testing Checklist

### Authentication:
- [ ] Register new user
- [ ] Login with valid credentials
- [ ] Login with invalid credentials
- [ ] Access protected route without token
- [ ] Access protected route with valid token
- [ ] Refresh token
- [ ] Logout

### Products:
- [ ] List all products
- [ ] Get single product
- [ ] Search products
- [ ] Filter by category
- [ ] Create product (admin)
- [ ] Update product (admin)
- [ ] Delete product (admin)

### Cart:
- [ ] Get empty cart
- [ ] Add item to cart
- [ ] Add duplicate item (should increment)
- [ ] Update cart item quantity
- [ ] Remove item from cart
- [ ] Clear cart
- [ ] Get cart total

### Orders:
- [ ] Create order from cart
- [ ] Create order with empty cart (should fail)
- [ ] Get user's orders
- [ ] Get specific order details
- [ ] Access other user's order (should fail)
- [ ] Cancel pending order
- [ ] Cancel shipped order (should fail)
- [ ] Update order status (admin)

### Payments:
- [ ] Create payment intent
- [ ] Confirm payment with test card
- [ ] Handle webhook events
- [ ] Test payment failure
- [ ] Test refund

---

## Common Issues & Solutions

### Issue: "Route [login] not defined" error
**Learn:** 🔍 "Laravel API authentication route not defined"

**Solution:** Configure exception handler to return JSON for API routes:
```php
// bootstrap/app.php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->shouldRenderJsonWhen(function ($request) {
        return $request->is('api/*');
    });
})
```

### Issue: Route model binding not working
**Learn:** 🔍 "Laravel route model binding parameter names"

**Solution:** Parameter name in route must match method parameter:
```php
// Route
Route::get('/orders/{order}', [OrderController::class, 'show']);

// Controller
public function show(Order $order) { } // Parameter name must be 'order'
```

### Issue: Undefined array key in resource
**Learn:** 🔍 "Laravel API resource conditional attributes"

**Solution:** Use `$this->when()` or `$this->whenLoaded()`:
```php
return [
    'comment' => $this->when($this->comment, $this->comment),
    'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
];
```

### Issue: Mass assignment error
**Learn:** 🔍 "Laravel mass assignment protection"

**Solution:** Add fields to `$fillable` array in model:
```php
protected $fillable = ['name', 'email', 'password'];
```

---

## Resources & Documentation

### Official Documentation:
- 📚 [Laravel Documentation](https://laravel.com/docs)
- 📚 [Stripe API Documentation](https://stripe.com/docs/api)
- 📚 [JWT Auth Documentation](https://jwt-auth.readthedocs.io/)

### Learning Resources:
- 🎥 YouTube: "Laravel API tutorial"
- 🎥 YouTube: "Stripe payment integration"
- 📖 Laracasts: Laravel courses
- 📖 Laravel Daily: Tips and tricks

### Tools:
- Postman: API testing
- Stripe Dashboard: Payment testing
- Laravel Telescope: Debugging
- MySQL Workbench: Database management

---

## Next Steps

1. ✅ Add product reviews and ratings
2. ✅ Implement wishlist functionality
3. ✅ Add product images upload
4. ✅ Implement email notifications
5. ✅ Add order tracking
6. ✅ Implement discount codes/coupons
7. ✅ Add admin dashboard endpoints
8. ✅ Implement analytics and reporting
9. ✅ Add multiple payment gateways
10. ✅ Deploy to production

---

## Best Practices Learned

1. **Always validate input** - Use Form Requests
2. **Use database transactions** - For multi-step operations
3. **Eager load relationships** - Prevent N+1 queries
4. **Return consistent API responses** - Use Resources
5. **Verify resource ownership** - Authorization checks
6. **Store price snapshots** - Capture price at purchase time
7. **Generate unique identifiers** - Order numbers, SKUs
8. **Handle errors gracefully** - Return meaningful messages
9. **Test thoroughly** - Write feature tests
10. **Document your API** - Keep documentation updated

---

**Happy Coding! 🚀**

Remember: The best way to learn is by building. Start with the basics and gradually add more features. Don't hesitate to search for specific topics as you encounter them!
