<?php
namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Jobs\OrderCompleted;
use Cartalyst\Stripe\Stripe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InfluencerMicroservices\UserService;

class OrderController extends Controller
{
    public UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function store(Request $request)
    {
        if (!$link = Link::where('code', $request->input('code'))->first()) {
            abort(400, 'Invalid code');
        }

        $user = $this->userService->get($link->user_id);
        // $user = $this->userService->get($link->user_id);

        try {
            DB::beginTransaction();

            $order = new Order();

            $order->code = $link->code;
            $order->user_id = $user->id;
            $order->influencer_email = $user->email;
            $order->first_name = $request->input('first_name');
            $order->last_name = $request->input('last_name');
            $order->email = $request->input('email');
            $order->address = $request->input('address');
            $order->country = $request->input('country');
            $order->city = $request->input('city');
            $order->zip = $request->input('zip');

            $order->save();

            $lineItems = [];
            // dd($request->input('items'));
            foreach ($request->input('items') as $item) {
                $product = Product::find($item['product_id']);

                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_title = $product->title;
                $orderItem->price = $product->price;
                $orderItem->quantity = $item['quantity'];
                $orderItem->influencer_revenue = 0.1 * $product->price * $item['quantity'];
                $orderItem->admin_revenue = 0.9 * $product->price * $item['quantity'];

                $orderItem->save();

                $lineItems[] = [
                    'name' => $product->title,
                    'description' => $product->description,
                    'images' => [
                        $product->image
                    ],
                    'amount' => 100 * $product->price,
                    'currency' => 'usd',
                    'quantity' => $item['quantity']
                ];
            }

            $stripe = Stripe::make(env('STRIPE_SECRET'));
            // dd([
            //     'payment_method_types' => ['card'],
            //     'line_items' => $lineItems,
            //     'success_url' => env('CHECKOUT_URL') . '/success?source={CHECKOUT_SESSION_ID}',
            //     'cancel_url' => env('CHECKOUT_URL') . '/error'
            // ]);

            $source = $stripe->checkout()->sessions()->create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'success_url' => env('CHECKOUT_URL') . '/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => env('CHECKOUT_URL') . '/error'
            ]);

            $order->transaction_id = $source['id'];
            $order->save();

            DB::commit();
            return $source;
        } catch (\Throwable $e) {
            DB::rollBack();

            return response([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function confirm(Request $request)
    {
        // dd('here');
        if (!$order = Order::where('transaction_id', $request->input('session_id'))->first()) {
            return response([
                'error' => 'Order not found!'
            ], 404);
        }

        $order->complete = 1;
        $order->save();

        $data = $order->toArray();
        $data['influencer_total'] = $order->influencer_total;
        $data['admin_total'] = $order->admin_total;
        $orderItems = [];

        foreach ($order->orderItems as $item) {
            $orderItems[] = $item->toArray();
        }
        // dd($orderItems);

        OrderCompleted::dispatch([$data, $orderItems])->onQueue('influencer_queue');
        OrderCompleted::dispatch([$data, $orderItems])->onQueue('emails_queue');
        OrderCompleted::dispatch([$data, $orderItems])->onQueue('admin_queue');

        return [
            'message' => 'success'
        ];
    }
}
