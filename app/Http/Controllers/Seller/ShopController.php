<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ShopController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $supplier = $user->supplier;

        return view('seller.shop.index', compact('supplier'));
    }

    public function edit()
    {
        $user = Auth::user();
        $supplier = $user->supplier;

        return view('seller.shop.edit', compact('supplier'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $supplier = $user->supplier;

        $validated = $request->validate([
            'brand_name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:5'],
            'description' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
            'banner' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:5120'],
        ]);

        // Update basic info
        $supplier->brand_name = $validated['brand_name'];
        $supplier->country = $validated['country'];
        $supplier->description = $validated['description'] ?? null;

        // Handle avatar upload
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            // Delete old avatar if exists
            if ($supplier->avatar && Storage::disk('public')->exists($supplier->avatar)) {
                Storage::disk('public')->delete($supplier->avatar);
            }

            $avatarPath = $request->file('avatar')->store('suppliers/avatars', 'public');
            $supplier->avatar = $avatarPath;
        }

        // Handle banner upload
        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            // Delete old banner if exists
            if ($supplier->banner && Storage::disk('public')->exists($supplier->banner)) {
                Storage::disk('public')->delete($supplier->banner);
            }

            $bannerPath = $request->file('banner')->store('suppliers/banners', 'public');
            $supplier->banner = $bannerPath;
        }

        $supplier->save();

        return redirect()->route('seller.shop.index')->with('success', __('seller.shop.updated'));
    }

    public function deleteAvatar()
    {
        $user = Auth::user();
        $supplier = $user->supplier;

        if ($supplier->avatar && Storage::disk('public')->exists($supplier->avatar)) {
            Storage::disk('public')->delete($supplier->avatar);
            $supplier->avatar = null;
            $supplier->save();
        }

        return back()->with('success', __('seller.shop.avatar_deleted'));
    }

    public function deleteBanner()
    {
        $user = Auth::user();
        $supplier = $user->supplier;

        if ($supplier->banner && Storage::disk('public')->exists($supplier->banner)) {
            Storage::disk('public')->delete($supplier->banner);
            $supplier->banner = null;
            $supplier->save();
        }

        return back()->with('success', __('seller.shop.banner_deleted'));
    }
}
