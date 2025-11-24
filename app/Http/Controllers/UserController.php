<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\AddressesResource;
use App\Http\Resources\OrdersResource;
use App\Http\Resources\UsersResource;
use App\HttpResponses;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use HttpResponses;

    public function show_profile()
    {
        return new UsersResource(Auth::user());
    }

    public function update_profile(UpdateProfileRequest $request)
    {
        $user = Auth::user();

        $validated = $request->validated();

        $user->update($validated);

        return $this->success([
            'user' => new UsersResource($user)
        ], 'Profile updated successfully');
    }

    public function show_addresses()
    {
        $user = Auth::user();
        return AddressesResource::collection($user->addresses);
    }

    public function create_address(StoreAddressRequest $request)
    {
        $validated = $request->validated();

        $validated['user_id'] = Auth::id();

        $address = Address::create($validated);

        return $this->success([
            'address' => new AddressesResource($address)
        ], 'Address added successfully', 201);
    }

    public function update_address(UpdateAddressRequest $request, Address $address)
    {
        $userId = Auth::id();

        // Authorization check
        if ($address->user_id != $userId) {
            return $this->error([], 'Unauthorized', 403);
        }

        $validated = $request->validated();

        $address->update($validated);

        return $this->success([
            'address' => new AddressesResource($address)
        ], 'Address updated successfully');
    }

    public function delete_address(Address $address)
    {
        $userId = Auth::id();

        if ($address->user_id != $userId) {
            return $this->error([], 'Unauthorized', 403);
        }

        $address->delete();

        return $this->success([], 'Address deleted successfully');
    }

    public function index_orders()
    {
        $user = Auth::user();
        return OrdersResource::collection($user->orders);
    }

    public function show_order(Order $order)
    {
        $userId = Auth::id();

        if ($order->user_id != $userId) {
            return $this->error([], 'Unauthorized', 403);
        }

        return new OrdersResource($order);
    }
}
