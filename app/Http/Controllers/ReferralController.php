<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Helpers\Coinpayments;
use App\Http\Controllers\ApiController;
use App\Models\BillingHistory;
use App\Models\BillingPackage;
use App\Models\ReferralBonus;
use App\Models\ScratchCode;
use App\Models\UserPackage;
use App\Models\UserPackageExchange;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;


class ReferralController extends ApiController
{
    public function index(Auth $auth, Request $request)
    {
        $referrals = ReferralBonus::with('mentee', 'billing')->where('mentor_id', $auth->user()->id);
        $referrals = $this->applyPaginationData($request, $referrals, ['page' => ['limit' => null]])->get();

        $not_sent = $referrals->where('sent', false)->sum('mentor_bonus_to_pay');
        $sent = $referrals->where('sent', true)->sum('mentor_bonus_to_pay');
        $total = $referrals->sum('mentor_bonus_to_pay');

        return response()->json([
            'data' => [
                'has_wallet' => $auth->user()->wallet_id ? true : false,
                'not_sent' => $not_sent,
                'sent' => $sent,
                'total' => $total,
                'list' => $referrals,
            ],
            'meta' => $this->getResponseMetadata($request, $referrals->count())
        ]);
    }

    public function getReferralUrl(Auth $auth, Request $request)
    {
        $user = $auth->user();

        if (!$user->referral) {
            $user->referral = md5(time() . $user->id . rand(0, 99999));
            $user->save();
        }

        return response()->json([
            'url' => $user->referral
        ]);
    }

    public function adminUsersList(Request $request)
    {
        $referrals = ReferralBonus::with('mentor')->groupBy('mentor_id')
            ->selectRaw('mentor_id, sum(mentor_bonus_to_pay) as total_mentor_bonus_to_pay, max(created_at) as last_unpaid_bonus_date')
            ->where('sent', false);
        $referrals = $this->applyPaginationData($request, $referrals, ['page' => ['limit' => null]])->get();


        return response()->json([
                'data' => $referrals,
                'meta' => $this->getResponseMetadata($request, $referrals->count())
            ]
        );
    }


    public function adminUsersListPaid(Request $request)
    {
        $referrals = ReferralBonus::with('mentor')->groupBy('mentor_id')
            ->selectRaw('mentor_id, sum(mentor_bonus_to_pay) as total_mentor_bonus_paid, max(sent_date) as last_paid_bonus_date')
            ->where('sent', true);
        $referrals = $this->applyPaginationData($request, $referrals, ['page' => ['limit' => null]])->get();

        return response()->json([
                'data' => $referrals,
                'meta' => $this->getResponseMetadata($request, $referrals->count())
            ]
        );    }


    public function adminUsersPay(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required',
            'last_date' => 'required|date'
        ]);

        ReferralBonus::where('mentor_id', $request->input('user_id'))
            ->where('created_at', '<=', $request->input('last_date'))
            ->update([
                'sent' => true,
                'sent_date' => Carbon::now()
            ]);

        return $this->adminUsersList();
    }
}