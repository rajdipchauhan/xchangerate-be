<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('feature_list')->insert([
            'name' => 'Test Mode',
            'feature_type' => 'Individual',
            'sort_name' => 'test_mode',
            'price' =>'0.1231521',
            'duration' => '30',
            'description'=>'Test Mode allows to test different trading options without making an actual impact on your real trading strategy.'
        ]);
        DB::table('feature_list')->insert([
            'name' => 'Bitfinex',
            'feature_type' => 'Exchange',
            'sort_name' => 'bitfinex',
            'price' =>'1.0231248',
            'duration' => '365',
            'description'=>'Buying this options allows you to integrate your BITFINEX accounts into trading platform.',
            'total_resource'=>'0'
        ]);
        DB::table('feature_list')->insert([
            'name' => 'Bittrex',
            'feature_type' => 'Exchange',
            'sort_name' => 'bittrex',
            'price' =>'1.0231248',
            'duration' => '125',
            'description'=>'Buying this options allows you to integrate your BITTREX accounts into trading platform.',
            'total_resource'=>'0'
        ]);
        DB::table('feature_list')->insert([
            'name' => 'Email alerts',
            'feature_type' => 'Individual',
            'sort_name' => 'email_alerts',
            'price' =>'0.1231521',
            'duration' => '0',
            'description'=>'You can buy an amount of email alerts in order to enable the option to get notified about trading updates via email.',
            'total_resource'=>'500'
        ]);
        DB::table('feature_list')->insert([
            'name' => 'SMS alerts',
            'feature_type' => 'Individual',
            'sort_name' => 'sms_alerts',
            'price' =>'0.1231521',
            'duration' => '0',
            'description'=>'You can buy an amount of sms alerts in order to enable the option to get notified about trading updates via SMS.',
            'total_resource'=>'500'
        ]);
    }
}
