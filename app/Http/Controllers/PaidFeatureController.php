<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Models\PaidFeature;
use App\Models\FeatureList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PaidFeatureController extends ApiController
{
    /**
     * @var Auth
     */
    private $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function index(Auth $auth) {
        //
    }

    public function paidfeatures(Auth $auth, Request $request) {
        $returnData = array();

        $paidfeaturequery = PaidFeature::where('paid_feature.user_id', $auth->user()->id)
                ->join('feature_list', 'paid_feature.feature_id', '=', 'feature_list.id');

        $paidFeatureLists = $paidfeaturequery->get();
        foreach ($paidFeatureLists as $paidFeatureVal) {
            unset($paidFeatureVal->id);
            unset($paidFeatureVal->user_id);
            unset($paidFeatureVal->feature_id);
            unset($paidFeatureVal->created_at);
            unset($paidFeatureVal->updated_at);
            if ($paidFeatureVal->payment_status == 100 && $paidFeatureVal->expire_date >= date('Y-m-d h:i:s')) {
                $paidFeatureVal['account_status'] = true;
            } else {
                $paidFeatureVal['account_status'] = false;
            }

            unset($paidFeatureVal->payment_status);
            if ($paidFeatureVal->feature_type == 'Exchange') {
                unset($paidFeatureVal->feature_type);
                $returnData['exchanges'][$paidFeatureVal->sort_name] = $paidFeatureVal;
            } else {
                unset($paidFeatureVal->feature_type);
                $returnData[$paidFeatureVal->sort_name] = $paidFeatureVal;
            }
        }

        return response()->json([
                    'data' => $returnData,
                    'meta' => $this->getResponseMetadata($request)
        ]);
    }

    public function payinfo(Auth $auth, Request $request) {
        $returnData = array();

        $paidfeaturequery = PaidFeature::where('paid_feature.user_id', $auth->user()->id)
                ->where('paid_feature.payment_status', 100)
                ->join('feature_list', 'paid_feature.feature_id', '=', 'feature_list.id')
                ->select('paid_feature.price');

        $paidFeatureLists = $paidfeaturequery->get();
        foreach ($paidFeatureLists as $paidFeatureVal) {
            unset($paidFeatureVal->id);
            unset($paidFeatureVal->user_id);
            unset($paidFeatureVal->feature_id);
            unset($paidFeatureVal->created_at);
            unset($paidFeatureVal->updated_at);
            if ($paidFeatureVal->payment_status == 100 && $paidFeatureVal->expire_date >= date('Y-m-d h:i:s')) {
                $paidFeatureVal['account_status'] = true;
            } else {
                $paidFeatureVal['account_status'] = false;
            }

            unset($paidFeatureVal->payment_status);
            if ($paidFeatureVal->feature_type == 'Exchange') {
                unset($paidFeatureVal->feature_type);
                $returnData['exchanges'][$paidFeatureVal->sort_name] = $paidFeatureVal;
            } else {
                unset($paidFeatureVal->feature_type);
                $returnData[$paidFeatureVal->sort_name] = $paidFeatureVal;
            }
        }

        return response()->json([
                    'data' => $returnData,
                    'meta' => $this->getResponseMetadata($request)
        ]);
    }

    public function show($id)
    {
        //
    }

    public function create()
    {
        //
    }

    public function update() {
        //
    }

    public function delete()
    {
        //
    }
}
