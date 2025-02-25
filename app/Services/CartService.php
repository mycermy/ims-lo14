<?php

namespace App\Services;

use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\Repository;
use Illuminate\Support\Collection;

class CartService
{
    protected $instance;

    public function __construct($instance = 'order')
    {
        $this->instance = $instance;
    }

    public function addToCart($product, $product_namex = null, $quantity = 1, $unit_price, $weight = 0, $options = [])
    {
        Cart::instance($this->instance)->add([
            'id' => $product->id,
            'name' => $product_namex ?? $product->name,
            'qty' => $quantity,
            'price' => $unit_price,
            'weight' => $weight,
            'options' => $options,
        ]);
    }

    public function removeFromCart($rowId)
    {
        Cart::instance($this->instance)->remove($rowId);
    }

    public function destroyCart()
    {
        Cart::instance($this->instance)->destroy();
    }

    public function getSubTotal()
    {
        return Cart::instance($this->instance)->subtotal();
    }

    public function getPriceTotal()
    {
        return Cart::instance($this->instance)->priceTotal();
    }

    public function getCartItems()
    {
        $cartContent = Cart::instance($this->instance)->content();

        return $cartContent->map(function ($item) {
            return new Repository([
                'rowId' => $item->rowId,
                'product_id' => $item->id,
                'name' => $item->name,
                'qty' => $item->qty,
                'price' => number_format($item->price, 2),
                'subtotal' => number_format($item->subtotal, 2),
            ]);
        });
    }

    public function getCartItemsModel()
    {
        $cartContent = Cart::instance($this->instance)->content();

        return $cartContent->map(function ($cartItem) {
            return new class($cartItem) extends Model implements \Serializable {
                protected $fillable = [
                    'rowId',
                    'product_id',
                    'name',
                    'qty',
                    'price',
                    'subtotal'
                ];

                public function __construct($cartItem)
                {
                    parent::__construct([
                        'rowId' => $cartItem->rowId,
                        'product_id' => (string) $cartItem->id,
                        'name' => (string) $cartItem->name,
                        'qty' => (int) $cartItem->qty,
                        'price' => (float) number_format($cartItem->price, 2),
                        'subtotal' => (float) number_format($cartItem->subtotal, 2),
                    ]);
                }

                public function serialize(): string
                {
                    return serialize([
                        'rowId' => $this->rowId,
                        'product_id' => $this->product_id,
                        'name' => $this->name,
                        'qty' => $this->qty,
                        'price' => $this->price,
                        'subtotal' => $this->subtotal,
                    ]);
                }

                public function unserialize($data)
                {
                    $unserializedData = unserialize($data);
                    foreach ($unserializedData as $key => $value) {
                        $this->$key = $value;
                    }
                }
            };
        });
    }

    public function getCartItemsCollection()
    {
        $cartContent = Cart::instance($this->instance)->content();

        return $cartContent->map(function ($cartItem) {
            return [
                'rowId' => $cartItem->rowId,
                'product_id' => (string) $cartItem->id,
                'product_name' => (string) $cartItem->name,
                'quantity' => (int) $cartItem->qty,
                'unit_price' => (float) number_format($cartItem->price, 2),
                'sub_total' => (float) number_format($cartItem->subtotal, 2),
            ];
        });
    }
}
