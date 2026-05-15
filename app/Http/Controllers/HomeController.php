<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        try {
            $testimonials = Schema::hasTable('testimonials')
                ? Testimonial::query()
                    ->with('submitter:id,name,profile_photo_path')
                    ->where('is_published', true)
                    ->orderBy('sort_order')
                    ->orderByDesc('created_at')
                    ->take(8)
                    ->get()
                : Collection::make();
        } catch (\Exception $e) {
            $testimonials = Collection::make();
        }

        $requestedReferralCode = trim((string) old('referral_code', $request->query('ref', '')));
        $referralReferrer = null;
        $referralCode = '';

        if ($requestedReferralCode !== '' && Schema::hasColumn('users', 'referral_code')) {
            $referralReferrer = User::query()
                ->where('role', 'model')
                ->where('referral_code', $requestedReferralCode)
                ->first(['id', 'name', 'referral_code']);

            $referralCode = $referralReferrer?->referral_code ?? '';
        }

        return view('home', compact('testimonials', 'referralCode', 'referralReferrer'));
    }
}
