<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use League\Csv\Reader;
use SoapClient;

class CreateCustomersCommand extends Command
{
    protected $signature = 'customers:create {--from-csv} {firstname?} {lastname?} {email?} {password?}';

    protected $description = 'Create new magento customers';

    public function handle()
    {
        $client = new SoapClient(config('services.magento.wsdl'));

        $session = $client->login(config('services.magento.username'), config('services.magento.password'));

        if ($this->option('from-csv')) {
            $this->info('Creating customers from customers.csv');

            $csv = Reader::createFromPath(storage_path('app/customers.csv'));

            $customers = $csv->fetch();
            foreach ($customers as $index => $customer) {

                if ($index == 0) {
                    continue;
                }

                try {

                    $customerId = $client->customerCustomerCreate($session, [
                        'email'      => $customer[0],
                        'firstname'  => $customer[2],
                        'lastname'   => $customer[3],
                        'password'   => $customer[1],
                        'website_id' => 1,
                        'store_id'   => 1,
                    ]);

                    if ($customerId) {
                        $this->info('Customer created successfully');
                    }

                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
        }

        $this->info('Done');
    }
}