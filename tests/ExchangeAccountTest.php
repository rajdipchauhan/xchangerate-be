<?php

use App\Models\Coin;
use App\Models\CurrencyRate;
use App\Models\ExchangeAccount;
use App\Models\User;

class ExchangeAccountTest extends ApiTestCase
{
    public function testIndexRoute()
    {
        $user = factory(User::class)->create();
        $account = factory(ExchangeAccount::class)->create([
            'user_id' => $user->id
        ]);

        $data = DB::select('SELECT secret FROM exchange_accounts');
        $encrypted = object_get(array_get($data, '0'), 'secret');

        $this->authenticatedJson('GET', '/api/exchange-accounts', [], [], $user);
        $data = json_decode($this->response->getContent(), true)['data'];

        $this->assertNotEquals($encrypted, array_get($data, '0.secret'));
        $this->assertEquals($account->user_id, array_get($data, "0.user_id"));
        $this->assertEquals($account->exchange_id, array_get($data, "0.exchange_id"));
        $this->assertEquals($account->name, array_get($data, "0.name"));
        $this->assertEquals($account->secret, array_get($data, "0.secret"));
    }

    public function testShowRoute()
    {
        $user = factory(User::class)->create();
        $account = factory(ExchangeAccount::class)->create([
            'user_id' => $user->id
        ]);

        $data = DB::select('SELECT secret FROM exchange_accounts');
        $encrypted = object_get($data, 'secret');

        $this->authenticatedJson('GET', "/api/exchange-accounts/$account->id", [], [], $user);
        $data = json_decode($this->response->getContent(), true)['data'];

        $this->assertNotEquals($encrypted, array_get($data, 'secret'));
        $this->assertEquals($account->user_id, array_get($data, "user_id"));
        $this->assertEquals($account->exchange_id, array_get($data, "exchange_id"));
        $this->assertEquals($account->name, array_get($data, "name"));
        $this->assertEquals($account->secret, array_get($data, "secret"));
    }

    public function testInvalidShowRoute()
    {
        $user = factory(User::class)->create();
        $invalidUser = factory(User::class)->create();
        $account = factory(ExchangeAccount::class)->create([
            'user_id' => $user->id
        ]);

        $this->authenticatedJson('GET', "/api/exchange-accounts/$account->id", [], [], $invalidUser);
        $this->assertEquals(403, $this->response->status());

        $this->authenticatedJson('GET', "/api/exchange-accounts/99", [], [], $invalidUser);
        $this->assertEquals(404, $this->response->status());
    }

    public function testCreateRoute()
    {
        $user = factory(User::class)->create();
        $input = [
            'exchange_id' => 'bittrex',
            'user_id' => $user->id,
            'key' => 'key',
            'secret' => 'secret',
            'name' => 'My account'
        ];
        $this->authenticatedJson('POST', "/api/exchange-accounts", $input, [], $user);
        $data = json_decode($this->response->content(), true)['data'];

        $this->assertEquals(201, $this->response->status());
        foreach ($input as $name => $value) {
            $this->assertEquals($value, array_get($data, $name));
        }
    }

    public function testUpdateRoute()
    {
        $user = factory(User::class)->create();
        $account = factory(ExchangeAccount::class)->create([
            'user_id' => $user->id
        ]);
        $input = [
            'exchange_id' => 'bittrex',
            'user_id' => $user->id,
            'key' => 'key',
            'secret' => 'secret',
            'name' => 'My account'
        ];
        $this->authenticatedJson('PUT', "/api/exchange-accounts/$account->id", $input, [], $user);
        $data = json_decode($this->response->content(), true)['data'];

        $this->assertEquals(200, $this->response->status());
        foreach ($input as $name => $value) {
            $this->assertEquals($value, array_get($data, $name));
        }
    }

    public function testDeleteRoute()
    {
        $user = factory(User::class)->create();
        $account = factory(ExchangeAccount::class)->create([
            'user_id' => $user->id
        ]);
        $this->authenticatedJson('DELETE', "/api/exchange-accounts/$account->id", [], [], $user);

        $this->assertEquals(204, $this->response->status());
        $this->assertNull(ExchangeAccount::find($account->id));
    }
}