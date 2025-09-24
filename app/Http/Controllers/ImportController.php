<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'csv' => 'required|file|mimes:csv,txt',
        ]);

        $file = fopen($request->file('csv')->getPathname(), 'r');
        $header = fgetcsv($file);

        $summary = ['total'=>0, 'imported'=>0, 'updated'=>0, 'invalid'=>0, 'duplicates'=>0];
        $seen = [];

        while (($row = fgetcsv($file)) !== false) {
            $summary['total']++;
            $data = array_combine($header, $row);

            if (!isset($data['sku'], $data['name'])) {
                $summary['invalid']++;
                continue;
            }

            if (in_array($data['sku'], $seen)) {
                $summary['duplicates']++;
                continue;
            }
            $seen[] = $data['sku'];

            $product = Product::updateOrCreate(
                ['sku' => $data['sku']],
                ['name' => $data['name'], 'price' => $data['price'] ?? null]
            );

            $product->wasRecentlyCreated ? $summary['imported']++ : $summary['updated']++;
        }

        fclose($file);
        return response()->json($summary);
    }
}
