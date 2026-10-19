<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $requestedReferralCode = trim((string) old(
            'referral_code',
            $request->query('ref') ?? $request->query('referral') ?? $request->query('referralCode') ?? ''
        ));
        $referralReferrer = null;
        $referralCode = '';

        if ($requestedReferralCode !== '' && Schema::hasColumn('users', 'referral_code')) {
            $referralReferrer = User::query()
                ->where('role', 'model')
                ->where('referral_code', $requestedReferralCode)
                ->first(['id', 'name', 'referral_code']);

            $referralCode = $referralReferrer?->referral_code ?? '';
        }

        return view('home', compact('referralCode', 'referralReferrer'));
    }
}
