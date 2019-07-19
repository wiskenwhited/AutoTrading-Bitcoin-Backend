<?php

namespace App\Models;


class ReferralBonus extends Model
{
    public function billing()
    {
        return $this->belongsTo(BillingHistory::class, 'billing_history_id');
    }
    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }
    public function mentee()
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }

    public function assignBonus($mentee_user_id, $billing_history_id)
    {
        $hasMentor = User::find($mentee_user_id);

        if ($hasMentor->mentor_id) {
            $hasPurchases = ReferralBonus::where('mentee_id', $mentee_user_id)->first();
            $percent = $hasPurchases ? 10 : 25;
            $billing_history = BillingHistory::find($billing_history_id);

            $referral = new ReferralBonus();
            $referral->mentor_id = $hasMentor->mentor_id;
            $referral->mentee_id = $mentee_user_id;
            $referral->package_id = $billing_history->package_id;
            $referral->billing_history_id = $billing_history_id;
            $referral->total_price = $billing_history->total_price;
            $referral->mentor_bonus_perc = $percent;
            $referral->mentor_bonus_to_pay = $referral->total_price * ($percent / 100);
            $referral->save();

            return $referral;
        }

        return null;
    }
}