<?php

namespace App\Http\Controllers\Api;
use App\Traits\ApiResponseTrait;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Http\Controllers\Controller;
use App\Models\ProductVariation;

class CartController extends Controller
{
        use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */

// public function index(Request $request)
// {
//     $user = $request->user();

//     dd([
//         'authenticated' => auth()->check(),
//         'user_id' => $user?->id,
//         'user_email' => $user?->email,
//         'cart_items_count' => Cart::where('user_id', $user?->id)->count(),
//         'cart_items' => Cart::where('user_id', $user?->id)->get(),
//     ]);
// }
public function index(Request $request)
{
    $user = $request->user();

    $cartItems = Cart::where('user_id', $user->id)
        ->with([
            'product',
            'variation',
        ])
        ->get();

    $total = $cartItems->sum(function ($item) {

        $price = $item->variation
            ? ($item->variation->sale_price ?? $item->variation->price)
            : $item->product->price;

        return $price * $item->quantity;
    });

    return $this->successResponse(
        [
            'items' => $cartItems,
            'total' => round($total, 2),
        ],
        __('messages.cart_retrieved')
    );
}

    /* Add a new item to the cart
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */




public function store(StoreCartRequest $request)
{
    $user = $request->user();

    $data = $request->validated();

    $product = Product::findOrFail($data['product_id']);

    $variation = null;

    /*
    |--------------------------------------------------------------------------
    | Check Variation
    |--------------------------------------------------------------------------
    */

    if ($product->has_variations) {

        if (empty($data['product_variation_id'])) {
            return $this->errorResponse(
                __('messages.select_product_variation'),
                [],
                422
            );
        }

        $variation = ProductVariation::where('id', $data['product_variation_id'])
            ->where('product_id', $product->id)
            ->first();

        if (! $variation) {
            return $this->errorResponse(
                __('messages.invalid_product_variation'),
                [],
                422
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Find Existing Cart Item
    |--------------------------------------------------------------------------
    */

    $cartItem = Cart::where('user_id', $user->id)
        ->where('product_id', $product->id)
        ->where('product_variation_id', $data['product_variation_id'] ?? null)
        ->first();

    $requestedQuantity = $data['quantity'];

    if ($cartItem) {
        $requestedQuantity += $cartItem->quantity;
    }

    /*
    |--------------------------------------------------------------------------
    | Check Stock
    |--------------------------------------------------------------------------
    */

    $availableStock = $variation
        ? $variation->stock
        : $product->stock;

    if ($requestedQuantity > $availableStock) {
        return $this->errorResponse(
            __('messages.insufficient_stock', [
                'stock' => $availableStock
            ]),
            [],
            422
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Existing Cart
    |--------------------------------------------------------------------------
    */

    if ($cartItem) {

        $cartItem->increment('quantity', $data['quantity']);

        return $this->successResponse(
            $cartItem->fresh()->load(['product', 'variation']),
            __('messages.cart_updated')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create Cart
    |--------------------------------------------------------------------------
    */

    $cartItem = Cart::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_variation_id' => $data['product_variation_id'] ?? null,
        'quantity' => $data['quantity'],
    ]);

    return $this->successResponse(
        $cartItem->load(['product', 'variation']),
        __('messages.cart_added'),
        201
    );
 }
    /**
     * Update the specified resource in storage.
     */


public function update(UpdateCartRequest $request, Cart $cart)
{
    // التأكد أن العنصر يخص المستخدم الحالي
    $cart = Cart::where('id', $cart->id)
        ->where('user_id', $request->user()->id)
        ->first();

    if (! $cart) {
        return $this->errorResponse(
            __('messages.cart_not_found'),
            [],
            404
        );
    }

    $data = $request->validated();

    // تحميل العلاقات
    $cart->load([
        'product',
        'variation',
    ]);

    // تحديد المخزون حسب نوع المنتج
    $availableStock = $cart->variation
        ? $cart->variation->stock
        : $cart->product->stock;

    // التحقق من المخزون
    if ($data['quantity'] > $availableStock) {

        return $this->errorResponse(
            __('messages.insufficient_stock', [
                'stock' => $availableStock,
            ]),
            [],
            422
        );
    }

    // تحديث الكمية
    $cart->update([
        'quantity' => $data['quantity'],
    ]);

    // إعادة تحميل العلاقات
    $cart->load([
        'product',
        'variation',
    ]);

    return $this->successResponse(
        $cart,
        __('messages.cart_updated')
    );
}
    /**
     * Remove the specified resource from storage.
     */
  public function destroy(Request $request, $id)
{
    $deleted = Cart::where('id', $id)
        ->where('user_id', $request->user()->id)
        ->delete();

    if (! $deleted) {
        return $this->errorResponse('Cart not found', 404);
    }

    return $this->successResponse(null, 'Cart item deleted successfully');
}
    
public function clear(Request $request)
{
    $deleted = Cart::where('user_id', $request->user()->id)->delete();

    if ($deleted === 0) {
        return $this->successResponse(
            null,
            'Cart is already empty'
        );
    }

    return $this->successResponse(
        null,
        'Cart cleared successfully'
    );
}
public function applyCoupon(Request $request)
{
    $data = $request->validate([
        'coupon' => 'required|string'
    ]);

    $coupons = [
        'SALE10' => 10,
        'SALE20' => 20,
    ];

    $couponCode = strtoupper($data['coupon']);

    if (!isset($coupons[$couponCode])) {
        return $this->errorResponse(
            'Invalid coupon code',
            422
        );
    }

    $cartItems = Cart::where(
        'user_id',
        $request->user()->id
    )->with('product')->get();

    $total = $cartItems->sum(function ($item) {
        return $item->product->price * $item->quantity;
    });

    $discount = $coupons[$couponCode];
    $discountAmount = ($total * $discount) / 100;
    $finalTotal = $total - $discountAmount;

    return $this->successResponse([
        'coupon' => $couponCode,
        'discount_percent' => $discount,
        'discount_amount' => round($discountAmount, 2),
        'final_total' => round($finalTotal, 2),
    ], 'Coupon applied successfully');
}

}