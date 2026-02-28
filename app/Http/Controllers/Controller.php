<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

//    protected function getCachedVendor(): Vendor
//    {
//        $user = Auth::user();
//        if (!$user) {
//            abort(403, 'User not authenticated.');
//        }
//
//        $cacheKey = 'vendor_' . $user->id . '_' . ($user->vendorID ?? 'none');
//
//        return Cache::remember($cacheKey, 300, function () use ($user) {
//            if ($user->vendorID) {
//                $vendor = Vendor::select(['id', 'title', 'author', 'subscriptionPlanId', 'gst'])->where('id', $user->vendorID)->first();
//                if ($vendor) {
//                    return $vendor;
//                }
//            }
//
//
//
//            $vendor = Vendor::select(['id', 'title', 'author', 'subscriptionPlanId', 'gst'])->where('author', $user->firebase_id ?? $user->id)->first();
//            if (!$vendor) {
//                abort(403, 'Vendor profile not found.');
//            }
//
//            return $vendor;
//        });
//    }
}
