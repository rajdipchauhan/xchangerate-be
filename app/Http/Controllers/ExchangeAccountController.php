<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Models\ExchangeAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExchangeAccountController extends ApiController
{
    /**
     * @var Auth
     */
    private $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function index(Request $request)
    {
        $query = ExchangeAccount::byUser($this->auth->user());
        $total = $query->count();
        $accounts = $this->applyPaginationData($request, $query)->get();

        return response()->json([
            'data' => $accounts,
            'meta' => $this->getResponseMetadata($request, $total)
        ]);
    }

    public function show($id)
    {
        $account = ExchangeAccount::findOrFail($id);
        if (!$this->userHasAccess($id)) {
            return response('Forbidden', 403);
        }

        return response()->json($account);
    }

    public function create(Request $request)
    {

        $validator = Validator::make($request->input(), [
            'name' => 'required',
            'exchange_id' => 'required|string',
            'key' => 'required',
            'secret' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $account = ExchangeAccount::create(array_merge($request->input(), ['user_id' => $this->auth->user()->id]));

        return response()->json($account, 201);
    }

    public function update(Request $request, $id)
    {
        $account = ExchangeAccount::findOrFail($id);
        if (!$this->userHasAccess($id)) {
            return response('Forbidden', 403);
        }
        $validator = Validator::make($request->input(), [
            'name' => 'required',
            'exchange_id' => 'required|string',
            'key' => 'required',
            'secret' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $account->fill(array_merge($request->input(), ['user_id' => $this->auth->user()->id]));
        $account->save();

        return response()->json($account);
    }

    public function delete($id)
    {
        $account = ExchangeAccount::findOrFail($id);
        if (!$this->userHasAccess($id)) {
            return response('Forbidden', 403);
        }
        $account->delete();

        return response()->json([], 200);
    }

    protected function userHasAccess($id)
    {
        $user = $this->auth->user();
        $account = ExchangeAccount::findOrFail($id);

        return $account->user_id == $user->id;
    }
}