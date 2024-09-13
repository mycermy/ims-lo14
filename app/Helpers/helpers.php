<?php

// if (!function_exists('settings')) {
//     function settings() {
//         $settings = cache()->remember('settings', 24*60, function () {
//             return \Modules\Setting\Entities\Setting::firstOrFail();
//         });

//         return $settings;
//     }
// }

// if (!function_exists('format_currency')) {
//     function format_currency($value, $format = true) {
//         if (!$format) {
//             return $value;
//         }

//         $settings = settings();
//         $position = $settings->default_currency_position;
//         $symbol = $settings->currency->symbol;
//         $decimal_separator = $settings->currency->decimal_separator;
//         $thousand_separator = $settings->currency->thousand_separator;

//         if ($position == 'prefix') {
//             $formatted_value = $symbol . number_format((float) $value, 2, $decimal_separator, $thousand_separator);
//         } else {
//             $formatted_value = number_format((float) $value, 2, $decimal_separator, $thousand_separator) . $symbol;
//         }

//         return $formatted_value;
//     }
// }

use App\Models\Product;

if (!function_exists('make_reference_id')) {
    function make_reference_id($prefix, $number) {
        $padded_text = $prefix . '-' . date('Y') . '-' . str_pad($number, 5, 0, STR_PAD_LEFT);

        return $padded_text;
    }
}

// if (!function_exists('array_merge_numeric_values')) {
//     function array_merge_numeric_values() {
//         $arrays = func_get_args();
//         $merged = array();
//         foreach ($arrays as $array) {
//             foreach ($array as $key => $value) {
//                 if (!is_numeric($value)) {
//                     continue;
//                 }
//                 if (!isset($merged[$key])) {
//                     $merged[$key] = $value;
//                 } else {
//                     $merged[$key] += $value;
//                 }
//             }
//         }

//         return $merged;
//     }
// }

if (!function_exists('updateStock')) {
    function updateStock($productID, $purchaseQty, $type)
        {
            $product = Product::findOrFail($productID);
            $updateQty = 0;
    
            if ($type == 'add') {
                $updateQty = $product->quantity + $purchaseQty;
            } else if ($type == 'sub') {
                $updateQty = $product->quantity - $purchaseQty;
            }
    
            // Update stock quantity in the product
            $product->update([
                'quantity' => $updateQty
            ]);
        }
}
